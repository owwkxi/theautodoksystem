<?php
/**
 * Job Orders API
 * RESTful API for job order management
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
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
require_once __DIR__ . '/../models/JobOrder.php';
require_once __DIR__ . '/../models/User.php';

// Verify JWT token
$token = getBearerToken();
if (!$token) {
    jsonResponse(['success' => false, 'message' => 'No token provided'], 401);
}

$payload = verifyJWT($token);
if (!$payload) {
    jsonResponse(['success' => false, 'message' => 'Invalid or expired token'], 401);
}

$currentUserId = $payload['user_id'];
$currentUserRole = $payload['role'];

// Get request method
$method = $_SERVER['REQUEST_METHOD'];
$id = $_GET['id'] ?? null;

// Initialize response
$response = [
    'success' => false,
    'message' => '',
    'data' => null
];

try {
    $jobOrderModel = new JobOrder();

    switch ($method) {
        case 'GET':
            if ($id) {
                // Get single job order
                $jobOrder = $jobOrderModel->findById($id);
                
                if (!$jobOrder) {
                    throw new Exception('Job order not found');
                }

                $response['success'] = true;
                $response['data'] = $jobOrder;
            } else {
                // Get all job orders with filters
                $filters = [
                    'status' => $_GET['status'] ?? '',
                    'payment_status' => $_GET['payment_status'] ?? '',
                    'technician_id' => $_GET['technician_id'] ?? '',
                    'search' => $_GET['search'] ?? '',
                    'limit' => $_GET['limit'] ?? 10,
                    'offset' => $_GET['offset'] ?? 0
                ];

                $jobOrders = $jobOrderModel->getAll($filters);
                $total = $jobOrderModel->count($filters);

                $response['success'] = true;
                $response['data'] = [
                    'job_orders' => $jobOrders,
                    'total' => $total,
                    'limit' => $filters['limit'],
                    'offset' => $filters['offset']
                ];
            }
            break;

        case 'POST':
            // Create new job order
            $input = json_decode(file_get_contents('php://input'), true);

            // Validate required fields
            $required = ['customer_name', 'customer_phone', 'service_type', 'total_amount'];
            foreach ($required as $field) {
                if (empty($input[$field])) {
                    throw new Exception(ucfirst(str_replace('_', ' ', $field)) . ' is required');
                }
            }

            // Generate job order number
            $input['job_order_number'] = generateJobOrderNumber();
            $input['created_by'] = $currentUserId;

            $jobOrderId = $jobOrderModel->create($input);

            if (!$jobOrderId) {
                throw new Exception('Failed to create job order');
            }

            // Log activity
            logActivity($currentUserId, 'create_job_order', "Created job order #{$input['job_order_number']}");

            $response['success'] = true;
            $response['message'] = 'Job order created successfully';
            $response['data'] = ['id' => $jobOrderId, 'job_order_number' => $input['job_order_number']];
            http_response_code(201);
            break;

        case 'PUT':
            // Update job order
            if (!$id) {
                throw new Exception('Job order ID is required');
            }

            $jobOrder = $jobOrderModel->findById($id);
            if (!$jobOrder) {
                throw new Exception('Job order not found');
            }

            $input = json_decode(file_get_contents('php://input'), true);

            $updated = $jobOrderModel->update($id, $input);

            if (!$updated) {
                throw new Exception('Failed to update job order');
            }

            // Log activity
            logActivity($currentUserId, 'update_job_order', "Updated job order #{$jobOrder['job_order_number']}");

            $response['success'] = true;
            $response['message'] = 'Job order updated successfully';
            break;

        case 'DELETE':
            // Delete job order
            if (!$id) {
                throw new Exception('Job order ID is required');
            }

            // Only admins can delete
            if ($currentUserRole !== 'admin') {
                throw new Exception('Unauthorized: Only admins can delete job orders');
            }

            $jobOrder = $jobOrderModel->findById($id);
            if (!$jobOrder) {
                throw new Exception('Job order not found');
            }

            $deleted = $jobOrderModel->delete($id);

            if (!$deleted) {
                throw new Exception('Failed to delete job order');
            }

            // Log activity
            logActivity($currentUserId, 'delete_job_order', "Deleted job order #{$jobOrder['job_order_number']}");

            $response['success'] = true;
            $response['message'] = 'Job order deleted successfully';
            break;

        default:
            throw new Exception('Method not allowed');
    }

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    http_response_code(400);
}

echo json_encode($response);
