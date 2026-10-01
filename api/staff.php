<?php
/**
 * Staff API Endpoint
 * Handles CRUD operations for staff management
 */

define('APP_ACCESS', true);
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../models/Staff.php';

// Set JSON header
header('Content-Type: application/json');

// Require login and admin role
if (!isLoggedIn()) {
    jsonResponse(['success' => false, 'message' => 'Unauthorized access'], 401);
}

if (!hasAnyRole(['admin', 'cashier', 'chief_mechanic', 'service_adviser'])) {
    jsonResponse(['success' => false, 'message' => 'Insufficient permissions'], 403);
}

$isCashier = hasRole('cashier');
$canManageStaff = hasAnyRole(['admin', 'cashier']);

$staffModel = new Staff();
$method = $_SERVER['REQUEST_METHOD'];

function dispatchStaffChangeNotification($title, $action, $subject, $details = '', $referenceId = null) {
    $actorName = sanitize($_SESSION['full_name'] ?? $_SESSION['username'] ?? 'Staff');
    notifyRoles(
        'staff_update',
        $title,
        buildNotificationMessageTemplate($actorName, $action, $subject, $details),
        ['admin', 'cashier'],
        [
            'exclude_user_id' => (int)($_SESSION['user_id'] ?? 0),
            'reference_type' => 'staff',
            'reference_id' => $referenceId !== null ? (int)$referenceId : null,
        ]
    );
}

// Allow method override for multipart form PUT requests
if ($method === 'POST' && !empty($_POST['_method'])) {
    $method = strtoupper($_POST['_method']);
}

try {
    switch ($method) {
        case 'GET':
            if (!empty($_GET['attendance_date'])) {
                handleAttendanceByDate($_GET['attendance_date']);
                break;
            }
            handleGet($staffModel);
            break;
        
        case 'POST':
            if (($_POST['action'] ?? '') === 'save_attendance') {
                if (!hasAnyRole(['admin', 'cashier'])) {
                    jsonResponse(['success' => false, 'message' => 'Only admin or cashier can save attendance'], 403);
                }
                handleAttendance();
                break;
            }
            if (($_POST['action'] ?? '') === 'register_nfc') {
                if (!$canManageStaff) {
                    jsonResponse(['success' => false, 'message' => 'Insufficient permissions'], 403);
                }
                handleNfcRegistration($staffModel);
                break;
            }
            if (!$canManageStaff) {
                jsonResponse(['success' => false, 'message' => 'Insufficient permissions'], 403);
            }
            handlePost($staffModel);
            break;
        
        case 'PUT':
            if (!$canManageStaff) {
                jsonResponse(['success' => false, 'message' => 'Insufficient permissions'], 403);
            }
            handlePut($staffModel);
            break;
        
        case 'DELETE':
            if (!$canManageStaff) {
                jsonResponse(['success' => false, 'message' => 'Insufficient permissions'], 403);
            }
            handleDelete($staffModel);
            break;
        
        default:
            jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
    }
} catch (Throwable $e) {
    error_log("Staff API Error: " . $e->getMessage());
    $message = $e->getMessage();
    $statusCode = $message === 'Select at least one attendance status before saving' ? 400 : 500;
    jsonResponse(['success' => false, 'message' => $message ?: 'Unable to save attendance'], $statusCode);
}

/**
 * Save one attendance status per staff member for the selected date.
 */
