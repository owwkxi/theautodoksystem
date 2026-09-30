<?php
/**
 * Public vehicle status lookup for customers from login page.
 * Supports JO number, customer name, or plate number search.
 */

define('APP_ACCESS', true);
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

try {
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        exit;
    }

    $rawQuery = (string)($_GET['q'] ?? '');
    $query = sanitize(trim($rawQuery));

    if ($query === '' || mb_strlen($query) < 2) {
        echo json_encode(['success' => false, 'message' => 'Enter at least 2 characters']);
        exit;
    }

    $requestedShopKey = trim((string)($_GET['shop_key'] ?? ''));
    $shopOptions = getShopOptions();
    if ($requestedShopKey === '' || !isset($shopOptions[$requestedShopKey])) {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'Please select a valid branch.']);
        exit;
    }

    $requestedShop = resolveShopOption($requestedShopKey);
    $shopDbName = $requestedShop['db_name'] ?? '';
    $shopDbUser = $requestedShop['db_user'] ?? DB_USER;
    $shopDbPass = $requestedShop['db_pass'] ?? DB_PASS;
    if ($shopDbName === '') {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'Branch database is not configured.']);
        exit;
    }

    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . $shopDbName . ";charset=" . DB_CHARSET;
    $pdo = new PDO($dsn, $shopDbUser, $shopDbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    $pdo->exec("SET time_zone = " . $pdo->quote(DB_TIMEZONE_OFFSET));

    $like = '%' . $query . '%';

    $stmt = $pdo->prepare(
        "SELECT jo.job_order_number,
                jo.status,
                jo.created_at,
                jo.updated_at,
                c.full_name AS customer_name,
                v.plate_number,
                v.brand,
                v.model
         FROM job_orders jo
         LEFT JOIN customers c ON c.id = jo.customer_id
         LEFT JOIN vehicles v ON v.id = jo.vehicle_id
         WHERE jo.job_order_number LIKE ?
            OR c.full_name LIKE ?
            OR v.plate_number LIKE ?
         ORDER BY jo.updated_at DESC, jo.created_at DESC
         LIMIT 20"
    );
    $stmt->execute([$like, $like, $like]);
    $rows = $stmt->fetchAll();

    echo json_encode([
        'success' => true,
        'shop_key' => $requestedShop['key'] ?? $requestedShopKey,
        'shop_name' => $requestedShop['name'] ?? APP_NAME,
        'count' => count($rows),
        'data' => $rows,
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Unable to fetch vehicle status']);
}
