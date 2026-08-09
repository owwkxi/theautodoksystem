<?php
define('APP_ACCESS', true);
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/Database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/security.php';
require_once __DIR__ . '/../../models/Report.php';

requireLogin();
requireAnyRole(['admin', 'cashier']);

$canManageExpenses = hasAnyRole(['admin', 'system_administrator']);

$pageTitle = 'Reports';

// Date range filter (default to today)
$dateFrom = $_GET['from'] ?? date('Y-m-d');
$dateTo = $_GET['to'] ?? date('Y-m-d');
$activityDate = $dateTo;

if (!strtotime($dateFrom)) {
    $dateFrom = date('Y-m-d');
}
if (!strtotime($dateTo)) {
    $dateTo = date('Y-m-d');
}
if ($dateFrom > $dateTo) {
    $dateFrom = date('Y-m-d');
    $dateTo = date('Y-m-d');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete_expense') {
    try {
        validateCSRF();

        if (!$canManageExpenses) {
            throw new Exception('Only admin/system administrator can delete expenses.');
        }

        $expenseId = trim((string)($_POST['expense_id'] ?? ''));
        if ($expenseId === '') {
            throw new Exception('Expense ID is required.');
        }

        $allExpenses = getReportExpenses();
        $updatedExpenses = array_values(array_filter($allExpenses, function ($row) use ($expenseId) {
            return (string)($row['id'] ?? '') !== $expenseId;
        }));

        if (count($updatedExpenses) === count($allExpenses)) {
            throw new Exception('Expense entry not found.');
        }

        if (!saveReportExpenses($updatedExpenses)) {
            throw new Exception('Failed to delete expense entry.');
        }

        $removedExpense = null;
        foreach ($allExpenses as $row) {
            if ((string)($row['id'] ?? '') === $expenseId) {
                $removedExpense = $row;
                break;
            }
        }

        $actorName = sanitize($_SESSION['full_name'] ?? $_SESSION['username'] ?? 'Staff');
        $removedAmount = (float)($removedExpense['amount'] ?? 0);
        $removedDate = (string)($removedExpense['expense_date'] ?? date('Y-m-d'));
        notifyRoles(
            'system',
            'Expense Deleted',
            buildNotificationMessageTemplate(
                $actorName,
                'deleted',
                'expense entry',
                'Amount: ₱' . number_format($removedAmount, 2) . ', Date: ' . date('M d, Y', strtotime($removedDate))
            ),
            ['admin', 'cashier'],
            [
                'reference_type' => 'report_expense',
            ]
        );

        setMessage('Expense entry deleted successfully.', 'success');
    } catch (Exception $e) {
        setMessage('Error: ' . $e->getMessage(), 'error');
    }

    $redirectFrom = $_POST['from'] ?? $dateFrom;
    $redirectTo = $_POST['to'] ?? $dateTo;
    redirect(APP_URL . '/views/reports/index.php?from=' . urlencode($redirectFrom) . '&to=' . urlencode($redirectTo));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add_expense') {
    try {
        validateCSRF();

        $expenseDate = $_POST['expense_date'] ?? date('Y-m-d');
        $expenseAmount = (float)($_POST['expense_amount'] ?? 0);
        $expenseNotes = trim((string)($_POST['expense_notes'] ?? ''));

        if (!strtotime($expenseDate)) {
            throw new Exception('Please provide a valid expense date.');
        }
        if ($expenseAmount <= 0) {
            throw new Exception('Expense amount must be greater than zero.');
        }

        $added = addReportExpense([
            'expense_date' => date('Y-m-d', strtotime($expenseDate)),
            'category' => 'General',
            'description' => $expenseNotes,
            'amount' => $expenseAmount,
            'created_by' => $_SESSION['full_name'] ?? $_SESSION['username'] ?? 'System'
        ]);

        if (!$added) {
            throw new Exception('Failed to save expense entry.');
        }

        $actorName = sanitize($_SESSION['full_name'] ?? $_SESSION['username'] ?? 'Staff');
        notifyRoles(
            'system',
            'Expense Added',
            buildNotificationMessageTemplate(
                $actorName,
                'added',
                'an expense',
                'Amount: ₱' . number_format($expenseAmount, 2) . ', Date: ' . date('M d, Y', strtotime($expenseDate))
            ),
            ['admin', 'cashier'],
            [
                'reference_type' => 'report_expense',
            ]
        );

        setMessage('Expense entry added successfully.', 'success');
    } catch (Exception $e) {
        setMessage('Error: ' . $e->getMessage(), 'error');
    }

    $redirectFrom = $_POST['from'] ?? $dateFrom;
    $redirectTo = $_POST['to'] ?? $dateTo;
    redirect(APP_URL . '/views/reports/index.php?from=' . urlencode($redirectFrom) . '&to=' . urlencode($redirectTo));
}