function handleAttendance() {
    $date = trim((string)($_POST['date'] ?? ''));
    $dateObject = DateTime::createFromFormat('Y-m-d', $date);
    if (!$dateObject || $dateObject->format('Y-m-d') !== $date) {
        jsonResponse(['success' => false, 'message' => 'A valid attendance date is required'], 400);
    }

    $statuses = $_POST['status'] ?? [];
    $notes = $_POST['notes'] ?? [];
    $times = $_POST['time'] ?? [];
    $timeOuts = $_POST['time_out'] ?? [];
    if (!is_array($statuses) || empty($statuses)) {
        jsonResponse(['success' => false, 'message' => 'Attendance records are required'], 400);
    }

    $allowedStatuses = ['present', 'late', 'absent', 'on_leave', 'other'];
    $db = Database::getInstance();
    $savedRecords = 0;
    $db->getConnection()->beginTransaction();
    try {
        foreach (['morning' => '08:00:00', 'afternoon' => '13:00:00'] as $period => $defaultTime) {
            foreach (($statuses[$period] ?? []) as $staffId => $status) {
            $staffId = (int)$staffId;
            $status = strtolower(trim((string)$status));
            if ($staffId < 1 || ($status !== '' && !in_array($status, $allowedStatuses, true))) {
                throw new Exception('Invalid attendance record');
            }
            if (!$db->fetch("SELECT id FROM staff WHERE id = ?", [$staffId])) {
                throw new Exception('Staff member not found');
            }

            $existing = $db->fetch(
                "SELECT id FROM attendance WHERE staff_id = ? AND date = ? AND time_in " . ($period === 'morning' ? "< '12:00:00'" : ">= '12:00:00'") . " ORDER BY id DESC LIMIT 1",
                [$staffId, $date]
            );
            if ($status === '') {
                if ($existing) {
                    $db->query("DELETE FROM attendance WHERE id = ?", [(int)$existing['id']]);
                    $savedRecords++;
                }
                continue;
            }

            $nonTimedStatus = in_array($status, ['absent', 'on_leave', 'other'], true);
            $timeIn = $defaultTime;
            $timeOut = '';
            if (!$nonTimedStatus) {
                $timeIn = trim((string)($times[$period][$staffId] ?? ''));
                if ($timeIn === '') {
                    $timeIn = $defaultTime;
                } elseif (!preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $timeIn)) {
                    throw new Exception('Invalid attendance time');
                } else {
                    $timeIn .= ':00';
                }
                $timeOut = trim((string)($timeOuts[$period][$staffId] ?? ''));
                if ($timeOut !== '') {
                    if (!preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $timeOut)) {
                        throw new Exception('Invalid time out');
                    }
                    $timeOut .= ':00';
                }
            }

            $note = sanitize(trim((string)($notes[$period][$staffId] ?? '')));
            if ($status === 'other' && $note === '') {
                throw new Exception('Details are required for Other attendance');
            }
            if ($status !== 'other') {
                $note = '';
            }

        if ($existing) {
            $db->query(
                "UPDATE attendance SET status = ?, notes = ?, time_in = ?, time_out = ? WHERE id = ?",
                [$status, $note, $timeIn, $timeOut !== '' ? $timeOut : null, (int)$existing['id']]
            );
        } else {
            $db->query(
                "INSERT INTO attendance (staff_id, date, time_in, time_out, status, notes) VALUES (?, ?, ?, ?, ?, ?)",
                [$staffId, $date, $timeIn, $timeOut !== '' ? $timeOut : null, $status, $note]
            );
        }
                $savedRecords++;
            }
        }
        if ($savedRecords === 0) {
            throw new Exception('Select at least one attendance status before saving');
        }
        $db->getConnection()->commit();
    } catch (Throwable $e) {
        if ($db->getConnection()->inTransaction()) {
            $db->getConnection()->rollBack();
        }
        throw $e;
    }

    logActivity($_SESSION['user_id'], 'update_attendance', 'Updated staff attendance for ' . $date);
    jsonResponse(['success' => true, 'message' => 'Attendance saved successfully']);
}

function handleAttendanceByDate($date) {
    $date = trim((string)$date);
    $dateObject = DateTime::createFromFormat('Y-m-d', $date);
    if (!$dateObject || $dateObject->format('Y-m-d') !== $date) {
        jsonResponse(['success' => false, 'message' => 'A valid attendance date is required'], 400);
    }

    $records = Database::getInstance()->fetchAll(
        "SELECT staff_id, time_in, time_out, status, notes
         FROM attendance
         WHERE date = ?
         ORDER BY time_in ASC",
        [$date]
    );
    jsonResponse(['success' => true, 'data' => $records]);
}

