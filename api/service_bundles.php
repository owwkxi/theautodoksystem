<?php
session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/session.php';
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
$sessionRole = normalizeRole($_SESSION['role'] ?? $_SESSION['user_role'] ?? '');

function dispatchBundleChangeNotification($title, $action, $subject, $details = '', $referenceId = null) {
    $actorName = sanitize($_SESSION['full_name'] ?? $_SESSION['username'] ?? 'Staff');
    notifyRoles(
        'system',
        $title,
        buildNotificationMessageTemplate($actorName, $action, $subject, $details),
        ['admin', 'cashier', 'service_adviser'],
        [
            'exclude_user_id' => (int)($_SESSION['user_id'] ?? 0),
            'reference_type' => 'service_bundle',
            'reference_id' => $referenceId !== null ? (int)$referenceId : null,
        ]
    );
}

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
            if ($sessionRole !== 'admin') {
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
                // Save products if provided
                if (!empty($data['products']) && is_array($data['products'])) {
                    $bundleModel->updateProducts($bundleId, $data['products']);
                }
                $bundle = $bundleModel->findById($bundleId);
                logActivity((int)($_SESSION['user_id'] ?? 0), 'create_service_bundle', 'Created service bundle: ' . ($bundle['bundle_name'] ?? 'Unknown'));
                dispatchBundleChangeNotification(
                    'Service Bundle Added',
                    'added',
                    'bundle ' . ($bundle['bundle_name'] ?? ('#' . $bundleId)),
                    'Price: ₱' . number_format((float)($bundle['package_price'] ?? 0), 2),
                    (int)$bundleId
                );
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
            if ($sessionRole !== 'admin') {
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
            $existingBundle = $bundleModel->findById($data['id']);
            if (!$existingBundle) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Bundle not found']);
                exit;
            }

            $expectedUpdatedAt = trim((string)($data['expected_updated_at'] ?? ''));
            if ($expectedUpdatedAt !== '' && (string)($existingBundle['updated_at'] ?? '') !== $expectedUpdatedAt) {
                http_response_code(409);
                echo json_encode(['success' => false, 'message' => 'Conflict: this bundle was updated by another user. Please refresh and try again.']);
                exit;
            }
            $result = $bundleModel->update($data['id'], $data);
            
            // Update services if provided
            if ($result && isset($data['service_ids']) && is_array($data['service_ids'])) {
                $bundleModel->updateServices($data['id'], $data['service_ids']);
            }

            // Update products if provided
            if ($result && isset($data['products']) && is_array($data['products'])) {
                $bundleModel->updateProducts($data['id'], $data['products']);
            }
            
            if ($result) {
                $bundle = $bundleModel->findById($data['id']);
                $oldStatus = strtoupper((string)($existingBundle['status'] ?? ''));
                $newStatus = strtoupper((string)($bundle['status'] ?? ''));
                logActivity((int)($_SESSION['user_id'] ?? 0), 'update_service_bundle', 'Updated service bundle: ' . ($bundle['bundle_name'] ?? ('#' . $data['id'])));
                dispatchBundleChangeNotification(
                    'Service Bundle Updated',
                    'updated',
                    'bundle ' . ($bundle['bundle_name'] ?? ('#' . $data['id'])),
                    'Status: ' . $oldStatus . ' -> ' . $newStatus . '; Price: ₱' . number_format((float)($bundle['package_price'] ?? 0), 2),
                    (int)$data['id']
                );
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
            if ($sessionRole !== 'admin') {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Admin access required']);
                exit;
            }
            
            if (empty($_GET['id'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Bundle ID is required']);
                exit;
            }
            
            $existingBundle = $bundleModel->findById($_GET['id']);
            if (!$existingBundle) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Bundle not found']);
                exit;
            }
            $bundleId = (int)$_GET['id'];
            $db = Database::getInstance();
            $bundleRow = $db->fetch("SELECT * FROM service_bundles WHERE id = ? LIMIT 1", [$bundleId]);
            if (!is_array($bundleRow) || empty($bundleRow)) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Bundle payload not found']);
                exit;
            }
            $bundleServices = $db->fetchAll("SELECT * FROM bundle_services WHERE bundle_id = ?", [$bundleId]);
            $bundleProducts = $db->fetchAll("SELECT * FROM bundle_products WHERE bundle_id = ?", [$bundleId]);
            $bundleInJobOrders = $db->fetch("SELECT COUNT(*) AS count FROM job_order_services WHERE bundle_id = ?", [$bundleId]);
            if ((int)($bundleInJobOrders['count'] ?? 0) > 0) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Cannot delete bundle. It may be in use in job orders.']);
                exit;
            }

            $expectedUpdatedAt = trim((string)($_GET['expected_updated_at'] ?? ''));
            if ($expectedUpdatedAt !== '' && (string)($existingBundle['updated_at'] ?? '') !== $expectedUpdatedAt) {
                http_response_code(409);
                echo json_encode(['success' => false, 'message' => 'Conflict: this bundle was updated by another user. Please refresh and try again.']);
                exit;
            }
            $archiveId = archiveDeletedRecord('service_bundle', [
                'bundle' => $bundleRow,
                'bundle_services' => $bundleServices,
                'bundle_products' => $bundleProducts,
            ], ['source' => 'database', 'meta' => ['table' => 'service_bundles']]);
            if (!$archiveId) {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to archive bundle before deletion']);
                exit;
            }
            $result = $bundleModel->delete($_GET['id']);
            
            if ($result) {
                logActivity((int)($_SESSION['user_id'] ?? 0), 'delete_service_bundle', 'Deleted service bundle: ' . ($existingBundle['bundle_name'] ?? ('#' . $_GET['id'])));
                dispatchBundleChangeNotification(
                    'Service Bundle Removed',
                    'removed',
                    'bundle ' . ($existingBundle['bundle_name'] ?? ('#' . $_GET['id'])),
                    '',
                    (int)$_GET['id']
                );
                echo json_encode(['success' => true, 'message' => 'Service bundle deleted successfully']);
            } else {
                deleteArchivedRecordById($archiveId);
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Cannot delete bundle. It may be in use in job orders.']);
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
                echo json_encode(['success' => false, 'message' => 'Bundle ID is required']);
                exit;
            }
            
            $existingBundle = $bundleModel->findById($data['id']);
            if (!$existingBundle) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Bundle not found']);
                exit;
            }

            $expectedUpdatedAt = trim((string)($data['expected_updated_at'] ?? ''));
            if ($expectedUpdatedAt !== '' && (string)($existingBundle['updated_at'] ?? '') !== $expectedUpdatedAt) {
                http_response_code(409);
                echo json_encode(['success' => false, 'message' => 'Conflict: this bundle was updated by another user. Please refresh and try again.']);
                exit;
            }
            $result = $bundleModel->toggleStatus($data['id']);
            
            if ($result) {
                $bundle = $bundleModel->findById($data['id']);
                $oldStatus = strtoupper((string)($existingBundle['status'] ?? ''));
                $newStatus = strtoupper((string)($bundle['status'] ?? ''));
                logActivity((int)($_SESSION['user_id'] ?? 0), 'update_service_bundle_status', 'Updated service bundle status: ' . ($bundle['bundle_name'] ?? ('#' . $data['id'])) . ' (' . $oldStatus . ' -> ' . $newStatus . ')');
                dispatchBundleChangeNotification(
                    'Service Bundle Status Updated',
                    'updated status for',
                    'bundle ' . ($bundle['bundle_name'] ?? ('#' . $data['id'])),
                    'Status: ' . $oldStatus . ' -> ' . $newStatus,
                    (int)$data['id']
                );
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
