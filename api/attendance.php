<?php
define('APP_ACCESS', true);
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/session.php';

header('Content-Type: application/json');

$host = strtolower((string)($_SERVER['HTTP_HOST'] ?? ''));
$isPublicKiosk = preg_match('/^tautodokattendance\.theautodok\.com(?::\d+)?$/', $host) === 1;
$isLocalKiosk = preg_match('/^(localhost|127\.0\.0\.1)(?::\d+)?$/', $host) === 1;
if (!$isPublicKiosk && !$isLocalKiosk && (!isLoggedIn() || !hasAnyRole(['admin', 'cashier']))) {
    jsonResponse(['success' => false, 'message' => 'Attendance kiosk access is restricted'], 403);
}

$db = Database::getInstance();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $now = new DateTime('now', new DateTimeZone(TIMEZONE));
    $today = $now->format('Y-m-d');
    $currentPeriod = (int)$now->format('G') < 12 ? 'Morning' : 'Afternoon';
    $records = $db->fetchAll(
        "SELECT CONCAT(s.first_name, ' ', s.last_name) AS name,
                s.role
         FROM attendance a
         INNER JOIN staff s ON s.id = a.staff_id
         WHERE a.date = ? AND a.time_out IS NULL AND LOWER(s.role) <> 'admin'
         ORDER BY a.time_in DESC, s.first_name ASC, s.last_name ASC",
        [$today]
    );
    jsonResponse(['success' => true, 'date' => $today, 'period' => $currentPeriod, 'staff' => $records]);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

$identifier = strtoupper(trim((string)($_POST['identifier'] ?? '')));
$identifier = preg_replace('/^(?:CARD\s*)?UID\s*[:=#-]\s*/i', '', $identifier);
$identifier = preg_replace('/^0X/i', '', $identifier);
$identifier = preg_replace('/[\s:-]+/', '', $identifier);
if ($identifier === '' || strlen($identifier) > 64 || !preg_match('/^[A-Z0-9._-]+$/', $identifier)) {
    jsonResponse(['success' => false, 'message' => 'A valid staff ID or NFC card UID is required'], 400);
}

$staff = $db->fetch(
    "SELECT id, staff_id, first_name, last_name, role, status, profile_photo
     FROM staff
     WHERE (nfc_uid = ? OR (? REGEXP '^[0-9]{5}$' AND staff_id = ? AND LOWER(role) <> 'admin'))
     LIMIT 1",
    [$identifier, $identifier, $identifier]
);

if (!$staff) {
    jsonResponse(['success' => false, 'message' => 'No staff member matches this card or staff ID'], 404);
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
$staffPhoto = trim((string)($staff['profile_photo'] ?? ''));
$staffPhotoUrl = $staffPhoto !== ''
    ? UPLOAD_URL . rawurlencode(basename($staffPhoto))
    : null;
if (!$existing) {
    $status = ((int)$now->format('H') < 12 && $time > '08:15:00') ? 'late' : 'present';
    $db->query(
        "INSERT INTO attendance (staff_id, date, time_in, status, notes) VALUES (?, ?, ?, ?, ?)",
        [(int)$staff['id'], $date, $time, $status, 'NFC tap']
    );
    logActivity(($isPublicKiosk || $isLocalKiosk) ? null : (int)($_SESSION['user_id'] ?? 0), 'nfc_attendance_in', 'NFC time in: ' . $fullName);
    jsonResponse([
        'success' => true,
        'action' => 'time_in',
        'message' => $fullName . ' timed in at ' . $now->format('g:i A'),
        'staff' => ['name' => $fullName, 'role' => $staff['role'], 'photo' => $staffPhotoUrl, 'time' => $now->format('g:i A'), 'status' => $status]
    ]);
}

if (!empty($existing['time_out'])) {
    jsonResponse([
        'success' => false,
        'action' => 'already_completed',
        'message' => $fullName . ' already completed this attendance period',
        'staff' => [
            'name' => $fullName,
            'role' => $staff['role'],
            'photo' => $staffPhotoUrl,
            'time' => date('g:i A', strtotime((string)$existing['time_out'])),
            'status' => $existing['status']
        ]
    ], 409);
}

$db->query("UPDATE attendance SET time_out = ?, notes = ? WHERE id = ?", [$time, 'NFC tap', (int)$existing['id']]);
logActivity(($isPublicKiosk || $isLocalKiosk) ? null : (int)($_SESSION['user_id'] ?? 0), 'nfc_attendance_out', 'NFC time out: ' . $fullName);
jsonResponse([
    'success' => true,
    'action' => 'time_out',
    'message' => $fullName . ' timed out at ' . $now->format('g:i A'),
    'staff' => ['name' => $fullName, 'role' => $staff['role'], 'photo' => $staffPhotoUrl, 'time' => $now->format('g:i A'), 'status' => $existing['status']]
]);
