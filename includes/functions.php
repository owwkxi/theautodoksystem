<?php

function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

function escape($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function isValidPhone($phone) {
    return preg_match('/^(09|\+639)\d{9}$/', $phone);
}

function generateCSRFToken() {
    if (empty($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

function verifyCSRFToken($token) {
    if (!isset($_SESSION[CSRF_TOKEN_NAME]) || !isset($token)) {
        return false;
    }
    return hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
}

function generateRandomString($length = 10) {
    return bin2hex(random_bytes($length / 2));
}

function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => PASSWORD_COST]);
}

function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

function redirect($url) {
    header("Location: " . $url);
    exit();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function hasRole($role) {
    return isset($_SESSION['user_role']) && strcasecmp($_SESSION['user_role'], $role) === 0;
}

function hasAnyRole($roles) {
    if (!isset($_SESSION['user_role'])) {
        return false;
    }
    foreach ((array)$roles as $role) {
        if (strcasecmp($_SESSION['user_role'], $role) === 0) {
            return true;
        }
    }
    return false;
}

function requireLogin() {
    if (!isLoggedIn()) {
        redirect(APP_URL . '/views/auth/login.php');
    }
}

function requireRole($role) {
    requireLogin();
    if (!hasRole($role)) {
        redirect(APP_URL . '/views/dashboard/index.php');
    }
}

function formatDate($date, $format = DISPLAY_DATE_FORMAT) {
    if (empty($date)) return '';
    return date($format, strtotime($date));
}

function formatDateTime($datetime, $format = DISPLAY_DATETIME_FORMAT) {
    if (empty($datetime)) return '';
    return date($format, strtotime($datetime));
}

function formatCurrency($amount) {
    return '₱' . number_format($amount, 2);
}

function getDefaultPrintTemplateSettings() {
    return [
        'company_name' => 'THE AUTODOK',
        'company_subtitle' => 'Automotive Care Services',
        'contact_line' => 'Tel: (02) XXX-XXXX | autodok@email.com',
        'logo_url' => APP_URL . '/assets/images/logo.png',
        'footer_note' => 'Thank you for choosing The Autodok - Automotive Care Services',
        'header_template' => '<table style="width:100%;border-collapse:collapse;margin-bottom:8px;"><tr><td style="width:70px;vertical-align:middle;padding-right:12px;"><img src="{{logo_url}}" style="width:60px;height:60px;object-fit:contain;" alt="Logo"></td><td style="vertical-align:middle;"><div style="font-size:17pt;font-weight:700;letter-spacing:2px;line-height:1.1;">{{company_name}}</div><div style="font-size:9pt;color:#444;">{{company_subtitle}}</div><div style="font-size:8.5pt;color:#666;">{{contact_line}}</div></td><td style="text-align:right;vertical-align:middle;font-size:9pt;"><div style="font-size:12pt;font-weight:700;">{{document_title}}</div><div style="color:#555;"># {{document_number}}</div><div style="margin-top:4px;"><strong>Date:</strong> {{document_date}}</div></td></tr></table><hr style="border:none;border-top:1.5px solid #333;margin-bottom:10px;">',
        'footer_template' => '<div style="text-align:center;font-size:8pt;color:#999;margin-top:10px;border-top:1px solid #ddd;padding-top:5px;">{{footer_note}}</div>'
    ];
}

function getPrintTemplateSettings() {
    $defaults = getDefaultPrintTemplateSettings();
    $filePath = UPLOAD_PATH . 'print_template_settings.json';

    if (!file_exists($filePath)) {
        return $defaults;
    }

    $raw = file_get_contents($filePath);
    if ($raw === false || trim($raw) === '') {
        return $defaults;
    }

    $decoded = json_decode($raw, true);
    if (!is_array($decoded)) {
        return $defaults;
    }

    return array_merge($defaults, $decoded);
}

function savePrintTemplateSettings($settings) {
    $defaults = getDefaultPrintTemplateSettings();
    $merged = array_merge($defaults, (array)$settings);

    $normalized = [];
    foreach ($defaults as $key => $defaultValue) {
        $normalized[$key] = trim((string)($merged[$key] ?? $defaultValue));
        if ($normalized[$key] === '') {
            $normalized[$key] = $defaultValue;
        }
    }

    if (!is_dir(UPLOAD_PATH) && !mkdir(UPLOAD_PATH, 0755, true) && !is_dir(UPLOAD_PATH)) {
        return false;
    }

    if (!is_writable(UPLOAD_PATH)) {
        return false;
    }

    $filePath = UPLOAD_PATH . 'print_template_settings.json';
    $json = json_encode($normalized, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($json === false) {
        return false;
    }

    return file_put_contents($filePath, $json) !== false;
}

function timeAgo($datetime) {
    $timestamp = strtotime($datetime);
    $difference = time() - $timestamp;
    
    if ($difference < 60) {
        return 'just now';
    } elseif ($difference < 3600) {
        $minutes = floor($difference / 60);
        return $minutes . ' minute' . ($minutes > 1 ? 's' : '') . ' ago';
    } elseif ($difference < 86400) {
        $hours = floor($difference / 3600);
        return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
    } elseif ($difference < 604800) {
        $days = floor($difference / 86400);
        return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    } else {
        return formatDate($datetime);
    }
}

function generateJobOrderNumber() {
    $db = Database::getInstance();

    $result = $db->fetch(
        "SELECT MAX(CAST(SUBSTRING(job_order_number, 3) AS UNSIGNED)) AS max_num
         FROM job_orders
         WHERE job_order_number REGEXP '^JO[0-9]+$'"
    );

    $newNumber = (int)($result['max_num'] ?? 0) + 1;
    return 'JO' . str_pad((string)$newNumber, 3, '0', STR_PAD_LEFT);
}

function uploadFile($file, $allowedTypes = ALLOWED_FILE_TYPES, $maxSize = MAX_FILE_SIZE) {
    if (!isset($file['error']) || is_array($file['error'])) {
        return ['success' => false, 'message' => 'Invalid file upload'];
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'File upload error'];
    }

    if ($file['size'] > $maxSize) {
        return ['success' => false, 'message' => 'File size exceeds limit'];
    }

    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, $allowedTypes)) {
        return ['success' => false, 'message' => 'File type not allowed'];
    }

    if (!is_dir(UPLOAD_PATH)) {
        if (!mkdir(UPLOAD_PATH, 0755, true) && !is_dir(UPLOAD_PATH)) {
            return ['success' => false, 'message' => 'Upload directory is not writable'];
        }
    }

    if (!is_writable(UPLOAD_PATH)) {
        return ['success' => false, 'message' => 'Upload directory is not writable'];
    }

    $filename = uniqid() . '_' . time() . '.' . $extension;
    $destination = UPLOAD_PATH . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        return ['success' => false, 'message' => 'Failed to move uploaded file'];
    }

    return ['success' => true, 'filename' => $filename, 'url' => UPLOAD_URL . $filename];
}

