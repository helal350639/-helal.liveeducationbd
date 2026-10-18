/* LEBD-3 v60: resilient shared support toggle (works with late DOM and multiple layouts). */
(function () {
  'use strict';
  if (window.__lebdSupportV60Bound) return;
  window.__lebdSupportV60Bound = true;

  function canonicalWidget() {
    var widgets = document.querySelectorAll('[data-lebd-help]');
    if (!widgets.length) return null;
    for (var i = 1; i < widgets.length; i++) widgets[i].remove();
    return widgets[0];
  }

  function hideOldSupport() {
    document.querySelectorAll('.objection').forEach(function (bar) {
      var links = Array.from(bar.querySelectorAll('a[href]'));
      var hasWa = links.some(function (a) { return a.href.indexOf('wa.me/8801718778184') !== -1; });
      var hasMessenger = links.some(function (a) { return a.href.indexOf('m.me/saeonlineexamgroup') !== -1; });
      if (hasWa && hasMessenger) bar.style.setProperty('display', 'none', 'important');
    });
  }

  function update(isOpen) {
    var root = canonicalWidget();
    if (!root) return;
    var panel = root.querySelector('.lebd-help-panel');
    var toggle = root.querySelector('.lebd-help-trigger');
    if (!panel || !toggle) return;
    panel.hidden = !isOpen;
    panel.setAttribute('aria-hidden', String(!isOpen));
    toggle.setAttribute('aria-expanded', String(isOpen));
    toggle.setAttribute('aria-label', isOpen ? 'সাপোর্ট চ্যাট বন্ধ করুন' : 'সাপোর্ট চ্যাট খুলুন');
  }

  // Delegated events work even when script executes before the widget is rendered.
  document.addEventListener('click', function (e) {
    var root = canonicalWidget();
    if (!root) return;
    var btn = e.target.closest ? e.target.closest('.lebd-help-trigger') : null;
    var close = e.target.closest ? e.target.closest('.lebd-help-close') : null;
    var panel = root.querySelector('.lebd-help-panel');
    if (!panel) return;
    if (btn && root.contains(btn)) {
      e.preventDefault();
      update(panel.hidden);
      return;
    }
    if (close && root.contains(close)) {
      e.preventDefault();
      update(false);
      var trigger = root.querySelector('.lebd-help-trigger');
      if (trigger) trigger.focus();
      return;
    }
    if (!panel.hidden && !root.contains(e.target)) update(false);
  }, true);

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') update(false);
  });
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function () { canonicalWidget(); hideOldSupport(); });
  } else {
    canonicalWidget(); hideOldSupport();
  }
})();
