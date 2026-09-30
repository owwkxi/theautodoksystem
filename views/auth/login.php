<?php
define('APP_ACCESS', true);
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/Database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../controllers/AuthController.php';

if (isLoggedIn()) {
    redirect(routeUrl('dashboard'));
}

$error = '';
$shopOptions = getShopOptions();
$defaultShopKey = isset($shopOptions['autodok_main']) ? 'autodok_main' : (array_key_first($shopOptions) ?: '');
$selectedShopKey = $_SESSION['shop_key'] ?? $defaultShopKey;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $requestedShopKey = trim((string)($_GET['shop'] ?? ''));
    if ($requestedShopKey === '' || !isset($shopOptions[$requestedShopKey])) {
        $requestedShopKey = $defaultShopKey;
    }

    $selectedShop = resolveShopOption($requestedShopKey);
    $_SESSION['shop_key'] = $selectedShop['key'];
    $_SESSION['shop_name'] = $selectedShop['name'];
    $_SESSION['shop_db_name'] = $selectedShop['db_name'];
    $selectedShopKey = $selectedShop['key'];
} else {
    $selectedShop = resolveShopOption($_SESSION['shop_key'] ?? $defaultShopKey);
    $selectedShopKey = $selectedShop['key'] ?? $defaultShopKey;
}

$shopBrandingByKey = [];

foreach ($shopOptions as $shopKey => $shopMeta) {
    $branding = function_exists('getSystemBrandingSettings') ? getSystemBrandingSettings($shopKey) : [];
    $shopBrandingByKey[$shopKey] = [
        'name' => $shopMeta['name'] ?? APP_NAME,
        'logo_url' => $branding['system_logo_url'] ?? (APP_URL . '/assets/images/logo.png'),
    ];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $error = 'Invalid request. Please try again.';
    } else {
        $postedShop = resolveShopOption($_POST['shop_key'] ?? '');
        $_SESSION['shop_key'] = $postedShop['key'];
        $_SESSION['shop_name'] = $postedShop['name'];
        $_SESSION['shop_db_name'] = $postedShop['db_name'];
        $selectedShop = $postedShop;

        $loginId = sanitize($_POST['login_id'] ?? '');
        $password = $_POST['password'] ?? '';

        $authController = new AuthController();
        $result = $authController->login($loginId, $password);

        if ($result['success']) {
            redirect(routeUrl('dashboard'));
        } else {
            $error = $result['message'];
        }
    }
}

