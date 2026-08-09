<?php
/**
 * Notification Model
 * Handles all notification operations
 */

class Notification {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Build the session key namespace for dynamic notification dismissal
     */
    private function getDynamicDismissedMap($userId) {
        if (!isset($_SESSION['dynamic_notifications_dismissed']) || !is_array($_SESSION['dynamic_notifications_dismissed'])) {
            $_SESSION['dynamic_notifications_dismissed'] = [];
        }

        if (!isset($_SESSION['dynamic_notifications_dismissed'][$userId]) || !is_array($_SESSION['dynamic_notifications_dismissed'][$userId])) {
            $_SESSION['dynamic_notifications_dismissed'][$userId] = [];
        }

        return $_SESSION['dynamic_notifications_dismissed'][$userId];
    }

    /**
     * Save dynamic dismissal signatures to session
     */
    private function setDynamicDismissedMap($userId, $map) {
        if (!isset($_SESSION['dynamic_notifications_dismissed']) || !is_array($_SESSION['dynamic_notifications_dismissed'])) {
            $_SESSION['dynamic_notifications_dismissed'] = [];
        }

        $_SESSION['dynamic_notifications_dismissed'][$userId] = $map;
    }

    private function normalizeRoleValue($role) {
        $normalized = strtolower(trim((string)$role));
        $aliases = [
            'system_administrator' => 'admin',
            'system_admin' => 'admin',
            'administrator' => 'admin',
        ];
        return $aliases[$normalized] ?? $normalized;
    }

    private function resolveUserRole($userId) {
        $sessionUserId = (int)($_SESSION['user_id'] ?? 0);
        if ($sessionUserId === (int)$userId && !empty($_SESSION['user_role'])) {
            return $this->normalizeRoleValue($_SESSION['user_role']);
        }

        $userRow = $this->db->fetch("SELECT role FROM users WHERE id = ? LIMIT 1", [(int)$userId]);
        if (!empty($userRow['role'])) {
            return $this->normalizeRoleValue($userRow['role']);
        }

        $staffRow = $this->db->fetch("SELECT role FROM staff WHERE id = ? LIMIT 1", [(int)$userId]);
        if (!empty($staffRow['role'])) {
            return $this->normalizeRoleValue($staffRow['role']);
        }

        return 'staff';
    }

