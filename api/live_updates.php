<?php
/**
 * Lightweight live update token endpoint.
 * Returns a token that changes when key data changes.
 */

define('APP_ACCESS', true);
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/session.php';

header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

try {
    $db = Database::getInstance();

    // These tables are part of the application schema. Avoid SHOW COLUMNS on
    // every poll; it is expensive on shared hosting and can block navigation.
    $getTableSignal = static function ($table, $timeColumn) use ($db) {
        $allowedTables = ['activity_logs', 'job_orders', 'inventory_transactions', 'notifications'];
        if (!in_array($table, $allowedTables, true) || !in_array($timeColumn, ['created_at', 'updated_at'], true)) {
            return ['last_change' => '1970-01-01 00:00:00', 'total_rows' => 0, 'max_id' => 0];
        }

        $row = $db->fetch(
            "SELECT COALESCE(MAX({$timeColumn}), '1970-01-01 00:00:00') AS last_change,
                    COUNT(*) AS total_rows
             FROM {$table}"
        );
        return [
            'last_change' => (string)($row['last_change'] ?? '1970-01-01 00:00:00'),
            'total_rows' => (int)($row['total_rows'] ?? 0),
            'max_id' => 0,
        ];
    };

    // Global cross-role signals.
    $activity = $getTableSignal('activity_logs', 'created_at');
    $jobOrders = $getTableSignal('job_orders', 'updated_at');
    $inventoryTx = $getTableSignal('inventory_transactions', 'created_at');
    $notificationsGlobal = $getTableSignal('notifications', 'created_at');

    $userId = (int)$_SESSION['user_id'];
    $notif = $db->fetch(
        "SELECT COALESCE(MAX(created_at), '1970-01-01 00:00:00') AS last_notification,
                COUNT(*) AS unread_count
         FROM notifications
         WHERE user_id = ? AND is_read = 0",
        [$userId]
    );

    $tokenPayload = [
        'activity_last_change' => (string)($activity['last_change'] ?? ''),
        'activity_total_rows' => (int)($activity['total_rows'] ?? 0),
        'activity_max_id' => (int)($activity['max_id'] ?? 0),
        'job_orders_last_change' => (string)($jobOrders['last_change'] ?? ''),
        'job_orders_total_rows' => (int)($jobOrders['total_rows'] ?? 0),
        'job_orders_max_id' => (int)($jobOrders['max_id'] ?? 0),
        'inventory_last_change' => (string)($inventoryTx['last_change'] ?? ''),
        'inventory_total_rows' => (int)($inventoryTx['total_rows'] ?? 0),
        'inventory_max_id' => (int)($inventoryTx['max_id'] ?? 0),
        'notifications_last_change' => (string)($notificationsGlobal['last_change'] ?? ''),
        'notifications_total_rows' => (int)($notificationsGlobal['total_rows'] ?? 0),
        'notifications_max_id' => (int)($notificationsGlobal['max_id'] ?? 0),
        'last_notification' => (string)($notif['last_notification'] ?? ''),
        'unread_count' => (int)($notif['unread_count'] ?? 0),
    ];

    echo json_encode([
        'success' => true,
        'token' => sha1(json_encode($tokenPayload)),
        'server_time' => date('c'),
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Unable to get live update token']);
}