function handleNfcRegistration($staffModel) {
    $staffId = (int)($_POST['staff_id'] ?? 0);
    if ($staffId <= 0) {
        jsonResponse(['success' => false, 'message' => 'A valid staff record is required'], 400);
    }

    $staff = $staffModel->findById($staffId);
    if (!$staff) {
        jsonResponse(['success' => false, 'message' => 'Staff member not found'], 404);
    }

    $uid = strtoupper(trim((string)($_POST['nfc_uid'] ?? '')));
    $uid = preg_replace('/^(?:CARD\s*)?UID\s*[:=#-]\s*/i', '', $uid);
    $uid = preg_replace('/^0X/i', '', $uid);
    $uid = preg_replace('/[\s:-]+/', '', $uid);
    $uid = strtoupper($uid);
    if ($uid !== '' && !preg_match('/^(?:[A-F0-9]{8}(?:[A-F0-9]{6}|[A-F0-9]{12})?|\d{10})$/', $uid)) {
        jsonResponse(['success' => false, 'message' => 'Scan a valid NFC UID, or clear the field to remove the card'], 400);
    }

    if ($uid !== '') {
        $existing = $staffModel->findByNfcUid($uid);
        if ($existing && (int)$existing['id'] !== $staffId) {
            jsonResponse(['success' => false, 'message' => 'This NFC card is already registered to another staff member'], 409);
        }
    }

    if (!$staffModel->updateNfcUid($staffId, $uid === '' ? null : $uid)) {
        jsonResponse(['success' => false, 'message' => 'Unable to save NFC card registration'], 500);
    }

    $action = $uid === '' ? 'Removed NFC card from staff: ' : 'Registered NFC card for staff: ';
    logActivity((int)$_SESSION['user_id'], 'update_staff_nfc', $action . ($staff['full_name'] ?? $staff['staff_id']));
    jsonResponse([
        'success' => true,
        'message' => $uid === '' ? 'NFC card removed from staff member' : 'NFC card registered successfully'
    ]);
}

/**
 * Handle GET requests
 */
function handleGet($staffModel) {
    // Get single staff by ID
    if (isset($_GET['id'])) {
        $staff = $staffModel->findById($_GET['id']);
        
        if (!$staff) {
            jsonResponse(['success' => false, 'message' => 'Staff not found'], 404);
        }
        
        // Remove password from response
        unset($staff['password']);

        $staff['assigned_job_orders'] = [];
        $staff['attendance'] = [];
        $db = Database::getInstance();
        if (($staff['role'] ?? '') !== 'admin') {
            $staff['attendance'] = $db->fetchAll(
                "SELECT id, date, time_in, time_out, status, notes
                 FROM attendance
                 WHERE staff_id = ?
                 ORDER BY date DESC, time_in DESC",
                [(int)$staff['id']]
            );
        }

        if (($staff['role'] ?? '') === 'technician') {
            $assignedJobOrders = $db->fetchAll(
                "SELECT jo.id,
                        jo.job_order_number,
                        jo.status,
                        jo.created_at,
                        jo.status_timer_seconds,
                        jo.status_timer_started_at,
                        jot.work_duration AS tech_work_duration,
                        jot.started_at AS tech_started_at,
                        jot.status AS tech_status,
                        jot.id AS jot_id,
                        c.full_name AS customer_name,
                        v.plate_number
                 FROM job_orders jo
                 INNER JOIN job_order_technicians jot ON jot.job_order_id = jo.id
                 LEFT JOIN customers c ON c.id = jo.customer_id
                 LEFT JOIN vehicles v ON v.id = jo.vehicle_id
                 WHERE jot.technician_id = ?
                   AND jot.id = (
                       SELECT MAX(j2.id) FROM job_order_technicians j2
                       WHERE j2.job_order_id = jot.job_order_id
                         AND j2.technician_id = jot.technician_id
                   )
                 ORDER BY jo.created_at DESC",
                [(int)$staff['id']]
            );

            foreach ($assignedJobOrders as &$jo) {
                // Technician's own elapsed time
                $techBanked = max(0, (int)($jo['tech_work_duration'] ?? 0));
                $techLive = 0;
                if (in_array($jo['tech_status'], ['assigned', 'working'], true) && !empty($jo['tech_started_at'])) {
                    $techLive = max(0, time() - strtotime($jo['tech_started_at']));
                }
                $techTotal = $techBanked + $techLive;
                $jo['tech_elapsed_display'] = sprintf('%02d:%02d:%02d', floor($techTotal / 3600), floor(($techTotal % 3600) / 60), $techTotal % 60);

                // JO timer (for reference)
                $elapsedSeconds = (int)($jo['status_timer_seconds'] ?? 0);
                if (in_array($jo['status'] ?? '', ['ongoing', 'under_inspection', 'returned_for_revision'], true) && !empty($jo['status_timer_started_at'])) {
                    $elapsedSeconds += max(0, time() - strtotime($jo['status_timer_started_at']));
                }
                $jo['elapsed_display'] = sprintf('%02d:%02d:%02d', floor($elapsedSeconds / 3600), floor(($elapsedSeconds % 3600) / 60), $elapsedSeconds % 60);

                // Fetch work sessions for activity log
                $jo['work_sessions'] = $db->fetchAll(
                    "SELECT start_time, end_time, duration, notes
                     FROM work_sessions
                     WHERE job_order_technician_id = ?
                     ORDER BY start_time ASC",
                    [(int)$jo['jot_id']]
                );
            }
            unset($jo);

            $staff['assigned_job_orders'] = $assignedJobOrders;
        }
        
        jsonResponse(['success' => true, 'data' => $staff]);
    }
    
    // Get all staff with filters
    $filters = [
        'role' => $_GET['role'] ?? '',
        'status' => $_GET['status'] ?? '',
        'search' => $_GET['search'] ?? '',
        'limit' => $_GET['limit'] ?? RECORDS_PER_PAGE,
        'offset' => $_GET['offset'] ?? 0
    ];
    
    $staffList = $staffModel->getAll($filters);
    $totalRecords = $staffModel->count($filters);
    
    // Remove passwords from all staff records
    foreach ($staffList as &$staff) {
        unset($staff['password']);
    }
    
    jsonResponse([
        'success' => true,
        'data' => $staffList,
        'total' => $totalRecords
    ]);
}

