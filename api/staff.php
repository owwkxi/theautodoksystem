<?php
/**
 * Staff API Endpoint
 * Handles CRUD operations for staff management
 */

define('APP_ACCESS', true);
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../models/Staff.php';

// Set JSON header
header('Content-Type: application/json');

// Require login and admin role
if (!isLoggedIn()) {
    jsonResponse(['success' => false, 'message' => 'Unauthorized access'], 401);
}

if (!hasAnyRole(['admin', 'cashier', 'chief_mechanic', 'service_adviser'])) {
    jsonResponse(['success' => false, 'message' => 'Insufficient permissions'], 403);
}

$currentRole = $_SESSION['user_role'] ?? '';
$isCashier = ($currentRole === 'cashier');
$canManageStaff = hasAnyRole(['admin', 'cashier']);

$staffModel = new Staff();
$method = $_SERVER['REQUEST_METHOD'];

// Allow method override for multipart form PUT requests
if ($method === 'POST' && !empty($_POST['_method'])) {
    $method = strtoupper($_POST['_method']);
}

try {
    switch ($method) {
        case 'GET':
            handleGet($staffModel);
            break;
        
        case 'POST':
            if (!$canManageStaff) {
                jsonResponse(['success' => false, 'message' => 'Insufficient permissions'], 403);
            }
            handlePost($staffModel);
            break;
        
        case 'PUT':
            if (!$canManageStaff) {
                jsonResponse(['success' => false, 'message' => 'Insufficient permissions'], 403);
            }
            handlePut($staffModel);
            break;
        
        case 'DELETE':
            if (!$canManageStaff) {
                jsonResponse(['success' => false, 'message' => 'Insufficient permissions'], 403);
            }
            handleDelete($staffModel);
            break;
        
        default:
            jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
    }
} catch (Exception $e) {
    error_log("Staff API Error: " . $e->getMessage());
    jsonResponse(['success' => false, 'message' => 'An error occurred'], 500);
}

/**
 * Handle GET requests
 */
function handleGet($staffModel) {
    // Get single staff by ID
    if (isset($_GET['id'])) {
        $staff = $staffModel->findById($_GET['id']);
        
        if (!$staff) {
            jsonResponse(['success' => false, 'message' => 'Staff not found'], 404);
        }
        
        // Remove password from response
        unset($staff['password']);

        $staff['assigned_job_orders'] = [];
        if (($staff['role'] ?? '') === 'technician') {
            $db = Database::getInstance();
            $assignedJobOrders = $db->fetchAll(
                "SELECT jo.id,
                        jo.job_order_number,
                        jo.status,
                        jo.created_at,
                        jo.status_timer_seconds,
                        jo.status_timer_started_at,
                        c.full_name AS customer_name,
                        v.plate_number
                 FROM job_orders jo
                 INNER JOIN job_order_technicians jot ON jot.job_order_id = jo.id
                 LEFT JOIN customers c ON c.id = jo.customer_id
                 LEFT JOIN vehicles v ON v.id = jo.vehicle_id
                 WHERE jot.technician_id = ?
                 ORDER BY jo.created_at DESC",
                [(int)$staff['id']]
            );

            foreach ($assignedJobOrders as &$jo) {
                $elapsedSeconds = (int)($jo['status_timer_seconds'] ?? 0);
                if (in_array($jo['status'] ?? '', ['ongoing', 'under_inspection'], true) && !empty($jo['status_timer_started_at'])) {
                    $elapsedSeconds += max(0, time() - strtotime($jo['status_timer_started_at']));
                }
                $hours = floor($elapsedSeconds / 3600);
                $minutes = floor(($elapsedSeconds % 3600) / 60);
                $seconds = $elapsedSeconds % 60;
                $jo['elapsed_display'] = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
            }
            unset($jo);

            $staff['assigned_job_orders'] = $assignedJobOrders;
        }
        
        jsonResponse(['success' => true, 'data' => $staff]);
    }
    
    // Get all staff with filters
    $filters = [
        'role' => $_GET['role'] ?? '',
        'status' => $_GET['status'] ?? '',
        'search' => $_GET['search'] ?? '',
        'limit' => $_GET['limit'] ?? RECORDS_PER_PAGE,
        'offset' => $_GET['offset'] ?? 0
    ];
    
    $staffList = $staffModel->getAll($filters);
    $totalRecords = $staffModel->count($filters);
    
    // Remove passwords from all staff records
    foreach ($staffList as &$staff) {
        unset($staff['password']);
    }
    
    jsonResponse([
        'success' => true,
        'data' => $staffList,
        'total' => $totalRecords
    ]);
}

