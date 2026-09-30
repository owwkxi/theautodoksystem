<?php
define('APP_ACCESS', true);
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/Database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/security.php';

requireLogin();
requireRole('admin');

$pageTitle = 'Deleted Archive';
$activeShop = getActiveShopOption();
$activeShopName = (string)($activeShop['name'] ?? APP_NAME);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'restore_archive_record') {
    try {
        validateCSRF();
        $archiveId = trim((string)($_POST['archive_id'] ?? ''));
        if ($archiveId === '') {
            throw new Exception('Archive ID is required.');
        }

        $restoreResult = restoreDeletedArchiveRecord($archiveId);
        if (empty($restoreResult['success'])) {
            throw new Exception((string)($restoreResult['message'] ?? 'Failed to restore record.'));
        }

        logActivity((int)($_SESSION['user_id'] ?? 0), 'restore_deleted_archive_record', 'Restored archived record #' . $archiveId);
        setMessage('Record restored successfully.', 'success');
    } catch (Exception $e) {
        setMessage('Error: ' . $e->getMessage(), 'error');
    }

    redirect(routeUrl('settings_deleted_archive'));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'clear_trash') {
    try {
        validateCSRF();
        $clearResult = clearDeletedArchiveEntries();
        if (!$clearResult) {
            throw new Exception('Failed to clear trash.');
        }
        setMessage('Trash cleared successfully.', 'success');
    } catch (Exception $e) {
        setMessage('Error: ' . $e->getMessage(), 'error');
    }

    redirect(routeUrl('settings_deleted_archive'));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete_archive_record') {
    try {
        validateCSRF();
        $archiveId = trim((string)($_POST['archive_id'] ?? ''));
        if ($archiveId === '') {
            throw new Exception('Archive ID is required.');
        }

        if (!deleteArchivedRecordById($archiveId)) {
            throw new Exception('Failed to permanently delete archive record.');
        }

        logActivity((int)($_SESSION['user_id'] ?? 0), 'delete_deleted_archive_record', 'Permanently deleted archived record #' . $archiveId);
        setMessage('Archived record deleted permanently.', 'success');
    } catch (Exception $e) {
        setMessage('Error: ' . $e->getMessage(), 'error');
    }

    redirect(routeUrl('settings_deleted_archive'));
}

$entries = getDeletedArchiveEntries();
$search = trim((string)($_GET['q'] ?? ''));
$typeFilter = trim((string)($_GET['type'] ?? ''));

if ($typeFilter !== '') {
    $entries = array_values(array_filter($entries, static function ($entry) use ($typeFilter) {
        return (string)($entry['entity_type'] ?? '') === $typeFilter;
    }));
}

if ($search !== '') {
    $entries = array_values(array_filter($entries, static function ($entry) use ($search) {
        $needle = strtolower($search);
        $entityType = strtolower((string)($entry['entity_type'] ?? ''));
        $deletedBy = strtolower((string)($entry['deleted_by']['name'] ?? ''));
        $payloadText = strtolower(json_encode($entry['payload'] ?? []));
        return strpos($entityType, $needle) !== false
            || strpos($deletedBy, $needle) !== false
            || strpos($payloadText, $needle) !== false;
    }));
}

usort($entries, static function ($a, $b) {
    $aTime = strtotime((string)($a['deleted_at'] ?? '')) ?: 0;
    $bTime = strtotime((string)($b['deleted_at'] ?? '')) ?: 0;
    return $bTime <=> $aTime;
});

$types = [];
foreach ($entries as $entry) {
    $type = (string)($entry['entity_type'] ?? '');
    if ($type !== '') {
        $types[$type] = true;
    }
}
$typeOptions = array_keys($types);
sort($typeOptions);

