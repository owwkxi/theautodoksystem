<?php
define('APP_ACCESS', true);
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/Database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/session.php';

requireLogin();
requireAnyRole(['technician', 'stockman', 'lead_man']);

$selectedMonth = trim((string)($_GET['month'] ?? date('Y-m')));
if (!preg_match('/^\d{4}-\d{2}$/', $selectedMonth)) {
    $selectedMonth = date('Y-m');
}

$attendance = Database::getInstance()->fetchAll(
    "SELECT date, time_in, time_out, status, notes
     FROM attendance
     WHERE staff_id = ? AND date LIKE ?
     ORDER BY date DESC, time_in DESC",
    [(int)$_SESSION['user_id'], $selectedMonth . '-%']
);

$attendanceByDate = [];
foreach ($attendance as $record) {
    $date = (string)$record['date'];
    $period = ((int)substr((string)$record['time_in'], 0, 2) < 12) ? 'morning' : 'afternoon';
    $attendanceByDate[$date][$period] = $record;
}

$pageTitle = 'Attendance';
include __DIR__ . '/../partials/header.php';
?>

<style>
.my-attendance-card {
    border: 1px solid #dfe3e7;
    border-radius: 14px;
    overflow: hidden;
    box-shadow: 0 1px 2px rgba(0, 0, 0, .04);
}
.my-attendance-table {
    min-width: 820px;
    margin-bottom: 0;
}
.my-attendance-table-wrap {
    max-height: 720px;
    overflow: auto;
}
.my-attendance-table thead th {
    background: #f8f9fa;
    color: #888;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: .07em;
    padding: 14px 16px;
    text-transform: uppercase;
    border-bottom: 1px solid #e7e9eb;
    position: sticky;
    top: 0;
    z-index: 1;
}
.my-attendance-table tbody td {
    padding: 16px;
    vertical-align: middle;
    border-bottom: 1px solid #e7e9eb;
}
.my-attendance-table tbody tr:last-child td {
    border-bottom: 0;
}
.my-attendance-table .attendance-date {
    color: #252a2e;
    font-size: 15px;
    white-space: nowrap;
}
.my-attendance-table .attendance-value {
    color: #333;
    font-size: 14px;
    min-width: 145px;
    white-space: nowrap;
}
.my-attendance-table .attendance-status {
    color: #777;
    font-size: 14px;
    text-transform: lowercase;
    white-space: nowrap;
}
.my-attendance-table .attendance-status .status-pill {
    display: inline-block;
    background: #999;
    border-radius: 10px;
    color: #fff;
    padding: 5px 12px;
}
.my-attendance-table .attendance-status .status-present,
.my-attendance-table .attendance-status .status-late {
    background: #2d2d2d;
}
.my-attendance-table .attendance-status .status-absent,
.my-attendance-table .attendance-status .status-on_leave,
.my-attendance-table .attendance-status .status-other {
    background: #999;
}
.my-attendance-table .attendance-details {
    color: #777;
    font-size: 13px;
    max-width: 190px;
    white-space: normal;
}
.attendance-details-view {
    font-size: 13px;
    padding: 4px 10px;
}
.attendance-details-modal .modal-header {
    background-color: var(--avatar-bg);
    color: #fff;
}
.attendance-details-modal .modal-content {
    border: 1px solid var(--avatar-bg);
}
.attendance-details-modal .attendance-note-box {
    background-color: var(--page-bg);
    border: 1px solid var(--avatar-bg);
    overflow-wrap: anywhere;
    white-space: pre-wrap;
    word-break: break-word;
}
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">My Attendance</h4>
        <p class="text-muted small mb-0">View your morning and afternoon attendance for the selected month.</p>
    </div>
    <form method="get" class="d-flex gap-2">
        <label for="attendanceMonth" class="visually-hidden">Month</label>
        <input id="attendanceMonth" type="month" name="month" class="form-control"
               value="<?php echo escape($selectedMonth); ?>">
        <button class="btn btn-dark" type="submit">View</button>
    </form>
