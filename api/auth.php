<?php
/**
 * Authentication API
 * RESTful API for user authentication
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

define('APP_ACCESS', true);

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../models/User.php';

// Get request method and action
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

// Initialize response
$response = [
    'success' => false,
    'message' => '',
    'data' => null
];

try {
    $userModel = new User();

    switch ($method) {
        case 'POST':
            // Get JSON input
            $input = json_decode(file_get_contents('php://input'), true);

            if ($action === 'login') {
                // Login
                if (empty($input['username']) || empty($input['password'])) {
                    throw new Exception('Username and password are required');
                }

                $user = $userModel->authenticate($input['username'], $input['password']);

                if (!$user) {
                    throw new Exception('Invalid username or password');
                }

                // Generate JWT token
                $token = generateJWT([
                    'user_id' => $user['id'],
                    'username' => $user['username'],
                    'role' => $user['role']
                ]);

                // Log activity
                logActivity($user['id'], 'api_login', 'User logged in via API');

                $response['success'] = true;
                $response['message'] = 'Login successful';
                $response['data'] = [
                    'token' => $token,
                    'user' => $user
                ];

            } elseif ($action === 'register') {
                // Register
                $required = ['username', 'email', 'password', 'full_name'];
                foreach ($required as $field) {
                    if (empty($input[$field])) {
                        throw new Exception(ucfirst($field) . ' is required');
                    }
                }

                // Validate email
                if (!isValidEmail($input['email'])) {
                    throw new Exception('Invalid email address');
                }

                // Check if username exists
                if ($userModel->usernameExists($input['username'])) {
                    throw new Exception('Username already exists');
                }

                // Check if email exists
                if ($userModel->emailExists($input['email'])) {
                    throw new Exception('Email already exists');
                }

                // Create user
                $userId = $userModel->create($input);

                if (!$userId) {
                    throw new Exception('Registration failed');
                }

                // Log activity
                logActivity($userId, 'api_register', 'User registered via API');

                $response['success'] = true;
                $response['message'] = 'Registration successful';
                $response['data'] = ['user_id' => $userId];

            } elseif ($action === 'verify') {
                // Verify token
                $token = getBearerToken();

                if (!$token) {
                    throw new Exception('No token provided');
                }

                $payload = verifyJWT($token);

                if (!$payload) {
                    throw new Exception('Invalid or expired token');
                }

                $user = $userModel->findById($payload['user_id']);

                if (!$user) {
                    throw new Exception('User not found');
                }

                $response['success'] = true;
                $response['message'] = 'Token is valid';
                $response['data'] = ['user' => $user];

            } else {
                throw new Exception('Invalid action');
            }
            break;

        default:
            throw new Exception('Method not allowed');
    }

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    http_response_code(400);
}

echo json_encode($response);