/**
 * Handle POST requests (Create)
 */
function handlePost($staffModel) {
    global $isCashier;
    // Validate required fields
    $requiredFields = ['full_name', 'password', 'email', 'contact_number', 'role'];
    foreach ($requiredFields as $field) {
        if (empty($_POST[$field])) {
            jsonResponse(['success' => false, 'message' => ucfirst(str_replace('_', ' ', $field)) . ' is required'], 400);
        }
    }
    
    // Validate password confirmation
    if ($_POST['password'] !== $_POST['confirm_password']) {
        jsonResponse(['success' => false, 'message' => 'Passwords do not match'], 400);
    }
    
    // Validate password strength
    if (strlen($_POST['password']) < 6) {
        jsonResponse(['success' => false, 'message' => 'Password must be at least 6 characters'], 400);
    }
    
    // Validate email format
    if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        jsonResponse(['success' => false, 'message' => 'Invalid email format'], 400);
    }
    
    // Check if email already exists
    if ($staffModel->emailExists($_POST['email'])) {
        jsonResponse(['success' => false, 'message' => 'Email already exists'], 400);
    }

    // Validate role value
    $allowedRoles = ['admin', 'cashier', 'chief_mechanic', 'service_adviser', 'technician'];
    if (!in_array($_POST['role'], $allowedRoles, true)) {
        jsonResponse(['success' => false, 'message' => 'Invalid staff role'], 400);
    }
    if ($_POST['role'] === 'admin' && !hasRole('admin')) {
        jsonResponse(['success' => false, 'message' => 'Only admin can assign admin role'], 403);
    }
    
    // Handle profile image upload
    $profileImage = null;
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $uploadResult = uploadFile($_FILES['profile_image'], ['jpg', 'jpeg', 'png'], MAX_FILE_SIZE);
        
        if (!$uploadResult['success']) {
            jsonResponse(['success' => false, 'message' => $uploadResult['message']], 400);
        }
        
        $profileImage = $uploadResult['filename'];
    }
    
    // Prepare data
    $data = [
        'full_name' => sanitize($_POST['full_name']),
        'password' => $_POST['password'],
        'email' => sanitize($_POST['email']),
        'contact_number' => sanitize($_POST['contact_number']),
        'address' => sanitize($_POST['address'] ?? ''),
        'role' => sanitize($_POST['role']),
        'status' => sanitize($_POST['status'] ?? 'active'),
        'profile_image' => $profileImage
    ];
    
    // Create staff
    $staffId = $staffModel->create($data);
    
    if (!$staffId) {
        jsonResponse(['success' => false, 'message' => 'Failed to create staff'], 500);
    }
    
    // Log activity
    logActivity($_SESSION['user_id'], 'create_staff', 'Created staff: ' . $data['full_name']);
    
    jsonResponse([
        'success' => true,
        'message' => 'Staff created successfully',
        'id' => $staffId
    ]);
}

/**
 * Handle PUT requests (Update)
 */
