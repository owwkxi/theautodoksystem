<?php
/**
 * Staff Model
 * Handles staff-related database operations
 */

class Staff {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Split a full name into first and last name values
     * @param string $fullName
     * @return array [first_name, last_name]
     */
    private function splitFullName($fullName) {
        $parts = preg_split('/\s+/', trim($fullName));
        $firstName = $parts[0] ?? '';
        $lastName = count($parts) > 1 ? implode(' ', array_slice($parts, 1)) : '';
        return [$firstName, $lastName];
    }

    /**
     * Generate unique staff ID
     * @return string Staff ID in 5 random-digit format
     */
    private function generateStaffId() {
        for ($attempt = 0; $attempt < 50; $attempt++) {
            $candidate = str_pad((string) random_int(0, 99999), 5, '0', STR_PAD_LEFT);
            if (!$this->findByStaffId($candidate) && !$this->usernameExists($candidate)) {
                return $candidate;
            }
        }

        throw new RuntimeException('Unable to generate unique staff ID');
    }

    /**
     * Create a new staff member
     * @param array $data Staff data
     * @return int|false Staff ID on success, false on failure
     */
    public function create($data) {
        // Check if email already exists
        if ($this->emailExists($data['email'])) {
            return false;
        }

        list($firstName, $lastName) = $this->splitFullName($data['full_name']);
        $staffId = $this->generateStaffId();
        $sql = "INSERT INTO staff (staff_id, first_name, last_name, username, password, email, phone, address, role, profile_photo, hire_date, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $hashedPassword = password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => PASSWORD_COST]);
        $hireDate = $data['hire_date'] ?? date('Y-m-d');
        
        try {
            $this->db->query($sql, [
                $staffId,
                $firstName,
                $lastName,
                $staffId,
                $hashedPassword,
                $data['email'],
                $data['contact_number'],
                $data['address'] ?? null,
                $data['role'] ?? 'Staff',
                $data['profile_image'] ?? null,
                $hireDate,
                $data['status'] ?? 'active'
            ]);
            
            return $this->db->lastInsertId();
        } catch (Exception $e) {
            error_log("Staff creation error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Find staff by ID
     * @param int $id Staff ID
     * @return array|false Staff data
     */
    public function findById($id) {
        $sql = "SELECT staff.*, phone AS contact_number, profile_photo AS profile_image FROM staff WHERE id = ?";
        return $this->db->fetch($sql, [$id]);
    }

    /**
     * Find staff by staff ID
        * @param string $staffId Staff ID (e.g., 04217)
     * @return array|false Staff data
     */
    public function findByStaffId($staffId) {
        $sql = "SELECT staff.*, phone AS contact_number, profile_photo AS profile_image FROM staff WHERE staff_id = ?";
        return $this->db->fetch($sql, [$staffId]);
    }

    /**
     * Find staff by username
     * @param string $username Username
     * @return array|false Staff data
     */
    public function findByUsername($username) {
        $sql = "SELECT * FROM staff WHERE username = ?";
        return $this->db->fetch($sql, [$username]);
    }

    /**
     * Find staff by email
     * @param string $email Email
     * @return array|false Staff data
     */
    public function findByEmail($email) {
        $sql = "SELECT * FROM staff WHERE email = ?";
        return $this->db->fetch($sql, [$email]);
    }

    /**
     * Get all staff with optional filters
     * @param array $filters Optional filters (role, status, search, limit, offset)
     * @return array List of staff
     */
    public function getAll($filters = []) {
        $sql = "SELECT staff.*, phone AS contact_number, profile_photo AS profile_image FROM staff WHERE 1=1";
        $params = [];

        if (!empty($filters['role'])) {
            $sql .= " AND role = ?";
            $params[] = $filters['role'];
        }

        if (!empty($filters['status'])) {
            $sql .= " AND status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (CONCAT(first_name, ' ', last_name) LIKE ? OR first_name LIKE ? OR last_name LIKE ? OR username LIKE ? OR email LIKE ? OR staff_id LIKE ? OR phone LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        $sql .= " ORDER BY created_at DESC";

        if (!empty($filters['limit'])) {
            $sql .= " LIMIT ?";
            $params[] = (int)$filters['limit'];
            
            if (isset($filters['offset'])) {
                $sql .= " OFFSET ?";
                $params[] = (int)$filters['offset'];
            }
        }

        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Update staff information
     * @param int $id Staff ID
     * @param array $data Data to update
     * @return bool Success status
     */
    public function update($id, $data) {
        $fields = [];
        $params = [];

        if (isset($data['full_name'])) {
            list($firstName, $lastName) = $this->splitFullName($data['full_name']);
            $fields[] = "first_name = ?";
            $params[] = $firstName;
            $fields[] = "last_name = ?";
            $params[] = $lastName;
        }

        if (isset($data['username'])) {
            // Check if username exists for other staff
            if ($this->usernameExists($data['username'], $id)) {
                return false;
            }
            $fields[] = "username = ?";
            $params[] = $data['username'];
        }

        if (isset($data['email'])) {
            // Check if email exists for other staff
            if ($this->emailExists($data['email'], $id)) {
                return false;
            }
            $fields[] = "email = ?";
            $params[] = $data['email'];
        }

        if (isset($data['contact_number'])) {
            $fields[] = "phone = ?";
            $params[] = $data['contact_number'];
        }

        if (isset($data['address'])) {
            $fields[] = "address = ?";
            $params[] = $data['address'];
        }

        if (isset($data['role'])) {
            $fields[] = "role = ?";
            $params[] = $data['role'];
        }

        if (isset($data['profile_image'])) {
            $fields[] = "profile_photo = ?";
            $params[] = $data['profile_image'];
        }

        if (isset($data['status'])) {
            $fields[] = "status = ?";
            $params[] = $data['status'];
        }

        if (isset($data['password']) && !empty($data['password'])) {
            $fields[] = "password = ?";
            $params[] = password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => PASSWORD_COST]);
        }

        if (empty($fields)) {
            return false;
        }

        $params[] = $id;
        $sql = "UPDATE staff SET " . implode(', ', $fields) . " WHERE id = ?";

        try {
            $this->db->query($sql, $params);
            return true;
        } catch (Exception $e) {
            error_log("Staff update error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete staff
     * @param int $id Staff ID
     * @return bool Success status
     */
    public function delete($id) {
        $sql = "DELETE FROM staff WHERE id = ?";
        try {
            $this->db->query($sql, [$id]);
            return true;
        } catch (Exception $e) {
            error_log("Staff deletion error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Toggle staff status (active/inactive)
     * @param int $id Staff ID
     * @return bool Success status
     */
    public function toggleStatus($id) {
        $sql = "UPDATE staff SET status = IF(status = 'active', 'inactive', 'active') WHERE id = ?";
        try {
            $this->db->query($sql, [$id]);
            return true;
        } catch (Exception $e) {
            error_log("Staff status toggle error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Count staff with optional filters
     * @param array $filters Optional filters
     * @return int Total count
     */
    public function count($filters = []) {
        $sql = "SELECT COUNT(*) as total FROM staff WHERE 1=1";
        $params = [];

        if (!empty($filters['role'])) {
            $sql .= " AND role = ?";
            $params[] = $filters['role'];
        }

        if (!empty($filters['status'])) {
            $sql .= " AND status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (CONCAT(first_name, ' ', last_name) LIKE ? OR first_name LIKE ? OR last_name LIKE ? OR username LIKE ? OR email LIKE ? OR staff_id LIKE ? OR phone LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        $result = $this->db->fetch($sql, $params);
        return $result['total'] ?? 0;
    }

    /**
     * Check if username exists
     * @param string $username Username to check
     * @param int|null $excludeId Staff ID to exclude from check
     * @return bool True if exists, false otherwise
     */
    public function usernameExists($username, $excludeId = null) {
        $sql = "SELECT COUNT(*) as count FROM staff WHERE username = ?";
        $params = [$username];

        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }

        $result = $this->db->fetch($sql, $params);
        return $result['count'] > 0;
    }

    /**
     * Check if email exists
     * @param string $email Email to check
     * @param int|null $excludeId Staff ID to exclude from check
     * @return bool True if exists, false otherwise
     */
    public function emailExists($email, $excludeId = null) {
        $sql = "SELECT COUNT(*) as count FROM staff WHERE email = ?";
        $params = [$email];

        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }

        $result = $this->db->fetch($sql, $params);
        return $result['count'] > 0;
    }

    /**
     * Get staff statistics
     * @return array Statistics data
     */
    public function getStats() {
        $sql = "SELECT 
                    COUNT(*) as total_staff,
                    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_staff,
                    SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) as inactive_staff,
                    SUM(CASE WHEN role = 'Admin' THEN 1 ELSE 0 END) as admin_count,
                    SUM(CASE WHEN role = 'Staff' THEN 1 ELSE 0 END) as staff_count,
                    SUM(CASE WHEN role = 'Technician' THEN 1 ELSE 0 END) as technician_count,
                    SUM(CASE WHEN role = 'Manager' THEN 1 ELSE 0 END) as manager_count
                FROM staff";
        
        return $this->db->fetch($sql);
    }

    /**
     * Authenticate staff with login ID and password
     * @param string $loginId Staff ID or legacy username
     * @param string $password Plain text password
     * @return array|false Staff data without password on success, false on failure
     */
    public function authenticate($loginId, $password) {
        $staff = $this->db->fetch(
            "SELECT * FROM staff WHERE staff_id = ? OR username = ? LIMIT 1",
            [$loginId, $loginId]
        );
        
        if (!$staff) {
            return false;
        }

        // Check if staff is active
        if ($staff['status'] !== 'active') {
            return false;
        }

        // Verify password
        if (!password_verify($password, $staff['password'])) {
            return false;
        }

        // Remove password from returned data
        unset($staff['password']);
        return $staff;
    }

    /**
     * Change staff password
     * @param int $id Staff ID
     * @param string $newPassword New plain text password
     * @return bool Success status
     */
    public function changePassword($id, $newPassword) {
        $sql = "UPDATE staff SET password = ? WHERE id = ?";
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => PASSWORD_COST]);
        
        try {
            $this->db->query($sql, [$hashedPassword, $id]);
            return true;
        } catch (Exception $e) {
            error_log("Password change error: " . $e->getMessage());
            return false;
        }
    }
}
