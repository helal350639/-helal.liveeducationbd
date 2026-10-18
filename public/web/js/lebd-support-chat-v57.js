(function () {
  'use strict';
  function ready() {
    const widget = document.getElementById('lebdHelp');
    const panel = document.getElementById('lebdHelpPanel');
    const toggle = document.getElementById('lebdHelpToggle');
    const close = document.getElementById('lebdHelpClose');
    if (!widget || !panel || !toggle || !close) return;

    // Hide only the known legacy WhatsApp/Messenger bar while this widget is active.
    // Preserve its markup so other pages that do not use the widget still work.
    document.querySelectorAll('.objection').forEach(function (oldBar) {
      if (oldBar.closest('#lebdHelp')) return;
      const anchors = Array.from(oldBar.querySelectorAll('a[href]'));
      const links = anchors.map(function (a) { return a.getAttribute('href') || ''; });
      const matching = links.some(function (url) { return url.includes('wa.me/8801718778184'); }) &&
                       links.some(function (url) { return url.includes('m.me/saeonlineexamgroup'); });
      if (matching) oldBar.style.setProperty('display', 'none', 'important');
    });

    function setOpen(open) {
      panel.hidden = !open;
      panel.setAttribute('aria-hidden', String(!open));
      toggle.setAttribute('aria-expanded', String(open));
      toggle.setAttribute('aria-label', open ? 'সাপোর্ট চ্যাট বন্ধ করুন' : 'সাপোর্ট চ্যাট খুলুন');
      if (open) close.focus();
    }
    toggle.addEventListener('click', function () { setOpen(panel.hidden); });
    close.addEventListener('click', function () { setOpen(false); toggle.focus(); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && !panel.hidden) { setOpen(false); toggle.focus(); } });
    document.addEventListener('click', function (e) { if (!panel.hidden && !widget.contains(e.target)) setOpen(false); });
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', ready);
  else ready();
})();
