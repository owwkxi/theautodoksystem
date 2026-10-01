<?php
define('APP_ACCESS', true);
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/Database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/security.php';
require_once __DIR__ . '/../../models/Staff.php';

// Require login and admin/cashier role
requireLogin();
requireAnyRole(['admin', 'cashier', 'chief_mechanic', 'service_adviser']);

$isCashier = (($_SESSION['user_role'] ?? '') === 'cashier');
$canManageStaff = hasAnyRole(['admin', 'cashier']);

$pageTitle = 'Staff Management';

// Get filters
$filters = [
    'role' => $_GET['role'] ?? '',
    'status' => $_GET['status'] ?? '',
    'search' => $_GET['search'] ?? '',
    'limit' => RECORDS_PER_PAGE,
    'offset' => (($_GET['page'] ?? 1) - 1) * RECORDS_PER_PAGE
];

// Get staff
$staffModel = new Staff();
$staffList = $staffModel->getAll($filters);
$totalRecords = $staffModel->count($filters);
$attendanceDate = date('Y-m-d');
$attendanceStaff = Database::getInstance()->fetchAll(
    "SELECT staff.*, phone AS contact_number, profile_photo AS profile_image
     FROM staff
     WHERE LOWER(role) <> 'admin'
     ORDER BY CONCAT(first_name, ' ', last_name) ASC"
);
$attendanceRecords = Database::getInstance()->fetchAll(
    "SELECT staff_id, time_in, time_out, status, notes
     FROM attendance
     WHERE date = ?",
    [$attendanceDate]
);
$attendanceByStaff = [];
foreach ($attendanceRecords as $attendanceRecord) {
    $period = ((int)substr((string)$attendanceRecord['time_in'], 0, 2) < 12) ? 'morning' : 'afternoon';
    $attendanceByStaff[(int)$attendanceRecord['staff_id']][$period] = $attendanceRecord;
}
$pagination = paginate($totalRecords, $_GET['page'] ?? 1);

// Get statistics
$stats = $staffModel->getStats();

include __DIR__ . '/../partials/header.php';
?>

