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
(function () {
    let baselineToken = null;
    let checking = false;

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

    async function fetchLiveToken() {
        const res = await fetch('<?php echo APP_URL; ?>/api/live_updates.php', {
            method: 'GET',
            cache: 'no-store',
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        if (!res.ok) return null;
        const data = await res.json();
        return data && data.success ? data.token : null;
    }

    async function checkUpdates() {
        if (checking || document.hidden) return;
        checking = true;
        try {
            const token = await fetchLiveToken();
            if (!token) return;

            if (baselineToken === null) {
                baselineToken = token;
                return;
            }

            if (token !== baselineToken) {
                if (hasOpenModal() || hasActiveInputFocus() || hasDirtyForm()) {
                    baselineToken = token;
                    return;
                }
                window.location.reload();
            }
        } catch (e) {
            // Silent fail to avoid interrupting usage.
        } finally {
            checking = false;
        }
    }

    setTimeout(checkUpdates, 1200);
    setInterval(checkUpdates, 12000);
    document.addEventListener('visibilitychange', function () {
        if (!document.hidden) {
            checkUpdates();
        }
    });
})();
</script>
</body>
</html>
