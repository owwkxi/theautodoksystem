<?php
/**
 * Notifications API
 * Handles notification operations
 */

define('APP_ACCESS', true);
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../models/Notification.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$userId = $_SESSION['user_id'];

try {
    $notification = new Notification();

    switch ($method) {
        case 'GET':
            // Get notifications
            if (isset($_GET['action'])) {
                switch ($_GET['action']) {
                    case 'unread_count':
                        // Get unread count
                        $count = $notification->getUnreadCount($userId);
                        echo json_encode([
                            'success' => true,
                            'count' => $count
                        ]);
                        break;

                    case 'unread':
                        // Get unread notifications
                        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
                        $notifications = $notification->getUnreadNotifications($userId, $limit);
                        echo json_encode([
                            'success' => true,
                            'notifications' => $notifications
                        ]);
                        break;

                    case 'all':
                        // Get all notifications
                        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
                        $notifications = $notification->getUserNotifications($userId, $limit);
                        echo json_encode([
                            'success' => true,
                            'notifications' => $notifications
                        ]);
                        break;

                    default:
                        http_response_code(400);
                        echo json_encode(['success' => false, 'message' => 'Invalid action']);
                }
            } else {
                // Default: get unread notifications
                $notifications = $notification->getUnreadNotifications($userId);
                echo json_encode([
                    'success' => true,
                    'notifications' => $notifications
                ]);
            }
            break;

        case 'PUT':
            // Mark as read
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (isset($data['action'])) {
                switch ($data['action']) {
                    case 'mark_read':
                        if (isset($data['notification_id'])) {
                            $result = $notification->markAsRead($data['notification_id'], $userId);
                            echo json_encode([
                                'success' => $result,
                                'message' => $result ? 'Notification marked as read' : 'Failed to mark as read'
                            ]);
                        } else {
                            http_response_code(400);
                            echo json_encode(['success' => false, 'message' => 'Notification ID required']);
                        }
                        break;

                    case 'mark_all_read':
                        $result = $notification->markAllAsRead($userId);
                        echo json_encode([
                            'success' => $result,
                            'message' => $result ? 'All notifications marked as read' : 'Failed to mark all as read'
                        ]);
                        break;

                    default:
                        http_response_code(400);
                        echo json_encode(['success' => false, 'message' => 'Invalid action']);
                }
            } else {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Action required']);
            }
            break;

        case 'DELETE':
            // Delete notification
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (isset($data['action'])) {
                switch ($data['action']) {
                    case 'delete_all_read':
                        $result = $notification->deleteAllRead($userId);
                        echo json_encode([
                            'success' => $result,
                            'message' => $result ? 'All read notifications deleted' : 'Failed to delete notifications'
                        ]);
                        break;

                    case 'delete_all':
                        $result = $notification->deleteAll($userId);
                        echo json_encode([
                            'success' => $result,
                            'message' => $result ? 'All notifications deleted' : 'Failed to delete notifications'
                        ]);
                        break;

                    case 'auto_clear_old':
                        // Auto-clear notifications older than 1 month
                        $result = $notification->deleteOlderThan($userId, 30);
                        echo json_encode([
                            'success' => $result,
                            'message' => $result ? 'Old notifications cleared' : 'Failed to clear old notifications'
                        ]);
                        break;

                    default:
                        http_response_code(400);
                        echo json_encode(['success' => false, 'message' => 'Invalid action']);
                }
            } elseif (isset($data['notification_id'])) {
                $result = $notification->delete($data['notification_id'], $userId);
                echo json_encode([
                    'success' => $result,
                    'message' => $result ? 'Notification deleted' : 'Failed to delete notification'
                ]);
            } else {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Notification ID or action required']);
            }
            break;

        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}
