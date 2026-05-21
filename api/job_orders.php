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

/*
Job Estimate Print Template
---------------------------
This template is for the Job Estimate print view and is typically used in a front-end view file,
not in an API endpoint. It is provided here for reference.

<div class="modal fade" id="jobEstimateModal" tabindex="-1" aria-labelledby="jobEstimateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background: #f8f9fa; border-bottom: 2px solid #e0e0e0;">
                <h5 class="modal-title" id="jobEstimateModalLabel" style="color: #000;">
                    <i class="bi bi-calculator"></i> Job Estimate Calculator
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="padding: 30px;">
                <div class="row g-3">
                    <div class="col-12">
                        <h6 style="color: #000; margin-bottom: 15px;">Select Services</h6>
                        <div style="max-height: 300px; overflow-y: auto; border: 1.5px solid #e0e0e0; border-radius: 8px; padding: 15px; background: #f9f9f9;">
                            <?php if (!empty($allActiveServices)): ?>
                                <?php foreach ($allActiveServices as $service): ?>
                                    <div class="form-check mb-2" style="padding: 10px; background: #fff; border-radius: 6px;">
                                        <input class="form-check-input estimate-service" type="checkbox"
                                               data-price="<?php echo $service['service_price'] + $service['labor_cost']; ?>"
                                               id="est_service_<?php echo $service['id']; ?>">
                                        <label class="form-check-label" for="est_service_<?php echo $service['id']; ?>" style="color: #000; width: 100%;">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <strong><?php echo escape($service['service_name']); ?></strong>
                                                </div>
                                                <div>
                                                    <strong><?php echo formatCurrency($service['service_price'] + $service['labor_cost']); ?></strong>
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p style="color: #666; text-align: center;">No services available</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="card" style="background: #f8f9fa; border: 2px solid #e0e0e0;">
                            <div class="card-body">
                                <h6 style="color: #000; margin-bottom: 15px;">Estimate Summary</h6>
                                <div class="d-flex justify-content-between mb-2">
                                    <span style="color: #666;">Services Total:</span>
                                    <strong style="color: #000;" id="estimateTotal">₱0.00</strong>
                                </div>

                                <div class="mb-2">
                                    <label class="form-label form-label-sm" style="color:#000;font-weight:500;">
                                        <i class="bi bi-box-seam"></i> Products
                                    </label>
                                    <div class="d-flex gap-1 mb-1">
                                        <select class="form-select form-select-sm" id="est_product_select" style="flex:1;">
                                            <option value="">— Select product —</option>
                                            <?php foreach ($allInventoryProducts as $prod): ?>
                                            <option value="<?php echo $prod['id']; ?>"
                                                data-name="<?php echo addslashes(escape($prod['product_name'])); ?>"
                                                data-price="<?php echo $prod['selling_price']; ?>"
                                                data-stock="<?php echo $prod['quantity']; ?>">
                                                <?php echo escape($prod['product_name']); ?> — ₱<?php echo number_format($prod['selling_price'], 2); ?> (<?php echo $prod['quantity']; ?> in stock)
                                            </option>
                                            <?php endforeach; ?>
                                            <?php if (empty($allInventoryProducts)): ?>
                                            <option disabled>No products in inventory</option>
                                            <?php endif; ?>
                                        </select>
                                        <input type="number" id="est_product_qty" class="form-control form-control-sm text-center" value="1" min="1" style="width:55px;">
                                        <button type="button" class="btn btn-sm btn-dark px-2" onclick="estAddProduct()"><i class="bi bi-plus"></i></button>
                                    </div>
                                    <div id="estProductsList" style="max-height:120px;overflow-y:auto;"></div>
                                </div>

                                <div class="d-flex justify-content-between mb-2">
                                    <span style="color: #666;">Products Total:</span>
                                    <strong style="color: #000;" id="estimateProductsTotal">₱0.00</strong>
                                </div>

                                <hr style="border-color: #e0e0e0;">
                                <div class="d-flex justify-content-between">
                                    <strong style="color: #000;">Grand Total:</strong>
                                    <h4 style="color: #000; margin: 0;" id="estimateGrandTotal">₱0.00</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="background: #f8f9fa; border-top: 2px solid #e0e0e0;">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Close
                </button>
                <button type="button" class="btn btn-primary" onclick="window.print()">
                    <i class="bi bi-printer"></i> Print Estimate
                </button>
            </div>
        </div>
    </div>
</div>
*/