$allExpenses = getReportExpenses();
$filteredExpenses = array_values(array_filter($allExpenses, function ($row) use ($dateFrom, $dateTo) {
    $expenseDate = $row['expense_date'] ?? '';
    return $expenseDate >= $dateFrom && $expenseDate <= $dateTo;
}));

usort($filteredExpenses, function ($a, $b) {
    $aDate = ($a['expense_date'] ?? '') . ' ' . ($a['created_at'] ?? '');
    $bDate = ($b['expense_date'] ?? '') . ' ' . ($b['created_at'] ?? '');
    return strcmp($bDate, $aDate);
});

$reportModel = new Report();
$incomeReport = $reportModel->getIncomeReport($dateFrom, $dateTo);
$serviceStats = $reportModel->getServiceTypeStats($dateFrom, $dateTo);
$paymentMethods = $reportModel->getPaymentMethodStats($dateFrom, $dateTo);
$paymentSummary = $reportModel->getPaymentStatusSummary($dateFrom, $dateTo);
$statusSummary = $reportModel->getJobOrderStatusSummary($dateFrom, $dateTo);
$topCustomers = $reportModel->getTopCustomers(10, $dateFrom, $dateTo);
$recentActivity = $reportModel->getRecentActivity(0, $activityDate);

$totalOrders = array_sum(array_column($incomeReport, 'job_orders_count'));
$totalIncome = array_sum(array_column($incomeReport, 'total_income'));
$paidIncome = array_sum(array_column($incomeReport, 'paid_income'));
$pendingIncome = array_sum(array_column($incomeReport, 'pending_income'));
$totalExpenses = 0;
foreach ($filteredExpenses as $expenseItem) {
    $totalExpenses += (float)($expenseItem['amount'] ?? 0);
}
$netIncome = $paidIncome - $totalExpenses;

