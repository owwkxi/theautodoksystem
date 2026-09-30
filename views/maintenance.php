<?php

define('APP_ACCESS', true);
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

$branding = function_exists('getSystemBrandingSettings') ? getSystemBrandingSettings() : [];
$systemLogoUrl = $branding['system_logo_url'] ?? (APP_URL . '/assets/images/logo.png');
$backUrl = function_exists('isLoggedIn') && isLoggedIn() ? routeUrl('dashboard') : routeUrl('login');
$backLabel = function_exists('isLoggedIn') && isLoggedIn() ? 'Back to Dashboard' : 'Back to Login';

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance | <?php echo APP_NAME; ?></title>
    <style>
        :root {
            --bg-1: #111111;
            --bg-2: #1f1f1f;
            --bg-3: #4b4b4b;
            --text-strong: #ffffff;
            --text: #e5e7eb;
            --text-light: #ffffff;
            --button-bg: #7a7a7a;
            --button-hover: #5a5a5a;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #111111 0%, #1f1f1f 35%, #4b4b4b 100%);
            color: var(--text-strong);
            font-family: "Courier New", "Lucida Console", monospace;
            padding: 24px;
        }

        .maintenance-wrap {
            width: min(760px, 100%);
            text-align: center;
        }

        .maintenance-box {
            background: transparent;
            border: none;
            border-radius: 0;
            box-shadow: none;
            padding: 0;
            backdrop-filter: none;
        }

        .maintenance-logo {
            width: 92px;
            height: 92px;
            margin: 0 auto 24px;
            display: block;
            object-fit: contain;
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.18);
            box-shadow: none;
            padding: 10px;
        }

        h1 {
            margin: 0 0 18px;
            font-size: clamp(2.3rem, 6vw, 4.3rem);
            line-height: 1.1;
            letter-spacing: -0.05em;
            font-weight: 800;
            color: var(--text-strong);
        }

        p {
            margin: 0 auto 10px;
            max-width: 620px;
            font-size: clamp(1rem, 2vw, 1.2rem);
            line-height: 1.7;
            color: var(--text);
        }

        .maintenance-actions {
            margin-top: 28px;
            display: flex;
            justify-content: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .btn-back {
            position: fixed;
            right: 28px;
            bottom: 28px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 12px 22px;
            border-radius: 12px;
            background: var(--button-bg);
            color: var(--text-light);
            text-decoration: none;
            font-weight: 600;
            transition: 0.2s ease;
            border: none;
            cursor: pointer;
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.22);
        }

        .btn-back:hover {
            background: var(--button-hover);
            color: var(--text-light);
            text-decoration: none;
        }

        .signature {
            margin-top: 22px;
            color: var(--text-strong);
            font-weight: 700;
            letter-spacing: 0.08em;
            font-size: 1.05rem;
        }
    </style>
</head>
<body>
    <div class="maintenance-wrap">
        <div class="maintenance-box">
            <img class="maintenance-logo" src="<?php echo escape($systemLogoUrl); ?>" alt="System Logo" onerror="this.onerror=null; this.src='<?php echo APP_URL; ?>/assets/images/logo.png';">
            <h1>We’ll Be Back Soon</h1>
            <p>We’re currently performing some updates to improve your experience. Please check back again soon. <br>Thank you for your patience!</p>
            <div class="maintenance-actions">
                <a href="<?php echo escape($backUrl); ?>" class="btn-back">Back</a>
            </div>
            <div class="signature">- owwkxi</div>
        </div>
    </div>
</body>
</html>
