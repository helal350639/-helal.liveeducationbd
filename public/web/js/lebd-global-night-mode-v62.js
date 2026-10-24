/* LEBD-3 v79: globally persisted night theme, icon-only button in shared header. */
(function () {
  'use strict';
  var KEY = 'lebd-site-theme';
  var root = document.documentElement;
  function current() { return root.getAttribute('data-lebd-theme') === 'dark' ? 'dark' : 'light'; }
  // Apply previous preference even when the layout lacks an early-theme inline script.
  try {
    if (localStorage.getItem(KEY) === 'dark') root.setAttribute('data-lebd-theme', 'dark');
    else root.removeAttribute('data-lebd-theme');
  } catch (e) {}
  function updateButton() {
    var btn = document.getElementById('lebdNightToggle');
    if (!btn) return;
    var dark = current() === 'dark';
    btn.setAttribute('aria-pressed', String(dark));
    btn.setAttribute('aria-label', dark ? 'লাইট মোড চালু করুন' : 'নাইট মোড চালু করুন');
    btn.title = dark ? 'লাইট মোড' : 'নাইট মোড';
    btn.innerHTML = '<span class="lebd-night-icon" aria-hidden="true">' + (dark ? '☀' : '☾') + '</span><span class="lebd-night-label">' + (dark ? 'লাইট মোড' : 'নাইট মোড') + '</span>';
  }
  function apply(theme) {
    if (theme === 'dark') root.setAttribute('data-lebd-theme', 'dark');
    else root.removeAttribute('data-lebd-theme');
    try { localStorage.setItem(KEY, theme); } catch (e) {}
    updateButton();
  }
  function placeButton() {
    var btn = document.getElementById('lebdNightToggle');
    var header = document.getElementById('Header');
    if (!btn || !header) return;
    var isMobile = window.matchMedia('(max-width: 991px)').matches;
    var target = isMobile ? header.querySelector('.moblie-icon') : header.querySelector('.lebd-nav-bell');
    if (isMobile && target) {
      // Before profile; mobile bell remains ahead of the new moon/sun icon.
      var profile = target.querySelector('.lebd-mobile-profile');
      if (profile) target.insertBefore(btn, profile);
      else target.appendChild(btn);
    } else if (target && target.parentNode) {
      target.parentNode.insertBefore(btn, target);
    } else {
      var fallback = header.querySelector('.moblie-icon') || header;
      fallback.appendChild(btn);
    }
    btn.classList.add('lebd-night-header-icon');
  }
  function init() {
    if (!document.body) return;
    var btn = document.getElementById('lebdNightToggle');
    if (!btn) {
      btn = document.createElement('button');
      btn.type = 'button';
      btn.id = 'lebdNightToggle';
      document.body.appendChild(btn);
    }
    if (!btn.dataset.lebdNightBound) {
      btn.addEventListener('click', function () { apply(current() === 'dark' ? 'light' : 'dark'); });
      btn.dataset.lebdNightBound = '1';
    }
    placeButton();
    updateButton();
    window.addEventListener('resize', placeButton, {passive:true});
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
  else init();
  window.addEventListener('storage', function (ev) {
    if (ev.key === KEY) {
      if (ev.newValue === 'dark') root.setAttribute('data-lebd-theme', 'dark');
      else root.removeAttribute('data-lebd-theme');
      updateButton();
    }
  });
})();