if (($_GET['export'] ?? '') === 'excel') {
    $db = Database::getInstance();
    $jobOrderDetails = $db->fetchAll(
        "SELECT
            jo.job_order_number,
            jo.created_at,
            jo.status,
            jo.payment_status,
            jo.total_amount,
            jo.partial_amount,
            c.full_name AS customer_name,
            c.phone AS customer_phone,
            v.brand,
            v.model,
            v.plate_number
         FROM job_orders jo
         LEFT JOIN customers c ON jo.customer_id = c.id
         LEFT JOIN vehicles v ON jo.vehicle_id = v.id
         WHERE DATE(jo.created_at) BETWEEN ? AND ?
         ORDER BY jo.created_at DESC",
        [$dateFrom, $dateTo]
    );

    $safeFrom = preg_replace('/[^0-9\-]/', '', $dateFrom);
    $safeTo = preg_replace('/[^0-9\-]/', '', $dateTo);
    $filename = "autodok_reports_{$safeFrom}_to_{$safeTo}.xls";

    header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');

    echo "<html><head><meta charset='UTF-8'><style>";
    echo "body{font-family:Calibri,Arial,sans-serif;font-size:12px;color:#111;}";
    echo ".report-title{font-size:22px;font-weight:700;margin:0 0 6px 0;color:#1f2937;}";
    echo ".report-sub{font-size:12px;color:#4b5563;margin:0 0 12px 0;}";
    echo ".meta{margin-bottom:10px;}";
    echo ".meta td{padding:4px 8px;border:none;}";
    echo ".note{background:#f8fafc;border:1px solid #dbe3ee;padding:8px 10px;margin:8px 0 14px 0;color:#334155;}";
    echo ".section{font-size:15px;font-weight:700;color:#1f2937;margin:14px 0 6px 0;}";
    echo "table{border-collapse:collapse;width:100%;margin-bottom:14px;}";
    echo "th,td{border:1px solid #d1d5db;padding:6px 8px;vertical-align:middle;}";
    echo "th{background:#eef2f7;color:#111827;font-weight:700;text-align:left;white-space:nowrap;}";
    echo "tr:nth-child(even) td{background:#fafafa;}";
    echo ".text-right{text-align:right;}";
    echo ".muted{color:#6b7280;}";
    echo "</style></head><body>";

    echo "<div class='report-title'>The Autodok Reports</div>";
    echo "<div class='report-sub'>Exported financial and operational report</div>";
    echo "<table class='meta'>";
    echo "<tr><td><strong>Period:</strong></td><td>" . escape($dateFrom) . " to " . escape($dateTo) . "</td></tr>";
    echo "<tr><td><strong>Generated At:</strong></td><td>" . escape(date('Y-m-d h:i A')) . "</td></tr>";
    echo "<tr><td><strong>Generated By:</strong></td><td>" . escape($_SESSION['full_name'] ?? $_SESSION['username'] ?? 'System') . "</td></tr>";
    echo "</table>";
    echo "<div class='note'><strong>How totals are computed:</strong> <span class='muted'>Paid includes fully paid orders plus partial amounts from partially paid orders. Pending includes full pending orders plus remaining balances from partially paid orders.</span></div>";

    echo "<div class='section'>1. Executive Summary</div>";
    echo "<table>";
    echo "<tr><th>Total Job Orders</th><th>Total Income (PHP)</th><th>Paid Income (PHP)</th><th>Expenses (PHP)</th><th>Net Income (PHP)</th><th>Pending Income (PHP)</th></tr>";
    echo "<tr>";
    echo "<td class='text-right'>" . number_format($totalOrders) . "</td>";
    echo "<td class='text-right'>" . number_format((float)$totalIncome, 2) . "</td>";
    echo "<td class='text-right'>" . number_format((float)$paidIncome, 2) . "</td>";
    echo "<td class='text-right'>" . number_format((float)$totalExpenses, 2) . "</td>";
    echo "<td class='text-right'>" . number_format((float)$netIncome, 2) . "</td>";
    echo "<td class='text-right'>" . number_format((float)$pendingIncome, 2) . "</td>";
    echo "</tr></table>";

    echo "<div class='section'>2. Expenses Log</div>";
    echo "<table><tr><th>Date</th><th>Description</th><th>Entered By</th><th class='text-right'>Amount (PHP)</th></tr>";
    if (empty($filteredExpenses)) {
        echo "<tr><td colspan='4'>No expenses recorded for this range</td></tr>";
    } else {
        foreach ($filteredExpenses as $expenseRow) {
            echo "<tr>";
            echo "<td>" . escape($expenseRow['expense_date'] ?? '—') . "</td>";
            echo "<td>" . escape($expenseRow['description'] ?? '—') . "</td>";
            echo "<td>" . escape($expenseRow['created_by'] ?? 'System') . "</td>";
            echo "<td class='text-right'>" . number_format((float)($expenseRow['amount'] ?? 0), 2) . "</td>";
            echo "</tr>";
        }
    }
    echo "</table>";

    echo "<div class='section'>3. Job Orders Details</div>";
    echo "<table><tr>";
    echo "<th>JO #</th><th>Date</th><th>Customer</th><th>Phone</th><th>Vehicle</th><th>Plate</th><th>Status</th><th>Payment Status</th><th class='text-right'>Total (PHP)</th><th class='text-right'>Paid (PHP)</th><th class='text-right'>Pending (PHP)</th>";
    echo "</tr>";
    if (empty($jobOrderDetails)) {
        echo "<tr><td colspan='11'>No job orders for this range</td></tr>";
    } else {
        foreach ($jobOrderDetails as $row) {
            $rowTotal = (float)($row['total_amount'] ?? 0);
            $rowPartial = (float)($row['partial_amount'] ?? 0);
            $rowPayment = (string)($row['payment_status'] ?? 'pending');
            $rowPaid = 0.0;
            $rowPending = 0.0;

            if ($rowPayment === 'paid') {
                $rowPaid = $rowTotal;
            } elseif ($rowPayment === 'partial') {
                $rowPaid = max(0, min($rowPartial, $rowTotal));
                $rowPending = max(0, $rowTotal - $rowPaid);
            } else {
                $rowPending = $rowTotal;
            }

            echo "<tr>";
            echo "<td>" . escape($row['job_order_number']) . "</td>";
            echo "<td>" . escape(date('Y-m-d H:i', strtotime($row['created_at']))) . "</td>";
            echo "<td>" . escape($row['customer_name'] ?? 'Unknown') . "</td>";
            echo "<td>" . escape($row['customer_phone'] ?? '—') . "</td>";
            echo "<td>" . escape(trim(($row['brand'] ?? '') . ' ' . ($row['model'] ?? ''))) . "</td>";
            echo "<td>" . escape($row['plate_number'] ?? '—') . "</td>";
            echo "<td>" . escape(ucwords(str_replace('_', ' ', $row['status'] ?? 'pending'))) . "</td>";
            echo "<td>" . escape(ucwords(str_replace('_', ' ', $rowPayment))) . "</td>";
            echo "<td class='text-right'>" . number_format($rowTotal, 2) . "</td>";
            echo "<td class='text-right'>" . number_format($rowPaid, 2) . "</td>";
            echo "<td class='text-right'>" . number_format($rowPending, 2) . "</td>";
            echo "</tr>";
        }
    }
    echo "</table>";

    echo "<div class='section'>4. Income Trend</div>";
    echo "<table><tr><th>Date</th><th class='text-right'>Job Orders</th><th class='text-right'>Total Income (PHP)</th><th class='text-right'>Paid Income (PHP)</th><th class='text-right'>Pending Income (PHP)</th></tr>";
    if (empty($incomeReport)) {
        echo "<tr><td colspan='5'>No data for this range</td></tr>";
    } else {
        foreach ($incomeReport as $row) {
            echo "<tr>";
            echo "<td>" . escape($row['date']) . "</td>";
            echo "<td class='text-right'>" . number_format((int)$row['job_orders_count']) . "</td>";
            echo "<td class='text-right'>" . number_format((float)$row['total_income'], 2) . "</td>";
            echo "<td class='text-right'>" . number_format((float)$row['paid_income'], 2) . "</td>";
            echo "<td class='text-right'>" . number_format((float)$row['pending_income'], 2) . "</td>";
            echo "</tr>";
        }
    }
    echo "</table>";

    echo "<div class='section'>5. Service Type Revenue</div>";
    echo "<table><tr><th>Service Type</th><th class='text-right'>Orders</th><th class='text-right'>Revenue (PHP)</th></tr>";
    if (empty($serviceStats)) {
        echo "<tr><td colspan='3'>No data for this range</td></tr>";
    } else {
        foreach ($serviceStats as $row) {
            echo "<tr>";
            echo "<td>" . escape($row['service_name'] ?? $row['service_type'] ?? 'Unknown') . "</td>";
            echo "<td class='text-right'>" . number_format((int)$row['count']) . "</td>";
            echo "<td class='text-right'>" . number_format((float)$row['total_revenue'], 2) . "</td>";
            echo "</tr>";
        }
    }
    echo "</table>";

    echo "<div class='section'>6. Payment Method Breakdown</div>";
    echo "<table><tr><th>Method</th><th class='text-right'>Orders</th><th class='text-right'>Amount (PHP)</th></tr>";
    if (empty($paymentMethods)) {
        echo "<tr><td colspan='3'>No payment data for this range</td></tr>";
    } else {
        foreach ($paymentMethods as $row) {
            echo "<tr>";
            echo "<td>" . escape($row['payment_method'] ?? 'Unknown') . "</td>";
            echo "<td class='text-right'>" . number_format((int)$row['count']) . "</td>";
            echo "<td class='text-right'>" . number_format((float)$row['total_amount'], 2) . "</td>";
            echo "</tr>";
        }
    }
    echo "</table>";

    echo "<div class='section'>7. Job Order Status Summary</div>";
    echo "<table><tr><th>Status</th><th class='text-right'>Count</th><th class='text-right'>Total (PHP)</th></tr>";
    if (empty($statusSummary)) {
        echo "<tr><td colspan='3'>No status summary available</td></tr>";
    } else {
        foreach ($statusSummary as $row) {
            echo "<tr>";
            echo "<td>" . escape(ucwords(str_replace('_', ' ', $row['status']))) . "</td>";
            echo "<td class='text-right'>" . number_format((int)$row['count']) . "</td>";
            echo "<td class='text-right'>" . number_format((float)$row['total_amount'], 2) . "</td>";
            echo "</tr>";
        }
    }
    echo "</table>";

    echo "<div class='section'>8. Payment Status Summary</div>";
    echo "<table><tr><th>Payment Status</th><th class='text-right'>Orders</th><th class='text-right'>Total (PHP)</th></tr>";
    if (empty($paymentSummary)) {
        echo "<tr><td colspan='3'>No payment summary available</td></tr>";
    } else {
        foreach ($paymentSummary as $row) {
            echo "<tr>";
            echo "<td>" . escape(ucwords(str_replace('_', ' ', $row['payment_status']))) . "</td>";
            echo "<td class='text-right'>" . number_format((int)$row['count']) . "</td>";
            echo "<td class='text-right'>" . number_format((float)$row['total_amount'], 2) . "</td>";
            echo "</tr>";
        }
    }
    echo "</table>";

    echo "<div class='section'>9. Top Customers</div>";
    echo "<table><tr><th>Customer</th><th>Phone</th><th class='text-right'>Visits</th><th class='text-right'>Spent (PHP)</th></tr>";
    if (empty($topCustomers)) {
        echo "<tr><td colspan='4'>No customer activity yet</td></tr>";
    } else {
        foreach ($topCustomers as $row) {
            echo "<tr>";
            echo "<td>" . escape($row['customer_name']) . "</td>";
            echo "<td>" . escape($row['customer_phone']) . "</td>";
            echo "<td class='text-right'>" . number_format((int)$row['total_visits']) . "</td>";
            echo "<td class='text-right'>" . number_format((float)$row['total_spent'], 2) . "</td>";
            echo "</tr>";
        }
    }
    echo "</table>";

    echo "<div class='section'>10. Recent Activity</div>";
    echo "<table><tr><th>Action</th><th>Description</th><th>User</th><th>Date</th></tr>";
    if (empty($recentActivity)) {
        echo "<tr><td colspan='4'>No recent activity found</td></tr>";
    } else {
        foreach ($recentActivity as $activity) {
            echo "<tr>";
            echo "<td>" . escape($activity['action']) . "</td>";
            echo "<td>" . escape($activity['description']) . "</td>";
            echo "<td>" . escape($activity['username'] ?? 'System') . "</td>";
            echo "<td>" . escape(date('F d, Y h:i A', strtotime($activity['created_at']))) . "</td>";
            echo "</tr>";
        }
    }
    echo "</table>";

    echo "</body></html>";
    exit;
}

