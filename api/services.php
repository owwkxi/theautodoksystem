<?php
session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/session.php';
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
$sessionRole = normalizeRole($_SESSION['role'] ?? $_SESSION['user_role'] ?? '');

function dispatchServiceChangeNotification($title, $action, $subject, $details = '', $referenceId = null) {
    $actorName = sanitize($_SESSION['full_name'] ?? $_SESSION['username'] ?? 'Staff');
    notifyRoles(
        'system',
        $title,
        buildNotificationMessageTemplate($actorName, $action, $subject, $details),
        ['admin', 'cashier', 'service_adviser'],
        [
            'exclude_user_id' => (int)($_SESSION['user_id'] ?? 0),
            'reference_type' => 'service',
            'reference_id' => $referenceId !== null ? (int)$referenceId : null,
        ]
    );
}

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
            if ($sessionRole !== 'admin') {
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
                logActivity((int)($_SESSION['user_id'] ?? 0), 'create_service', 'Created service: ' . ($service['service_name'] ?? 'Unknown'));
                dispatchServiceChangeNotification(
                    'Service Added',
                    'added',
                    'service ' . ($service['service_name'] ?? ('#' . $serviceId)),
                    'Price: ₱' . number_format((float)($service['base_price'] ?? $service['service_price'] ?? 0), 2),
                    (int)$serviceId
                );
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
            if ($sessionRole !== 'admin') {
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
            $existingService = $serviceModel->findById($data['id']);
            if (!$existingService) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Service not found']);
                exit;
            }

            $expectedUpdatedAt = trim((string)($data['expected_updated_at'] ?? ''));
            if ($expectedUpdatedAt !== '' && (string)($existingService['updated_at'] ?? '') !== $expectedUpdatedAt) {
                http_response_code(409);
                echo json_encode(['success' => false, 'message' => 'Conflict: this service was updated by another user. Please refresh and try again.']);
                exit;
            }
            $result = $serviceModel->update($data['id'], $data);
            
            if ($result) {
                $service = $serviceModel->findById($data['id']);
                logActivity((int)($_SESSION['user_id'] ?? 0), 'update_service', 'Updated service: ' . ($service['service_name'] ?? ('#' . $data['id'])));
                $oldStatus = strtoupper((string)($existingService['status'] ?? ''));
                $newStatus = strtoupper((string)($service['status'] ?? ''));
                dispatchServiceChangeNotification(
                    'Service Updated',
                    'updated',
                    'service ' . ($service['service_name'] ?? ('#' . $data['id'])),
                    'Status: ' . $oldStatus . ' -> ' . $newStatus . '; Price: ₱' . number_format((float)($service['base_price'] ?? $service['service_price'] ?? 0), 2),
                    (int)$data['id']
                );
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
            if ($sessionRole !== 'admin') {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Admin access required']);
                exit;
            }
            
            if (empty($_GET['id'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Service ID is required']);
                exit;
            }
            
            $existingService = $serviceModel->findById($_GET['id']);
            if (!$existingService) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Service not found']);
                exit;
            }
            $serviceArchivePayload = Database::getInstance()->fetch("SELECT * FROM services WHERE id = ? LIMIT 1", [(int)$_GET['id']]);
            if (!is_array($serviceArchivePayload) || empty($serviceArchivePayload)) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Service payload not found']);
                exit;
            }
            $serviceInJobOrders = Database::getInstance()->fetch("SELECT COUNT(*) AS count FROM job_order_services WHERE service_id = ?", [(int)$_GET['id']]);
            if ((int)($serviceInJobOrders['count'] ?? 0) > 0) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Cannot delete service. It may be in use in bundles or job orders.']);
                exit;
            }
            $serviceInBundles = Database::getInstance()->fetch("SELECT COUNT(*) AS count FROM bundle_services WHERE service_id = ?", [(int)$_GET['id']]);
            if ((int)($serviceInBundles['count'] ?? 0) > 0) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Cannot delete service. It may be in use in bundles or job orders.']);
                exit;
            }

            $expectedUpdatedAt = trim((string)($_GET['expected_updated_at'] ?? ''));
            if ($expectedUpdatedAt !== '' && (string)($existingService['updated_at'] ?? '') !== $expectedUpdatedAt) {
                http_response_code(409);
                echo json_encode(['success' => false, 'message' => 'Conflict: this service was updated by another user. Please refresh and try again.']);
                exit;
            }
            $archiveId = archiveDeletedRecord('service', $serviceArchivePayload, ['source' => 'database', 'meta' => ['table' => 'services']]);
            if (!$archiveId) {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to archive service before deletion']);
                exit;
            }
            $result = $serviceModel->delete($_GET['id']);
            
            if ($result) {
                logActivity((int)($_SESSION['user_id'] ?? 0), 'delete_service', 'Deleted service: ' . ($existingService['service_name'] ?? ('#' . $_GET['id'])));
                dispatchServiceChangeNotification(
                    'Service Removed',
                    'removed',
                    'service ' . ($existingService['service_name'] ?? ('#' . $_GET['id'])),
                    '',
                    (int)$_GET['id']
                );
                echo json_encode(['success' => true, 'message' => 'Service deleted successfully']);
            } else {
                deleteArchivedRecordById($archiveId);
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Cannot delete service. It may be in use in bundles or job orders.']);
            }
            break;
            
        case 'PATCH':
            // Check admin permission
            if ($sessionRole !== 'admin') {
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
            
            $existingService = $serviceModel->findById($data['id']);
            if (!$existingService) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Service not found']);
                exit;
            }

            $expectedUpdatedAt = trim((string)($data['expected_updated_at'] ?? ''));
            if ($expectedUpdatedAt !== '' && (string)($existingService['updated_at'] ?? '') !== $expectedUpdatedAt) {
                http_response_code(409);
                echo json_encode(['success' => false, 'message' => 'Conflict: this service was updated by another user. Please refresh and try again.']);
                exit;
            }
            $result = $serviceModel->toggleStatus($data['id']);
            
            if ($result) {
                $service = $serviceModel->findById($data['id']);
                $oldStatus = strtoupper((string)($existingService['status'] ?? ''));
                $newStatus = strtoupper((string)($service['status'] ?? ''));
                logActivity((int)($_SESSION['user_id'] ?? 0), 'update_service_status', 'Updated service status: ' . ($service['service_name'] ?? ('#' . $data['id'])) . ' (' . $oldStatus . ' -> ' . $newStatus . ')');
                dispatchServiceChangeNotification(
                    'Service Status Updated',
                    'updated status for',
                    'service ' . ($service['service_name'] ?? ('#' . $data['id'])),
                    'Status: ' . $oldStatus . ' -> ' . $newStatus,
                    (int)$data['id']
                );
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
