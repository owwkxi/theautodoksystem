<?php
define('APP_ACCESS', true);
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/Database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/security.php';

requireLogin();
requireAnyRole(['admin', 'cashier']);

$pageTitle = 'Print Template Settings';
$hideTopbarLogo = true;
$current = getPrintTemplateSettings();
$activeShop = getActiveShopOption();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_print_template') {
    try {
        validateCSRF();

        $payload = [
            'company_name' => sanitizeTextValue($_POST['company_name'] ?? '', 'THE AUTODOK'),
            'company_subtitle' => sanitizeTextValue($_POST['company_subtitle'] ?? '', 'Automotive Care Services'),
            'contact_line' => sanitizeTextValue($_POST['contact_line'] ?? '', 'Tel: (02) XXX-XXXX | autodok@email.com'),
            'address_line' => sanitizeTextValue($_POST['address_line'] ?? '', ''),
            'tax_info' => sanitizeTextValue($_POST['tax_info'] ?? '', ''),
            'logo_url' => sanitizeTextValue($_POST['logo_url'] ?? '', APP_URL . '/assets/images/logo.png'),
            'footer_note' => sanitizeTextValue($_POST['footer_note'] ?? '', 'Thank you for choosing The Autodok - Automotive Care Services'),
            'terms_conditions' => sanitizeTextValue($_POST['terms_conditions'] ?? '', ''),
            // Keep existing templates since advanced HTML editors are hidden from UI.
            'header_template' => trim((string)($current['header_template'] ?? '')),
            'footer_template' => trim((string)($current['footer_template'] ?? '')),
        ];

        if (!empty($_FILES['logo_image']['name'])) {
            $upload = uploadImage($_FILES['logo_image'], MAX_FILE_SIZE);
            if ($upload['success']) {
                $payload['logo_url'] = $upload['url'];
            } else {
                throw new Exception($upload['message']);
            }
        }

        if (!savePrintTemplateSettings($payload)) {
            throw new Exception('Failed to save print template settings.');
        }

        $actorName = sanitize($_SESSION['full_name'] ?? $_SESSION['username'] ?? 'Staff');
        $shopName = sanitize($activeShop['name'] ?? APP_NAME);
        logActivity((int)($_SESSION['user_id'] ?? 0), 'update_print_template', 'Updated print template settings for ' . $shopName);
        notifyRoles(
            'system',
            'Print Template Updated',
            buildNotificationMessageTemplate($actorName, 'updated print template for', $shopName),
            ['admin', 'cashier'],
            [
                'exclude_user_id' => (int)($_SESSION['user_id'] ?? 0),
                'reference_type' => 'settings',
            ]
        );

        $current = array_merge($current, $payload);
        setMessage('Print preview template updated successfully.', 'success');
        redirect(routeUrl('settings_print_template'));
    } catch (Exception $e) {
        setMessage('Error: ' . $e->getMessage(), 'error');
        $current = array_merge($current, $payload ?? []);
    }
}

$sampleVars = [
    '{{logo_url}}' => $current['logo_url'],
    '{{company_name}}' => $current['company_name'],
    '{{company_subtitle}}' => $current['company_subtitle'],
    '{{contact_line}}' => $current['contact_line'],
    '{{address_line}}' => $current['address_line'] ?? '',
    '{{tax_info}}' => $current['tax_info'] ?? '',
    '{{document_title}}' => 'JOB ORDER',
    '{{document_number}}' => 'JO001',
    '{{document_date}}' => date('F d, Y'),
    '{{footer_note}}' => $current['footer_note'],
];

$previewHeader = strtr($current['header_template'], $sampleVars);
$previewFooter = strtr($current['footer_template'], $sampleVars);