include __DIR__ . '/../partials/header.php';
?>

<style>
.report-table-wrap {
    overflow-x: auto;
    overflow-y: visible;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: none;
    -ms-overflow-style: none;
}

.report-table-wrap::-webkit-scrollbar {
    width: 0;
    height: 0;
}

.report-table {
    margin-bottom: 0;
    min-width: 420px;
}

.report-table th,
.report-table td {
    padding: 7px 9px;
    vertical-align: middle;
}

.report-table th {
    white-space: nowrap;
}

.report-table td {
    white-space: normal;
}

.report-table th.text-end,
.report-table td.text-end {
    white-space: nowrap;
    min-width: 88px;
}

@media (max-width: 768px) {
    .report-table {
        min-width: 400px;
    }

    .report-table th,
    .report-table td {
        font-size: 11px;
        padding: 7px 8px;
    }
}
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Reports</h4>
        <p class="text-muted small mb-0">View business performance, payment status, and service statistics.</p>
    </div>
    <form class="row gx-2 gy-2 align-items-center" method="GET" action="">
        <div class="col-auto">
            <input type="date" class="form-control" name="from" value="<?php echo escape($dateFrom); ?>">
        </div>
        <div class="col-auto">
            <input type="date" class="form-control" name="to" value="<?php echo escape($dateTo); ?>">
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-primary">Apply</button>
        </div>
        <div class="col-auto">
            <button type="submit" name="export" value="excel" class="btn btn-success">Export Excel</button>
        </div>
    </form>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-4 col-md-12">
        <div class="card h-100">
            <div class="card-body">
                <p class="text-muted mb-1 small">Paid / Pending</p>
                <h4 class="mb-0"><?php echo number_format($paidIncome, 2); ?>/<?php echo number_format($pendingIncome, 2); ?></h4>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-12">
        <div class="card h-100">
            <div class="card-body">
                <p class="text-muted mb-1 small">Revenue / Expenses</p>
                <h4 class="mb-0"><?php echo number_format($totalIncome, 2); ?>/<?php echo number_format($totalExpenses, 2); ?></h4>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-12">
        <div class="card h-100 border-secondary bg-light">
            <div class="card-body">
                <p class="text-muted mb-1 small">Net Income</p>
                <h3 class="mb-0">₱ <?php echo number_format($netIncome, 2); ?></h3>
                <small class="text-muted">Period: <?php echo escape($dateFrom); ?> — <?php echo escape($dateTo); ?> | JO: <?php echo number_format($totalOrders); ?></small>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h5 class="card-title mb-0">Add Expense</h5>
                <p class="text-muted small mb-0">Record expenses separately for transparent net-income reporting.</p>
            </div>
        </div>
        <form method="POST" class="row g-2 align-items-end">
            <?php echo csrfField(); ?>
            <input type="hidden" name="action" value="add_expense">
            <input type="hidden" name="from" value="<?php echo escape($dateFrom); ?>">
            <input type="hidden" name="to" value="<?php echo escape($dateTo); ?>">
            <div class="col-lg-2 col-md-4">
                <label class="form-label">Date</label>
                <input type="date" class="form-control" name="expense_date" value="<?php echo escape($dateTo); ?>" required>
            </div>
            <div class="col-lg-2 col-md-4">
                <label class="form-label">Amount</label>
                <input type="number" class="form-control" name="expense_amount" min="0.01" step="0.01" required>
            </div>
            <div class="col-lg-6 col-md-8">
                <label class="form-label">Description</label>
                <input type="text" class="form-control" name="expense_notes" maxlength="180">
            </div>
            <div class="col-lg-2 col-md-4">
                <button type="submit" class="btn btn-dark w-100">Add Expense</button>
            </div>
        </form>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h5 class="card-title mb-0">Expenses Transparency Log</h5>
                <p class="text-muted small mb-0">All recorded expenses for the selected report period.</p>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Description</th>
                        <th>Entered By</th>
                        <th class="text-end">Amount</th>
                        <?php if ($canManageExpenses): ?>
                            <th class="text-end">Action</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($filteredExpenses)): ?>
                        <tr><td colspan="<?php echo $canManageExpenses ? '5' : '4'; ?>" class="text-center text-muted">No expenses recorded for this date range</td></tr>
                    <?php else: ?>
                        <?php foreach ($filteredExpenses as $expenseRow): ?>
                            <tr>
                                <td><?php echo escape($expenseRow['expense_date'] ?? '—'); ?></td>
                                <td><?php echo escape($expenseRow['description'] ?? '—'); ?></td>
                                <td><?php echo escape($expenseRow['created_by'] ?? 'System'); ?></td>
                                <td class="text-end">₱ <?php echo number_format((float)($expenseRow['amount'] ?? 0), 2); ?></td>
                                <?php if ($canManageExpenses): ?>
                                    <td class="text-end">
                                        <form method="POST" class="d-inline" onsubmit="return confirmExpenseDelete(this);">
                                            <?php echo csrfField(); ?>
                                            <input type="hidden" name="action" value="delete_expense">
                                            <input type="hidden" name="from" value="<?php echo escape($dateFrom); ?>">
                                            <input type="hidden" name="to" value="<?php echo escape($dateTo); ?>">
                                            <input type="hidden" name="expense_id" value="<?php echo escape($expenseRow['id'] ?? ''); ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                        </form>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h5 class="card-title mb-0">Income Trend</h5>
                <p class="text-muted small mb-0">Daily income between selected dates.</p>
            </div>
        </div>
        <div>
            <canvas id="incomeTrendChart" style="min-height:240px;"></canvas>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title">Service Type Revenue</h5>
                <div class="table-responsive report-table-wrap">
                    <table class="table table-sm align-middle mb-0 report-table">
                        <thead>
                            <tr>
                                <th>Service Type</th>
                                <th class="text-end">Orders</th>
                                <th class="text-end">Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($serviceStats)): ?>
                                <tr><td colspan="3" class="text-center text-muted">No data for this range</td></tr>
                            <?php else: ?>
                                <?php foreach ($serviceStats as $row): ?>
                                    <tr>
                                        <td><?php echo escape($row['service_name'] ?? $row['service_type'] ?? 'Unknown'); ?></td>
                                        <td class="text-end"><?php echo number_format($row['count']); ?></td>
                                        <td class="text-end">₱ <?php echo number_format($row['total_revenue'], 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title">Payment Method Breakdown</h5>
                <div class="table-responsive report-table-wrap">
                    <table class="table table-sm align-middle mb-0 report-table">
                        <thead>
                            <tr>
                                <th>Method</th>
                                <th class="text-end">Orders</th>
                                <th class="text-end">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($paymentMethods)): ?>
                                <tr><td colspan="3" class="text-center text-muted">No paid orders for this range</td></tr>
                            <?php else: ?>
                                <?php foreach ($paymentMethods as $row): ?>
                                    <tr>
                                        <td><?php echo escape($row['payment_method'] ?? 'Unknown'); ?></td>
                                        <td class="text-end"><?php echo number_format($row['count']); ?></td>
                                        <td class="text-end">₱ <?php echo number_format($row['total_amount'], 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4" id="recent-activity">
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title">Job Order Status Summary</h5>
                <div class="table-responsive report-table-wrap">
                    <table class="table table-sm align-middle mb-0 report-table">
                        <thead>
                            <tr>
                                <th>Status</th>
                                <th class="text-end">Count</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($statusSummary)): ?>
                                <tr><td colspan="3" class="text-center text-muted">No status summary available</td></tr>
                            <?php else: ?>
                                <?php foreach ($statusSummary as $row): ?>
                                    <tr>
                                        <td><?php echo escape(ucwords(str_replace('_', ' ', $row['status']))); ?></td>
                                        <td class="text-end"><?php echo number_format($row['count']); ?></td>
                                        <td class="text-end">₱ <?php echo number_format($row['total_amount'], 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title">Top Customers</h5>
                <div class="table-responsive report-table-wrap">
                    <table class="table table-sm align-middle mb-0 report-table">
                        <thead>
                            <tr>
                                <th>Customer</th>
                                <th class="text-end">Visits</th>
                                <th class="text-end">Spent</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($topCustomers)): ?>
                                <tr><td colspan="3" class="text-center text-muted">No customer activity yet</td></tr>
                            <?php else: ?>
                                <?php foreach ($topCustomers as $row): ?>
                                    <tr>
                                        <td><?php echo escape($row['customer_name']); ?> <span class="text-muted small d-block"><?php echo escape($row['customer_phone']); ?></span></td>
                                        <td class="text-end"><?php echo number_format($row['total_visits']); ?></td>
                                        <td class="text-end">₱ <?php echo number_format($row['total_spent'], 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-12">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title">Payment Status Summary</h5>
                <div class="table-responsive report-table-wrap">
                    <table class="table table-sm align-middle mb-0 report-table">
                        <thead>
                            <tr>
                                <th>Payment Status</th>
                                <th class="text-end">Orders</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($paymentSummary)): ?>
                                <tr><td colspan="3" class="text-center text-muted">No payment data available</td></tr>
                            <?php else: ?>
                                <?php foreach ($paymentSummary as $row): ?>
                                    <tr>
                                        <td><?php echo escape(ucwords(str_replace('_', ' ', $row['payment_status']))); ?></td>
                                        <td class="text-end"><?php echo number_format($row['count']); ?></td>
                                        <td class="text-end">₱ <?php echo number_format($row['total_amount'], 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-12">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                    <h5 class="card-title mb-0">Recent Activity</h5>
                </div>
                <div class="table-responsive" style="max-height:360px;overflow-y:auto;">
                    <table class="table table-sm align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Activity</th>
                                <th>User</th>
                                <th class="text-end">Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recentActivity)): ?>
                                <tr><td colspan="3" class="text-center text-muted">No recent activity found</td></tr>
                            <?php else: ?>
                                <?php foreach ($recentActivity as $activity): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo escape($activity['action']); ?></strong>
                                            <div class="text-muted small"><?php echo escape($activity['description']); ?></div>
                                        </td>
                                        <td><?php echo escape($activity['username'] ?? 'System'); ?></td>
                                        <td class="text-end"><?php echo escape(date('F d, Y h:i A', strtotime($activity['created_at']))); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    window.confirmExpenseDelete = function(form) {
        appConfirm('Delete this expense entry?', {
            title: 'Delete Expense',
            confirmText: 'Delete',
            cancelText: 'Cancel',
            variant: 'danger'
        }).then(function(confirmed) {
            if (confirmed && form) {
                form.submit();
            }
        });

        return false;
    };
})();
</script>