/**
 * Handle POST requests (Create)
 */
function handlePost($staffModel) {
    global $isCashier;
    // Validate required fields
    $requiredFields = ['full_name', 'password', 'email', 'contact_number', 'role'];
    foreach ($requiredFields as $field) {
        if (empty($_POST[$field])) {
            jsonResponse(['success' => false, 'message' => ucfirst(str_replace('_', ' ', $field)) . ' is required'], 400);
        }
    }
    
    // Validate password confirmation
    if ($_POST['password'] !== $_POST['confirm_password']) {
        jsonResponse(['success' => false, 'message' => 'Passwords do not match'], 400);
    }
    
    // Validate password strength
    if (strlen($_POST['password']) < 6) {
        jsonResponse(['success' => false, 'message' => 'Password must be at least 6 characters'], 400);
    }
    
    // Validate email format
    if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        jsonResponse(['success' => false, 'message' => 'Invalid email format'], 400);
    }
    
    // Check if email already exists
    if ($staffModel->emailExists($_POST['email'])) {
        jsonResponse(['success' => false, 'message' => 'Email already exists'], 400);
    }

    // Validate role value
    $allowedRoles = ['admin', 'cashier', 'chief_mechanic', 'service_adviser', 'technician', 'lead_man', 'stockman'];
    if (!in_array($_POST['role'], $allowedRoles, true)) {
        jsonResponse(['success' => false, 'message' => 'Invalid staff role'], 400);
    }
    if ($_POST['role'] === 'admin' && !hasRole('admin')) {
        jsonResponse(['success' => false, 'message' => 'Only admin can assign admin role'], 403);
    }
    
    // Handle profile image upload
    $profileImage = null;
    if (!empty($_FILES['profile_image']['name'])) {
        $uploadResult = uploadFile($_FILES['profile_image'], ['jpg', 'jpeg', 'png', 'webp'], MAX_FILE_SIZE);
        
        if (!$uploadResult['success']) {
            jsonResponse(['success' => false, 'message' => $uploadResult['message']], 400);
        }
        
        $profileImage = $uploadResult['filename'];
    }
    
    // Prepare data
    $data = [
        'full_name' => sanitize($_POST['full_name']),
        'password' => $_POST['password'],
        'email' => sanitize($_POST['email']),
        'contact_number' => sanitize($_POST['contact_number']),
        'address' => sanitize($_POST['address'] ?? ''),
        'role' => sanitize($_POST['role']),
        'status' => sanitize($_POST['status'] ?? 'active'),
        'profile_image' => $profileImage
    ];
    
    // Create staff
    $staffId = $staffModel->create($data);
    
    if (!$staffId) {
        jsonResponse(['success' => false, 'message' => 'Failed to create staff'], 500);
    }
    
    // Log activity
    logActivity($_SESSION['user_id'], 'create_staff', 'Created staff: ' . $data['full_name']);
    dispatchStaffChangeNotification(
        'Staff Added',
        'added',
        'staff ' . $data['full_name'],
        'Role: ' . strtoupper((string)($data['role'] ?? '')) . ', Status: ' . strtoupper((string)($data['status'] ?? 'active')),
        (int)$staffId
    );
    
    jsonResponse([
        'success' => true,
        'message' => 'Staff created successfully',
        'id' => $staffId
    ]);
}

