<?php
define('APP_ACCESS', true);
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/Database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/security.php';

requireLogin();

$pageTitle = 'My Profile';
$db = Database::getInstance();
$userId = (int)($_SESSION['user_id'] ?? 0);
$userType = $_SESSION['user_type'] ?? 'admin';
$profileImageUrl = null;
$profileImageFilename = null;
$adminPhone = '';

if ($userType === 'staff') {
    $profile = $db->fetch(
        "SELECT id, staff_id, username, first_name, last_name, full_name, email, phone, address, role, profile_photo, status
         FROM staff
         WHERE id = ?
         LIMIT 1",
        [$userId]
    );

    if (!$profile) {
        setMessage('Profile not found.', 'error');
        redirect(routeUrl('dashboard'));
    }

    if (!empty($profile['profile_photo'])) {
        $profileImageFilename = $profile['profile_photo'];
        $profileImageUrl = UPLOAD_URL . $profile['profile_photo'];
    }
} else {
    $profile = $db->fetch(
        "SELECT id, username, full_name, email, role, status
         FROM users
         WHERE id = ?
         LIMIT 1",
        [$userId]
    );

    if (!$profile) {
        setMessage('Profile not found.', 'error');
        redirect(routeUrl('dashboard'));
    }

    $adminPhotoKey = 'user_profile_photo_admin_' . $userId;
    $photoRow = $db->fetch(
        "SELECT setting_value FROM system_settings WHERE setting_key = ? LIMIT 1",
        [$adminPhotoKey]
    );

    $adminPhoneKey = 'user_phone_admin_' . $userId;
    $phoneRow = $db->fetch(
        "SELECT setting_value FROM system_settings WHERE setting_key = ? LIMIT 1",
        [$adminPhoneKey]
    );

    if (!empty($photoRow['setting_value'])) {
        $profileImageFilename = $photoRow['setting_value'];
        $profileImageUrl = UPLOAD_URL . $photoRow['setting_value'];
    }

    if (!empty($phoneRow['setting_value'])) {
        $adminPhone = (string)$phoneRow['setting_value'];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_profile') {
    try {
        validateCSRF();

        $newPhotoFilename = $profileImageFilename;
        if (!empty($_FILES['profile_photo']['name'])) {
            $upload = uploadFile($_FILES['profile_photo'], ['jpg', 'jpeg', 'png', 'webp'], MAX_FILE_SIZE);
            if (!$upload['success']) {
                throw new Exception($upload['message']);
            }
            $newPhotoFilename = $upload['filename'];
        }

        if ($userType === 'staff') {
            $firstName = sanitizeTextValue($_POST['first_name'] ?? '');
            $lastName = sanitizeTextValue($_POST['last_name'] ?? '');
            $email = sanitizeTextValue($_POST['email'] ?? '');
            $phone = sanitizeTextValue($_POST['phone'] ?? '');
            $address = sanitizeTextValue($_POST['address'] ?? '');
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            if ($firstName === '' || $lastName === '' || $phone === '') {
                throw new Exception('First name, last name, and phone are required.');
            }

            if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Invalid email format.');
            }

            if ($email !== '') {
                $emailExists = $db->fetch(
                    "SELECT id FROM staff WHERE email = ? AND id <> ? LIMIT 1",
                    [$email, $userId]
                );
                if ($emailExists) {
                    throw new Exception('Email already exists.');
                }
            }

            if ($newPassword !== '' || $confirmPassword !== '') {
                if ($newPassword !== $confirmPassword) {
                    throw new Exception('Passwords do not match.');
                }

                if (strlen($newPassword) < 6) {
                    throw new Exception('Password must be at least 6 characters.');
                }
            }

            $staffSql = "UPDATE staff
                         SET first_name = ?,
                             last_name = ?,
                             email = ?,
                             phone = ?,
                             address = ?,
                             profile_photo = ?";
            $staffParams = [$firstName, $lastName, $email !== '' ? $email : null, $phone, $address !== '' ? $address : null, $newPhotoFilename];

            if ($newPassword !== '') {
                $staffSql .= ", password = ?";
                $staffParams[] = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => PASSWORD_COST]);
            }

            $staffSql .= " WHERE id = ?";
            $staffParams[] = $userId;

            $db->execute($staffSql, $staffParams);

            $_SESSION['full_name'] = trim($firstName . ' ' . $lastName);
        } else {
            $fullName = sanitizeTextValue($_POST['full_name'] ?? '');
            $email = sanitizeTextValue($_POST['email'] ?? '');
            $phone = sanitizeTextValue($_POST['phone'] ?? '');
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            if ($fullName === '') {
                throw new Exception('Full name is required.');
            }

            if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Invalid email format.');
            }

            if ($email !== '') {
                $emailExists = $db->fetch(
                    "SELECT id FROM users WHERE email = ? AND id <> ? LIMIT 1",
                    [$email, $userId]
                );
                if ($emailExists) {
                    throw new Exception('Email already exists.');
                }
            }

            if ($newPassword !== '' || $confirmPassword !== '') {
                if ($newPassword !== $confirmPassword) {
                    throw new Exception('Passwords do not match.');
                }

                if (strlen($newPassword) < 6) {
                    throw new Exception('Password must be at least 6 characters.');
                }
            }

            $userSql = "UPDATE users
                        SET full_name = ?,
                            email = ?";
            $userParams = [$fullName, $email !== '' ? $email : null];

            if ($newPassword !== '') {
                $userSql .= ", password = ?";
                $userParams[] = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => PASSWORD_COST]);
            }

            $userSql .= " WHERE id = ?";
            $userParams[] = $userId;

            $db->execute($userSql, $userParams);

            $adminPhotoKey = 'user_profile_photo_admin_' . $userId;
            $db->execute(
                "INSERT INTO system_settings (setting_key, setting_value, setting_type, description)
                 VALUES (?, ?, 'string', 'Admin profile photo filename')
                 ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), setting_type = VALUES(setting_type), description = VALUES(description)",
                [$adminPhotoKey, $newPhotoFilename]
            );

            $adminPhoneKey = 'user_phone_admin_' . $userId;
            $db->execute(
                "INSERT INTO system_settings (setting_key, setting_value, setting_type, description)
                 VALUES (?, ?, 'string', 'Admin profile phone number')
                 ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), setting_type = VALUES(setting_type), description = VALUES(description)",
                [$adminPhoneKey, $phone]
            );

            $_SESSION['full_name'] = $fullName;
        }

        logActivity($userId, 'update_profile', 'Updated own profile details');
        setMessage('Profile updated successfully.', 'success');
        redirect(routeUrl('profile'));
    } catch (Exception $e) {
        setMessage('Error: ' . $e->getMessage(), 'error');
    }
}

