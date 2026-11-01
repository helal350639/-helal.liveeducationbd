@php $le185PageTitle = 'Exam Section'; @endphp
@extends('student')
@section('title', 'Exam Section')
@section('stylesheets')
<style>

*{box-sizing:border-box}.lebd-exam-scope{background:#f2f6fc;font-family:system-ui,-apple-system,"Segoe UI",sans-serif;color:#17315d}.lebd-exam-scope a{color:inherit}.le185-head{background:#101c91;color:white;padding:16px max(20px,calc((100vw - 1180px)/2));display:flex;align-items:center;gap:12px}.le185-head a{color:white;text-decoration:none;font-weight:700}.le185-main{padding:10px 16px 32px}.le185-footer{text-align:center;color:#8290a7;font-size:12px;padding:24px}.le184-item{line-height:1.5} .le184-grid p{color:#475b75}

</style>
@endsection
@section('content')
<div class="lebd-exam-scope">
<main class="le185-main">
@include('student.partials.exam-section-category-cards')
</main><footer class="le185-footer">Live Education BD · Exam Section</footer>
<script>
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
