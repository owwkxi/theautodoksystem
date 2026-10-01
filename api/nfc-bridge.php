<?php
define('APP_ACCESS', true);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

$host = strtolower((string)($_SERVER['HTTP_HOST'] ?? ''));
$host = preg_replace('/:\d+$/', '', $host);
$remoteAddress = (string)($_SERVER['REMOTE_ADDR'] ?? '');
$isLoopbackHost = in_array($host, ['localhost', '127.0.0.1', '::1'], true);
$isLoopbackClient = in_array($remoteAddress, ['127.0.0.1', '::1', '::ffff:127.0.0.1'], true);

if (!$isLoopbackHost || !$isLoopbackClient) {
    http_response_code(403);
    echo json_encode(['error' => 'The local NFC bridge proxy is available only on this computer']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$route = (string)($_GET['route'] ?? '');
if ($route === 'health') {
    $bridgeUrl = 'http://127.0.0.1:8765/health';
    $timeout = 4;
} elseif ($route === 'scan') {
    $scanTimeout = filter_var($_GET['timeout'] ?? 2, FILTER_VALIDATE_FLOAT);
    if ($scanTimeout === false || !is_finite((float)$scanTimeout)) {
        $scanTimeout = 2;
    }
    $scanTimeout = min(max((float)$scanTimeout, 0), 2);
    $bridgeUrl = 'http://127.0.0.1:8765/scan?timeout=' . rawurlencode((string)$scanTimeout);
    $timeout = (int)ceil($scanTimeout) + 2;
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Unknown local NFC bridge route']);
    exit;
}

if (!function_exists('curl_init')) {
    http_response_code(503);
    echo json_encode(['error' => 'The PHP cURL extension is required for the local NFC bridge']);
    exit;
}

$curl = curl_init($bridgeUrl);
curl_setopt_array($curl, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CONNECTTIMEOUT => 2,
    CURLOPT_TIMEOUT => $timeout,
    CURLOPT_FOLLOWLOCATION => false,
    CURLOPT_HTTPHEADER => ['Accept: application/json'],
]);
$responseBody = curl_exec($curl);
$curlError = curl_error($curl);
$statusCode = (int)curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
curl_close($curl);

if ($responseBody === false) {
    error_log('Local NFC bridge proxy error: ' . $curlError);
    http_response_code(503);
    echo json_encode(['error' => 'Cannot reach the local ACR122 bridge. Start it on this computer and try again.']);
    exit;
}

$responseData = json_decode($responseBody, true);
if (!is_array($responseData)) {
    error_log('Local NFC bridge returned an invalid JSON response');
    http_response_code(502);
    echo json_encode(['error' => 'The local ACR122 bridge returned an invalid response']);
    exit;
}

http_response_code($statusCode >= 400 ? $statusCode : 200);
echo json_encode($responseData);
