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
            <p class="text-muted mb-0">Tap a staff card to record time in or time out.</p>
        </div>
    </div>

    <div class="nfc-date-strip" aria-label="Current date">
        <span class="nfc-date-track"><?php echo escape(date('l, F d, Y')); ?></span>
    </div>
    <div class="card nfc-tap-card mb-3">
        <div class="card-body text-center p-5">
            <div class="nfc-tap-icon"><i class="bi bi-phone-vibrate"></i></div>
            <h2 class="h4">Ready for the next tap</h2>
            <p class="opacity-75 mb-4">Use a Web NFC-enabled device or a USB NFC reader.</p>
            <button type="button" class="btn btn-light btn-lg px-4" id="startNfcButton">
                <i class="bi bi-broadcast-pin me-2"></i>Enable device NFC
            </button>
            <div class="small opacity-75 mt-3" id="nfcStatus">USB readers can type the staff ID below automatically.</div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <label for="nfcIdentifier" class="form-label fw-semibold">Staff ID</label>
            <div class="input-group">
                <input id="nfcIdentifier" class="form-control nfc-reader-input" autocomplete="off"
                       inputmode="numeric" pattern="\d{5}" minlength="5" maxlength="5"
                       placeholder="Enter 5-digit staff ID">
                <button class="btn btn-dark px-4" type="button" id="submitNfcButton">Record tap</button>
            </div>
            <div id="nfcResult" class="nfc-result d-flex align-items-center justify-content-center text-center mt-3 p-3" aria-live="polite">
                Waiting for a tap…
            </div>
            <div class="nfc-help text-center mt-2">Your staff ID is the 5-digit number assigned to your staff account.</div>
        </div>
    </div>
</div>
</main>

<script>
(() => {
    const input = document.getElementById('nfcIdentifier');
    const submit = document.getElementById('submitNfcButton');
    const result = document.getElementById('nfcResult');
    const status = document.getElementById('nfcStatus');
    let processing = false;

    const showResult = (message, type = '') => {
        result.className = `nfc-result d-flex align-items-center justify-content-center text-center mt-3 p-3 ${type}`;
        result.textContent = message;
    };

    const recordTap = async () => {
        if (processing || !input.value.trim()) return;
        processing = true;
        submit.disabled = true;
        showResult('Recording tap…');
        try {
            const body = new FormData();
            body.append('identifier', input.value.trim());
            const response = await fetch(`${window.APP_URL}/api/attendance.php`, { method: 'POST', body });
            const data = await response.json();
            if (!response.ok || !data.success) throw new Error(data.message || 'Unable to record tap');
            showResult(data.message, 'success');
            input.value = '';
        } catch (error) {
            showResult(error.message, 'error');
        } finally {
            processing = false;
            submit.disabled = false;
            input.focus();
            input.addEventListener('input', () => {
                input.value = input.value.replace(/\D/g, '').slice(0, 5);
            });
        }
    };

    submit.addEventListener('click', recordTap);
    input.addEventListener('keydown', event => {
        if (event.key === 'Enter') {
            event.preventDefault();
            recordTap();
        }
    });
    input.focus();

    document.getElementById('startNfcButton').addEventListener('click', async () => {
        if (!('NDEFReader' in window)) {
            status.textContent = 'Web NFC is not available here. Use a USB reader or enter the staff ID.';
            return;
        }
        try {
            const reader = new NDEFReader();
            await reader.scan();
            status.textContent = 'Device NFC enabled. Tap a staff card.';
            reader.addEventListener('reading', ({ message }) => {
                for (const record of message.records) {
                    if (record.recordType === 'text' || record.recordType === 'url' || record.recordType === 'mime') {
                        try {
                            const value = new TextDecoder(record.encoding || 'utf-8').decode(record.data);
                            const staffId = value.match(/\b\d{5}\b/);
                            input.value = staffId ? staffId[0] : value.replace(/^.*?:\/\//, '').trim();
                            recordTap();
                            return;
                        } catch (_) {}
                    }
                }
            });
        } catch (error) {
            status.textContent = error.message || 'Unable to enable device NFC.';
        }
    });
})();
</script>

</body>
</html>