if ($userType === 'staff') {
    $profile = $db->fetch(
        "SELECT id, staff_id, username, first_name, last_name, full_name, email, phone, address, role, profile_photo, status
         FROM staff
         WHERE id = ?
         LIMIT 1",
        [$userId]
    );
    if (!empty($profile['profile_photo'])) {
        $profileImageFilename = $profile['profile_photo'];
        $profileImageUrl = UPLOAD_URL . $profile['profile_photo'];
    }
} else {
    $profile = $db->fetch(
        "SELECT id, username, full_name, email, role, status
         FROM users
         WHERE id = ?
         LIMIT 1",
        [$userId]
    );
    $adminPhotoKey = 'user_profile_photo_admin_' . $userId;
    $photoRow = $db->fetch("SELECT setting_value FROM system_settings WHERE setting_key = ? LIMIT 1", [$adminPhotoKey]);
    $adminPhoneKey = 'user_phone_admin_' . $userId;
    $phoneRow = $db->fetch("SELECT setting_value FROM system_settings WHERE setting_key = ? LIMIT 1", [$adminPhoneKey]);
    if (!empty($photoRow['setting_value'])) {
        $profileImageFilename = $photoRow['setting_value'];
        $profileImageUrl = UPLOAD_URL . $photoRow['setting_value'];
    }
    if (!empty($phoneRow['setting_value'])) {
        $adminPhone = (string)$phoneRow['setting_value'];
    }
}

