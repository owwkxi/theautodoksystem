<?php
define('APP_ACCESS', true);
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/Database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../controllers/AuthController.php';

if (isLoggedIn()) {
    redirect(APP_URL . '/views/dashboard/index.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $error = 'Invalid request. Please try again.';
    } else {
        $username = sanitize($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        $authController = new AuthController();
        $result = $authController->login($username, $password);

        if ($result['success']) {
            redirect(APP_URL . '/views/dashboard/index.php');
        } else {
            $error = $result['message'];
        }
    }
}

$csrfToken = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/css/style.css?v=<?php echo time(); ?>">
    <style>
        /* Login-specific styles */
        .login-card {
            background: #fff;
            border-radius: 16px;
            padding: 36px 34px 32px;
            width: 100%;
            max-width: 310px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.18);
        }

        .login-logo {
            text-align: center;
            margin-bottom: 24px;
        }

        .login-logo img {
            width: 80px;
            height: 80px;
            display: inline-block;
        }

        .field-label {
            font-size: 13px;
            font-weight: 500;
            color: #333;
            margin-bottom: 6px;
            display: block;
        }

        .input-wrap {
            display: flex;
            align-items: center;
            background: #f5f5f5;
            border: 1.5px solid #e0e0e0;
            border-radius: 8px;
            padding: 0 12px;
            margin-bottom: 16px;
            transition: border-color 0.18s;
        }

        .input-wrap:focus-within {
            border-color: #888;
            background: #fafafa;
        }

        .input-wrap i {
            color: #999;
            font-size: 15px;
            margin-right: 8px;
            flex-shrink: 0;
        }

        .input-wrap input {
            border: none;
            background: transparent;
            outline: none;
            width: 100%;
            padding: 10px 0;
            font-size: 14px;
            color: #333;
        }

        .input-wrap input::placeholder { color: #bbb; }

        .error-alert {
            background: #fff0f0;
            border: 1px solid #ffcccc;
            border-radius: 8px;
            padding: 10px 14px;
            margin-bottom: 16px;
            font-size: 13px;
            color: #cc0000;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-signin {
            width: 100%;
            padding: 11px;
            background: #7a7a7a;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.18s;
            margin-top: 4px;
        }

        .btn-signin:hover { background: #5a5a5a; }
    </style>
</head>
<body class="login-body">

    <div class="login-card">

        <!-- Logo -->
        <div class="login-logo">
            <img src="<?php echo APP_URL; ?>/assets/images/logo.png" alt="The Autodok Logo">
        </div>

        <?php if ($error): ?>
        <div class="error-alert">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <?php echo escape($error); ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">

            <label class="field-label" for="username">Username</label>
            <div class="input-wrap">
                <i class="bi bi-person"></i>
                <input type="text" id="username" name="username" placeholder="Username" required autofocus>
            </div>

            <label class="field-label" for="password">Password</label>
            <div class="input-wrap">
                <i class="bi bi-lock"></i>
                <input type="password" id="password" name="password" placeholder="••••••••" required>
            </div>

            <button type="submit" class="btn-signin">Sign In</button>
        </form>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
