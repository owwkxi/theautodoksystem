<?php

defined('APP_ACCESS') or define('APP_ACCESS', true);

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'autodok_prime_auto_services_db');
define('DB_CHARSET', 'utf8mb4');

// Application Configuration
define('APP_NAME', 'Autodok Prime Auto Services');
define('APP_DESCRIPTION', 'Prime Automotive Care Services');
define('SHOP_BRANCH_NAME', 'autodok-prime-auto-services');
define('APP_VERSION', '1.0.0');
define('APP_URL', 'http://localhost/theautodoksystem');

if (!function_exists('getShopOptions')) {
	function getShopOptions() {
		return [
			'autodok_main' => [
				'name' => 'The Autodok',
				'db_name' => 'autodok_db',
			],
			'autodok_prime' => [
				'name' => 'Autodok Prime Auto Services',
				'db_name' => 'autodok_prime_auto_services_db',
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
define('BASE_PATH', dirname(__DIR__));
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
define('DATE_FORMAT', 'Y-m-d');
define('DATETIME_FORMAT', 'Y-m-d H:i:s');
define('DISPLAY_DATE_FORMAT', 'F d, Y');
define('DISPLAY_DATETIME_FORMAT', 'F d, Y h:i A');

// Error Reporting (Set to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

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

// Email Configuration (for future use)
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'your-email@gmail.com');
define('SMTP_PASS', 'your-password');
define('SMTP_FROM', 'noreply@autodok.com');
define('SMTP_FROM_NAME', 'Autodok Prime Auto Services');

// Application Status
define('MAINTENANCE_MODE', false);
