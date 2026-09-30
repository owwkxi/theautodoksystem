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
require_once __DIR__ . '/../includes/sms.php';
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
$emailError = '';
$smsError = '';

$normalizePhoneInput = static function ($value) {
    return preg_replace('/\D+/', '', (string)($value ?? ''));
};
$isValidCustomerPhone = static function ($phone): bool {
    return (bool)preg_match('/^09\d{9}$/', (string)$phone);
};

$allowedJoStatuses = ['pending', 'ongoing', 'under_inspection', 'car_washing', 'completed', 'released', 'returned_for_revision', 'cancelled'];
$runningJoStatuses = ['ongoing', 'under_inspection', 'returned_for_revision'];
$activeJoStatuses = ['pending', 'ongoing', 'under_inspection', 'car_washing', 'released', 'returned_for_revision'];

$normalizeJoStatus = static function ($status) {
    $clean = sanitize((string)$status);
    $aliases = [
        'for_approval' => 'under_inspection',
        'return_for_revision' => 'returned_for_revision',
    ];
    return $aliases[$clean] ?? $clean;
};

$calculateJoDiscount = static function ($input, $subtotal, $partsCost) {
    $base = (float)$subtotal + (float)$partsCost;
    $discTypeFront = (string)($input['discount_type'] ?? 'none');
    $discVal = (float)($input['discount_value'] ?? 0);
    $discountAmount = 0.0;
    $discountPercentage = 0.0;
    $dbDiscType = 'none';

    switch ($discTypeFront) {
        case 'percentage':
            $dbDiscType = 'custom';
            $discountAmount = $base * ($discVal / 100);
            $discountPercentage = $discVal;
            break;
        case 'fixed':
            $dbDiscType = 'custom';
            $discountAmount = $discVal;
            break;
        case 'senior':
            $dbDiscType = 'senior_citizen';
            $discountAmount = $base * 0.20;
            $discountPercentage = 20;
            break;
        case 'pwd':
            $dbDiscType = 'pwd';
            $discountAmount = $base * 0.20;
            $discountPercentage = 20;
            break;
        default:
            $dbDiscType = 'none';
            $discountAmount = 0;
            break;
    }

    $discountAmount = min(max(0.0, $discountAmount), max(0.0, $base));
    $total = max(0.0, $base - $discountAmount);

    return [
        'subtotal' => $subtotal,
        'parts_cost' => $partsCost,
        'discount_type' => $dbDiscType,
        'discount_amount' => $discountAmount,
        'discount_percentage' => $discountPercentage,
        'total_amount' => $total,
    ];
};

$calculateJoFinancials = static function ($items, $products, $input, $fallbackSubtotal = 0.0, $fallbackPartsCost = 0.0) use ($calculateJoDiscount) {
    $subtotal = (float)$fallbackSubtotal;
    if (is_array($items)) {
        $subtotal = 0.0;
        foreach ($items as $item) {
            $qty = max(1, (int)($item['qty'] ?? $item['quantity'] ?? 1));
            $basePrice = isset($item['base_price']) ? (float)$item['base_price'] : (float)($item['price'] ?? 0);
            $laborCost = isset($item['labor_cost']) ? (float)$item['labor_cost'] : (float)($item['labor'] ?? 0);
            $subtotal += ($basePrice * $qty) + $laborCost;
        }
    }

    $partsCost = (float)$fallbackPartsCost;
    if (is_array($products)) {
        $partsCost = 0.0;
        foreach ($products as $prod) {
            $qty = max(1, (int)($prod['qty'] ?? $prod['quantity'] ?? 1));
            $unitPrice = (float)($prod['price'] ?? $prod['unit_price'] ?? 0);
            $partsCost += $unitPrice * $qty;
        }
    }

    return $calculateJoDiscount($input, $subtotal, $partsCost);
};

$calculateJoFinancialsFromDb = static function ($db, $jobOrderId, $input) use ($calculateJoDiscount) {
    $serviceRows = $db->fetchAll(
        "SELECT service_price, labor_cost, quantity FROM job_order_services WHERE job_order_id = ?",
        [$jobOrderId]
    );
    $subtotal = 0.0;
    foreach ($serviceRows as $row) {
        $qty = max(1, (int)($row['quantity'] ?? 1));
        $basePrice = (float)($row['service_price'] ?? 0);
        $laborCost = (float)($row['labor_cost'] ?? 0);
        $subtotal += ($basePrice * $qty) + $laborCost;
    }

    $productRows = $db->fetchAll(
        "SELECT unit_price, quantity FROM job_order_products WHERE job_order_id = ?",
        [$jobOrderId]
    );
    $partsCost = 0.0;
    foreach ($productRows as $row) {
        $qty = max(1, (int)($row['quantity'] ?? 1));
        $partsCost += (float)($row['unit_price'] ?? 0) * $qty;
    }

    return $calculateJoDiscount($input, $subtotal, $partsCost);
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

    if (in_array($role, ['service_adviser', 'chief_mechanic', 'lead_man', 'stockman'], true)) {
        return true;
    }

    if ($role === 'technician') {
        return in_array($jobOrderStatus, $activeJoStatuses, true)
            && $isAssignedTechnician($db, (int)$jobOrderId, (int)$userId);
    }

    return false;
};

$deductJobOrderStock = static function ($db, $jobOrderId, $jobOrderNumber, $userId) {
    $products = $db->fetchAll(
        "SELECT product_id, quantity FROM job_order_products WHERE job_order_id=? AND product_id IS NOT NULL",
        [$jobOrderId]
    );

    foreach ($products as $product) {
        $productId = (int)$product['product_id'];
        $quantity = max(1, (int)$product['quantity']);
        $stock = $db->fetch("SELECT quantity, product_name FROM products WHERE id=?", [$productId]);
        if (!$stock || (int)$stock['quantity'] < $quantity) {
            throw new Exception("Insufficient stock for: " . ($stock['product_name'] ?? "product #$productId") . " (available: " . (int)($stock['quantity'] ?? 0) . ")");
        }

        $updatedStock = $db->query("UPDATE products SET quantity = quantity - ? WHERE id=? AND quantity >= ?", [$quantity, $productId, $quantity]);
        if ($updatedStock->rowCount() !== 1) {
            throw new Exception("Insufficient stock for: " . ($stock['product_name'] ?? "product #$productId"));
        }
        $db->query(
            "INSERT INTO inventory_transactions (product_id, transaction_type, quantity, reference_type, reference_id, notes, created_by) VALUES (?,?,?,?,?,?,?)",
            [$productId, 'stock_out', $quantity, 'job_order', $jobOrderId, "Started JO #{$jobOrderNumber} - stock deducted", $userId]
        );
    }
};

$restoreJobOrderStock = static function ($db, $jobOrderId, $jobOrderNumber, $userId) {
    $products = $db->fetchAll(
        "SELECT product_id, quantity FROM job_order_products WHERE job_order_id=? AND product_id IS NOT NULL",
        [$jobOrderId]
    );

    foreach ($products as $product) {
        $productId = (int)$product['product_id'];
        $quantity = max(1, (int)$product['quantity']);
        $db->query("UPDATE products SET quantity = quantity + ? WHERE id=?", [$quantity, $productId]);
        $db->query(
            "INSERT INTO inventory_transactions (product_id, transaction_type, quantity, reference_type, reference_id, notes, created_by) VALUES (?,?,?,?,?,?,?)",
            [$productId, 'return', $quantity, 'job_order', $jobOrderId, "JO #{$jobOrderNumber} returned to pending - stock restored", $userId]
        );
    }
};

$validateInventoryProducts = static function ($db, $products) {
    if (!is_array($products)) {
        return;
    }

    foreach ($products as $prod) {
        $prodId = (int)($prod['id'] ?? $prod['product_id'] ?? 0);
        $prodQty = max(1, (int)($prod['qty'] ?? $prod['quantity'] ?? 1));
        if ($prodId <= 0) {
            continue;
        }

        $stock = $db->fetch("SELECT quantity, product_name FROM products WHERE id=?", [$prodId]);
        if (!$stock) {
            throw new Exception("Insufficient stock for: product #{$prodId}");
        }

        if ((int)$stock['quantity'] < $prodQty) {
            throw new Exception("Insufficient stock for: " . ($stock['product_name'] ?? "product #{$prodId}") . " (available: " . (int)$stock['quantity'] . ")");
        }
    }
};

// Check whether inventory is currently held by a JO. A previous deduction that was
// returned must not prevent stock from being deducted when the JO starts again.
$joHasDeductedStock = static function ($db, $jobOrderId) {
    $row = $db->fetch(
        "SELECT COALESCE(SUM(CASE
                    WHEN transaction_type = 'stock_out' THEN quantity
                    WHEN transaction_type = 'return' THEN -quantity
                    ELSE 0
                END), 0) AS held_quantity
         FROM inventory_transactions
         WHERE reference_type='job_order' AND reference_id=?",
        [(int)$jobOrderId]
    );
    return ((int)($row['held_quantity'] ?? 0)) > 0;
};

// Validate stock for a JO that has ALREADY consumed inventory.
// Compares required qty against (current stock + what this JO already holds),
// so editing an active JO never falsely blocks unchanged products.
$validateInventoryProductsForActiveJo = static function ($db, $products, $jobOrderId) {
    if (!is_array($products)) return;
    foreach ($products as $prod) {
        $prodId  = (int)($prod['id'] ?? $prod['product_id'] ?? 0);
        $prodQty = max(1, (int)($prod['qty'] ?? $prod['quantity'] ?? 1));
        if ($prodId <= 0) continue;
        $stock = $db->fetch("SELECT quantity, product_name FROM products WHERE id=?", [$prodId]);
        if (!$stock) throw new Exception("Insufficient stock for: product #{$prodId}");
        // How much of this product is already committed to this JO
        $held = $db->fetch(
            "SELECT COALESCE(SUM(quantity),0) AS held FROM job_order_products WHERE job_order_id=? AND product_id=?",
            [(int)$jobOrderId, $prodId]
        );
        $heldQty = (int)($held['held'] ?? 0);
        $available = (int)$stock['quantity'] + $heldQty;
        if ($available < $prodQty) {
            throw new Exception("Insufficient stock for: " . ($stock['product_name'] ?? "product #{$prodId}") . " (available: {$available})");
        }
    }
};

// ── Shared: award all completion points for a JO ─────────────────────────────
// Called from both PUT (Edit modal) and PATCH (status dropdown) when a JO
// transitions to 'completed'. Extracts all queries that are the same for
// every technician on the JO (avg time, job time, revision check) outside the
// per-tech loop so they only run once.
$awardCompletionPoints = static function ($db, $jobOrderId, $jobOrderNumber, $userId) {
    // Always read total_amount fresh from DB — never trust the request payload.
    $joRow = $db->fetch("SELECT total_amount FROM job_orders WHERE id=? LIMIT 1", [(int)$jobOrderId]);
    $joTotal = (float)($joRow['total_amount'] ?? 0);

    // Technicians eligible for completion points:
    // active (assigned/working/completed) + removed as Absent or Transfer
    $techs = $db->fetchAll(
        "SELECT technician_id, COALESCE(is_assist,0) AS is_assist, COALESCE(work_duration,0) AS work_duration
         FROM job_order_technicians
         WHERE job_order_id=?
           AND (status IN ('assigned','working','completed')
                OR (status='removed' AND LOWER(TRIM(COALESCE(notes,''))) IN ('absent','transfer')))",
        [(int)$jobOrderId]
    );
    if (empty($techs)) return;

    // Average JO work time — used once for all techs on this JO.
    $avgJoWorkTime = 0;
    try {
        $avgRow = $db->fetch(
            "SELECT AVG(CAST(status_timer_seconds AS DECIMAL(10,2))) AS avg
             FROM job_orders WHERE status_timer_seconds > 0",
            []
        );
        $avgJoWorkTime = (float)($avgRow['avg'] ?? 0);
        if ($avgJoWorkTime <= 0) {
            $avgFb = $db->fetch(
                "SELECT AVG(total_duration) AS avg FROM (
                     SELECT SUM(CAST(work_duration AS DECIMAL(10,2))) AS total_duration
                     FROM job_order_technicians WHERE work_duration > 0
                     GROUP BY job_order_id
                 ) t",
                []
            );
            $avgJoWorkTime = (float)($avgFb['avg'] ?? 0);
        }
    } catch (Exception $e) {}

    // This JO's elapsed time — queried once, shared across all techs.
    $jobWorkTime = 0;
    try {
        $jtRow = $db->fetch(
            "SELECT COALESCE(status_timer_seconds,0) AS secs FROM job_orders WHERE id=? LIMIT 1",
            [(int)$jobOrderId]
        );
        $jobWorkTime = (float)($jtRow['secs'] ?? 0);
        if ($jobWorkTime <= 0) {
            $jtSum = $db->fetch(
                "SELECT SUM(CAST(work_duration AS DECIMAL(10,2))) AS total
                 FROM job_order_technicians WHERE job_order_id=? AND work_duration > 0",
                [(int)$jobOrderId]
            );
            $jobWorkTime = (float)($jtSum['total'] ?? 0);
        }
    } catch (Exception $e) {}

    // Revision check — queried once, shared across all techs.
    $wasReturned = false;
    try {
        $retRow = $db->fetch(
            "SELECT id FROM job_order_status_history
             WHERE job_order_id=?
               AND to_status IN ('returned_for_revision','ongoing')
               AND from_status IN ('under_inspection','released') LIMIT 1",
            [(int)$jobOrderId]
        );
        if ($retRow) $wasReturned = true;
    } catch (Exception $e) {}
    try {
        $inspRow = $db->fetch(
            "SELECT id FROM job_order_inspections WHERE job_order_id=? AND result='revision' LIMIT 1",
            [(int)$jobOrderId]
        );
        if ($inspRow) $wasReturned = true;
    } catch (Exception $e) {}

    foreach ($techs as $t) {
        $techId  = (int)$t['technician_id'];
        $isAssist = (int)$t['is_assist'];

        // Skip if already awarded for this JO (duplicate guard).
        try {
            $dup = $db->fetch(
                "SELECT id FROM technician_points WHERE job_order_id=? AND technician_id=? AND reason LIKE '%Completed%' LIMIT 1",
                [(int)$jobOrderId, $techId]
            );
            if ($dup) continue;
        } catch (Exception $e) {}

        // Completion points
        recordTechnicianPoints($techId, $jobOrderId, $isAssist ? 'JO Completed (Assistant)' : 'JO Completed (Lead)', $isAssist ? 5 : 10);

        // Revenue bonus
        $revPts = $isAssist ? round($joTotal / 1000 * 0.5, 1) : round($joTotal / 1000, 1);
        if ($revPts > 0) recordTechnicianPoints($techId, $jobOrderId, 'Revenue Bonus', $revPts);

        // Speed bonus
        if ($avgJoWorkTime > 0 && $jobWorkTime > 0) {
            $ratio    = $jobWorkTime / $avgJoWorkTime;
            $speedPts = $ratio <= 0.5 ? 5 : ($ratio <= 0.75 ? 4 : ($ratio <= 1.0 ? 3 : ($ratio <= 1.25 ? 2 : 1)));
            recordTechnicianPoints($techId, $jobOrderId, 'Speed Bonus', $speedPts);
        }

        // Clean completion
        if (!$wasReturned) {
            recordTechnicianPoints($techId, $jobOrderId, 'Clean Completion', 3);
        }
    }
};

