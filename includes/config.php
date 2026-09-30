<?php

defined('APP_ACCESS') or define('APP_ACCESS', true);

// Database Configuration
define('DB_HOST', '127.0.0.1');
define('DB_USER', 'u141753080_tautodok');
define('DB_PASS', 'Tautod0k');
define('DB_NAME', 'u141753080_tautodok');
define('DB_CHARSET', 'utf8mb4');

// Application Configuration
define('APP_NAME', 'Autodok Prime Auto Services');
define('APP_DESCRIPTION', 'Prime Automotive Care Services');
define('SHOP_BRANCH_NAME', 'u141753080_tautodok');
define('APP_VERSION', '1.0.0');

$basePathFs = realpath(dirname(__DIR__)) ?: dirname(__DIR__);
$configuredBasePath = trim((string)(getenv('APP_BASE_PATH') ?: ''), '/');

if ($configuredBasePath !== '') {
	$baseUrlPath = '/' . $configuredBasePath;
} else {
	$baseUrlPath = '';
	$docRoot = $_SERVER['DOCUMENT_ROOT'] ?? '';
	if ($docRoot !== '') {
		$docRootFs = realpath($docRoot) ?: $docRoot;
		$normalizedDocRoot = rtrim(str_replace('\\', '/', $docRootFs), '/');
		$normalizedBasePath = rtrim(str_replace('\\', '/', $basePathFs), '/');

		if ($normalizedDocRoot !== '' && strpos($normalizedBasePath, $normalizedDocRoot) === 0) {
			$relativePath = trim(substr($normalizedBasePath, strlen($normalizedDocRoot)), '/');
			$baseUrlPath = $relativePath === '' ? '' : ('/' . $relativePath);
		}
	}
}

define('APP_BASE_PATH', $baseUrlPath);

$configuredAppUrl = trim((string)(getenv('APP_URL') ?: ''));
if ($configuredAppUrl !== '') {
	define('APP_URL', rtrim($configuredAppUrl, '/'));
} else {
	$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['SERVER_PORT'] ?? '') == 443);
	$scheme = $isHttps ? 'https' : 'http';
	$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
	define('APP_URL', rtrim($scheme . '://' . $host . APP_BASE_PATH, '/'));
}

if (!function_exists('getShopOptions')) {
	function getShopOptions() {
		return [
			'autodok_main' => [
				'name' => 'The Autodok',
				'db_name' => 'u141753080_tautodok',
				'db_user' => 'u141753080_tautodok',
				'db_pass' => 'Tautod0k',
			],
			'autodok_prime' => [
				'name' => 'Autodok Prime Auto Services',
				'db_name' => 'u141753080_pautodok',
				'db_user' => 'u141753080_pautodok',
				'db_pass' => 'Pautod0k',
			],
		];
	}
}

if (!function_exists('resolveShopOption')) {
	function resolveShopOption($shopKey) {
		$options = getShopOptions();
		$key = trim((string)$shopKey);
		if ($key !== '' && isset($options[$key])) {
			return ['key' => $key] + $options[$key];
		}

		foreach ($options as $candidateKey => $option) {
			if (($option['db_name'] ?? '') === DB_NAME) {
				return ['key' => $candidateKey] + $option;
			}
		}

		$firstKey = array_key_first($options);
		return ['key' => $firstKey] + $options[$firstKey];
	}
}

// Path Configuration
define('BASE_PATH', $basePathFs);
define('UPLOAD_PATH', BASE_PATH . '/uploads/');
define('UPLOAD_URL', APP_URL . '/uploads/');

// Security Configuration
define('SESSION_LIFETIME', 3600); 
define('CSRF_TOKEN_NAME', 'csrf_token');
define('JWT_SECRET_KEY', 'your-secret-key-change-this-in-production-2026');
define('JWT_ALGORITHM', 'HS256');
define('JWT_EXPIRATION', 86400); 

// Password Configuration
define('PASSWORD_COST', 12);

// Pagination
define('RECORDS_PER_PAGE', 10);

// Date and Time
define('TIMEZONE', 'Asia/Manila');
date_default_timezone_set(TIMEZONE);
$timezoneNow = new DateTime('now', new DateTimeZone(TIMEZONE));
define('DB_TIMEZONE_OFFSET', $timezoneNow->format('P'));
define('DATE_FORMAT', 'Y-m-d');
define('DATETIME_FORMAT', 'Y-m-d H:i:s');
define('DISPLAY_DATE_FORMAT', 'F d, Y');
define('DISPLAY_DATETIME_FORMAT', 'F d, Y h:i A');

// Keep diagnostic details in the server log instead of exposing them in the page.
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Session Configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); 
ini_set('session.cookie_samesite', 'Strict');

// File Upload Configuration
define('MAX_FILE_SIZE', 5242880); 
define('ALLOWED_FILE_TYPES', ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx']);

// API Configuration
define('API_RATE_LIMIT', 100); 
define('API_VERSION', 'v1');

// SMTP email configuration. Use your Hostinger mailbox credentials in production.
define('SMTP_HOST', 'smtp.hostinger.com');
define('SMTP_PORT', 465);
define('SMTP_ENCRYPTION', 'ssl');
define('SMTP_USER', getenv('SMTP_USER') ?: 'update@theautodok.com');
define('SMTP_PASS', getenv('SMTP_PASS') ?: 'UpdateTautod0k.');
define('SMTP_FROM', getenv('SMTP_FROM') ?: SMTP_USER);
define('SMTP_FROM_NAME', 'The Autodok');

// Infobip SMS configuration. Keep the API key outside the repository.
define('SMS_ENABLED', true);
define('INFOBIP_BASE_URL', 'https://6z3emr.api.infobip.com');
define('INFOBIP_API_KEY', 'ed7aa5dc023f4d4cbb7cfe4e7aaf6516-b6bdf888-c2cb-4282-8872-c3df45de4d50');
define('INFOBIP_SENDER', '447491163443');

// Application Status
define('MAINTENANCE_MODE', false);