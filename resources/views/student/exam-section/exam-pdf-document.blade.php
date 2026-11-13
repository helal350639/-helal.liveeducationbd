<!doctype html>
<html lang="bn">
<head><meta charset="utf-8"><style>
html,body,div,span,p,h1,h2,strong,b{font-family:lebdsolaiman !important;} body{font-family:lebdsolaiman;font-size:12pt;color:#17253b;line-height:1.55}h1{text-align:center;font-size:19pt;margin:0 0 4px;color:#12345d}h2{text-align:center;font-size:14pt;margin:0 0 14px;color:#405c7b}.meta{border:1px solid #9aaec5;padding:9px 12px;margin:12px 0 16px;font-size:10pt}.topic{background:#edf4fc;padding:6px 9px;margin:12px 0 8px;font-weight:bold;border-left:3px solid #1674d4}.question{margin:0 0 13px;page-break-inside:avoid}.qtitle{font-weight:bold;margin-bottom:5px}.options{margin:4px 0 0 18px}.option{margin:2px 0}.muted{font-size:9pt;color:#66788e}.footer{border-top:1px solid #ccd5df;margin-top:20px;padding-top:6px;text-align:center;font-size:9pt;color:#75859a}
</style></head>
<body>
<h1>{{ $categoryName }}</h1><h2>{{ $examName }} — প্রশ্নপত্র</h2>
<div class="meta"><strong>Routine:</strong> {{ $routineName }}<br><strong>পরীক্ষার তারিখ:</strong> {{ $examStart ? \Carbon\Carbon::parse($examStart,'Asia/Dhaka')->format('d M Y, h:i A') : 'নির্ধারিত নয়' }}<br><strong>সময়:</strong> {{ $examEnd ? \Carbon\Carbon::parse($examEnd,'Asia/Dhaka')->format('d M Y, h:i A') : '—' }}<br><strong>মোট প্রশ্ন:</strong> {{ $questions->count() }} টি</div>
@if($topics)<div class="topic">Subject &amp; Syllabus: {{ implode(' • ',$topics) }}</div>@endif
@foreach($questions as $index=>$question)
<div class="question"><div class="qtitle">{{ $index+1 }}. {!! $question['title'] !!}</div>
@if($question['options'])<div class="options">@foreach($question['options'] as $oi=>$option)<div class="option">{{ chr(65+$oi) }}. {!! $option !!}</div>@endforeach</div>@endif
</div>
@endforeach
<div class="footer">LiveEducationBD.com | Generated from the saved Exam Section Routine</div>
</body></html>
