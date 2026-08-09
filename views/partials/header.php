<?php
if (!defined('APP_ACCESS')) {
    die('Direct access not permitted');
}
$brandingSettings = function_exists('getSystemBrandingSettings') ? getSystemBrandingSettings() : [];
$systemLogoUrl = $brandingSettings['system_logo_url'] ?? (APP_URL . '/assets/images/logo.png');
$activeShop = function_exists('getActiveShopOption') ? getActiveShopOption() : ['key' => 'autodok_main'];
$themeClass = (($activeShop['key'] ?? '') === 'autodok_prime') ? 'theme-prime' : 'theme-main';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' — ' . APP_NAME : APP_NAME; ?></title>
    <link rel="icon" type="image/png" href="<?php echo escape($systemLogoUrl); ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/css/style.css?v=<?php echo time(); ?>">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        window.APP_URL = <?php echo json_encode(APP_URL); ?>;
    </script>
</head>
<body class="<?php echo escape($themeClass); ?>">
<div class="dashboard-wrapper">

    <!-- Sidebar overlay for mobile -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <?php include __DIR__ . '/sidebar.php'; ?>

    <div class="main-content">

        <!-- Top bar -->
        <div class="topbar">
            <button class="hamburger-btn" id="sidebarToggle">
                <i class="bi bi-list"></i>
            </button>
            <div class="topbar-title d-flex align-items-center" style="gap:8px;">
                <span><?php echo isset($pageTitle) ? $pageTitle : 'Dashboard'; ?></span>
            </div>
            <div class="topbar-actions">
                <div class="bell-wrap" id="bellWrap" title="Notifications">
                    <i class="bi bi-bell-fill"></i>
                    <span class="bell-dot" id="bellDot"></span>
                </div>
                <a href="<?php echo APP_URL; ?>/views/profile/index.php" class="topbar-user topbar-user-link" title="My Profile">
                    <?php
                    // Show profile photo if staff user has one
                    $profilePhoto = null;
                    if (($_SESSION['user_type'] ?? '') === 'staff' && !empty($_SESSION['user_id'])) {
                        try {
                            $staffRow = Database::getInstance()->fetch(
                                "SELECT profile_photo FROM staff WHERE id = ? LIMIT 1",
                                [$_SESSION['user_id']]
                            );
                            if (!empty($staffRow['profile_photo'])) {
                                $profilePhoto = UPLOAD_URL . $staffRow['profile_photo'];
                            }
                        } catch (Exception $e) {}
                    } elseif (!empty($_SESSION['user_id'])) {
                        try {
                            $adminSettingKey = 'user_profile_photo_admin_' . (int)$_SESSION['user_id'];
                            $adminPhotoRow = Database::getInstance()->fetch(
                                "SELECT setting_value FROM system_settings WHERE setting_key = ? LIMIT 1",
                                [$adminSettingKey]
                            );
                            if (!empty($adminPhotoRow['setting_value'])) {
                                $profilePhoto = UPLOAD_URL . $adminPhotoRow['setting_value'];
                            }
                        } catch (Exception $e) {}
                    }
                    ?>
                    <div class="topbar-avatar" <?php if ($profilePhoto): ?>style="padding:0;overflow:hidden;"<?php endif; ?>>
                        <?php if ($profilePhoto): ?>
                            <img src="<?php echo escape($profilePhoto); ?>"
                                 alt="Profile"
                                 style="width:100%;height:100%;object-fit:cover;border-radius:50%;">
                        <?php else: ?>
                            <?php echo strtoupper(substr($_SESSION['full_name'] ?? 'U', 0, 2)); ?>
                        <?php endif; ?>
                    </div>
                    <span class="topbar-name"><?php echo escape($_SESSION['full_name'] ?? 'User'); ?></span>
                </a>
            </div>
        </div>

        <!-- Page body -->
        <div class="page-body">

            <?php if (function_exists('hasMessage') && hasMessage()):
                $msg = getMessage();
                $type = ($msg['type'] === 'error') ? 'danger' : $msg['type'];
            ?>
            <div id="flashToastMessage"
                 data-message="<?php echo escape($msg['message']); ?>"
                 data-type="<?php echo escape($type); ?>"
                 style="display:none;"></div>
            <?php endif; ?>
