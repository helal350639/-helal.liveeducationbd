@php $le185PageTitle = 'Exam Section'; @endphp
@extends('student')
@section('title', 'Exam Section')
@section('stylesheets')
<style>

*{box-sizing:border-box}.lebd-exam-scope{background:#f2f6fc;font-family:system-ui,-apple-system,"Segoe UI",sans-serif;color:#17315d}.lebd-exam-scope a{color:inherit}.le185-head{background:#101c91;color:white;padding:16px max(20px,calc((100vw - 1180px)/2));display:flex;align-items:center;gap:12px}.le185-head a{color:white;text-decoration:none;font-weight:700}.le185-main{padding:10px 16px 32px}.le185-footer{text-align:center;color:#8290a7;font-size:12px;padding:24px}.le184-item{line-height:1.5} .le184-grid p{color:#475b75}
.lebd-exam-success-notice{max-width:1180px;margin:8px auto 16px;padding:13px 14px;display:flex;align-items:center;gap:12px;border:1px solid #9cddb5;border-left:5px solid #159653;border-radius:12px;background:linear-gradient(100deg,#e8f9ee 0%,#f4fff7 100%);box-shadow:0 5px 16px rgba(20,120,70,.08);color:#145b37}
.lebd-exam-success-icon{width:36px;height:36px;flex:0 0 36px;display:grid;place-items:center;border-radius:50%;background:#d1f3dd;color:#13864b;font-size:19px}
.lebd-exam-success-copy{min-width:0;display:flex;flex-direction:column;gap:3px;font-size:13px;line-height:1.55}
.lebd-exam-success-copy strong{font-size:14px;font-weight:850;color:#10663b}
.lebd-exam-success-close{margin-left:auto;flex:0 0 32px;width:32px;height:32px;border:1px solid #b5dfc4;border-radius:8px;background:#fff;color:#28764a;font-size:23px;line-height:1;cursor:pointer}
.lebd-exam-success-close:hover{background:#dff5e7}
.lebd-exam-success-notice.is-closing{display:none}
@media(max-width:600px){.lebd-exam-success-notice{padding:10px;gap:9px}.lebd-exam-success-copy{font-size:11px}.lebd-exam-success-copy strong{font-size:13px}.lebd-exam-success-icon{width:32px;height:32px;flex-basis:32px}}


</style>
@endsection
@section('content')
<div class="lebd-exam-scope">
<main class="le185-main">
@if(session('success'))
  <div class="lebd-exam-success-notice" id="lebdExamSuccessNotice" role="status" aria-live="polite">
    <span class="lebd-exam-success-icon" aria-hidden="true"><i class="fa-solid fa-circle-check"></i></span>
    <div class="lebd-exam-success-copy">
      <strong>প্যাকেজ কেনা সফল হয়েছে!</strong>
      <span>{{ session('success') }}</span>
    </div>
    <button type="button" class="lebd-exam-success-close" id="lebdExamSuccessClose" aria-label="Notice বন্ধ করুন" title="বন্ধ করুন">×</button>
  </div>
@endif
@php $lebdShowOwnedExamPackages = true; @endphp
@include('student.partials.exam-section-category-cards')
</main><footer class="le185-footer">Live Education BD · Exam Section</footer>
<script>

const successNotice = document.getElementById('lebdExamSuccessNotice');
const successClose = document.getElementById('lebdExamSuccessClose');
if (successNotice && successClose) {
  successClose.addEventListener('click', function () {
    successNotice.classList.add('is-closing');
    successNotice.setAttribute('aria-hidden', 'true');
  });
}
/* Replace ONLY category-card navigation, so browser Back skips this intermediate landing page.
   Do not intercept payment, package, exam attempt or unrelated links. */
document.addEventListener('click', function (event) {
  if (event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
  const a = event.target.closest('a[href]');
  if (!a || a.hasAttribute('download') || (a.target && a.target !== '_self')) return;
  let target;
  try { target = new URL(a.href, window.location.href); } catch (_) { return; }
  if (target.origin !== window.location.origin) return;
  if (!/^\/(?:lebd-exam-browse|lebd-student-exams)\/category\/\d+\/?$/.test(target.pathname)) return;
  event.preventDefault();
  window.location.replace(target.href);
});
</script>
</div>
@endsection
