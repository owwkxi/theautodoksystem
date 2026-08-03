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
$currentUserRole = $_SESSION['user_role'] ?? 'admin';
$method          = $_SERVER['REQUEST_METHOD'];
$id              = $_GET['id'] ?? null;

$response = ['success' => false, 'message' => '', 'data' => null];

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
                            v.color AS vehicle_color, v.mileage AS vehicle_mileage
                     FROM job_orders jo
                     LEFT JOIN customers c ON jo.customer_id = c.id
                     LEFT JOIN vehicles  v ON jo.vehicle_id  = v.id
                     WHERE jo.id = ?",
                    [$id]
                );
                if (!$jo) throw new Exception('Job order not found');
                // Attach services and products
                $jo['services'] = $db->fetchAll(
                    "SELECT service_id, bundle_id, service_name, service_price, labor_cost, quantity, total FROM job_order_services WHERE job_order_id = ?",
                    [$id]
                );
                $jo['products'] = $db->fetchAll(
                    "SELECT product_name, unit_price, quantity, total FROM job_order_products WHERE job_order_id = ?",
                    [$id]
                );
                $response['success'] = true;
                $response['data']    = $jo;
            } else {
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

            $serviceAdviserId = !empty($input['technician_id']) ? (int)$input['technician_id'] : null;

            $db->query(
                "INSERT INTO job_orders
                    (job_order_number, customer_id, vehicle_id, service_adviser_id,
                     subtotal, labor_total, parts_total,
                     discount_type, discount_amount, discount_percentage,
                     partial_amount, total_amount, payment_method, payment_status,
                     status, priority, notes, created_by)
                 VALUES (?,?,?,?, ?,?,?, ?,?,?, ?,?,?,?, ?,?,?,?)",
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
                    'pending',
                    'normal',
                    sanitize($input['notes'] ?? ''),
                    $currentUserId,
                ]
            );
            $jobOrderId = $db->lastInsertId();

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

            $response['success'] = true;
            $response['message'] = 'Job order created successfully';
            $response['data']    = ['id' => $jobOrderId, 'job_order_number' => $joNumber];
            http_response_code(201);
            break;

        // ── PUT (update) ───────────────────────────────────────────────────────
        case 'PUT':
            if (!$id) throw new Exception('Job order ID is required');
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input) throw new Exception('Invalid JSON payload');

            // Get current JO to find customer_id and vehicle_id
            $jo = $db->fetch("SELECT customer_id, vehicle_id, job_order_number FROM job_orders WHERE id=?", [$id]);
            if (!$jo) throw new Exception('Job order not found');

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
            $db->query(
                "UPDATE job_orders SET status=?, payment_status=?, payment_method=?, partial_amount=?, notes=? WHERE id=?",
                [
                    sanitize($input['status']         ?? 'pending'),
                    sanitize($input['payment_status'] ?? 'pending'),
                    sanitize($input['payment_method'] ?? 'cash'),
                    $editPartial,
                    sanitize($input['notes']          ?? ''),
                    $id,
                ]
            );

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
            }

            logActivity($currentUserId, 'update_job_order', "Updated job order #{$jo['job_order_number']}");
            $response['success'] = true;
            $response['message'] = 'Job order updated successfully';
            break;

        // ── DELETE ───────────────────────────────────────────────────────────
        case 'DELETE':
            if (!$id) throw new Exception('Job order ID is required');
            if ($currentUserRole !== 'admin') throw new Exception('Only admins can delete job orders');

            $jo = $db->fetch("SELECT job_order_number FROM job_orders WHERE id=?", [$id]);
            if (!$jo) throw new Exception('Job order not found');

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

            $db->query("DELETE FROM job_orders WHERE id=?", [$id]);
            logActivity($currentUserId, 'delete_job_order', "Deleted job order #{$jo['job_order_number']}");

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