    /**
     * Build dynamic notifications with deterministic signatures.
     * A dynamic notification only reappears when its signature changes.
     */
    private function buildDynamicNotifications($userId, $applyDismissed = true): array {
        $notifications = [];
        $dismissed = $this->getDynamicDismissedMap($userId);
        $userRole = $this->resolveUserRole($userId);

        $addDynamic = function ($id, $type, $title, $message, $link, $signature) use (&$notifications, $dismissed, $applyDismissed) {
            if ($applyDismissed && isset($dismissed[$id]) && $dismissed[$id] === $signature) {
                return;
            }

            $notifications[] = [
                'id' => $id,
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'link' => $link,
                'is_read' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'is_dynamic' => 1,
                'dynamic_signature' => $signature
            ];
        };

        if (in_array($userRole, ['admin', 'cashier'], true)) {
            $lowStockSql = "SELECT COUNT(*) as count FROM products WHERE status = 'active' AND quantity <= min_stock_level";
            $lowStockResult = $this->db->fetch($lowStockSql);
            $lowStockCount = (int)($lowStockResult['count'] ?? 0);

            if ($lowStockCount > 0) {
                $addDynamic(
                    'dyn_low_stock',
                    'system',
                    'Low Stock Alert',
                    $lowStockCount === 1 ? '1 product is low on stock.' : "{$lowStockCount} products are low on stock.",
                    '/views/inventory/index.php',
                    "low_stock:{$lowStockCount}"
                );
            }
        }

        if (in_array($userRole, ['admin', 'cashier', 'service_adviser'], true)) {
            $paidSql = "SELECT COUNT(*) as count FROM job_orders WHERE payment_status = 'paid' AND DATE(created_at) = CURDATE()";
            $paidResult = $this->db->fetch($paidSql);
            $paidCount = (int)($paidResult['count'] ?? 0);

            if ($paidCount > 0) {
                $addDynamic(
                    'dyn_paid_orders',
                    'payment',
                    'Paid Job Orders',
                    $paidCount === 1 ? '1 job order was paid today.' : "{$paidCount} job orders were paid today.",
                    '/views/job_orders/index.php?status=paid',
                    'paid_today:' . date('Y-m-d') . ":{$paidCount}"
                );
            }
        }

        if ($userRole === 'service_adviser') {
            $assignedSql = "SELECT COUNT(*) as count FROM job_orders WHERE service_adviser_id = ? AND status NOT IN ('completed', 'cancelled')";
            $assignedResult = $this->db->fetch($assignedSql, [$userId]);
            $assignedCount = (int)($assignedResult['count'] ?? 0);

            if ($assignedCount > 0) {
                $addDynamic(
                    'dyn_assigned_jobs',
                    'job_assigned',
                    'Assigned Job Orders',
                    $assignedCount === 1 ? 'You have 1 assigned job order.' : "You have {$assignedCount} assigned job orders.",
                    '/views/job_orders/index.php?assigned=me',
                    "assigned_jobs:{$assignedCount}"
                );
            }
        }

        if ($userRole === 'technician') {
            $techAssignedSql = "SELECT COUNT(DISTINCT jo.id) AS count
                                FROM job_orders jo
                                INNER JOIN job_order_technicians jot ON jot.job_order_id = jo.id
                                WHERE jot.technician_id = ? AND jo.status NOT IN ('completed', 'cancelled')";
            $techAssignedResult = $this->db->fetch($techAssignedSql, [$userId]);
            $techAssignedCount = (int)($techAssignedResult['count'] ?? 0);

            if ($techAssignedCount > 0) {
                $addDynamic(
                    'dyn_technician_assigned_jobs',
                    'job_assigned',
                    'Your Assigned Job Orders',
                    $techAssignedCount === 1 ? 'You have 1 active assigned job order.' : "You have {$techAssignedCount} active assigned job orders.",
                    '/views/services/manage.php?tab=job_orders',
                    "tech_assigned_jobs:{$techAssignedCount}"
                );
            }
        }

        return $notifications;
    }

    /**
     * Create a new notification
     */
    public function create($data) {
        $sql = "INSERT INTO notifications (user_id, type, title, message, link) 
                VALUES (?, ?, ?, ?, ?)";
        
        return $this->db->execute($sql, [
            $data['user_id'],
            $data['type'],
            $data['title'],
            $data['message'],
            $data['link'] ?? null
        ]);
    }

    /**
     * Get all notifications for a user
     */
    public function getUserNotifications($userId, $limit = 50) {
        $sql = "SELECT * FROM notifications 
                WHERE user_id = ? 
                ORDER BY created_at DESC 
                LIMIT ?";
        
        return $this->db->fetchAll($sql, [$userId, (int)$limit]);
    }

    /**
     * Get unread notifications count
     */
    public function getUnreadCount($userId) {
        $sql = "SELECT COUNT(*) as count FROM notifications 
                WHERE user_id = ? AND is_read = 0";
        
        $result = $this->db->fetch($sql, [$userId]);
        return (int)($result['count'] ?? 0);
    }

    /**
     * Get unread notifications
     */
    public function getUnreadNotifications($userId, $limit = 10) {
        $sql = "SELECT * FROM notifications 
                WHERE user_id = ? AND is_read = 0 
                ORDER BY created_at DESC 
                LIMIT ?";
        
        return $this->db->fetchAll($sql, [$userId, (int)$limit]);
    }

    /**
     * Get dynamic notifications based on live business conditions
     */
    public function getDynamicNotifications($userId) {
        return $this->buildDynamicNotifications($userId, true);
    }

    /**
     * Count dynamic notifications
     */
    public function countDynamicNotifications($userId) {
        return count($this->getDynamicNotifications($userId));
    }

    /**
     * Get unread notifications including dynamic system alerts
     */
    public function getUnreadNotificationsWithDynamic($userId, $limit = 10) {
        $dynamicNotifications = $this->getDynamicNotifications($userId);
        $unreadNotifications = $this->getUnreadNotifications($userId, $limit);

        if (count($dynamicNotifications) >= $limit) {
            return array_slice($dynamicNotifications, 0, $limit);
        }

        $remaining = $limit - count($dynamicNotifications);
        return array_merge($dynamicNotifications, array_slice($unreadNotifications, 0, $remaining));
    }

