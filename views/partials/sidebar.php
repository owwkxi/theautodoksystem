<?php
if (!defined('APP_ACCESS')) {
    die('Direct access not permitted');
}
$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
$isDashboardRoute = strpos($requestPath, '/dashboard') !== false || strpos($requestPath, '/views/dashboard/') !== false;
$isServicesRoute = strpos($requestPath, '/services') !== false || strpos($requestPath, '/views/services/') !== false;
$isReportsRoute = strpos($requestPath, '/reports') !== false || strpos($requestPath, '/views/reports/') !== false;
$isInventoryRoute = strpos($requestPath, '/inventory') !== false || strpos($requestPath, '/views/inventory/') !== false;
$isStaffRoute = strpos($requestPath, '/staff') !== false || strpos($requestPath, '/views/staff/') !== false;
$isNotesRoute = strpos($requestPath, '/settings/notes') !== false || strpos($requestPath, '/views/settings/notes') !== false;
$isAttendanceRoute = strpos($requestPath, '/attendance') !== false || strpos($requestPath, '/views/attendance/') !== false;
$isSettingsRoute = !$isNotesRoute && (strpos($requestPath, '/settings') !== false || strpos($requestPath, '/views/settings/') !== false);
$userRole = $_SESSION['user_role'] ?? '';
$isTechnician = $userRole === 'technician';
$isChiefMechanic = $userRole === 'chief_mechanic';
$isServiceAdviser = $userRole === 'service_adviser';
$isLeadMan = $userRole === 'lead_man';
$isStockman = $userRole === 'stockman';
$isJobOrderOnlyRole = $isTechnician || $isChiefMechanic || $isLeadMan;
$isAdminOrCashier = hasAnyRole(['admin', 'cashier']);
$canAccessInventory = hasAnyRole(['admin', 'cashier', 'stockman']);
$canOpenStaffManagement = hasAnyRole(['admin', 'cashier', 'chief_mechanic', 'service_adviser']);
$brandingSettings = function_exists('getSystemBrandingSettings') ? getSystemBrandingSettings() : [];
$systemLogoUrl = $brandingSettings['system_logo_url'] ?? (APP_URL . '/assets/images/logo.png');
$activeShop = function_exists('getActiveShopOption') ? getActiveShopOption() : ['key' => 'autodok_main', 'name' => APP_NAME];
$shopName = $brandingSettings['sidebar_brand_name'] ?? ($activeShop['name'] ?? APP_NAME);
$shopSubtitle = $brandingSettings['sidebar_brand_subtitle'] ?? ((($activeShop['key'] ?? '') === 'autodok_prime') ? 'Prime Automotive Care Services' : 'Automotive Care Services');
?>
<div class="sidebar">

    <div class="sidebar-brand">
        <img src="<?php echo escape($systemLogoUrl); ?>" alt="<?php echo escape($shopName); ?> Logo" class="sidebar-logo" onerror="this.onerror=null;this.src='<?php echo APP_URL; ?>/assets/images/logo.png';">
        <div class="sidebar-brand-text">
            <div class="sidebar-brand-name"><?php echo escape($shopName); ?></div>
            <div class="sidebar-brand-sub"><?php echo escape($shopSubtitle); ?></div>
        </div>
    </div>

    <div class="user-badge">
        <i class="bi bi-person-fill badge-icon"></i>
        <span><?php echo ucfirst($_SESSION['user_role'] ?? 'user'); ?></span>
    </div>

    <nav class="sidebar-nav">
        <?php if (!$isTechnician && !$isChiefMechanic && !$isLeadMan && !$isStockman && !$isServiceAdviser): ?>
        <a href="<?php echo routeUrl('dashboard'); ?>"
           class="nav-item <?php echo $isDashboardRoute ? 'active' : ''; ?>">
            <i class="bi bi-grid-fill"></i>
            <span>Dashboard</span>
        </a>
        <?php endif; ?>

        <?php if ($isStockman): ?>
        <!-- Stockman: Job Orders (view only) + Inventory -->
        <a href="<?php echo routeUrl('services', ['tab' => 'job_orders']); ?>"
           class="nav-item <?php echo $isServicesRoute ? 'active' : ''; ?>">
            <i class="bi bi-file-earmark-text"></i>
            <span>Job Orders</span>
        </a>
        <a href="<?php echo routeUrl('settings_notes'); ?>"
           class="nav-item <?php echo $isNotesRoute ? 'active' : ''; ?>">
            <i class="bi bi-journal-text"></i>
            <span>Notes</span>
        </a>
        <a href="<?php echo routeUrl('inventory'); ?>"
           class="nav-item <?php echo $isInventoryRoute ? 'active' : ''; ?>">
            <i class="bi bi-box-seam"></i>
            <span>Inventory</span>
        </a>
        <a href="<?php echo routeUrl('attendance'); ?>"
           class="nav-item <?php echo $isAttendanceRoute ? 'active' : ''; ?>">
            <i class="bi bi-calendar-check"></i>
            <span>Attendance</span>
        </a>
        <?php elseif ($isJobOrderOnlyRole): ?>
        <a href="<?php echo routeUrl('services', ['tab' => 'job_orders']); ?>"
           class="nav-item <?php echo $isServicesRoute ? 'active' : ''; ?>">
            <i class="bi bi-wrench"></i>
            <span>Job Orders</span>
        </a>
        <?php if ($isTechnician || $isLeadMan): ?>
        <a href="<?php echo routeUrl('attendance'); ?>"
           class="nav-item <?php echo $isAttendanceRoute ? 'active' : ''; ?>">
            <i class="bi bi-calendar-check"></i>
            <span>Attendance</span>
        </a>
        <?php endif; ?>
        <?php else: ?>
        <a href="<?php echo routeUrl('services', ['tab' => 'job_orders']); ?>"
           class="nav-item <?php echo $isServicesRoute ? 'active' : ''; ?>">
            <i class="bi bi-wrench"></i>
            <span>Services</span>
        </a>
        <?php if (hasAnyRole(['admin', 'cashier', 'service_adviser'])): ?>
        <a href="<?php echo routeUrl('settings_notes'); ?>"
           class="nav-item <?php echo $isNotesRoute ? 'active' : ''; ?>">
            <i class="bi bi-journal-text"></i>
            <span>Notes</span>
        </a>
        <?php endif; ?>

        <?php if ($isAdminOrCashier): ?>
        <a href="<?php echo routeUrl('reports'); ?>"
           class="nav-item <?php echo $isReportsRoute ? 'active' : ''; ?>">
            <i class="bi bi-file-earmark-bar-graph"></i>
            <span>Report</span>
        </a>

          <a href="<?php echo routeUrl('inventory'); ?>"
              class="nav-item <?php echo $isInventoryRoute ? 'active' : ''; ?>">
                <i class="bi bi-box-seam"></i>
                <span>Inventory</span>
          </a>

                                        <?php if ($canOpenStaffManagement): ?>
          <a href="<?php echo routeUrl('staff'); ?>"
              class="nav-item <?php echo $isStaffRoute ? 'active' : ''; ?>">
                <i class="bi bi-people-fill"></i>
                <span>Staff Management</span>
          </a>
          <?php endif; ?>

               <?php if (hasAnyRole(['admin', 'cashier'])): ?>
        <a href="<?php echo routeUrl('settings'); ?>"
           class="nav-item <?php echo $isSettingsRoute ? 'active' : ''; ?>">
            <i class="bi bi-gear"></i>
            <span>Settings</span>
        </a>
        <?php endif; ?>

               <?php endif; ?>
        <?php endif; ?>

    </nav>

    <div class="sidebar-footer">
        <a href="<?php echo routeUrl('logout'); ?>" class="nav-item logout">
            <i class="bi bi-box-arrow-left"></i>
            <span>Logout</span>
        </a>
    </div>

</div>
