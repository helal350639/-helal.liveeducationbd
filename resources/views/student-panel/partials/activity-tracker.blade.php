{{-- LEBD Student Activity Tracker: Page Visits, Safe Clicks and Active Time --}}
@auth('student')
<script>
(function () {
    'use strict';

    if (window.__lebdStudentActivityTracker) return;
    window.__lebdStudentActivityTracker = true;

    const endpoint = @json(route('students.activity.track'));
    const csrf = @json(csrf_token());
    const sessionStorageKey = 'lebd:student:activity-session:v2';
    const privateSections = '.mcqitems, .exam-options, .exam-question-card, [data-no-track], [data-private]';

    let sessionId = sessionStorage.getItem(sessionStorageKey);
    if (!sessionId) {
        sessionId = (window.crypto && window.crypto.randomUUID)
            ? window.crypto.randomUUID()
            : 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
                const n = Math.floor(Math.random() * 16);
                return (c === 'x' ? n : ((n & 3) | 8)).toString(16);
            });
        sessionStorage.setItem(sessionStorageKey, sessionId);
    }

    const pagePath = window.location.pathname.slice(0, 240);
    let lastInteraction = Date.now();
    let lastCreditAt = Date.now();

    function send(eventType, label, target, seconds) {
        fetch(endpoint, {
            method: 'POST',
            credentials: 'same-origin',
            keepalive: true,
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf
            },
            body: JSON.stringify({
                session_key: sessionId,
                event_type: eventType,
                path: pagePath,
                target_path: target || null,
                target_label: label || null,
                active_seconds: seconds || 0
            })
        }).catch(function () {
            // Never interfere with normal student navigation or exams.
        });
    }

    function safeLabel(element) {
        let label = element.getAttribute('data-track-label')
            || element.getAttribute('aria-label')
            || element.getAttribute('title')
            || element.textContent
            || element.tagName;

        label = String(label).replace(/\s+/g, ' ').trim().slice(0, 100);
        label = label.replace(/[\w.+-]+@[\w.-]+\.[A-Za-z]{2,}/g, '[email]');
        label = label.replace(/\+?\d[\d\s()-]{8,}\d/g, '[number]');
        return label.slice(0, 100);
    }

    function safeTarget(element) {
        const value = element.getAttribute('href');
        if (!value || value.startsWith('#')) return null;
        try {
            const parsed = new URL(value, window.location.origin);
            return parsed.origin === window.location.origin
                ? parsed.pathname.slice(0, 240)
                : null;
        } catch (_) {
            return null;
        }
    }

    // Credit activity at most once every 12 seconds. Unlike relying on
    // document.hasFocus(), this works with docked developer tools and mobiles.
    function flushActiveTime() {
        const now = Date.now();
        const elapsed = Math.floor((now - lastCreditAt) / 1000);
        if (elapsed < 12) return;

        const recentlyActive = (now - lastInteraction) < 60000;
        lastCreditAt = now;

        if (document.visibilityState === 'visible' && recentlyActive) {
            send('heartbeat', null, null, Math.min(15, elapsed));
        }
    }

    function markInteraction() {
        const now = Date.now();
        // Do not count the time a student spent idle before returning.
        if (now - lastInteraction > 60000) {
            lastCreditAt = now;
        }
        lastInteraction = now;
        flushActiveTime();
    }

    ['pointerdown', 'keydown', 'scroll', 'touchstart'].forEach(function (eventName) {
        document.addEventListener(eventName, markInteraction, { passive: true });
    });

    document.addEventListener('click', function (event) {
        if (!event.target || !event.target.closest) return;
        const element = event.target.closest('button, a, [role="button"]');
        if (!element || element.closest(privateSections)) return;
        send('click', safeLabel(element), safeTarget(element), 0);
    }, true);

    document.addEventListener('visibilitychange', function () {
        if (document.visibilityState !== 'visible') {
            // Do not credit hidden-tab time.
            lastCreditAt = Date.now();
        } else {
            lastCreditAt = Date.now();
            lastInteraction = Date.now();
        }
    });

    send('page_view', null, null, 0);
    window.setInterval(flushActiveTime, 15000);
})();
</script>
@endauth