    /**
     * Get unread count including dynamic system alerts
     */
    public function getUnreadCountWithDynamic($userId) {
        return $this->getUnreadCount($userId) + $this->countDynamicNotifications($userId);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead($notificationId, $userId) {
        $sql = "UPDATE notifications 
                SET is_read = 1 
                WHERE id = ? AND user_id = ?";
        
        return $this->db->execute($sql, [$notificationId, $userId]);
    }

    /**
     * Mark all notifications as read for a user
     */
    public function markAllAsRead($userId) {
        $sql = "UPDATE notifications 
                SET is_read = 1 
                WHERE user_id = ? AND is_read = 0";

        $result = $this->db->execute($sql, [$userId]);
        $this->dismissCurrentDynamicNotifications($userId);
        return $result;
    }

    /**
     * Delete a notification
     */
    public function delete($notificationId, $userId) {
        $sql = "DELETE FROM notifications 
                WHERE id = ? AND user_id = ?";
        
        return $this->db->execute($sql, [$notificationId, $userId]);
    }

    /**
     * Delete all read notifications for a user
     */
    public function deleteAllRead($userId) {
        $sql = "DELETE FROM notifications 
                WHERE user_id = ? AND is_read = 1";
        
        return $this->db->execute($sql, [$userId]);
    }

    /**
     * Delete all notifications for a user
     */
    public function deleteAll($userId) {
        $sql = "DELETE FROM notifications 
                WHERE user_id = ?";

        $result = $this->db->execute($sql, [$userId]);
        $this->dismissCurrentDynamicNotifications($userId);
        return $result;
    }

    /**
     * Dismiss all currently visible dynamic notifications for this user/session.
     */
    public function dismissCurrentDynamicNotifications($userId) {
        $current = $this->buildDynamicNotifications($userId, false);
        $dismissed = $this->getDynamicDismissedMap($userId);

        foreach ($current as $item) {
            if (!empty($item['id']) && isset($item['dynamic_signature'])) {
                $dismissed[$item['id']] = $item['dynamic_signature'];
            }
        }

        $this->setDynamicDismissedMap($userId, $dismissed);
        return true;
    }

    /**
     * Dismiss a single dynamic notification by id for this user/session.
     */
    public function dismissDynamicById($userId, $dynamicId) {
        $current = $this->buildDynamicNotifications($userId, false);
        $dismissed = $this->getDynamicDismissedMap($userId);

        foreach ($current as $item) {
            if (($item['id'] ?? '') === $dynamicId && isset($item['dynamic_signature'])) {
                $dismissed[$dynamicId] = $item['dynamic_signature'];
                $this->setDynamicDismissedMap($userId, $dismissed);
                return true;
            }
        }

        return false;
    }

    /**
     * Delete notifications older than specified days
     */
    public function deleteOlderThan($userId, $days = 30) {
        $sql = "DELETE FROM notifications 
                WHERE user_id = ? 
                AND created_at < DATE_SUB(NOW(), INTERVAL ? DAY)";
        
        return $this->db->execute($sql, [$userId, (int)$days]);
    }

    /**
     * Auto-clear old notifications (run this periodically)
     */
    public static function autoCleanOldNotifications($days = 30) {
        $notification = new self();
        $sql = "DELETE FROM notifications 
                WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)";
        
        return $notification->db->execute($sql, [(int)$days]);
    }

    /**
     * Helper: Create account update notification
     */
    public static function notifyAccountUpdate($userId, $message) {
        $notification = new self();
        return $notification->create([
            'user_id' => $userId,
            'type' => 'account_update',
            'title' => 'Account Updated',
            'message' => function_exists('buildNotificationMessageTemplate')
                ? buildNotificationMessageTemplate('System', 'updated', 'your account', $message)
                : $message,
            'link' => '/views/profile/index.php'
        ]);
    }

