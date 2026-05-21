<?php
session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../models/ServiceBundle.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$bundleModel = new ServiceBundle();
$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            if (isset($_GET['id'])) {
                // Get single bundle with services
                $bundle = $bundleModel->findById($_GET['id']);
                if ($bundle) {
                    echo json_encode(['success' => true, 'data' => $bundle]);
                } else {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'message' => 'Bundle not found']);
                }
            } else {
                // Get all bundles with filters
                $filters = [
                    'status' => $_GET['status'] ?? null,
                    'search' => $_GET['search'] ?? null,
                    'limit' => $_GET['limit'] ?? 100,
                    'offset' => $_GET['offset'] ?? 0
                ];
                
                $bundles = $bundleModel->getAll($filters);
                $total = $bundleModel->count($filters);
                
                echo json_encode([
                    'success' => true,
                    'data' => $bundles,
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
            
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (empty($data['bundle_name'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Bundle name is required']);
                exit;
            }
            
            if (!isset($data['package_price']) || $data['package_price'] < 0) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Valid package price is required']);
                exit;
            }
            
            if (empty($data['service_ids']) || !is_array($data['service_ids'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'At least one service must be selected']);
                exit;
            }
            
            // Create bundle
            $bundleId = $bundleModel->create($data, $data['service_ids']);
            
            if ($bundleId) {
                $bundle = $bundleModel->findById($bundleId);
                echo json_encode([
                    'success' => true,
                    'message' => 'Service bundle created successfully',
                    'data' => $bundle
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to create service bundle']);
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
                echo json_encode(['success' => false, 'message' => 'Bundle ID is required']);
                exit;
            }
            
            if (empty($data['bundle_name'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Bundle name is required']);
                exit;
            }
            
            // Update bundle
            $result = $bundleModel->update($data['id'], $data);
            
            // Update services if provided
            if ($result && isset($data['service_ids']) && is_array($data['service_ids'])) {
                $bundleModel->updateServices($data['id'], $data['service_ids']);
            }
            
            if ($result) {
                $bundle = $bundleModel->findById($data['id']);
                echo json_encode([
                    'success' => true,
                    'message' => 'Service bundle updated successfully',
                    'data' => $bundle
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to update service bundle']);
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
                echo json_encode(['success' => false, 'message' => 'Bundle ID is required']);
                exit;
            }
            
            $result = $bundleModel->delete($_GET['id']);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Service bundle deleted successfully']);
            } else {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Cannot delete bundle. It may be in use in job orders.']);
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
                echo json_encode(['success' => false, 'message' => 'Bundle ID is required']);
                exit;
            }
            
            $result = $bundleModel->toggleStatus($data['id']);
            
            if ($result) {
                $bundle = $bundleModel->findById($data['id']);
                echo json_encode([
                    'success' => true,
                    'message' => 'Bundle status updated successfully',
                    'data' => $bundle
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to update bundle status']);
            }
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            break;
    }
} catch (Exception $e) {
    error_log("Service Bundles API error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'An error occurred']);
}
