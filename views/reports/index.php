<?php
define('APP_ACCESS', true);
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/Database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../models/Report.php';

requireLogin();

$pageTitle = 'Reports';

// Date range filter
$dateFrom = $_GET['from'] ?? date('Y-m-d', strtotime('-30 days'));
$dateTo = $_GET['to'] ?? date('Y-m-d');

if (!strtotime($dateFrom)) {
    $dateFrom = date('Y-m-d', strtotime('-30 days'));
}
if (!strtotime($dateTo)) {
    $dateTo = date('Y-m-d');
}
if ($dateFrom > $dateTo) {
    $dateFrom = date('Y-m-d', strtotime('-30 days'));
    $dateTo = date('Y-m-d');
}

$reportModel = new Report();
$incomeReport = $reportModel->getIncomeReport($dateFrom, $dateTo);
$serviceStats = $reportModel->getServiceTypeStats($dateFrom, $dateTo);
$paymentMethods = $reportModel->getPaymentMethodStats($dateFrom, $dateTo);
$paymentSummary = $reportModel->getPaymentStatusSummary();
$statusSummary = $reportModel->getJobOrderStatusSummary();
$topCustomers = $reportModel->getTopCustomers(10);
$recentActivity = $reportModel->getRecentActivity(8);

$totalOrders = array_sum(array_column($incomeReport, 'job_orders_count'));
$totalIncome = array_sum(array_column($incomeReport, 'total_income'));
$paidIncome = array_sum(array_column($incomeReport, 'paid_income'));
$pendingIncome = array_sum(array_column($incomeReport, 'pending_income'));

include __DIR__ . '/../partials/header.php';
?>

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
    </form>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card h-100">
            <div class="card-body">
                <p class="text-muted mb-1 small">Report Period</p>
                <h5 class="mb-0"><?php echo escape($dateFrom); ?> — <?php echo escape($dateTo); ?></h5>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card h-100">
            <div class="card-body">
                <p class="text-muted mb-1 small">Job Orders</p>
                <h3 class="mb-0"><?php echo number_format($totalOrders); ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card h-100">
            <div class="card-body">
                <p class="text-muted mb-1 small">Total Income</p>
                <h3 class="mb-0">₱ <?php echo number_format($totalIncome, 2); ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card h-100">
            <div class="card-body">
                <p class="text-muted mb-1 small">Paid / Pending</p>
                <h5 class="mb-0">₱ <?php echo number_format($paidIncome, 2); ?> / ₱ <?php echo number_format($pendingIncome, 2); ?></h5>
            </div>
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
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
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
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
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

<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title">Job Order Status Summary</h5>
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
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
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
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
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
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
                <h5 class="card-title">Recent Activity</h5>
                <div class="table-responsive">
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
