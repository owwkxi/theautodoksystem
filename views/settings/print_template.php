<?php
define('APP_ACCESS', true);
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/Database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/security.php';

requireLogin();
requireAnyRole(['admin']);

$pageTitle = 'Print Template Settings';
$current = getPrintTemplateSettings();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_print_template') {
    try {
        validateCSRF();

        $payload = [
            'company_name' => $_POST['company_name'] ?? '',
            'company_subtitle' => $_POST['company_subtitle'] ?? '',
            'contact_line' => $_POST['contact_line'] ?? '',
            'logo_url' => $_POST['logo_url'] ?? '',
            'footer_note' => $_POST['footer_note'] ?? '',
            // Keep existing templates since advanced HTML editors are hidden from UI.
            'header_template' => $current['header_template'] ?? '',
            'footer_template' => $current['footer_template'] ?? '',
        ];

        if (!empty($_FILES['logo_image']['name'])) {
            $upload = uploadFile($_FILES['logo_image'], ['jpg', 'jpeg', 'png', 'webp', 'gif'], MAX_FILE_SIZE);
            if ($upload['success']) {
                $payload['logo_url'] = $upload['url'];
            } else {
                throw new Exception($upload['message']);
            }
        }

        if (!savePrintTemplateSettings($payload)) {
            throw new Exception('Failed to save print template settings.');
        }

        setMessage('Print preview template updated successfully.', 'success');
        redirect(APP_URL . '/views/settings/print_template.php');
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
        </div>
        <a href="<?php echo APP_URL; ?>/views/settings/index.php" class="btn btn-outline-secondary btn-sm">
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
                        <label class="form-label">Logo URL</label>
                        <input type="text" class="form-control" name="logo_url" value="<?php echo escape($current['logo_url']); ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Upload Logo Image</label>
                        <input type="file" class="form-control" name="logo_image" accept="image/*">
                        <small class="text-muted">Optional: Upload overrides the logo URL above.</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Footer Note</label>
                        <input type="text" class="form-control" name="footer_note" value="<?php echo escape($current['footer_note']); ?>">
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

<?php include __DIR__ . '/../partials/footer.php'; ?>
