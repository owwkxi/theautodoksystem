<?php
define('APP_ACCESS', true);
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/Database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../models/JobOrder.php';
require_once __DIR__ . '/../../models/User.php';
require_once __DIR__ . '/../../models/Report.php';

requireLogin();

$isTechnician = ($_SESSION['user_role'] ?? '') === 'technician';
$isServiceAdviser = ($_SESSION['user_role'] ?? '') === 'service_adviser';
$isChiefMechanic = ($_SESSION['user_role'] ?? '') === 'chief_mechanic';
if ($isTechnician || $isServiceAdviser || $isChiefMechanic) {
    redirect(APP_URL . '/views/services/manage.php?tab=job_orders');
}
$pageTitle = 'Dashboard';

$reportModel  = new Report();
$monthlyIncome = $reportModel->getMonthlyIncomeStats();

$jobOrderModel = new JobOrder();

// Technicians only see their assigned job orders
if ($isTechnician) {
    $techId = $_SESSION['user_id'] ?? 0;
    $db = Database::getInstance();
    $assignedTotalCountRow = $db->fetch(
        "SELECT COUNT(DISTINCT jo.id) AS total_assigned
         FROM job_orders jo
         INNER JOIN job_order_technicians jot ON jot.job_order_id = jo.id
         WHERE jot.technician_id = ?",
        [$techId]
    );
    $assignedActiveCountRow = $db->fetch(
        "SELECT COUNT(DISTINCT jo.id) AS active_assigned
         FROM job_orders jo
         INNER JOIN job_order_technicians jot ON jot.job_order_id = jo.id
         WHERE jot.technician_id = ?
           AND jo.status IN ('pending', 'ongoing', 'under_inspection', 'car_washing', 'returned_for_revision')",
        [$techId]
    );
    $assignedTotalJo = (int)($assignedTotalCountRow['total_assigned'] ?? 0);
    $assignedActiveJo = (int)($assignedActiveCountRow['active_assigned'] ?? 0);

    $assignedJobOrders = $db->fetchAll(
        "SELECT jo.id,
                jo.job_order_number,
                jo.status,
                jo.created_at,
                jo.status_timer_seconds,
                jo.status_timer_started_at,
                c.full_name AS customer_name,
                v.plate_number
         FROM job_orders jo
         INNER JOIN job_order_technicians jot ON jot.job_order_id = jo.id
         LEFT JOIN customers c ON c.id = jo.customer_id
         LEFT JOIN vehicles v ON v.id = jo.vehicle_id
         WHERE jot.technician_id = ?
         ORDER BY jo.created_at DESC",
        [$techId]
    );

    $runningStatuses = ['ongoing', 'under_inspection'];
    foreach ($assignedJobOrders as &$assignedJo) {
        $elapsedSeconds = (int)($assignedJo['status_timer_seconds'] ?? 0);
        if (in_array($assignedJo['status'], $runningStatuses, true) && !empty($assignedJo['status_timer_started_at'])) {
            $elapsedSeconds += max(0, time() - strtotime($assignedJo['status_timer_started_at']));
        }
        $hours = floor($elapsedSeconds / 3600);
        $minutes = floor(($elapsedSeconds % 3600) / 60);
        $seconds = $elapsedSeconds % 60;
        $assignedJo['elapsed_display'] = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
    }
    unset($assignedJo);

    $recentJobOrders = $db->fetchAll(
        "SELECT jo.* FROM job_orders jo
         INNER JOIN job_order_technicians jot ON jot.job_order_id = jo.id
         WHERE jot.technician_id = ?
         ORDER BY jo.created_at DESC LIMIT 5",
        [$techId]
    );
    $stats = ['yesterday_income' => 0, 'last_month_income' => 0];
} else {
    $stats           = $reportModel->getDashboardStats();
    $recentJobOrders = $jobOrderModel->getRecent(5);
}

$hour      = (int)date('H');
$greeting  = $hour < 12 ? 'Good Morning' : ($hour < 18 ? 'Good Afternoon' : 'Good Evening');
$firstName = escape(explode(' ', $_SESSION['full_name'])[0]);

$dailyDate   = date('Y-m-d', strtotime('-1 day'));
$dailyIncome = $reportModel->getDailyIncomeByDate($dailyDate);
$previousDailyIncome = $reportModel->getDailyIncomeByDate(date('Y-m-d', strtotime('-2 days')));
$dailyTrend = $dailyIncome > $previousDailyIncome ? 'High' : 'Low';

$monthlyDate = date('Y-m-01', strtotime('first day of last month'));
$monthlyVal  = $reportModel->getMonthlyIncomeByYearMonth(date('Y', strtotime($monthlyDate)), date('n', strtotime($monthlyDate)));
$previousMonthlyDate = date('Y-m-01', strtotime('first day of -2 months'));
$previousMonthlyVal = $reportModel->getMonthlyIncomeByYearMonth(date('Y', strtotime($previousMonthlyDate)), date('n', strtotime($previousMonthlyDate)));
$monthlyTrend = $monthlyVal > $previousMonthlyVal ? 'High' : 'Low';