<style>
.other-attendance-modal .modal-header,
.other-attendance-view-modal .modal-header {
    background-color: var(--avatar-bg);
    color: #fff;
}
.other-attendance-modal .modal-content,
.other-attendance-view-modal .modal-content {
    border: 1px solid var(--avatar-bg);
}
.other-attendance-modal .form-control,
.other-attendance-view-modal .attendance-note-box {
    background-color: var(--page-bg);
    border-color: var(--avatar-bg);
}
.other-attendance-view-modal .attendance-note-box {
    white-space: pre-wrap;
    overflow-wrap: anywhere;
    word-break: break-word;
}
#attendanceModal .modal-header {
    display: none;
}
#attendanceModal .modal-dialog {
    max-width: 1400px;
    margin: 1.25rem auto;
}
#attendanceModal .modal-content {
    border: 1px solid rgba(15, 23, 42, 0.08);
    border-radius: 0.8rem;
    box-shadow: 0 8px 22px rgba(15, 23, 42, 0.08);
    overflow: hidden;
    background: #f5f5f5;
}
#attendanceModal .modal-body {
    padding: 1.25rem 1.5rem 0.85rem;
}
#attendanceModal .modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: .5rem;
    padding: .75rem 1.5rem 1.25rem;
    border-top: 1px solid rgba(15, 23, 42, 0.08);
    background: #f5f5f5;
}
#attendanceModal .attendance-modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    margin-bottom: 1.2rem;
}
#attendanceModal .attendance-modal-header h3 {
    margin: 0;
    font-size: 1.05rem;
    font-weight: 700;
    color: #2a2a2a;
}
#attendanceModal .attendance-modal-header p {
    margin: 0.2rem 0 0;
    color: #6b7280;
    font-size: 0.9rem;
}
#attendanceModal .attendance-modal-close {
    flex: 0 0 auto;
    border: 0;
    background: transparent;
    color: #6b7280;
    font-size: 1.35rem;
    line-height: 1;
    padding: .25rem;
}
#attendanceModal .attendance-modal-close:hover {
    color: #2a2a2a;
}
#attendanceModal .attendance-date-field {
    width: 170px;
    min-width: 170px;
    height: 42px;
    border: 1px solid #b1b7bd;
    border-radius: 10px;
    background: #f1f3f5;
    color: #2f2f2f;
    text-align: center;
    font-size: 0.92rem;
    padding: 0.5rem 0.75rem;
    box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.02);
}
.attendance-controls {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: .55rem;
    width: 100%;
    padding: .15rem 0;
}
.attendance-controls .attendance-status,
.attendance-controls .attendance-time,
.attendance-controls .attendance-time-out {
    height: 42px;
    font-size: .875rem;
    background: rgba(255, 255, 255, 0.55);
    border: 1px solid #c5c9ce;
    border-radius: 10px;
    box-shadow: none;
    color: #2c2c2c;
}
.attendance-controls .attendance-status {
    flex: 0 0 180px;
    width: 180px;
    min-width: 180px;
}
.attendance-controls .attendance-time {
    flex: 0 0 142px;
    width: 142px;
    min-width: 142px;
}
.attendance-controls .attendance-time-out {
    flex: 0 0 142px;
    width: 142px;
    min-width: 142px;
}
.attendance-table th {
    font-size: .72rem;
    font-weight: 800;
    letter-spacing: .08em;
    text-transform: uppercase;
    color: #6b7280;
    border-bottom: 1px solid rgba(15, 23, 42, 0.08);
    background: rgba(248, 250, 252, 0.8);
    padding: 1rem .7rem;
}
.attendance-table {
    width: 100%;
    table-layout: fixed;
    border-collapse: separate;
    border-spacing: 0;
    background: transparent;
}
.attendance-table tbody tr {
    background: transparent;
}
.attendance-table tbody tr:nth-child(even) {
    background: rgba(0, 0, 0, 0.015);
}
.attendance-table tbody td {
    border-top: 1px solid rgba(15, 23, 42, 0.08);
    padding: 1rem .75rem;
    vertical-align: middle;
}
.attendance-table td:first-child {
    font-size: 1.05rem;
    font-weight: 700;
    color: #1f2937;
}
.attendance-table td:nth-child(2) {
    width: 17%;
}
.attendance-table .badge {
    display: inline-block;
    min-width: 120px;
    padding: .55rem .9rem;
    border-radius: 8px;
    font-size: .85rem;
    font-weight: 600;
    letter-spacing: .01em;
    background: rgba(148, 163, 184, 0.32) !important;
    color: #374151 !important;
}
.attendance-table .attendance-time::-webkit-calendar-picker-indicator,
.attendance-table .attendance-time-out::-webkit-calendar-picker-indicator {
    opacity: 0;
    width: 0;
    display: none;
    cursor: pointer;
}
.attendance-time-group .attendance-time,
.attendance-time-group .attendance-time-out {
    -webkit-appearance: none;
    appearance: none;
}
.attendance-table th:nth-child(1),
.attendance-table td:nth-child(1) {
    width: 17%;
}
.attendance-table th:nth-child(2),
.attendance-table td:nth-child(2) {
    width: 15%;
}
.attendance-table th:nth-child(3),
.attendance-table td:nth-child(3),
.attendance-table th:nth-child(4),
.attendance-table td:nth-child(4) {
    width: 34%;
    min-width: 620px;
}
.attendance-table td {
    vertical-align: middle;
    padding: 1.15rem .75rem;
}
.attendance-table .attendance-cell {
    min-width: 0;
    overflow: visible;
}
.attendance-table .attendance-cell-afternoon {
    width: 34%;
    min-width: 620px;
}
.attendance-cell-afternoon .attendance-controls {
    width: 100%;
}
.attendance-cell-afternoon .attendance-status {
    flex: 0 0 176px !important;
    width: 176px !important;
    min-width: 176px !important;
}
.attendance-cell-afternoon .attendance-time {
    flex: 0 0 149px !important;
    width: 149px !important;
    min-width: 149px !important;
}
.attendance-cell-afternoon .attendance-time-out {
    flex: 0 0 149px !important;
    width: 149px !important;
    min-width: 149px !important;
}
.attendance-table-wrap {
    overflow-x: auto;
    padding-bottom: .25rem;
}
#attendanceModal .modal-dialog {
    width: calc(100% - 2rem);
    max-width: 1400px;
    margin: 1rem auto;
}
#attendanceModal .modal-content {
    max-height: calc(100vh - 2rem);
}
#attendanceModal .modal-body {
    overflow-y: auto;
}
@media (max-width: 768px) {
    #attendanceModal .modal-dialog {
        width: calc(100% - .75rem);
        margin: .375rem auto;
    }
    #attendanceModal .modal-content {
        max-height: calc(100vh - .75rem);
    }
    #attendanceModal .modal-body {
        padding: .75rem;
    }
    #attendanceModal .attendance-filter-row {
        align-items: flex-start !important;
        flex-direction: column;
        gap: .75rem;
    }
    #attendanceModal .attendance-filter-row #attendanceDate {
        max-width: none !important;
        width: 100%;
        flex: 0 0 auto;
    }
    #attendanceModal .attendance-table {
        table-layout: auto;
        width: max-content;
        min-width: 980px;
        white-space: nowrap;
    }
    #attendanceModal .attendance-table th:nth-child(1),
    #attendanceModal .attendance-table td:nth-child(1) {
        width: 140px;
        min-width: 140px;
    }
    #attendanceModal .attendance-table th:nth-child(2),
    #attendanceModal .attendance-table td:nth-child(2) {
        width: 110px;
        min-width: 110px;
    }
    #attendanceModal .attendance-table th:nth-child(3),
    #attendanceModal .attendance-table td:nth-child(3),
    #attendanceModal .attendance-table th:nth-child(4),
    #attendanceModal .attendance-table td:nth-child(4) {
        width: 560px;
        min-width: 560px;
    }
    #attendanceModal .attendance-table td:nth-child(1) {
        font-size: .8rem;
    }
    #attendanceModal .attendance-table td:nth-child(2) .badge {
        display: inline-block;
        max-width: 100%;
        white-space: nowrap;
        line-height: 1.2;
    }
    #attendanceModal .attendance-table .attendance-cell {
        min-width: 0;
    }
    #attendanceModal .attendance-controls,
    #attendanceModal .attendance-cell-afternoon .attendance-controls {
        flex-wrap: wrap;
        width: 560px;
    }
    #attendanceModal .attendance-controls .attendance-status,
    #attendanceModal .attendance-cell-afternoon .attendance-status {
        flex: 0 0 176px !important;
        width: 176px !important;
        min-width: 176px !important;
    }
    #attendanceModal .attendance-controls .attendance-time,
    #attendanceModal .attendance-controls .attendance-time-out,
    #attendanceModal .attendance-cell-afternoon .attendance-time,
    #attendanceModal .attendance-cell-afternoon .attendance-time-out {
        flex: 0 0 149px !important;
        width: 149px !important;
        min-width: 149px !important;
    }
    #attendanceModal .attendance-controls .btn {
        flex: 0 0 auto;
    }
}
.page-item.active .page-link {
    background-color: #2a2a2a !important;
    border-color: #2a2a2a !important;
    color: #fff !important;
}
.page-link { color: #000 !important; }
.page-link:hover { background-color: #e9ecef !important; }

/* Restore the original compact attendance modal layout. */
#attendanceModal .modal-header {
    display: flex;
}
#attendanceModal .modal-dialog {
    width: calc(100% - 2rem);
    max-width: 1400px;
    margin: 1rem auto;
}
#attendanceModal .modal-content {
    border: 1px solid var(--bs-border-color);
    border-radius: .375rem;
    box-shadow: none;
    background: #fff;
    display: flex;
    flex-direction: column;
}
#attendanceModal .modal-body {
    padding: 1rem;
    min-height: 0;
    overflow-y: auto;
}
#attendanceModal .modal-footer {
    flex: 0 0 auto;
    display: flex;
    justify-content: flex-end;
    gap: .5rem;
    padding: .75rem;
    border-top: 1px solid var(--bs-border-color);
    background: #fff;
}
@media (max-width: 768px) {
    #attendanceModal .modal-footer {
        position: sticky;
        bottom: 0;
        z-index: 3;
        padding: .65rem;
    }

    #attendanceModal .modal-footer .btn {
        flex: 1 1 0;
        min-width: 0;
    }
}
#attendanceModal .attendance-modal-header,
#attendanceModal .attendance-date-field {
    all: revert;
}
.attendance-controls {
    gap: .4rem;
    min-width: 0;
    padding: .15rem 0;
}
.attendance-controls .attendance-status {
    flex: 0 1 128px;
    width: 118px;
    min-width: 118px;
    max-width: 128px;
    height: 38px;
    font-size: .875rem;
}
.attendance-time-group {
    position: relative;
    display: flex;
    align-items: center;
    flex: 0 0 120px;
    width: 120px;
    min-width: 120px;
}
.attendance-time-group .attendance-time,
.attendance-time-group .attendance-time-out {
    flex: 1 1 100%;
    width: 100%;
    min-width: 100%;
    padding-right: 2rem;
}
.attendance-time-picker {
    position: absolute;
    top: 1px;
    right: 1px;
    z-index: 2;
    width: 32px;
    height: 36px;
    padding: 0;
    color: #6c757d;
    border: 0;
    background: transparent;
}
.attendance-time-picker:hover,
.attendance-time-picker:focus {
    color: #212529;
    background: transparent;
    border: 0;
    box-shadow: none;
}
.attendance-controls .attendance-time,
.attendance-controls .attendance-time-out {
    flex: 0 1 100px;
    width: 100px;
    min-width: 100px;
    height: 38px;
    font-size: .875rem;
}
.attendance-time-group .attendance-time,
.attendance-time-group .attendance-time-out {
    flex: 1 1 auto;
    width: 100%;
    min-width: 0;
    padding-right: 2rem;
}
.attendance-time-group .attendance-time-picker {
    position: absolute;
    top: 1px;
    right: 1px;
}
.attendance-controls .attendance-other-button,
.attendance-controls .attendance-clear-button {
    flex: 0 0 auto;
    height: 38px;
    padding: .375rem .7rem;
    font-size: .8rem;
    font-weight: 500;
    white-space: nowrap;
}
.attendance-controls .attendance-other-button {
    color: #495057;
    border-color: #adb5bd;
}
.attendance-controls .attendance-clear-button {
    color: #6c757d;
    border-color: #ced4da;
}
.attendance-controls .attendance-clear-button:hover,
.attendance-controls .attendance-clear-button:focus-visible {
    color: #fff;
    background-color: #6c757d;
    border-color: #6c757d;
}
.attendance-controls .attendance-clear-button:focus:not(:hover) {
    color: #6c757d;
    background-color: transparent;
    border-color: #ced4da;
    box-shadow: none;
}
.attendance-controls .attendance-clear-button.is-cleared {
    color: #fff;
    background-color: #6c757d;
    border-color: #6c757d;
}
.attendance-controls .attendance-other-button,
.attendance-controls .attendance-clear-button {
    flex: 0 0 auto;
    min-width: 64px;
}
.attendance-filter-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .75rem;
}
.attendance-filter-row #attendanceDate {
    flex: 0 1 180px;
    width: 180px;
}
.attendance-table th {
    font-size: .75rem;
    font-weight: 600;
    letter-spacing: .02em;
    text-transform: uppercase;
    color: #6c757d;
}
.attendance-table {
    table-layout: fixed;
    border-collapse: separate;
    border-spacing: 0;
    width: 100%;
    min-width: 1210px;
}
.attendance-table tbody tr,
.attendance-table tbody tr:nth-child(even) {
    background: transparent;
}
.attendance-table tbody td {
    border-top: 0;
    padding: .5rem;
}
.attendance-table td:first-child {
    font-size: inherit;
    font-weight: 600;
    color: inherit;
}
.attendance-table .badge {
    min-width: 0;
    padding: .35em .65em;
    border-radius: .375rem;
    font-size: .75em;
    font-weight: 700;
    background: #6c757d !important;
    color: #fff !important;
}
.attendance-table th:nth-child(1),
.attendance-table td:nth-child(1) {
    width: 180px;
    min-width: 180px;
}
.attendance-table th:nth-child(2),
.attendance-table td:nth-child(2) {
    width: 150px;
    min-width: 150px;
}
.attendance-table th:nth-child(3),
.attendance-table td:nth-child(3),
.attendance-table th:nth-child(4),
.attendance-table td:nth-child(4) {
    width: 440px !important;
    min-width: 440px;
}
.attendance-table-wrap {
    display: block;
    width: 100%;
    max-width: 100%;
    overflow-x: scroll;
    overflow-y: scroll;
    max-height: calc(100vh - 245px);
    padding-bottom: .35rem;
    scrollbar-gutter: stable;
    scrollbar-width: thin;
    scrollbar-color: #adb5bd #f1f3f5;
}
.attendance-table-wrap::-webkit-scrollbar {
    width: 12px;
    height: 12px;
}
.attendance-table-wrap::-webkit-scrollbar-track {
    background: #f1f3f5;
    border-radius: 6px;
}
.attendance-table-wrap::-webkit-scrollbar-thumb {
    background: #adb5bd;
    border: 2px solid #f1f3f5;
    border-radius: 6px;
}
.attendance-table-wrap::-webkit-scrollbar-thumb:hover {
    background: #6c757d;
}
.attendance-cell-afternoon,
.attendance-cell-afternoon .attendance-controls {
    width: auto;
    min-width: 0;
}
.attendance-table .attendance-cell-afternoon {
    width: 440px;
    min-width: 440px;
}
.attendance-cell-afternoon .attendance-status {
    flex: 0 1 128px !important;
    width: 118px !important;
    min-width: 118px !important;
    max-width: 128px !important;
}
.attendance-cell-afternoon .attendance-time,
.attendance-cell-afternoon .attendance-time-out {
    flex: 0 1 100px !important;
    width: 100px !important;
    min-width: 100px !important;
}
.attendance-cell-afternoon .attendance-time-group {
    flex: 0 0 100px;
    width: 100px;
    min-width: 100px;
}
.attendance-cell-afternoon .attendance-time-picker {
    right: 1px;
}
@media (max-width: 768px) {
    .staff-stats-row {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        overflow: visible;
        margin-bottom: 10px !important;
    }

    .staff-stats-row .staff-stat-col {
        flex: 0 0 calc(50% - 4px);
        max-width: calc(50% - 4px);
        min-width: 0;
        padding-left: 0;
        padding-right: 0;
    }

    .staff-stats-row .card-body {
        padding: 9px;
    }

    .staff-stats-row .card-body p {
        font-size: 11px;
        margin-bottom: 4px !important;
    }

    .staff-stats-row .card-body h3 {
        font-size: 22px;
    }

    .staff-stats-row .card-body i {
        font-size: 1.55rem !important;
    }

    #attendanceModal .attendance-time-group {
        flex: 0 0 132px !important;
        flex-basis: 132px !important;
        width: 132px !important;
        min-width: 132px !important;
        max-width: 132px !important;
        overflow: hidden;
    }

    #attendanceModal .attendance-time-group .attendance-time,
    #attendanceModal .attendance-time-group .attendance-time-out {
        box-sizing: border-box;
        flex: 0 0 132px !important;
        width: 132px !important;
        min-width: 132px !important;
        max-width: 132px !important;
        padding-right: 2rem;
    }

    #attendanceModal .attendance-time-group .attendance-time-picker {
        top: 1px;
        right: 6px;
        width: 26px;
        max-width: 26px;
    }
}