// ── Shared: notify the customer when a JO is completed ───────────────────────
$sendCompletionEmail = static function ($db, $jobOrderId) {
    $activeShop = getActiveShopOption();
    if (($activeShop['key'] ?? '') !== 'autodok_main') {
        return '';
    }

    $joRow = $db->fetch(
        "SELECT jo.job_order_number, c.full_name AS customer_name, c.email AS customer_email,
                v.brand AS vehicle_make, v.model AS vehicle_model,
                v.plate_number AS vehicle_license, jo.total_amount
         FROM job_orders jo
         LEFT JOIN customers c ON c.id = jo.customer_id
         LEFT JOIN vehicles v ON v.id = jo.vehicle_id
         WHERE jo.id=? LIMIT 1",
        [(int)$jobOrderId]
    );

    if (!$joRow) {
        return 'Job order not found';
    }

    $customerEmail = trim((string)($joRow['customer_email'] ?? ''));
    if (!isValidEmail($customerEmail)) {
        return 'Customer email is missing or invalid';
    }
    if (trim((string)SMTP_USER) === '' || trim((string)SMTP_PASS) === '') {
        return 'SMTP username or password is not configured';
    }

    $customerName = trim((string)($joRow['customer_name'] ?? 'Customer')) ?: 'Customer';
    $jobOrderNumber = (string)($joRow['job_order_number'] ?? $jobOrderId);
    $vehicle = trim(implode(' ', array_filter([
        $joRow['vehicle_make'] ?? '',
        $joRow['vehicle_model'] ?? '',
    ])));
    $plate = trim((string)($joRow['vehicle_license'] ?? ''));
    $vehicleLabel = $vehicle !== '' ? $vehicle : 'your vehicle';
    if ($plate !== '') {
        $vehicleLabel .= " ({$plate})";
    }
    $totalAmount = number_format((float)($joRow['total_amount'] ?? 0), 2);
    $template = getCompletionEmailSettings();
    $companyDetails = getCompletionEmailCompanyDetails($template);
    $placeholders = [
        '{customer_name}' => $customerName,
        '{job_order_number}' => $jobOrderNumber,
        '{vehicle}' => $vehicleLabel,
        '{total_amount}' => $totalAmount,
        '{company_name}' => $companyDetails['company_contact'],
        '{company_contact}' => $companyDetails['company_contact'],
        '{company_contact_number}' => $companyDetails['company_contact_number'],
        '{company_email}' => $companyDetails['company_email'],
        '{company_address}' => $companyDetails['company_address'],
    ];
    $subject = strtr((string)$template['subject'], $placeholders);
    $message = strtr((string)$template['message'], $placeholders);
    $message = appendCompletionEmailCompanyDetails($message, $companyDetails);
    $message = strtr($message, $placeholders);
    $socket = null;
    try {
        $transport = strtolower((string)(defined('SMTP_ENCRYPTION') ? SMTP_ENCRYPTION : 'tls')) === 'ssl'
            ? 'ssl://'
            : 'tcp://';
        $socket = stream_socket_client(
            $transport . SMTP_HOST . ':' . SMTP_PORT,
            $errorCode,
            $errorMessage,
            15,
            STREAM_CLIENT_CONNECT
        );
        if (!$socket) {
            throw new RuntimeException("SMTP connection failed: {$errorMessage} ({$errorCode})");
        }
        stream_set_timeout($socket, 15);

        $readResponse = static function ($socket, $expectedCode) {
            $response = '';
            while (($line = fgets($socket)) !== false) {
                $response .= $line;
                if (isset($line[3]) && $line[3] === ' ') {
                    break;
                }
            }
            if ((int)substr($response, 0, 3) !== $expectedCode) {
                throw new RuntimeException("SMTP response error: " . trim($response));
            }
        };
        $sendCommand = static function ($socket, $command, $expectedCode) use ($readResponse) {
            fwrite($socket, $command . "\r\n");
            $readResponse($socket, $expectedCode);
        };

        $readResponse($socket, 220);
        $sendCommand($socket, 'EHLO autodok.local', 250);
        if ($transport === 'tcp://') {
            $sendCommand($socket, 'STARTTLS', 220);
            if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                throw new RuntimeException('Unable to establish SMTP TLS encryption');
            }
            $sendCommand($socket, 'EHLO autodok.local', 250);
        }
        $sendCommand($socket, 'AUTH LOGIN', 334);
        $sendCommand($socket, base64_encode(SMTP_USER), 334);
        $sendCommand($socket, base64_encode(SMTP_PASS), 235);
        $sendCommand($socket, 'MAIL FROM:<' . SMTP_FROM . '>', 250);
        $sendCommand($socket, 'RCPT TO:<' . $customerEmail . '>', 250);
        $sendCommand($socket, 'DATA', 354);

        $headers = [
            'From: ' . SMTP_FROM_NAME . ' <' . SMTP_FROM . '>',
            'Reply-To: ' . SMTP_FROM,
            'To: ' . $customerEmail,
            'Subject: ' . $subject,
            'MIME-Version: 1.0',
            'Content-Type: text/html; charset=UTF-8',
        ];
        $body = implode("\r\n", $headers) . "\r\n\r\n" . $message;
        $body = preg_replace('/^\./m', '..', $body);
        fwrite($socket, $body . "\r\n.\r\n");
        $readResponse($socket, 250);
        $sendCommand($socket, 'QUIT', 221);
        return '';
    } catch (Throwable $e) {
        error_log("Unable to send completion email for job order #{$jobOrderNumber}: " . $e->getMessage());
        return 'Email delivery failed: ' . $e->getMessage();
    } finally {
        if (is_resource($socket)) {
            fclose($socket);
        }
    }
};

// ── Shared: notify the customer by SMS when a JO is completed ─────────────────
$sendCompletionSms = static function ($db, $jobOrderId) {
    $joRow = $db->fetch(
        "SELECT jo.job_order_number, c.full_name AS customer_name, c.phone AS customer_phone,
                jo.total_amount
         FROM job_orders jo
         LEFT JOIN customers c ON c.id = jo.customer_id
         WHERE jo.id=? LIMIT 1",
        [(int)$jobOrderId]
    );

    if (!$joRow) {
        return 'Job order not found';
    }

    $customerName = trim((string)($joRow['customer_name'] ?? 'Customer')) ?: 'Customer';
    $jobOrderNumber = (string)($joRow['job_order_number'] ?? $jobOrderId);
    $totalAmount = number_format((float)($joRow['total_amount'] ?? 0), 2);
    $message = "Hi {$customerName}, your Job Order {$jobOrderNumber} is completed. "
        . "Total: PHP {$totalAmount}. Thank you for choosing Autodok.";

    return sendInfobipSms($joRow['customer_phone'] ?? '', $message);
};

// ── Shared: deduct points when JO is returned after release ──────────────────
$deductReturnAfterReleasePoints = static function ($db, $jobOrderId) {
    $techs = $db->fetchAll(
        "SELECT technician_id FROM job_order_technicians
         WHERE job_order_id=?
           AND (status IN ('assigned','working','completed')
                OR (status='removed' AND LOWER(TRIM(COALESCE(notes,''))) IN ('absent','transfer')))",
        [(int)$jobOrderId]
    );
    foreach ($techs as $jt) {
        $techId = (int)$jt['technician_id'];
        try {
            $cleanRow = $db->fetch(
                "SELECT id FROM technician_points WHERE job_order_id=? AND technician_id=? AND reason='Clean Completion' LIMIT 1",
                [(int)$jobOrderId, $techId]
            );
            if ($cleanRow) recordTechnicianPoints($techId, $jobOrderId, 'Clean Completion Reversed', -3);
        } catch (Exception $e) {}
        recordTechnicianPoints($techId, $jobOrderId, 'Return After Release', -10);
    }
};

