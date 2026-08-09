<?php
/**
 * Job Orders API — session-based auth, matches real DB schema
 */

header('Content-Type: application/json');

define('APP_ACCESS', true);
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../models/JobOrder.php';

// Session auth
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

$currentUserId   = $_SESSION['user_id'];
$currentUserRole = normalizeRole($_SESSION['user_role'] ?? 'admin');
$method          = $_SERVER['REQUEST_METHOD'];
$id              = $_GET['id'] ?? null;

$response = ['success' => false, 'message' => '', 'data' => null];

$allowedJoStatuses = ['pending', 'ongoing', 'under_inspection', 'car_washing', 'completed', 'released', 'returned_for_revision', 'cancelled'];
$runningJoStatuses = ['ongoing', 'under_inspection', 'returned_for_revision'];
$activeJoStatuses = ['pending', 'ongoing', 'under_inspection', 'car_washing', 'returned_for_revision'];

$normalizeJoStatus = static function ($status) {
    $clean = sanitize((string)$status);
    $aliases = [
        'for_approval' => 'under_inspection',
        'return_for_revision' => 'returned_for_revision',
    ];
    return $aliases[$clean] ?? $clean;
};

$isAssignedTechnician = static function ($db, $jobOrderId, $technicianId): bool {
    $row = $db->fetch(
        "SELECT 1 AS found FROM job_order_technicians WHERE job_order_id = ? AND technician_id = ? LIMIT 1",
        [$jobOrderId, $technicianId]
    );
    return (bool)$row;
};

$canViewJobOrder = static function ($db, $jobOrderId, $jobOrderStatus, $role, $userId) use ($activeJoStatuses, $isAssignedTechnician): bool {
    if (in_array($role, ['admin', 'cashier'], true)) {
        return true;
    }

    if ($role === 'service_adviser' || $role === 'chief_mechanic') {
        return true;
    }

    if ($role === 'technician') {
        return in_array($jobOrderStatus, $activeJoStatuses, true)
            && $isAssignedTechnician($db, (int)$jobOrderId, (int)$userId);
    }

    return false;
};