/* Keep morning and afternoon time controls the same size. */
#attendanceModal .attendance-time-group {
    flex: 0 0 112px !important;
    flex-basis: 112px !important;
    width: 112px !important;
    min-width: 112px !important;
    max-width: 112px !important;
}

#attendanceModal .attendance-time-group .attendance-time,
#attendanceModal .attendance-time-group .attendance-time-out {
    box-sizing: border-box;
    flex: 0 0 112px !important;
    width: 112px !important;
    min-width: 112px !important;
    max-width: 112px !important;
}

@media (max-width: 768px) {
    #attendanceModal .attendance-time-group,
    #attendanceModal .attendance-time-group .attendance-time,
    #attendanceModal .attendance-time-group .attendance-time-out {
        flex-basis: 112px !important;
        width: 112px !important;
        min-width: 112px !important;
        max-width: 112px !important;
    }
}

@media (max-width: 768px) {
    #attendanceModal {
        overflow: hidden !important;
    }

    #attendanceModal .modal-dialog {
        width: calc(100% - .75rem);
        height: calc(100% - .75rem) !important;
        min-height: 0;
        max-height: calc(100% - .75rem) !important;
        margin: .375rem auto;
        display: flex;
        align-items: stretch;
    }

    #attendanceModal .modal-content {
        height: 100% !important;
        min-height: 0;
        max-height: none !important;
    }

    #attendanceModal .modal-body {
        flex: 1 1 auto;
        min-height: 0;
        overflow: auto;
        padding-bottom: 5rem;
    }

    #attendanceModal .modal-footer {
        position: fixed !important;
        left: .375rem;
        right: .375rem;
        bottom: .375rem;
        width: auto;
        flex: none;
        display: flex !important;
        min-height: 58px;
        visibility: visible;
        opacity: 1;
        z-index: 1060;
        box-sizing: border-box;
        border-radius: 0 0 .375rem .375rem;
    }
}

