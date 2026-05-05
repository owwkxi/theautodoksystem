<?php
if (!defined('APP_ACCESS')) {
    die('Direct access not permitted');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' — ' . APP_NAME : APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/css/style.css?v=<?php echo time(); ?>">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body>
<div class="dashboard-wrapper">

    <?php include __DIR__ . '/sidebar.php'; ?>

    <div class="main-content">

        <!-- Top bar -->
        <div class="topbar">
            <div class="topbar-title"><?php echo isset($pageTitle) ? $pageTitle : 'Dashboard'; ?></div>
            <div class="topbar-user">
                <div class="topbar-avatar">
                    <?php echo strtoupper(substr($_SESSION['full_name'] ?? 'U', 0, 2)); ?>
                </div>
                <span class="topbar-name"><?php echo escape($_SESSION['full_name'] ?? 'User'); ?></span>
            </div>
        </div>

        <!-- Page body -->
        <div class="page-body">

            <?php if (function_exists('hasMessage') && hasMessage()):
                $msg = getMessage();
                $type = ($msg['type'] === 'error') ? 'danger' : $msg['type'];
            ?>
            <div class="alert alert-<?php echo $type; ?> alert-dismissible fade show mb-3" role="alert">
                <?php echo escape($msg['message']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>