try {
    $db = Database::getInstance();

    // Quick product availability validator: POST /api/job_orders.php?action=validate_products
    if ($method === 'POST' && (($_GET['action'] ?? '') === 'validate_products')) {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            echo json_encode(['success' => false, 'message' => 'Invalid JSON payload']);
            exit;
        }
        if (empty($input['csrf_token']) || !verifyCSRFToken($input['csrf_token'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
            exit;
        }
        $productsToCheck = is_array($input['products'] ?? null) ? $input['products'] : [];
        $result = [];
        foreach ($productsToCheck as $p) {
            $prodId = (int)($p['id'] ?? 0);
            $reqQty = max(1, (int)($p['qty'] ?? 1));
            if ($prodId <= 0) {
                $result[] = ['id' => $prodId, 'ok' => true, 'stock' => null];
                continue;
            }
            $stockRow = $db->fetch("SELECT id, quantity, product_name FROM products WHERE id = ?", [$prodId]);
            if (!$stockRow) {
                $result[] = ['id' => $prodId, 'ok' => false, 'stock' => 0, 'message' => 'Product not found'];
                continue;
            }
            $ok = ((int)$stockRow['quantity'] >= $reqQty);
            $result[] = ['id' => $prodId, 'ok' => $ok, 'stock' => (int)$stockRow['quantity'], 'name' => $stockRow['product_name']];
        }
        echo json_encode(['success' => true, 'data' => ['result' => $result]]);
        exit;
    }

    // ── Special: Add payment record ─────────────────────────────────────────
    if ($method === 'POST' && ($_GET['action'] ?? '') === 'add_payment') {
        if (!in_array($currentUserRole, ['admin', 'cashier', 'service_adviser'], true)) {
            throw new Exception('Insufficient permissions');
        }
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) throw new Exception('Invalid JSON payload');
        if (empty($input['csrf_token']) || !verifyCSRFToken($input['csrf_token'])) {
            throw new Exception('Invalid CSRF token');
        }
        $joId   = (int)($input['job_order_id'] ?? 0);
        $amount = (float)($input['amount'] ?? 0);
        $method_pay = sanitize($input['payment_method'] ?? 'cash');
        $ref    = sanitize($input['reference_number'] ?? '');
        $notes  = sanitize($input['notes'] ?? '');
        $paidBy = sanitize($input['paid_by'] ?? '');
        $payDate = sanitize($input['payment_date'] ?? date('Y-m-d H:i:s'));

        if ($joId <= 0) throw new Exception('Invalid job order');
        if ($amount <= 0) throw new Exception('Amount must be greater than 0');

        $jo = $db->fetch("SELECT id, total_amount, partial_amount, payment_status, job_order_number FROM job_orders WHERE id=?", [$joId]);
        if (!$jo) throw new Exception('Job order not found');

        $db->query(
            "INSERT INTO job_order_payments (job_order_id, amount, payment_method, reference_number, notes, paid_by, created_by, payment_date) VALUES (?,?,?,?,?,?,?,?)",
            [$joId, $amount, $method_pay, $ref, $notes, $paidBy, $currentUserId, $payDate]
        );

        // Recalculate total paid and update payment_status
        $totalPaid = (float)$db->fetch("SELECT COALESCE(SUM(amount),0) AS total FROM job_order_payments WHERE job_order_id=?", [$joId])['total'];
        $totalAmount = (float)$jo['total_amount'];
        $newStatus = $totalPaid >= $totalAmount ? 'paid' : ($totalPaid > 0 ? 'partial' : 'pending');
        $paymentDate = in_array($newStatus, ['paid','partial'], true) ? $payDate : null;
        $db->query("UPDATE job_orders SET payment_status=?, partial_amount=?, payment_date=? WHERE id=?",
            [$newStatus, min($totalPaid, $totalAmount), $paymentDate, $joId]);


        logActivity($currentUserId, 'add_payment', "Added payment ₱" . number_format($amount,2) . " for JO #{$jo['job_order_number']}");

        $response['success'] = true;
        $response['message'] = 'Payment recorded successfully';
        $response['data'] = ['payment_status' => $newStatus, 'total_paid' => $totalPaid];
        echo json_encode($response);
        exit;
    }

    if ($method === 'POST' && ($_GET['action'] ?? '') === 'update_payment') {
        if (!in_array($currentUserRole, ['admin', 'cashier', 'service_adviser'], true)) {
            throw new Exception('Insufficient permissions');
        }
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) throw new Exception('Invalid JSON payload');
        if (empty($input['csrf_token']) || !verifyCSRFToken($input['csrf_token'])) {
            throw new Exception('Invalid CSRF token');
        }

        $joId = (int)($id ?: ($input['job_order_id'] ?? 0));
        $paymentId = (int)($input['payment_id'] ?? 0);
        $amount = (float)($input['amount'] ?? 0);
        if ($joId <= 0 || $paymentId <= 0) {
            throw new Exception('Invalid payment record');
        }
        if ($amount <= 0) {
            throw new Exception('Amount must be greater than 0');
        }

        $existing = $db->fetch("SELECT * FROM job_order_payments WHERE id = ? AND job_order_id = ? LIMIT 1", [$paymentId, $joId]);
        if (!$existing) {
            throw new Exception('Payment not found');
        }
        $jo = $db->fetch("SELECT id, total_amount, job_order_number FROM job_orders WHERE id=?", [$joId]);
        if (!$jo) {
            throw new Exception('Job order not found');
        }

        $paymentMethod = sanitize($input['payment_method'] ?? $existing['payment_method'] ?? 'cash');
        $referenceNumber = sanitize($input['reference_number'] ?? $existing['reference_number'] ?? '');
        $paidBy = sanitize($input['paid_by'] ?? $existing['paid_by'] ?? '');
        $notes = sanitize($input['notes'] ?? $existing['notes'] ?? '');
        $paymentDate = sanitize($input['payment_date'] ?? $existing['payment_date'] ?? date('Y-m-d H:i:s'));

        $db->query(
            "UPDATE job_order_payments
             SET amount = ?, payment_method = ?, reference_number = ?, notes = ?, paid_by = ?, payment_date = ?
             WHERE id = ? AND job_order_id = ?",
            [$amount, $paymentMethod, $referenceNumber, $notes, $paidBy, $paymentDate, $paymentId, $joId]
        );

        $totalPaid = (float)$db->fetch("SELECT COALESCE(SUM(amount),0) AS total FROM job_order_payments WHERE job_order_id = ?", [$joId])['total'];
        $totalAmount = (float)$jo['total_amount'];
        $newStatus = $totalPaid >= $totalAmount ? 'paid' : ($totalPaid > 0 ? 'partial' : 'pending');
        $latestPaymentDate = $db->fetch(
            "SELECT payment_date FROM job_order_payments WHERE job_order_id = ? ORDER BY payment_date DESC, id DESC LIMIT 1",
            [$joId]
        );
        $db->query(
            "UPDATE job_orders SET payment_status = ?, partial_amount = ?, payment_date = ? WHERE id = ?",
            [$newStatus, min($totalPaid, $totalAmount), $newStatus === 'pending' ? null : ($latestPaymentDate['payment_date'] ?? null), $joId]
        );

        logActivity($currentUserId, 'update_payment', "Updated payment #{$paymentId} for JO #{$jo['job_order_number']}");
        $response['success'] = true;
        $response['message'] = 'Payment updated successfully';
        $response['data'] = ['payment_status' => $newStatus, 'total_paid' => $totalPaid];
        echo json_encode($response);
        exit;
    }

    if ($method === 'POST' && ($_GET['action'] ?? '') === 'delete_payment') {
        if (!in_array($currentUserRole, ['admin', 'cashier', 'service_adviser'], true)) {
            throw new Exception('Insufficient permissions');
        }
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) throw new Exception('Invalid JSON payload');
        if (empty($input['csrf_token']) || !verifyCSRFToken($input['csrf_token'])) {
            throw new Exception('Invalid CSRF token');
        }

        $joId = (int)($id ?: ($input['job_order_id'] ?? 0));
        $paymentId = (int)($input['payment_id'] ?? 0);
        if ($joId <= 0 || $paymentId <= 0) {
            throw new Exception('Invalid payment record');
        }

        $existing = $db->fetch("SELECT * FROM job_order_payments WHERE id = ? AND job_order_id = ? LIMIT 1", [$paymentId, $joId]);
        if (!$existing) {
            throw new Exception('Payment not found');
        }
        $jo = $db->fetch("SELECT id, total_amount, job_order_number FROM job_orders WHERE id=?", [$joId]);
        if (!$jo) {
            throw new Exception('Job order not found');
        }

        $db->query("DELETE FROM job_order_payments WHERE id = ? AND job_order_id = ?", [$paymentId, $joId]);

        $totalPaid = (float)$db->fetch("SELECT COALESCE(SUM(amount),0) AS total FROM job_order_payments WHERE job_order_id = ?", [$joId])['total'];
        $totalAmount = (float)$jo['total_amount'];
        $newStatus = $totalPaid >= $totalAmount ? 'paid' : ($totalPaid > 0 ? 'partial' : 'pending');
        $latestPaymentDate = $db->fetch(
            "SELECT payment_date FROM job_order_payments WHERE job_order_id = ? ORDER BY payment_date DESC, id DESC LIMIT 1",
            [$joId]
        );
        $db->query(
            "UPDATE job_orders SET payment_status = ?, partial_amount = ?, payment_date = ? WHERE id = ?",
            [$newStatus, min($totalPaid, $totalAmount), $newStatus === 'pending' ? null : ($latestPaymentDate['payment_date'] ?? null), $joId]
        );

        logActivity($currentUserId, 'delete_payment', "Deleted payment #{$paymentId} for JO #{$jo['job_order_number']}");
        $response['success'] = true;
        $response['message'] = 'Payment deleted successfully';
        $response['data'] = ['payment_status' => $newStatus, 'total_paid' => $totalPaid];
        echo json_encode($response);
        exit;
    }

    switch ($method) {

        // ── GET ──────────────────────────────────────────────────────────────
        case 'GET':
            if (($_GET['action'] ?? '') === 'customer_lookup') {
                if (!in_array($currentUserRole, ['admin', 'cashier', 'service_adviser'], true)) {
                    throw new Exception('Insufficient permissions');
                }
                $plate = strtoupper(trim((string)($_GET['plate'] ?? '')));
                if ($plate === '') {
                    throw new Exception('Enter a vehicle plate number.');
                }
                $customer = $db->fetch(
                    "SELECT c.id, c.full_name AS customer_name, c.phone AS customer_phone,
                           c.email AS customer_email, c.address AS customer_address,
                           v.brand AS vehicle_make, v.model AS vehicle_model,
                           v.year_model AS vehicle_year, v.plate_number AS vehicle_license,
                           v.color AS vehicle_color
                    FROM customers c
                    INNER JOIN vehicles v ON v.customer_id = c.id
                    WHERE UPPER(TRIM(v.plate_number)) = ?
                    ORDER BY v.created_at DESC, v.id DESC
                    LIMIT 1",
                    [$plate]
                );
                $response['success'] = true;
                $response['data'] = $customer ?: null;
                $response['message'] = $customer ? 'Previous customer record found.' : 'No previous record found.';
                break;
            }
            $jobOrderModel = new JobOrder();

            // Special action: technician availability
            if (($_GET['action'] ?? '') === 'technician_availability') {
                $activeJoStatuses = ['ongoing', 'under_inspection', 'returned_for_revision', 'car_washing'];
                // Busy = has an open work_session (end_time IS NULL) on an active JO
                $busyRows = $db->fetchAll(
                    "SELECT DISTINCT jot.technician_id
                     FROM job_order_technicians jot
                     INNER JOIN job_orders jo ON jo.id = jot.job_order_id
                     INNER JOIN work_sessions ws ON ws.job_order_technician_id = jot.id
                     WHERE jo.status IN ('ongoing','under_inspection','returned_for_revision','car_washing')
                       AND ws.end_time IS NULL
                       AND jot.status != 'removed'",
                    []
                );
                $busyIds = array_column($busyRows, 'technician_id');
                $response['success'] = true;
                $response['data'] = ['busy_technician_ids' => array_map('intval', $busyIds)];
                break;
            }

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
                     LEFT JOIN job_order_technicians jot ON jot.job_order_id = jo.id AND jot.status IN ('assigned','working','completed')
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
                    "SELECT s.id, s.full_name,
                        jot.id AS jot_id, jot.assigned_at, jot.started_at, jot.completed_at,
                        jot.status AS assignment_status,
                        COALESCE(jot.work_duration, 0) AS work_duration,
                        COALESCE(jot.is_assist, 0) AS is_assist_stored
                     FROM job_order_technicians jot
                     INNER JOIN staff s ON s.id = jot.technician_id
                     WHERE jot.job_order_id = ?
                       AND jot.id = (
                           SELECT MAX(j2.id) FROM job_order_technicians j2
                           WHERE j2.job_order_id = jot.job_order_id
                             AND j2.technician_id = jot.technician_id
                       )
                     ORDER BY jot.assigned_at ASC",
                    [$id]
                );

                foreach ($techRows as &$tr) {
                    $isActive = in_array($tr['assignment_status'], ['assigned', 'working']);
                    $banked = max(0, (int)$tr['work_duration']);
                    if ($isActive) {
                        // Each technician's own total = their banked (frozen) time plus
                        // whatever has elapsed since THEIR OWN started_at — never the
                        // JO's full elapsed time. A technician added partway through (or
                        // re-added after being removed) therefore starts clean at their
                        // own started_at instead of inheriting the JO's running total.
                        $liveSecs = !empty($tr['started_at']) ? max(0, time() - strtotime($tr['started_at'])) : 0;
                        $tr['total_seconds'] = $banked + $liveSecs;
                        $tr['has_open_session'] = !empty($tr['started_at']) ? 1 : 0;
                    } else {
                        // Removed/completed: show saved snapshot (always positive)
                        $tr['total_seconds'] = $banked;
                        $tr['has_open_session'] = 0;
                    }
                    $tr['is_assist'] = (int)($tr['is_assist_stored'] ?? 0);
                    // Attach work sessions (activity log) for this technician
                    $tr['work_sessions'] = $db->fetchAll(
                        "SELECT start_time, end_time, duration, notes FROM work_sessions WHERE job_order_technician_id = ? ORDER BY start_time ASC",
                        [(int)$tr['jot_id']]
                    );
                }
                unset($tr);
                // Total seconds across all technicians (max of any individual, not sum — represents wall clock)
                // But store both: per-tech and JO total timer
                $jo['technicians'] = $techRows;
                $jo['technician_ids'] = array_values(array_map(
                    fn($t) => (int)$t['id'],
                    array_filter($techRows, fn($t) => in_array($t['assignment_status'], ['assigned', 'working', 'completed']))
                ));

                // Attach services and products
                $jo['services'] = $db->fetchAll(
                    "SELECT service_id, bundle_id, service_name, service_price, labor_cost, quantity, total, sub_items_json FROM job_order_services WHERE job_order_id = ?",
                    [$id]
                );
                $jo['products'] = $db->fetchAll(
                    "SELECT jop.product_id AS id, jop.product_name, p.product_code,
                            jop.unit_price, jop.unit_price AS price, jop.quantity, jop.quantity AS qty, jop.total
                     FROM job_order_products jop
                     LEFT JOIN products p ON p.id = jop.product_id
                     WHERE jop.job_order_id = ?",
                    [$id]
                );
                // Fetch payment records
                try {
                    $jo['payment_records'] = $db->fetchAll(
                        "SELECT id, amount, payment_method, reference_number, notes, paid_by, payment_date, created_at
                         FROM job_order_payments WHERE job_order_id = ? ORDER BY payment_date ASC",
                        [$id]
                    );
                } catch (Exception $e) {
                    $jo['payment_records'] = []; // table may not exist yet
                }
                // Fetch status change history
                try {
                    $jo['status_history'] = $db->fetchAll(
                        "SELECT from_status, to_status, changed_at, s.full_name AS changed_by_name
                         FROM job_order_status_history h
                         LEFT JOIN staff s ON s.id = h.changed_by
                         WHERE h.job_order_id = ?
                         ORDER BY h.changed_at ASC",
                        [$id]
                    );
                } catch (Exception $e) {
                    $jo['status_history'] = []; // table may not exist yet
                }
                $response['success'] = true;
                $response['data']    = $jo;
            } else {
                if (!in_array($currentUserRole, ['admin', 'cashier', 'service_adviser'], true)) {
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
            if (!in_array($currentUserRole, ['admin', 'cashier', 'service_adviser'], true)) {
                throw new Exception('Insufficient permissions');
            }
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input) throw new Exception('Invalid JSON payload');

            // CSRF
            if (empty($input['csrf_token']) || !verifyCSRFToken($input['csrf_token'])) {
                throw new Exception('Invalid CSRF token');
            }

            if (empty($input['customer_name']))  throw new Exception('Customer name is required');
            $input['customer_phone'] = $normalizePhoneInput($input['customer_phone'] ?? '');
            if (empty($input['customer_phone'])) throw new Exception('Customer phone is required');
            if (!$isValidCustomerPhone($input['customer_phone'])) {
                throw new Exception('Phone number must contain exactly 11 digits and start with 09.');
            }

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

            if ($existing && !(int)($db->fetch("SELECT COUNT(*) AS cnt FROM job_orders WHERE customer_id = ?", [$existing['id']])['cnt'] ?? 0)) {
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
            $items    = is_array($input['items'] ?? null) ? $input['items'] : [];
            $products = is_array($input['products'] ?? null) ? $input['products'] : [];

            $financialSummary = $calculateJoFinancials($items, $products, $input, 0.0, 0.0);
            $subtotal = (float)$financialSummary['subtotal'];
            $partsCost = (float)$financialSummary['parts_cost'];
            $dbDiscType = $financialSummary['discount_type'];
            $discountAmt = (float)$financialSummary['discount_amount'];
            $discPct = (float)$financialSummary['discount_percentage'];
            $total = (float)$financialSummary['total_amount'];

            // ── 4. Generate JO number ────────────────────────────────────────
            $joNumber = generateJobOrderNumber();

            // ── 5. Insert job order ──────────────────────────────────────────
            // ── 5. Insert job order ──────────────────────────────────────────
            // Start transaction to ensure atomic creation of JO and its related records
            $db->beginTransaction();
            $sourceEstimateId = (int)($input['source_estimate_id'] ?? 0);
            if ($sourceEstimateId > 0) {
                $sourceEstimate = $db->fetch(
                    "SELECT id, status FROM job_estimates WHERE id = ? FOR UPDATE",
                    [$sourceEstimateId]
                );
                if (!$sourceEstimate) {
                    throw new Exception('Source estimate not found.');
                }
            }
            $partialAmount = (float)($input['partial_amount'] ?? 0);
            if ($partialAmount < 0) $partialAmount = 0;
            if ($partialAmount > $total) $partialAmount = $total;

            $status = $normalizeJoStatus($input['status'] ?? 'pending');
            if (!in_array($status, $allowedJoStatuses, true)) {
                $status = 'pending';
            }

            // Inventory must be checked before creating a JO, including catalog bundle items.
            // But actual stock deduction should only happen once the JO is no longer pending.
            $validateInventoryProducts($db, $products);
            $shouldDeductOnCreate = $status !== 'pending';

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

            // Parse assist_ids for role assignment
            $assistIds = [];
            if (!empty($input['assist_ids']) && is_array($input['assist_ids'])) {
                foreach ($input['assist_ids'] as $aid) {
                    $aidInt = (int)$aid;
                    if ($aidInt > 0) $assistIds[] = $aidInt;
                }
            }

            $serviceAdviserId = !empty($technicianIds) ? (int)$technicianIds[0] : null;

            $paymentStatusOnCreate = sanitize($input['payment_status'] ?? 'pending');
            $paymentDateOnCreate = in_array($paymentStatusOnCreate, ['paid', 'partial'], true) ? $now : null;

            $db->query(
                "INSERT INTO job_orders
                    (job_order_number, customer_id, vehicle_id, service_adviser_id,
                     subtotal, labor_total, parts_total,
                     discount_type, discount_amount, discount_percentage,
                     partial_amount, total_amount, payment_method, payment_status, payment_date,
                     status, priority, notes, created_by,
                     status_timer_seconds, status_timer_started_at,
                     work_started_at, inspection_started_at, completed_at)
                 VALUES (?,?,?,?, ?,?,?, ?,?,?, ?,?,?,?,?, ?,?,?,?, ?,?,?,?,?)",
                [
                    $joNumber,
                    $customerId,
                    $vehicleId,
                    $serviceAdviserId,
                    $subtotal,
                    0,
                    $partsCost,
                    $dbDiscType,
                    $discountAmt,
                    $discPct,
                    $partialAmount,
                    $total,
                    sanitize($input['payment_method'] ?? 'cash'),
                    $paymentStatusOnCreate,
                    $paymentDateOnCreate,
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
            $techStartedAt = in_array($status, $runningJoStatuses, true) ? $now : null;
            foreach ($technicianIds as $techId) {
                $isAssist = in_array($techId, $assistIds, true) ? 1 : 0;
                $db->query(
                    "INSERT INTO job_order_technicians (job_order_id, technician_id, is_assist, assigned_at, started_at, status, work_duration)
                     VALUES (?, ?, ?, NOW(), ?, 'assigned', 0)",
                    [$jobOrderId, $techId, $isAssist, $techStartedAt]
                );
                if ($techStartedAt) {
                    $newJotId = $db->lastInsertId();
                    $db->query("INSERT INTO work_sessions (job_order_technician_id, start_time) VALUES (?,?)", [$newJotId, $now]);
                }
            }

            // ── 6. Insert job_order_services ─────────────────────────────────
            foreach ($items as $item) {
                $basePrice = isset($item['base_price']) ? (float)$item['base_price'] : (float)($item['price'] ?? 0);
                $laborCost = isset($item['labor_cost']) ? (float)$item['labor_cost'] : (float)($item['labor'] ?? 0);
                $unitTotal = $basePrice;
                $qty = (int)($item['qty'] ?? 1);
                $subItemsJson = !empty($item['selectedSubItems']) ? json_encode($item['selectedSubItems']) : null;

                if (($item['type'] ?? '') === 'service' && !empty($item['id'])) {
                    $db->query(
                        "INSERT INTO job_order_services (job_order_id, service_id, service_name, service_price, labor_cost, quantity, total, sub_items_json) VALUES (?,?,?,?,?,?,?,?)",
                        [$jobOrderId, $item['id'], sanitize($item['name']), $basePrice, $laborCost, $qty, ($unitTotal * $qty) + $laborCost, $subItemsJson]
                    );
                } elseif (($item['type'] ?? '') === 'bundle' && !empty($item['id'])) {
                    $db->query(
                        "INSERT INTO job_order_services (job_order_id, bundle_id, service_name, service_price, labor_cost, quantity, total, sub_items_json) VALUES (?,?,?,?,?,?,?,?)",
                        [$jobOrderId, $item['id'], sanitize($item['name']), $basePrice, $laborCost, $qty, ($unitTotal * $qty) + $laborCost, $subItemsJson]
                    );
                } elseif (($item['type'] ?? '') === 'custom' && !empty($item['name'])) {
                    // Custom entry — no service_id or bundle_id
                    $db->query(
                        "INSERT INTO job_order_services (job_order_id, service_name, service_price, labor_cost, quantity, total, sub_items_json) VALUES (?,?,?,?,?,?,?)",
                        [$jobOrderId, sanitize($item['name']), $basePrice, $laborCost, $qty, ($unitTotal * $qty) + $laborCost, $subItemsJson]
                    );
                }
            }

            // ── 7. Insert job_order_products; deduct stock for every status except pending ──
            foreach ($products as $prod) {
                $prodName   = sanitize($prod['name'] ?? '');
                $prodQty    = max(1, (int)($prod['qty'] ?? 1));
                $prodPrice  = (float)($prod['price'] ?? 0);
                $fromBundle = !empty($prod['fromBundle']);
                $prodId     = (int)($prod['id'] ?? 0);

                if (empty($prodName)) continue;

                if ($prodId > 0) {
                    $stock = $db->fetch("SELECT quantity, product_name FROM products WHERE id=?", [$prodId]);
                    if (!$stock) {
                        throw new Exception("Product #$prodId not found in inventory.");
                    }
                    // Stock was already validated by $validateInventoryProducts above.
                    // Only re-check here when we are about to deduct (non-pending status)
                    // to catch any race condition between validation and insert.
                    if ($shouldDeductOnCreate && (int)$stock['quantity'] < $prodQty) {
                        notifyRoles('inventory', 'Out of Stock Product', "Job order {$joNumber} blocked: {$stock['product_name']}", ['admin', 'cashier', 'service_adviser', 'stockman']);
                        throw new Exception("Insufficient stock for: {$stock['product_name']} (available: {$stock['quantity']})");
                    }

                    $db->query(
                        "INSERT INTO job_order_products (job_order_id, product_id, product_name, product_type, unit_price, quantity, total) VALUES (?,?,?,?,?,?,?)",
                        [$jobOrderId, $prodId, $prodName, 'parts', $prodPrice, $prodQty, $prodPrice * $prodQty]
                    );

                    if ($shouldDeductOnCreate) {
                        $updatedStock = $db->query("UPDATE products SET quantity = quantity - ? WHERE id=? AND quantity >= ?", [$prodQty, $prodId, $prodQty]);
                        if ($updatedStock->rowCount() !== 1) {
                            throw new Exception("Insufficient stock for: {$stock['product_name']}");
                        }
                        $db->query(
                            "INSERT INTO inventory_transactions (product_id, transaction_type, quantity, reference_type, reference_id, notes, created_by) VALUES (?,?,?,?,?,?,?)",
                            [$prodId, 'stock_out', $prodQty, 'job_order', $jobOrderId, "Used in JO #{$joNumber}", $currentUserId]
                        );
                    }
                } else {
                    // Custom product (no inventory link) — just insert the record
                    $db->query(
                        "INSERT INTO job_order_products (job_order_id, product_name, product_type, unit_price, quantity, total) VALUES (?,?,?,?,?,?)",
                        [$jobOrderId, $prodName, 'parts', $prodPrice, $prodQty, $prodPrice * $prodQty]
                    );
                }
            }

            $recalculatedSummary = $calculateJoFinancialsFromDb($db, $jobOrderId, $input);
            $recalculatedSubtotal = (float)$recalculatedSummary['subtotal'];
            $recalculatedPartsCost = (float)$recalculatedSummary['parts_cost'];
            $recalculatedDiscountAmt = (float)$recalculatedSummary['discount_amount'];
            $recalculatedDiscPct = (float)$recalculatedSummary['discount_percentage'];
            $recalculatedTotal = (float)$recalculatedSummary['total_amount'];
            $recalculatedPartialAmount = min($partialAmount, $recalculatedTotal);
            $db->query(
                "UPDATE job_orders
                 SET subtotal=?, parts_total=?, discount_type=?, discount_amount=?, discount_percentage=?, total_amount=?, partial_amount=?
                 WHERE id=?",
                [
                    $recalculatedSubtotal,
                    $recalculatedPartsCost,
                    $recalculatedSummary['discount_type'],
                    $recalculatedDiscountAmt,
                    $recalculatedDiscPct,
                    $recalculatedTotal,
                    $recalculatedPartialAmount,
                    $jobOrderId,
                ]
            );

            logActivity($currentUserId, 'create_job_order', "Created job order #{$joNumber}");

            // ── Save inline payment records ──────────────────────────────────
            $inlinePayments = is_array($input['inline_payments'] ?? null) ? $input['inline_payments'] : [];
            $totalInlinePaid = 0.0;
            try {
                foreach ($inlinePayments as $pay) {
                    $payAmt = (float)($pay['amount'] ?? 0);
                    if ($payAmt <= 0) continue;
                    $payMethod = sanitize($pay['method'] ?? 'cash');
                    $payRef    = sanitize($pay['reference'] ?? '');
                    $now = date('Y-m-d H:i:s');
                    $db->query(
                        "INSERT INTO job_order_payments (job_order_id, amount, payment_method, reference_number, created_by, payment_date) VALUES (?,?,?,?,?,?)",
                        [$jobOrderId, $payAmt, $payMethod, $payRef, $currentUserId, $now]
                    );
                    $totalInlinePaid += $payAmt;
                }
                if ($totalInlinePaid > 0) {
                    $newPayStatus = $totalInlinePaid >= $total ? 'paid' : 'partial';
                    $payDate = date('Y-m-d H:i:s');
                    $db->query("UPDATE job_orders SET payment_status=?, partial_amount=?, payment_date=? WHERE id=?",
                        [$newPayStatus, min($totalInlinePaid, $total), $payDate, $jobOrderId]);
                }
            } catch (Exception $e) {
                error_log("Inline payments save error: " . $e->getMessage()); // table may not exist
            }

            if ($sourceEstimateId > 0) {
                $db->query(
                    "UPDATE job_estimates SET status = 'converted' WHERE id = ?",
                    [$sourceEstimateId]
                );
            }

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

            // All inserts succeeded — commit transaction
            $db->commit();

            $response['success'] = true;
            $response['message'] = 'Job order created successfully';
            $response['data']    = ['id' => $jobOrderId, 'job_order_number' => $joNumber];

            // Record initial status in history
            try {
                $db->query(
                    "INSERT INTO job_order_status_history (job_order_id, from_status, to_status, changed_by, changed_at) VALUES (?,?,?,?,?)",
                    [$jobOrderId, null, 'pending', $currentUserId, date('Y-m-d H:i:s')]
                );
            } catch (Exception $e) { /* table may not exist yet */ }

            http_response_code(201);
            break;

        // ── PUT (update) ───────────────────────────────────────────────────────
        case 'PUT':
            if (!in_array($currentUserRole, ['admin', 'cashier', 'service_adviser'], true)) {
                throw new Exception('Insufficient permissions');
            }
            if (!$id) throw new Exception('Job order ID is required');
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input) throw new Exception('Invalid JSON payload');
            if (array_key_exists('customer_phone', $input)) {
                $input['customer_phone'] = $normalizePhoneInput($input['customer_phone'] ?? '');
                if ($input['customer_phone'] === '') {
                    throw new Exception('Customer phone is required');
                }
                if (!$isValidCustomerPhone($input['customer_phone'])) {
                    throw new Exception('Phone number must contain exactly 11 digits and start with 09.');
                }
            }

            // Get current JO to find customer_id and vehicle_id
            $jo = $db->fetch(
                "SELECT customer_id, vehicle_id, job_order_number, status, payment_status, payment_method,
                        status_timer_seconds, status_timer_started_at,
                        work_started_at, inspection_started_at, completed_at, updated_at
                 FROM job_orders WHERE id=?",
                [$id]
            );
            if (!$jo) throw new Exception('Job order not found');

            $expectedUpdatedAt = trim((string)($input['expected_updated_at'] ?? ''));
            if ($expectedUpdatedAt !== '' && (string)($jo['updated_at'] ?? '') !== $expectedUpdatedAt) {
                throw new Exception('Conflict: this job order was updated by another user. Please refresh and try again.');
            }

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
            $currentJoTotals = $db->fetch(
                "SELECT subtotal, parts_total FROM job_orders WHERE id = ?",
                [$id]
            );
            $newSubtotal = (float)($currentJoTotals['subtotal'] ?? 0);
            $newPartsCost = (float)($currentJoTotals['parts_total'] ?? 0);
            $itemsInput = isset($input['items']) && is_array($input['items']) ? $input['items'] : null;
            $productsInput = isset($input['products']) && is_array($input['products']) ? $input['products'] : null;

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

            // Keep JO edits isolated from other JO records. If a customer/vehicle is
            // shared by multiple job orders, create a new customer/vehicle row for this
            // JO instead of writing back to the shared record.
            $customerId = (int)($jo['customer_id'] ?? 0);
            $vehicleId = (int)($jo['vehicle_id'] ?? 0);

            $customerJoCount = $customerId > 0
                ? (int)($db->fetch("SELECT COUNT(*) AS cnt FROM job_orders WHERE customer_id = ?", [$customerId])['cnt'] ?? 0)
                : 0;
            if ($customerId > 0 && $customerJoCount > 1) {
                $year = date('Y');
                $lastCust = $db->fetch(
                    "SELECT customer_code FROM customers WHERE customer_code LIKE ? ORDER BY id DESC LIMIT 1",
                    ["CUST-{$year}-%"]
                );
                $custNum = $lastCust ? (intval(substr($lastCust['customer_code'], -4)) + 1) : 1;
                $custCode = sprintf("CUST-%s-%04d", $year, $custNum);

                $db->query(
                    "INSERT INTO customers (customer_code, full_name, phone, email, address) VALUES (?,?,?,?,?)",
                    [
                        $custCode,
                        sanitize($input['customer_name'] ?? ''),
                        sanitize($input['customer_phone'] ?? ''),
                        sanitize($input['customer_email'] ?? '') ?: null,
                        sanitize($input['customer_address'] ?? '') ?: null,
                    ]
                );
                $newCustomerId = (int)$db->lastInsertId();
                $db->query("UPDATE job_orders SET customer_id = ? WHERE id = ?", [$newCustomerId, $id]);
                $customerId = $newCustomerId;
            } else {
                $db->query(
                    "UPDATE customers SET full_name=?, phone=?, email=?, address=? WHERE id=?",
                    [
                        sanitize($input['customer_name']    ?? ''),
                        sanitize($input['customer_phone']   ?? ''),
                        sanitize($input['customer_email']   ?? '') ?: null,
                        sanitize($input['customer_address'] ?? '') ?: null,
                        $customerId,
                    ]
                );
            }

            $vehicleJoCount = $vehicleId > 0
                ? (int)($db->fetch("SELECT COUNT(*) AS cnt FROM job_orders WHERE vehicle_id = ?", [$vehicleId])['cnt'] ?? 0)
                : 0;
            if ($vehicleId > 0 && $vehicleJoCount > 1) {
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
                $newVehicleId = (int)$db->lastInsertId();
                $db->query("UPDATE job_orders SET vehicle_id = ? WHERE id = ?", [$newVehicleId, $id]);
                $vehicleId = $newVehicleId;
            } else {
                $db->query(
                    "UPDATE vehicles SET brand=?, model=?, year_model=?, plate_number=?, color=?, mileage=? WHERE id=?",
                    [
                        sanitize($input['vehicle_make']    ?? ''),
                        sanitize($input['vehicle_model']   ?? ''),
                        sanitize($input['vehicle_year']    ?? ''),
                        sanitize($input['vehicle_license'] ?? ''),
                        sanitize($input['vehicle_color']   ?? ''),
                        sanitize($input['vehicle_mileage'] ?? ''),
                        $vehicleId,
                    ]
                );
            }

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

            // Parse assist_ids to know which technicians are assistants
            $assistIds = [];
            if (!empty($input['assist_ids']) && is_array($input['assist_ids'])) {
                foreach ($input['assist_ids'] as $aid) {
                    $aidInt = (int)$aid;
                    if ($aidInt > 0) $assistIds[] = $aidInt;
                }
            }

            $serviceAdviserId = !empty($technicianIds) ? (int)$technicianIds[0] : null;

            $newStatus = $normalizeJoStatus($input['status'] ?? $jo['status'] ?? 'pending');
            if (!in_array($newStatus, $allowedJoStatuses, true)) {
                $newStatus = 'pending';
            }

            // Restrict only on pending → non-cancelled (first time stock leaves).
            // Broadened from 'ongoing' only — any status move out of pending must pass.
            // Once already deducted, never restrict status transitions.
            $alreadyDeductedPut = $joHasDeductedStock($db, $id);
            if (!$alreadyDeductedPut && ($jo['status'] ?? 'pending') === 'pending' && $newStatus !== 'pending' && $newStatus !== 'cancelled') {
                $statusProductsToCheck = is_array($productsInput) ? $productsInput : $db->fetchAll(
                    "SELECT product_id AS id, quantity AS qty, product_name AS name FROM job_order_products WHERE job_order_id = ? AND product_id IS NOT NULL",
                    [$id]
                );
                if (!empty($statusProductsToCheck)) {
                    $validateInventoryProducts($db, $statusProductsToCheck);
                }
            }

            // Removed the separate $shouldDeductOnStatusTransition block — it duplicated
            // $canDeductOnEdit below and caused double-deduction when a products payload
            // was also provided in the same request.

            $requestedPaymentStatus = sanitize($input['payment_status'] ?? $jo['payment_status'] ?? 'pending');
            if (($jo['payment_status'] ?? '') === 'paid' && $requestedPaymentStatus !== 'paid' && $currentUserRole !== 'admin') {
                throw new Exception('Only admin/system administrator can change payment status for a paid job order');
            }

            $now = date('Y-m-d H:i:s');
            $timerSeconds = (int)($jo['status_timer_seconds'] ?? 0);
            $timerStartedAt = $jo['status_timer_started_at'] ?? null;

            $wasRunning = in_array($jo['status'], $runningJoStatuses, true) && !empty($timerStartedAt);
            if ($wasRunning) {
                // Accumulate elapsed and stop — timer state is preserved below
                $timerSeconds += max(0, strtotime($now) - strtotime($timerStartedAt));
                $timerStartedAt = null;
            }

            // Only restart the JO timer if it was already running before this edit
            // Do NOT auto-start if it was stopped — user must explicitly press Start
            if ($wasRunning && in_array($newStatus, $runningJoStatuses, true)) {
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

            $paymentDate = null;
            if ($requestedPaymentStatus === 'paid') {
                $paymentDate = $now;
            } elseif ($requestedPaymentStatus === 'partial') {
                // Preserve existing partial payment date; set now if this is the first partial update.
                $existingPaymentDate = $db->fetch("SELECT payment_date FROM job_orders WHERE id=?", [$id]);
                $paymentDate = $existingPaymentDate['payment_date'] ?: $now;
            }

            $db->query(
                "UPDATE job_orders
                 SET status=?, payment_status=?, payment_method=?, payment_date=?, service_adviser_id=?, partial_amount=?, notes=?,
                     status_timer_seconds=?, status_timer_started_at=?,
                     work_started_at=?, inspection_started_at=?, completed_at=?
                 WHERE id=?",
                [
                    $newStatus,
                    $requestedPaymentStatus,
                    sanitize($input['payment_method'] ?? $jo['payment_method'] ?? 'cash'),
                    $paymentDate,
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

            // Record status change in history
            if ($oldStatus !== $newStatus) {
                try {
                    $db->query(
                        "INSERT INTO job_order_status_history (job_order_id, from_status, to_status, changed_by, changed_at) VALUES (?,?,?,?,?)",
                        [$id, $oldStatus, $newStatus, $currentUserId, $now]
                    );
                } catch (Exception $e) { /* table may not exist yet */ }
            }

            // Award points when JO is completed (PUT handler)
            if ($newStatus === 'completed' && $oldStatus !== 'completed') {
                $awardCompletionPoints($db, $id, $jo['job_order_number'], $currentUserId);
                $emailError = $sendCompletionEmail($db, $id);
                $smsError = $sendCompletionSms($db, $id);
                $deliveryErrors = array_filter([
                    $emailError !== '' ? 'email: ' . $emailError : '',
                    $smsError !== '' && $smsError !== 'SMS is disabled' ? 'SMS: ' . $smsError : '',
                ]);
                if ($deliveryErrors) {
                    $response['message'] = 'Job order completed, but notification delivery had issues: '
                        . implode('; ', $deliveryErrors);
                }
            }

            // Deduct points when returned after release (PUT handler)
            if ($newStatus === 'returned_for_revision' && $oldStatus === 'released') {
                $deductReturnAfterReleasePoints($db, $id);
            }

            // Refresh technician assignments for this JO
            // ── Smart reassignment: preserve session history, track time per technician ──
            $now = date('Y-m-d H:i:s');
            $runningJoStatusesForSessions = ['ongoing', 'under_inspection', 'returned_for_revision'];
            // Technician timers should only auto-start if the JO timer is ACTUALLY running
            // (i.e. user explicitly pressed Start), not just because the JO status is 'ongoing'
            $joTimerIsActuallyRunning = !empty($timerStartedAt);
            $joIsRunning = $joTimerIsActuallyRunning;
            $joIsTerminal = in_array($newStatus, ['completed', 'released', 'cancelled'], true);

            // Get currently active technicians (exclude removed and already completed)
            $oldAssignments = $db->fetchAll(
                "SELECT id, technician_id FROM job_order_technicians WHERE job_order_id=? AND status IN ('assigned','working','on_hold')",
                [$id]
            );
            $oldTechIds = array_column($oldAssignments, 'technician_id', 'id'); // [jot_id => tech_id]
            $oldTechIdList = array_values(array_map('intval', $oldTechIds));

            // If the request doesn't mention technicians at all (e.g. a quick status
            // change like "Return for Revision" that only sends `status`), don't treat
            // that as "unassign everyone" — keep the existing roster. Only an explicit
            // technician_ids/technician_id key (even an empty array, meaning "clear
            // them") should drive reassignment/removal logic.
            $technicianFieldProvided = array_key_exists('technician_ids', $input) || array_key_exists('technician_id', $input);
            if (!$technicianFieldProvided) {
                $technicianIds = $oldTechIdList;
            }

            // Determine removed and added technicians
            // NOTE: normalize to int — DB driver may return technician_id as a numeric
            // string, and downstream code uses strict (===/in_array(..., true)) int
            // comparisons. Without this cast, a "removed" technician's id would never
            // strictly match, so their status/time snapshot would silently fail to save
            // and re-adding them later would look like a no-op.
            $removedTechIds = array_map('intval', array_diff($oldTechIdList, $technicianIds));
            $addedTechIds   = array_map('intval', array_diff($technicianIds, $oldTechIdList));
            $removalReason = ucfirst(strtolower(trim((string)($input['removal_reason'] ?? 'Absent'))));
            if (!in_array($removalReason, ['Absent', 'Transfer'], true)) {
                $removalReason = 'Absent';
            }

            // Per-technician timers are now self-contained (banked work_duration +
            // their own started_at), so no JO-wide elapsed snapshot is needed here.

            foreach ($oldAssignments as $assignment) {
                $jotId  = (int)$assignment['id'];
                $techId = (int)$assignment['technician_id'];
                if (in_array($techId, $removedTechIds, true) || $joIsTerminal) {
                    // Bank only the time actually elapsed since THIS technician's own
                    // started_at (not the JO's overall elapsed) on top of whatever they
                    // already had banked, so their recorded time stays their own.
                    $jotSnap = $db->fetch("SELECT work_duration, started_at FROM job_order_technicians WHERE id=?", [$jotId]);
                    $bankedWd = max(0, (int)($jotSnap['work_duration'] ?? 0));
                    $liveSecs = !empty($jotSnap['started_at']) ? max(0, strtotime($now) - strtotime($jotSnap['started_at'])) : 0;
                    $personalSnapshot = $bankedWd + $liveSecs;
                    $assignmentStatus = in_array($techId, $removedTechIds, true)
                        ? 'removed'
                        : ($joIsTerminal ? 'completed' : 'removed');
                    if ($assignmentStatus === 'removed') {
                        $db->query(
                            "UPDATE job_order_technicians SET completed_at=?, work_duration=?, started_at=NULL, status='removed', notes=? WHERE id=?",
                            [$now, $personalSnapshot, $removalReason, $jotId]
                        );
                    } else {
                        $db->query(
                            "UPDATE job_order_technicians SET completed_at=?, work_duration=?, started_at=NULL, status=? WHERE id=?",
                            [$now, $personalSnapshot, $assignmentStatus, $jotId]
                        );
                    }
                    $openSession = $db->fetch("SELECT id FROM work_sessions WHERE job_order_technician_id=? AND end_time IS NULL ORDER BY id DESC LIMIT 1", [$jotId]);
                    if ($openSession) {
                        $db->query("UPDATE work_sessions SET end_time=?, duration=? WHERE id=?", [$now, $personalSnapshot, $openSession['id']]);
                    }
                }
            }

            // Remove de-assigned technicians — deduct points for Absent
            foreach ($oldAssignments as $assignment) {
                if (in_array((int)$assignment['technician_id'], $removedTechIds, true)) {
                    if ($removalReason === 'Absent') {
                        recordTechnicianPoints((int)$assignment['technician_id'], $id, 'Absent from JO', -5);
                    }
                }
            }

            // Insert new technician assignments (BEFORE terminal block so we can skip them)
            $reactivatedJotIds = [];
            foreach ($addedTechIds as $techId) {
                $isAssist = in_array($techId, $assistIds, true) ? 1 : 0;
                $prevJot = $db->fetch(
                    "SELECT id, work_duration, status FROM job_order_technicians WHERE job_order_id=? AND technician_id=? ORDER BY id DESC LIMIT 1",
                    [$id, $techId]
                );

                if ($prevJot && in_array($prevJot['status'], ['removed', 'on_hold', 'completed'])) {
                    $db->query(
                        "UPDATE job_order_technicians SET status='working', completed_at=NULL, started_at=?, is_assist=? WHERE id=?",
                        [$joIsRunning ? $now : null, $isAssist, (int)$prevJot['id']]
                    );
                    $jotId = (int)$prevJot['id'];
                    $reactivatedJotIds[] = $jotId;
                    if ($joIsRunning) {
                        $db->query("INSERT INTO work_sessions (job_order_technician_id, start_time) VALUES (?,?)", [$jotId, $now]);
                    }
                } else if (!$prevJot) {
                    $db->query(
                        "INSERT INTO job_order_technicians (job_order_id, technician_id, is_assist, assigned_at, started_at, status, work_duration)
                         VALUES (?, ?, ?, ?, ?, 'working', 0)",
                        [$id, $techId, $isAssist, $now, $joIsRunning ? $now : null]
                    );
                    $jotId = $db->lastInsertId();
                    $reactivatedJotIds[] = $jotId;
                    if ($joIsRunning) {
                        $db->query("INSERT INTO work_sessions (job_order_technician_id, start_time) VALUES (?,?)", [$jotId, $now]);
                    }
                } else {
                    // Record exists with some other status (possibly 'assigned'/'working'
                    // from a previous incomplete update) — force reactivate to be safe
                    $jotId = (int)$prevJot['id'];
                    $reactivatedJotIds[] = $jotId;
                    $db->query(
                        "UPDATE job_order_technicians SET status='working', completed_at=NULL, started_at=? WHERE id=?",
                        [$joIsRunning ? $now : null, $jotId]
                    );
                    if ($joIsRunning) {
                        // Only open session if none open already
                        $existingOpen = $db->fetch("SELECT id FROM work_sessions WHERE job_order_technician_id=? AND end_time IS NULL LIMIT 1", [$jotId]);
                        if (!$existingOpen) {
                            $db->query("INSERT INTO work_sessions (job_order_technician_id, start_time) VALUES (?,?)", [$jotId, $now]);
                        }
                    }
                }
            }

            // Update is_assist role for all active technicians on this JO
            if ($technicianFieldProvided) {
                $activeTechsForRole = $db->fetchAll(
                    "SELECT id, technician_id FROM job_order_technicians WHERE job_order_id=? AND status IN ('assigned','working')",
                    [$id]
                );
                foreach ($activeTechsForRole as $atr) {
                    $isAssistRole = in_array((int)$atr['technician_id'], $assistIds, true) ? 1 : 0;
                    $db->query("UPDATE job_order_technicians SET is_assist=? WHERE id=?", [$isAssistRole, (int)$atr['id']]);
                }
            }

            // If JO is terminal, close all remaining open sessions
            // (but skip technicians that were just reactivated in this request)
            if ($joIsTerminal) {
                $remaining = $db->fetchAll(
                    "SELECT jot.id, jot.started_at, jot.work_duration FROM job_order_technicians jot WHERE jot.job_order_id=? AND jot.status IN ('assigned','working')",
                    [$id]
                );
                foreach ($remaining as $assignment) {
                    $jotId = (int)$assignment['id'];
                    if (in_array($jotId, $reactivatedJotIds, true)) continue; // just added back — don't re-complete
                    $bankedR = max(0, (int)($assignment['work_duration'] ?? 0));
                    $liveR = !empty($assignment['started_at']) ? max(0, strtotime($now) - strtotime($assignment['started_at'])) : 0;
                    $finalR = $bankedR + $liveR;
                    $db->query("UPDATE job_order_technicians SET completed_at=COALESCE(completed_at,?), work_duration=?, started_at=NULL, status='completed' WHERE id=?", [$now, $finalR, $jotId]);
                    $openSession = $db->fetch("SELECT id FROM work_sessions WHERE job_order_technician_id=? AND end_time IS NULL LIMIT 1", [$jotId]);
                    if ($openSession) {
                        $db->query("UPDATE work_sessions SET end_time=?, duration=? WHERE id=?", [$now, $finalR, $openSession['id']]);
                    }
                }
            }

            // If JO is (now) running, resume/self-heal any already-assigned technician
            // whose personal clock isn't currently ticking — e.g. JO moved from a
            // non-running status (pending/under_inspection/car_washing) INTO a running
            // one like returned_for_revision. Newly-added technicians already got their
            // session opened above, so their started_at is already set here and they're
            // skipped. Mirrors the PATCH endpoint's self-healing behavior.
            if ($joIsRunning) {
                $activeAssigned = $db->fetchAll(
                    "SELECT id, started_at FROM job_order_technicians WHERE job_order_id=? AND status IN ('assigned','working')",
                    [$id]
                );
                foreach ($activeAssigned as $aa) {
                    $jotId = (int)$aa['id'];
                    if (!empty($aa['started_at'])) {
                        continue; // already ticking
                    }
                    $openSession = $db->fetch(
                        "SELECT id FROM work_sessions WHERE job_order_technician_id=? AND end_time IS NULL ORDER BY id DESC LIMIT 1",
                        [$jotId]
                    );
                    if ($openSession) {
                        continue; // session already open, just missing started_at somehow — leave as-is
                    }
                    $db->query("UPDATE job_order_technicians SET started_at=?, status='working' WHERE id=?", [$now, $jotId]);
                    $db->query("INSERT INTO work_sessions (job_order_technician_id, start_time) VALUES (?,?)", [$jotId, $now]);
                }

                // Reactivate completed technicians when JO TRANSITIONS to a running status
                // (e.g. returned_for_revision after completed). Their banked time is
                // preserved so the timer continues from where it left off.
                // Only do this if the JO was NOT already running (i.e. actual status transition)
                $wasAlreadyRunning = in_array($jo['status'], $runningJoStatusesForSessions, true);
                if (!$wasAlreadyRunning) {
                    $completedTechs = $db->fetchAll(
                        "SELECT id, work_duration FROM job_order_technicians WHERE job_order_id=? AND status='completed'",
                        [$id]
                    );
                    foreach ($completedTechs as $ct) {
                        $jotId = (int)$ct['id'];
                        if (in_array($jotId, $reactivatedJotIds, true)) continue;
                        $db->query(
                            "UPDATE job_order_technicians SET status='working', started_at=?, completed_at=NULL WHERE id=?",
                            [$now, $jotId]
                        );
                        $db->query("INSERT INTO work_sessions (job_order_technician_id, start_time) VALUES (?,?)", [$jotId, $now]);
                    }
                }
            }

            // If JO timer was running before but is no longer running, bank tech time
            if (!$joIsRunning && $wasRunning) {
                $activeTechs = $db->fetchAll(
                    "SELECT id, started_at, work_duration FROM job_order_technicians WHERE job_order_id=? AND status NOT IN ('removed','completed','on_hold')",
                    [$id]
                );
                foreach ($activeTechs as $at) {
                    $jotId = (int)$at['id'];
                    $prevA = (int)($at['work_duration'] ?? 0);
                    $intSec = !empty($at['started_at']) ? max(0, strtotime($now) - strtotime($at['started_at'])) : 0;
                    $newA = $prevA + $intSec;
                    $db->query("UPDATE job_order_technicians SET work_duration=?, started_at=NULL WHERE id=?", [$newA, $jotId]);
                    $openSession = $db->fetch("SELECT id FROM work_sessions WHERE job_order_technician_id=? AND end_time IS NULL LIMIT 1", [$jotId]);
                    if ($openSession) {
                        $db->query("UPDATE work_sessions SET end_time=?, duration=? WHERE id=?", [$now, $newA, $openSession['id']]);
                    }
                }
            }

            $alreadyDeductedPut = $alreadyDeductedPut ?? $joHasDeductedStock($db, $id);
            $canDeductOnEdit = !$alreadyDeductedPut
                && ($jo['status'] ?? 'pending') === 'pending'
                && $newStatus !== 'pending'
                && $newStatus !== 'cancelled';
            if ($productsInput === null && $canDeductOnEdit) {
                $deductJobOrderStock($db, $id, $jo['job_order_number'], $currentUserId);
            }

            // Return stock when reverting to pending — only if:
            // - stock was actually consumed
            // - JO is not paid/partial (parts committed)
            // - old status was not completed or released (terminal)
            $canRestorePendingStock = $alreadyDeductedPut
                && $newStatus === 'pending'
                && !in_array(($jo['payment_status'] ?? 'pending'), ['paid', 'partial'], true)
                && !in_array(($jo['status'] ?? 'pending'), ['completed', 'released'], true);
            if ($productsInput === null && $canRestorePendingStock) {
                $restoreJobOrderStock($db, $id, $jo['job_order_number'], $currentUserId);
            }

            // Update job_order_services if items provided
            if ($itemsInput !== null) {
                $db->query("DELETE FROM job_order_services WHERE job_order_id=?", [$id]);
                foreach ($itemsInput as $item) {
                    $basePrice = isset($item['base_price']) ? (float)$item['base_price'] : (float)($item['price'] ?? 0);
                    $laborCost = isset($item['labor_cost']) ? (float)$item['labor_cost'] : (float)($item['labor'] ?? 0);
                    $unitTotal = $basePrice;
                    $qty       = max(1, (int)($item['qty'] ?? 1));
                    $total     = $unitTotal * $qty + $laborCost;
                    $subItemsJson = !empty($item['selectedSubItems']) ? json_encode($item['selectedSubItems']) : null;
                    if (($item['type']??'') === 'bundle') {
                        $db->query(
                            "INSERT INTO job_order_services (job_order_id,bundle_id,service_name,service_price,labor_cost,quantity,total,sub_items_json) VALUES (?,?,?,?,?,?,?,?)",
                            [$id, (int)($item['id']??0), sanitize($item['name']??''), $basePrice, $laborCost, $qty, $total, $subItemsJson]
                        );
                    } elseif (($item['type']??'') === 'custom' || empty($item['id'])) {
                        $db->query(
                            "INSERT INTO job_order_services (job_order_id,service_name,service_price,labor_cost,quantity,total,sub_items_json) VALUES (?,?,?,?,?,?,?)",
                            [$id, sanitize($item['name']??''), $basePrice, $laborCost, $qty, $total, $subItemsJson]
                        );
                    } else {
                        $db->query(
                            "INSERT INTO job_order_services (job_order_id,service_id,service_name,service_price,labor_cost,quantity,total,sub_items_json) VALUES (?,?,?,?,?,?,?,?)",
                            [$id, !empty($item['id']) ? (int)$item['id'] : null, sanitize($item['name']??''), $basePrice, $laborCost, $qty, $total, $subItemsJson]
                        );
                    }
                }
            }

            // Update job_order_products if products provided.
            // For active JOs (stock already consumed): use delta approach —
            // only deduct/return the difference, never do a full restore.
            // For pending JOs (stock not yet consumed): just replace rows, deduct all if now non-pending.
            if ($productsInput !== null) {
                $alreadyDeductedPut = $alreadyDeductedPut ?? $joHasDeductedStock($db, $id);
                $joIsStartedOrRunning = !in_array($newStatus, ['pending', 'cancelled', 'completed', 'released'], true)
                    && in_array($newStatus, ['ongoing', 'under_inspection', 'returned_for_revision', 'car_washing'], true);

                if ($alreadyDeductedPut || $joIsStartedOrRunning) {
                    // ── Delta mode: compute old qty map, diff against new, adjust stock ──
                    // This catches the case where the JO is already active/running and a
                    // technician adds products mid-job, even if the initial pending→active
                    // stock deduction never fired (e.g. timer started without a full status change).
                    $oldProdRows = $db->fetchAll(
                        "SELECT product_id, quantity FROM job_order_products WHERE job_order_id=? AND product_id IS NOT NULL",
                        [$id]
                    );
                    // Build map: product_id → old qty
                    $oldQtyMap = [];
                    foreach ($oldProdRows as $op) {
                        $pid = (int)$op['product_id'];
                        $oldQtyMap[$pid] = ($oldQtyMap[$pid] ?? 0) + (int)$op['quantity'];
                    }

                    // Build map: product_id → new qty from input
                    $newQtyMap = [];
                    foreach ($input['products'] as $prod) {
                        $prodId  = (int)($prod['id'] ?? 0);
                        $prodQty = max(1, (int)($prod['qty'] ?? 1));
                        if ($prodId > 0) {
                            $newQtyMap[$prodId] = ($newQtyMap[$prodId] ?? 0) + $prodQty;
                        }
                    }

                    // Products removed or qty decreased → return the difference
                    foreach ($oldQtyMap as $pid => $oldQty) {
                        $newQty  = $newQtyMap[$pid] ?? 0;
                        $delta   = $oldQty - $newQty; // positive = returning stock
                        if ($delta > 0) {
                            $db->query("UPDATE products SET quantity = quantity + ? WHERE id=?", [$delta, $pid]);
                            $db->query(
                                "INSERT INTO inventory_transactions (product_id,transaction_type,quantity,reference_type,reference_id,notes,created_by) VALUES (?,?,?,?,?,?,?)",
                                [$pid, 'return', $delta, 'job_order', $id, "Edit qty reduced JO #{$jo['job_order_number']}", $currentUserId]
                            );
                        }
                    }

                    // Products added or qty increased → deduct only the new delta
                    foreach ($newQtyMap as $pid => $newQty) {
                        $oldQty = $oldQtyMap[$pid] ?? 0;
                        $delta  = $newQty - $oldQty; // positive = need more stock
                        if ($delta > 0) {
                            $stock = $db->fetch("SELECT quantity, product_name FROM products WHERE id=?", [$pid]);
                            if (!$stock || (int)$stock['quantity'] < $delta) {
                                throw new Exception("Insufficient stock for: " . ($stock['product_name'] ?? "product #$pid") . " (available: " . (int)($stock['quantity'] ?? 0) . ", need: $delta more)");
                            }
                            $updatedStock = $db->query("UPDATE products SET quantity = quantity - ? WHERE id=? AND quantity >= ?", [$delta, $pid, $delta]);
                            if ($updatedStock->rowCount() !== 1) {
                                throw new Exception("Insufficient stock for: " . ($stock['product_name'] ?? "product #$pid"));
                            }
                            $db->query(
                                "INSERT INTO inventory_transactions (product_id,transaction_type,quantity,reference_type,reference_id,notes,created_by) VALUES (?,?,?,?,?,?,?)",
                                [$pid, 'stock_out', $delta, 'job_order', $id, "Edit qty added JO #{$jo['job_order_number']}", $currentUserId]
                            );
                        }
                    }

                    // Replace job_order_products rows (no stock movement, already handled above)
                    $db->query("DELETE FROM job_order_products WHERE job_order_id=?", [$id]);
                    $computedPartsCost = 0;
                    foreach ($input['products'] as $prod) {
                        $prodName   = sanitize($prod['name'] ?? '');
                        $prodQty    = max(1, (int)($prod['qty'] ?? 1));
                        $prodPrice  = (float)($prod['price'] ?? 0);
                        $fromBundle = !empty($prod['fromBundle']);
                        $prodId     = (int)($prod['id'] ?? 0);
                        if (empty($prodName)) continue;
                        if ($prodId > 0) {
                            $db->query(
                                "INSERT INTO job_order_products (job_order_id,product_id,product_name,product_type,unit_price,quantity,total) VALUES (?,?,?,?,?,?,?)",
                                [$id, $prodId, $prodName, 'parts', $prodPrice, $prodQty, $prodPrice * $prodQty]
                            );
                        } else {
                            $db->query(
                                "INSERT INTO job_order_products (job_order_id,product_name,product_type,unit_price,quantity,total) VALUES (?,?,?,?,?,?)",
                                [$id, $prodName, 'parts', $prodPrice, $prodQty, $prodPrice * $prodQty]
                            );
                        }
                        $computedPartsCost += $fromBundle ? 0 : ($prodPrice * $prodQty);
                    }

                } else {
                    // ── Pending mode: full replace, deduct all if now non-pending ──
                    $db->query("DELETE FROM job_order_products WHERE job_order_id=?", [$id]);
                    $shouldDeductOnEdit = ($newStatus !== 'pending') && $canDeductOnEdit;
                    $computedPartsCost  = 0;
                    foreach ($input['products'] as $prod) {
                        $prodName   = sanitize($prod['name'] ?? '');
                        $prodQty    = max(1, (int)($prod['qty'] ?? 1));
                        $prodPrice  = (float)($prod['price'] ?? 0);
                        $fromBundle = !empty($prod['fromBundle']);
                        $prodId     = (int)($prod['id'] ?? 0);
                        if (empty($prodName)) continue;
                        if ($prodId > 0) {
                            if ($shouldDeductOnEdit) {
                                $stock = $db->fetch("SELECT quantity, product_name FROM products WHERE id=?", [$prodId]);
                                if (!$stock || (int)$stock['quantity'] < $prodQty) {
                                    throw new Exception("Insufficient stock for: " . ($stock['product_name'] ?? "product #$prodId") . " (available: " . (int)($stock['quantity'] ?? 0) . ")");
                                }
                            }
                            $db->query(
                                "INSERT INTO job_order_products (job_order_id,product_id,product_name,product_type,unit_price,quantity,total) VALUES (?,?,?,?,?,?,?)",
                                [$id, $prodId, $prodName, 'parts', $prodPrice, $prodQty, $prodPrice * $prodQty]
                            );
                            if ($shouldDeductOnEdit) {
                                $updatedStock = $db->query("UPDATE products SET quantity = quantity - ? WHERE id=? AND quantity >= ?", [$prodQty, $prodId, $prodQty]);
                                if ($updatedStock->rowCount() !== 1) {
                                    throw new Exception("Insufficient stock for: {$stock['product_name']}");
                                }
                                $db->query(
                                    "INSERT INTO inventory_transactions (product_id,transaction_type,quantity,reference_type,reference_id,notes,created_by) VALUES (?,?,?,?,?,?,?)",
                                    [$prodId, 'stock_out', $prodQty, 'job_order', $id, "Used in JO #{$jo['job_order_number']}", $currentUserId]
                                );
                            }
                        } else {
                            $db->query(
                                "INSERT INTO job_order_products (job_order_id,product_name,product_type,unit_price,quantity,total) VALUES (?,?,?,?,?,?)",
                                [$id, $prodName, 'parts', $prodPrice, $prodQty, $prodPrice * $prodQty]
                            );
                        }
                        $computedPartsCost += $fromBundle ? 0 : ($prodPrice * $prodQty);
                    }
                }
                $newPartsCost = $computedPartsCost;

                $newServiceCount = is_array($itemsInput) ? count($itemsInput) : $oldServiceCount;
                if ($newServiceCount !== $oldServiceCount) {
                    $updateNotes[] = "Services/Bundles count: {$oldServiceCount} → {$newServiceCount}";
                }
                if ($oldServiceCount === $newServiceCount) {
                    $updateNotes[] = "Services/Bundles updated";
                }

                $newProductCount = is_array($productsInput) ? count($productsInput) : $oldProductCount;
                if ($newProductCount !== $oldProductCount) {
                    $updateNotes[] = "Products count: {$oldProductCount} → {$newProductCount}";
                }
                if ($oldProductCount === $newProductCount) {
                    $updateNotes[] = "Products updated";
                }
            }

            $financialSummary = $calculateJoFinancialsFromDb($db, $id, $input);
            $newSubtotal = (float)$financialSummary['subtotal'];
            $newPartsCost = (float)$financialSummary['parts_cost'];
            $discountSummary = [
                'discount_type' => $financialSummary['discount_type'],
                'discount_amount' => (float)$financialSummary['discount_amount'],
                'discount_percentage' => (float)$financialSummary['discount_percentage'],
                'total_amount' => (float)$financialSummary['total_amount'],
            ];
            $editPartial = min($editPartial, $discountSummary['total_amount']);
            $db->query(
                "UPDATE job_orders
                 SET subtotal=?, parts_total=?, discount_type=?, discount_amount=?, discount_percentage=?, total_amount=?, partial_amount=?
                 WHERE id=?",
                [
                    $newSubtotal,
                    $newPartsCost,
                    $discountSummary['discount_type'],
                    $discountSummary['discount_amount'],
                    $discountSummary['discount_percentage'],
                    $discountSummary['total_amount'],
                    $editPartial,
                    $id,
                ]
            );

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

            $latestFinancial = $db->fetch(
                "SELECT total_amount, partial_amount, payment_status FROM job_orders WHERE id=?",
                [$id]
            ) ?: [];
            $latestTotalAmount = (float)($latestFinancial['total_amount'] ?? 0);
            $latestPartialAmount = (float)($latestFinancial['partial_amount'] ?? 0);

            if ($requestedPaymentStatus === 'paid' && $oldPaymentStatus !== 'paid') {
                $actorName = sanitize($_SESSION['full_name'] ?? $_SESSION['username'] ?? 'Staff');
                notifyRoles(
                    'payment',
                    'Job Order Paid',
                    buildNotificationMessageTemplate(
                        $actorName,
                        'marked as paid',
                        'job order #' . $jo['job_order_number'],
                        'Amount: ₱' . number_format($latestTotalAmount, 2)
                    ),
                    ['admin', 'cashier', 'service_adviser'],
                    [
                        'reference_type' => 'job_order',
                        'reference_id' => (int)$id,
                    ]
                );
            }

            if (
                $requestedPaymentStatus === 'partial'
                && ($oldPaymentStatus !== 'partial' || abs($editPartial - $oldPartialAmount) > 0.0001)
            ) {
                $actorName = sanitize($_SESSION['full_name'] ?? $_SESSION['username'] ?? 'Staff');
                notifyRoles(
                    'payment',
                    'Partial Payment Updated',
                    buildNotificationMessageTemplate(
                        $actorName,
                        'updated partial payment for',
                        'job order #' . $jo['job_order_number'],
                        'Partial: ₱' . number_format($latestPartialAmount, 2) . ' / Total: ₱' . number_format($latestTotalAmount, 2)
                    ),
                    ['admin', 'cashier', 'service_adviser'],
                    [
                        'reference_type' => 'job_order',
                        'reference_id' => (int)$id,
                    ]
                );
            }

            $latestVersionRow = $db->fetch("SELECT updated_at FROM job_orders WHERE id=?", [$id]);

            // ── Save new inline payment records (edit mode) ──────────────────
            $inlinePayments = is_array($input['inline_payments'] ?? null) ? $input['inline_payments'] : [];
            if (!empty($inlinePayments)) {
                try {
                    // Get existing payment count to only add NEW ones
                    $existingPayCount = (int)($db->fetch("SELECT COUNT(*) AS cnt FROM job_order_payments WHERE job_order_id=?", [$id])['cnt'] ?? 0);
                    $newPayments = array_slice($inlinePayments, $existingPayCount);
                    $totalNewPaid = 0.0;
                    foreach ($newPayments as $pay) {
                        $payAmt = (float)($pay['amount'] ?? 0);
                        if ($payAmt <= 0) continue;
                        $payMethod = sanitize($pay['method'] ?? 'cash');
                        $payRef    = sanitize($pay['reference'] ?? '');
                        $payNow = date('Y-m-d H:i:s');
                        $db->query(
                            "INSERT INTO job_order_payments (job_order_id, amount, payment_method, reference_number, created_by, payment_date) VALUES (?,?,?,?,?,?)",
                            [$id, $payAmt, $payMethod, $payRef, $currentUserId, $payNow]
                        );
                        $totalNewPaid += $payAmt;
                    }
                    if ($totalNewPaid > 0) {
                        // Recalculate payment status
                        $totalPaidAll = (float)($db->fetch("SELECT COALESCE(SUM(amount),0) AS total FROM job_order_payments WHERE job_order_id=?", [$id])['total'] ?? 0);
                        $joTotalAmt = (float)($db->fetch("SELECT total_amount FROM job_orders WHERE id=?", [$id])['total_amount'] ?? 0);
                        $newPaySt = $totalPaidAll >= $joTotalAmt ? 'paid' : ($totalPaidAll > 0 ? 'partial' : 'pending');
                        $db->query("UPDATE job_orders SET payment_status=?, partial_amount=?, payment_date=? WHERE id=?",
                            [$newPaySt, min($totalPaidAll, $joTotalAmt), date('Y-m-d H:i:s'), $id]);
                    }
                } catch (Exception $e) {
                    error_log("Edit inline payments save error: " . $e->getMessage());
                }
                $latestVersionRow = $db->fetch("SELECT updated_at FROM job_orders WHERE id=?", [$id]);
            }

            $response['success'] = true;
            $response['message'] = $emailError !== ''
                ? 'Job order updated, but email was not sent: ' . $emailError
                : 'Job order updated successfully';
            $response['data'] = [
                'updated_at' => (string)($latestVersionRow['updated_at'] ?? ''),
            ];
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
                "SELECT job_order_number, status, payment_status, status_timer_seconds, status_timer_started_at,
                        work_started_at, inspection_started_at, completed_at, updated_at
                 FROM job_orders WHERE id=?",
                [$id]
            );
            if (!$jo) throw new Exception('Job order not found');

            $expectedUpdatedAt = trim((string)($input['expected_updated_at'] ?? ''));
            if ($expectedUpdatedAt !== '' && (string)($jo['updated_at'] ?? '') !== $expectedUpdatedAt) {
                throw new Exception('Conflict: this job order was updated by another user. Please refresh and try again.');
            }
            $oldStatus = (string)($jo['status'] ?? 'pending');
            if (!$canViewJobOrder($db, (int)$id, (string)$jo['status'], $currentUserRole, $currentUserId)) {
                throw new Exception('Insufficient permissions');
            }

            $hasStatus = array_key_exists('status', $input);
            $timerAction = sanitize($input['timer_action'] ?? '');
            $stopNotes = sanitize($input['stop_notes'] ?? '');
            $inspectionResult = sanitize($input['inspection_result'] ?? ''); // 'pass' or 'revision'
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

            // Starting the timer for a pending JO means work has actually begun.
            // Without this, a PATCH with timer_action=start leaves the JO in 'pending'
            // and stock is never deducted even though the job is now active.
            $targetStatusInput = $input['status'] ?? (
                $timerAction === 'done'
                    ? 'under_inspection'
                    : ($timerAction === 'start' && (($jo['status'] ?? 'pending') === 'pending')
                        ? 'ongoing'
                        : ($jo['status'] ?? 'pending'))
            );
            $newStatus = $normalizeJoStatus($targetStatusInput);
            if (!in_array($newStatus, $allowedJoStatuses, true)) {
                throw new Exception('Invalid job order status');
            }

            // Restrict stock only on pending → non-cancelled (first time stock leaves).
            // Once stock is already consumed never block any status transition.
            $alreadyDeducted = $joHasDeductedStock($db, $id);
            if (!$alreadyDeducted && $oldStatus === 'pending' && $newStatus !== 'pending' && $newStatus !== 'cancelled') {
                $statusProductsToCheck = $db->fetchAll(
                    "SELECT product_id AS id, quantity AS qty, product_name AS name
                     FROM job_order_products
                     WHERE job_order_id = ? AND product_id IS NOT NULL",
                    [$id]
                );
                if (!empty($statusProductsToCheck)) {
                    $validateInventoryProducts($db, $statusProductsToCheck);
                }
            }

            // Deduct stock when pending → any active status (not cancelled).
            $canDeductOnStatusPatch = !$alreadyDeducted
                && $oldStatus === 'pending'
                && $newStatus !== 'pending'
                && $newStatus !== 'cancelled';
            if ($canDeductOnStatusPatch) {
                $deductJobOrderStock($db, $id, $jo['job_order_number'], $currentUserId);
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

            // Record status change in history
            if ($oldStatus !== $newStatus) {
                try {
                    $db->query(
                        "INSERT INTO job_order_status_history (job_order_id, from_status, to_status, changed_by, changed_at) VALUES (?,?,?,?,?)",
                        [$id, $oldStatus, $newStatus, $currentUserId, $now]
                    );
                } catch (Exception $e) { /* table may not exist yet */ }
            }

            // Record inspection result
            if ($inspectionResult !== '') {
                try {
                    $db->query(
                        "INSERT INTO job_order_inspections (job_order_id, result, inspected_by, inspected_at) VALUES (?,?,?,?)",
                        [$id, $inspectionResult, $currentUserId, $now]
                    );
                } catch (Exception $e) { /* table may not exist yet */ }

                $inspLabel = $inspectionResult === 'pass' ? 'PASSED' : 'NEEDS REVISION';
                logActivity($currentUserId, 'jo_inspection', "Inspection for JO #{$jo['job_order_number']}: {$inspLabel}");

                // Award/deduct points for inspection
                if ($inspectionResult === 'revision') {
                    $joTechs = $db->fetchAll("SELECT technician_id FROM job_order_technicians WHERE job_order_id=? AND status IN ('assigned','working','completed')", [$id]);
                    foreach ($joTechs as $jt) {
                        recordTechnicianPoints((int)$jt['technician_id'], $id, 'Needs Revision (inspection)', -5);
                    }
                }
            }

            // Award points when JO is completed (PATCH handler)
            if ($newStatus === 'completed' && $oldStatus !== 'completed') {
                $awardCompletionPoints($db, $id, $jo['job_order_number'], $currentUserId);
                $emailError = $sendCompletionEmail($db, $id);
                $smsError = $sendCompletionSms($db, $id);
                $deliveryErrors = array_filter([
                    $emailError !== '' ? 'email: ' . $emailError : '',
                    $smsError !== '' && $smsError !== 'SMS is disabled' ? 'SMS: ' . $smsError : '',
                ]);
                if ($deliveryErrors) {
                    $response['message'] = 'Job order completed, but notification delivery had issues: '
                        . implode('; ', $deliveryErrors);
                }
            }

            // Deduct points when JO returned after release (PATCH handler)
            if ($newStatus === 'returned_for_revision' && $oldStatus === 'released') {
                $deductReturnAfterReleasePoints($db, $id);
            }

            // ── Restore stock when JO is cancelled ───────────────────────────
            // Only return stock if it was consumed (non-pending before cancel)
            // AND the JO has not been paid (paid means the work/parts are committed).
            if ($newStatus === 'cancelled'
                && $oldStatus !== 'cancelled'
                && $oldStatus !== 'pending'
                && !in_array(($jo['payment_status'] ?? 'pending'), ['paid', 'partial'], true)
            ) {
                $joProds = $db->fetchAll(
                    "SELECT product_id, quantity FROM job_order_products WHERE job_order_id=? AND product_id IS NOT NULL",
                    [$id]
                );
                foreach ($joProds as $p) {
                    $db->query("UPDATE products SET quantity = quantity + ? WHERE id=?", [$p['quantity'], $p['product_id']]);
                    $db->query(
                        "INSERT INTO inventory_transactions (product_id, transaction_type, quantity, reference_type, reference_id, notes, created_by) VALUES (?,?,?,?,?,?,?)",
                        [$p['product_id'], 'return', $p['quantity'], 'job_order', $id, "JO #{$jo['job_order_number']} cancelled — stock returned", $currentUserId]
                    );
                }
            }

            // ── Restore stock when JO goes back to pending ───────────────────
            // Return stock only if:
            // - stock was actually consumed ($alreadyDeducted via inventory_transactions)
            // - JO is not paid or partial (paid means parts are committed)
            // - old status was not completed or released (terminal work states)
            $shouldRestorePendingStock = $newStatus === 'pending'
                && $oldStatus !== 'pending'
                && $alreadyDeducted
                && !in_array(($jo['payment_status'] ?? 'pending'), ['paid', 'partial'], true)
                && !in_array($oldStatus, ['completed', 'released'], true);

            if ($shouldRestorePendingStock) {
                $joProds = $db->fetchAll(
                    "SELECT product_id, quantity FROM job_order_products WHERE job_order_id=? AND product_id IS NOT NULL",
                    [$id]
                );
                foreach ($joProds as $p) {
                    $db->query("UPDATE products SET quantity = quantity + ? WHERE id=?", [$p['quantity'], $p['product_id']]);
                    $db->query(
                        "INSERT INTO inventory_transactions (product_id, transaction_type, quantity, reference_type, reference_id, notes, created_by) VALUES (?,?,?,?,?,?,?)",
                        [$p['product_id'], 'return', $p['quantity'], 'job_order', $id, "JO #{$jo['job_order_number']} reverted to pending — stock returned", $currentUserId]
                    );
                }
            }

            // Inventory is consumed when a JO leaves pending, regardless of payment status.

            // ── Session management on status change / timer action ───────────
            // The technician timer is a direct function of the JO's own running
            // status — built on the exact same $runningJoStatuses list the JO
            // status timer above uses (ongoing, under_inspection,
            // returned_for_revision). Technicians run wherever the JO timer runs
            // and pause everywhere else. This is computed fresh from the
            // *current* status on every request rather than from the transition
            // that led here, so a technician's timer always self-heals back in
            // sync with the JO even if their session was closed out of band for
            // some other reason (manual stop, reassignment, etc.) — including
            // resuming automatically on returned_for_revision no matter what
            // status came right before.
            $isNowTerminal = in_array($newStatus, ['completed', 'released', 'cancelled'], true);
            $techShouldRun = in_array($newStatus, $runningJoStatuses, true) && !$isNowTerminal;

            // Explicit timer_action overrides always win, mirroring the JO work
            // timer's own start/stop controls directly onto every assigned
            // technician. "done" is NOT an override here: it moves the JO into
            // under_inspection, which is already a running status above, so the
            // technician's timer just keeps going straight through inspection.
            $timerStartsSessions = ($timerAction === 'start');
            if ($timerAction === 'stop') {
                $techShouldRun = false;
            } elseif ($timerAction === 'start') {
                $techShouldRun = true;
            }

            if ($hasStatus || $timerAction !== '') {
                $jotAssignments = $db->fetchAll(
                    "SELECT id, started_at, work_duration FROM job_order_technicians WHERE job_order_id=? AND status IN ('assigned','working')", [$id]
                );
                foreach ($jotAssignments as $jotRow) {
                    $jotId    = (int)$jotRow['id'];
                    $savedWd  = (int)($jotRow['work_duration'] ?? 0); // may be negative (assist offset)
                    $bankedWd = max(0, $savedWd);                     // positive banked seconds only
                    $openSession = $db->fetch(
                        "SELECT id, start_time FROM work_sessions WHERE job_order_technician_id=? AND end_time IS NULL ORDER BY id DESC LIMIT 1",
                        [$jotId]
                    );

                    if ($techShouldRun && !$openSession) {
                        // JO timer is running for this status but this technician's
                        // clock isn't — start/resume it (self-heals any gap).
                        $db->query("UPDATE job_order_technicians SET started_at=?, status='working' WHERE id=?", [$now, $jotId]);
                        $db->query("INSERT INTO work_sessions (job_order_technician_id, start_time) VALUES (?,?)", [$jotId, $now]);
                    } elseif ($techShouldRun && $openSession && $timerStartsSessions) {
                        // Already running and explicitly (re)started — just refresh started_at
                        $db->query("UPDATE job_order_technicians SET started_at=?, status='working' WHERE id=?", [$now, $jotId]);
                    } elseif (!$techShouldRun && $openSession) {
                        // Bank only the time actually elapsed since THIS technician's own
                        // started_at (not the JO's overall elapsed), so technicians added
                        // partway through keep their own clean total.
                        $liveSecs = !empty($jotRow['started_at']) ? max(0, strtotime($now) - strtotime($jotRow['started_at'])) : 0;
                        $newBanked = $bankedWd + $liveSecs;
                        $db->query("UPDATE work_sessions SET end_time=?, duration=?, notes=? WHERE id=?", [$now, $newBanked, $stopNotes ?: null, $openSession['id']]);
                        $db->query("UPDATE job_order_technicians SET work_duration=?, started_at=NULL WHERE id=?", [$newBanked, $jotId]);
                    }

                    // Terminal JO status always finalizes every active technician's
                    // record, whether or not their personal clock happened to be
                    // running at that exact moment.
                    if ($isNowTerminal) {
                        $db->query(
                            "UPDATE job_order_technicians SET completed_at=COALESCE(completed_at,?), status='completed' WHERE id=?",
                            [$now, $jotId]
                        );
                    }
                }

                // Reactivate completed technicians when JO TRANSITIONS to running status
                // (e.g. returned_for_revision after completed). Timer continues from banked time.
                // Only do this on actual status transitions, not when already running.
                $wasAlreadyRunningPatch = in_array($jo['status'], $runningJoStatuses, true);
                if ($techShouldRun && !$isNowTerminal && !$wasAlreadyRunningPatch) {
                    $completedTechs = $db->fetchAll(
                        "SELECT id, work_duration FROM job_order_technicians WHERE job_order_id=? AND status='completed'",
                        [$id]
                    );
                    foreach ($completedTechs as $ct) {
                        $jotId = (int)$ct['id'];
                        $db->query(
                            "UPDATE job_order_technicians SET status='working', started_at=?, completed_at=NULL WHERE id=?",
                            [$now, $jotId]
                        );
                        $db->query("INSERT INTO work_sessions (job_order_technician_id, start_time) VALUES (?,?)", [$jotId, $now]);
                    }
                }
            }

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
            $response['message'] = $emailError !== ''
                ? 'Job order updated, but email was not sent: ' . $emailError
                : ($timerAction === 'done'
                    ? 'Job order marked done and moved to under inspection'
                    : ($timerAction !== '' ? 'Job order timer updated successfully' : 'Job order status updated successfully'));

            $latestVersionRow = $db->fetch("SELECT updated_at FROM job_orders WHERE id=?", [$id]);
            $response['data'] = [
                'status' => $newStatus,
                'status_timer_is_running' => !empty($timerStartedAt),
                'status_elapsed_seconds' => $elapsedSeconds,
                'updated_at' => (string)($latestVersionRow['updated_at'] ?? ''),
            ];
            break;

        // ── DELETE ───────────────────────────────────────────────────────────
        case 'DELETE':
            if (!$id) throw new Exception('Job order ID is required');
            if ($currentUserRole !== 'admin') throw new Exception('Only admins can delete job orders');

            $jo = $db->fetch("SELECT job_order_number, status, payment_status FROM job_orders WHERE id=?", [$id]);
            if (!$jo) throw new Exception('Job order not found');
            $jobOrderRow = $db->fetch("SELECT * FROM job_orders WHERE id = ? LIMIT 1", [$id]);
            if (!is_array($jobOrderRow) || empty($jobOrderRow)) {
                throw new Exception('Unable to load job order archive payload.');
            }

            $jobOrderServices = $db->fetchAll("SELECT * FROM job_order_services WHERE job_order_id = ?", [$id]);
            $jobOrderProducts = $db->fetchAll("SELECT * FROM job_order_products WHERE job_order_id = ?", [$id]);
            $jobOrderTechnicians = $db->fetchAll("SELECT * FROM job_order_technicians WHERE job_order_id = ?", [$id]);
            $jobOrderInspections = [];
            $technicianPoints = [];
            $inspectionTable = $db->fetch("SHOW TABLES LIKE 'job_order_inspections'");
            if ($inspectionTable) {
                $jobOrderInspections = $db->fetchAll("SELECT * FROM job_order_inspections WHERE job_order_id = ?", [$id]);
            }
            $pointsTable = $db->fetch("SHOW TABLES LIKE 'technician_points'");
            if ($pointsTable) {
                $technicianPoints = $db->fetchAll("SELECT * FROM technician_points WHERE job_order_id = ?", [$id]);
            }
            $jobOrderTechnicianIds = [];
            foreach ($jobOrderTechnicians as $techRow) {
                $jotId = (int)($techRow['id'] ?? 0);
                if ($jotId > 0) {
                    $jobOrderTechnicianIds[] = $jotId;
                }
            }

            $workSessions = [];
            if (!empty($jobOrderTechnicianIds)) {
                $placeholders = implode(',', array_fill(0, count($jobOrderTechnicianIds), '?'));
                $workSessions = $db->fetchAll(
                    "SELECT * FROM work_sessions WHERE job_order_technician_id IN ({$placeholders})",
                    $jobOrderTechnicianIds
                );
            }

            $reportExpenses = getReportExpenses();
            $linkedReportExpenses = array_values(array_filter($reportExpenses, function ($expenseRow) use ($id) {
                return (int)($expenseRow['job_order_id'] ?? 0) === (int)$id;
            }));
            $archiveId = archiveDeletedRecord('job_order', [
                'job_order' => $jobOrderRow,
                'job_order_services' => $jobOrderServices,
                'job_order_products' => $jobOrderProducts,
                'job_order_technicians' => $jobOrderTechnicians,
                'work_sessions' => $workSessions,
                'job_order_inspections' => $jobOrderInspections,
                'technician_points' => $technicianPoints,
                'report_expenses' => $linkedReportExpenses,
            ], ['source' => 'database', 'meta' => ['table' => 'job_orders']]);
            if (!$archiveId) {
                throw new Exception('Failed to archive job order before deletion.');
            }

            $shouldRestoreStock = $jo['status'] !== 'pending';

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

            $remainingReportExpenses = array_values(array_filter($reportExpenses, function ($expenseRow) use ($id) {
                return (int)($expenseRow['job_order_id'] ?? 0) !== (int)$id;
            }));
            if (count($remainingReportExpenses) !== count($reportExpenses)
                && !saveReportExpenses($remainingReportExpenses)) {
                throw new Exception('Failed to remove linked expense records.');
            }

            try {
                $db->query("DELETE FROM job_orders WHERE id=?", [$id]);
            } catch (Exception $deleteException) {
                deleteArchivedRecordById($archiveId);
                throw $deleteException;
            }
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
    // If a DB transaction is open, rollback to avoid partial inserts (e.g., JO row created but products failed)
    if (isset($db)) {
        try {
            $conn = $db->getConnection();
            if ($conn && $conn->inTransaction()) {
                $db->rollback();
            }
        } catch (Exception $ex) {
            error_log("Rollback failed: " . $ex->getMessage());
        }
    }

    $response['message'] = $e->getMessage();
    if (stripos($e->getMessage(), 'Conflict:') === 0) {
        http_response_code(409);
    } else {
        http_response_code(400);
    }
}

echo json_encode($response);