try {
    $db = Database::getInstance();

    switch ($method) {

        // ── GET ──────────────────────────────────────────────────────────────
        case 'GET':
            $jobOrderModel = new JobOrder();
            if ($id) {
                // Return full data including customer and vehicle details
                $jo = $db->fetch(
                    "SELECT jo.*,
                            c.full_name AS customer_name, c.phone AS customer_phone,
                            c.email AS customer_email, c.address AS customer_address,
                            v.brand AS vehicle_make, v.model AS vehicle_model,
                            v.year_model AS vehicle_year, v.plate_number AS vehicle_license,
                            v.color AS vehicle_color, v.mileage AS vehicle_mileage,
                            COALESCE(
                                NULLIF(GROUP_CONCAT(DISTINCT st.full_name ORDER BY st.full_name SEPARATOR ', '), ''),
                                sa.full_name,
                                'Unassigned'
                            ) AS assigned_technician_name
                     FROM job_orders jo
                     LEFT JOIN customers c ON jo.customer_id = c.id
                     LEFT JOIN vehicles  v ON jo.vehicle_id  = v.id
                     LEFT JOIN job_order_technicians jot ON jot.job_order_id = jo.id
                     LEFT JOIN staff st ON st.id = jot.technician_id
                     LEFT JOIN staff     sa ON jo.service_adviser_id = sa.id
                     WHERE jo.id = ?
                     GROUP BY jo.id",
                    [$id]
                );
                if (!$jo) throw new Exception('Job order not found');
                if (!$canViewJobOrder($db, (int)$jo['id'], (string)$jo['status'], $currentUserRole, $currentUserId)) {
                    throw new Exception('Insufficient permissions');
                }

                $elapsedSeconds = (int)($jo['status_timer_seconds'] ?? 0);
                $isTimerRunning = in_array($jo['status'], $runningJoStatuses, true) && !empty($jo['status_timer_started_at']);
                if ($isTimerRunning) {
                    $elapsedSeconds += max(0, time() - strtotime($jo['status_timer_started_at']));
                }
                $jo['status_elapsed_seconds'] = $elapsedSeconds;
                $jo['status_timer_is_running'] = $isTimerRunning;

                $techRows = $db->fetchAll(
                    "SELECT DISTINCT s.id, s.full_name
                     FROM job_order_technicians jot
                     INNER JOIN staff s ON s.id = jot.technician_id
                     WHERE jot.job_order_id = ?
                     ORDER BY s.full_name ASC",
                    [$id]
                );
                $jo['technicians'] = $techRows;
                $jo['technician_ids'] = array_map(fn($t) => (int)$t['id'], $techRows);

                // Attach services and products
                $jo['services'] = $db->fetchAll(
                    "SELECT service_id, bundle_id, service_name, service_price, labor_cost, quantity, total FROM job_order_services WHERE job_order_id = ?",
                    [$id]
                );
                $jo['products'] = $db->fetchAll(
                    "SELECT product_id AS id, product_name, unit_price, unit_price AS price, quantity, quantity AS qty, total
                     FROM job_order_products WHERE job_order_id = ?",
                    [$id]
                );
                $response['success'] = true;
                $response['data']    = $jo;
            } else {
                if (!in_array($currentUserRole, ['admin', 'cashier'], true)) {
                    throw new Exception('Insufficient permissions');
                }
                $filters = [
                    'status'         => $_GET['status']         ?? '',
                    'payment_status' => $_GET['payment_status'] ?? '',
                    'search'         => $_GET['search']         ?? '',
                    'limit'          => $_GET['limit']          ?? 10,
                    'offset'         => $_GET['offset']         ?? 0,
                ];
                $response['success'] = true;
                $response['data']    = [
                    'job_orders' => $jobOrderModel->getAll($filters),
                    'total'      => $jobOrderModel->count($filters),
                ];
            }
            break;

        // ── POST (create) ────────────────────────────────────────────────────
        case 'POST':
            if (!in_array($currentUserRole, ['admin', 'cashier'], true)) {
                throw new Exception('Insufficient permissions');
            }
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input) throw new Exception('Invalid JSON payload');

            // CSRF
            if (empty($input['csrf_token']) || !verifyCSRFToken($input['csrf_token'])) {
                throw new Exception('Invalid CSRF token');
            }

            if (empty($input['customer_name']))  throw new Exception('Customer name is required');
            if (empty($input['customer_phone'])) throw new Exception('Customer phone is required');

            // ── 1. Create or reuse customer ──────────────────────────────────
            $custName  = sanitize($input['customer_name']);
            $custPhone = sanitize($input['customer_phone']);
            $custEmail = sanitize($input['customer_email']   ?? '');
            $custAddr  = sanitize($input['customer_address'] ?? '');

            // Try to find existing customer by phone
            $existing = $db->fetch(
                "SELECT id FROM customers WHERE phone = ? LIMIT 1",
                [$custPhone]
            );

            if ($existing) {
                $customerId = $existing['id'];
                // Update name/email/address in case they changed
                $db->query(
                    "UPDATE customers SET full_name=?, email=?, address=? WHERE id=?",
                    [$custName, $custEmail ?: null, $custAddr ?: null, $customerId]
                );
            } else {
                // Generate customer code
                $year     = date('Y');
                $lastCust = $db->fetch(
                    "SELECT customer_code FROM customers WHERE customer_code LIKE ? ORDER BY id DESC LIMIT 1",
                    ["CUST-{$year}-%"]
                );
                $custNum  = $lastCust ? (intval(substr($lastCust['customer_code'], -4)) + 1) : 1;
                $custCode = sprintf("CUST-%s-%04d", $year, $custNum);

                $db->query(
                    "INSERT INTO customers (customer_code, full_name, phone, email, address) VALUES (?,?,?,?,?)",
                    [$custCode, $custName, $custPhone, $custEmail ?: null, $custAddr ?: null]
                );
                $customerId = $db->lastInsertId();
            }

            // ── 2. Create vehicle ────────────────────────────────────────────
            $db->query(
                "INSERT INTO vehicles (customer_id, brand, model, year_model, plate_number, color, mileage)
                 VALUES (?,?,?,?,?,?,?)",
                [
                    $customerId,
                    sanitize($input['vehicle_make']    ?? ''),
                    sanitize($input['vehicle_model']   ?? ''),
                    sanitize($input['vehicle_year']    ?? ''),
                    sanitize($input['vehicle_license'] ?? ''),
                    sanitize($input['vehicle_color']   ?? ''),
                    sanitize($input['vehicle_mileage'] ?? ''),
                ]
            );
            $vehicleId = $db->lastInsertId();

            // ── 3. Calculate totals ──────────────────────────────────────────
            $items    = $input['items']    ?? [];
            $products = $input['products'] ?? [];

            $subtotal  = array_sum(array_map(function ($i) {
                $basePrice = isset($i['base_price']) ? (float)$i['base_price'] : (float)($i['price'] ?? 0);
                $laborCost = isset($i['labor_cost']) ? (float)$i['labor_cost'] : (float)($i['labor'] ?? 0);
                return ($basePrice + $laborCost) * (int)($i['qty'] ?? 1);
            }, $items));
            $partsCost = array_sum(array_map(fn($p) => $p['price'] * $p['qty'], $products));
            $base      = $subtotal + $partsCost;

            // Map frontend discount types to DB enum values
            $discTypeFront = $input['discount_type'] ?? 'none';
            $discVal       = (float)($input['discount_value'] ?? 0);
            $discountAmt   = 0;
            $discPct       = 0;

            switch ($discTypeFront) {
                case 'percentage':
                    $dbDiscType  = 'custom';
                    $discountAmt = $base * ($discVal / 100);
                    $discPct     = $discVal;
                    break;
                case 'fixed':
                    $dbDiscType  = 'custom';
                    $discountAmt = $discVal;
                    break;
                case 'senior':
                    $dbDiscType  = 'senior_citizen';
                    $discountAmt = $base * 0.20;
                    $discPct     = 20;
                    break;
                case 'pwd':
                    $dbDiscType  = 'pwd';
                    $discountAmt = $base * 0.20;
                    $discPct     = 20;
                    break;
                default:
                    $dbDiscType  = 'none';
                    $discountAmt = 0;
            }

            $discountAmt = min($discountAmt, $base);
            $total       = max(0, $base - $discountAmt);

            // ── 4. Generate JO number ────────────────────────────────────────
            $joNumber = generateJobOrderNumber();

            // ── 5. Insert job order ──────────────────────────────────────────
            // ── 5. Insert job order ──────────────────────────────────────────
            $partialAmount = (float)($input['partial_amount'] ?? 0);
            if ($partialAmount < 0) $partialAmount = 0;
            if ($partialAmount > $total) $partialAmount = $total;

            $status = $normalizeJoStatus($input['status'] ?? 'pending');
            if (!in_array($status, $allowedJoStatuses, true)) {
                $status = 'pending';
            }

            $now = date('Y-m-d H:i:s');
            $statusTimerStartedAt = in_array($status, $runningJoStatuses, true) ? $now : null;
            $workStartedAt = $status === 'ongoing' ? $now : null;
            $inspectionStartedAt = $status === 'under_inspection' ? $now : null;
            $completedAt = $status === 'completed' ? $now : null;

            $technicianIds = [];
            if (!empty($input['technician_ids']) && is_array($input['technician_ids'])) {
                foreach ($input['technician_ids'] as $techId) {
                    $idInt = (int)$techId;
                    if ($idInt > 0) $technicianIds[] = $idInt;
                }
            } elseif (!empty($input['technician_id'])) {
                $idInt = (int)$input['technician_id'];
                if ($idInt > 0) $technicianIds[] = $idInt;
            }
            $technicianIds = array_values(array_unique($technicianIds));

            $serviceAdviserId = !empty($technicianIds) ? (int)$technicianIds[0] : null;

            $db->query(
                "INSERT INTO job_orders
                    (job_order_number, customer_id, vehicle_id, service_adviser_id,
                     subtotal, labor_total, parts_total,
                     discount_type, discount_amount, discount_percentage,
                     partial_amount, total_amount, payment_method, payment_status,
                     status, priority, notes, created_by,
                     status_timer_seconds, status_timer_started_at,
                     work_started_at, inspection_started_at, completed_at)
                 VALUES (?,?,?,?, ?,?,?, ?,?,?, ?,?,?,?, ?,?,?,?, ?,?,?,?,?)",
                [
                    $joNumber,
                    $customerId,
                    $vehicleId,
                    $serviceAdviserId,
                    $subtotal,
                    0,                // labor_total — handled via services
                    $partsCost,
                    $dbDiscType,
                    $discountAmt,
                    $discPct,
                    $partialAmount,
                    $total,
                    sanitize($input['payment_method'] ?? 'cash'),
                    sanitize($input['payment_status'] ?? 'pending'),
                    $status,
                    'normal',
                    sanitize($input['notes'] ?? ''),
                    $currentUserId,
                    0,
                    $statusTimerStartedAt,
                    $workStartedAt,
                    $inspectionStartedAt,
                    $completedAt,
                ]
            );
            $jobOrderId = $db->lastInsertId();

            // ── 5b. Insert JO technician assignments ───────────────────────
            foreach ($technicianIds as $techId) {
                $db->query(
                    "INSERT INTO job_order_technicians (job_order_id, technician_id, assigned_at, status)
                     VALUES (?, ?, NOW(), 'assigned')",
                    [$jobOrderId, $techId]
                );
            }

            // ── 6. Insert job_order_services ─────────────────────────────────
            foreach ($items as $item) {
                if (($item['type'] ?? '') === 'service' && !empty($item['id'])) {
                    $basePrice = isset($item['base_price']) ? (float)$item['base_price'] : (float)($item['price'] ?? 0);
                    $laborCost = isset($item['labor_cost']) ? (float)$item['labor_cost'] : (float)($item['labor'] ?? 0);
                    $unitTotal = $basePrice + $laborCost;
                    $db->query(
                        "INSERT INTO job_order_services (job_order_id, service_id, service_name, service_price, labor_cost, quantity, total) VALUES (?,?,?,?,?,?,?)",
                        [$jobOrderId, $item['id'], sanitize($item['name']), $basePrice, $laborCost, (int)($item['qty']??1), $unitTotal * (int)($item['qty']??1)]
                    );
                } elseif (($item['type'] ?? '') === 'bundle' && !empty($item['id'])) {
                    $basePrice = isset($item['base_price']) ? (float)$item['base_price'] : (float)($item['price'] ?? 0);
                    $laborCost = isset($item['labor_cost']) ? (float)$item['labor_cost'] : (float)($item['labor'] ?? 0);
                    $unitTotal = $basePrice + $laborCost;
                    $db->query(
                        "INSERT INTO job_order_services (job_order_id, bundle_id, service_name, service_price, labor_cost, quantity, total) VALUES (?,?,?,?,?,?,?)",
                        [$jobOrderId, $item['id'], sanitize($item['name']), $basePrice, $laborCost, (int)($item['qty']??1), $unitTotal * (int)($item['qty']??1)]
                    );
                }
            }

            // ── 7. Insert job_order_products + deduct inventory ──────────────
            foreach ($products as $prod) {
                if (!empty($prod['id'])) {
                    $prodId  = (int)$prod['id'];
                    $prodQty = (int)($prod['qty'] ?? 1);
                    $prodPrice = (float)$prod['price'];

                    // Check stock
                    $stock = $db->fetch("SELECT quantity, product_name FROM products WHERE id=?", [$prodId]);
                    if (!$stock) continue;
                    if ($stock['quantity'] < $prodQty) {
                        throw new Exception("Insufficient stock for: {$stock['product_name']} (available: {$stock['quantity']})");
                    }

                    // Insert into job_order_products
                    $db->query(
                        "INSERT INTO job_order_products (job_order_id, product_id, product_name, product_type, unit_price, quantity, total) VALUES (?,?,?,?,?,?,?)",
                        [$jobOrderId, $prodId, sanitize($prod['name']), 'parts', $prodPrice, $prodQty, $prodPrice * $prodQty]
                    );

                    // Deduct from inventory
                    $db->query("UPDATE products SET quantity = quantity - ? WHERE id=?", [$prodQty, $prodId]);

                    // Log inventory transaction
                    $db->query(
                        "INSERT INTO inventory_transactions (product_id, transaction_type, quantity, reference_type, reference_id, notes, created_by) VALUES (?,?,?,?,?,?,?)",
                        [$prodId, 'stock_out', $prodQty, 'job_order', $jobOrderId, "Used in JO #{$joNumber}", $currentUserId]
                    );
                }
            }

            logActivity($currentUserId, 'create_job_order', "Created job order #{$joNumber}");

            $actorName = sanitize($_SESSION['full_name'] ?? $_SESSION['username'] ?? 'Staff');
            notifyRoles(
                'job_status',
                'New Job Order Created',
                buildNotificationMessageTemplate($actorName, 'created', 'job order #' . $joNumber),
                ['admin', 'cashier', 'service_adviser', 'chief_mechanic', 'technician'],
                [
                    'reference_type' => 'job_order',
                    'reference_id' => (int)$jobOrderId,
                ]
            );

            $response['success'] = true;
            $response['message'] = 'Job order created successfully';
            $response['data']    = ['id' => $jobOrderId, 'job_order_number' => $joNumber];
            http_response_code(201);
            break;

        // ── PUT (update) ───────────────────────────────────────────────────────
        case 'PUT':
            if (!in_array($currentUserRole, ['admin', 'cashier'], true)) {
                throw new Exception('Insufficient permissions');
            }
            if (!$id) throw new Exception('Job order ID is required');
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input) throw new Exception('Invalid JSON payload');

            // Get current JO to find customer_id and vehicle_id
            $jo = $db->fetch(
                "SELECT customer_id, vehicle_id, job_order_number, status, payment_status, payment_method,
                        status_timer_seconds, status_timer_started_at,
                        work_started_at, inspection_started_at, completed_at
                 FROM job_orders WHERE id=?",
                [$id]
            );
            if (!$jo) throw new Exception('Job order not found');

            $updateNotes = [];
            $oldPaymentMethod = (string)($jo['payment_method'] ?? 'cash');
            $oldPartialAmount = 0.0;
            $oldPaymentStatus = (string)($jo['payment_status'] ?? 'pending');

            $currentEditSnapshot = $db->fetch(
                "SELECT c.full_name AS customer_name, c.phone AS customer_phone, c.email AS customer_email, c.address AS customer_address,
                        v.brand AS vehicle_make, v.model AS vehicle_model, v.year_model AS vehicle_year, v.plate_number AS vehicle_license,
                        v.color AS vehicle_color, v.mileage AS vehicle_mileage,
                        jo.partial_amount, jo.notes, jo.payment_method, jo.payment_status
                 FROM job_orders jo
                 LEFT JOIN customers c ON jo.customer_id = c.id
                 LEFT JOIN vehicles v ON jo.vehicle_id = v.id
                 WHERE jo.id = ?",
                [$id]
            );
            if ($currentEditSnapshot) {
                $oldPartialAmount = (float)($currentEditSnapshot['partial_amount'] ?? 0);
            }

            $oldServiceCount = (int)($db->fetch(
                "SELECT COUNT(*) AS total FROM job_order_services WHERE job_order_id = ?",
                [$id]
            )['total'] ?? 0);
            $oldProductCount = (int)($db->fetch(
                "SELECT COUNT(*) AS total FROM job_order_products WHERE job_order_id = ?",
                [$id]
            )['total'] ?? 0);

            $oldStatus = (string)($jo['status'] ?? 'pending');

            $compareField = static function ($label, $oldValue, $newValue) use (&$updateNotes) {
                $oldText = trim((string)$oldValue);
                $newText = trim((string)$newValue);
                if ($oldText === $newText) {
                    return;
                }
                $oldText = $oldText === '' ? '—' : $oldText;
                $newText = $newText === '' ? '—' : $newText;
                $updateNotes[] = "{$label}: {$oldText} → {$newText}";
            };

            // Update customer
            $db->query(
                "UPDATE customers SET full_name=?, phone=?, email=?, address=? WHERE id=?",
                [
                    sanitize($input['customer_name']    ?? ''),
                    sanitize($input['customer_phone']   ?? ''),
                    sanitize($input['customer_email']   ?? '') ?: null,
                    sanitize($input['customer_address'] ?? '') ?: null,
                    $jo['customer_id'],
                ]
            );

            // Update vehicle
            $db->query(
                "UPDATE vehicles SET brand=?, model=?, year_model=?, plate_number=?, color=?, mileage=? WHERE id=?",
                [
                    sanitize($input['vehicle_make']    ?? ''),
                    sanitize($input['vehicle_model']   ?? ''),
                    sanitize($input['vehicle_year']    ?? ''),
                    sanitize($input['vehicle_license'] ?? ''),
                    sanitize($input['vehicle_color']   ?? ''),
                    sanitize($input['vehicle_mileage'] ?? ''),
                    $jo['vehicle_id'],
                ]
            );

            // Update job order status/payment/notes
            $editPartial = (float)($input['partial_amount'] ?? 0);
            if ($editPartial < 0) $editPartial = 0;

            if ($currentEditSnapshot) {
                $compareField('Customer name', $currentEditSnapshot['customer_name'] ?? '', $input['customer_name'] ?? '');
                $compareField('Customer phone', $currentEditSnapshot['customer_phone'] ?? '', $input['customer_phone'] ?? '');
                $compareField('Customer email', $currentEditSnapshot['customer_email'] ?? '', $input['customer_email'] ?? '');
                $compareField('Customer address', $currentEditSnapshot['customer_address'] ?? '', $input['customer_address'] ?? '');
                $compareField('Vehicle make', $currentEditSnapshot['vehicle_make'] ?? '', $input['vehicle_make'] ?? '');
                $compareField('Vehicle model', $currentEditSnapshot['vehicle_model'] ?? '', $input['vehicle_model'] ?? '');
                $compareField('Vehicle year', $currentEditSnapshot['vehicle_year'] ?? '', $input['vehicle_year'] ?? '');
                $compareField('Vehicle plate', $currentEditSnapshot['vehicle_license'] ?? '', $input['vehicle_license'] ?? '');
                $compareField('Vehicle color', $currentEditSnapshot['vehicle_color'] ?? '', $input['vehicle_color'] ?? '');
                $compareField('Vehicle mileage', $currentEditSnapshot['vehicle_mileage'] ?? '', $input['vehicle_mileage'] ?? '');
                $compareField('Payment method', $oldPaymentMethod, $input['payment_method'] ?? $oldPaymentMethod);
                $compareField('Payment status', $oldPaymentStatus, $input['payment_status'] ?? $oldPaymentStatus);
                $compareField('Partial amount', number_format($oldPartialAmount, 2, '.', ','), number_format($editPartial, 2, '.', ','));
                $compareField('Notes', $currentEditSnapshot['notes'] ?? '', $input['notes'] ?? '');
            }

            $technicianIds = [];
            if (!empty($input['technician_ids']) && is_array($input['technician_ids'])) {
                foreach ($input['technician_ids'] as $techId) {
                    $idInt = (int)$techId;
                    if ($idInt > 0) $technicianIds[] = $idInt;
                }
            } elseif (!empty($input['technician_id'])) {
                $idInt = (int)$input['technician_id'];
                if ($idInt > 0) $technicianIds[] = $idInt;
            }
            $technicianIds = array_values(array_unique($technicianIds));

            $serviceAdviserId = !empty($technicianIds) ? (int)$technicianIds[0] : null;

            $newStatus = $normalizeJoStatus($input['status'] ?? $jo['status'] ?? 'pending');
            if (!in_array($newStatus, $allowedJoStatuses, true)) {
                $newStatus = 'pending';
            }

            $requestedPaymentStatus = sanitize($input['payment_status'] ?? $jo['payment_status'] ?? 'pending');
            if (($jo['payment_status'] ?? '') === 'paid' && $requestedPaymentStatus !== 'paid' && $currentUserRole !== 'admin') {
                throw new Exception('Only admin/system administrator can change payment status for a paid job order');
            }

            $now = date('Y-m-d H:i:s');
            $timerSeconds = (int)($jo['status_timer_seconds'] ?? 0);
            $timerStartedAt = $jo['status_timer_started_at'] ?? null;

            $wasRunning = in_array($jo['status'], $runningJoStatuses, true) && !empty($timerStartedAt);
            if ($wasRunning) {
                $timerSeconds += max(0, strtotime($now) - strtotime($timerStartedAt));
                $timerStartedAt = null;
            }

            if (in_array($newStatus, $runningJoStatuses, true)) {
                $timerStartedAt = $now;
            }

            $workStartedAt = $jo['work_started_at'] ?? null;
            $inspectionStartedAt = $jo['inspection_started_at'] ?? null;
            $completedAt = $jo['completed_at'] ?? null;

            if ($newStatus === 'ongoing' && empty($workStartedAt)) {
                $workStartedAt = $now;
            }
            if ($newStatus === 'under_inspection' && empty($inspectionStartedAt)) {
                $inspectionStartedAt = $now;
            }
            if ($newStatus === 'completed') {
                $completedAt = $completedAt ?: $now;
                $timerStartedAt = null;
            } elseif (!empty($completedAt)) {
                $completedAt = null;
            }

            $db->query(
                "UPDATE job_orders
                 SET status=?, payment_status=?, payment_method=?, service_adviser_id=?, partial_amount=?, notes=?,
                     status_timer_seconds=?, status_timer_started_at=?,
                     work_started_at=?, inspection_started_at=?, completed_at=?
                 WHERE id=?",
                [
                    $newStatus,
                    $requestedPaymentStatus,
                    sanitize($input['payment_method'] ?? $jo['payment_method'] ?? 'cash'),
                    $serviceAdviserId,
                    $editPartial,
                    sanitize($input['notes']          ?? ''),
                    $timerSeconds,
                    $timerStartedAt,
                    $workStartedAt,
                    $inspectionStartedAt,
                    $completedAt,
                    $id,
                ]
            );

            // Refresh technician assignments for this JO
            $db->query("DELETE FROM job_order_technicians WHERE job_order_id=?", [$id]);
            foreach ($technicianIds as $techId) {
                $db->query(
                    "INSERT INTO job_order_technicians (job_order_id, technician_id, assigned_at, status)
                     VALUES (?, ?, NOW(), 'assigned')",
                    [$id, $techId]
                );
            }

            // Update job_order_services if items provided
            if (isset($input['items']) && is_array($input['items'])) {
                $db->query("DELETE FROM job_order_services WHERE job_order_id=?", [$id]);
                $newSubtotal = 0;
                foreach ($input['items'] as $item) {
                    $price = (float)($item['price'] ?? 0);
                    $qty   = (int)($item['qty']   ?? 1);
                    $total = $price * $qty;
                    $newSubtotal += $total;
                    if (($item['type']??'') === 'bundle') {
                        $db->query(
                            "INSERT INTO job_order_services (job_order_id,bundle_id,service_name,service_price,labor_cost,quantity,total) VALUES (?,?,?,?,?,?,?)",
                            [$id, (int)($item['id']??0), sanitize($item['name']??''), $price, 0, $qty, $total]
                        );
                    } else {
                        $db->query(
                            "INSERT INTO job_order_services (job_order_id,service_id,service_name,service_price,labor_cost,quantity,total) VALUES (?,?,?,?,?,?,?)",
                            [$id, !empty($item['id']) ? (int)$item['id'] : null, sanitize($item['name']??''), $price, 0, $qty, $total]
                        );
                    }
                }
                // Recalculate subtotal
                $partsTotal = (float)($db->fetch("SELECT parts_total FROM job_orders WHERE id=?",[$id])['parts_total']??0);
                $newTotal   = max(0, $newSubtotal + $partsTotal - (float)($jo['discount_amount']??0));
                $db->query("UPDATE job_orders SET subtotal=?, total_amount=? WHERE id=?", [$newSubtotal, $newTotal, $id]);
            }

            // Update job_order_products if products provided — restore old stock, deduct new
            if (isset($input['products']) && is_array($input['products'])) {
                // Restore old product quantities
                $oldProds = $db->fetchAll(
                    "SELECT product_id, quantity FROM job_order_products WHERE job_order_id=? AND product_id IS NOT NULL",
                    [$id]
                );
                foreach ($oldProds as $op) {
                    $db->query("UPDATE products SET quantity = quantity + ? WHERE id=?", [$op['quantity'], $op['product_id']]);
                    $db->query(
                        "INSERT INTO inventory_transactions (product_id,transaction_type,quantity,reference_type,reference_id,notes,created_by) VALUES (?,?,?,?,?,?,?)",
                        [$op['product_id'], 'return', $op['quantity'], 'job_order', $id, "Edit restore JO #{$jo['job_order_number']}", $currentUserId]
                    );
                }

                // Delete old product rows
                $db->query("DELETE FROM job_order_products WHERE job_order_id=?", [$id]);

                // Insert new products and deduct stock
                $newPartsCost = 0;
                foreach ($input['products'] as $prod) {
                    if (empty($prod['id'])) continue;
                    $prodId    = (int)$prod['id'];
                    $prodQty   = (int)($prod['qty'] ?? 1);
                    $prodPrice = (float)($prod['price'] ?? 0);

                    $stock = $db->fetch("SELECT quantity, product_name FROM products WHERE id=?", [$prodId]);
                    if (!$stock || $stock['quantity'] < $prodQty) {
                        throw new Exception("Insufficient stock for: " . ($stock['product_name'] ?? "product #$prodId"));
                    }

                    $db->query(
                        "INSERT INTO job_order_products (job_order_id,product_id,product_name,product_type,unit_price,quantity,total) VALUES (?,?,?,?,?,?,?)",
                        [$id, $prodId, sanitize($prod['name']??''), 'parts', $prodPrice, $prodQty, $prodPrice * $prodQty]
                    );
                    $db->query("UPDATE products SET quantity = quantity - ? WHERE id=?", [$prodQty, $prodId]);
                    $db->query(
                        "INSERT INTO inventory_transactions (product_id,transaction_type,quantity,reference_type,reference_id,notes,created_by) VALUES (?,?,?,?,?,?,?)",
                        [$prodId, 'stock_out', $prodQty, 'job_order', $id, "Used in JO #{$jo['job_order_number']}", $currentUserId]
                    );
                    $newPartsCost += $prodPrice * $prodQty;
                }

                // Recalculate parts_total and total_amount
                $currentSubtotal = (float)($db->fetch("SELECT subtotal FROM job_orders WHERE id=?", [$id])['subtotal'] ?? 0);
                $discountAmt     = (float)($db->fetch("SELECT discount_amount FROM job_orders WHERE id=?", [$id])['discount_amount'] ?? 0);
                $newTotal        = max(0, $currentSubtotal + $newPartsCost - $discountAmt);
                $db->query("UPDATE job_orders SET parts_total=?, total_amount=? WHERE id=?", [$newPartsCost, $newTotal, $id]);

                $newServiceCount = count($input['items']);
                if ($newServiceCount !== $oldServiceCount) {
                    $updateNotes[] = "Services/Bundles count: {$oldServiceCount} → {$newServiceCount}";
                }
                if ($oldServiceCount === $newServiceCount) {
                    $updateNotes[] = "Services/Bundles updated";
                }

                $newProductCount = count($input['products']);
                if ($newProductCount !== $oldProductCount) {
                    $updateNotes[] = "Products count: {$oldProductCount} → {$newProductCount}";
                }
                if ($oldProductCount === $newProductCount) {
                    $updateNotes[] = "Products updated";
                }
            }

            if ($newStatus !== $oldStatus) {
                $updateNotes[] = "Status: " . ucwords(str_replace('_', ' ', $oldStatus)) . " → " . ucwords(str_replace('_', ' ', $newStatus));
            }

            if ($requestedPaymentStatus === 'partial' || $editPartial > 0 || $editPartial !== $oldPartialAmount) {
                $updateNotes[] = "Partial payment: ₱" . number_format($oldPartialAmount, 2) . " → ₱" . number_format($editPartial, 2);
            }

            $activityDescription = 'Updated job order #' . $jo['job_order_number'];
            if (!empty($updateNotes)) {
                $activityDescription .= ': ' . implode('; ', array_values(array_unique($updateNotes)));
            }

            logActivity($currentUserId, 'update_job_order', $activityDescription);

            if ($newStatus !== $oldStatus) {
                $statusText = ucwords(str_replace('_', ' ', $newStatus));
                $actorName = sanitize($_SESSION['full_name'] ?? $_SESSION['username'] ?? 'Staff');
                notifyRoles(
                    'job_status',
                    'Job Order Status Updated',
                    buildNotificationMessageTemplate($actorName, 'updated', 'job order #' . $jo['job_order_number'], 'Status: ' . $statusText),
                    ['admin', 'cashier', 'service_adviser', 'chief_mechanic', 'technician'],
                    [
                        'reference_type' => 'job_order',
                        'reference_id' => (int)$id,
                    ]
                );
            }

            if ($requestedPaymentStatus === 'paid' && $oldPaymentStatus !== 'paid') {
                $actorName = sanitize($_SESSION['full_name'] ?? $_SESSION['username'] ?? 'Staff');
                notifyRoles(
                    'payment',
                    'Job Order Paid',
                    buildNotificationMessageTemplate($actorName, 'marked as paid', 'job order #' . $jo['job_order_number']),
                    ['admin', 'cashier', 'service_adviser'],
                    [
                        'reference_type' => 'job_order',
                        'reference_id' => (int)$id,
                    ]
                );
            }

            $response['success'] = true;
            $response['message'] = 'Job order updated successfully';
            break;

        // ── PATCH (status-only update from JO status row) ───────────────────
        case 'PATCH':
            if (!$id) throw new Exception('Job order ID is required');

            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input) throw new Exception('Invalid JSON payload');
            if (empty($input['csrf_token']) || !verifyCSRFToken($input['csrf_token'])) {
                throw new Exception('Invalid CSRF token');
            }

            $jo = $db->fetch(
                "SELECT job_order_number, status, status_timer_seconds, status_timer_started_at,
                        work_started_at, inspection_started_at, completed_at
                 FROM job_orders WHERE id=?",
                [$id]
            );
            if (!$jo) throw new Exception('Job order not found');
            $oldStatus = (string)($jo['status'] ?? 'pending');
            if (!$canViewJobOrder($db, (int)$id, (string)$jo['status'], $currentUserRole, $currentUserId)) {
                throw new Exception('Insufficient permissions');
            }

            $hasStatus = array_key_exists('status', $input);
            $timerAction = sanitize($input['timer_action'] ?? '');
            if (!$hasStatus && $timerAction === '') {
                throw new Exception('No status or timer action provided');
            }

            if ($currentUserRole === 'service_adviser') {
                $isStatusOnly = $hasStatus && $timerAction === '';
                $isStartOnly = !$hasStatus && $timerAction === 'start';
                $isStopOnly = !$hasStatus && $timerAction === 'stop';
                if (!$isStatusOnly && !$isStartOnly && !$isStopOnly) {
                    throw new Exception('Service adviser can only update status, start timer, or stop timer');
                }
            }

            if ($currentUserRole === 'chief_mechanic') {
                $isStatusOnly = $hasStatus && $timerAction === '';
                if (!$isStatusOnly) {
                    throw new Exception('Chief mechanic can only update job order status');
                }
            }

            if (in_array($currentUserRole, ['service_adviser', 'chief_mechanic'], true) && ($jo['status'] ?? '') === 'cancelled') {
                throw new Exception('Cancelled job order is locked for service adviser/chief mechanic.');
            }

            if ($currentUserRole === 'technician') {
                if ($hasStatus) {
                    throw new Exception('Technician cannot update job order status');
                }
                if (!in_array($timerAction, ['stop', 'done'], true)) {
                    throw new Exception('Technician can only stop timer or mark job done');
                }
                if (!$isAssignedTechnician($db, (int)$id, (int)$currentUserId)) {
                    throw new Exception('You can only control timer for assigned job orders');
                }
            }

            $targetStatusInput = $input['status'] ?? ($timerAction === 'done' ? 'under_inspection' : ($jo['status'] ?? 'pending'));
            $newStatus = $normalizeJoStatus($targetStatusInput);
            if (!in_array($newStatus, $allowedJoStatuses, true)) {
                throw new Exception('Invalid job order status');
            }

            if (in_array($currentUserRole, ['service_adviser', 'chief_mechanic'], true) && $hasStatus && ($jo['status'] ?? '') === 'completed') {
                $blockedAfterCompleted = ['pending', 'ongoing', 'under_inspection', 'car_washing', 'cancelled'];
                if (in_array($newStatus, $blockedAfterCompleted, true)) {
                    throw new Exception('After completed, service adviser/chief mechanic cannot move job order back to pending/ongoing/under inspection/car washing.');
                }
            }

            if (in_array($currentUserRole, ['service_adviser', 'chief_mechanic'], true) && $hasStatus && ($jo['status'] ?? '') === 'released') {
                $blockedAfterReleased = ['pending', 'ongoing', 'under_inspection', 'car_washing', 'completed', 'cancelled'];
                if (in_array($newStatus, $blockedAfterReleased, true)) {
                    throw new Exception('After released, service adviser/chief mechanic cannot move job order back to pending through completed statuses.');
                }
            }

            if ($timerAction === 'start' && !in_array($currentUserRole, ['admin', 'cashier', 'service_adviser'], true)) {
                throw new Exception('Insufficient permissions');
            }
            if ($timerAction === 'done' && !in_array($currentUserRole, ['admin', 'cashier', 'technician'], true)) {
                throw new Exception('Insufficient permissions');
            }
            if ($timerAction === 'stop' && !in_array($currentUserRole, ['admin', 'cashier', 'technician', 'service_adviser'], true)) {
                throw new Exception('Insufficient permissions');
            }
            if ($timerAction !== '' && ($jo['status'] ?? '') === 'completed') {
                throw new Exception('Completed job order timer is locked and cannot be edited');
            }

            $now = date('Y-m-d H:i:s');
            $timerSeconds = (int)($jo['status_timer_seconds'] ?? 0);
            $timerStartedAt = $jo['status_timer_started_at'] ?? null;

            $workStartedAt = $jo['work_started_at'] ?? null;
            $inspectionStartedAt = $jo['inspection_started_at'] ?? null;
            $completedAt = $jo['completed_at'] ?? null;

            if ($timerAction === 'start') {
                if (empty($timerStartedAt)) {
                    $timerStartedAt = $now;
                }
            } elseif ($timerAction === 'stop') {
                if (!empty($timerStartedAt)) {
                    $timerSeconds += max(0, strtotime($now) - strtotime($timerStartedAt));
                    $timerStartedAt = null;
                }
            } elseif ($timerAction === 'done') {
                $newStatus = 'under_inspection';
                if (empty($inspectionStartedAt)) {
                    $inspectionStartedAt = $now;
                }
                if (empty($timerStartedAt)) {
                    $timerStartedAt = $now;
                }
            } else {
                $wasRunning = in_array($jo['status'], $runningJoStatuses, true) && !empty($timerStartedAt);
                if ($wasRunning) {
                    $timerSeconds += max(0, strtotime($now) - strtotime($timerStartedAt));
                    $timerStartedAt = null;
                }

                if (in_array($newStatus, $runningJoStatuses, true)) {
                    $timerStartedAt = $now;
                }

                if ($newStatus === 'ongoing' && empty($workStartedAt)) {
                    $workStartedAt = $now;
                }
                if ($newStatus === 'under_inspection' && empty($inspectionStartedAt)) {
                    $inspectionStartedAt = $now;
                }
                if ($newStatus === 'completed') {
                    $completedAt = $completedAt ?: $now;
                    $timerStartedAt = null;
                } elseif (!empty($completedAt)) {
                    $completedAt = null;
                }
            }

            $db->query(
                "UPDATE job_orders
                 SET status=?,
                     status_timer_seconds=?, status_timer_started_at=?,
                     work_started_at=?, inspection_started_at=?, completed_at=?
                 WHERE id=?",
                [
                    $newStatus,
                    $timerSeconds,
                    $timerStartedAt,
                    $workStartedAt,
                    $inspectionStartedAt,
                    $completedAt,
                    $id,
                ]
            );

            $elapsedSeconds = $timerSeconds;
            if (!empty($timerStartedAt)) {
                $elapsedSeconds += max(0, strtotime($now) - strtotime($timerStartedAt));
            }

            if ($timerAction !== '') {
                $timerLabel = ucfirst($timerAction);
                logActivity($currentUserId, 'update_job_order_timer', "{$timerLabel} timer for job order #{$jo['job_order_number']} (elapsed: {$elapsedSeconds}s)");
            } else {
                $oldStatusText = ucwords(str_replace('_', ' ', $oldStatus));
                $newStatusText = ucwords(str_replace('_', ' ', $newStatus));
                logActivity($currentUserId, 'update_job_order_status', "Updated status for job order #{$jo['job_order_number']}: {$oldStatusText} → {$newStatusText}");
            }

            if ($timerAction === 'done') {
                $actorName = sanitize($_SESSION['full_name'] ?? $_SESSION['username'] ?? 'Technician');
                notifyRoles(
                    'job_status',
                    'Job Order Ready for Inspection',
                    buildNotificationMessageTemplate($actorName, 'marked done', 'job order #' . $jo['job_order_number'], 'Moved to Under Inspection'),
                    ['admin', 'cashier', 'service_adviser', 'chief_mechanic'],
                    [
                        'reference_type' => 'job_order',
                        'reference_id' => (int)$id,
                    ]
                );
            } elseif ($newStatus !== $oldStatus) {
                $statusText = ucwords(str_replace('_', ' ', $newStatus));
                $actorName = sanitize($_SESSION['full_name'] ?? $_SESSION['username'] ?? 'Staff');
                notifyRoles(
                    'job_status',
                    'Job Order Status Updated',
                    buildNotificationMessageTemplate($actorName, 'updated', 'job order #' . $jo['job_order_number'], 'Status: ' . $statusText),
                    ['admin', 'cashier', 'service_adviser', 'chief_mechanic', 'technician'],
                    [
                        'reference_type' => 'job_order',
                        'reference_id' => (int)$id,
                    ]
                );
            }

            $response['success'] = true;
            $response['message'] = $timerAction === 'done'
                ? 'Job order marked done and moved to under inspection'
                : ($timerAction !== '' ? 'Job order timer updated successfully' : 'Job order status updated successfully');
            $response['data'] = [
                'status' => $newStatus,
                'status_timer_is_running' => !empty($timerStartedAt),
                'status_elapsed_seconds' => $elapsedSeconds,
            ];
            break;

        // ── DELETE ───────────────────────────────────────────────────────────
        case 'DELETE':
            if (!$id) throw new Exception('Job order ID is required');
            if ($currentUserRole !== 'admin') throw new Exception('Only admins can delete job orders');

            $jo = $db->fetch("SELECT job_order_number, status, payment_status FROM job_orders WHERE id=?", [$id]);
            if (!$jo) throw new Exception('Job order not found');

            $shouldRestoreStock = !in_array($jo['status'], ['completed', 'released'], true)
                && $jo['payment_status'] !== 'paid';

            if ($shouldRestoreStock) {
                // Restore inventory for all products used in this JO
                $joProds = $db->fetchAll(
                    "SELECT product_id, quantity FROM job_order_products WHERE job_order_id=? AND product_id IS NOT NULL",
                    [$id]
                );
                foreach ($joProds as $p) {
                    $db->query("UPDATE products SET quantity = quantity + ? WHERE id=?", [$p['quantity'], $p['product_id']]);
                    $db->query(
                        "INSERT INTO inventory_transactions (product_id, transaction_type, quantity, reference_type, reference_id, notes, created_by) VALUES (?,?,?,?,?,?,?)",
                        [$p['product_id'], 'return', $p['quantity'], 'job_order', $id, "JO #{$jo['job_order_number']} deleted", $currentUserId]
                    );
                }
            }

            $db->query("DELETE FROM job_orders WHERE id=?", [$id]);
            logActivity($currentUserId, 'delete_job_order', "Deleted job order #{$jo['job_order_number']}");

            $actorName = sanitize($_SESSION['full_name'] ?? $_SESSION['username'] ?? 'Staff');
            notifyRoles(
                'system',
                'Job Order Deleted',
                buildNotificationMessageTemplate($actorName, 'deleted', 'job order #' . $jo['job_order_number']),
                ['admin', 'cashier', 'service_adviser'],
                [
                    'reference_type' => 'job_order',
                    'reference_id' => (int)$id,
                ]
            );

            $response['success'] = true;
            $response['message'] = 'Job order deleted successfully';
            break;

        default:
            throw new Exception('Method not allowed');
    }

} catch (Exception $e) {
    error_log("Job order API error: " . $e->getMessage());
    $response['message'] = $e->getMessage();
    http_response_code(400);
}

echo json_encode($response);
