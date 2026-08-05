<?php
if (!defined('APP_ACCESS')) {
    die('Direct access not permitted');
}
$p = $_SERVER['PHP_SELF'];
$userRole = $_SESSION['user_role'] ?? '';
$isTechnician = $userRole === 'technician';
$isChiefMechanic = $userRole === 'chief_mechanic';
$isServiceAdviser = $userRole === 'service_adviser';
$isJobOrderOnlyRole = $isTechnician || $isChiefMechanic || $isServiceAdviser;
$isAdminOrCashier = hasAnyRole(['admin', 'cashier']);
$canOpenStaffManagement = hasAnyRole(['admin', 'cashier', 'chief_mechanic', 'service_adviser']);
?>
<div class="sidebar">

    <div class="sidebar-brand">
        <img src="<?php echo APP_URL; ?>/assets/images/logo.png" alt="The Autodok Logo" class="sidebar-logo">
        <div class="sidebar-brand-text">
            <div class="sidebar-brand-name">The Autodok</div>
            <div class="sidebar-brand-sub">Automotive Care Services</div>
        </div>
    </div>

    <div class="user-badge">
        <i class="bi bi-person-fill badge-icon"></i>
        <span><?php echo ucfirst($_SESSION['user_role'] ?? 'user'); ?></span>
    </div>

    <nav class="sidebar-nav">
        <?php if (!$isTechnician): ?>
        <a href="<?php echo APP_URL; ?>/views/dashboard/index.php"
           class="nav-item <?php echo strpos($p, '/dashboard/') !== false ? 'active' : ''; ?>">
            <i class="bi bi-grid-fill"></i>
            <span>Dashboard</span>
        </a>
        <?php endif; ?>

        <?php if ($isJobOrderOnlyRole): ?>
        <a href="<?php echo APP_URL; ?>/views/services/manage.php"
           class="nav-item <?php echo strpos($p, '/services/') !== false ? 'active' : ''; ?>">
            <i class="bi bi-wrench"></i>
            <span>Job Orders</span>
        </a>
        <?php else: ?>
        <a href="<?php echo APP_URL; ?>/views/services/manage.php"
           class="nav-item <?php echo strpos($p, '/services/') !== false ? 'active' : ''; ?>">
            <i class="bi bi-wrench"></i>
            <span>Services</span>
        </a>

        <?php if ($isAdminOrCashier): ?>
        <a href="<?php echo APP_URL; ?>/views/reports/index.php"
           class="nav-item <?php echo strpos($p, '/reports/') !== false ? 'active' : ''; ?>">
            <i class="bi bi-file-earmark-bar-graph"></i>
            <span>Report</span>
        </a>

          <a href="<?php echo APP_URL; ?>/views/inventory/index.php"
              class="nav-item <?php echo strpos($p, '/inventory/') !== false ? 'active' : ''; ?>">
                <i class="bi bi-box-seam"></i>
                <span>Inventory</span>
          </a>

                                        <?php if ($canOpenStaffManagement): ?>
          <a href="<?php echo APP_URL; ?>/views/staff/index.php"
              class="nav-item <?php echo strpos($p, '/staff/') !== false ? 'active' : ''; ?>">
                <i class="bi bi-people-fill"></i>
                <span>Staff Management</span>
          </a>
          <?php endif; ?>

                <?php if (hasAnyRole(['admin', 'cashier'])): ?>
        <a href="<?php echo APP_URL; ?>/views/settings/index.php"
           class="nav-item <?php echo strpos($p, '/settings/') !== false ? 'active' : ''; ?>">
            <i class="bi bi-gear"></i>
            <span>Settings</span>
        </a>
        <?php endif; ?>
                <?php endif; ?>
        <?php endif; ?>
    </nav>

    <div class="sidebar-footer">
        <a href="<?php echo APP_URL; ?>/views/auth/logout.php" class="nav-item logout">
            <i class="bi bi-box-arrow-left"></i>
            <span>Logout</span>
        </a>
    </div>

</div>
