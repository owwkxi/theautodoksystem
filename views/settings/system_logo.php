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
            'system_logo_url' => sanitizeTextValue($_POST['system_logo_url'] ?? '', APP_URL . '/assets/images/logo.png'),
            'sidebar_brand_name' => sanitizeTextValue($_POST['sidebar_brand_name'] ?? '', $activeShop['name'] ?? APP_NAME),
            'sidebar_brand_subtitle' => sanitizeTextValue(
                $_POST['sidebar_brand_subtitle'] ?? '',
                (($activeShop['key'] ?? '') === 'autodok_prime') ? 'Prime Automotive Care Services' : 'Automotive Care Services'
            ),
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

        $actorName = sanitize($_SESSION['full_name'] ?? $_SESSION['username'] ?? 'Staff');
        $shopName = sanitize($activeShop['name'] ?? APP_NAME);
        logActivity((int)($_SESSION['user_id'] ?? 0), 'update_system_logo', 'Updated system logo settings for ' . $shopName);
        notifyRoles(
            'system',
            'System Logo Updated',
            buildNotificationMessageTemplate($actorName, 'updated system logo for', $shopName),
            ['admin', 'cashier'],
            [
                'exclude_user_id' => (int)($_SESSION['user_id'] ?? 0),
                'reference_type' => 'settings',
            ]
        );

        setMessage('System logo updated successfully.', 'success');
        redirect(routeUrl('settings_system_logo'));
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
        <a href="<?php echo routeUrl('settings'); ?>" class="btn btn-outline-secondary btn-sm">
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
                        <input type="text" class="form-control" id="systemLogoUrlInput" name="system_logo_url" value="<?php echo escape($branding['system_logo_url'] ?? (APP_URL . '/assets/images/logo.png')); ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Upload System Logo Image</label>
                        <input type="file" class="form-control" id="systemLogoImageInput" name="system_logo_image" accept="image/*">
                        <small class="text-muted">Optional: Upload overrides the URL above.</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Sidebar Brand Name</label>
                        <input type="text" class="form-control" id="sidebarBrandNameInput" name="sidebar_brand_name" maxlength="80" value="<?php echo escape($branding['sidebar_brand_name'] ?? ($activeShop['name'] ?? APP_NAME)); ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Sidebar Brand Subtitle</label>
                        <input type="text" class="form-control" id="sidebarBrandSubtitleInput" name="sidebar_brand_subtitle" maxlength="120" value="<?php echo escape($branding['sidebar_brand_subtitle'] ?? ((($activeShop['key'] ?? '') === 'autodok_prime') ? 'Prime Automotive Care Services' : 'Automotive Care Services')); ?>">
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
            <img id="systemLogoPreview" src="<?php echo escape($branding['system_logo_url'] ?? (APP_URL . '/assets/images/logo.png')); ?>" alt="System Logo Preview" data-fallback-src="<?php echo APP_URL; ?>/assets/images/logo.png" style="width:52px;height:52px;object-fit:contain;border:1px solid #ddd;border-radius:8px;padding:4px;background:#fff;" onerror="this.onerror=null; this.src=this.getAttribute('data-fallback-src') || '<?php echo APP_URL; ?>/assets/images/logo.png'; this.style.display='block';">
            <div>
                <div id="sidebarBrandNamePreview" class="fw-semibold"><?php echo escape($branding['sidebar_brand_name'] ?? ($activeShop['name'] ?? APP_NAME)); ?></div>
                <div id="sidebarBrandSubtitlePreview" class="text-muted small"><?php echo escape($branding['sidebar_brand_subtitle'] ?? ((($activeShop['key'] ?? '') === 'autodok_prime') ? 'Prime Automotive Care Services' : 'Automotive Care Services')); ?></div>
                <div class="text-muted small mt-1">These appear beside the sidebar logo for this shop.</div>
            </div>
        </div>
    </div>
</div>

<script>
function setSystemLogoPreview(preview, src) {
    if (!preview) return;

    const fallbackSrc = preview.getAttribute('data-fallback-src') || '<?php echo APP_URL; ?>/assets/images/logo.png';
    const nextSrc = src && String(src).trim() ? src : fallbackSrc;

    preview.src = nextSrc;
    preview.style.display = 'block';
}

document.getElementById('systemLogoImageInput')?.addEventListener('change', function (e) {
    const file = e.target.files[0];
    const preview = document.getElementById('systemLogoPreview');
    if (!file || !preview) return;

    const reader = new FileReader();
    reader.onload = function (ev) {
        setSystemLogoPreview(preview, ev.target.result);
    };
    reader.readAsDataURL(file);
});

document.getElementById('systemLogoUrlInput')?.addEventListener('input', function (e) {
    const preview = document.getElementById('systemLogoPreview');
    if (!preview) return;
    setSystemLogoPreview(preview, e.target.value);
});

document.getElementById('sidebarBrandNameInput')?.addEventListener('input', function (e) {
    const preview = document.getElementById('sidebarBrandNamePreview');
    if (!preview) return;
    preview.textContent = e.target.value.trim() || '<?php echo escape($activeShop['name'] ?? APP_NAME); ?>';
});

document.getElementById('sidebarBrandSubtitleInput')?.addEventListener('input', function (e) {
    const preview = document.getElementById('sidebarBrandSubtitlePreview');
    if (!preview) return;
    preview.textContent = e.target.value.trim() || '<?php echo escape((($activeShop['key'] ?? '') === 'autodok_prime') ? 'Prime Automotive Care Services' : 'Automotive Care Services'); ?>';
});
</script>

<?php include __DIR__ . '/../partials/footer.php'; ?>
