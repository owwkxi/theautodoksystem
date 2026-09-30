<?php
/**
 * User Model - Version 2.0
 * Simplified user management (admin and staff only)
 * Handles user-related database operations with enhanced security
 */

class User {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Create a new user
     * @param array $data User data (username, password, role)
     * @return int|false User ID on success, false on failure
     */
    public function create($data) {
        $sql = "INSERT INTO users (username, password, full_name, email, role, status) VALUES (?, ?, ?, ?, ?, ?)";
        
        // Hash password securely
        $hashedPassword = password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]);
        $role = (($data['role'] ?? 'admin') === 'admin') ? 'admin' : 'admin';
        $fullName = trim((string)($data['full_name'] ?? $data['username'] ?? 'User'));
        $email = $data['email'] ?? null;
        $status = (($data['status'] ?? 'active') === 'inactive') ? 'inactive' : 'active';
        
        try {
            $this->db->query($sql, [
                $data['username'],
                $hashedPassword,
                $fullName,
                $email,
                $role,
                $status
            ]);
            
            return $this->db->lastInsertId();
        } catch (Exception $e) {
            error_log("User creation error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Find user by ID
     * @param int $id User ID
     * @return array|false User data without password
     */
    public function findById($id) {
        $sql = "SELECT id, username, role, created_at FROM users WHERE id = ?";
        return $this->db->fetch($sql, [$id]);
    }

    /**
     * Find user by username (includes password for authentication)
     */
    public function findByUsername($username) {
        $sql = "SELECT id, username, password, full_name, email, role, status FROM users WHERE username = ?";
        return $this->db->fetch($sql, [$username]);
    }

    /**
     * Authenticate user with username and password
     */
    public function authenticate($username, $password) {
        $user = $this->findByUsername($username);

        if (!$user) return false;

        if ($user['status'] !== 'active') return false;

        if (!password_verify($password, $user['password'])) return false;

        unset($user['password']);
        return $user;
    }

    /**
     * Get all users with optional filters
     * @param array $filters Optional filters (role, search, limit, offset)
     * @return array List of users
     */
    public function getAll($filters = []) {
        $sql = "SELECT id, username, role, created_at FROM users WHERE 1=1";
        $params = [];

        if (!empty($filters['role'])) {
            $sql .= " AND role = ?";
            $params[] = $filters['role'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND username LIKE ?";
            $params[] = '%' . $filters['search'] . '%';
        }

        $sql .= " ORDER BY created_at DESC";

        if (!empty($filters['limit'])) {
            $sql .= " LIMIT ?";
            $params[] = (int)$filters['limit'];
            
            if (!empty($filters['offset'])) {
                $sql .= " OFFSET ?";
                $params[] = (int)$filters['offset'];
            }
        }

        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Get technicians (for backward compatibility - returns empty array)
     * @return array Empty array (no technicians in simplified version)
     */
    public function getTechnicians() {
        // Return empty array since we removed technician role
        return [];
    }

    /**
     * Update user information
     * @param int $id User ID
     * @param array $data Data to update
     * @return bool Success status
     */
    public function update($id, $data) {
        $fields = [];
        $params = [];

        if (isset($data['username'])) {
            $fields[] = "username = ?";
            $params[] = $data['username'];
        }

        if (isset($data['role'])) {
            $fields[] = "role = ?";
            $params[] = $data['role'];
        }

        if (isset($data['password'])) {
            $fields[] = "password = ?";
            $params[] = password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]);
        }

        if (empty($fields)) {
            return false;
        }

        $params[] = $id;
        $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?";

        try {
            $this->db->query($sql, $params);
            return true;
        } catch (Exception $e) {
            error_log("User update error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete user
     * @param int $id User ID
     * @return bool Success status
     */
    public function delete($id) {
        $sql = "DELETE FROM users WHERE id = ?";
        try {
            $this->db->query($sql, [$id]);
            return true;
        } catch (Exception $e) {
            error_log("User deletion error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Count users with optional filters
     * @param array $filters Optional filters
     * @return int Total count
     */
    public function count($filters = []) {
        $sql = "SELECT COUNT(*) as total FROM users WHERE 1=1";
        $params = [];

        if (!empty($filters['role'])) {
            $sql .= " AND role = ?";
            $params[] = $filters['role'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND username LIKE ?";
            $params[] = '%' . $filters['search'] . '%';
        }

        $result = $this->db->fetch($sql, $params);
        return $result['total'] ?? 0;
    }

    /**
     * Check if username exists
     * @param string $username Username to check
     * @param int|null $excludeId User ID to exclude from check
     * @return bool True if exists, false otherwise
     */
    public function usernameExists($username, $excludeId = null) {
        $sql = "SELECT COUNT(*) as count FROM users WHERE username = ?";
        $params = [$username];

        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }

        $result = $this->db->fetch($sql, $params);
        return $result['count'] > 0;
    }

    /**
     * Check if email exists (for backward compatibility - always returns false)
     * @param string $email Email to check
     * @param int|null $excludeId User ID to exclude from check
     * @return bool Always false (no email field in simplified version)
     */
    public function emailExists($email, $excludeId = null) {
        // Return false since we removed email field
        return false;
    }

    /**
     * Change user password
     * @param int $id User ID
     * @param string $newPassword New plain text password
     * @return bool Success status
     */
    public function changePassword($id, $newPassword) {
        $sql = "UPDATE users SET password = ? WHERE id = ?";
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]);
        
        try {
            $this->db->query($sql, [$hashedPassword, $id]);
            return true;
        } catch (Exception $e) {
            error_log("Password change error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get user statistics
     * @return array Statistics data
     */
    public function getStats() {
        $sql = "SELECT 
                    COUNT(*) as total_users,
                    SUM(CASE WHEN role = 'admin' THEN 1 ELSE 0 END) as admin_count,
                    0 as staff_count
                FROM users";
        
        return $this->db->fetch($sql);
    }
}