include __DIR__ . '/../partials/header.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0">Deleted Archive</h4>
            <p class="text-muted mb-0 small">
                Branch: <?php echo escape($activeShopName); ?>. Deleted records are kept for <?php echo (int)getDeletedArchiveRetentionDays(); ?> days.
            </p>
        </div>
        <a href="<?php echo routeUrl('settings'); ?>" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Back to Settings
        </a>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-2 align-items-end">
                <div class="col-md-5">
                    <label class="form-label small mb-1">Search</label>
                    <input type="text" class="form-control form-control-sm" name="q" form="archiveFilterForm" value="<?php echo escape($search); ?>" placeholder="Search in payload, type, or deleted by">
                </div>
                <div class="col-md-3">
                    <label class="form-label small mb-1">Type</label>
                    <select class="form-select form-select-sm" name="type" form="archiveFilterForm">
                        <option value="">All types</option>
                        <?php foreach ($typeOptions as $typeOption): ?>
                            <option value="<?php echo escape($typeOption); ?>" <?php echo $typeFilter === $typeOption ? 'selected' : ''; ?>>
                                <?php echo escape(getArchivedEntityDisplayName($typeOption)); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <form id="archiveFilterForm" method="GET" action="<?php echo routeUrl('settings_deleted_archive'); ?>"></form>
                    <button type="submit" class="btn btn-dark btn-sm" form="archiveFilterForm"><i class="bi bi-search"></i> Filter</button>
                    <a href="<?php echo routeUrl('settings_deleted_archive'); ?>" class="btn btn-outline-secondary btn-sm">Clear</a>
                    <form method="POST" class="ms-auto">
                        <?php echo csrfField(); ?>
                        <input type="hidden" name="action" value="clear_trash">
                        <button type="submit" class="btn btn-outline-danger btn-sm">
                            <i class="bi bi-trash3"></i> Clear Trash
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive table-responsive-actions">
                <table class="table table-striped mb-0">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Deleted At</th>
                            <th>Deleted By</th>
                            <th>Auto Purge At</th>
                            <th>Summary</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($entries)): ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">No archived deleted records.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($entries as $entry): ?>
                                <?php
                                    $entityType = (string)($entry['entity_type'] ?? '');
                                    $payload = is_array($entry['payload'] ?? null) ? $entry['payload'] : [];
                                    $summary = '';
                                    if ($entityType === 'job_order' && isset($payload['job_order']['job_order_number'])) {
                                        $summary = 'JOID: ' . (string)$payload['job_order']['job_order_number'];
                                    } elseif (in_array($entityType, ['manual_income', 'report_expense'], true) && isset($payload['description'])) {
                                        $summary = (string)$payload['description'];
                                    } elseif (isset($payload['title'])) {
                                        $summary = (string)$payload['title'];
                                    } elseif (isset($payload['description'])) {
                                        $summary = (string)$payload['description'];
                                    } elseif (isset($payload['full_name'])) {
                                        $summary = (string)$payload['full_name'];
                                    } elseif (isset($payload['job_order_number'])) {
                                        $summary = 'JO #' . (string)$payload['job_order_number'];
                                    } elseif (isset($payload['estimate_number'])) {
                                        $summary = 'JEID: ' . (string)$payload['estimate_number'];
                                    } elseif (isset($payload['product_name'])) {
                                        $summary = (string)$payload['product_name'];
                                    } elseif (isset($payload['bundle']['bundle_name'])) {
                                        $summary = (string)$payload['bundle']['bundle_name'];
                                    } elseif (isset($payload['service_name'])) {
                                        $summary = (string)$payload['service_name'];
                                    }
                                    if ($summary === '') {
                                        $summary = 'Archived ' . strtolower(getArchivedEntityDisplayName($entityType));
                                    }
                                ?>
                                <tr>
                                    <td><?php echo escape(getArchivedEntityDisplayName($entityType)); ?></td>
                                    <td><?php echo escape(formatDateTime((string)($entry['deleted_at'] ?? ''))); ?></td>
                                    <td><?php echo escape((string)($entry['deleted_by']['name'] ?? 'System')); ?></td>
                                    <td><?php echo escape(formatDateTime((string)($entry['expires_at'] ?? ''))); ?></td>
                                    <td class="text-truncate" style="max-width:380px;"><?php echo escape($summary); ?></td>
                                    <td class="text-end">
                                        <form method="POST" class="d-inline-block">
                                            <?php echo csrfField(); ?>
                                            <input type="hidden" name="action" value="restore_archive_record">
                                            <input type="hidden" name="archive_id" value="<?php echo escape((string)($entry['id'] ?? '')); ?>">
                                            <button type="submit" class="btn btn-sm btn-success">
                                                <i class="bi bi-arrow-counterclockwise"></i> Restore
                                            </button>
                                        </form>
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-outline-danger ms-1"
                                            data-bs-toggle="modal"
                                            data-bs-target="#deleteArchiveModal"
                                            data-archive-id="<?php echo escape((string)($entry['id'] ?? '')); ?>"
                                            data-archive-summary="<?php echo escape($summary); ?>"
                                        >
                                            <i class="bi bi-x-circle"></i> Delete Forever
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteArchiveModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light">
                <h5 class="modal-title"><i class="bi bi-exclamation-triangle text-danger me-2"></i>Delete Forever</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-2">This will permanently delete the archived record and cannot be undone.</p>
                <div class="small text-muted">Record: <span id="archiveDeleteSummary">-</span></div>
                <form id="archiveDeleteForm" method="POST" class="d-none">
                    <?php echo csrfField(); ?>
                    <input type="hidden" name="action" value="delete_archive_record">
                    <input type="hidden" name="archive_id" id="archiveDeleteId" value="">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="archiveDeleteForm" class="btn btn-danger">
                    <i class="bi bi-trash3"></i> Delete Forever
                </button>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    const modalEl = document.getElementById('deleteArchiveModal');
    if (!modalEl) return;
    modalEl.addEventListener('show.bs.modal', function (event) {
        const trigger = event.relatedTarget;
        if (!trigger) return;
        const archiveId = trigger.getAttribute('data-archive-id') || '';
        const archiveSummary = trigger.getAttribute('data-archive-summary') || '';
        const idInput = document.getElementById('archiveDeleteId');
        const summaryEl = document.getElementById('archiveDeleteSummary');
        if (idInput) idInput.value = archiveId;
        if (summaryEl) summaryEl.textContent = archiveSummary || archiveId;
    });
})();
</script>

<?php include __DIR__ . '/../partials/footer.php'; ?>
