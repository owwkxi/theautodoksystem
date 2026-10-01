<?php
define('APP_ACCESS', true);
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/Database.php';
require_once __DIR__ . '/../../includes/functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>NFC Attendance — The Autodok</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">

<style>
.nfc-kiosk { width: min(100%, 1120px); margin: 0 auto; font-family: "Courier New", Courier, monospace; }
.nfc-logo { display: block; width: 72px; height: 72px; margin: 0 auto .55rem; object-fit: contain; }
.nfc-kiosk h1, .nfc-kiosk h2, .nfc-kiosk label, .nfc-kiosk button { letter-spacing: -.01em; }
.nfc-date-strip { width: 100%; overflow: hidden; padding: .7rem 0; margin-bottom: .9rem; border-top: 1px solid #dfe2e5; border-bottom: 1px solid #dfe2e5; color: #72777d; font-size: .85rem; font-weight: 600; letter-spacing: .08em; text-transform: uppercase; white-space: nowrap; }
.nfc-date-track { display: inline-block; min-width: 100%; padding-left: 100%; animation: nfc-date-scroll 18s linear infinite; }
@keyframes nfc-date-scroll {
    from { transform: translateX(0); }
    to { transform: translateX(-100%); }
}
.nfc-tap-card { border: 0; border-radius: 24px; background: radial-gradient(circle at 50% 0%, #535860, #222427 62%); color: #fff; box-shadow: 0 18px 40px rgba(0,0,0,.16); }
.nfc-tap-icon { width: 82px; height: 82px; display: grid; place-items: center; border-radius: 50%; margin: 0 auto .8rem; background: rgba(255,255,255,.12); border: 1px solid rgba(255,255,255,.15); font-size: 2.6rem; }
.nfc-reader-input { height: 52px; font-size: 1.05rem; text-align: center; letter-spacing: .08em; }
.nfc-result { min-height: 64px; border-radius: 14px; background: #f8f9fa; font-weight: 600; }
.nfc-result.success { background: #eaf7ef; color: #176b38; }
.nfc-result.error { background: #fff0f0; color: #9b2424; }
.nfc-help { color: #858b91; font-size: .84rem; }
.nfc-kiosk > .mb-4 { margin-bottom: 1rem !important; }
.nfc-tap-card .card-body { padding: 2rem !important; }
.nfc-kiosk .card.border-0 .card-body { padding: 1.25rem !important; }
@media (max-width: 576px) {
    .nfc-kiosk { max-width: 100%; }
    .nfc-tap-card .card-body { padding: 1.5rem 1rem !important; }
    .nfc-reader-input { min-width: 0; }
    .nfc-reader-input::placeholder { font-size: .9rem; }
    .nfc-reader-input + button { padding-left: .8rem !important; padding-right: .8rem !important; }
}
</style>

<main class="container py-2 py-md-3">
<div class="nfc-kiosk">
    <img class="nfc-logo" src="<?php echo escape(APP_URL . '/assets/images/logo.png'); ?>" alt="The Autodok logo">
    <div class="mb-4">
        <div>
            <h1 class="display-6 mb-2 fw-bold">Tap Attendance</h1>
            <p class="text-muted mb-0">Tap a registered staff card to record time in or time out.</p>
        </div>
    </div>

    <div class="nfc-date-strip" aria-label="Current date">
        <span class="nfc-date-track"><?php echo escape(date('l, F d, Y')); ?></span>
    </div>
    <div class="card nfc-tap-card mb-3">
        <div class="card-body text-center p-5">
            <div class="nfc-tap-icon"><i class="bi bi-phone-vibrate"></i></div>
            <h2 class="h4">Ready for the next tap</h2>
            <p class="opacity-75 mb-2">Tap a card on the connected reader.</p>
            <div class="small opacity-75" id="nfcStatus">Ready for USB keyboard reader input.</div>
            <button class="btn btn-light btn-sm mt-3" type="button" id="connectAcr122Button">
                <i class="bi bi-usb-drive me-1"></i>Connect ACR122 PC/SC
            </button>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <label for="nfcIdentifier" class="form-label fw-semibold">Staff ID or NFC card UID</label>
            <div class="input-group">
                <input id="nfcIdentifier" class="form-control nfc-reader-input" autocomplete="off"
                       maxlength="64" placeholder="Scan card or enter staff ID">
                <button class="btn btn-dark px-4" type="button" id="submitNfcButton">Record tap</button>
            </div>
            <div id="nfcResult" class="nfc-result d-flex align-items-center justify-content-center text-center mt-3 p-3" aria-live="polite">
                Waiting for a tap…
            </div>
            <div class="nfc-help text-center mt-2">Register each card UID in Staff Management first. ACR122 readers require the local PC/SC bridge to be running on this kiosk computer.</div>
        </div>
    </div>
</div>
</main>

<script src="<?php echo escape(APP_URL . '/assets/js/nfc-keyboard-reader.js?v=' . time()); ?>"></script>
<script>
    window.NFC_BRIDGE_PROXY_URL = <?php echo json_encode(APP_URL . '/api/nfc-bridge.php?route=', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>;
</script>
<script src="<?php echo escape(APP_URL . '/assets/js/nfc-pcsc-bridge.js?v=' . time()); ?>"></script>
<script>
(() => {
    const appUrl = <?php echo json_encode(APP_URL, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>;
    const input = document.getElementById('nfcIdentifier');
    const submit = document.getElementById('submitNfcButton');
    const connectAcr122 = document.getElementById('connectAcr122Button');
    const result = document.getElementById('nfcResult');
    const status = document.getElementById('nfcStatus');
    let processing = false;
    let stopAcr122Reader = null;

    const showResult = (message, type = '') => {
        result.className = `nfc-result d-flex align-items-center justify-content-center text-center mt-3 p-3 ${type}`;
        result.textContent = message;
    };

    const recordTap = async (identifier = input.value.trim()) => {
        if (processing || !identifier) return;
        processing = true;
        submit.disabled = true;
        showResult('Recording tap…');
        try {
            const body = new FormData();
            body.append('identifier', identifier);
            const response = await fetch(`${appUrl}/api/attendance.php`, { method: 'POST', body });
            const data = await response.json();
            if (!response.ok || !data.success) throw new Error(data.message || 'Unable to record tap');
            showResult(data.message, 'success');
            status.textContent = data.message;
            input.value = '';
        } catch (error) {
            showResult(error.message, 'error');
            status.textContent = error.message;
        } finally {
            if (input.value === identifier) input.value = '';
            processing = false;
            submit.disabled = false;
            input.focus();
        }
    };

    submit.addEventListener('click', () => recordTap());
    input.addEventListener('keydown', event => {
        if (event.key === 'Enter') {
            event.preventDefault();
            recordTap(input.value.trim());
        }
    });
    const focusReaderInput = () => {
        if (document.activeElement !== input) input.focus();
    };
    window.addEventListener('focus', focusReaderInput);
    document.addEventListener('visibilitychange', () => {
        if (!document.hidden) focusReaderInput();
    });
    focusReaderInput();

    connectAcr122.addEventListener('click', () => {
        if (stopAcr122Reader) return;
        connectAcr122.disabled = true;
        status.textContent = 'Connecting to the local ACR122 bridge…';
        stopAcr122Reader = window.NfcPcscBridge.start({
            onStatus: (message) => {
                status.textContent = message;
                if (message.startsWith('Cannot connect')) {
                    stopAcr122Reader?.();
                    stopAcr122Reader = null;
                    connectAcr122.disabled = false;
                }
            },
            onUid: (uid) => {
                status.textContent = 'ACR122 card detected. Recording attendance…';
                recordTap(uid);
            },
        });
    });

    window.NfcKeyboardReader.attach({
        getInput: () => input,
        allowStaffId: true,
        onScan: (uid) => {
            status.textContent = 'Card UID detected. Recording attendance…';
            recordTap(uid);
        },
        onInput: (value) => {
            status.textContent = `USB reader sent: ${value.slice(-64)}`;
        },
        onInvalid: (value) => {
            if (value) status.textContent = `Reader sent unsupported data: ${value.slice(-64)}`;
        },
    });
})();
</script>

</body>
</html>