function handlePut($staffModel) {
    global $isCashier;
    // Parse PUT data or method-override POST data
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $_PUT = $_POST;
    } else {
        parse_str(file_get_contents("php://input"), $_PUT);
    }
    
    // Get staff ID
    if (empty($_PUT['id'])) {
        jsonResponse(['success' => false, 'message' => 'Staff ID is required'], 400);
    }
    
    $staffId = (int)$_PUT['id'];
    
    // Check if staff exists
    $existingStaff = $staffModel->findById($staffId);
    if (!$existingStaff) {
        jsonResponse(['success' => false, 'message' => 'Staff not found'], 404);
    }
    
    // If this is a status-only update, allow it without full validation
    if (!empty($_PUT['status']) && empty($_PUT['full_name']) && empty($_PUT['email']) && empty($_PUT['contact_number']) && empty($_PUT['role'])) {
        $data = ['status' => sanitize($_PUT['status'])];
        $success = $staffModel->update($staffId, $data);
        
        if (!$success) {
            jsonResponse(['success' => false, 'message' => 'Failed to update staff status'], 500);
        }
        
        logActivity($_SESSION['user_id'], 'update_staff_status', 'Updated staff status: ' . $existingStaff['full_name']);
        jsonResponse(['success' => true, 'message' => 'Staff status updated successfully']);
    }
    
    // Validate required fields for full update
    $requiredFields = ['full_name', 'email', 'contact_number', 'role'];
    foreach ($requiredFields as $field) {
        if (empty($_PUT[$field])) {
            jsonResponse(['success' => false, 'message' => ucfirst(str_replace('_', ' ', $field)) . ' is required'], 400);
        }
    }
    
    // Validate password if provided
    if (!empty($_PUT['password'])) {
        if ($_PUT['password'] !== $_PUT['confirm_password']) {
            jsonResponse(['success' => false, 'message' => 'Passwords do not match'], 400);
        }
        
        if (strlen($_PUT['password']) < 6) {
            jsonResponse(['success' => false, 'message' => 'Password must be at least 6 characters'], 400);
        }
    }
    
    // Validate email format
    if (!filter_var($_PUT['email'], FILTER_VALIDATE_EMAIL)) {
        jsonResponse(['success' => false, 'message' => 'Invalid email format'], 400);
    }
    
    // Check if email already exists for other staff
    if ($staffModel->emailExists($_PUT['email'], $staffId)) {
        jsonResponse(['success' => false, 'message' => 'Email already exists'], 400);
    }

    // Validate role value
    $allowedRoles = ['admin', 'cashier', 'chief_mechanic', 'service_adviser', 'technician'];
    if (!in_array($_PUT['role'], $allowedRoles, true)) {
        jsonResponse(['success' => false, 'message' => 'Invalid staff role'], 400);
    }
    if ($_PUT['role'] === 'admin' && !hasRole('admin')) {
        jsonResponse(['success' => false, 'message' => 'Only admin can assign admin role'], 403);
    }
    
    // Handle profile image upload if provided
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $uploadResult = uploadFile($_FILES['profile_image'], ['jpg', 'jpeg', 'png'], MAX_FILE_SIZE);
        if (!$uploadResult['success']) {
            jsonResponse(['success' => false, 'message' => $uploadResult['message']], 400);
        }
        $_PUT['profile_image'] = $uploadResult['filename'];
    }
    
    // Prepare data
    $data = [
        'full_name' => sanitize($_PUT['full_name']),
        'email' => sanitize($_PUT['email']),
        'contact_number' => sanitize($_PUT['contact_number']),
        'address' => sanitize($_PUT['address'] ?? ''),
        'role' => sanitize($_PUT['role']),
        'status' => sanitize($_PUT['status'] ?? 'active')
    ];
    
    // Add password if provided
    if (!empty($_PUT['password'])) {
        $data['password'] = $_PUT['password'];
    }
    
    if (!empty($_PUT['profile_image'])) {
        $data['profile_image'] = $_PUT['profile_image'];
    }
    
    // Update staff
    $success = $staffModel->update($staffId, $data);
    
    if (!$success) {
        jsonResponse(['success' => false, 'message' => 'Failed to update staff'], 500);
    }
    
    // Log activity
    logActivity($_SESSION['user_id'], 'update_staff', 'Updated staff: ' . $data['full_name']);
    
    jsonResponse([
        'success' => true,
        'message' => 'Staff updated successfully'
    ]);
}

/**
 * Handle DELETE requests
 */
function handleDelete($staffModel) {
    global $isCashier;
    if ($isCashier) {
        jsonResponse(['success' => false, 'message' => 'Cashier is not allowed to delete staff records'], 403);
    }
    // Get staff ID
    if (empty($_GET['id'])) {
        jsonResponse(['success' => false, 'message' => 'Staff ID is required'], 400);
    }
    
    $staffId = (int)$_GET['id'];
    
    // Check if staff exists
    $staff = $staffModel->findById($staffId);
    if (!$staff) {
        jsonResponse(['success' => false, 'message' => 'Staff not found'], 404);
    }
    
    // Delete profile image if exists
    if (!empty($staff['profile_image'])) {
        deleteFile($staff['profile_image']);
    }
    
    // Delete staff
    $success = $staffModel->delete($staffId);
    
    if (!$success) {
        jsonResponse(['success' => false, 'message' => 'Failed to delete staff'], 500);
    }
    
    // Log activity
    logActivity($_SESSION['user_id'], 'delete_staff', 'Deleted staff: ' . $staff['full_name']);
    
    jsonResponse([
        'success' => true,
        'message' => 'Staff deleted successfully'
    ]);
}
