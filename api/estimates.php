<?php
/**
 * Job Estimates API — session-based auth
 */
header('Content-Type: application/json');

define('APP_ACCESS', true);
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/security.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

$currentUserId = $_SESSION['user_id'];
$currentUserRole = $_SESSION['user_role'] ?? '';
$method        = $_SERVER['REQUEST_METHOD'];
$id            = $_GET['id'] ?? null;
$response      = ['success' => false, 'message' => '', 'data' => null];

try {
    $db = Database::getInstance();

    switch ($method) {

        case 'GET':
            if (!in_array($currentUserRole, ['admin', 'cashier'], true)) {
                throw new Exception('Insufficient permissions');
            }
            if ($id) {
                $est = $db->fetch("SELECT * FROM job_estimates WHERE id = ?", [$id]);
                if (!$est) throw new Exception('Estimate not found');
                $response['success'] = true;
                $response['data']    = $est;
            } else {
                $search = $_GET['search'] ?? '';
                $sql    = "SELECT * FROM job_estimates WHERE 1=1";
                $params = [];
                if ($search) {
                    $sql    .= " AND (estimate_number LIKE ? OR vehicle_plate LIKE ? OR vehicle_make LIKE ?)";
                    $params  = array_merge($params, ["%$search%", "%$search%", "%$search%"]);
                }
                $sql .= " ORDER BY created_at DESC";
                $response['success'] = true;
                $response['data']    = $db->fetchAll($sql, $params);
            }
            break;

        case 'POST':
            if (!in_array($currentUserRole, ['admin', 'cashier'], true)) {
                throw new Exception('Insufficient permissions');
            }
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input) throw new Exception('Invalid JSON payload');

            if (empty($input['csrf_token']) || !verifyCSRFToken($input['csrf_token'])) {
                throw new Exception('Invalid CSRF token');
            }

            // Generate estimate number (JE###)
            $last = $db->fetch(
                "SELECT MAX(CAST(SUBSTRING(estimate_number, 3) AS UNSIGNED)) AS max_num
                 FROM job_estimates
                 WHERE estimate_number REGEXP '^JE[0-9]+$'"
            );
            $num = (int)($last['max_num'] ?? 0) + 1;
            $estNum = 'JE' . str_pad((string)$num, 3, '0', STR_PAD_LEFT);

            $servicesTotal = (float)($input['services_total'] ?? 0);
            $productsTotal = (float)($input['products_total'] ?? 0);
            $grandTotal    = $servicesTotal + $productsTotal;

            $db->query(
                "INSERT INTO job_estimates
                    (estimate_number, vehicle_make, vehicle_model, vehicle_year,
                     vehicle_plate, vehicle_color, vehicle_mileage,
                     services_total, products_total, grand_total,
                     services_json, products_json, status, created_by)
                 VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)",
                [
                    $estNum,
                    sanitize($input['vehicle_make']    ?? ''),
                    sanitize($input['vehicle_model']   ?? ''),
                    sanitize($input['vehicle_year']    ?? ''),
                    sanitize($input['vehicle_plate']   ?? ''),
                    sanitize($input['vehicle_color']   ?? ''),
                    sanitize($input['vehicle_mileage'] ?? ''),
                    $servicesTotal,
                    $productsTotal,
                    $grandTotal,
                    json_encode($input['services']  ?? []),
                    json_encode($input['products']  ?? []),
                    'draft',
                    $currentUserId,
                ]
            );
            $estId = $db->lastInsertId();

            logActivity($currentUserId, 'create_estimate', "Created estimate #{$estNum}");

            $response['success'] = true;
            $response['message'] = 'Estimate saved successfully';
            $response['data']    = ['id' => $estId, 'estimate_number' => $estNum];
            http_response_code(201);
            break;

        case 'PUT':
            if (!in_array($currentUserRole, ['admin', 'cashier'], true)) {
                throw new Exception('Insufficient permissions');
            }
            if (!$id) throw new Exception('Estimate ID is required');
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input) throw new Exception('Invalid JSON payload');

            $updateFields = [];
            $params = [];
            foreach (['vehicle_make','vehicle_model','vehicle_year','vehicle_plate','vehicle_color','vehicle_mileage','status'] as $field) {
                if (array_key_exists($field, $input)) {
                    $updateFields[] = "$field = ?";
                    $params[] = sanitize($input[$field]);
                }
            }

            if (empty($updateFields)) {
                throw new Exception('No estimate fields to update');
            }

            $params[] = $id;
            $db->query('UPDATE job_estimates SET ' . implode(', ', $updateFields) . ' WHERE id = ?', $params);
            $response['success'] = true;
            $response['message'] = 'Estimate updated successfully';
            break;

        case 'DELETE':
            if (!$id) throw new Exception('Estimate ID is required');
            if ($currentUserRole !== 'admin') {
                throw new Exception('Only admins can delete estimates');
            }
            $est = $db->fetch("SELECT estimate_number FROM job_estimates WHERE id = ?", [$id]);
            if (!$est) throw new Exception('Estimate not found');
            $db->query("DELETE FROM job_estimates WHERE id = ?", [$id]);
            logActivity($currentUserId, 'delete_estimate', "Deleted estimate #{$est['estimate_number']}");
            $response['success'] = true;
            $response['message'] = 'Estimate deleted';
            break;

        default:
            throw new Exception('Method not allowed');
    }

} catch (Exception $e) {
    error_log("Estimates API error: " . $e->getMessage());
    $response['message'] = $e->getMessage();
    http_response_code(400);
}

echo json_encode($response);
