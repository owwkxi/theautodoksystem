<?php
// local file
if (PHP_SAPI === 'cli-server') { 
    $requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    $requestPath = is_string($requestPath) ? $requestPath : '/';
    $basePath = trim((string)(getenv('APP_BASE_PATH') ?: ''), '/');

    if ($basePath !== '') {
        $basePrefix = '/' . $basePath;
        if ($requestPath === $basePrefix) {
            $requestPath = '/';
        } elseif (strpos($requestPath, $basePrefix . '/') === 0) {
            $requestPath = substr($requestPath, strlen($basePrefix));
        }
    }

    $route = trim($requestPath, '/');
    if ($route === 'index.php') {
        $route = '';
    }

    $routes = [
        'login' => 'views/auth/login.php',
        'logout' => 'views/auth/logout.php',
        'maintenance' => 'views/maintenance.php',
        'dashboard' => 'views/dashboard/index.php',
        'services' => 'views/services/manage.php',
        'reports' => 'views/reports/index.php',
        'inventory' => 'views/inventory/index.php',
        'staff' => 'views/staff/index.php',
        'settings' => 'views/settings/index.php',
        'settings/print-template' => 'views/settings/print_template.php',
        'settings/system-logo' => 'views/settings/system_logo.php',
        'settings/role-permissions' => 'views/settings/role_permissions.php',
        'settings/announcement' => 'views/settings/announcement.php',
        'settings/completion-email' => 'views/settings/completion_email.php',
        'settings/deleted-archive' => 'views/settings/deleted_archive.php',
        'settings/notes' => 'views/settings/notes.php',
        'attendance' => 'views/attendance/index.php',
        'attendance/nfc' => 'views/attendance/nfc.php',
        'profile' => 'views/profile/index.php',
        'job-orders' => 'views/job_orders/index.php',
        'job-orders/create' => 'views/job_orders/create.php',
        'job-orders/view' => 'views/job_orders/view.php',
        'job-orders/edit' => 'views/job_orders/edit.php',
    ];

    if (isset($routes[$route])) {
        require __DIR__ . '/' . $routes[$route];
        exit;
    }

    if ($route !== '') {
        http_response_code(404);
        exit('Not Found');
    }
}

define('APP_ACCESS', true);

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/Database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/session.php';

if (defined('MAINTENANCE_MODE') && MAINTENANCE_MODE) {
    redirect(routeUrl('maintenance'));
}

// Check if user is logged in
if (isLoggedIn()) {
    // Redirect to dashboard
    redirect(routeUrl('dashboard'));
} else {
    // Redirect to login with main shop as the default branch view
    redirect(routeUrl('login'));
}