#attendanceModal .attendance-controls,
#attendanceModal .attendance-cell-afternoon .attendance-controls {
    flex-wrap: nowrap !important;
    white-space: nowrap;
}

.assigned-job-orders-scroll {
    max-height: 420px;
    overflow-y: auto;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
}

.assigned-job-orders-scroll thead th {
    position: sticky;
    top: 0;
    z-index: 1;
    background-color: #f8f9fa;
}

.staff-profile-avatar {
    width: 32px !important;
    height: 32px !important;
    min-width: 32px;
    min-height: 32px;
    flex: 0 0 32px;
    object-fit: cover;
}

#viewStaffModal .nav-tabs .nav-link {
    color: #212529;
}

#viewStaffModal .nav-tabs .nav-link.active {
    color: #000;
    background-color: #fff;
    border-color: #dee2e6 #dee2e6 #fff;
}
</style>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Staff Management</h4>
        <p class="text-muted small mb-0">Manage all staff members and their information</p>
    </div>
    <?php if ($canManageStaff): ?>
    <div class="d-flex flex-wrap gap-2">
        <button type="button" class="btn btn-outline-dark" data-bs-toggle="modal" data-bs-target="#attendanceModal">
            <i class="bi bi-calendar-check"></i> Attendance
        </button>
        <button type="button" class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#addStaffModal">
            <i class="bi bi-plus-circle"></i> Add New Staff
        </button>
    </div>
    <?php endif; ?>
</div>

