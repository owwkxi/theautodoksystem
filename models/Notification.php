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
        
        return $this->db->execute($sql, [$userId]);
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
        
        return $this->db->execute($sql, [$userId]);
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
            'message' => $message,
            'link' => '/views/profile/index.php'
        ]);
    }

    /**
     * Helper: Create cash advance notification
     */
    public static function notifyCashAdvance($userId, $amount, $status = 'approved') {
        $notification = new self();
        $title = $status === 'approved' ? 'Cash Advance Approved' : 'Cash Advance Reflected';
        $message = "Your cash advance of ₱" . number_format($amount, 2) . " has been " . $status . ".";
        
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
            'message' => "You have been assigned to Job Order #{$jobOrderNumber}",
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
            'message' => "Job Order #{$jobOrderNumber} status changed to {$statusText}",
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
            'message' => "Payment of ₱" . number_format($amount, 2) . " received for Job Order #{$jobOrderNumber}",
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
            ? "Your staff account has been created. {$details}" 
            : "Your staff account has been updated. {$details}";
        
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
