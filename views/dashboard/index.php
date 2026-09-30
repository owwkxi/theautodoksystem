<?php
define('APP_ACCESS', true);
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/Database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../models/JobOrder.php';
require_once __DIR__ . '/../../models/User.php';
require_once __DIR__ . '/../../models/Report.php';

// Run auto-cleanup of old records (once per day)
runAutoCleanup();

requireLogin();

$isTechnician = ($_SESSION['user_role'] ?? '') === 'technician';
$isServiceAdviser = ($_SESSION['user_role'] ?? '') === 'service_adviser';
$isChiefMechanic = ($_SESSION['user_role'] ?? '') === 'chief_mechanic';
$isLeadMan = ($_SESSION['user_role'] ?? '') === 'lead_man';
$isStockman = ($_SESSION['user_role'] ?? '') === 'stockman';
if ($isTechnician || $isServiceAdviser || $isChiefMechanic || $isLeadMan) {
    redirect(routeUrl('services', ['tab' => 'job_orders']));
}
if ($isStockman) {
    redirect(routeUrl('inventory'));
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

    $lastPointsModalSeen = $_SESSION['last_points_modal_seen'] ?? null;
    $recentTechPoints = $db->fetchAll(
        "SELECT tp.reason, tp.points, tp.created_at, jo.job_order_number
         FROM technician_points tp
         LEFT JOIN job_orders jo ON jo.id = tp.job_order_id
         WHERE tp.technician_id = ?
          AND tp.created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
         ORDER BY tp.created_at DESC
         LIMIT 20",
        [$techId]
    );
    $techPointsModalEntries = [];
    foreach ($recentTechPoints as $pointRow) {
        $pointCreatedAt = (string)($pointRow['created_at'] ?? '');
        if ($pointCreatedAt !== '' && $lastPointsModalSeen && $pointCreatedAt <= $lastPointsModalSeen) {
           continue;
        }
        if ((float)($pointRow['points'] ?? 0) === 0) {
           continue;
        }
        $techPointsModalEntries[] = $pointRow;
    }
    if (!empty($techPointsModalEntries)) {
        $latestPointsModalAt = $techPointsModalEntries[0]['created_at'] ?? null;
        if ($latestPointsModalAt) {
           $_SESSION['last_points_modal_seen'] = $latestPointsModalAt;
        }
    }

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

$dailyDate   = date('Y-m-d');
$dailyIncome = $reportModel->getDailyIncomeByDate($dailyDate);
$previousDailyIncome = $reportModel->getDailyIncomeByDate(date('Y-m-d', strtotime('-1 day')));
$dailyTrend = $dailyIncome > $previousDailyIncome ? 'High' : 'Low';

$monthlyDate = date('Y-m-01');
$monthlyVal  = $reportModel->getMonthlyIncomeByYearMonth(date('Y'), date('n'));
$previousMonthlyDate = date('Y-m-01', strtotime('first day of last month'));
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
    <a href="<?php echo routeUrl('services', ['tab' => 'job_orders']); ?>" class="qnav-card">
        <div class="qnav-icon"><i class="bi bi-file-earmark-text"></i></div>
        <span class="qnav-label">Job Order</span>
        <i class="bi bi-chevron-right qnav-arrow"></i>
    </a>
    <a href="<?php echo routeUrl('staff'); ?>" class="qnav-card">
        <div class="qnav-icon"><i class="bi bi-people-fill"></i></div>
        <span class="qnav-label">Technician</span>
        <i class="bi bi-chevron-right qnav-arrow"></i>
    </a>
    <a href="<?php echo routeUrl('inventory'); ?>" class="qnav-card">
        <div class="qnav-icon"><i class="bi bi-box-seam"></i></div>
        <span class="qnav-label">Inventory</span>
        <i class="bi bi-chevron-right qnav-arrow"></i>
    </a>
    <a href="<?php echo routeUrl('reports'); ?>" class="qnav-card">
        <div class="qnav-icon"><i class="bi bi-file-earmark-bar-graph"></i></div>
        <span class="qnav-label">Reports</span>
        <i class="bi bi-chevron-right qnav-arrow"></i>
    </a>
</div>
<?php endif; ?>

<?php if ($isTechnician): ?>
<?php if (!empty($techPointsModalEntries)): ?>
<div class="modal fade" id="techPointsModal" tabindex="-1" aria-labelledby="techPointsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title" id="techPointsModalLabel"><i class="bi bi-star-fill me-2"></i>Points Update</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <div class="badge bg-success rounded-pill px-3 py-2 fs-6">
                        <?php
                        $techPointsModalTotal = array_sum(array_map(function ($row) {
                            return (float)($row['points'] ?? 0);
                        }, $techPointsModalEntries));
                        echo ($techPointsModalTotal >= 0 ? '+' : '') . number_format($techPointsModalTotal, 1) . ' pts';
                        ?>
                    </div>
                </div>
                <p class="text-muted small mb-3">You received the following points for your recent work:</p>
                <div class="list-group list-group-flush">
                    <?php foreach ($techPointsModalEntries as $pointEntry): ?>
                    <div class="list-group-item px-0 py-2">
                        <div class="d-flex justify-content-between align-items-center gap-3">
                            <div>
                                <div class="fw-semibold small"><?php echo escape($pointEntry['reason'] ?? 'Points'); ?></div>
                                <div class="text-muted small"><?php echo !empty($pointEntry['job_order_number']) ? 'JO #' . escape($pointEntry['job_order_number']) : 'Recent activity'; ?></div>
                            </div>
                            <span class="fw-bold <?php echo ((float)($pointEntry['points'] ?? 0)) >= 0 ? 'text-success' : 'text-danger'; ?>">
                                <?php echo ((float)($pointEntry['points'] ?? 0) >= 0 ? '+' : '') . number_format((float)($pointEntry['points'] ?? 0), 1); ?>
                            </span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-dark btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<script>
window.addEventListener('DOMContentLoaded', function () {
    const pointsModal = document.getElementById('techPointsModal');
    if (!pointsModal) return;
    const modal = bootstrap.Modal.getOrCreateInstance(pointsModal);
    modal.show();
});
</script>
<?php endif; ?>
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
                    <option value="completed">Completed</option>
                    <option value="car_washing">Car Washing</option>
                    <option value="released">Released</option>
                    <option value="returned_for_revision">Returned for Revision</option>
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
            <a href="<?php echo routeUrl('services', ['tab' => 'job_orders']); ?>" class="btn btn-dark btn-sm">Open Job Orders</a>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Income cards + Top Technicians row -->
<?php if (!$isTechnician): ?>
<div class="row g-3 mb-3" style="align-items:stretch;">
    <!-- Left: Income cards -->
    <div class="col-lg-8 d-flex flex-column gap-3">
        <div class="row g-3">
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-body py-3">
                        <div class="chart-head-title">Daily Income</div>
                        <div class="chart-head-sub"><?php echo date('F d, Y'); ?></div>
                        <div style="margin-top:10px;display:flex;align-items:center;gap:12px;">
                            <span style="font-size:24px;font-weight:700;color:#000;">&#8369; <?php echo number_format($dailyIncome, 0); ?></span>
                            <span style="font-size:10px;font-weight:600;padding:2px 10px;border-radius:20px;background:<?php echo $dailyTrend === 'High' ? '#222' : '#ccc'; ?>;color:<?php echo $dailyTrend === 'High' ? '#fff' : '#666'; ?>;"><?php echo $dailyTrend; ?></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-body py-3">
                        <div class="chart-head-title">Monthly Income</div>
                        <div class="chart-head-sub"><?php echo date('F Y'); ?></div>
                        <div style="margin-top:10px;display:flex;align-items:center;gap:12px;">
                            <span style="font-size:24px;font-weight:700;color:#000;">&#8369; <?php echo number_format($monthlyVal, 0); ?></span>
                            <span style="font-size:10px;font-weight:600;padding:2px 10px;border-radius:20px;background:<?php echo $monthlyTrend === 'High' ? '#222' : '#ccc'; ?>;color:<?php echo $monthlyTrend === 'High' ? '#fff' : '#666'; ?>;"><?php echo $monthlyTrend; ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Chart -->
        <div class="card flex-grow-1">
            <div class="card-body py-3">
                <div class="chart-head-title">Monthly Income Statistic</div>
                <div class="chart-head-sub"><?php echo date('Y'); ?></div>
                <div style="height:320px;position:relative;margin-top:8px;">
                    <canvas id="incomeChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    <!-- Right: Top Technicians -->
    <div class="col-lg-4">
        <?php
        $techToday = $reportModel->getTechnicianPerformance(date('Y-m-d'), date('Y-m-d'));
        $techWeek = $reportModel->getTechnicianPerformance(date('Y-m-d', strtotime('monday this week')), date('Y-m-d'));
        $techMonth = $reportModel->getTechnicianPerformance(date('Y-m-01'), date('Y-m-t'));
        ?>
        <div class="card h-100 top-tech-card">
            <div class="card-body py-3 d-flex flex-column h-100">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="mb-0" style="font-size:14px;font-weight:600;">Top Technicians</h6>
                    <a href="<?php echo routeUrl('reports', ['from' => date('Y-m-01'), 'to' => date('Y-m-t')]); ?>" class="btn btn-sm btn-outline-secondary" style="font-size:10px;padding:2px 8px;">View All</a>
                </div>
                <div class="d-flex gap-1 mb-2 flex-wrap">
                    <button class="btn btn-sm btn-dark dash-tech-filter active" data-period="today" style="font-size:10px;padding:2px 10px;">Today</button>
                    <button class="btn btn-sm btn-outline-secondary dash-tech-filter" data-period="week" style="font-size:10px;padding:2px 10px;">Week</button>
                    <button class="btn btn-sm btn-outline-secondary dash-tech-filter" data-period="month" style="font-size:10px;padding:2px 10px;">Month</button>
                </div>
                <?php
                $techPeriods = [
                    'today' => $techToday,
                    'week' => $techWeek,
                    'month' => $techMonth,
                ];
                foreach ($techPeriods as $period => $techs): ?>
                <div class="dash-tech-table flex-grow-1" id="dashTech_<?php echo $period; ?>" style="<?php echo $period !== 'today' ? 'display:none;' : ''; ?>">
                    <?php if (empty($techs)): ?>
                        <p class="text-muted text-center py-3 mb-0" style="font-size:11px;">No data for this period.</p>
                    <?php else: ?>
                    <div class="table-responsive dash-tech-scroll">
                        <table class="table table-sm table-hover align-middle mb-0" style="font-size:12px;">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:30px;">#</th>
                                    <th>Technician</th>
                                    <th class="text-center">JOs</th>
                                    <th class="text-end">Score</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($techs as $rank => $tp): ?>
                                <tr>
                                    <td><?php echo $rank + 1; ?></td>
                                    <td><strong><?php echo escape($tp['full_name']); ?></strong></td>
                                    <td class="text-center"><?php echo $tp['total_jos']; ?></td>
                                    <td class="text-end"><strong><?php echo number_format($tp['total_score'], 1); ?></strong> <span class="text-muted" style="font-size:9px;">pts</span></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="dash-tech-fill"></div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<style>
.top-tech-card {
    height: 100%;
}

.top-tech-card .card-body {
    min-height: 0;
    display: flex;
    flex-direction: column;
}

.dash-tech-table {
    min-height: 0;
    flex: 1 1 auto;
    display: flex;
    flex-direction: column;
    background: rgba(15, 23, 42, 0.015);
    border-radius: 10px;
    overflow: hidden;
}

.dash-tech-table .table-responsive {
    flex: 1 1 0;
    min-height: 0;
    max-height: none;
    overflow-y: auto;
    overflow-x: hidden;
}

.dash-tech-scroll {
    height: 100%;
}

.dash-tech-table .dash-tech-fill {
    display: none;
}

.dash-tech-table table {
    margin-bottom: 0;
}

.dash-tech-filter {
    border-radius: 999px;
    font-weight: 600;
    letter-spacing: 0.01em;
}
</style>

<script>
document.querySelectorAll('.dash-tech-filter').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.dash-tech-filter').forEach(b => {
            b.className = 'btn btn-sm btn-outline-secondary dash-tech-filter';
            b.style.fontSize = '10px';
            b.style.padding = '2px 10px';
        });
        this.className = 'btn btn-sm btn-dark dash-tech-filter active';
        this.style.fontSize = '10px';
        this.style.padding = '2px 10px';
        document.querySelectorAll('.dash-tech-table').forEach(t => t.style.display = 'none');
        const target = document.getElementById('dashTech_' + this.dataset.period);
        if (target) target.style.display = 'flex';
    });
});
</script>

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

<?php
// Top Technicians already shown in the main layout above
?>

<?php include __DIR__ . '/../partials/footer.php'; ?>
