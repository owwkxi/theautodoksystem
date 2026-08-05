<?php
/**
 * Public vehicle status lookup for customers from login page.
 * Supports JO number, customer name, or plate number search.
 */

define('APP_ACCESS', true);
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/Database.php';
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

    $db = Database::getInstance();
    $like = '%' . $query . '%';

    $rows = $db->fetchAll(
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
         LIMIT 20",
        [$like, $like, $like]
    );

    echo json_encode([
        'success' => true,
        'count' => count($rows),
        'data' => $rows,
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Unable to fetch vehicle status']);
}
