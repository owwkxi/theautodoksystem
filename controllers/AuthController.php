<?php
/**
 * Authentication Controller
 * Handles user authentication and registration
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../models/User.php';

class AuthController {
    private $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    /**
     * Handle login — checks both users table (admin) and staff table (technicians etc.)
     */
    public function login($loginId, $password) {
        if (empty($loginId) || empty($password)) {
            return ['success' => false, 'message' => 'ID and password are required'];
        }

        // ── 1. Try admin users table first ───────────────────────────────────
        $user = $this->userModel->authenticate($loginId, $password);

        if ($user) {
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['username']  = $user['username'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['full_name'] = $user['full_name'] ?? $user['username'];
            $_SESSION['user_type'] = 'admin';

            session_regenerate_id(true);
            logActivity($user['id'], 'login', 'Admin logged in successfully');

            return ['success' => true, 'message' => 'Login successful', 'user' => $user];
        }

        // ── 2. Try staff table (technicians, cashiers, etc.) ─────────────────
        $db   = Database::getInstance();
        $staff = $db->fetch(
            "SELECT id, staff_id, first_name, last_name, full_name, username, password, role, status
             FROM staff WHERE staff_id = ? OR username = ? LIMIT 1",
            [$loginId, $loginId]
        );

        if (!$staff) {
            logActivity(0, 'failed_login', "Failed login attempt for ID: {$loginId}");
            return ['success' => false, 'message' => 'Invalid ID or password'];
        }

        if ($staff['status'] !== 'active') {
            return ['success' => false, 'message' => 'Your account is inactive. Please contact the administrator.'];
        }

        if (empty($staff['password']) || !password_verify($password, $staff['password'])) {
            logActivity(0, 'failed_login', "Failed login attempt for staff ID: {$loginId}");
            return ['success' => false, 'message' => 'Invalid ID or password'];
        }

        // Set session for staff
        $_SESSION['user_id']   = $staff['id'];
        $_SESSION['username']  = $staff['staff_id'];
        $_SESSION['user_role'] = $staff['role'];
        $_SESSION['full_name'] = $staff['full_name'] ?: trim($staff['first_name'] . ' ' . $staff['last_name']);
        $_SESSION['staff_id']  = $staff['staff_id'];
        $_SESSION['user_type'] = 'staff';

        session_regenerate_id(true);
        logActivity($staff['id'], 'login', "Staff ({$staff['role']}) logged in successfully");

        return ['success' => true, 'message' => 'Login successful'];
    }

    /**
     * Handle registration
     */
    public function register($data) {
        // Validate required fields
        $required = ['username', 'email', 'password', 'full_name'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                return [
                    'success' => false,
                    'message' => ucfirst($field) . ' is required'
                ];
            }
        }

        // Validate email
        if (!isValidEmail($data['email'])) {
            return [
                'success' => false,
                'message' => 'Invalid email address'
            ];
        }

        // Validate password strength
        if (strlen($data['password']) < 6) {
            return [
                'success' => false,
                'message' => 'Password must be at least 6 characters long'
            ];
        }

        // Check if username already exists
        if ($this->userModel->usernameExists($data['username'])) {
            return [
                'success' => false,
                'message' => 'Username already exists'
            ];
        }

        // Check if email already exists
        if ($this->userModel->emailExists($data['email'])) {
            return [
                'success' => false,
                'message' => 'Email already exists'
            ];
        }

        // Create user
        $userId = $this->userModel->create($data);

        if (!$userId) {
            return [
                'success' => false,
                'message' => 'Registration failed. Please try again.'
            ];
        }

        // Log registration
        logActivity($userId, 'register', 'New user registered');

        return [
            'success' => true,
            'message' => 'Registration successful',
            'user_id' => $userId
        ];
    }

    /**
     * Handle logout
     */
    public function logout() {
        if (isset($_SESSION['user_id'])) {
            logActivity($_SESSION['user_id'], 'logout', 'User logged out');
        }

        // Clear session
        session_unset();
        session_destroy();

        return [
            'success' => true,
            'message' => 'Logout successful'
        ];
    }

    /**
     * Check if user is authenticated
     */
    public function isAuthenticated() {
        return isLoggedIn();
    }

    /**
     * Get current user — checks both users and staff tables
     */
    public function getCurrentUser() {
        if (!isLoggedIn()) return null;

        if (($_SESSION['user_type'] ?? '') === 'staff') {
            $db = Database::getInstance();
            return $db->fetch(
                "SELECT id, username, full_name, role, 'staff' AS user_type FROM staff WHERE id = ?",
                [$_SESSION['user_id']]
            );
        }

        return $this->userModel->findById($_SESSION['user_id']);
    }

    /**
     * Change password
     */
    public function changePassword($userId, $currentPassword, $newPassword) {
        // Get user
        $user = $this->userModel->findById($userId);
        if (!$user) {
            return [
                'success' => false,
                'message' => 'User not found'
            ];
        }

        // Verify current password
        $userWithPassword = $this->userModel->findByUsername($user['username']);
        if (!verifyPassword($currentPassword, $userWithPassword['password'])) {
            return [
                'success' => false,
                'message' => 'Current password is incorrect'
            ];
        }

        // Validate new password
        if (strlen($newPassword) < 6) {
            return [
                'success' => false,
                'message' => 'New password must be at least 6 characters long'
            ];
        }

        // Update password
        $updated = $this->userModel->update($userId, ['password' => $newPassword]);

        if (!$updated) {
            return [
                'success' => false,
                'message' => 'Failed to update password'
            ];
        }

        // Log password change
        logActivity($userId, 'change_password', 'User changed password');

        return [
            'success' => true,
            'message' => 'Password changed successfully'
        ];
    }
}