function getRoleLabel($role) {
    $labels = [
        'cashier' => 'Cashier',
        'chief_mechanic' => 'Chief Mechanic',
        'service_adviser' => 'Service Adviser',
        'lead_man' => 'Lead Man',
        'technician' => 'Technician'
    ];
    return $labels[$role] ?? ucfirst(str_replace('_', ' ', $role));
}

function getStatusLabel($status) {
    $labels = [
        'active' => 'Active',
        'inactive' => 'Inactive',
        'on_leave' => 'On Leave'
    ];
    return $labels[$status] ?? ucfirst(str_replace('_', ' ', $status));
}

function deleteFile($filename) {
    $filepath = UPLOAD_PATH . $filename;
    if (file_exists($filepath)) {
        return unlink($filepath);
    }
    return false;
}

function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit();
}

function getClientIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        return $_SERVER['REMOTE_ADDR'];
    }
}

function getUserAgent() {
    return $_SERVER['HTTP_USER_AGENT'] ?? '';
}

function logActivity($userId, $action, $description = null) {
    try {
        $db = Database::getInstance();
        $sql = "INSERT INTO activity_logs (user_id, action, description, ip_address, user_agent) 
                VALUES (?, ?, ?, ?, ?)";
        $db->query($sql, [
            $userId,
            $action,
            $description,
            getClientIP(),
            getUserAgent()
        ]);
    } catch (Exception $e) {
        error_log("Failed to log activity: " . $e->getMessage());
    }
}

function paginate($totalRecords, $currentPage = 1, $recordsPerPage = RECORDS_PER_PAGE) {
    $totalPages = ceil($totalRecords / $recordsPerPage);
    $currentPage = max(1, min($currentPage, $totalPages));
    $offset = ($currentPage - 1) * $recordsPerPage;
    
    return [
        'total_records' => $totalRecords,
        'total_pages' => $totalPages,
        'current_page' => $currentPage,
        'records_per_page' => $recordsPerPage,
        'offset' => $offset,
        'has_previous' => $currentPage > 1,
        'has_next' => $currentPage < $totalPages
    ];
}

function generateJWT($payload) {
    $header = json_encode(['typ' => 'JWT', 'alg' => JWT_ALGORITHM]);
    $payload['iat'] = time();
    $payload['exp'] = time() + JWT_EXPIRATION;
    $payload = json_encode($payload);
    
    $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
    $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));
    
    $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, JWT_SECRET_KEY, true);
    $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
    
    return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
}

function verifyJWT($token) {
    $tokenParts = explode('.', $token);
    if (count($tokenParts) !== 3) {
        return false;
    }
    
    list($base64UrlHeader, $base64UrlPayload, $base64UrlSignature) = $tokenParts;
    
    $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, JWT_SECRET_KEY, true);
    $base64UrlSignatureCheck = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
    
    if ($base64UrlSignature !== $base64UrlSignatureCheck) {
        return false;
    }
    
    $payload = json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], $base64UrlPayload)), true);
    
    if (!isset($payload['exp']) || $payload['exp'] < time()) {
        return false;
    }
    
    return $payload;
}

function getAuthorizationHeader() {
    $headers = null;
    if (isset($_SERVER['Authorization'])) {
        $headers = trim($_SERVER["Authorization"]);
    } elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) {
        $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
    } elseif (function_exists('apache_request_headers')) {
        $requestHeaders = apache_request_headers();
        $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
        if (isset($requestHeaders['Authorization'])) {
            $headers = trim($requestHeaders['Authorization']);
        }
    }
    return $headers;
}

function getBearerToken() {
    $headers = getAuthorizationHeader();
    if (!empty($headers)) {
        if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
            return $matches[1];
        }
    }
    return null;
}


/**
 * Generate unique staff ID
 * @return string Staff ID in 5 random-digit format
 */
function generateStaffId() {
    $db = Database::getInstance();

    for ($attempt = 0; $attempt < 50; $attempt++) {
        $candidate = str_pad((string) random_int(0, 99999), 5, '0', STR_PAD_LEFT);
        $exists = $db->fetch(
            "SELECT id FROM staff WHERE staff_id = ? OR username = ? LIMIT 1",
            [$candidate, $candidate]
        );
        if (!$exists) {
            return $candidate;
        }
    }

    throw new RuntimeException('Unable to generate unique staff ID');
}

// Note: setMessage(), getMessage(), and hasMessage() functions 
// are already defined in includes/session.php