include __DIR__ . '/../partials/header.php';
?>

<?php if (!$isTechnician): ?>
<!-- Welcome banner -->
<div class="welcome-banner">
    <?php echo $greeting; ?>, <?php echo $firstName; ?>!
</div>

<!-- Quick nav -->
<div class="quick-nav">
    <a href="<?php echo APP_URL; ?>/views/services/manage.php?tab=job_orders" class="qnav-card">
        <div class="qnav-icon"><i class="bi bi-file-earmark-text"></i></div>
        <span class="qnav-label">Job Order</span>
        <i class="bi bi-chevron-right qnav-arrow"></i>
    </a>
    <a href="<?php echo APP_URL; ?>/views/staff/index.php" class="qnav-card">
        <div class="qnav-icon"><i class="bi bi-people-fill"></i></div>
        <span class="qnav-label">Technician</span>
        <i class="bi bi-chevron-right qnav-arrow"></i>
    </a>
    <a href="<?php echo APP_URL; ?>/views/inventory/index.php" class="qnav-card">
        <div class="qnav-icon"><i class="bi bi-box-seam"></i></div>
        <span class="qnav-label">Inventory</span>
        <i class="bi bi-chevron-right qnav-arrow"></i>
    </a>
    <a href="<?php echo APP_URL; ?>/views/reports/index.php" class="qnav-card">
        <div class="qnav-icon"><i class="bi bi-file-earmark-bar-graph"></i></div>
        <span class="qnav-label">Reports</span>
        <i class="bi bi-chevron-right qnav-arrow"></i>
    </a>
</div>
<?php endif; ?>

<?php if ($isTechnician): ?>
<div class="card">
    <div class="card-body p-0">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 px-3 py-2 border-bottom">
            <h6 class="mb-0">My Job Orders</h6>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-dark">Total: <?php echo (int)$assignedTotalJo; ?></span>
                <span class="badge bg-primary">Active: <?php echo (int)$assignedActiveJo; ?></span>
                <select id="techJoFilter" class="form-select form-select-sm" style="min-width: 170px;">
                    <option value="all">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="ongoing">Ongoing</option>
                    <option value="under_inspection">Under Inspection</option>
                    <option value="car_washing">Car Washing</option>
                    <option value="returned_for_revision">Returned for Revision</option>
                    <option value="completed">Completed</option>
                    <option value="released">Released</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>
        </div>
        <?php if (empty($assignedJobOrders)): ?>
        <div class="p-4 text-center text-muted">No assigned job orders found.</div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0 align-middle" style="font-size: 13px;">
                <thead class="table-light">
                    <tr>
                        <th class="px-3">JO #</th>
                        <th>Customer</th>
                        <th>Plate</th>
                        <th>Status</th>
                        <th>Recorded Time</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody id="techJoTableBody">
                    <?php foreach ($assignedJobOrders as $assignedJo): ?>
                    <?php
                        $statusLabel = ucfirst(str_replace('_', ' ', $assignedJo['status']));
                        $statusColor = 'secondary';
                        if ($assignedJo['status'] === 'ongoing') {
                            $statusColor = 'primary';
                        } elseif ($assignedJo['status'] === 'under_inspection') {
                            $statusColor = 'danger';
                        } elseif ($assignedJo['status'] === 'car_washing') {
                            $statusColor = 'warning';
                        } elseif ($assignedJo['status'] === 'completed' || $assignedJo['status'] === 'released') {
                            $statusColor = 'success';
                        } elseif ($assignedJo['status'] === 'returned_for_revision' || $assignedJo['status'] === 'cancelled') {
                            $statusColor = 'warning';
                        }
                    ?>
                    <tr data-status="<?php echo escape($assignedJo['status']); ?>">
                        <td class="px-3 fw-semibold"><?php echo escape($assignedJo['job_order_number']); ?></td>
                        <td><?php echo escape($assignedJo['customer_name'] ?? 'N/A'); ?></td>
                        <td><?php echo escape($assignedJo['plate_number'] ?? 'N/A'); ?></td>
                        <td>
                            <span class="badge bg-<?php echo $statusColor; ?>"><?php echo escape($statusLabel); ?></span>
                        </td>
                        <td class="fw-semibold"><?php echo escape($assignedJo['elapsed_display']); ?></td>
                        <td><?php echo !empty($assignedJo['created_at']) ? date('M d, Y', strtotime($assignedJo['created_at'])) : 'N/A'; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div id="techJoEmptyState" class="p-3 text-center text-muted" style="display:none;">No job orders for selected filter.</div>
        <?php endif; ?>
        <div class="px-3 py-2 border-top text-end">
            <a href="<?php echo APP_URL; ?>/views/services/manage.php?tab=job_orders" class="btn btn-dark btn-sm">Open Job Orders</a>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Income cards -->
