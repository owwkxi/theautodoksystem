<?php
if (!defined('APP_ACCESS')) {
    die('Direct access not permitted');
}
?>
        </div><!-- /page-body -->
    </div><!-- /main-content -->
</div><!-- /dashboard-wrapper -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="<?php echo APP_URL; ?>/assets/js/main.js?v=<?php echo time(); ?>"></script>
<script src="<?php echo APP_URL; ?>/assets/js/notifications.js?v=<?php echo time(); ?>"></script>
<script>
window.alert = function (message) {
    if (typeof showToast === "function") {
        showToast(String(message || "Notification"), "error");
    }
};
</script>
<script>
(function () {
    let baselineToken = null;
    let checking = false;
    let pendingRefresh = false;
    let pendingToken = null;
    const tabId = Math.random().toString(36).slice(2);
    const pollLockKey = 'autodok_live_poll_lock';
    const reloadStampKey = 'autodok_live_reload_stamp';

    function nowMs() {
        return Date.now();
    }

    function acquirePollLock(ttlMs = 2500) {
        try {
            const current = localStorage.getItem(pollLockKey);
            const parsed = current ? JSON.parse(current) : null;
            const now = nowMs();

            if (parsed && parsed.tabId !== tabId && typeof parsed.expiresAt === 'number' && parsed.expiresAt > now) {
                return false;
            }

            localStorage.setItem(pollLockKey, JSON.stringify({
                tabId,
                expiresAt: now + ttlMs
            }));
            return true;
        } catch (_e) {
            return true;
        }
    }

    function markReloadStamp() {
        try {
            localStorage.setItem(reloadStampKey, String(nowMs()));
        } catch (_e) {
            // ignore storage errors
        }
    }

    function hasRecentReload(windowMs = 1500) {
        try {
            const raw = localStorage.getItem(reloadStampKey);
            const stamp = raw ? parseInt(raw, 10) : 0;
            if (!stamp) return false;
            return nowMs() - stamp < windowMs;
        } catch (_e) {
            return false;
        }
    }

    function hasOpenModal() {
        return !!document.querySelector('.modal.show');
    }

    function hasActiveInputFocus() {
        const el = document.activeElement;
        if (!el) return false;
        const tag = (el.tagName || '').toLowerCase();
        return tag === 'input' || tag === 'textarea' || tag === 'select' || !!el.isContentEditable;
    }

    function hasDirtyForm() {
        const forms = Array.from(document.querySelectorAll('form'));
        return forms.some((form) => {
            const controls = Array.from(form.elements || []);
            return controls.some((control) => {
                if (!control || control.disabled) return false;
                const tag = (control.tagName || '').toLowerCase();
                const type = (control.type || '').toLowerCase();

                if (tag === 'textarea') {
                    return (control.value || '') !== (control.defaultValue || '');
                }
                if (tag === 'select') {
                    return control.selectedIndex !== -1 && control.options[control.selectedIndex]?.defaultSelected === false;
                }
                if (tag === 'input') {
                    if (type === 'checkbox' || type === 'radio') {
                        return !!control.checked !== !!control.defaultChecked;
                    }
                    if (type === 'hidden') {
                        return false;
                    }
                    return (control.value || '') !== (control.defaultValue || '');
                }
                return false;
            });
        });
    }

    function isSafeToRefresh() {
        return !hasOpenModal() && !hasActiveInputFocus() && !hasDirtyForm();
    }

    async function fetchLiveToken() {
        const controller = new AbortController();
        const timeout = setTimeout(() => controller.abort(), 5000);
        let res;
        try {
            res = await fetch('<?php echo APP_URL; ?>/api/live_updates.php', {
                method: 'GET',
                cache: 'no-store',
                credentials: 'same-origin',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                signal: controller.signal
            });
        } finally {
            clearTimeout(timeout);
        }
        if (!res.ok) return null;
        const data = await res.json();
        return data && data.success ? data.token : null;
    }

    async function checkUpdates() {
        if (checking || document.hidden) return;
        if (!acquirePollLock()) return;
        checking = true;
        try {
            const token = await fetchLiveToken();
            if (!token) return;

            if (baselineToken === null) {
                baselineToken = token;
                return;
            }

            if (token !== baselineToken) {
                pendingRefresh = true;
                pendingToken = token;
            }

            if (pendingRefresh) {
                if (window.NotificationManager && typeof window.NotificationManager.loadUnreadCount === 'function') {
                    window.NotificationManager.loadUnreadCount();
                }
                if (!isSafeToRefresh()) {
                    return;
                }
                if (hasRecentReload()) {
                    baselineToken = pendingToken || token;
                    pendingRefresh = false;
                    pendingToken = null;
                    return;
                }
                baselineToken = pendingToken || token;
                pendingRefresh = false;
                pendingToken = null;
                markReloadStamp();
                window.location.reload();
            }
        } catch (e) {
            // Silent fail to avoid interrupting usage.
        } finally {
            checking = false;
        }
    }

    setTimeout(checkUpdates, 2000);
    setInterval(checkUpdates, 10000);
    document.addEventListener('visibilitychange', function () {
        if (!document.hidden) {
            checkUpdates();
        }
    });

    // Close modal state before navigation so a backdrop cannot cover the
    // destination page during a fast transition.
    document.addEventListener('click', function (event) {
        const target = event.target;
        const link = target instanceof Element ? target.closest('a[href]') : null;
        if (!link || link.target === '_blank' || link.hasAttribute('download')) return;
        // In-page links (including Bootstrap tabs) must not close the modal.
        const href = link.getAttribute('href') || '';
        if (href.startsWith('#')) return;
        if (link.origin !== window.location.origin) return;

        document.querySelectorAll('.modal.show').forEach(function (modalEl) {
            const modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) modal.hide();
        });
        document.querySelectorAll('.modal-backdrop').forEach(function (backdrop) {
            backdrop.remove();
        });
        document.body.classList.remove('modal-open');
        document.body.style.removeProperty('padding-right');
    }, true);

    // If there is a pending refresh, trigger a quick re-check after user interaction ends.
    ['input', 'change', 'blur'].forEach(function (evtName) {
        document.addEventListener(evtName, function () {
            if (pendingRefresh) {
                setTimeout(checkUpdates, 150);
            }
        }, true);
    });
})();
</script>
</body>
</html>
