<!doctype html><html lang="bn"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Exam Result — Live Education BD</title><style>body{font-family:Arial,"Noto Sans Bengali",sans-serif;background:#f5f8fc;color:#173259;margin:0}.card{max-width:520px;margin:65px auto;background:#fff;padding:30px;border:1px solid #dce6f2;border-radius:18px;text-align:center}a{display:inline-block;margin-top:15px;padding:12px 17px;color:#fff;background:#1768d7;border-radius:9px;text-decoration:none}</style></head><body>
{{-- Fix215: shared package CTA, matching requested top-right orange pill design. --}}
<div class="lebd215-package-bar" style="display:flex;justify-content:flex-end;align-items:center;max-width:1180px;margin:12px auto 6px;padding:0 13px;box-sizing:border-box;">
    <a href="{{ route('student.exam-section.packages') }}" class="lebd215-package-button" style="display:inline-flex;align-items:center;justify-content:center;gap:8px;min-height:36px;padding:8px 18px;background:linear-gradient(115deg,#ff7a13,#ff4239);border-radius:999px;color:#fff!important;font-size:13px;font-weight:800;line-height:1.2;text-decoration:none;box-shadow:0 6px 15px rgba(235,81,31,.23);white-space:nowrap;">
        <span aria-hidden="true" style="font-size:13px">▣</span><span>প্যাকেজ কিনুন</span>
    </a>
</div>

<main class="card"><h1>{{ $row->exam_name }}</h1><h2>পরীক্ষা জমা হয়েছে</h2>@if($visible && $ended)<p>সঠিক: {{ $attempt->correct_count }} | ভুল: {{ $attempt->wrong_count }}</p><h2>প্রাপ্ত নম্বর: {{ $attempt->score }}</h2>@else<p>ফলাফল পরীক্ষার নির্ধারিত সময় শেষ হওয়ার পর প্রকাশ করা হবে, যদি Result Viewing চালু থাকে।</p>@endif<a href="{{ route('student.exam-section.categories') }}">পরীক্ষা বিভাগে ফিরুন</a></main></body></html>