/**
 * Handle PUT requests (Update)
 */
function handlePut($staffModel) {
    global $isCashier;
    // Parse PUT data or method-override POST data
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $_PUT = $_POST;
    } else {
        parse_str(file_get_contents("php://input"), $_PUT);
    }
    
    // Get staff ID
    if (empty($_PUT['id'])) {
        jsonResponse(['success' => false, 'message' => 'Staff ID is required'], 400);
    }
    
    $staffId = (int)$_PUT['id'];
    
    // Check if staff exists
    $existingStaff = $staffModel->findById($staffId);
    if (!$existingStaff) {
        jsonResponse(['success' => false, 'message' => 'Staff not found'], 404);
    }

    $expectedUpdatedAt = trim((string)($_PUT['expected_updated_at'] ?? ''));
    if ($expectedUpdatedAt !== '' && (string)($existingStaff['updated_at'] ?? '') !== $expectedUpdatedAt) {
        jsonResponse([
            'success' => false,
            'message' => 'Conflict: this staff record was updated by another user. Please refresh and try again.'
        ], 409);
    }
    
    // If this is a status-only update, allow it without full validation
    if (!empty($_PUT['status']) && empty($_PUT['full_name']) && empty($_PUT['email']) && empty($_PUT['contact_number']) && empty($_PUT['role'])) {
        $data = ['status' => sanitize($_PUT['status'])];
        $success = $staffModel->update($staffId, $data);
        
        if (!$success) {
            jsonResponse(['success' => false, 'message' => 'Failed to update staff status'], 500);
        }
        
        logActivity(
            $_SESSION['user_id'],
            'update_staff_status',
            'Updated staff status: ' . $existingStaff['full_name'] . ' (' . ($existingStaff['status'] ?? 'unknown') . ' -> ' . ($data['status'] ?? 'unknown') . ')'
        );
        dispatchStaffChangeNotification(
            'Staff Status Updated',
            'updated status for',
            'staff ' . ($existingStaff['full_name'] ?? ('#' . $staffId)),
            'Status: ' . strtoupper((string)($existingStaff['status'] ?? 'unknown')) . ' -> ' . strtoupper((string)($data['status'] ?? 'unknown')),
            $staffId
        );
        jsonResponse(['success' => true, 'message' => 'Staff status updated successfully']);
    }
    
    // Validate required fields for full update
    $requiredFields = ['full_name', 'email', 'contact_number', 'role'];
    foreach ($requiredFields as $field) {
        if (empty($_PUT[$field])) {
            jsonResponse(['success' => false, 'message' => ucfirst(str_replace('_', ' ', $field)) . ' is required'], 400);
        }
    }
    
    // Validate password if provided
    if (!empty($_PUT['password'])) {
        if ($_PUT['password'] !== $_PUT['confirm_password']) {
            jsonResponse(['success' => false, 'message' => 'Passwords do not match'], 400);
        }
        
        if (strlen($_PUT['password']) < 6) {
            jsonResponse(['success' => false, 'message' => 'Password must be at least 6 characters'], 400);
        }
    }
    
    // Validate email format
    if (!filter_var($_PUT['email'], FILTER_VALIDATE_EMAIL)) {
        jsonResponse(['success' => false, 'message' => 'Invalid email format'], 400);
    }
    
    // Check if email already exists for other staff
    if ($staffModel->emailExists($_PUT['email'], $staffId)) {
        jsonResponse(['success' => false, 'message' => 'Email already exists'], 400);
    }

    // Validate role value
    $allowedRoles = ['admin', 'cashier', 'chief_mechanic', 'service_adviser', 'technician', 'lead_man', 'stockman'];
    if (!in_array($_PUT['role'], $allowedRoles, true)) {
        jsonResponse(['success' => false, 'message' => 'Invalid staff role'], 400);
    }
    if ($_PUT['role'] === 'admin' && !hasRole('admin')) {
        jsonResponse(['success' => false, 'message' => 'Only admin can assign admin role'], 403);
    }
    
    // Handle profile image upload if provided
    if (!empty($_FILES['profile_image']['name'])) {
        $uploadResult = uploadFile($_FILES['profile_image'], ['jpg', 'jpeg', 'png', 'webp'], MAX_FILE_SIZE);
        if (!$uploadResult['success']) {
            jsonResponse(['success' => false, 'message' => $uploadResult['message']], 400);
        }
        $_PUT['profile_image'] = $uploadResult['filename'];
    }
    
    // Prepare data
    $data = [
        'full_name' => sanitize($_PUT['full_name']),
        'email' => sanitize($_PUT['email']),
        'contact_number' => sanitize($_PUT['contact_number']),
        'address' => sanitize($_PUT['address'] ?? ''),
        'role' => sanitize($_PUT['role']),
        'status' => sanitize($_PUT['status'] ?? 'active')
    ];
    
    // Add password if provided
    if (!empty($_PUT['password'])) {
        $data['password'] = $_PUT['password'];
    }
    
    if (!empty($_PUT['profile_image'])) {
        $data['profile_image'] = $_PUT['profile_image'];
    }
    
    // Update staff
    $success = $staffModel->update($staffId, $data);
    
    if (!$success) {
        jsonResponse(['success' => false, 'message' => 'Failed to update staff'], 500);
    }
    
    // Log activity
    logActivity($_SESSION['user_id'], 'update_staff', 'Updated staff: ' . $data['full_name']);
    $oldRole = strtoupper((string)($existingStaff['role'] ?? ''));
    $newRole = strtoupper((string)($data['role'] ?? ''));
    $oldStatus = strtoupper((string)($existingStaff['status'] ?? ''));
    $newStatus = strtoupper((string)($data['status'] ?? ''));
    dispatchStaffChangeNotification(
        'Staff Updated',
        'updated',
        'staff ' . $data['full_name'],
        'Role: ' . $oldRole . ' -> ' . $newRole . '; Status: ' . $oldStatus . ' -> ' . $newStatus,
        $staffId
    );
    
    jsonResponse([
        'success' => true,
        'message' => 'Staff updated successfully'
    ]);
}

