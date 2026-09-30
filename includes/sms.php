<?php

/**
 * Send a plain-text SMS through Infobip.
 *
 * Credentials must be supplied through environment variables:
 * INFOBIP_BASE_URL, INFOBIP_API_KEY, and INFOBIP_SENDER.
 */
function sendInfobipSms($phoneNumber, $message) {
    if (SMS_ENABLED !== true) {
        return 'SMS is disabled';
    }

    $baseUrl = INFOBIP_BASE_URL;
    $apiKey = INFOBIP_API_KEY;
    $sender = INFOBIP_SENDER;

    if ($baseUrl === '' || $apiKey === '' || $sender === '') {
        return 'Infobip SMS configuration is incomplete';
    }

    $phone = preg_replace('/\D+/', '', (string)$phoneNumber);
    if (str_starts_with($phone, '0')) {
        $phone = '63' . substr($phone, 1);
    }
    if (!preg_match('/^63\d{10}$/', $phone)) {
        return 'Customer phone number is invalid for SMS';
    }

    $payload = json_encode([
        'messages' => [[
            'from' => $sender,
            'destinations' => [['to' => $phone]],
            'text' => trim((string)$message),
        ]],
    ], JSON_UNESCAPED_UNICODE);

    if ($payload === false) {
        return 'Unable to prepare SMS request';
    }

    $curl = curl_init($baseUrl . '/sms/2/text');
    curl_setopt_array($curl, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_HTTPHEADER => [
            'Authorization: App ' . $apiKey,
            'Content-Type: application/json',
            'Accept: application/json',
        ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT => 20,
    ]);

    $response = curl_exec($curl);
    $curlError = curl_error($curl);
    $statusCode = (int)curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    if ($response === false || $curlError !== '') {
        error_log('Infobip SMS request failed: ' . $curlError);
        return 'SMS delivery request failed';
    }

    if ($statusCode < 200 || $statusCode >= 300) {
        error_log('Infobip SMS rejected (' . $statusCode . '): ' . $response);
        return 'Infobip rejected the SMS';
    }

    return '';
}
