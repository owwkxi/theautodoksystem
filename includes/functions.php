<?php

if (!function_exists('sanitizeFilename')) {
    require_once __DIR__ . '/security.php';
}

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

function sanitizeTextValue($value, $default = '') {
    if (is_array($value)) {
        return $default;
    }

    $text = trim((string)$value);
    $text = strip_tags($text);
    $text = preg_replace('/[\x00-\x1F\x7F]/', '', $text);
    $text = str_replace(["\r", "\n", "\t"], ' ', $text);
    $text = preg_replace('/\s{2,}/', ' ', $text);

    return $text === '' ? $default : $text;
}

function ensureUploadDirectoryWritable() {
    if (!is_dir(UPLOAD_PATH)) {
        if (!@mkdir(UPLOAD_PATH, 0755, true) && !is_dir(UPLOAD_PATH)) {
            return false;
        }
    }

    if (!is_writable(UPLOAD_PATH)) {
        @chmod(UPLOAD_PATH, 0777);
    }

    return is_writable(UPLOAD_PATH);
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

function appUrl($path = '') {
    $base = rtrim(APP_URL, '/');
    $cleanPath = ltrim((string)$path, '/');
    return $cleanPath === '' ? $base : ($base . '/' . $cleanPath);
}

function routeUrl($route, array $query = []) {
    $routes = [
        'home' => '',
        'login' => 'login',
        'logout' => 'logout',
        'maintenance' => 'maintenance',
        'dashboard' => 'dashboard',
        'services' => 'services',
        'reports' => 'reports',
        'inventory' => 'inventory',
        'staff' => 'staff',
        'settings' => 'settings',
        'settings_print_template' => 'settings/print-template',
        'settings_system_logo' => 'settings/system-logo',
        'settings_role_permissions' => 'settings/role-permissions',
        'settings_announcement' => 'settings/announcement',
        'settings_completion_email' => 'settings/completion-email',
        'settings_deleted_archive' => 'settings/deleted-archive',
        'settings_notes' => 'settings/notes',
        'attendance' => 'attendance',
        'attendance_nfc' => 'attendance/nfc',
        'profile' => 'profile',
        'job_orders' => 'job-orders',
        'job_orders_create' => 'job-orders/create',
    ];

    $path = $routes[$route] ?? trim((string)$route, '/');
    $url = appUrl($path);

    if (!empty($query)) {
        $queryString = http_build_query($query);
        if ($queryString !== '') {
            $url .= '?' . $queryString;
        }
    }

    return $url;
}

function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function normalizeRole($role) {
    $normalized = strtolower(trim((string)$role));
    $aliases = [
        'system_administrator' => 'admin',
        'system_admin' => 'admin',
        'administrator' => 'admin',
    ];
    return $aliases[$normalized] ?? $normalized;
}

function hasRole($role) {
    if (!isset($_SESSION['user_role'])) {
        return false;
    }
    return normalizeRole($_SESSION['user_role']) === normalizeRole($role);
}

function hasAnyRole($roles) {
    if (!isset($_SESSION['user_role'])) {
        return false;
    }
    $currentRole = normalizeRole($_SESSION['user_role']);
    foreach ((array)$roles as $role) {
        if ($currentRole === normalizeRole($role)) {
            return true;
        }
    }
    return false;
}

function requireLogin() {
    if (!isLoggedIn()) {
        redirect(routeUrl('login'));
    }
}

function requireRole($role) {
    requireLogin();
    if (!hasRole($role)) {
        redirect(routeUrl('dashboard'));
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
        'address_line' => '123 Sample Street, City, Philippines',
        'tax_info' => 'Non VAT Reg. ; TIN 652-842-009-00000',
        'logo_url' => APP_URL . '/assets/images/logo.png',
        'footer_note' => 'Thank you for choosing The Autodok - Automotive Care Services',
        'terms_conditions' => 'All services rendered are subject to warranty as per company policy. The client agrees to the estimated cost and any additional charges incurred during the repair process. Payment is due upon completion unless otherwise arranged.',
        'header_template' => '<table style="width:100%;border-collapse:collapse;margin-bottom:8px;"><tr><td style="width:110px;vertical-align:middle;padding-right:12px;"><img src="{{logo_url}}" style="width:100px;height:100px;object-fit:contain;" alt="Logo"></td><td style="vertical-align:middle;"><div style="font-size:17pt;font-weight:700;letter-spacing:2px;line-height:1.1;">{{company_name}}</div><div style="font-size:9pt;color:#444;">{{company_subtitle}}</div><div style="font-size:8.5pt;color:#666;">{{contact_line}}</div><div style="font-size:8.5pt;color:#666;">{{address_line}}</div><div style="font-size:8.5pt;color:#666;">{{tax_info}}</div></td><td style="text-align:right;vertical-align:middle;font-size:9pt;"><div style="font-size:12pt;font-weight:700;">{{document_title}}</div><div style="color:#555;"># {{document_number}}</div><div style="margin-top:4px;"><strong>Date:</strong> {{document_date}}</div></td></tr></table><hr style="border:none;border-top:1.5px solid #333;margin-bottom:10px;">',
        'footer_template' => '<div style="text-align:center;font-size:8pt;color:#999;margin-top:10px;border-top:1px solid #ddd;padding-top:5px;">{{footer_note}}</div>'
    ];
}

function getDefaultSystemBrandingSettings() {
    $activeShop = getActiveShopOption();
    $defaultName = $activeShop['name'] ?? APP_NAME;
    $defaultSubtitle = (($activeShop['key'] ?? '') === 'autodok_prime')
        ? 'Prime Automotive Care Services'
        : 'Automotive Care Services';

    return [
        'system_logo_url' => APP_URL . '/assets/images/logo.png',
        'sidebar_brand_name' => $defaultName,
        'sidebar_brand_subtitle' => $defaultSubtitle,
    ];
}

function getActiveShopOption($shopKey = null) {
    if (function_exists('resolveShopOption')) {
        $candidate = $shopKey;
        if ($candidate === null || $candidate === '') {
            $candidate = $_SESSION['shop_key'] ?? '';
        }
        return resolveShopOption($candidate);
    }

    return [
        'key' => 'default',
        'name' => APP_NAME,
        'db_name' => DB_NAME,
    ];
}

function getScopedSettingsFilePath($baseFilename, $shopKey = null) {
    $shop = getActiveShopOption($shopKey);
    $shopKey = strtolower((string)($shop['key'] ?? 'default'));
    $shopKey = preg_replace('/[^a-z0-9_-]/', '_', $shopKey);
    if ($shopKey === '' || $shopKey === null) {
        $shopKey = 'default';
    }

    return UPLOAD_PATH . $baseFilename . '_' . $shopKey . '.json';
}

function getUserNotesStorageFilePath($userId = null) {
    $userId = $userId ?? ($_SESSION['user_id'] ?? ($_SESSION['username'] ?? 'guest'));
    $safeValue = preg_replace('/[^A-Za-z0-9_-]/', '_', (string)$userId);
    $safeValue = trim($safeValue);
    if ($safeValue === '') {
        $safeValue = 'guest';
    }

    return UPLOAD_PATH . 'user_notes_' . $safeValue . '.json';
}

function normalizePrivateUserNote($entry, $ownerId) {
    if (!is_array($entry)) {
        return null;
    }

    $createdAt = (string)($entry['created_at'] ?? date('Y-m-d H:i:s'));
    $updatedAt = (string)($entry['updated_at'] ?? $createdAt);
    $legacyImageUrl = (string)($entry['image_url'] ?? '');
    $imageUrls = is_array($entry['image_urls'] ?? null) ? $entry['image_urls'] : [];
    if ($legacyImageUrl !== '' && !in_array($legacyImageUrl, $imageUrls, true)) {
        $imageUrls[] = $legacyImageUrl;
    }

    $sharedWith = is_array($entry['shared_with'] ?? null)
        ? array_values(array_unique(array_map('intval', $entry['shared_with'])))
        : [];

    return [
        'id' => (string)($entry['id'] ?? uniqid('note_', true)),
        'owner_id' => (int)($entry['owner_id'] ?? $ownerId),
        'title' => trim((string)($entry['title'] ?? 'Untitled Note')) !== '' ? trim((string)($entry['title'] ?? 'Untitled Note')) : 'Untitled Note',
        'content' => (string)($entry['content'] ?? ''),
        'image_urls' => array_values(array_filter(array_map('strval', $imageUrls))),
        'shared_with' => $sharedWith,
        'created_at' => $createdAt,
        'updated_at' => $updatedAt,
    ];
}

function getOwnedUserNotes($userId = null) {
    $userId = $userId ?? ($_SESSION['user_id'] ?? 0);
    $filePath = getUserNotesStorageFilePath($userId);
    if (!file_exists($filePath)) {
        return [];
    }

    $raw = @file_get_contents($filePath);
    if ($raw === false || trim($raw) === '') {
        return [];
    }

    $decoded = json_decode($raw, true);
    if (!is_array($decoded)) {
        return [];
    }

    $notes = [];
    foreach ($decoded as $entry) {
        $normalized = normalizePrivateUserNote($entry, $userId);
        if ($normalized !== null && $normalized['owner_id'] === (int)$userId) {
            $notes[] = $normalized;
        }
    }

    usort($notes, function ($a, $b) {
        return strtotime((string)$b['updated_at']) - strtotime((string)$a['updated_at']);
    });

    return $notes;
}

function getPrivateUserNotes($userId = null) {
    $userId = $userId ?? ($_SESSION['user_id'] ?? 0);
    $notes = getOwnedUserNotes($userId);
    $knownIds = [];

    foreach ($notes as $note) {
        $knownIds[$note['owner_id'] . ':' . $note['id']] = true;
    }

    foreach (glob(UPLOAD_PATH . 'user_notes_*.json') ?: [] as $filePath) {
        if (basename($filePath) === basename(getUserNotesStorageFilePath($userId))) {
            continue;
        }
        if (!preg_match('/user_notes_([A-Za-z0-9_-]+)\.json$/', basename($filePath), $matches)) {
            continue;
        }
        $ownerId = (int)$matches[1];
        $raw = @file_get_contents($filePath);
        $decoded = $raw !== false ? json_decode($raw, true) : null;
        foreach (is_array($decoded) ? $decoded : [] as $entry) {
            $normalized = normalizePrivateUserNote($entry, $ownerId);
            if ($normalized === null || !in_array((int)$userId, $normalized['shared_with'], true)) {
                continue;
            }
            $key = $normalized['owner_id'] . ':' . $normalized['id'];
            if (!isset($knownIds[$key])) {
                $notes[] = $normalized;
                $knownIds[$key] = true;
            }
        }
    }

    usort($notes, function ($a, $b) {
        return strtotime((string)$b['updated_at']) - strtotime((string)$a['updated_at']);
    });

    return $notes;
}

function getPrivateUserNoteOwnerId($noteId, $viewerId = null) {
    $noteId = (string)$noteId;
    $viewerId = (int)($viewerId ?? ($_SESSION['user_id'] ?? 0));

    foreach (getOwnedUserNotes($viewerId) as $note) {
        if ((string)$note['id'] === $noteId) {
            return $viewerId;
        }
    }

    foreach (glob(UPLOAD_PATH . 'user_notes_*.json') ?: [] as $filePath) {
        if (!preg_match('/user_notes_([A-Za-z0-9_-]+)\.json$/', basename($filePath), $matches)) {
            continue;
        }
        $ownerId = (int)$matches[1];
        if ($ownerId <= 0 || $ownerId === $viewerId) {
            continue;
        }
        $raw = @file_get_contents($filePath);
        $decoded = $raw !== false ? json_decode($raw, true) : null;
        foreach (is_array($decoded) ? $decoded : [] as $entry) {
            $normalized = normalizePrivateUserNote($entry, $ownerId);
            if ($normalized !== null
                && (string)$normalized['id'] === $noteId
                && in_array($viewerId, $normalized['shared_with'], true)
            ) {
                return $ownerId;
            }
        }
    }

    return 0;
}

function getUsersWithNoteAccess($excludeUserId = null) {
    $db = Database::getInstance();
    $users = [];
    $rows = array_merge(
        $db->fetchAll("SELECT id, full_name, username, role FROM staff WHERE status='active'"),
        $db->fetchAll("SELECT id, full_name, username, role FROM users WHERE status='active'")
    );

    foreach ($rows as $row) {
        $id = (int)($row['id'] ?? 0);
        if ($id <= 0 || ($excludeUserId !== null && $id === (int)$excludeUserId)) {
            continue;
        }
        $role = (string)($row['role'] ?? '');
        if (!in_array($role, ['admin', 'cashier', 'service_adviser', 'stockman'], true)) {
            continue;
        }
        $users[$id] = [
            'id' => $id,
            'name' => trim((string)($row['full_name'] ?? $row['username'] ?? 'User')),
            'role' => $role,
            'has_notes' => !empty(getOwnedUserNotes($id)),
        ];
    }

    uasort($users, static function ($a, $b) {
        return strcasecmp($a['name'], $b['name']);
    });
    return array_values($users);
}

function getUsersWithNotes($excludeUserId = null) {
    return array_values(array_filter(
        getUsersWithNoteAccess($excludeUserId),
        static function ($user) {
            return !empty($user['has_notes']);
        }
    ));
}

function savePrivateUserNotes($notes, $userId = null) {
    if (!function_exists('ensureUploadDirectoryWritable') || !ensureUploadDirectoryWritable()) {
        return false;
    }

    $filePath = getUserNotesStorageFilePath($userId);
    $normalized = [];

    foreach ((array)$notes as $entry) {
        if (!is_array($entry)) {
            continue;
        }

        $legacyImageUrl = (string)($entry['image_url'] ?? '');
        $imageUrls = is_array($entry['image_urls'] ?? null) ? $entry['image_urls'] : [];
        if ($legacyImageUrl !== '' && !in_array($legacyImageUrl, $imageUrls, true)) {
            $imageUrls[] = $legacyImageUrl;
        }
        $normalized[] = [
            'id' => (string)($entry['id'] ?? uniqid('note_', true)),
            'owner_id' => (int)($entry['owner_id'] ?? ($userId ?? ($_SESSION['user_id'] ?? 0))),
            'title' => trim((string)($entry['title'] ?? 'Untitled Note')) !== '' ? trim((string)($entry['title'] ?? 'Untitled Note')) : 'Untitled Note',
            'content' => (string)($entry['content'] ?? ''),
            'image_urls' => array_values(array_filter(array_map('strval', $imageUrls))),
            'shared_with' => is_array($entry['shared_with'] ?? null) ? array_values(array_unique(array_map('intval', $entry['shared_with']))) : [],
            'created_at' => (string)($entry['created_at'] ?? date('Y-m-d H:i:s')),
            'updated_at' => (string)($entry['updated_at'] ?? ($entry['created_at'] ?? date('Y-m-d H:i:s'))),
        ];
    }

    usort($normalized, function ($a, $b) {
        return strtotime((string)$b['updated_at']) - strtotime((string)$a['updated_at']);
    });

    $payload = json_encode($normalized, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($payload === false) {
        return false;
    }

    $temporaryPath = $filePath . '.tmp.' . bin2hex(random_bytes(8));
    if (@file_put_contents($temporaryPath, $payload, LOCK_EX) === false) {
        return false;
    }

    if (!@rename($temporaryPath, $filePath)) {
        @unlink($temporaryPath);
        return false;
    }

    @chmod($filePath, 0666);
    return true;
}

function getCompletionEmailCompanyDetails($settings = null) {
    $printSettings = getPrintTemplateSettings();
    $contactLine = trim((string)($printSettings['contact_line'] ?? ''));
    $addressLine = trim((string)($printSettings['address_line'] ?? ''));
    $companyName = trim((string)(($settings['company_contact'] ?? '') ?: (SMTP_FROM_NAME ?: ($printSettings['company_name'] ?? APP_NAME)))) ?: APP_NAME;

    $phone = trim((string)($settings['company_contact_number'] ?? ''));
    if ($phone === '') {
        if (preg_match('/(?:tel|phone|mobile|contact)[:\s]*([^|\r\n]+)/i', $contactLine, $phoneMatch)) {
            $phone = trim($phoneMatch[1]);
        }
        if ($phone === '' && preg_match('/\+?[0-9()\s.-]{7,}/', $contactLine, $phoneMatch)) {
            $phone = trim($phoneMatch[0]);
        }
    }

    $email = trim((string)($settings['company_email'] ?? ''));
    if ($email === '') {
        if (preg_match('/[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}/i', $contactLine, $emailMatch)) {
            $email = trim($emailMatch[0]);
        }
        if ($email === '') {
            $email = trim((string)SMTP_FROM);
        }
    }

    $address = trim((string)($settings['company_address'] ?? ''));
    if ($address === '') {
        $address = $addressLine !== '' ? $addressLine : 'N/A';
    }

    return [
        'company_contact' => $companyName,
        'company_contact_number' => $phone !== '' ? $phone : 'N/A',
        'company_email' => $email !== '' ? $email : 'N/A',
        'company_address' => $address !== '' ? $address : 'N/A',
    ];
}

function appendCompletionEmailCompanyDetails($message, $companyDetails = []) {
    $message = trim((string)$message);
    $details = [
        'company_contact' => (string)($companyDetails['company_contact'] ?? ''),
        'company_contact_number' => (string)($companyDetails['company_contact_number'] ?? ''),
        'company_email' => (string)($companyDetails['company_email'] ?? ''),
        'company_address' => (string)($companyDetails['company_address'] ?? ''),
    ];

    $hasFooter = stripos($message, 'Company Contact:') !== false || stripos($message, '{company_contact}') !== false;
    if ($hasFooter || $message === '') {
        return $message;
    }

    $footer = '<div style="border-top:1px solid #dfe4ea; padding-top:12px; margin-top:12px;">'
        . '<strong>Company Contact:</strong> ' . $details['company_contact'] . '<br>'
        . '<strong>Contact Number:</strong> ' . $details['company_contact_number'] . '<br>'
        . '<strong>Email:</strong> ' . $details['company_email'] . '<br>'
        . '<strong>Address:</strong> ' . $details['company_address']
        . '</div>';

    $message = rtrim($message, " \t\n\r<>");
    if ($message !== '') {
        $message .= '<br><br>';
    }

    return $message . $footer;
}

function getDefaultCompletionEmailSettings() {
    $fallbackCompany = getCompletionEmailCompanyDetails();
    return [
        'subject' => 'Job Order #{job_order_number} completed',
        'message' => "Dear {customer_name},<br><br>We are pleased to inform you that your vehicle, <strong>{vehicle}</strong>, under Job Order <strong>#{job_order_number}</strong>, has been completed and is now <strong style=\"color:#198754;\">ready for release</strong>.<br><br><strong>Total Amount:</strong> PHP {total_amount}<br><br>Please visit our service center at your convenience to proceed with the vehicle release. If you have any questions or concerns regarding the completed service, please feel free to contact us.<br><br>Thank you for choosing <strong>{company_name}</strong>. We appreciate your trust and continued support.<br><br><em>This is an automated message. Please do not reply directly to this email.</em>",
        'company_contact' => $fallbackCompany['company_contact'],
        'company_contact_number' => $fallbackCompany['company_contact_number'],
        'company_email' => $fallbackCompany['company_email'],
        'company_address' => $fallbackCompany['company_address'],
    ];
}

function sanitizeCompletionEmailHtml($html) {
    $html = trim((string)$html);
    if ($html === '') {
        return '';
    }

    // Convert legacy plain-text/Markdown templates before sanitizing formatted HTML.
    if (strpos($html, '<') === false) {
        $html = htmlspecialchars($html, ENT_QUOTES, 'UTF-8');
        $html = preg_replace('/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $html);
        $html = preg_replace('/(?<!\*)\*([^*]+)\*(?!\*)/', '<em>$1</em>', $html);
        return nl2br($html);
    }

    // Some browsers emit <font color="..."> for execCommand('foreColor').
    $safeColor = '(#[0-9a-f]{3,8}|[a-z]+|rgb\(\s*\d{1,3}\s*,\s*\d{1,3}\s*,\s*\d{1,3}\s*\))';
    $html = preg_replace_callback('/<font\b([^>]*)>/i', static function ($match) use ($safeColor) {
        preg_match('/\bcolor\s*=\s*(["\'])' . $safeColor . '\1/i', $match[1], $colorMatch);
        return !empty($colorMatch[2])
            ? '<span style="color:' . strtolower($colorMatch[2]) . ';">'
            : '<span>';
    }, $html);
    $html = preg_replace('/<\/font>/i', '</span>', $html);
    $html = strip_tags($html, '<br><p><div><strong><b><em><i><u><span>');
    $html = preg_replace('/\s+on[a-z]+\s*=\s*(["\']).*?\1/iu', '', $html);
    $html = preg_replace_callback('/<([a-z]+)([^>]*)>/i', static function ($match) use ($safeColor) {
        $tag = strtolower($match[1]);
        $attributes = $match[2];
        preg_match('/style\s*=\s*(["\'])(.*?)\1/i', $attributes, $styleMatch);
        $style = $styleMatch[2] ?? '';
        preg_match('/(?:^|;)\s*color\s*:\s*' . $safeColor . '\s*(?:;|$)/i', $style, $colorMatch);
        return !empty($colorMatch[1])
            ? '<' . $tag . ' style="color:' . strtolower($colorMatch[1]) . ';">'
            : '<' . $tag . '>';
    }, $html);

    return $html;
}

function getCompletionEmailSettings($shopKey = null) {
    $defaults = getDefaultCompletionEmailSettings();
    $filePath = getScopedSettingsFilePath('completion_email_settings', $shopKey);
    if (!file_exists($filePath)) {
        return $defaults;
    }

    $decoded = json_decode((string)file_get_contents($filePath), true);
    return is_array($decoded) ? array_merge($defaults, $decoded) : $defaults;
}

function saveCompletionEmailSettings($settings, $shopKey = null) {
    $defaults = getDefaultCompletionEmailSettings();
    $values = array_merge($defaults, (array)$settings);
    $filePath = getScopedSettingsFilePath('completion_email_settings', $shopKey);
    if (!ensureUploadDirectoryWritable()) {
        return false;
    }

    $json = json_encode([
        'subject' => sanitizeTextValue($values['subject'], $defaults['subject']),
        'message' => sanitizeCompletionEmailHtml($values['message']) ?: $defaults['message'],
        'company_contact' => sanitizeTextValue($values['company_contact'] ?? $defaults['company_contact'], $defaults['company_contact']),
        'company_contact_number' => sanitizeTextValue($values['company_contact_number'] ?? $defaults['company_contact_number'], $defaults['company_contact_number']),
        'company_email' => sanitizeTextValue($values['company_email'] ?? $defaults['company_email'], $defaults['company_email']),
        'company_address' => sanitizeTextValue($values['company_address'] ?? $defaults['company_address'], $defaults['company_address']),
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    return $json !== false && file_put_contents($filePath, $json, LOCK_EX) !== false;
}

function getScopedUploadDirectoryPath($shopKey = null) {
    $shop = getActiveShopOption($shopKey);
    $resolvedShopKey = strtolower((string)($shop['key'] ?? 'default'));
    $resolvedShopKey = preg_replace('/[^a-z0-9_-]/', '_', $resolvedShopKey);
    if ($resolvedShopKey === '' || $resolvedShopKey === null) {
        $resolvedShopKey = 'default';
    }

    $dir = rtrim(UPLOAD_PATH, '/') . '/' . $resolvedShopKey;
    if (!is_dir($dir)) {
        @mkdir($dir, 0775, true);
    }

    if (is_dir($dir) && !is_writable($dir)) {
        @chmod($dir, 0777);
    }

    if (!is_dir($dir) || !is_writable($dir)) {
        return rtrim(UPLOAD_PATH, '/');
    }

    return $dir;
}

function getUploadFilePath($filename, $shopKey = null) {
    $safeFilename = sanitizeFilename((string)$filename);
    $basePath = getScopedUploadDirectoryPath($shopKey);
    return rtrim($basePath, '/') . '/' . $safeFilename;
}

function getScopedUploadUrl($filename, $shopKey = null) {
    $safeFilename = rawurlencode(sanitizeFilename((string)$filename));
    $shop = getActiveShopOption($shopKey);
    $resolvedShopKey = strtolower((string)($shop['key'] ?? 'default'));
    $resolvedShopKey = preg_replace('/[^a-z0-9_-]/', '_', $resolvedShopKey);
    if ($resolvedShopKey === '' || $resolvedShopKey === null) {
        $resolvedShopKey = 'default';
    }

    return rtrim(UPLOAD_URL, '/') . '/' . rawurlencode($resolvedShopKey) . '/' . $safeFilename;
}

function getSystemBrandingSettingsFilePath($shopKey = null) {
    return getScopedSettingsFilePath('system_branding_settings', $shopKey);
}

function getLegacySystemBrandingSettingsFilePath() {
    return UPLOAD_PATH . 'system_branding_settings.json';
}

function getSystemBrandingSettings($shopKey = null) {
    $defaults = getDefaultSystemBrandingSettings();
    $filePath = getSystemBrandingSettingsFilePath($shopKey);

    if (!file_exists($filePath)) {
        $legacyPath = getLegacySystemBrandingSettingsFilePath();
        if ($legacyPath !== $filePath && file_exists($legacyPath)) {
            $filePath = $legacyPath;
        }
    }

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

function saveSystemBrandingSettings($settings) {
    $defaults = getDefaultSystemBrandingSettings();
    $merged = array_merge($defaults, (array)$settings);

    $normalized = [];
    foreach ($defaults as $key => $defaultValue) {
        $value = sanitizeTextValue($merged[$key] ?? $defaultValue, $defaultValue);
        $normalized[$key] = $value === '' ? $defaultValue : $value;
    }

    if (!ensureUploadDirectoryWritable()) {
        return false;
    }

    $filePath = getSystemBrandingSettingsFilePath();
    $json = json_encode($normalized, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($json === false) {
        return false;
    }

    if (file_exists($filePath) && !is_writable($filePath)) {
        @unlink($filePath);
    }

    $tmpPath = $filePath . '.tmp';
    if (file_put_contents($tmpPath, $json, LOCK_EX) === false) {
        return false;
    }

    @chmod($tmpPath, 0664);
    if (!@rename($tmpPath, $filePath)) {
        @unlink($tmpPath);
        return false;
    }

    return true;
}

function getPrintTemplateSettingsFilePath($shopKey = null) {
    return getScopedSettingsFilePath('print_template_settings', $shopKey);
}

function getLegacyPrintTemplateSettingsFilePath() {
    return UPLOAD_PATH . 'print_template_settings.json';
}

function getPrintTemplateSettings($shopKey = null) {
    $defaults = getDefaultPrintTemplateSettings();
    $filePath = getPrintTemplateSettingsFilePath($shopKey);

    if (!file_exists($filePath)) {
        $legacyPath = getLegacyPrintTemplateSettingsFilePath();
        if ($legacyPath !== $filePath && file_exists($legacyPath)) {
            $filePath = $legacyPath;
        }
    }

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

    $merged = array_merge($defaults, $decoded);

    // Inject {{address_line}} into saved templates that predate this field
    if (!empty($merged['header_template']) && strpos($merged['header_template'], '{{address_line}}') === false) {
        $merged['header_template'] = str_replace(
            '{{contact_line}}</div></td>',
            '{{contact_line}}</div><div style="font-size:8.5pt;color:#666;">{{address_line}}</div></td>',
            $merged['header_template']
        );
    }

    // Inject {{tax_info}} into saved templates that predate this field
    if (!empty($merged['header_template']) && strpos($merged['header_template'], '{{tax_info}}') === false) {
        $merged['header_template'] = str_replace(
            '{{address_line}}</div></td>',
            '{{address_line}}</div><div style="font-size:8.5pt;color:#666;">{{tax_info}}</div></td>',
            $merged['header_template']
        );
    }

    // Force tax_info style to match address_line (remove any bold/weight)
    if (!empty($merged['header_template']) && strpos($merged['header_template'], '{{tax_info}}') !== false) {
        $merged['header_template'] = preg_replace(
            '/<div style="[^"]*">(\{\{tax_info\}\})<\/div>/',
            '<div style="font-size:8.5pt;color:#666;">{{tax_info}}</div>',
            $merged['header_template']
        );
    }

    // Force logo size to 100x100px regardless of saved template
    if (!empty($merged['header_template'])) {
        $merged['header_template'] = preg_replace(
            '/width:\s*\d+px;\s*height:\s*\d+px;\s*object-fit:\s*contain;/',
            'width:100px;height:100px;object-fit:contain;',
            $merged['header_template']
        );
        // Fix container td width
        $merged['header_template'] = preg_replace(
            '/width:\s*\d+px;\s*vertical-align:\s*(middle|top);\s*padding-right:\s*12px;(padding-top:\s*\d+px;)?/',
            'width:110px;vertical-align:middle;padding-right:12px;',
            $merged['header_template']
        );
        // Ensure all cells use vertical-align:middle
        $merged['header_template'] = preg_replace('/vertical-align:\s*top;(padding-top:\s*\d+px;)?/', 'vertical-align:middle;', $merged['header_template']);
    }

    return $merged;
}

function savePrintTemplateSettings($settings) {
    $defaults = getDefaultPrintTemplateSettings();
    $merged = array_merge($defaults, (array)$settings);

    $normalized = [];
    foreach ($defaults as $key => $defaultValue) {
        $rawValue = $merged[$key] ?? $defaultValue;
        if (in_array($key, ['header_template', 'footer_template'], true)) {
            $normalized[$key] = trim((string)$rawValue);
            if ($normalized[$key] === '') {
                $normalized[$key] = $defaultValue;
            }
        } else {
            $value = sanitizeTextValue($rawValue, $defaultValue);
            $normalized[$key] = $value === '' ? $defaultValue : $value;
        }
    }

    if (!ensureUploadDirectoryWritable()) {
        return false;
    }

    $filePath = getPrintTemplateSettingsFilePath();
    $json = json_encode($normalized, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($json === false) {
        return false;
    }

    // If the file exists but is not writable (common when ownership changed),
    // write a temp file and replace the target atomically.
    if (file_exists($filePath) && !is_writable($filePath)) {
        @unlink($filePath);
    }

    $tmpPath = $filePath . '.tmp';
    if (file_put_contents($tmpPath, $json, LOCK_EX) === false) {
        return false;
    }

    @chmod($tmpPath, 0664);
    if (!@rename($tmpPath, $filePath)) {
        @unlink($tmpPath);
        return false;
    }

    return true;
}

function normalizeReportShopKey($shopKey) {
    $normalizedKey = strtolower(trim((string)$shopKey));
    $normalizedKey = preg_replace('/[^a-z0-9_-]/', '_', $normalizedKey);
    if ($normalizedKey === '' || $normalizedKey === null) {
        return 'default';
    }
    return $normalizedKey;
}

function getCurrentReportShopKey() {
    $sessionDbName = '';
    if (session_status() === PHP_SESSION_ACTIVE) {
        $sessionDbName = trim((string)($_SESSION['shop_db_name'] ?? ''));
    }

    if ($sessionDbName !== '' && function_exists('getShopOptions')) {
        foreach (getShopOptions() as $shopKey => $shopOption) {
            if (($shopOption['db_name'] ?? '') === $sessionDbName) {
                return normalizeReportShopKey((string)$shopKey);
            }
        }
    }

    $shop = getActiveShopOption();
    return normalizeReportShopKey($shop['key'] ?? getLegacyReportShopKey());
}

function getKnownReportShopKeys() {
    static $knownShopKeys = null;
    if ($knownShopKeys !== null) {
        return $knownShopKeys;
    }

    $knownShopKeys = [];
    $shopOptions = function_exists('getShopOptions') ? getShopOptions() : [];
    foreach ($shopOptions as $shopKey => $shopOption) {
        $knownShopKeys[] = normalizeReportShopKey((string)$shopKey);
    }

    if (empty($knownShopKeys)) {
        $knownShopKeys[] = 'default';
    }

    return array_values(array_unique($knownShopKeys));
}

function getLegacyReportShopKey() {
    static $legacyShopKey = null;
    if ($legacyShopKey !== null) {
        return $legacyShopKey;
    }

    if (function_exists('resolveShopOption')) {
        $legacyShop = resolveShopOption('');
        $legacyShopKey = normalizeReportShopKey($legacyShop['key'] ?? 'default');
        return $legacyShopKey;
    }

    $legacyShopKey = 'default';
    return $legacyShopKey;
}

function resolveReportShopKey($shopKey, $fallbackShopKey = null) {
    $normalized = normalizeReportShopKey($shopKey);
    $knownShopKeys = getKnownReportShopKeys();
    if (in_array($normalized, $knownShopKeys, true)) {
        return $normalized;
    }

    $fallback = normalizeReportShopKey($fallbackShopKey ?? getLegacyReportShopKey());
    if (in_array($fallback, $knownShopKeys, true)) {
        return $fallback;
    }

    return $knownShopKeys[0] ?? 'default';
}

function readReportRowsFromFilePath($filePath) {
    if (!file_exists($filePath)) {
        return [];
    }

    $raw = file_get_contents($filePath);
    if ($raw === false || trim($raw) === '') {
        return [];
    }

    $decoded = json_decode($raw, true);
    return is_array($decoded) ? array_values($decoded) : [];
}

function repairReportExpenseJoAssignments($expenses) {
    // Plate numbers can be reused. Preserve the explicit JO link saved with
    // each expense and never infer or change ownership from plate history.
    $rows = array_values((array)$expenses);
    $db = Database::getInstance();
    $jobOrderCreatedAt = [];
    $changed = false;

    foreach ($rows as $index => $expense) {
        if (!is_array($expense)) {
            continue;
        }

        $jobOrderId = (int)($expense['job_order_id'] ?? 0);
        $expenseCreatedAt = trim((string)($expense['created_at'] ?? ''));
        if ($jobOrderId <= 0 || $expenseCreatedAt === '') {
            continue;
        }

        if (!array_key_exists($jobOrderId, $jobOrderCreatedAt)) {
            $jobOrderRow = $db->fetch(
                "SELECT created_at FROM job_orders WHERE id = ? LIMIT 1",
                [$jobOrderId]
            );
            $jobOrderCreatedAt[$jobOrderId] = $jobOrderRow
                ? trim((string)($jobOrderRow['created_at'] ?? ''))
                : null;
        }

        // A JO cannot own an expense recorded before that JO existed. Detach
        // these legacy rows instead of allowing them to appear on the latest JO.
        if (
            !$jobOrderCreatedAt[$jobOrderId]
            || $expenseCreatedAt < $jobOrderCreatedAt[$jobOrderId]
        ) {
            $rows[$index]['job_order_id'] = null;
            $changed = true;
        }
    }

    return $changed ? $rows : (array)$expenses;
}

function mergeReportRowsById($existingRows, $incomingRows) {
    $orderedRows = [];
    $rowsById = [];

    foreach (array_values((array)$existingRows) as $row) {
        if (!is_array($row)) {
            continue;
        }
        $rowId = trim((string)($row['id'] ?? ''));
        if ($rowId === '') {
            $orderedRows[] = $row;
            continue;
        }
        $rowsById[$rowId] = $row;
    }

    foreach (array_values((array)$incomingRows) as $row) {
        if (!is_array($row)) {
            continue;
        }
        $rowId = trim((string)($row['id'] ?? ''));
        if ($rowId === '') {
            $orderedRows[] = $row;
            continue;
        }
        $rowsById[$rowId] = $row;
    }

    return array_values(array_merge($orderedRows, array_values($rowsById)));
}

function getReportExpensesLegacyFilePath() {
    return UPLOAD_PATH . 'report_expenses.json';
}

function getReportExpensesFilePath($shopKey = null) {
    $shop = getActiveShopOption($shopKey);
    $resolvedShopKey = normalizeReportShopKey($shop['key'] ?? 'default');

    return UPLOAD_PATH . 'report_expenses_' . $resolvedShopKey . '.json';
}

function getReportExpenses() {
    $currentShopKey = resolveReportShopKey(getCurrentReportShopKey(), getLegacyReportShopKey());
    $legacyShopKey = resolveReportShopKey(getLegacyReportShopKey(), $currentShopKey);
    $filePath = getReportExpensesFilePath($currentShopKey);
    $legacyFilePath = getReportExpensesLegacyFilePath();

    if (
        !file_exists($filePath)
        && file_exists($legacyFilePath)
        && $currentShopKey === $legacyShopKey
    ) {
        $legacyExpenses = [];
        $legacyRaw = file_get_contents($legacyFilePath);
        if ($legacyRaw !== false && trim($legacyRaw) !== '') {
            $legacyDecoded = json_decode($legacyRaw, true);
            if (is_array($legacyDecoded)) {
                foreach (array_values($legacyDecoded) as $expenseRow) {
                    if (!is_array($expenseRow)) {
                        continue;
                    }
                    $expenseRow['shop_key'] = $currentShopKey;
                    $legacyExpenses[] = $expenseRow;
                }
            }
        }

        if (!empty($legacyExpenses)) {
            saveReportExpenses($legacyExpenses, $currentShopKey);
        }
    }

    if (!file_exists($filePath)) {
        return [];
    }

    $decoded = readReportRowsFromFilePath($filePath);
    if (empty($decoded)) {
        return [];
    }

    $originalDecoded = $decoded;
    $decoded = repairReportExpenseJoAssignments($decoded);
    if ($decoded !== $originalDecoded) {
        saveReportExpenses($decoded, $currentShopKey);
    }

    $normalizedRows = [];
    $rowsByDestinationShop = [];
    $needsRebalancing = false;
    $legacyIds = [];

    if ($currentShopKey !== $legacyShopKey) {
        $legacyBranchRows = readReportRowsFromFilePath(getReportExpensesFilePath($legacyShopKey));
        $legacySharedRows = readReportRowsFromFilePath($legacyFilePath);
        foreach (array_merge($legacyBranchRows, $legacySharedRows) as $legacyRow) {
            if (!is_array($legacyRow)) {
                continue;
            }
            $legacyId = trim((string)($legacyRow['id'] ?? ''));
            if ($legacyId !== '') {
                $legacyIds[$legacyId] = true;
            }
        }
    }

    foreach (array_values($decoded) as $expenseRow) {
        if (!is_array($expenseRow)) {
            continue;
        }

        $rowId = trim((string)($expenseRow['id'] ?? ''));
        $entryShopKey = resolveReportShopKey($expenseRow['shop_key'] ?? '', $currentShopKey);
        if (!isset($expenseRow['shop_key']) || trim((string)$expenseRow['shop_key']) === '') {
            $expenseRow['shop_key'] = $legacyShopKey;
            $needsRebalancing = true;
            $entryShopKey = $legacyShopKey;
        }

        if (
            $currentShopKey !== $legacyShopKey
            && $rowId !== ''
            && isset($legacyIds[$rowId])
            && $entryShopKey !== $legacyShopKey
        ) {
            $entryShopKey = $legacyShopKey;
            $expenseRow['shop_key'] = $legacyShopKey;
            $needsRebalancing = true;
        }

        if ($entryShopKey !== normalizeReportShopKey($expenseRow['shop_key'] ?? '')) {
            $expenseRow['shop_key'] = $entryShopKey;
            $needsRebalancing = true;
        }

        $rowsByDestinationShop[$entryShopKey][] = $expenseRow;
        if ($entryShopKey === $currentShopKey) {
            $normalizedRows[] = $expenseRow;
        } else {
            $needsRebalancing = true;
        }
    }

    if ($needsRebalancing) {
        saveReportExpenses($normalizedRows, $currentShopKey);

        foreach ($rowsByDestinationShop as $destinationShopKey => $rowsForShop) {
            if ($destinationShopKey === $currentShopKey) {
                continue;
            }

            $destinationFilePath = getReportExpensesFilePath($destinationShopKey);
            $destinationExistingRows = readReportRowsFromFilePath($destinationFilePath);
            $mergedRows = mergeReportRowsById($destinationExistingRows, $rowsForShop);
            saveReportExpenses($mergedRows, $destinationShopKey);
        }
    }

    return $normalizedRows;
}

function saveReportExpenses($expenses, $shopKey = null) {
    if (!ensureUploadDirectoryWritable()) {
        return false;
    }

    $filePath = getReportExpensesFilePath($shopKey);
    $json = json_encode(array_values((array)$expenses), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($json === false) {
        return false;
    }

    if (file_exists($filePath) && !is_writable($filePath)) {
        @unlink($filePath);
    }

    $tmpPath = $filePath . '.tmp';
    if (file_put_contents($tmpPath, $json, LOCK_EX) === false) {
        return false;
    }

    @chmod($tmpPath, 0664);
    if (!@rename($tmpPath, $filePath)) {
        @unlink($tmpPath);
        return false;
    }

    return true;
}

function addReportExpense($expenseData) {
    $amount = (float)($expenseData['amount'] ?? 0);
    if ($amount <= 0) {
        return false;
    }

    $expenseDate = (string)($expenseData['expense_date'] ?? date('Y-m-d'));
    $timestamp = strtotime($expenseDate);
    if ($timestamp === false) {
        $expenseDate = date('Y-m-d');
    } else {
        $expenseDate = date('Y-m-d', $timestamp);
    }

    $jobOrderId = null;
    if (isset($expenseData['job_order_id'])) {
        $jobOrderId = trim((string)$expenseData['job_order_id']);
        if ($jobOrderId !== '' && (int)$jobOrderId > 0) {
            $jobOrderId = (int)$jobOrderId;
        } else {
            $jobOrderId = null;
        }
    }

    $plateNumber = trim((string)($expenseData['plate_number'] ?? ''));
    if ($plateNumber !== '') {
        $plateNumber = preg_replace('/\s+/', ' ', $plateNumber);
        $plateNumber = strtoupper($plateNumber);
    }

    $categoryRaw = trim((string)($expenseData['category'] ?? 'General'));
    $category = $categoryRaw !== '' ? $categoryRaw : 'General';
    $category = preg_replace('/\s+/', ' ', $category);

    $paymentMethod = trim((string)($expenseData['payment_method'] ?? 'Cash'));
    $paymentMethod = $paymentMethod !== '' ? $paymentMethod : 'Cash';
    $currentShopKey = getCurrentReportShopKey();

    $expenses = getReportExpenses();
    $expenses[] = [
        'id' => uniqid('exp_', true),
        'expense_date' => $expenseDate,
        'category' => $category,
        'description' => trim((string)($expenseData['description'] ?? '')),
        'amount' => round($amount, 2),
        'payment_method' => $paymentMethod,
        'job_order_id' => $jobOrderId,
        'plate_number' => $plateNumber,
        'shop_key' => $currentShopKey,
        'created_by' => trim((string)($expenseData['created_by'] ?? 'System')),
        'created_at' => date('Y-m-d H:i:s')
    ];

    return saveReportExpenses($expenses, $currentShopKey);
}

function getAnnouncementFilePath() {
    return UPLOAD_PATH . 'announcements.json';
}

function getAnnouncementBranchFilePath($shopKey = null) {
    $shop = getActiveShopOption($shopKey);
    $resolvedShopKey = normalizeReportShopKey($shop['key'] ?? 'default');
    return UPLOAD_PATH . 'announcements_' . $resolvedShopKey . '.json';
}

function readAnnouncementsFromFilePath($filePath) {
    if (!file_exists($filePath)) {
        return [];
    }

    $json = file_get_contents($filePath);
    if ($json === false || trim($json) === '') {
        return [];
    }

    $data = json_decode($json, true);
    return is_array($data) ? array_values($data) : [];
}

function writeAnnouncementsToFilePath($filePath, $entries) {
    if (!ensureUploadDirectoryWritable()) {
        return false;
    }

    $json = json_encode(array_values((array)$entries), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($json === false) {
        return false;
    }

    if (file_exists($filePath) && !is_writable($filePath)) {
        @unlink($filePath);
    }

    $tmpPath = $filePath . '.tmp';
    if (file_put_contents($tmpPath, $json, LOCK_EX) === false) {
        return false;
    }

    @chmod($tmpPath, 0664);
    if (!@rename($tmpPath, $filePath)) {
        @unlink($tmpPath);
        return false;
    }

    return true;
}

function normalizeAnnouncementAudience($audience) {
    $normalized = strtolower(trim((string)$audience));
    return $normalized === 'branch' ? 'branch' : 'everyone';
}

function normalizeAnnouncementEntry($entry, $defaultAudience = 'everyone', $defaultShopKey = '') {
    if (!is_array($entry)) {
        return null;
    }

    $normalized = $entry;
    $normalized['id'] = trim((string)($normalized['id'] ?? ''));
    $normalized['category'] = trim((string)($normalized['category'] ?? 'General'));
    $normalized['title'] = trim((string)($normalized['title'] ?? ''));
    $normalized['message'] = (string)($normalized['message'] ?? '');
    $normalized['enabled'] = !empty($normalized['enabled']);
    $normalized['updated_at'] = (string)($normalized['updated_at'] ?? '');
    $normalized['updated_by'] = trim((string)($normalized['updated_by'] ?? ''));

    $audience = normalizeAnnouncementAudience($normalized['audience'] ?? $defaultAudience);
    $normalized['audience'] = $audience;
    $normalized['shop_key'] = $audience === 'branch'
        ? normalizeReportShopKey($normalized['shop_key'] ?? $defaultShopKey)
        : '';

    return $normalized;
}

function getAnnouncements() {
    $all = [];

    $globalEntries = readAnnouncementsFromFilePath(getAnnouncementFilePath());
    foreach ($globalEntries as $entry) {
        $normalized = normalizeAnnouncementEntry($entry, 'everyone', '');
        if ($normalized === null) {
            continue;
        }
        $all[] = $normalized;
    }

    $shopOptions = function_exists('getShopOptions') ? getShopOptions() : [];
    foreach ($shopOptions as $shopKey => $shopOption) {
        $branchEntries = readAnnouncementsFromFilePath(getAnnouncementBranchFilePath($shopKey));
        foreach ($branchEntries as $entry) {
            $normalized = normalizeAnnouncementEntry($entry, 'branch', (string)$shopKey);
            if ($normalized === null) {
                continue;
            }
            $all[] = $normalized;
        }
    }

    usort($all, static function ($a, $b) {
        $aTime = strtotime((string)($a['updated_at'] ?? '')) ?: 0;
        $bTime = strtotime((string)($b['updated_at'] ?? '')) ?: 0;
        return $bTime <=> $aTime;
    });

    return array_values($all);
}

function isAnnouncementVisibleToShop($announcement, $shopKey) {
    $audience = normalizeAnnouncementAudience($announcement['audience'] ?? 'everyone');
    if ($audience === 'everyone') {
        return true;
    }

    $targetShopKey = normalizeReportShopKey($announcement['shop_key'] ?? '');
    return $targetShopKey === normalizeReportShopKey($shopKey);
}

function getAnnouncement() {
    // Returns first enabled announcement (for backward compat)
    $all = getActiveAnnouncements();
    foreach ($all as $a) {
        if (!empty($a['enabled'])) return $a;
    }
    return null;
}

function getActiveAnnouncements() {
    $all = getAnnouncements();
    $currentShopKey = getCurrentReportShopKey();

    return array_values(array_filter($all, function($a) use ($currentShopKey) {
        return !empty($a['enabled']) && isAnnouncementVisibleToShop($a, $currentShopKey);
    }));
}

function saveAnnouncements($data) {
    $globalEntries = [];
    $branchEntriesByShop = [];
    $knownShopKeys = [];
    $shopOptions = function_exists('getShopOptions') ? getShopOptions() : [];
    foreach ($shopOptions as $shopKey => $shopOption) {
        $knownShopKeys[] = normalizeReportShopKey((string)$shopKey);
    }

    foreach (array_values((array)$data) as $entry) {
        $normalized = normalizeAnnouncementEntry($entry, $entry['audience'] ?? 'everyone', $entry['shop_key'] ?? '');
        if ($normalized === null) {
            continue;
        }

        if ($normalized['id'] === '') {
            $normalized['id'] = uniqid('ann_', true);
        }

        if ($normalized['audience'] === 'branch') {
            $targetShopKey = $normalized['shop_key'] !== '' ? $normalized['shop_key'] : getCurrentReportShopKey();
            $normalized['shop_key'] = normalizeReportShopKey($targetShopKey);
            $branchEntriesByShop[$normalized['shop_key']][] = $normalized;
        } else {
            $normalized['audience'] = 'everyone';
            $normalized['shop_key'] = '';
            $globalEntries[] = $normalized;
        }
    }

    if (!writeAnnouncementsToFilePath(getAnnouncementFilePath(), $globalEntries)) {
        return false;
    }

    foreach ($knownShopKeys as $shopKey) {
        $rows = $branchEntriesByShop[$shopKey] ?? [];
        if (!writeAnnouncementsToFilePath(getAnnouncementBranchFilePath($shopKey), $rows)) {
            return false;
        }
    }

    foreach ($branchEntriesByShop as $shopKey => $rows) {
        if (in_array($shopKey, $knownShopKeys, true)) {
            continue;
        }
        if (!writeAnnouncementsToFilePath(getAnnouncementBranchFilePath($shopKey), $rows)) {
            return false;
        }
    }

    return true;
}

function saveAnnouncement($data) {
    $all = readAnnouncementsFromFilePath(getAnnouncementFilePath());
    $normalized = normalizeAnnouncementEntry($data, 'everyone', '');
    if ($normalized === null) {
        return false;
    }
    $normalized['audience'] = 'everyone';
    $normalized['shop_key'] = '';

    if (empty($all)) {
        $normalized['id'] = $normalized['id'] !== '' ? $normalized['id'] : uniqid('ann_', true);
        $all[] = $normalized;
    } else {
        $normalized['id'] = $normalized['id'] !== '' ? $normalized['id'] : ($all[0]['id'] ?? uniqid('ann_', true));
        $all[0] = $normalized;
    }

    return writeAnnouncementsToFilePath(getAnnouncementFilePath(), $all);
}

function recordTechnicianPoints($technicianId, $jobOrderId, $reason, $points) {
    try {
        $db = Database::getInstance();
        $db->query(
            "INSERT INTO technician_points (technician_id, job_order_id, reason, points, created_at) VALUES (?,?,?,?,NOW())",
            [(int)$technicianId, $jobOrderId ? (int)$jobOrderId : null, $reason, (float)$points]
        );
    } catch (\Exception $e) {
        // Table may not exist yet — fail silently
    }
}

function getReportIncomeLegacyFilePath() {
    return UPLOAD_PATH . 'report_manual_income.json';
}

function getReportIncomeFilePath($shopKey = null) {
    $shop = getActiveShopOption($shopKey);
    $resolvedShopKey = normalizeReportShopKey($shop['key'] ?? 'default');

    return UPLOAD_PATH . 'report_manual_income_' . $resolvedShopKey . '.json';
}

function getReportManualIncome() {
    $currentShopKey = resolveReportShopKey(getCurrentReportShopKey(), getLegacyReportShopKey());
    $legacyShopKey = resolveReportShopKey(getLegacyReportShopKey(), $currentShopKey);
    $filePath = getReportIncomeFilePath($currentShopKey);
    $legacyFilePath = getReportIncomeLegacyFilePath();

    if (
        !file_exists($filePath)
        && file_exists($legacyFilePath)
        && $currentShopKey === $legacyShopKey
    ) {
        $legacyIncome = [];
        $legacyJson = file_get_contents($legacyFilePath);
        if ($legacyJson !== false && trim($legacyJson) !== '') {
            $legacyDecoded = json_decode($legacyJson, true);
            if (is_array($legacyDecoded)) {
                foreach (array_values($legacyDecoded) as $incomeRow) {
                    if (!is_array($incomeRow)) {
                        continue;
                    }
                    $incomeRow['shop_key'] = $currentShopKey;
                    $legacyIncome[] = $incomeRow;
                }
            }
        }

        if (!empty($legacyIncome)) {
            saveReportManualIncome($legacyIncome, $currentShopKey);
        }
    }

    if (!file_exists($filePath)) {
        return [];
    }
    $data = readReportRowsFromFilePath($filePath);
    if (empty($data)) {
        return [];
    }

    $normalizedRows = [];
    $rowsByDestinationShop = [];
    $needsRebalancing = false;
    $legacyIds = [];

    if ($currentShopKey !== $legacyShopKey) {
        $legacyBranchRows = readReportRowsFromFilePath(getReportIncomeFilePath($legacyShopKey));
        $legacySharedRows = readReportRowsFromFilePath($legacyFilePath);
        foreach (array_merge($legacyBranchRows, $legacySharedRows) as $legacyRow) {
            if (!is_array($legacyRow)) {
                continue;
            }
            $legacyId = trim((string)($legacyRow['id'] ?? ''));
            if ($legacyId !== '') {
                $legacyIds[$legacyId] = true;
            }
        }
    }

    foreach (array_values($data) as $incomeRow) {
        if (!is_array($incomeRow)) {
            continue;
        }

        $rowId = trim((string)($incomeRow['id'] ?? ''));
        $entryShopKey = resolveReportShopKey($incomeRow['shop_key'] ?? '', $currentShopKey);
        if (!isset($incomeRow['shop_key']) || trim((string)$incomeRow['shop_key']) === '') {
            $incomeRow['shop_key'] = $legacyShopKey;
            $needsRebalancing = true;
            $entryShopKey = $legacyShopKey;
        }

        if (
            $currentShopKey !== $legacyShopKey
            && $rowId !== ''
            && isset($legacyIds[$rowId])
            && $entryShopKey !== $legacyShopKey
        ) {
            $entryShopKey = $legacyShopKey;
            $incomeRow['shop_key'] = $legacyShopKey;
            $needsRebalancing = true;
        }

        if ($entryShopKey !== normalizeReportShopKey($incomeRow['shop_key'] ?? '')) {
            $incomeRow['shop_key'] = $entryShopKey;
            $needsRebalancing = true;
        }

        $rowsByDestinationShop[$entryShopKey][] = $incomeRow;
        if ($entryShopKey === $currentShopKey) {
            $normalizedRows[] = $incomeRow;
        } else {
            $needsRebalancing = true;
        }
    }

    if ($needsRebalancing) {
        saveReportManualIncome($normalizedRows, $currentShopKey);

        foreach ($rowsByDestinationShop as $destinationShopKey => $rowsForShop) {
            if ($destinationShopKey === $currentShopKey) {
                continue;
            }

            $destinationFilePath = getReportIncomeFilePath($destinationShopKey);
            $destinationExistingRows = readReportRowsFromFilePath($destinationFilePath);
            $mergedRows = mergeReportRowsById($destinationExistingRows, $rowsForShop);
            saveReportManualIncome($mergedRows, $destinationShopKey);
        }
    }

    return $normalizedRows;
}

function saveReportManualIncome($entries, $shopKey = null) {
    if (!ensureUploadDirectoryWritable()) {
        return false;
    }
    $filePath = getReportIncomeFilePath($shopKey);
    $json = json_encode(array_values((array)$entries), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($json === false) {
        return false;
    }
    return file_put_contents($filePath, $json, LOCK_EX) !== false;
}

function addReportManualIncome($incomeData) {
    $amount = (float)($incomeData['amount'] ?? 0);
    if ($amount <= 0) {
        return false;
    }
    $currentShopKey = getCurrentReportShopKey();
    $entries = getReportManualIncome();
    $entries[] = [
        'id' => uniqid('inc_', true),
        'income_date' => $incomeData['income_date'] ?? date('Y-m-d'),
        'description' => trim((string)($incomeData['description'] ?? '')),
        'amount' => $amount,
        'payment_method' => trim((string)($incomeData['payment_method'] ?? 'Cash')),
        'shop_key' => $currentShopKey,
        'created_by' => $incomeData['created_by'] ?? 'System',
        'created_at' => date('Y-m-d H:i:s'),
    ];
    return saveReportManualIncome($entries, $currentShopKey);
}

function getDeletedArchiveRetentionDays() {
    return 30;
}

function getDeletedArchiveFilePath($shopKey = null) {
    $shop = getActiveShopOption($shopKey);
    $resolvedShopKey = normalizeReportShopKey($shop['key'] ?? 'default');
    return UPLOAD_PATH . 'deleted_archive_' . $resolvedShopKey . '.json';
}

function readDeletedArchiveEntries($shopKey = null) {
    $filePath = getDeletedArchiveFilePath($shopKey);
    if (!file_exists($filePath)) {
        return [];
    }

    $raw = file_get_contents($filePath);
    if ($raw === false || trim($raw) === '') {
        return [];
    }

    $decoded = json_decode($raw, true);
    return is_array($decoded) ? array_values($decoded) : [];
}

function saveDeletedArchiveEntries($entries, $shopKey = null) {
    if (!ensureUploadDirectoryWritable()) {
        return false;
    }

    $filePath = getDeletedArchiveFilePath($shopKey);
    $json = json_encode(array_values((array)$entries), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($json === false) {
        return false;
    }

    $tmpPath = $filePath . '.tmp';
    if (file_put_contents($tmpPath, $json, LOCK_EX) === false) {
        return false;
    }

    @chmod($tmpPath, 0664);
    if (!@rename($tmpPath, $filePath)) {
        @unlink($tmpPath);
        return false;
    }

    return true;
}

function purgeDeletedArchiveEntries($shopKey = null) {
    $entries = readDeletedArchiveEntries($shopKey);
    if (empty($entries)) {
        return ['success' => true, 'removed_count' => 0];
    }

    $nowTs = time();
    $retained = [];
    $removedCount = 0;

    foreach ($entries as $entry) {
        if (!is_array($entry)) {
            continue;
        }

        $expiresAt = trim((string)($entry['expires_at'] ?? ''));
        $expiresAtTs = $expiresAt !== '' ? strtotime($expiresAt) : false;
        if ($expiresAtTs !== false && $expiresAtTs <= $nowTs) {
            $removedCount++;
            if (($entry['entity_type'] ?? '') === 'staff' && !empty($entry['payload']['profile_image'])) {
                deleteFile((string)$entry['payload']['profile_image']);
            }
            continue;
        }

        $retained[] = $entry;
    }

    if ($removedCount === 0) {
        return ['success' => true, 'removed_count' => 0];
    }

    if (!saveDeletedArchiveEntries($retained, $shopKey)) {
        return ['success' => false, 'removed_count' => 0, 'message' => 'Failed to purge deleted archive entries'];
    }

    return ['success' => true, 'removed_count' => $removedCount];
}

function getDeletedArchiveEntries($shopKey = null) {
    $purgeResult = purgeDeletedArchiveEntries($shopKey);
    if (isset($purgeResult['success']) && !$purgeResult['success']) {
        return [];
    }
    return readDeletedArchiveEntries($shopKey);
}

function archiveDeletedRecord($entityType, $payload, $options = []) {
    if (!is_array($payload)) {
        return false;
    }

    $shopKey = resolveReportShopKey(
        $options['shop_key'] ?? getCurrentReportShopKey(),
        getLegacyReportShopKey()
    );
    $entries = readDeletedArchiveEntries($shopKey);
    $deletedAt = date('Y-m-d H:i:s');

    $archiveId = uniqid('trash_', true);
    $entries[] = [
        'id' => $archiveId,
        'entity_type' => trim((string)$entityType),
        'source' => trim((string)($options['source'] ?? 'unknown')),
        'deleted_at' => $deletedAt,
        'expires_at' => date('Y-m-d H:i:s', strtotime('+' . getDeletedArchiveRetentionDays() . ' days', strtotime($deletedAt))),
        'shop_key' => $shopKey,
        'deleted_by' => [
            'id' => (int)($_SESSION['user_id'] ?? 0),
            'name' => trim((string)($_SESSION['full_name'] ?? $_SESSION['username'] ?? 'System')),
        ],
        'meta' => is_array($options['meta'] ?? null) ? $options['meta'] : [],
        'payload' => $payload,
    ];

    return saveDeletedArchiveEntries($entries, $shopKey) ? $archiveId : false;
}

function deleteArchivedRecordById($archiveId, $shopKey = null) {
    $archiveId = trim((string)$archiveId);
    if ($archiveId === '') {
        return false;
    }

    $targetShopKey = resolveReportShopKey($shopKey ?? getCurrentReportShopKey(), getLegacyReportShopKey());
    $entries = readDeletedArchiveEntries($targetShopKey);
    $filteredEntries = array_values(array_filter($entries, static function ($entry) use ($archiveId) {
        return (string)($entry['id'] ?? '') !== $archiveId;
    }));

    if (count($filteredEntries) === count($entries)) {
        return true;
    }

    return saveDeletedArchiveEntries($filteredEntries, $targetShopKey);
}

function clearDeletedArchiveEntries($shopKey = null) {
    $targetShopKey = resolveReportShopKey($shopKey ?? getCurrentReportShopKey(), getLegacyReportShopKey());
    return saveDeletedArchiveEntries([], $targetShopKey);
}

function getArchivedEntityDisplayName($entityType) {
    $map = [
        'report_expense' => 'Manual Expense',
        'manual_income' => 'Manual Income',
        'announcement' => 'Announcement',
        'product' => 'Product',
        'product_category' => 'Product Category',
        'supplier' => 'Supplier',
        'estimate' => 'Estimate',
        'staff' => 'Staff',
        'service' => 'Service',
        'service_bundle' => 'Service Bundle',
        'job_order' => 'Job Order',
    ];
    return $map[$entityType] ?? ucwords(str_replace('_', ' ', (string)$entityType));
}

function insertArchivedDatabaseRow($table, $row, $primaryKey = 'id') {
    if (!preg_match('/^[a-zA-Z0-9_]+$/', (string)$table)) {
        throw new Exception('Invalid archive table name');
    }
    if (!preg_match('/^[a-zA-Z0-9_]+$/', (string)$primaryKey)) {
        throw new Exception('Invalid archive primary key');
    }
    if (!is_array($row) || empty($row)) {
        throw new Exception('Invalid archive payload for database restore');
    }

    $db = Database::getInstance();
    $columnRows = $db->fetchAll("SHOW COLUMNS FROM `{$table}`");
    if (empty($columnRows)) {
        throw new Exception('Unable to detect archive table columns');
    }

    $allowedColumns = [];
    foreach ($columnRows as $columnRow) {
        $columnName = trim((string)($columnRow['Field'] ?? ''));
        if ($columnName !== '') {
            $allowedColumns[$columnName] = true;
        }
    }

    $insertColumns = [];
    $insertValues = [];
    foreach ($row as $column => $value) {
        if (!is_string($column) || !isset($allowedColumns[$column])) {
            continue;
        }
        if (is_array($value) || is_object($value)) {
            continue;
        }
        $insertColumns[] = $column;
        $insertValues[] = $value;
    }

    if (empty($insertColumns)) {
        throw new Exception('No restorable columns found');
    }

    if (isset($row[$primaryKey]) && $row[$primaryKey] !== null && $row[$primaryKey] !== '') {
        $exists = $db->fetch("SELECT 1 AS found FROM `{$table}` WHERE `{$primaryKey}` = ? LIMIT 1", [$row[$primaryKey]]);
        if ($exists) {
            throw new Exception('Record already exists and cannot be restored');
        }
    }

    $quotedColumns = array_map(static function ($column) {
        return '`' . $column . '`';
    }, $insertColumns);
    $placeholders = implode(',', array_fill(0, count($insertColumns), '?'));
    $sql = "INSERT INTO `{$table}` (" . implode(',', $quotedColumns) . ") VALUES ({$placeholders})";
    $db->query($sql, $insertValues);
}

function restoreArchivedJobOrder($payload) {
    if (!is_array($payload) || !is_array($payload['job_order'] ?? null)) {
        throw new Exception('Invalid job order archive payload');
    }

    $db = Database::getInstance();
    $jobOrderRow = $payload['job_order'];
    $jobOrderId = (int)($jobOrderRow['id'] ?? 0);
    if ($jobOrderId <= 0) {
        throw new Exception('Invalid archived job order ID');
    }

    $existing = $db->fetch("SELECT 1 AS found FROM job_orders WHERE id = ? LIMIT 1", [$jobOrderId]);
    if ($existing) {
        throw new Exception('Job order already exists and cannot be restored');
    }

    $db->beginTransaction();
    try {
        insertArchivedDatabaseRow('job_orders', $jobOrderRow, 'id');

        foreach ((array)($payload['job_order_services'] ?? []) as $serviceRow) {
            if (is_array($serviceRow)) {
                insertArchivedDatabaseRow('job_order_services', $serviceRow, 'id');
            }
        }

        foreach ((array)($payload['job_order_products'] ?? []) as $productRow) {
            if (is_array($productRow)) {
                insertArchivedDatabaseRow('job_order_products', $productRow, 'id');
            }
        }

        foreach ((array)($payload['job_order_technicians'] ?? []) as $techRow) {
            if (is_array($techRow)) {
                insertArchivedDatabaseRow('job_order_technicians', $techRow, 'id');
            }
        }

        foreach ((array)($payload['work_sessions'] ?? []) as $sessionRow) {
            if (is_array($sessionRow)) {
                insertArchivedDatabaseRow('work_sessions', $sessionRow, 'id');
            }
        }

        foreach ((array)($payload['job_order_inspections'] ?? []) as $inspectionRow) {
            if (is_array($inspectionRow)) {
                insertArchivedDatabaseRow('job_order_inspections', $inspectionRow, 'id');
            }
        }

        foreach ((array)($payload['technician_points'] ?? []) as $pointsRow) {
            if (is_array($pointsRow)) {
                insertArchivedDatabaseRow('technician_points', $pointsRow, 'id');
            }
        }

        if (!empty($payload['report_expenses']) && is_array($payload['report_expenses'])) {
            $existingExpenses = getReportExpenses();
            $mergedExpenses = mergeReportRowsById($existingExpenses, $payload['report_expenses']);
            if (!saveReportExpenses($mergedExpenses)) {
                throw new Exception('Failed to restore linked report expenses');
            }
        }

        $db->commit();
    } catch (Exception $e) {
        $db->rollBack();
        throw $e;
    }
}

function restoreDeletedArchiveRecord($archiveId, $shopKey = null) {
    $archiveId = trim((string)$archiveId);
    if ($archiveId === '') {
        return ['success' => false, 'message' => 'Archive ID is required'];
    }

    $targetShopKey = resolveReportShopKey($shopKey ?? getCurrentReportShopKey(), getLegacyReportShopKey());
    purgeDeletedArchiveEntries($targetShopKey);
    $entries = readDeletedArchiveEntries($targetShopKey);

    $entryIndex = null;
    $entry = null;
    foreach ($entries as $index => $row) {
        if ((string)($row['id'] ?? '') === $archiveId) {
            $entryIndex = (int)$index;
            $entry = $row;
            break;
        }
    }

    if ($entry === null || $entryIndex === null) {
        return ['success' => false, 'message' => 'Archive entry not found'];
    }

    $payload = is_array($entry['payload'] ?? null) ? $entry['payload'] : [];
    $entityType = trim((string)($entry['entity_type'] ?? ''));

    try {
        if ($entityType === 'report_expense') {
            $rows = getReportExpenses();
            $rows = mergeReportRowsById($rows, [$payload]);
            if (!saveReportExpenses($rows)) {
                throw new Exception('Failed to restore manual expense');
            }
        } elseif ($entityType === 'manual_income') {
            $rows = getReportManualIncome();
            $rows = mergeReportRowsById($rows, [$payload]);
            if (!saveReportManualIncome($rows)) {
                throw new Exception('Failed to restore manual income');
            }
        } elseif ($entityType === 'announcement') {
            $rows = getAnnouncements();
            $rows = mergeReportRowsById($rows, [$payload]);
            if (!saveAnnouncements($rows)) {
                throw new Exception('Failed to restore announcement');
            }
        } elseif ($entityType === 'job_order') {
            restoreArchivedJobOrder($payload);
        } elseif ($entityType === 'service_bundle') {
            $bundleRow = is_array($payload['bundle'] ?? null) ? $payload['bundle'] : [];
            if (empty($bundleRow)) {
                throw new Exception('Invalid bundle archive payload');
            }

            $db = Database::getInstance();
            $db->beginTransaction();
            try {
                insertArchivedDatabaseRow('service_bundles', $bundleRow, 'id');
                $bundleId = (int)($bundleRow['id'] ?? 0);

                foreach ((array)($payload['bundle_services'] ?? []) as $serviceRow) {
                    $serviceId = (int)($serviceRow['service_id'] ?? 0);
                    if ($bundleId > 0 && $serviceId > 0) {
                        $db->query("INSERT IGNORE INTO bundle_services (bundle_id, service_id) VALUES (?, ?)", [$bundleId, $serviceId]);
                    }
                }

                foreach ((array)($payload['bundle_products'] ?? []) as $productRow) {
                    $productId = (int)($productRow['product_id'] ?? 0);
                    $qty = max(1, (int)($productRow['quantity'] ?? 1));
                    if ($bundleId > 0 && $productId > 0) {
                        $db->query("INSERT INTO bundle_products (bundle_id, product_id, quantity) VALUES (?,?,?)", [$bundleId, $productId, $qty]);
                    }
                }

                $db->commit();
            } catch (Exception $e) {
                $db->rollBack();
                throw $e;
            }
        } else {
            $tableMap = [
                'product' => 'products',
                'product_category' => 'product_categories',
                'supplier' => 'suppliers',
                'estimate' => 'job_estimates',
                'staff' => 'staff',
                'service' => 'services',
            ];
            if (!isset($tableMap[$entityType])) {
                throw new Exception('Restore is not supported for this record type');
            }
            insertArchivedDatabaseRow($tableMap[$entityType], $payload, 'id');
        }
    } catch (Exception $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }

    unset($entries[$entryIndex]);
    if (!saveDeletedArchiveEntries(array_values($entries), $targetShopKey)) {
        return ['success' => false, 'message' => 'Record restored but failed to update archive list'];
    }

    return ['success' => true, 'message' => 'Record restored successfully'];
}

function buildNotificationMessageTemplate($actorName, $action, $subject, $details = '') {
    $actor = trim((string)$actorName);
    $verb = trim((string)$action);
    $target = trim((string)$subject);
    $extra = trim((string)$details);

    $base = trim($actor . ' ' . $verb . ' ' . $target);
    if ($base === '') {
        return '';
    }

    if ($extra !== '') {
        return $base . ' (' . $extra . ').';
    }

    return $base . '.';
}

function notifyRoles($type, $title, $message, $roles = [], $options = []) {
    try {
        $db = Database::getInstance();

        $type = trim((string)$type);
        $allowedTypes = ['job_assigned', 'job_status', 'payment', 'low_stock', 'system', 'staff_update', 'account_update'];
        if (!in_array($type, $allowedTypes, true)) {
            $type = 'system';
        }

        $title = trim((string)$title);
        $message = trim((string)$message);
        if ($title === '' || $message === '') {
            return false;
        }

        $normalizedRoles = array_values(array_unique(array_filter(array_map('normalizeRole', (array)$roles))));
        if (empty($normalizedRoles)) {
            return false;
        }

        $excludeUserId = isset($options['exclude_user_id']) ? (int)$options['exclude_user_id'] : 0;
        $referenceType = trim((string)($options['reference_type'] ?? ''));
        $referenceId = isset($options['reference_id']) && $options['reference_id'] !== null
            ? (int)$options['reference_id']
            : null;

        $isTypeAllowedForRole = static function ($role, $notificationType) {
            $role = normalizeRole($role);

            $allowedByRole = [
                'admin' => ['job_assigned', 'job_status', 'payment', 'low_stock', 'system', 'staff_update', 'account_update'],
                'cashier' => ['job_assigned', 'job_status', 'payment', 'low_stock', 'system', 'staff_update', 'account_update'],
                'service_adviser' => ['job_assigned', 'job_status', 'payment', 'system'],
                'chief_mechanic' => ['job_assigned', 'job_status', 'system'],
                'technician' => ['job_assigned', 'job_status'],
            ];

            if (!isset($allowedByRole[$role])) {
                return false;
            }

            return in_array($notificationType, $allowedByRole[$role], true);
        };

        $isTechnicianAssignedToJo = static function ($dbInstance, $technicianId, $jobOrderId) {
            if ($technicianId <= 0 || $jobOrderId <= 0) {
                return false;
            }

            $row = $dbInstance->fetch(
                "SELECT 1 AS assigned FROM job_order_technicians WHERE technician_id = ? AND job_order_id = ? LIMIT 1",
                [(int)$technicianId, (int)$jobOrderId]
            );

            return !empty($row);
        };

        $recipientMap = []; // [user_id => role]

        $staffPlaceholders = implode(',', array_fill(0, count($normalizedRoles), '?'));
        $staffRows = $db->fetchAll(
            "SELECT id, role FROM staff WHERE status='active' AND role IN ($staffPlaceholders)",
            $normalizedRoles
        );
        foreach ($staffRows as $row) {
            $sid = (int)($row['id'] ?? 0);
            if ($sid > 0) {
                $recipientMap[$sid] = normalizeRole($row['role'] ?? '');
            }
        }

        if (in_array('admin', $normalizedRoles, true)) {
            $adminUsers = $db->fetchAll("SELECT id, role FROM users WHERE status='active' AND role='admin'");
            foreach ($adminUsers as $row) {
                $uid = (int)($row['id'] ?? 0);
                if ($uid > 0) {
                    $recipientMap[$uid] = normalizeRole($row['role'] ?? 'admin');
                }
            }
        }

        $recipientIds = array_values(array_filter(array_keys($recipientMap), static function ($id) use ($excludeUserId) {
            return (int)$id > 0 && (int)$id !== $excludeUserId;
        }));

        if (empty($recipientIds)) {
            return true;
        }

        foreach ($recipientIds as $recipientId) {
            $recipientRole = normalizeRole($recipientMap[$recipientId] ?? '');
            if (!$isTypeAllowedForRole($recipientRole, $type)) {
                continue;
            }

            if (
                $recipientRole === 'technician'
                && $referenceType === 'job_order'
                && $referenceId !== null
                && !$isTechnicianAssignedToJo($db, (int)$recipientId, (int)$referenceId)
            ) {
                continue;
            }

            $db->query(
                "INSERT INTO notifications (user_id, type, title, message, reference_type, reference_id, is_read)
                 VALUES (?, ?, ?, ?, ?, ?, 0)",
                [
                    (int)$recipientId,
                    $type,
                    $title,
                    $message,
                    $referenceType !== '' ? $referenceType : null,
                    $referenceId,
                ]
            );
        }

        return true;
    } catch (Throwable $e) {
        error_log('notifyRoles error: ' . $e->getMessage());
        return false;
    }
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

    if (empty($file['tmp_name']) || !is_file($file['tmp_name'])) {
        return ['success' => false, 'message' => 'Invalid temporary upload file'];
    }

    if ($file['size'] > $maxSize) {
        return ['success' => false, 'message' => 'File size exceeds limit'];
    }

    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $normalizedAllowed = array_map('strtolower', (array)$allowedTypes);
    if (!in_array($extension, $normalizedAllowed, true)) {
        return ['success' => false, 'message' => 'File type not allowed'];
    }

    if (!ensureUploadDirectoryWritable()) {
        return ['success' => false, 'message' => 'Upload directory is not writable'];
    }

    $filename = sanitizeFilename(uniqid() . '_' . time() . '.' . $extension);
    $destination = UPLOAD_PATH . $filename;

    if (!@copy($file['tmp_name'], $destination) && !@move_uploaded_file($file['tmp_name'], $destination)) {
        return ['success' => false, 'message' => 'Failed to move uploaded file'];
    }

    @chmod($destination, 0644);

    return ['success' => true, 'filename' => $filename, 'url' => UPLOAD_URL . $filename];
}

function getRoleLabel($role) {
    $role = normalizeRole($role);
    $labels = [
        'admin' => 'Admin',
        'cashier' => 'Cashier',
        'chief_mechanic' => 'Chief Mechanic',
        'service_adviser' => 'Service Adviser',
        'technician' => 'Technician',
        'lead_man' => 'Lead Man',
        'stockman' => 'Stockman'
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

function formatActivityAction($action) {
    $action = trim((string)$action);
    if ($action === '') {
        return 'Activity';
    }

    $normalized = strtolower($action);
    $knownLabels = [
        'login' => 'Login',
        'logout' => 'Logout',
        'register' => 'Register',
        'change_password' => 'Password Change',
    ];

    if (isset($knownLabels[$normalized])) {
        return $knownLabels[$normalized];
    }

    return ucwords(str_replace('_', ' ', $normalized));
}

function classifyActivityChangeType($action, $description = '') {
    $text = strtolower(trim((string)$action) . ' ' . trim((string)$description));

    if ($text === '') {
        return 'other';
    }

    if (preg_match('/\b(delete|deleted|remove|removed)\b/', $text)) {
        return 'delete';
    }

    if (preg_match('/\b(create|created|add|added|insert|inserted)\b/', $text)) {
        return 'add';
    }

    if (preg_match('/\b(update|updated|edit|edited|change|changed|toggle)\b/', $text)) {
        return 'update';
    }

    if (preg_match('/\b(status|approve|approved|reject|rejected|assign|assigned|release|released)\b/', $text)) {
        return 'status';
    }

    return 'other';
}

function getActivityTypeLabel($type) {
    $labels = [
        'add' => 'Add',
        'update' => 'Update',
        'delete' => 'Delete/Remove',
        'status' => 'Status Change',
        'other' => 'Other',
    ];

    return $labels[$type] ?? 'Other';
}

function getActivityTypeBadgeClass($type) {
    $classes = [
        'add' => 'bg-success-subtle text-success-emphasis border border-success-subtle',
        'update' => 'bg-primary-subtle text-primary-emphasis border border-primary-subtle',
        'delete' => 'bg-danger-subtle text-danger-emphasis border border-danger-subtle',
        'status' => 'bg-warning-subtle text-warning-emphasis border border-warning-subtle',
        'other' => 'bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle',
    ];

    return $classes[$type] ?? $classes['other'];
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

/**
 * Auto-cleanup old records (older than 1 year).
 * Runs at most once per day using a lock file.
 */
function runAutoCleanup() {
    $lockFile = sys_get_temp_dir() . '/autodok_cleanup_' . md5(__DIR__) . '.lock';
    
    // Only run once per day
    if (file_exists($lockFile) && (time() - filemtime($lockFile)) < 86400) {
        return;
    }
    
    @touch($lockFile);
    
    try {
        $db = Database::getInstance();
        $oneYearAgo = date('Y-m-d H:i:s', strtotime('-1 year'));
        
        // Delete job estimates older than 1 year
        $db->query("DELETE FROM job_estimates WHERE created_at < ?", [$oneYearAgo]);
        
        // Delete job orders older than 1 year (only completed/released/cancelled)
        $db->query("DELETE FROM job_orders WHERE created_at < ? AND status IN ('completed','released','cancelled')", [$oneYearAgo]);
        
        // Delete activity logs older than 1 year
        $db->query("DELETE FROM activity_logs WHERE created_at < ?", [$oneYearAgo]);
        
    } catch (Exception $e) {
        error_log("Auto-cleanup error: " . $e->getMessage());
    }
}
