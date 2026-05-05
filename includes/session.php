<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['created'])) {
    $_SESSION['created'] = time();
} elseif (time() - $_SESSION['created'] > 1800) {
    // Regenerate session ID every 30 minutes
    session_regenerate_id(true);
    $_SESSION['created'] = time();
}

// Check session timeout
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_LIFETIME)) {
    // Session expired
    session_unset();
    session_destroy();
    session_start();
}
$_SESSION['last_activity'] = time();

// Validate session fingerprint to prevent session hijacking
$currentFingerprint = md5($_SERVER['HTTP_USER_AGENT'] ?? '' . getClientIP());
if (isset($_SESSION['fingerprint'])) {
    if ($_SESSION['fingerprint'] !== $currentFingerprint) {
        // Possible session hijacking
        session_unset();
        session_destroy();
        session_start();
    }
} else {
    $_SESSION['fingerprint'] = $currentFingerprint;
}

function setMessage($message, $type = 'info') {
    $_SESSION['flash_message'] = [
        'message' => $message,
        'type' => $type
    ];
}

function getMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    return null;
}

function hasMessage() {
    return isset($_SESSION['flash_message']);
}
