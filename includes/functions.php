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
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === $role;
}

function hasAnyRole($roles) {
    if (!isset($_SESSION['user_role'])) {
        return false;
    }
    return in_array($_SESSION['user_role'], $roles);
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
    $year = date('Y');
    $db = Database::getInstance();
    
    // Get the last job order number for this year
    $sql = "SELECT job_order_number FROM job_orders 
            WHERE job_order_number LIKE ? 
            ORDER BY id DESC LIMIT 1";
    $result = $db->fetch($sql, ["JO-{$year}-%"]);
    
    if ($result) {
        // Extract the number and increment
        $lastNumber = intval(substr($result['job_order_number'], -4));
        $newNumber = $lastNumber + 1;
    } else {
        $newNumber = 1;
    }
    
    return sprintf("JO-%s-%04d", $year, $newNumber);
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

    $filename = uniqid() . '_' . time() . '.' . $extension;
    $destination = UPLOAD_PATH . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        return ['success' => false, 'message' => 'Failed to move uploaded file'];
    }

    return ['success' => true, 'filename' => $filename, 'url' => UPLOAD_URL . $filename];
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