</div>

<div class="card my-attendance-card">
    <div class="card-body p-0">
        <div class="table-responsive my-attendance-table-wrap">
            <table class="table table-hover my-attendance-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Morning</th>
                        <th>Status</th>
                        <th>Afternoon</th>
                        <th>Status</th>
                        <th>Other Details</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($attendance)): ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted py-5">
                            No attendance records found for <?php echo escape(date('F Y', strtotime($selectedMonth . '-01'))); ?>.
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($attendanceByDate as $date => $periods): ?>
                        <tr>
                            <td class="attendance-date"><?php echo escape($date); ?></td>
                            <?php foreach (['morning', 'afternoon'] as $period): ?>
                                <?php
                                $record = $periods[$period] ?? null;
                                $status = (string)($record['status'] ?? '');
                                $statusLabel = $status !== '' ? str_replace('_', ' ', $status) : '—';
                                $hasTime = $record && !in_array($status, ['absent', 'on_leave', 'other'], true);
                                ?>
                                <td class="attendance-value">
                                    <?php if (!$record || !$hasTime): ?>
                                        <span class="text-muted">—</span>
                                    <?php else: ?>
                                        <?php echo escape(date('g:i A', strtotime($record['time_in']))); ?>
                                        -
                                        <?php echo escape($record['time_out'] ? date('g:i A', strtotime($record['time_out'])) : '—'); ?>
                                    <?php endif; ?>
                                </td>
                                <td class="attendance-status">
                                    <?php if ($status !== ''): ?>
                                        <span class="status-pill status-<?php echo escape($status); ?>"><?php echo escape($statusLabel); ?></span>
                                    <?php else: ?>
                                        —
                                    <?php endif; ?>
                                </td>
                            <?php endforeach; ?>
                            <td class="attendance-details">
                                <?php
                                $morningDetails = (string)($periods['morning']['notes'] ?? '');
                                $afternoonDetails = (string)($periods['afternoon']['notes'] ?? '');
                                if ($morningDetails !== '' || $afternoonDetails !== ''):
                                ?>
                                    <button type="button" class="btn btn-sm btn-outline-secondary attendance-details-view"
                                            data-morning-note="<?php echo escape($morningDetails); ?>"
                                            data-afternoon-note="<?php echo escape($afternoonDetails); ?>">
                                        View
                                    </button>
                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade attendance-details-modal" id="attendanceDetailsModal" tabindex="-1" aria-labelledby="attendanceDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="attendanceDetailsModalLabel"><i class="bi bi-chat-left-text"></i> Other Attendance Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3" id="attendanceMorningDetailsGroup">
                    <label class="form-label fw-semibold">Morning</label>
                    <div id="attendanceMorningDetails" class="attendance-note-box rounded p-2 text-muted">—</div>
                </div>
                <div id="attendanceAfternoonDetailsGroup">
                    <label class="form-label fw-semibold">Afternoon</label>
                    <div id="attendanceAfternoonDetails" class="attendance-note-box rounded p-2 text-muted">—</div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('click', function (event) {
    const button = event.target.closest('.attendance-details-view');
    if (!button) return;
    const morningNote = button.dataset.morningNote || '';
    const afternoonNote = button.dataset.afternoonNote || '';
    document.getElementById('attendanceMorningDetails').textContent = morningNote || '—';
    document.getElementById('attendanceAfternoonDetails').textContent = afternoonNote || '—';
    document.getElementById('attendanceMorningDetailsGroup').style.display = morningNote ? '' : 'none';
    document.getElementById('attendanceAfternoonDetailsGroup').style.display = afternoonNote ? '' : 'none';
    bootstrap.Modal.getOrCreateInstance(document.getElementById('attendanceDetailsModal')).show();
});
</script>

<?php include __DIR__ . '/../partials/footer.php'; ?>