<?php if (!$isTechnician): ?>
<div class="income-grid">

    <!-- Daily Income -->
    <div class="income-card">
        <div class="income-card-top">
            <span class="income-label">Daily Income</span>
            <i class="bi bi-chevron-right income-chevron"></i>
        </div>
        <div class="income-sub"><?php echo date('F d, Y', strtotime($dailyDate)); ?></div>
        <hr class="income-divider">
        <div class="income-body">
            <div class="date-badge">
                <span class="db-top"><?php echo date('d', strtotime($dailyDate)); ?></span>
                <span class="db-bot"><?php echo date('M', strtotime($dailyDate)); ?></span>
            </div>
            <span class="income-value">&#8369; <?php echo number_format($dailyIncome, 0); ?></span>
            <span class="income-tag"><?php echo $dailyTrend; ?></span>
        </div>
    </div>

    <!-- Monthly Income -->
    <div class="income-card">
        <div class="income-card-top">
            <span class="income-label">Monthly Income</span>
            <i class="bi bi-chevron-right income-chevron"></i>
        </div>
        <div class="income-sub"><?php echo date('F Y', strtotime($monthlyDate)); ?></div>
        <hr class="income-divider">
        <div class="income-body">
            <div class="date-badge">
                <span class="db-top" style="font-size:16px"><?php echo date('Y', strtotime($monthlyDate)); ?></span>
                <span class="db-bot"><?php echo date('M', strtotime($monthlyDate)); ?></span>
            </div>
            <span class="income-value">&#8369;<?php echo number_format($monthlyVal, 0); ?></span>
            <span class="income-tag"><?php echo $monthlyTrend; ?></span>
        </div>
    </div>

</div>

<!-- Chart -->
<div class="chart-card">
    <div class="chart-head-title">Monthly Income Statistic</div>
    <div class="chart-head-sub">Latest updates from Technicians</div>
    <div class="chart-wrap">
        <canvas id="incomeChart"></canvas>
    </div>
</div>
<?php endif; ?>

<script>
(function() {
    const raw = <?php echo json_encode($monthlyIncome); ?>;
    const months = ['January','February','March','April','May','June',
                    'July','August','September','October','November','December'];

    const data = new Array(12).fill(0);
    if (Array.isArray(raw)) {
        raw.forEach(function(item) {
            const m = parseInt(item.month, 10);
            if (m >= 1 && m <= 12) {
                data[m - 1] = parseFloat(item.total_income) || 0;
            }
        });
    }

    const canvas = document.getElementById('incomeChart');
    if (!canvas) {
        return;
    }
    const ctx = canvas.getContext('2d');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: months,
            datasets: [{
                data: data,
                borderColor: '#555555',
                backgroundColor: function(context) {
                    const chart = context.chart;
                    const { ctx: c, chartArea } = chart;
                    if (!chartArea) return 'rgba(150,150,150,0.25)';
                    const g = c.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
                    g.addColorStop(0, 'rgba(140,140,140,0.50)');
                    g.addColorStop(1, 'rgba(200,200,200,0.02)');
                    return g;
                },
                tension: 0.42,
                fill: true,
                pointRadius: 5,
                pointHoverRadius: 7,
                pointBackgroundColor: '#333',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(ctx) {
                            return ' ₱' + ctx.parsed.y.toLocaleString('en-PH');
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { color: '#777', font: { size: 11 } }
                },
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(0,0,0,0.05)' },
                    ticks: {
                        color: '#777',
                        font: { size: 11 },
                        callback: function(v) {
                            if (v >= 1000000) return (v / 1000000).toFixed(1) + 'M';
                            if (v >= 1000)    return (v / 1000).toFixed(0) + 'k';
                            return v;
                        }
                    }
                }
            }
        }
    });
})();

(function() {
    const filterEl = document.getElementById('techJoFilter');
    const tbody = document.getElementById('techJoTableBody');
    const emptyEl = document.getElementById('techJoEmptyState');
    if (!filterEl || !tbody) {
        return;
    }

    function applyTechJoFilter() {
        const selected = filterEl.value;
        const rows = Array.from(tbody.querySelectorAll('tr[data-status]'));
        let visibleCount = 0;

        rows.forEach((row) => {
            const status = row.getAttribute('data-status') || '';
            const show = selected === 'all' || status === selected;
            row.style.display = show ? '' : 'none';
            if (show) visibleCount++;
        });

        if (emptyEl) {
            emptyEl.style.display = visibleCount === 0 ? '' : 'none';
        }
    }

    filterEl.addEventListener('change', applyTechJoFilter);
    applyTechJoFilter();
})();
</script>

<?php include __DIR__ . '/../partials/footer.php'; ?>