    /**
     * Helper: Create cash advance notification
     */
    public static function notifyCashAdvance($userId, $amount, $status = 'approved') {
        $notification = new self();
        $title = $status === 'approved' ? 'Cash Advance Approved' : 'Cash Advance Reflected';
        $message = function_exists('buildNotificationMessageTemplate')
            ? buildNotificationMessageTemplate('System', $status === 'approved' ? 'approved' : 'updated', 'cash advance of ₱' . number_format($amount, 2), '')
            : "Your cash advance of ₱" . number_format($amount, 2) . " has been " . $status . ".";
        
        return $notification->create([
            'user_id' => $userId,
            'type' => 'cash_advance',
            'title' => $title,
            'message' => $message,
            'link' => '/views/cash_advance/index.php'
        ]);
    }

    /**
     * Helper: Create job assigned notification
     */
    public static function notifyJobAssigned($userId, $jobOrderId, $jobOrderNumber) {
        $notification = new self();
        return $notification->create([
            'user_id' => $userId,
            'type' => 'job_assigned',
            'title' => 'New Job Order Assigned',
            'message' => function_exists('buildNotificationMessageTemplate')
                ? buildNotificationMessageTemplate('System', 'assigned', 'job order #' . $jobOrderNumber)
                : "You have been assigned to Job Order #{$jobOrderNumber}",
            'link' => "/views/job_orders/view.php?id={$jobOrderId}"
        ]);
    }

    /**
     * Helper: Create job status change notification
     */
    public static function notifyJobStatus($userId, $jobOrderId, $jobOrderNumber, $status) {
        $notification = new self();
        $statusText = ucwords(str_replace('_', ' ', $status));
        
        return $notification->create([
            'user_id' => $userId,
            'type' => 'job_status',
            'title' => 'Job Order Status Updated',
            'message' => function_exists('buildNotificationMessageTemplate')
                ? buildNotificationMessageTemplate('System', 'updated', 'job order #' . $jobOrderNumber, 'Status: ' . $statusText)
                : "Job Order #{$jobOrderNumber} status changed to {$statusText}",
            'link' => "/views/job_orders/view.php?id={$jobOrderId}"
        ]);
    }

    /**
     * Helper: Create payment notification
     */
    public static function notifyPayment($userId, $jobOrderNumber, $amount) {
        $notification = new self();
        return $notification->create([
            'user_id' => $userId,
            'type' => 'payment',
            'title' => 'Payment Received',
            'message' => function_exists('buildNotificationMessageTemplate')
                ? buildNotificationMessageTemplate('System', 'received payment for', 'job order #' . $jobOrderNumber, 'Amount: ₱' . number_format($amount, 2))
                : "Payment of ₱" . number_format($amount, 2) . " received for Job Order #{$jobOrderNumber}",
            'link' => '/views/job_orders/index.php'
        ]);
    }

    /**
     * Helper: Create staff update notification
     */
    public static function notifyStaffUpdate($userId, $action, $details = '') {
        $notification = new self();
        $title = $action === 'created' ? 'Account Created' : 'Account Updated';
        $message = $action === 'created' 
            ? (function_exists('buildNotificationMessageTemplate')
                ? buildNotificationMessageTemplate('System', 'created', 'your staff account', $details)
                : "Your staff account has been created. {$details}")
            : (function_exists('buildNotificationMessageTemplate')
                ? buildNotificationMessageTemplate('System', 'updated', 'your staff account', $details)
                : "Your staff account has been updated. {$details}");
        
        return $notification->create([
            'user_id' => $userId,
            'type' => 'staff_update',
            'title' => $title,
            'message' => $message,
            'link' => '/views/profile/index.php'
        ]);
    }

    /**
     * Helper: Create system notification
     */
    public static function notifySystem($userId, $title, $message, $link = null) {
        $notification = new self();
        return $notification->create([
            'user_id' => $userId,
            'type' => 'system',
            'title' => $title,
            'message' => $message,
            'link' => $link
        ]);
    }

    /**
     * Broadcast notification to multiple users
     */
    public static function broadcast($userIds, $type, $title, $message, $link = null) {
        $notification = new self();
        $success = true;
        
        foreach ($userIds as $userId) {
            $result = $notification->create([
                'user_id' => $userId,
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'link' => $link
            ]);
            
            if (!$result) {
                $success = false;
            }
        }
        
        return $success;
    }
}