<script>
(function() {
    const reportData = <?php echo json_encode($incomeReport); ?>;
    const labels = reportData.map(item => item.date);
    const totalIncomeData = reportData.map(item => parseFloat(item.total_income) || 0);
    const paidIncomeData = reportData.map(item => parseFloat(item.paid_income) || 0);
    const pendingIncomeData = reportData.map(item => parseFloat(item.pending_income) || 0);

    const ctx = document.getElementById('incomeTrendChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels,
                datasets: [
                    {
                        label: 'Total Income',
                        data: totalIncomeData,
                        borderColor: '#0d6efd',
                        backgroundColor: 'rgba(13,110,253,0.12)',
                        tension: 0.35,
                        fill: true,
                        pointRadius: 3
                    },
                    {
                        label: 'Paid Income',
                        data: paidIncomeData,
                        borderColor: '#198754',
                        backgroundColor: 'rgba(25,135,84,0.12)',
                        tension: 0.35,
                        fill: true,
                        pointRadius: 3
                    },
                    {
                        label: 'Pending Income',
                        data: pendingIncomeData,
                        borderColor: '#dc3545',
                        backgroundColor: 'rgba(220,53,69,0.12)',
                        tension: 0.35,
                        fill: true,
                        pointRadius: 3
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'top' },
                    tooltip: { mode: 'index', intersect: false }
                },
                interaction: { mode: 'nearest', intersect: false },
                scales: {
                    x: { display: true, title: { display: false } },
                    y: { display: true, beginAtZero: true, title: { display: true, text: 'Amount (₱)' } }
                }
            }
        });
    }
})();
</script>

<?php include __DIR__ . '/../partials/footer.php'; ?>
