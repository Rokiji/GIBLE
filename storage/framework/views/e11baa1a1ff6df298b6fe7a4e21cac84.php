<div id="gibleReminderToastHost" class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1085" aria-live="polite" aria-label="Reminder notifications"></div>
<script>
  (function () {
    var host = document.getElementById('gibleReminderToastHost');
    var csrf = document.querySelector('meta[name="csrf-token"]');
    var alertsUrl = <?php echo json_encode(route('api.reminders.alerts.index'), 15, 512) ?>;
    var dismissTpl = <?php echo json_encode(route('api.reminders.alerts.dismiss', ['alert' => '__ID__']), 512) ?>;
    var shownToastIds = new Set();
    var pollMs = parseInt(sessionStorage.getItem('gible_reminder_poll_ms') || '42000', 10);
    if (pollMs < 15000 || pollMs > 120000 || isNaN(pollMs)) pollMs = 42000;

    function dismissUrl(id) {
      return dismissTpl.replace('__ID__', String(id));
    }

    async function acknowledgeDismiss(id) {
      try {
        await fetch(dismissUrl(id), {
          method: 'POST',
          headers: {
            Accept: 'application/json',
            'X-CSRF-TOKEN': csrf ? csrf.getAttribute('content') || '' : '',
            'X-Requested-With': 'XMLHttpRequest',
          },
          credentials: 'same-origin',
        });
      } catch (e) {}
    }

    function maybeBrowserNotify(title, body, tag) {
      if (typeof Notification === 'undefined' || Notification.permission !== 'granted') return;
      if (document.visibilityState === 'visible') return;
      try {
        new Notification(title || 'GIBLE reminder', { body: body || '', tag: tag || 'gible-reminder', silent: false });
      } catch (e) {}
    }

    function showReminderToast(payload) {
      var idNum = payload.id;
      var idStr = String(idNum);
      if (shownToastIds.has(idStr)) return;
      shownToastIds.add(idStr);

      if (typeof bootstrap === 'undefined' || !bootstrap.Toast) return;

      var el = document.createElement('div');
      el.className = 'toast gible-reminder-toast border-0 shadow';
      el.setAttribute('role', 'alert');

      var phaseBadge = '';
      if (payload.phase === 'day_before') phaseBadge = '<span class="badge rounded-pill text-bg-secondary me-2">Tomorrow</span>';
      else if (payload.phase === 'ten_before') phaseBadge = '<span class="badge rounded-pill text-bg-warning text-dark me-2">10 min</span>';
      else if (payload.phase === 'due') phaseBadge = '<span class="badge rounded-pill text-bg-success me-2">Now</span>';

      el.innerHTML =
        '<div class="toast-header bg-white">' +
        '<i class="bi bi-bell-fill text-warning me-2"></i>' +
        '<strong class="me-auto">' +
        escapeHtml(payload.title || 'Reminder') +
        '</strong>' +
        phaseBadge +
        '<button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Dismiss"></button></div>' +
        '<div class="toast-body">' +
        escapeHtml(payload.body || '') +
        '</div>';

      host.appendChild(el);

      maybeBrowserNotify(payload.title || 'GIBLE reminder', payload.body || '', 'gible-reminder-' + idStr);

      el.addEventListener('hidden.bs.toast', function () {
        acknowledgeDismiss(idNum);
        el.remove();
      });

      try {
        new bootstrap.Toast(el, { autohide: false }).show();
      } catch (err) {}
    }

    function escapeHtml(s) {
      return String(s)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
    }

    async function pollAlerts() {
      try {
        var res = await fetch(alertsUrl, { headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' });
        if (!res.ok) return;
        var json = await res.json();
        var list = json.alerts || [];
        list.reverse().forEach(function (payload) {
          showReminderToast(payload);
        });
      } catch (e) {}
      window.setTimeout(pollAlerts, pollMs);
    }

    pollAlerts();
  })();
</script>
<?php /**PATH C:\Users\agull\OneDrive\Documents\Visual Studio\GIBLE\resources\views/partials/reminder-toasts.blade.php ENDPATH**/ ?>