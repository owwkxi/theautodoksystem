<?php
define('APP_ACCESS', true);
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/Database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/security.php';

requireLogin();
requireAnyRole(['admin', 'cashier']);

$activeShop = getActiveShopOption();
$settings = getCompletionEmailSettings($activeShop['key'] ?? null);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        validateCSRF();
        if (!saveCompletionEmailSettings([
            'subject' => $_POST['subject'] ?? '',
            'message' => $_POST['message'] ?? '',
            'company_contact' => $_POST['company_contact'] ?? '',
            'company_contact_number' => $_POST['company_contact_number'] ?? '',
            'company_email' => $_POST['company_email'] ?? '',
            'company_address' => $_POST['company_address'] ?? '',
        ], $activeShop['key'] ?? null)) {
            throw new Exception('Unable to save the email template.');
        }
        setMessage('Completion email template saved successfully.', 'success');
        redirect(routeUrl('settings_completion_email'));
    } catch (Exception $e) {
        setMessage('Error: ' . $e->getMessage(), 'error');
    }
}

$pageTitle = 'Completion Email Settings';
include __DIR__ . '/../partials/header.php';
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0">Completion Email Template</h4>
            <p class="text-muted mb-0 small">This email is sent automatically when a job order changes to Completed.</p>
        </div>
        <a href="<?php echo routeUrl('settings'); ?>" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Back to Settings
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST">
                <?php echo csrfField(); ?>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Subject</label>
                    <input type="text" class="form-control" name="subject" required
                           value="<?php echo escape($settings['subject']); ?>">
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Company Contact</label>
                        <input type="text" class="form-control" name="company_contact" value="<?php echo escape($settings['company_contact'] ?? ''); ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Contact Number</label>
                        <input type="text" class="form-control" name="company_contact_number" value="<?php echo escape($settings['company_contact_number'] ?? ''); ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Email</label>
                        <input type="email" class="form-control" name="company_email" value="<?php echo escape($settings['company_email'] ?? ''); ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Address</label>
                        <input type="text" class="form-control" name="company_address" value="<?php echo escape($settings['company_address'] ?? ''); ?>">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Message</label>
                    <div class="btn-toolbar mb-2" role="toolbar" aria-label="Email formatting">
                        <div class="btn-group btn-group-sm me-2" role="group">
                           <button type="button" class="btn btn-outline-secondary" data-email-command="bold" title="Bold"><i class="bi bi-type-bold"></i></button>
                           <button type="button" class="btn btn-outline-secondary" data-email-command="italic" title="Italic"><i class="bi bi-type-italic"></i></button>
                           <button type="button" class="btn btn-outline-secondary" data-email-command="underline" title="Underline"><i class="bi bi-type-underline"></i></button>
                        </div>
                        <label class="btn btn-outline-secondary btn-sm mb-0" title="Text color">
                           <i class="bi bi-palette"></i>
                           <input type="color" id="emailTextColor" value="#212529" class="visually-hidden">
                        </label>
                    </div>
                    <div id="emailMessageEditor" class="form-control" contenteditable="true" role="textbox" aria-multiline="true"
                         style="min-height:280px;white-space:normal;"><?php echo appendCompletionEmailCompanyDetails(sanitizeCompletionEmailHtml($settings['message']), getCompletionEmailCompanyDetails($settings)); ?></div>
                    <textarea name="message" id="emailMessageValue" class="d-none" required></textarea>
                </div>
                <button type="submit" class="btn btn-dark">
                    <i class="bi bi-save"></i> Save Template
                </button>
            </form>
        </div>
    </div>
</div>
<script>
(() => {
    const editor = document.getElementById('emailMessageEditor');
    const value = document.getElementById('emailMessageValue');
    if (!editor || !value) return;
    let savedSelection = null;

    const saveSelection = () => {
        const selection = window.getSelection();
        if (!selection || selection.rangeCount === 0) return;
        const range = selection.getRangeAt(0);
        if (!range.collapsed && editor.contains(range.commonAncestorContainer)) {
            savedSelection = range.cloneRange();
        }
    };

    const restoreSelection = () => {
        if (!savedSelection) return;
        const selection = window.getSelection();
        selection.removeAllRanges();
        selection.addRange(savedSelection);
    };

    const applyTextColor = (color) => {
        restoreSelection();
        if (!savedSelection || savedSelection.collapsed) return;

        const range = savedSelection.cloneRange();
        const wrapper = document.createElement('span');
        wrapper.style.color = color;
        wrapper.appendChild(range.extractContents());
        range.insertNode(wrapper);
        savedSelection = null;
        syncMessage();
    };

    const syncMessage = () => {
        value.value = editor.innerHTML.trim();
    };

    document.querySelectorAll('[data-email-command]').forEach((button) => {
        button.addEventListener('click', () => {
            editor.focus();
            document.execCommand(button.dataset.emailCommand, false);
            syncMessage();
        });
    });

    const colorPicker = document.getElementById('emailTextColor');
    colorPicker?.addEventListener('mousedown', saveSelection);
    colorPicker?.addEventListener('change', (event) => {
        applyTextColor(event.target.value);
    });

    editor.addEventListener('mouseup', saveSelection);
    editor.addEventListener('keyup', saveSelection);
    document.addEventListener('selectionchange', saveSelection);
    editor.addEventListener('input', () => {
        saveSelection();
        syncMessage();
    });
    editor.closest('form')?.addEventListener('submit', syncMessage);
    syncMessage();
})();
</script>
<?php include __DIR__ . '/../partials/footer.php'; ?>