$csrfToken = generateCSRFToken();
$selectedBranding = $shopBrandingByKey[$selectedShopKey] ?? [];
$systemLogoUrl = $selectedBranding['logo_url'] ?? (APP_URL . '/assets/images/logo.png');
$displayShopName = $_SESSION['shop_name'] ?? ($selectedShop['name'] ?? APP_NAME);
$themeClass = ($selectedShopKey === 'autodok_prime') ? 'theme-prime' : 'theme-main';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — <?php echo escape($displayShopName); ?></title>
    <link id="loginFavicon" rel="icon" type="image/png" href="<?php echo escape($systemLogoUrl); ?>">
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

        .login-shop-name {
            margin-top: 8px;
            font-size: 15px;
            font-weight: 700;
            color: #1f1f1f;
            text-align: center;
            line-height: 1.3;
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

        .password-wrap {
            padding-right: 44px;
            position: relative;
        }

        .password-toggle {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            border: none;
            background: transparent;
            color: #777;
            cursor: pointer;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .password-toggle:hover {
            color: #333;
        }

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

        .shop-select-wrap {
            margin-bottom: 14px;
        }

        .shop-select-wrap select {
            border: 1.5px solid #e0e0e0;
            border-radius: 8px;
            padding: 9px 12px;
            font-size: 13px;
            color: #333;
            background: #f7f7f7;
        }

        .login-shell {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            padding: 24px;
        }

        .customer-check-title {
            font-size: 14px;
            font-weight: 700;
            color: #1b1b1b;
            margin-bottom: 0;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(255, 255, 255, 0.88);
            border: 1px solid rgba(0, 0, 0, 0.15);
            border-radius: 999px;
            padding: 7px 12px;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
        }

        .customer-check-input {
            font-size: 13px;
            background: transparent;
            border-color: rgba(255, 255, 255, 0.6);
            color: #1f1f1f;
        }

        .customer-check-input::placeholder {
            color: rgba(31, 31, 31, 0.7);
        }

        .customer-check-floating {
            position: fixed;
            top: 16px;
            left: 16px;
            width: min(420px, calc(100vw - 32px));
            z-index: 12;
            padding: 0;
        }

        .customer-check-panel {
            display: none;
            margin-top: 8px;
            background: rgba(255, 255, 255, 0.92);
            border: 1px solid rgba(0, 0, 0, 0.12);
            border-radius: 12px;
            padding: 10px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.12);
            max-height: 60vh;
            overflow: auto;
        }

        .customer-check-floating:hover .customer-check-panel,
        .customer-check-floating.active .customer-check-panel {
            display: block;
        }

        #customerStatusSearchBtn {
            background: transparent;
            border-color: rgba(255, 255, 255, 0.6);
            color: #1f1f1f;
        }

        .customer-results {
            margin-top: 10px;
            max-height: 250px;
            overflow: auto;
            border: 1px solid #efefef;
            border-radius: 8px;
            display: none;
        }

        .customer-result-row {
            padding: 10px 12px;
            border-bottom: 1px solid #f3f3f3;
            font-size: 12px;
        }

        .customer-result-row:last-child {
            border-bottom: none;
        }

        .customer-result-meta {
            color: #666;
            font-size: 11px;
        }

        .customer-status-badge {
            font-size: 10px;
            font-weight: 600;
            padding: 3px 7px;
            border-radius: 999px;
            text-transform: capitalize;
        }

        .status-pending { background: #ececec; color: #333; }
        .status-ongoing { background: #dce8ff; color: #1147aa; }
        .status-under_inspection { background: #fde2e2; color: #9f2020; }
        .status-car_washing { background: #fff1c2; color: #7a4f00; }
        .status-completed { background: #ddf5e3; color: #146c2e; }
        .status-released { background: #d7f1e3; color: #0f5e36; }
        .status-returned_for_revision { background: #ffe8c8; color: #9b5a00; }
        .status-cancelled { background: #f0d8d8; color: #7e1b1b; }

        .customer-check-empty {
            font-size: 12px;
            color: #6a6a6a;
            padding: 10px 2px 2px;
            display: none;
        }

        .customer-check-error {
            font-size: 12px;
            color: #b42318;
            padding-top: 8px;
            display: none;
        }

        .customer-check-branch {
            margin-top: 8px;
            font-size: 11px;
            color: #4b4b4b;
        }

        @media (max-width: 768px) {
            .login-shell {
                align-items: flex-start;
                padding-top: 130px;
                padding-left: 14px;
                padding-right: 14px;
            }

            .customer-check-floating {
                position: fixed;
                top: 10px;
                left: 10px;
                width: auto;
                right: 10px;
                padding: 0;
            }

            .login-card {
                max-width: 100%;
                border-radius: 14px;
                padding: 26px 20px 22px;
            }

            .customer-check-title {
                width: 100%;
                justify-content: center;
                text-align: center;
            }
        }

        @media (max-width: 480px) {
            .customer-check-title {
                font-size: 13px;
                padding: 6px 10px;
            }

            .btn-signin {
                padding: 10px;
                font-size: 14px;
            }

            .login-logo img {
                width: 68px;
                height: 68px;
            }
        }
    </style>
</head>
<body class="login-body <?php echo escape($themeClass); ?>">

    <div class="customer-check-floating">
        <div class="customer-check-title" id="customerCheckTrigger">
            <i class="bi bi-search"></i>
            <span>Check Vehicle Status</span>
        </div>
        <div class="customer-check-panel" id="customerCheckPanel">
            <div class="input-group input-group-sm">
                <input
                    type="text"
                    id="customerStatusQuery"
                    class="form-control customer-check-input"
                    placeholder="Search plate, name, or JO ID">
                <button class="btn btn-outline-secondary" type="button" id="customerStatusSearchBtn">Search</button>
            </div>
            <div id="customerCheckBranch" class="customer-check-branch">
                Searching in: <?php echo escape($displayShopName); ?>
            </div>
            <div id="customerCheckError" class="customer-check-error"></div>
            <div id="customerCheckEmpty" class="customer-check-empty">No matching job orders found.</div>
            <div id="customerStatusResults" class="customer-results"></div>
        </div>
    </div>

    <div class="login-shell">

    <div class="login-card">

        <!-- Logo -->
        <div class="login-logo">
            <img id="loginShopLogo" src="<?php echo escape($systemLogoUrl); ?>" alt="The Autodok Logo" onerror="this.onerror=null;this.src='<?php echo APP_URL; ?>/assets/images/logo.png';">
            <div class="login-shop-name" id="loginShopName"><?php echo escape($displayShopName); ?></div>
        </div>

        <?php if ($error): ?>
        <div class="error-alert">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <?php echo escape($error); ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">

            <label class="field-label" for="shop_key">Shop</label>
            <div class="shop-select-wrap">
                <select id="shop_key" name="shop_key" class="form-select" required>
                    <?php foreach ($shopOptions as $shopKey => $shop): ?>
                        <?php $brandingMeta = $shopBrandingByKey[$shopKey] ?? []; ?>
                        <option
                            value="<?php echo escape($shopKey); ?>"
                            data-shop-name="<?php echo escape($shop['name'] ?? APP_NAME); ?>"
                            data-logo-url="<?php echo escape($brandingMeta['logo_url'] ?? (APP_URL . '/assets/images/logo.png')); ?>"
                            <?php echo (($selectedShop['key'] ?? '') === $shopKey) ? 'selected' : ''; ?>>
                            <?php echo escape($shop['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <label class="field-label" for="login_id">ID</label>
            <div class="input-wrap">
                <i class="bi bi-person"></i>
                <input type="text" id="login_id" name="login_id" required autofocus>
            </div>

            <label class="field-label" for="password">Password</label>
            <div class="input-wrap password-wrap">
                <i class="bi bi-lock"></i>
                <input type="password" id="password" name="password" required>
                <button type="button" class="password-toggle" aria-label="Show password" aria-pressed="false">
                    <i class="bi bi-eye"></i>
                </button>
            </div>

            <button type="submit" class="btn-signin">Sign In</button>
        </form>

    </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const passwordInput = document.getElementById('password');
            const toggleButton = document.querySelector('.password-toggle');
            const shopSelect = document.getElementById('shop_key');
            const shopNameEl = document.getElementById('loginShopName');
            const shopLogoEl = document.getElementById('loginShopLogo');
            const faviconEl = document.getElementById('loginFavicon');
            const fallbackLogo = <?php echo json_encode(APP_URL . '/assets/images/logo.png'); ?>;

            if (shopSelect && shopNameEl) {
                const syncShopName = function () {
                    const selectedOption = shopSelect.options[shopSelect.selectedIndex];
                    if (selectedOption) {
                        const selectedShopName = selectedOption.dataset.shopName || selectedOption.text;
                        const selectedLogoUrl = selectedOption.dataset.logoUrl || fallbackLogo;

                        shopNameEl.textContent = selectedShopName;
                        if (shopLogoEl) {
                            shopLogoEl.src = selectedLogoUrl;
                        }
                        if (document.body) {
                            document.body.classList.remove('theme-main', 'theme-prime');
                            document.body.classList.add(shopSelect.value === 'autodok_prime' ? 'theme-prime' : 'theme-main');
                        }
                        if (faviconEl) {
                            const iconUrl = selectedLogoUrl.indexOf('?') === -1
                                ? (selectedLogoUrl + '?v=' + Date.now())
                                : (selectedLogoUrl + '&v=' + Date.now());
                            faviconEl.href = iconUrl;
                        }
                        document.title = 'Login — ' + selectedShopName;
                    }
                };

                shopSelect.addEventListener('change', syncShopName);
                syncShopName();
            }

            if (!passwordInput || !toggleButton) {
                return;
            }

            toggleButton.addEventListener('click', function () {
                const isPassword = passwordInput.type === 'password';
                passwordInput.type = isPassword ? 'text' : 'password';

                const icon = this.querySelector('i');
                icon.className = isPassword ? 'bi bi-eye-slash' : 'bi bi-eye';

                this.setAttribute('aria-label', isPassword ? 'Hide password' : 'Show password');
                this.setAttribute('aria-pressed', String(isPassword));
            });

            const floating = document.querySelector('.customer-check-floating');
            const trigger = document.getElementById('customerCheckTrigger');
            const queryInput = document.getElementById('customerStatusQuery');
            const searchBtn = document.getElementById('customerStatusSearchBtn');
            const resultsWrap = document.getElementById('customerStatusResults');
            const emptyText = document.getElementById('customerCheckEmpty');
            const errorText = document.getElementById('customerCheckError');
            const branchText = document.getElementById('customerCheckBranch');

            if (floating && trigger) {
                trigger.addEventListener('click', function (ev) {
                    ev.stopPropagation();
                    floating.classList.toggle('active');
                    if (floating.classList.contains('active') && queryInput) {
                        queryInput.focus();
                    }
                });

                document.addEventListener('click', function (ev) {
                    if (!floating.contains(ev.target)) {
                        floating.classList.remove('active');
                    }
                });
            }

            if (!queryInput || !searchBtn || !resultsWrap || !emptyText || !errorText) {
                return;
            }

            function statusBadgeClass(status) {
                return 'status-' + String(status || '').toLowerCase();
            }

            function showError(message) {
                errorText.textContent = message || 'Unable to search right now.';
                errorText.style.display = 'block';
            }

            function clearMessages() {
                errorText.style.display = 'none';
                emptyText.style.display = 'none';
            }

            function clearResultsForBranchChange() {
                clearMessages();
                if (resultsWrap) {
                    resultsWrap.innerHTML = '';
                    resultsWrap.style.display = 'none';
                }
            }

            function renderRows(rows) {
                if (!Array.isArray(rows) || rows.length === 0) {
                    resultsWrap.style.display = 'none';
                    emptyText.style.display = 'block';
                    return;
                }

                resultsWrap.innerHTML = rows.map((row) => {
                    const jo = row.job_order_number || 'N/A';
                    const name = row.customer_name || 'N/A';
                    const plate = row.plate_number || 'N/A';
                    const vehicle = [row.brand, row.model].filter(Boolean).join(' ') || 'N/A';
                    const status = row.status || 'unknown';
                    const createdAt = row.created_at
                        ? new Date(row.created_at).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })
                        : 'N/A';

                    return `
                        <div class="customer-result-row">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <strong>${jo}</strong>
                                <span class="customer-status-badge ${statusBadgeClass(status)}">${String(status).replaceAll('_', ' ')}</span>
                            </div>
                            <div>${name}</div>
                            <div class="customer-result-meta">Plate: ${plate} | Vehicle: ${vehicle} | Date: ${createdAt}</div>
                        </div>
                    `;
                }).join('');

                resultsWrap.style.display = 'block';
            }

            async function runCustomerLookup() {
                clearMessages();
                const q = (queryInput.value || '').trim();
                if (q.length < 2) {
                    showError('Enter at least 2 characters to search.');
                    resultsWrap.style.display = 'none';
                    return;
                }

                searchBtn.disabled = true;
                searchBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

                try {
                    const selectedShopKey = shopSelect ? shopSelect.value : '';
                    const searchUrl = `<?php echo APP_URL; ?>/api/customer_vehicle_status.php?q=${encodeURIComponent(q)}&shop_key=${encodeURIComponent(selectedShopKey)}`;
                    const res = await fetch(searchUrl, {
                        method: 'GET',
                        cache: 'no-store'
                    });
                    const data = await res.json();

                    if (!res.ok || !data.success) {
                        showError(data.message || 'Search failed.');
                        resultsWrap.style.display = 'none';
                        return;
                    }

                    if (branchText && data.shop_name) {
                        branchText.textContent = 'Searching in: ' + data.shop_name;
                    }

                    renderRows(data.data || []);
                } catch (e) {
                    showError('Network error while searching.');
                    resultsWrap.style.display = 'none';
                } finally {
                    searchBtn.disabled = false;
                    searchBtn.textContent = 'Search';
                }
            }

            searchBtn.addEventListener('click', runCustomerLookup);
            if (shopSelect) {
                shopSelect.addEventListener('change', function () {
                    const selectedOption = shopSelect.options[shopSelect.selectedIndex];
                    if (branchText && selectedOption) {
                        branchText.textContent = 'Searching in: ' + (selectedOption.dataset.shopName || selectedOption.text || 'Selected branch');
                    }
                    clearResultsForBranchChange();
                });
            }
            queryInput.addEventListener('keydown', function (ev) {
                if (ev.key === 'Enter') {
                    ev.preventDefault();
                    runCustomerLookup();
                }
            });

        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