<!-- Statistics Cards -->
<div class="row mb-4 staff-stats-row">
    <div class="col-md-3 staff-stat-col">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <p class="text-muted mb-1 small">Total Staff</p>
                        <h3 class="mb-0"><?php echo $stats['total_staff'] ?? 0; ?></h3>
                    </div>
                    <div class="text-dark">
                        <i class="bi bi-people-fill" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 staff-stat-col">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <p class="text-muted mb-1 small">Active Staff</p>
                        <h3 class="mb-0"><?php echo $stats['active_staff'] ?? 0; ?></h3>
                    </div>
                    <div class="text-success">
                        <i class="bi bi-check-circle-fill" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 staff-stat-col">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <p class="text-muted mb-1 small">Technicians</p>
                        <h3 class="mb-0"><?php echo $stats['technician_count'] ?? 0; ?></h3>
                    </div>
                    <div class="text-warning">
                        <i class="bi bi-tools" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 staff-stat-col">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <p class="text-muted mb-1 small">Inactive Staff</p>
                        <h3 class="mb-0"><?php echo $stats['inactive_staff'] ?? 0; ?></h3>
                    </div>
                    <div class="text-danger">
                        <i class="bi bi-x-circle-fill" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="" class="row g-3">
            <div class="col-md-4">
                <input type="text" class="form-control" name="search" 
                      placeholder="Search by name, login ID, email, or staff ID..." 
                       value="<?php echo escape($filters['search']); ?>">
            </div>
            <div class="col-md-2">
                <select class="form-select" name="role">
                    <option value="">All Roles</option>
                    <option value="admin" <?php echo $filters['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                    <option value="cashier" <?php echo $filters['role'] === 'cashier' ? 'selected' : ''; ?>>Cashier</option>
                    <option value="chief_mechanic" <?php echo $filters['role'] === 'chief_mechanic' ? 'selected' : ''; ?>>Chief Mechanic</option>
                    <option value="service_adviser" <?php echo $filters['role'] === 'service_adviser' ? 'selected' : ''; ?>>Service Adviser</option>
                    <option value="technician" <?php echo $filters['role'] === 'technician' ? 'selected' : ''; ?>>Technician</option>
                    <option value="lead_man" <?php echo $filters['role'] === 'lead_man' ? 'selected' : ''; ?>>Lead Man</option>
                    <option value="stockman" <?php echo $filters['role'] === 'stockman' ? 'selected' : ''; ?>>Stockman</option>
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select" name="status">
                    <option value="">All Status</option>
                    <option value="active" <?php echo $filters['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                    <option value="inactive" <?php echo $filters['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                    <option value="on_leave" <?php echo $filters['status'] === 'on_leave' ? 'selected' : ''; ?>>On Leave</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-dark w-100">
                    <i class="bi bi-search"></i> Filter
                </button>
            </div>
            <div class="col-md-2">
                <a href="<?php echo routeUrl('staff'); ?>" class="btn btn-secondary w-100">
                    <i class="bi bi-x-circle"></i> Clear
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Staff Table -->
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive table-responsive-actions">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Role</th>
                        <th>Contact</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($staffList)): ?>
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <i class="bi bi-inbox display-4 text-muted"></i>
                            <p class="text-muted mt-3">No staff members found</p>
                            <?php if ($canManageStaff): ?>
                            <button type="button" class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#addStaffModal">
                                <i class="bi bi-plus-circle"></i> Add First Staff Member
                            </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($staffList as $staff): ?>
                        <tr>
                            <td>
                                <strong><?php echo escape($staff['staff_id']); ?></strong>
                                <div class="small mt-1">
                                    <?php if (!empty($staff['nfc_uid'])): ?>
                                        <span class="badge bg-success-subtle text-success-emphasis">NFC registered</span>
                                    <?php else: ?>
                                        <span class="badge bg-light text-muted">No NFC card</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <?php if (!empty($staff['profile_image'])): ?>
                                        <img src="<?php echo UPLOAD_URL . escape($staff['profile_image']); ?>" 
                                             alt="Profile" class="rounded-circle me-2 staff-profile-avatar">
                                    <?php else: ?>
                                        <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center me-2 staff-profile-avatar"
                                             style="font-size: 12px; font-weight: 600;">
                                            <?php echo strtoupper(substr($staff['full_name'], 0, 2)); ?>
                                        </div>
                                    <?php endif; ?>
                                    <span><?php echo escape($staff['full_name']); ?></span>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-secondary">
                                    <?php echo escape(getRoleLabel($staff['role'])); ?>
                                </span>
                            </td>
                            <td><?php echo escape($staff['contact_number']); ?></td>
                            <td><?php echo escape($staff['email']); ?></td>
                            <td>
                                <span class="badge <?php echo $staff['status'] === 'active' ? 'bg-success' : 'bg-danger'; ?>">
                                    <?php echo escape(getStatusLabel($staff['status'])); ?>
                                </span>
                            </td>
                            <td><?php echo formatDate($staff['created_at']); ?></td>
                            <td>
                                <div class="dropdown action-dropdown">
                                    <button class="btn btn-sm action-menu-btn dropdown-toggle" type="button" id="staffActions<?php echo $staff['id']; ?>" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Staff actions">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="staffActions<?php echo $staff['id']; ?>">
                                        <li>
                                            <button type="button" class="dropdown-item" onclick="viewStaff(<?php echo $staff['id']; ?>)">
                                                <i class="bi bi-eye me-2"></i>View
                                            </button>
                                        </li>
                                        <?php if ($canManageStaff): ?>
                                        <li>
                                            <button type="button" class="dropdown-item" onclick="registerStaffNfc(<?php echo (int)$staff['id']; ?>)">
                                                <i class="bi bi-credit-card-2-front me-2"></i>Register NFC card
                                            </button>
                                        </li>
                                        <?php endif; ?>
                                        <?php if ($canManageStaff && !($isCashier && $staff['role'] === 'admin')): ?>
                                        <li>
                                            <button type="button" class="dropdown-item" onclick="editStaff(<?php echo $staff['id']; ?>)">
                                                <i class="bi bi-pencil me-2"></i>Edit
                                            </button>
                                        </li>
                                        <?php endif; ?>
                                        <?php if ($canManageStaff && !($isCashier && $staff['role'] === 'admin')): ?>
                                        <li>
                                            <button type="button" class="dropdown-item" onclick="toggleStatus(<?php echo $staff['id']; ?>, '<?php echo $staff['status']; ?>', '<?php echo escape((string)($staff['updated_at'] ?? '')); ?>')">
                                                <i class="bi bi-<?php echo $staff['status'] === 'active' ? 'x-circle' : 'check-circle'; ?> me-2"></i><?php echo $staff['status'] === 'active' ? 'Deactivate' : 'Activate'; ?>
                                            </button>
                                        </li>
                                        <?php endif; ?>
                                        <?php if ($canManageStaff && !$isCashier): ?>
                                        <li>
                                            <button type="button" class="dropdown-item text-danger" onclick="deleteStaff(<?php echo $staff['id']; ?>, <?php echo htmlspecialchars(json_encode($staff['full_name'] ?? $staff['username'] ?? 'Unknown'), ENT_QUOTES); ?>)">
                                                <i class="bi bi-trash me-2"></i>Delete
                                            </button>
                                        </li>
                                        <?php endif; ?>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <?php if ($pagination['total_pages'] > 1): ?>
    <div class="card-footer">
        <nav>
            <ul class="pagination pagination-sm mb-0 justify-content-center">
                <li class="page-item <?php echo !$pagination['has_previous'] ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $pagination['current_page'] - 1; ?>&role=<?php echo $filters['role']; ?>&status=<?php echo $filters['status']; ?>&search=<?php echo urlencode($filters['search']); ?>">
                        Previous
                    </a>
                </li>
                
                <?php
                $totalPages = $pagination['total_pages'];
                $currentPage = $pagination['current_page'];
                $startPage = max(1, $currentPage - 2);
                $endPage = min($totalPages, $startPage + 4);
                if ($endPage - $startPage < 4) $startPage = max(1, $endPage - 4);
                for ($i = $startPage; $i <= $endPage; $i++): ?>
                <li class="page-item <?php echo $i === $currentPage ? 'active' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $i; ?>&role=<?php echo $filters['role']; ?>&status=<?php echo $filters['status']; ?>&search=<?php echo urlencode($filters['search']); ?>">
                        <?php echo $i; ?>
                    </a>
                </li>
                <?php endfor; ?>
                
                <li class="page-item <?php echo !$pagination['has_next'] ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $pagination['current_page'] + 1; ?>&role=<?php echo $filters['role']; ?>&status=<?php echo $filters['status']; ?>&search=<?php echo urlencode($filters['search']); ?>">
                        Next
                    </a>
                </li>
            </ul>
        </nav>
        <div class="text-center mt-2">
            <small class="text-muted">
                Showing <?php echo $pagination['offset'] + 1; ?> to 
                <?php echo min($pagination['offset'] + $pagination['records_per_page'], $pagination['total_records']); ?> 
                of <?php echo $pagination['total_records']; ?> records
            </small>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php if ($canManageStaff): ?>
<!-- Attendance Modal -->
<div class="modal fade" id="attendanceModal" tabindex="-1" aria-labelledby="attendanceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="attendanceModalLabel">
                    <i class="bi bi-calendar-check"></i> Staff Attendance
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="attendanceForm">
                <div class="modal-body">
                    <div class="attendance-filter-row mb-3">
                        <div>
                            <p class="mb-0 fw-semibold">Record attendance</p>
                            <small class="text-muted">Choose a status for each staff member. Select Other to add a note.</small>
                        </div>
                        <input type="date" class="form-control" id="attendanceDate" name="date" value="<?php echo escape($attendanceDate); ?>" required style="max-width: 180px;">
                    </div>
                    <div class="table-responsive attendance-table-wrap">
                       <table class="table table-hover align-middle text-nowrap attendance-table">
                           <thead>
                               <tr>
                                   <th>Staff</th>
                                   <th>Role</th>
                                   <th style="width: 320px;">Morning</th>
                                   <th style="width: 320px;">Afternoon</th>
                               </tr>
                           </thead>
                           <tbody>
                               <?php foreach ($attendanceStaff as $attendanceMember):
                                   $attendancePeriods = [
                                       'morning' => $attendanceByStaff[(int)$attendanceMember['id']]['morning'] ?? [],
                                       'afternoon' => $attendanceByStaff[(int)$attendanceMember['id']]['afternoon'] ?? [],
                                   ];
                               ?>
                               <tr>
                                   <td class="fw-semibold"><?php echo escape($attendanceMember['full_name']); ?></td>
                                   <td><span class="badge bg-secondary"><?php echo escape(getRoleLabel($attendanceMember['role'])); ?></span></td>
                                   <?php foreach ($attendancePeriods as $period => $periodRecord):
                                       $attendanceStatus = $periodRecord['status'] ?? '';
                                       $attendanceNotes = $periodRecord['notes'] ?? '';
                                       $attendanceTime = !empty($periodRecord['time_in']) ? substr((string)$periodRecord['time_in'], 0, 5) : '';
                                       $attendanceTimeOut = !empty($periodRecord['time_out']) ? substr((string)$periodRecord['time_out'], 0, 5) : '';
                                   ?>
                                   <td class="attendance-cell attendance-cell-<?php echo $period; ?>">
                                      <div class="attendance-controls">
                                       <select class="form-select attendance-status" name="status[<?php echo $period; ?>][<?php echo (int)$attendanceMember['id']; ?>]" data-staff-id="<?php echo (int)$attendanceMember['id']; ?>" data-period="<?php echo $period; ?>">
                                            <option value="" <?php echo $attendanceStatus === '' ? 'selected' : ''; ?>>Select status</option>
                                            <?php foreach (['present' => 'Present', 'late' => 'Late', 'absent' => 'Absent', 'on_leave' => 'On Leave', 'other' => 'Other'] as $statusValue => $statusLabel): ?>
                                            <option value="<?php echo $statusValue; ?>" <?php echo $attendanceStatus === $statusValue ? 'selected' : ''; ?>><?php echo $statusLabel; ?></option>
                                            <?php endforeach; ?>
                                       </select>
                                       <div class="attendance-time-group">
                                           <input type="time" class="form-control attendance-time" id="attendance-time-<?php echo $period; ?>-<?php echo (int)$attendanceMember['id']; ?>" name="time[<?php echo $period; ?>][<?php echo (int)$attendanceMember['id']; ?>]" value="<?php echo escape($attendanceTime); ?>" aria-label="<?php echo ucfirst($period); ?> time" <?php echo in_array($attendanceStatus, ['absent', 'on_leave', 'other'], true) ? 'style="display:none;"' : ''; ?>>
                                           <button type="button" class="btn btn-outline-secondary attendance-time-picker" data-target="attendance-time-<?php echo $period; ?>-<?php echo (int)$attendanceMember['id']; ?>" aria-label="Choose <?php echo strtolower($period); ?> time" title="Choose time" <?php echo in_array($attendanceStatus, ['absent', 'on_leave', 'other'], true) ? 'style="display:none;"' : ''; ?>><i class="bi bi-clock"></i></button>
                                       </div>
                                       <div class="attendance-time-group">
                                           <input type="time" class="form-control attendance-time-out" id="attendance-time-out-<?php echo $period; ?>-<?php echo (int)$attendanceMember['id']; ?>" name="time_out[<?php echo $period; ?>][<?php echo (int)$attendanceMember['id']; ?>]" value="<?php echo escape($attendanceTimeOut); ?>" aria-label="<?php echo ucfirst($period); ?> time out" <?php echo in_array($attendanceStatus, ['absent', 'on_leave', 'other'], true) ? 'style="display:none;"' : ''; ?>>
                                           <button type="button" class="btn btn-outline-secondary attendance-time-picker" data-target="attendance-time-out-<?php echo $period; ?>-<?php echo (int)$attendanceMember['id']; ?>" aria-label="Choose <?php echo strtolower($period); ?> time out" title="Choose time out" <?php echo in_array($attendanceStatus, ['absent', 'on_leave', 'other'], true) ? 'style="display:none;"' : ''; ?>><i class="bi bi-clock"></i></button>
                                       </div>
                                       <input type="hidden" class="attendance-notes" name="notes[<?php echo $period; ?>][<?php echo (int)$attendanceMember['id']; ?>]" value="<?php echo escape($attendanceNotes); ?>">
                                       <button type="button" class="btn btn-outline-secondary attendance-other-button" data-staff-id="<?php echo (int)$attendanceMember['id']; ?>" data-period="<?php echo $period; ?>" <?php echo $attendanceStatus === 'other' ? '' : 'style="display:none;"'; ?>>
                                           View
                                       </button>
                                       <button type="button" class="btn btn-outline-danger attendance-clear-button" data-staff-id="<?php echo (int)$attendanceMember['id']; ?>" data-period="<?php echo $period; ?>">
                                           Clear
                                       </button>
                                       </div>
                                   </td>
                                   <?php endforeach; ?>
                               </tr>
                               <?php endforeach; ?>
                           </tbody>
                       </table>
                   </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-dark"><i class="bi bi-save"></i> Save Attendance</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Other Attendance Details Modal -->
<div class="modal fade other-attendance-modal" id="attendanceOtherModal" tabindex="-1" aria-labelledby="attendanceOtherModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="attendanceOtherModalLabel"><i class="bi bi-chat-left-text"></i> Other Attendance Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="attendanceOtherStaffId">
                <input type="hidden" id="attendanceOtherPeriod">
                <label for="attendanceOtherMorningNotes" class="form-label">Morning details</label>
                <textarea class="form-control mb-3" id="attendanceOtherMorningNotes" rows="3" placeholder="Enter morning details"></textarea>
                <label for="attendanceOtherAfternoonNotes" class="form-label">Afternoon details</label>
                <textarea class="form-control" id="attendanceOtherAfternoonNotes" rows="3" placeholder="Enter afternoon details"></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-dark" id="saveAttendanceOtherNotes">Save Details</button>
            </div>
        </div>
    </div>
</div>

<!-- Add Staff Modal -->
<div class="modal fade" id="addStaffModal" tabindex="-1" aria-labelledby="addStaffModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addStaffModalLabel">
                    <i class="bi bi-person-plus"></i> Add New Staff Member
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addStaffForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="add_full_name" class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="add_full_name" name="full_name" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Login ID</label>
                            <input type="text" class="form-control" value="Auto-generated 5-digit ID" readonly>
                        </div>
                        <div class="col-md-6">
                            <label for="add_password" class="form-label">Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="add_password" name="password" required>
                        </div>
                        <div class="col-md-6">
                            <label for="add_confirm_password" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="add_confirm_password" name="confirm_password" required>
                        </div>
                        <div class="col-md-6">
                            <label for="add_email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="add_email" name="email" required>
                        </div>
                        <div class="col-md-6">
                            <label for="add_contact_number" class="form-label">Contact Number <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="add_contact_number" name="contact_number" required>
                        </div>
                        <div class="col-md-12">
                            <label for="add_address" class="form-label">Address</label>
                            <textarea class="form-control" id="add_address" name="address" rows="2"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label for="add_role" class="form-label">Role/Position <span class="text-danger">*</span></label>
                            <select class="form-select" id="add_role" name="role" required>
                                <option value="">Select Role</option>
                                <?php if (!$isCashier): ?>
                                <option value="admin">Admin</option>
                                <?php endif; ?>
                                <option value="cashier">Cashier</option>
                                <option value="chief_mechanic">Chief Mechanic</option>
                                <option value="service_adviser">Service Adviser</option>
                                <option value="technician">Technician</option>
                                <option value="lead_man">Lead Man</option>
                                <option value="stockman">Stockman</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="add_status" class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-select" id="add_status" name="status" required>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label for="add_profile_image" class="form-label">Profile Image</label>
                            <input type="file" class="form-control" id="add_profile_image" name="profile_image" accept="image/*">
                            <small class="text-muted">Max file size: 5MB. Allowed: JPG, JPEG, PNG</small>
                            <div id="add_image_preview" class="mt-2"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-dark">
                        <i class="bi bi-save"></i> Save Staff
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Staff Modal -->
<div class="modal fade" id="editStaffModal" tabindex="-1" aria-labelledby="editStaffModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editStaffModalLabel">
                    <i class="bi bi-pencil"></i> Edit Staff Member
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editStaffForm" enctype="multipart/form-data">
                <input type="hidden" id="edit_staff_id" name="id">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="edit_full_name" class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_full_name" name="full_name" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Login ID</label>
                            <input type="text" class="form-control" id="edit_staff_login_id" readonly>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_password" class="form-label">Password <small class="text-muted">(leave blank to keep current)</small></label>
                            <input type="password" class="form-control" id="edit_password" name="password">
                        </div>
                        <div class="col-md-6">
                            <label for="edit_confirm_password" class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" id="edit_confirm_password" name="confirm_password">
                        </div>
                        <div class="col-md-6">
                            <label for="edit_email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="edit_email" name="email" required>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_contact_number" class="form-label">Contact Number <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_contact_number" name="contact_number" required>
                        </div>
                        <div class="col-md-12">
                            <label for="edit_address" class="form-label">Address</label>
                            <textarea class="form-control" id="edit_address" name="address" rows="2"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_role" class="form-label">Role/Position <span class="text-danger">*</span></label>
                            <select class="form-select" id="edit_role" name="role" required>
                                <option value="">Select Role</option>
                                <?php if (!$isCashier): ?>
                                <option value="admin">Admin</option>
                                <?php endif; ?>
                                <option value="cashier">Cashier</option>
                                <option value="chief_mechanic">Chief Mechanic</option>
                                <option value="service_adviser">Service Adviser</option>
                                <option value="technician">Technician</option>
                                <option value="lead_man">Lead Man</option>
                                <option value="stockman">Stockman</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_status" class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-select" id="edit_status" name="status" required>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label for="edit_profile_image" class="form-label">Profile Image</label>
                            <input type="file" class="form-control" id="edit_profile_image" name="profile_image" accept="image/*">
                            <small class="text-muted">Max file size: 5MB. Allowed: JPG, JPEG, PNG</small>
                            <div id="edit_image_preview" class="mt-2"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-dark">
                        <i class="bi bi-save"></i> Update Staff
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if ($canManageStaff): ?>
<div class="modal fade" id="registerNfcModal" tabindex="-1" aria-labelledby="registerNfcModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="registerNfcModalLabel"><i class="bi bi-credit-card-2-front"></i> Register NFC Card</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="registerNfcForm">
                <div class="modal-body">
                    <input type="hidden" id="nfc_staff_id">
                    <p class="mb-2">Staff member: <strong id="nfc_staff_name"></strong></p>
                    <label for="nfc_card_uid" class="form-label">Card UID</label>
                    <input type="text" class="form-control" id="nfc_card_uid" maxlength="80" autocomplete="off" placeholder="Click here, then scan the card" required>
                    <div class="form-text">Click Start Scan, then tap the card. Its UID is registered to this staff member automatically. If needed, enter a UID and choose Save Card.</div>
                    <button type="button" class="btn btn-sm btn-outline-dark mt-2" id="startNfcScanButton"><i class="bi bi-broadcast-pin"></i> Start Scan</button>
                    <button type="button" class="btn btn-sm btn-outline-primary mt-2" id="connectAcr122RegistrationButton"><i class="bi bi-usb-drive"></i> Connect ACR122</button>
                    <div class="form-text" id="nfcScanStatus" aria-live="polite">Waiting for a card scan.</div>
                    <button type="button" class="btn btn-sm btn-outline-secondary mt-2" id="clearNfcUidButton">Remove registered card</button>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-dark" id="saveNfcUidButton"><i class="bi bi-save"></i> Save Card</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- View Staff Modal -->
<div class="modal fade" id="viewStaffModal" tabindex="-1" aria-labelledby="viewStaffModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewStaffModalLabel">
                    <i class="bi bi-eye"></i> Staff Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="viewStaffContent">
                <!-- Content will be loaded dynamically -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade other-attendance-view-modal" id="viewAttendanceNotesModal" tabindex="-1" aria-labelledby="viewAttendanceNotesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewAttendanceNotesModalLabel"><i class="bi bi-chat-left-text"></i> Other Attendance Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3" id="viewMorningAttendanceNoteGroup">
                    <label class="form-label fw-semibold">Morning</label>
                    <div id="viewMorningAttendanceNote" class="attendance-note-box rounded p-2 text-muted">—</div>
                </div>
                <div id="viewAfternoonAttendanceNoteGroup">
                    <label class="form-label fw-semibold">Afternoon</label>
                    <div id="viewAfternoonAttendanceNote" class="attendance-note-box rounded p-2 text-muted">—</div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const APP_URL = '<?php echo APP_URL; ?>';
    window.NFC_BRIDGE_PROXY_URL = <?php echo json_encode(APP_URL . '/api/nfc-bridge.php?route=', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>;
</script>
<script src="<?php echo escape(APP_URL . '/assets/js/nfc-keyboard-reader.js?v=' . time()); ?>"></script>
<script src="<?php echo escape(APP_URL . '/assets/js/nfc-pcsc-bridge.js?v=' . time()); ?>"></script>
<script src="<?php echo APP_URL; ?>/assets/js/staff.js?v=<?php echo time(); ?>"></script>

<?php include __DIR__ . '/../partials/footer.php'; ?>
