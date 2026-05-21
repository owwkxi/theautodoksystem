<?php
session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../models/Service.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$serviceModel = new Service();
$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            if (isset($_GET['id'])) {
                // Get single service
                $service = $serviceModel->findById($_GET['id']);
                if ($service) {
                    echo json_encode(['success' => true, 'data' => $service]);
                } else {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'message' => 'Service not found']);
                }
            } else {
                // Get all services with filters
                $filters = [
                    'status' => $_GET['status'] ?? null,
                    'search' => $_GET['search'] ?? null,
                    'limit' => $_GET['limit'] ?? 100,
                    'offset' => $_GET['offset'] ?? 0
                ];
                
                $services = $serviceModel->getAll($filters);
                $total = $serviceModel->count($filters);
                
                echo json_encode([
                    'success' => true,
                    'data' => $services,
                    'total' => $total
                ]);
            }
            break;
            
        case 'POST':
            // Check admin permission
            if ($_SESSION['role'] !== 'admin') {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Admin access required']);
                exit;
            }
            
            // Validate input
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (empty($data['service_name'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Service name is required']);
                exit;
            }
            
            if (!isset($data['service_price']) || $data['service_price'] < 0) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Valid service price is required']);
                exit;
            }
            
            // Create service
            $serviceId = $serviceModel->create($data);
            
            if ($serviceId) {
                $service = $serviceModel->findById($serviceId);
                echo json_encode([
                    'success' => true,
                    'message' => 'Service created successfully',
                    'data' => $service
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to create service']);
            }
            break;
            
        case 'PUT':
            // Check admin permission
            if ($_SESSION['role'] !== 'admin') {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Admin access required']);
                exit;
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (empty($data['id'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Service ID is required']);
                exit;
            }
            
            if (empty($data['service_name'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Service name is required']);
                exit;
            }
            
            // Update service
            $result = $serviceModel->update($data['id'], $data);
            
            if ($result) {
                $service = $serviceModel->findById($data['id']);
                echo json_encode([
                    'success' => true,
                    'message' => 'Service updated successfully',
                    'data' => $service
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to update service']);
            }
            break;
            
        case 'DELETE':
            // Check admin permission
            if ($_SESSION['role'] !== 'admin') {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Admin access required']);
                exit;
            }
            
            if (empty($_GET['id'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Service ID is required']);
                exit;
            }
            
            $result = $serviceModel->delete($_GET['id']);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Service deleted successfully']);
            } else {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Cannot delete service. It may be in use in bundles or job orders.']);
            }
            break;
            
        case 'PATCH':
            // Check admin permission
            if ($_SESSION['role'] !== 'admin') {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Admin access required']);
                exit;
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (empty($data['id'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Service ID is required']);
                exit;
            }
            
            $result = $serviceModel->toggleStatus($data['id']);
            
            if ($result) {
                $service = $serviceModel->findById($data['id']);
                echo json_encode([
                    'success' => true,
                    'message' => 'Service status updated successfully',
                    'data' => $service
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to update service status']);
            }
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            break;
    }
} catch (Exception $e) {
    error_log("Services API error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'An error occurred']);
}
