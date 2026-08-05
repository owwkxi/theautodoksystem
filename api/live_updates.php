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

    // activity_logs captures most business mutations through logActivity().
    $activity = $db->fetch("SELECT COALESCE(MAX(created_at), '1970-01-01 00:00:00') AS last_activity, COUNT(*) AS total_logs FROM activity_logs");

    $userId = (int)$_SESSION['user_id'];
    $notif = $db->fetch(
        "SELECT COALESCE(MAX(created_at), '1970-01-01 00:00:00') AS last_notification,
                COUNT(*) AS unread_count
         FROM notifications
         WHERE user_id = ? AND is_read = 0",
        [$userId]
    );

    $tokenPayload = [
        'last_activity' => (string)($activity['last_activity'] ?? ''),
        'total_logs' => (int)($activity['total_logs'] ?? 0),
        'last_notification' => (string)($notif['last_notification'] ?? ''),
        'unread_count' => (int)($notif['unread_count'] ?? 0),
    ];

    echo json_encode([
        'success' => true,
        'token' => sha1(json_encode($tokenPayload)),
        'server_time' => gmdate('c'),
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Unable to get live update token']);
}