include __DIR__ . '/../partials/header.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0">Print Template Settings</h4>
            <p class="text-muted mb-0">Edit image/logo and print preview template used by Job Order and Estimate printouts.</p>
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
                <input type="hidden" name="action" value="save_print_template">

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Company Name</label>
                        <input type="text" class="form-control" name="company_name" value="<?php echo escape($current['company_name']); ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Company Subtitle</label>
                        <input type="text" class="form-control" name="company_subtitle" value="<?php echo escape($current['company_subtitle']); ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Contact Line</label>
                        <input type="text" class="form-control" name="contact_line" value="<?php echo escape($current['contact_line']); ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Address</label>
                        <input type="text" class="form-control" name="address_line" value="<?php echo escape($current['address_line'] ?? ''); ?>" placeholder="e.g. 123 Main St, City, Province">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Tax Info / TIN</label>
                        <input type="text" class="form-control" name="tax_info" value="<?php echo escape($current['tax_info'] ?? ''); ?>" placeholder="e.g. Non VAT Reg. ; TIN 000-000-000-00000">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Logo URL</label>
                        <input type="text" class="form-control" name="logo_url" value="<?php echo escape($current['logo_url']); ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Upload Logo Image</label>
                        <input type="file" class="form-control" id="logoImageInput" name="logo_image" accept="image/*">
                        <small class="text-muted">Optional: Upload overrides the logo URL above.</small>
                        <div class="mt-2" id="logoImagePreviewWrap">
                            <img id="logoImagePreview" src="<?php echo escape($current['logo_url']); ?>" alt="Logo Preview" data-fallback-src="<?php echo APP_URL; ?>/assets/images/logo.png" style="width:60px;height:60px;object-fit:contain;border:1px solid #ddd;border-radius:6px;padding:4px;background:#fff;" onerror="this.onerror=null; this.src=this.getAttribute('data-fallback-src') || '<?php echo APP_URL; ?>/assets/images/logo.png'; this.style.display='block';">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Footer Note</label>
                        <input type="text" class="form-control" name="footer_note" value="<?php echo escape($current['footer_note']); ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Terms & Conditions <small class="text-muted">(shown in print preview)</small></label>
                        <textarea class="form-control" name="terms_conditions" rows="3" placeholder="Enter terms and conditions..."><?php echo escape($current['terms_conditions'] ?? ''); ?></textarea>
                    </div>
                </div>

                <div class="mt-3 d-flex gap-2">
                    <button type="submit" class="btn btn-dark">
                        <i class="bi bi-save"></i> Save Template
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header">
            <h6 class="mb-0">Template Preview (Sample)</h6>
        </div>
        <div class="card-body" style="background:#fff;">
            <div style="font-family:Arial,sans-serif;font-size:9.5pt;color:#000;line-height:1.4;">
                <?php echo $previewHeader; ?>
                <table style="width:100%;border-collapse:collapse;margin-bottom:8px;">
                    <tr>
                        <td style="padding:6px;border:1px solid #ddd;font-weight:600;">Sample body content...</td>
                    </tr>
                </table>
                <?php echo $previewFooter; ?>
            </div>
        </div>
    </div>
</div>

<script>
function setPrintTemplateLogoPreview(preview, src) {
    if (!preview) return;

    const fallbackSrc = preview.getAttribute('data-fallback-src') || '<?php echo APP_URL; ?>/assets/images/logo.png';
    const nextSrc = src && String(src).trim() ? src : fallbackSrc;

    preview.src = nextSrc;
    preview.style.display = 'block';
}

document.getElementById('logoImageInput')?.addEventListener('change', function (e) {
    const file = e.target.files[0];
    const preview = document.getElementById('logoImagePreview');
    if (!file || !preview) return;

    const reader = new FileReader();
    reader.onload = function (ev) {
        setPrintTemplateLogoPreview(preview, ev.target.result);
    };
    reader.readAsDataURL(file);
});

document.querySelector('input[name="logo_url"]')?.addEventListener('input', function (e) {
    const preview = document.getElementById('logoImagePreview');
    if (!preview) return;
    setPrintTemplateLogoPreview(preview, e.target.value);
});
</script>

<?php include __DIR__ . '/../partials/footer.php'; ?>
