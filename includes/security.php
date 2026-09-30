<?php
/**
 * Security Middleware
 * Comprehensive security functions for The Autodok System
 */

if (!defined('APP_ACCESS')) {
    die('Direct access not permitted');
}

if (!function_exists('csrfField')) {

    // ============================================================================
    // CSRF PROTECTION
    // ============================================================================

/**
 * Generate CSRF field for forms
 */
function csrfField() {
    $token = generateCSRFToken();
    return '<input type="hidden" name="csrf_token" value="' . escape($token) . '">';
}

/**
 * Validate CSRF token from request
 */
function validateCSRF() {
    $token = $_POST['csrf_token'] ?? $_GET['csrf_token'] ?? '';
    if (!verifyCSRFToken($token)) {
        http_response_code(403);
        die('CSRF token validation failed. Please refresh the page and try again.');
    }
}

// ============================================================================
// INPUT VALIDATION & SANITIZATION
// ============================================================================

/**
 * Sanitize for database (additional layer before PDO)
 */
function sanitizeForDB($input) {
    return trim(strip_tags($input));
}

/**
 * Validate password strength
 */
function isStrongPassword($password) {
    // At least 8 characters, 1 uppercase, 1 lowercase, 1 number
    return strlen($password) >= 8 &&
           preg_match('/[A-Z]/', $password) &&
           preg_match('/[a-z]/', $password) &&
           preg_match('/[0-9]/', $password);
}

/**
 * Validate numeric input
 */
function isValidNumber($value, $min = null, $max = null) {
    if (!is_numeric($value)) {
        return false;
    }
    if ($min !== null && $value < $min) {
        return false;
    }
    if ($max !== null && $value > $max) {
        return false;
    }
    return true;
}

/**
 * Validate date format
 */
function isValidDate($date, $format = 'Y-m-d') {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

/**
 * Sanitize filename
 */
function sanitizeFilename($filename) {
    // Remove any path components
    $filename = basename($filename);
    // Remove special characters
    $filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
    return $filename;
}

// ============================================================================
// AUTHENTICATION & AUTHORIZATION
// ============================================================================

/**
 * Check if staff is logged in
 */
function isStaffLoggedIn() {
    return isset($_SESSION['staff_id']) && !empty($_SESSION['staff_id']);
}

/**
 * Check if user has permission
 */
function hasPermission($permissionCode) {
    if (!isLoggedIn() && !isStaffLoggedIn()) {
        return false;
    }
    
    // Admin has all permissions
    if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
        return true;
    }
    
    // Check staff permissions
    if (isset($_SESSION['staff_role'])) {
        $db = Database::getInstance();
        $sql = "SELECT COUNT(*) as count FROM role_permissions rp
                INNER JOIN permissions p ON rp.permission_id = p.id
                WHERE rp.role = ? AND p.permission_code = ?";
        $result = $db->fetch($sql, [$_SESSION['staff_role'], $permissionCode]);
        return $result && $result['count'] > 0;
    }
    
    return false;
}

/**
 * Require permission
 */
function requirePermission($permissionCode) {
    requireLogin();
    if (!hasPermission($permissionCode)) {
        http_response_code(403);
        setMessage('Access denied: Insufficient permissions', 'error');
        redirect(routeUrl('dashboard'));
    }
}

/**
 * Require any of specified roles
 */
function requireAnyRole($roles) {
    requireLogin();
    if (!hasAnyRole($roles)) {
        http_response_code(403);
        setMessage('Access denied: Insufficient permissions', 'error');
        redirect(routeUrl('dashboard'));
    }
}

/**
 * Regenerate session ID for security
 */
function regenerateSession() {
    session_regenerate_id(true);
}

/**
 * Destroy session completely
 */
function destroySession() {
    $_SESSION = [];
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }
    session_destroy();
}

// ============================================================================
// RATE LIMITING
// ============================================================================

/**
 * Check rate limit for an action
 */
function checkRateLimit($action, $maxAttempts = 5, $timeWindow = 300) {
    $key = 'rate_limit_' . $action . '_' . getClientIP();
    
    if (!isset($_SESSION[$key])) {
        $_SESSION[$key] = [
            'attempts' => 1,
            'first_attempt' => time()
        ];
        return true;
    }
    
    $data = $_SESSION[$key];
    $timePassed = time() - $data['first_attempt'];
    
    // Reset if time window passed
    if ($timePassed > $timeWindow) {
        $_SESSION[$key] = [
            'attempts' => 1,
            'first_attempt' => time()
        ];
        return true;
    }
    
    // Check if exceeded max attempts
    if ($data['attempts'] >= $maxAttempts) {
        return false;
    }
    
    // Increment attempts
    $_SESSION[$key]['attempts']++;
    return true;
}

/**
 * Get remaining rate limit time
 */
function getRateLimitWaitTime($action, $timeWindow = 300) {
    $key = 'rate_limit_' . $action . '_' . getClientIP();
    
    if (!isset($_SESSION[$key])) {
        return 0;
    }
    
    $data = $_SESSION[$key];
    $timePassed = time() - $data['first_attempt'];
    $remaining = $timeWindow - $timePassed;
    
    return max(0, $remaining);
}

