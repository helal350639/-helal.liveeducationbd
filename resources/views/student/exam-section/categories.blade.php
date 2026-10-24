@php $le185PageTitle = 'Exam Section'; @endphp
<!DOCTYPE html>
<html lang="bn"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>{{ $le185PageTitle }} — Live Education BD</title><style>
*{box-sizing:border-box}body{margin:0;background:#f2f6fc;font-family:system-ui,-apple-system,"Segoe UI",sans-serif;color:#17315d}a{color:inherit}.le185-head{background:#101c91;color:white;padding:16px max(20px,calc((100vw - 1180px)/2));display:flex;align-items:center;gap:12px}.le185-head a{color:white;text-decoration:none;font-weight:700}.le185-main{padding:10px 16px 32px}.le185-footer{text-align:center;color:#8290a7;font-size:12px;padding:24px}.le184-item{line-height:1.5} .le184-grid p{color:#475b75}
</style></head><body><header class="le185-head"><a href="{{ url('/') }}">LIVE Education BD</a><span aria-hidden="true">›</span><a href="{{ route('student.exam-section.categories') }}">পরীক্ষা বিভাগ</a></header><main class="le185-main">
@include('student.partials.exam-section-category-cards')
</main><footer class="le185-footer">Live Education BD · Exam Section</footer>
</body>
</html>
