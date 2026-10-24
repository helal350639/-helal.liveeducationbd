{{-- Render MathLive LaTeX (\( ... \), \[ ... \]) wherever included. --}}
@once
<script>
window.MathJax = {
    tex: {
        inlineMath: [['\\(', '\\)'], ['$', '$']],
        displayMath: [['\\[', '\\]'], ['$$', '$$']],
        processEscapes: true
    },
    options: {skipHtmlTags: ['script', 'noscript', 'style', 'textarea', 'pre', 'code']},
    startup: {pageReady: function () {return MathJax.startup.defaultPageReady();}}
};
window.LEBDTypesetMath = function (element) {
    if (!window.MathJax || typeof window.MathJax.typesetPromise !== 'function') return Promise.resolve();
    try { window.MathJax.typesetClear(element ? [element] : undefined); } catch (e) {}
    return window.MathJax.typesetPromise(element ? [element] : undefined);
};
</script>
<script defer src="https://cdn.jsdelivr.net/npm/mathjax@4/tex-mml-chtml.js"></script>
@endonce