// ============================================================================
// XSS PROTECTION
// ============================================================================

/**
 * Clean HTML content (for rich text editors)
 */
function cleanHTML($html) {
    // Allow only safe HTML tags
    $allowed_tags = '<p><br><strong><em><u><h1><h2><h3><h4><ul><ol><li><a>';
    return strip_tags($html, $allowed_tags);
}

// ============================================================================
// SQL INJECTION PROTECTION
// ============================================================================

/**
 * Validate table name (prevent SQL injection in dynamic queries)
 */
function isValidTableName($tableName) {
    // Only allow alphanumeric and underscore
    return preg_match('/^[a-zA-Z0-9_]+$/', $tableName);
}

/**
 * Validate column name (prevent SQL injection in dynamic queries)
 */
function isValidColumnName($columnName) {
    // Only allow alphanumeric and underscore
    return preg_match('/^[a-zA-Z0-9_]+$/', $columnName);
}

// ============================================================================
// FILE UPLOAD SECURITY
// ============================================================================

/**
 * Validate image file
 */
function isValidImage($file) {
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/x-webp'];
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    
    // Check MIME type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    
    if (!in_array($mimeType, $allowedTypes)) {
        return false;
    }
    
    // Check extension
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, $allowedExtensions)) {
        return false;
    }
    
    // Verify it's actually an image
    $imageInfo = getimagesize($file['tmp_name']);
    if ($imageInfo === false) {
        return false;
    }
    
    return true;
}

/**
 * Upload image with validation
 */
function uploadImage($file, $maxSize = 2097152) { // 2MB default
    if (!isset($file) || !isset($file['error'])) {
        return ['success' => false, 'message' => 'No file uploaded or upload error'];
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $uploadErrorMessages = [
            UPLOAD_ERR_INI_SIZE   => 'The uploaded file exceeds the server upload_max_filesize limit.',
            UPLOAD_ERR_FORM_SIZE  => 'The uploaded file exceeds the form MAX_FILE_SIZE limit.',
            UPLOAD_ERR_PARTIAL    => 'The file was only partially uploaded. Please try again.',
            UPLOAD_ERR_NO_FILE    => 'No file was uploaded.',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary upload directory on server.',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write uploaded file to disk.',
            UPLOAD_ERR_EXTENSION  => 'A server extension stopped the file upload.'
        ];
        return ['success' => false, 'message' => $uploadErrorMessages[$file['error']] ?? 'File upload error'];
    }

    if (empty($file['tmp_name']) || !is_string($file['tmp_name']) || !is_file($file['tmp_name']) || !is_readable($file['tmp_name'])) {
        return ['success' => false, 'message' => 'Invalid temporary upload file'];
    }
    
    // Check file size
    if ($file['size'] > $maxSize) {
        return ['success' => false, 'message' => 'Image size exceeds maximum allowed (2MB)'];
    }
    
    // Validate image
    if (!isValidImage($file)) {
        return ['success' => false, 'message' => 'Invalid image file'];
    }
    
    // Generate unique filename
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $filename = uniqid() . '_' . time() . '.' . $extension;
    if (function_exists('getUploadFilePath')) {
        $destination = getUploadFilePath($filename);
    } else {
        $destination = rtrim(UPLOAD_PATH, '/') . '/' . $filename;
    }

    if ($destination === false || !is_dir(dirname($destination))) {
        return ['success' => false, 'message' => 'Upload directory is not writable'];
    }

    if (!is_writable(dirname($destination))) {
        return ['success' => false, 'message' => 'Upload directory is not writable'];
    }
    
    // Move uploaded file
    $moved = false;
    if (is_uploaded_file($file['tmp_name'])) {
        $moved = move_uploaded_file($file['tmp_name'], $destination);
    } else {
        $moved = @copy($file['tmp_name'], $destination);
    }

    if ($moved) {
        @chmod($destination, 0644);
        $resolvedUrl = function_exists('getScopedUploadUrl')
            ? getScopedUploadUrl($filename)
            : rtrim(UPLOAD_URL, '/') . '/' . rawurlencode($filename);

        return [
            'success' => true,
            'filename' => $filename,
            'path' => $destination,
            'url' => $resolvedUrl
        ];
    }
    
    return ['success' => false, 'message' => 'Failed to move uploaded file'];
}

}

if (!function_exists('setSecurityHeaders')) {

// ============================================================================
// SECURITY HEADERS
// ============================================================================

/**
 * Set security headers
 */
function setSecurityHeaders() {
    // Prevent clickjacking
    header('X-Frame-Options: SAMEORIGIN');
    
    // XSS Protection
    header('X-XSS-Protection: 1; mode=block');
    
    // Prevent MIME type sniffing
    header('X-Content-Type-Options: nosniff');
    
    // Referrer Policy
    header('Referrer-Policy: strict-origin-when-cross-origin');
    
    // Content Security Policy: keep scripts/styles restricted, but allow data/blob images for local previews.
    header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; img-src 'self' data: blob: https:; font-src 'self' data: https://cdn.jsdelivr.net https://cdnjs.cloudflare.com;");
}

// Apply security headers
setSecurityHeaders();

}
