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
$pageTitle = 'Dashboard';

$reportModel  = new Report();
$monthlyIncome = $reportModel->getMonthlyIncomeStats();

$jobOrderModel = new JobOrder();

// Technicians only see their assigned job orders
if ($isTechnician) {
    $techId = $_SESSION['user_id'] ?? 0;
    $db = Database::getInstance();
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
    <?php if (!$isTechnician): ?>
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
    <?php endif; ?>
</div>

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
</script>

<?php include __DIR__ . '/../partials/footer.php'; ?>
