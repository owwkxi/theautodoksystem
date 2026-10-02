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
.nfc-tap-icon[hidden] { display: none; }
.nfc-staff-card { display: flex; align-items: center; justify-content: center; gap: 1.5rem; width: min(100%, 620px); margin: 1.25rem auto .25rem; text-align: left; }
.nfc-staff-photo, .nfc-staff-photo-fallback { width: 148px; height: 148px; flex: 0 0 148px; border-radius: 50%; object-fit: cover; border: 3px solid rgba(255,255,255,.8); }
.nfc-staff-photo-fallback { display: grid; place-items: center; background: rgba(255,255,255,.12); color: #fff; font-size: 3rem; }
.nfc-staff-photo-fallback[hidden] { display: none; }
.nfc-staff-photo[hidden] { display: none; }
.nfc-staff-name { margin: 0 0 .25rem; font-size: 1.5rem; font-weight: 700; }
.nfc-staff-role { margin: 0; color: rgba(255,255,255,.75); text-transform: capitalize; }
.nfc-attendance-action { display: inline-block; margin-top: .75rem; padding: .35rem .85rem; border-radius: 999px; background: rgba(255,255,255,.14); color: #fff; font-weight: 700; text-transform: capitalize; }
.nfc-kiosk > .mb-4 { margin-bottom: 1rem !important; }
.nfc-tap-card .card-body { padding: 2rem !important; }
.nfc-attendance-list { border: 0; border-radius: 18px; }
.nfc-attendance-list .table { margin-bottom: 0; }
.nfc-attendance-list .table th { color: #6c757d; font-size: .8rem; font-weight: 700; text-transform: uppercase; white-space: nowrap; }
.nfc-attendance-status { text-transform: capitalize; }
@media (max-width: 576px) {
    .nfc-kiosk { max-width: 100%; }
    .nfc-tap-card .card-body { padding: 1.5rem 1rem !important; }
    .nfc-staff-card { justify-content: flex-start; gap: 1rem; }
    .nfc-staff-photo, .nfc-staff-photo-fallback { width: 104px; height: 104px; flex-basis: 104px; }
    .nfc-staff-name { font-size: 1.25rem; }
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
            <div class="nfc-tap-icon" id="nfcTapIcon"><i class="bi bi-phone-vibrate"></i></div>
            <div id="nfcReadyContent">
                <h2 class="h4">Ready for the next tap</h2>
                <p class="opacity-75 mb-0">Tap ID card</p>
                <div class="small opacity-75" id="nfcStatus" aria-live="polite"></div>
            </div>
            <div class="nfc-staff-card" id="nfcStaffCard" hidden aria-live="polite">
                <img class="nfc-staff-photo" id="nfcStaffPhoto" alt="" hidden>
                <div class="nfc-staff-photo-fallback" id="nfcStaffPhotoFallback" aria-hidden="true"><i class="bi bi-person-fill"></i></div>
                <div>
                    <h2 class="nfc-staff-name" id="nfcStaffName"></h2>
                    <p class="nfc-staff-role" id="nfcStaffRole"></p>
                    <div class="nfc-attendance-action" id="nfcStaffAction"></div>
                    <p class="small opacity-75 mt-2 mb-0" id="nfcStaffTime"></p>
                </div>
            </div>
        </div>
    </div>
    <section class="card nfc-attendance-list shadow-sm mt-3" aria-labelledby="staffAttendanceTitle">
        <div class="card-body p-3 p-md-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="d-flex align-items-center gap-2">
                    <h2 class="h5 mb-0" id="staffAttendanceTitle">Staff Attendance Today</h2>
                    <span class="badge rounded-pill bg-transparent text-secondary border border-secondary-subtle" id="staffAttendancePeriod"></span>
                </div>
                <span class="small text-muted" id="staffAttendanceDate"></span>
            </div>
            <div class="table-responsive">
                <table class="table table-sm align-middle">
                    <thead>
                        <tr>
                            <th scope="colgroup" colspan="2">Staff</th>
                        </tr>
                    </thead>
                    <tbody id="staffAttendanceRows">
                        <tr><td class="text-center text-muted py-3" colspan="2">Loading staff attendance…</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
    <input id="nfcIdentifier" type="text" autocomplete="off" maxlength="64" aria-label="NFC card UID" hidden>
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
    const status = document.getElementById('nfcStatus');
    const attendanceRows = document.getElementById('staffAttendanceRows');
    const attendanceDate = document.getElementById('staffAttendanceDate');
    const attendancePeriod = document.getElementById('staffAttendancePeriod');
    const readyContent = document.getElementById('nfcReadyContent');
    const staffCard = document.getElementById('nfcStaffCard');
    const tapIcon = document.getElementById('nfcTapIcon');
    const staffPhoto = document.getElementById('nfcStaffPhoto');
    const staffPhotoFallback = document.getElementById('nfcStaffPhotoFallback');
    let processing = false;
    let resetStaffCardTimer;
    let stopAcr122Reader = null;
    let retryAcr122Reader;

    const appendCell = (row, value, className = '') => {
        const cell = document.createElement('td');
        cell.textContent = value;
        if (className) cell.className = className;
        row.appendChild(cell);
    };

    const refreshTechnicianAttendance = async () => {
        const response = await fetch(`${appUrl}/api/attendance.php`, { cache: 'no-store' });
        const data = await response.json();
        if (!response.ok || !data.success) {
            throw new Error(data.message || 'Unable to load staff attendance');
        }

        attendanceRows.replaceChildren();
        attendanceDate.textContent = new Date(`${data.date}T00:00:00`).toLocaleDateString();
        attendancePeriod.textContent = data.period;
        if (!data.staff.length) {
            const row = document.createElement('tr');
            const cell = document.createElement('td');
            cell.colSpan = 2;
            cell.className = 'text-center text-muted py-3';
            cell.textContent = 'No staff attendance recorded yet.';
            row.appendChild(cell);
            attendanceRows.appendChild(row);
            return;
        }

        for (const member of data.staff) {
            const row = document.createElement('tr');
            appendCell(row, member.name);
            appendCell(row, (member.role || '').replaceAll('_', ' '), 'text-end text-muted nfc-attendance-status');
            attendanceRows.appendChild(row);
        }
    };

    const showStaffCard = (staff) => {
        window.clearTimeout(resetStaffCardTimer);
        document.getElementById('nfcStaffName').textContent = staff.name || '';
        document.getElementById('nfcStaffRole').textContent = (staff.role || '').replaceAll('_', ' ');
        document.getElementById('nfcStaffAction').textContent = (staff.action || '').replaceAll('_', ' ');
        document.getElementById('nfcStaffTime').textContent = staff.time || '';
        staffPhoto.hidden = true;
        staffPhotoFallback.hidden = false;
        if (staff.photo) {
            staffPhoto.onload = () => {
                staffPhoto.hidden = false;
                staffPhotoFallback.hidden = true;
            };
            staffPhoto.onerror = () => {
                staffPhoto.hidden = true;
                staffPhotoFallback.hidden = false;
            };
            staffPhoto.src = staff.photo;
        } else {
            staffPhoto.removeAttribute('src');
        }
        readyContent.hidden = true;
        tapIcon.hidden = true;
        staffCard.hidden = false;
        resetStaffCardTimer = window.setTimeout(() => {
            staffCard.hidden = true;
            tapIcon.hidden = false;
            readyContent.hidden = false;
            status.textContent = '';
        }, 6000);
    };

    const recordTap = async (identifier = input.value.trim()) => {
        if (processing || !identifier) return;
        processing = true;
        window.clearTimeout(resetStaffCardTimer);
        staffCard.hidden = true;
        readyContent.hidden = false;
        status.textContent = 'Recording attendance…';
        try {
            const body = new FormData();
            body.append('identifier', identifier);
            const response = await fetch(`${appUrl}/api/attendance.php`, { method: 'POST', body });
            const data = await response.json();
            if (!response.ok || !data.success) {
                if (data.staff) {
                    showStaffCard({ ...data.staff, action: data.action });
                    void refreshTechnicianAttendance().catch((error) => {
                        console.error('Unable to refresh staff attendance:', error);
                    });
                }
                status.textContent = data.message || 'Unable to record tap';
                throw new Error(data.message || 'Unable to record tap');
            }
            if (data.staff) showStaffCard({ ...data.staff, action: data.action });
            status.textContent = data.message;
            void refreshTechnicianAttendance().catch((error) => {
                console.error('Unable to refresh staff attendance:', error);
            });
            input.value = '';
        } catch (error) {
            if (!staffCard.hidden) {
                status.textContent = 'Attendance already recorded.';
            } else {
                status.textContent = error.message;
            }
        } finally {
            if (input.value === identifier) input.value = '';
            processing = false;
        }
    };

    void refreshTechnicianAttendance().catch((error) => {
        console.error('Unable to load staff attendance:', error);
        attendanceRows.replaceChildren();
        const row = document.createElement('tr');
        const cell = document.createElement('td');
        cell.colSpan = 3;
        cell.className = 'text-center text-danger py-3';
        cell.textContent = 'Unable to load staff attendance.';
        row.appendChild(cell);
        attendanceRows.appendChild(row);
    });
    window.setInterval(() => {
        void refreshTechnicianAttendance().catch((error) => {
            console.error('Unable to refresh staff attendance:', error);
        });
    }, 30000);

    const connectAcr122Reader = () => {
        if (stopAcr122Reader) return;
        window.clearTimeout(retryAcr122Reader);
        status.textContent = 'Connecting to the local ACR122 bridge…';
        stopAcr122Reader = window.NfcPcscBridge.start({
            onStatus: (message) => {
            status.textContent = message.startsWith('ACR122 reader ready.') ? '' : message;
                if (message.startsWith('Cannot connect')) {
                    stopAcr122Reader?.();
                    stopAcr122Reader = null;
                    retryAcr122Reader = window.setTimeout(connectAcr122Reader, 3000);
                }
            },
            onUid: (uid) => {
                status.textContent = 'ACR122 card detected. Recording attendance…';
                recordTap(uid);
            },
        });
    };

    connectAcr122Reader();

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
