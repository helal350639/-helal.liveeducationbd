/* Live Education BD — Homepage E-Book (v28)
 * Progressive enhancement only; cards remain normal Laravel links without JS.
 */
(function () {
  'use strict';
  function init() {
    var root = document.querySelector('.lebd-ebook-home');
    if (!root || root.dataset.ebookJsReady === '1') return;
    root.dataset.ebookJsReady = '1';
    root.querySelectorAll('a.lebd-ebook-card, a.lebd-ebook-see-all').forEach(function (link) {
      link.addEventListener('click', function () {
        link.classList.add('lebd-ebook-is-opening');
      });
      link.addEventListener('keydown', function (event) {
        if (event.key === ' ') {
          // Anchors navigate on Enter by default. Also support Space for accessibility.
          event.preventDefault();
          link.click();
        }
      });
    });
    window.addEventListener('pageshow', function () {
      root.querySelectorAll('.lebd-ebook-is-opening').forEach(function (link) {
        link.classList.remove('lebd-ebook-is-opening');
      });
    });
  }
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init, { once: true });
  } else {
    init();
  }
})();