/**
 * Handle DELETE requests
 */
function handleDelete($staffModel) {
    global $isCashier;
    if ($isCashier) {
        jsonResponse(['success' => false, 'message' => 'Cashier is not allowed to delete staff records'], 403);
    }
    // Get staff ID
    if (empty($_GET['id'])) {
        jsonResponse(['success' => false, 'message' => 'Staff ID is required'], 400);
    }
    
    $staffId = (int)$_GET['id'];
    
    // Check if staff exists
    $staff = $staffModel->findById($staffId);
    if (!$staff) {
        jsonResponse(['success' => false, 'message' => 'Staff not found'], 404);
    }
    
    $archiveId = archiveDeletedRecord('staff', $staff, ['source' => 'database', 'meta' => ['table' => 'staff']]);
    if (!$archiveId) {
        jsonResponse(['success' => false, 'message' => 'Failed to archive staff record before deletion'], 500);
    }
    
    // Delete staff
    $success = $staffModel->delete($staffId);
    
    if (!$success) {
        deleteArchivedRecordById($archiveId);
        jsonResponse(['success' => false, 'message' => 'Failed to delete staff'], 500);
    }
    
    // Log activity
    logActivity($_SESSION['user_id'], 'delete_staff', 'Deleted staff: ' . $staff['full_name']);
    dispatchStaffChangeNotification(
        'Staff Removed',
        'removed',
        'staff ' . ($staff['full_name'] ?? ('#' . $staffId)),
        'Role: ' . strtoupper((string)($staff['role'] ?? '')),
        $staffId
    );
    
    jsonResponse([
        'success' => true,
        'message' => 'Staff deleted successfully'
    ]);
}
