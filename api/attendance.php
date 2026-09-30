<?php
define('APP_ACCESS', true);
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/session.php';

header('Content-Type: application/json');

$host = strtolower((string)($_SERVER['HTTP_HOST'] ?? ''));
$isPublicKiosk = preg_match('/^tautodokattendance\.theautodok\.com(?::\d+)?$/', $host) === 1;
if (!$isPublicKiosk && (!isLoggedIn() || !hasAnyRole(['admin', 'cashier']))) {
    jsonResponse(['success' => false, 'message' => 'Attendance kiosk access is restricted'], 403);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

$identifier = trim((string)($_POST['identifier'] ?? ''));
$identifierMatch = [];
if (preg_match('/\b\d{5}\b/', $identifier, $identifierMatch)) {
    $identifier = $identifierMatch[0];
}
$identifier = preg_replace('/[^a-zA-Z0-9._-]/', '', $identifier);
if ($identifier === '' || strlen($identifier) > 50) {
    jsonResponse(['success' => false, 'message' => 'A valid NFC staff ID is required'], 400);
}

$db = Database::getInstance();
$staff = $db->fetch(
    "SELECT id, staff_id, first_name, last_name, role, status
     FROM staff
     WHERE staff_id = ? AND LOWER(role) <> 'admin'
     LIMIT 1",
    [$identifier]
);

if (!$staff) {
    jsonResponse(['success' => false, 'message' => 'No active staff member matches this NFC card'], 404);
}

if (($staff['status'] ?? '') !== 'active') {
    jsonResponse(['success' => false, 'message' => 'This staff member is not active'], 409);
}

$now = new DateTime('now', new DateTimeZone(TIMEZONE));
$date = $now->format('Y-m-d');
$time = $now->format('H:i:s');
$periodStart = ((int)$now->format('H') < 12) ? '00:00:00' : '12:00:00';
$periodEnd = ((int)$now->format('H') < 12) ? '11:59:59' : '23:59:59';
$existing = $db->fetch(
    "SELECT id, time_in, time_out, status
     FROM attendance
     WHERE staff_id = ? AND date = ? AND time_in BETWEEN ? AND ?
     ORDER BY id DESC LIMIT 1",
    [(int)$staff['id'], $date, $periodStart, $periodEnd]
);

$fullName = trim(($staff['first_name'] ?? '') . ' ' . ($staff['last_name'] ?? ''));
if (!$existing) {
    $status = ((int)$now->format('H') < 12 && $time > '08:15:00') ? 'late' : 'present';
    $db->query(
        "INSERT INTO attendance (staff_id, date, time_in, status, notes) VALUES (?, ?, ?, ?, ?)",
        [(int)$staff['id'], $date, $time, $status, 'NFC tap']
    );
    logActivity($isPublicKiosk ? null : (int)$_SESSION['user_id'], 'nfc_attendance_in', 'NFC time in: ' . $fullName);
    jsonResponse([
        'success' => true,
        'action' => 'time_in',
        'message' => $fullName . ' timed in at ' . $now->format('g:i A'),
        'staff' => ['name' => $fullName, 'role' => $staff['role'], 'time' => $now->format('g:i A'), 'status' => $status]
    ]);
}

if (!empty($existing['time_out'])) {
    jsonResponse(['success' => false, 'message' => $fullName . ' already completed this attendance period'], 409);
}

$db->query("UPDATE attendance SET time_out = ?, notes = ? WHERE id = ?", [$time, 'NFC tap', (int)$existing['id']]);
logActivity($isPublicKiosk ? null : (int)$_SESSION['user_id'], 'nfc_attendance_out', 'NFC time out: ' . $fullName);
jsonResponse([
    'success' => true,
    'action' => 'time_out',
    'message' => $fullName . ' timed out at ' . $now->format('g:i A'),
    'staff' => ['name' => $fullName, 'role' => $staff['role'], 'time' => $now->format('g:i A'), 'status' => $existing['status']]
]);
