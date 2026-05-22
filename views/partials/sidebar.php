<?php
if (!defined('APP_ACCESS')) {
    die('Direct access not permitted');
}
$p = $_SERVER['PHP_SELF'];
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
        <a href="<?php echo APP_URL; ?>/views/dashboard/index.php"
           class="nav-item <?php echo strpos($p, '/dashboard/') !== false ? 'active' : ''; ?>">
            <i class="bi bi-grid-fill"></i>
            <span>Dashboard</span>
        </a>

        <a href="<?php echo APP_URL; ?>/views/services/manage.php"
           class="nav-item <?php echo strpos($p, '/services/') !== false ? 'active' : ''; ?>">
            <i class="bi bi-wrench"></i>
            <span>Services</span>
        </a>

        <?php if (hasAnyRole(['admin'])): ?>
        <a href="<?php echo APP_URL; ?>/views/staff/index.php"
           class="nav-item <?php echo strpos($p, '/staff/') !== false ? 'active' : ''; ?>">
            <i class="bi bi-people-fill"></i>
            <span>Staff Management</span>
        </a>
        <?php endif; ?>

        <a href="<?php echo APP_URL; ?>/views/inventory/index.php"
           class="nav-item <?php echo strpos($p, '/inventory/') !== false ? 'active' : ''; ?>">
            <i class="bi bi-box-seam"></i>
            <span>Inventory</span>
        </a>

        <a href="<?php echo APP_URL; ?>/views/reports/index.php"
           class="nav-item <?php echo strpos($p, '/reports/') !== false ? 'active' : ''; ?>">
            <i class="bi bi-file-earmark-bar-graph"></i>
            <span>Report</span>
        </a>
    </nav>

    <div class="sidebar-footer">
        <a href="<?php echo APP_URL; ?>/views/auth/logout.php" class="nav-item logout">
            <i class="bi bi-box-arrow-left"></i>
            <span>Logout</span>
        </a>
    </div>

</div>
