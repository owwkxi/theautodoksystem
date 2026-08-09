<?php
define('APP_ACCESS', true);
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/Database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/security.php';

requireLogin();
requireAnyRole(['admin', 'cashier']);

$pageTitle = 'System Logo Settings';
$hideTopbarLogo = true;
$branding = getSystemBrandingSettings();
$activeShop = getActiveShopOption();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_system_logo') {
    try {
        validateCSRF();

        $payload = [
            'system_logo_url' => $_POST['system_logo_url'] ?? '',
        ];

        if (!empty($_FILES['system_logo_image']['name'])) {
            $upload = uploadImage($_FILES['system_logo_image'], MAX_FILE_SIZE);
            if ($upload['success']) {
                $payload['system_logo_url'] = $upload['url'];
            } else {
                throw new Exception($upload['message']);
            }
        }

        if (!saveSystemBrandingSettings($payload)) {
            throw new Exception('Failed to save system logo settings.');
        }

        setMessage('System logo updated successfully.', 'success');
        redirect(APP_URL . '/views/settings/system_logo.php');
    } catch (Exception $e) {
        setMessage('Error: ' . $e->getMessage(), 'error');
        $branding = array_merge($branding, $payload ?? []);
    }
}

include __DIR__ . '/../partials/header.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0">System Logo Settings</h4>
            <p class="text-muted mb-0">Manage logo used in the sidebar, top bar, login page, and browser tab icon.</p>
            <p class="mb-0 mt-1"><span class="badge bg-secondary-subtle text-dark border">Shop: <?php echo escape($activeShop['name'] ?? APP_NAME); ?></span></p>
        </div>
        <a href="<?php echo APP_URL; ?>/views/settings/index.php" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Back to Settings
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" enctype="multipart/form-data">
                <?php echo csrfField(); ?>
                <input type="hidden" name="action" value="save_system_logo">

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">System Logo URL</label>
                        <input type="text" class="form-control" name="system_logo_url" value="<?php echo escape($branding['system_logo_url'] ?? (APP_URL . '/assets/images/logo.png')); ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Upload System Logo Image</label>
                        <input type="file" class="form-control" name="system_logo_image" accept="image/*">
                        <small class="text-muted">Optional: Upload overrides the URL above.</small>
                    </div>
                </div>

                <div class="mt-3 d-flex gap-2">
                    <button type="submit" class="btn btn-dark">
                        <i class="bi bi-save"></i> Save System Logo
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header">
            <h6 class="mb-0">Preview</h6>
        </div>
        <div class="card-body d-flex align-items-center" style="gap:12px;">
            <img src="<?php echo escape($branding['system_logo_url'] ?? (APP_URL . '/assets/images/logo.png')); ?>" alt="System Logo Preview" style="width:52px;height:52px;object-fit:contain;border:1px solid #ddd;border-radius:8px;padding:4px;background:#fff;" onerror="this.onerror=null;this.src='<?php echo APP_URL; ?>/assets/images/logo.png';">
            <div class="text-muted">This logo appears in sidebar, top bar, login page, and favicon.</div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>