include __DIR__ . '/../partials/header.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0">My Profile</h4>
            <p class="text-muted mb-0">Update your account details and profile photo.</p>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" enctype="multipart/form-data">
                <?php echo csrfField(); ?>
                <input type="hidden" name="action" value="save_profile">

                <div class="row g-3 align-items-start">
                    <div class="col-lg-3 col-md-4">
                        <div class="text-center">
                            <div class="mb-2" id="profilePhotoPreviewWrap">
                                <?php if ($profileImageUrl): ?>
                                    <img id="profilePhotoPreviewImg" src="<?php echo escape($profileImageUrl); ?>" alt="Profile Photo" style="width:120px;height:120px;border-radius:50%;object-fit:cover;border:1px solid #ddd;">
                                <?php else: ?>
                                    <div id="profilePhotoPreviewImg" style="width:120px;height:120px;border-radius:50%;background:#6c757d;color:#fff;display:flex;align-items:center;justify-content:center;font-size:34px;font-weight:700;margin:0 auto;">
                                        <?php echo escape(strtoupper(substr($profile['full_name'] ?? ($_SESSION['full_name'] ?? 'U'), 0, 2))); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="mb-2 text-muted small">
                                Login ID: <strong><?php echo escape($profile['username'] ?? $profile['staff_id'] ?? ''); ?></strong>
                            </div>
                            <label class="form-label">Profile Photo</label>
                            <input type="file" class="form-control" id="profilePhotoInput" name="profile_photo" accept="image/png,image/jpeg,image/webp">
                            <small class="text-muted">PNG, JPG, WEBP up to 5MB.</small>
                        </div>
                    </div>

                    <div class="col-lg-9 col-md-8">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Role</label>
                                <input type="text" class="form-control" value="<?php echo escape(getRoleLabel($profile['role'] ?? 'staff')); ?>" readonly>
                            </div>

                            <?php if ($userType === 'staff'): ?>
                                <div class="col-md-6">
                                    <label class="form-label">First Name</label>
                                    <input type="text" class="form-control" name="first_name" value="<?php echo escape($profile['first_name'] ?? ''); ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Last Name</label>
                                    <input type="text" class="form-control" name="last_name" value="<?php echo escape($profile['last_name'] ?? ''); ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control" name="email" value="<?php echo escape($profile['email'] ?? ''); ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Phone</label>
                                    <input type="text" class="form-control" name="phone" value="<?php echo escape($profile['phone'] ?? ''); ?>" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Address</label>
                                    <input type="text" class="form-control" name="address" value="<?php echo escape($profile['address'] ?? ''); ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">New Password</label>
                                    <input type="password" class="form-control" name="new_password" placeholder="Leave blank to keep current password">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Confirm Password</label>
                                    <input type="password" class="form-control" name="confirm_password" placeholder="Re-enter new password">
                                </div>
                            <?php else: ?>
                                <div class="col-md-6">
                                    <label class="form-label">Full Name</label>
                                    <input type="text" class="form-control" name="full_name" value="<?php echo escape($profile['full_name'] ?? ''); ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control" name="email" value="<?php echo escape($profile['email'] ?? ''); ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Phone</label>
                                    <input type="text" class="form-control" name="phone" value="<?php echo escape($adminPhone); ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">New Password</label>
                                    <input type="password" class="form-control" name="new_password" placeholder="Leave blank to keep current password">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Confirm Password</label>
                                    <input type="password" class="form-control" name="confirm_password" placeholder="Re-enter new password">
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="mt-3">
                            <button type="submit" class="btn btn-dark">
                                <i class="bi bi-save"></i> Save Profile
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('profilePhotoInput')?.addEventListener('change', function (e) {
    const file = e.target.files[0];
    const wrap = document.getElementById('profilePhotoPreviewWrap');
    if (!file || !wrap) return;

    if (file.size > 5242880) {
        alert('File size must not exceed 5MB');
        e.target.value = '';
        return;
    }

    const reader = new FileReader();
    reader.onload = function (ev) {
        wrap.innerHTML = '<img id="profilePhotoPreviewImg" src="' + ev.target.result + '" alt="Profile Photo" style="width:120px;height:120px;border-radius:50%;object-fit:cover;border:1px solid #ddd;">';
    };
    reader.readAsDataURL(file);
});
</script>

<?php include __DIR__ . '/../partials/footer.php'; ?>
