/* LEBD-3 Student MCQ Quiz: countdown, answer progress, timed submission. */
(function () {
  'use strict';
  function ready() {
    const form = document.getElementById('jp-exam-form');
    const timer = document.getElementById('jp-timer');
    if (!form || !timer) return;

    const answerInputs = Array.from(form.querySelectorAll('input[type="radio"][name^="answer["]'));
    const answeredCount = document.getElementById('jp-answered');
    const progressBar = document.getElementById('jp-progress-fill');
    const countFromPill = document.querySelector('.jp-exam-pill');
    const total = Number.parseInt(countFromPill ? countFromPill.textContent : '', 10) ||
      new Set(answerInputs.map(input => input.name)).size;

    let remaining = Math.max(0, Number.parseInt(timer.getAttribute('data-seconds') || '0', 10) || 0);
    let submitted = false;
    const deadline = Date.now() + remaining * 1000;

    function updateProgress() {
      const answered = new Set(answerInputs.filter(input => input.checked).map(input => input.name)).size;
      if (answeredCount) answeredCount.textContent = String(answered);
      if (progressBar) progressBar.style.width = (total > 0 ? Math.min(100, answered * 100 / total) : 0) + '%';
    }

    function renderTime(seconds) {
      const minutes = Math.floor(seconds / 60);
      const secs = seconds % 60;
      timer.textContent = minutes + ':' + String(secs).padStart(2, '0');
      timer.classList.toggle('jp-time-warning', seconds <= 60);
    }

    function submitOnce() {
      if (submitted) return;
      submitted = true;
      window.clearInterval(intervalId);
      // Native submit avoids a confirmation handler preventing the timed submission.
      HTMLFormElement.prototype.submit.call(form);
    }

    answerInputs.forEach(input => input.addEventListener('change', updateProgress));
    form.addEventListener('submit', function () {
      submitted = true;
      window.clearInterval(intervalId);
    });

    let intervalId = null;
    function tick() {
      remaining = Math.max(0, Math.ceil((deadline - Date.now()) / 1000));
      renderTime(remaining);
      if (remaining <= 0) submitOnce();
    }

    updateProgress();
    tick();
    if (!submitted) intervalId = window.setInterval(tick, 1000);
  }
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', ready, { once: true });
  } else {
    ready();
  }
})();
