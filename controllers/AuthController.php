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
     * Handle login
     */
    public function login($username, $password) {
        // Validate inputs
        if (empty($username) || empty($password)) {
            return [
                'success' => false,
                'message' => 'Username and password are required'
            ];
        }

        // Authenticate user
        $user = $this->userModel->authenticate($username, $password);

        if (!$user) {
            // Log failed login attempt
            logActivity(0, 'failed_login', "Failed login attempt for username: {$username}");
            
            return [
                'success' => false,
                'message' => 'Invalid username or password'
            ];
        }

        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['full_name'] = $user['username']; // Use username as display name

        // Regenerate session ID for security
        session_regenerate_id(true);

        // Log successful login
        logActivity($user['id'], 'login', 'User logged in successfully');

        return [
            'success' => true,
            'message' => 'Login successful',
            'user' => $user
        ];
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
     * Get current user
     */
    public function getCurrentUser() {
        if (!isLoggedIn()) {
            return null;
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
