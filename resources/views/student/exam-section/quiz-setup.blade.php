@extends('student')
@section('title','Make a Quiz — '.$scopeTitle)
@section('stylesheets')
<style>
.lebd-mq,.lebd-mq *{box-sizing:border-box}.lebd-mq{background:#f1f6fd;min-height:75vh;padding:22px 12px 45px;color:#17345c;font-family:system-ui,-apple-system,'Segoe UI','Noto Sans Bengali',sans-serif}.lebd-mq .wrap{max-width:760px;margin:auto}.lebd-mq .panel{background:#fff;border:1px solid #dce8f7;border-radius:16px;padding:20px;box-shadow:0 6px 20px #1b47700d;margin-bottom:14px}.lebd-mq .back{display:inline-block;color:#1767c8;text-decoration:none;font-weight:750;font-size:13px;margin-bottom:12px}.lebd-mq h1{font-size:23px;color:#104d9b;margin:0 0 5px}.lebd-mq .muted{font-size:12px;color:#7185a0;margin:0}.lebd-mq .grid{display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-top:18px}.lebd-mq label{display:block;font-size:13px;font-weight:800;margin-bottom:7px}.lebd-mq select,.lebd-mq input[type=number]{width:100%;height:44px;border:1px solid #bfd3ee;border-radius:9px;padding:8px 12px;background:#f8fbff;color:#17345c;font-size:14px}.lebd-mq .available{margin-top:15px;padding:13px;border-radius:11px;background:#eaf4ff;color:#155db2;font-weight:800;display:flex;justify-content:space-between}.lebd-mq .check{display:flex;align-items:center;gap:9px;font-size:13px;margin-top:16px}.lebd-mq .check input{width:17px;height:17px;accent-color:#1768d7}.lebd-mq .start{width:100%;border:0;border-radius:11px;padding:14px;background:linear-gradient(100deg,#075ac8,#1685ed);color:#fff;font-size:15px;font-weight:850;margin-top:18px;cursor:pointer;box-shadow:0 4px 0 #064b9d}.lebd-mq .start:disabled{opacity:.45;cursor:not-allowed}.lebd-mq .error{color:#bd3447;font-size:12px;margin-top:8px}
@media(max-width:600px){.lebd-mq{padding:12px 8px 30px}.lebd-mq .panel{padding:15px}.lebd-mq h1{font-size:20px}.lebd-mq .grid{grid-template-columns:1fr;gap:12px}}
</style>
@endsection
@section('content')
<div class="lebd-mq"><main class="wrap">
<a class="back" href="{{ route('student.exam-section.wrong-unanswered',['category'=>$category->id,'subcategory_id'=>$subcategory?->id]) }}">← Question Review-এ ফিরুন</a>
<section class="panel"><h1>✦ Make a Quiz</h1><p class="muted">{{ $scopeTitle }} — আপনার আগের পরীক্ষার ভুল ও উত্তরহীন প্রশ্ন দিয়ে নতুন অনুশীলনী পরীক্ষা দিন।</p>
<form method="POST" action="{{ route('student.exam-section.wrong-unanswered.quiz.start',['category'=>$category->id]) }}">@csrf
@if($subcategory)<input type="hidden" name="subcategory_id" value="{{ $subcategory->id }}">@endif
<div class="grid"><div><label for="mode">কোন ধরনের প্রশ্নে পরীক্ষা দেবেন?</label><select name="mode" id="mode"><option value="wrong" @selected($mode==='wrong')>শুধু ভুল উত্তর</option><option value="unanswered" @selected($mode==='unanswered')>শুধু উত্তরহীন</option><option value="both" @selected($mode==='both')>ভুল + উত্তরহীন</option></select></div>
<div><label for="question_count">কতটি প্রশ্নে পরীক্ষা দেবেন?</label><input type="number" id="question_count" name="question_count" min="1" max="200" value="{{ min(max(1,$available),20) }}" required></div>
<div><label for="duration_minutes">পরীক্ষার সময় (মিনিট)</label><input type="number" id="duration_minutes" name="duration_minutes" min="1" max="180" value="20" required></div>
<div><label>প্রশ্নের নম্বর</label><div class="available"><span>প্রতি সঠিক উত্তরে</span><b>{{ rtrim(rtrim(number_format((float)($settings['correct_mark']??1),2,'.',''),'0'),'.') }} নম্বর</b></div><div class="available" style="margin-top:7px;background:#fff4e6;color:#9a6100"><span>নেগেটিভ মার্ক</span><b>{{ rtrim(rtrim(number_format((float)($settings['negative_mark']??0),2,'.',''),'0'),'.') }}</b></div></div></div>
<div class="available"><span>এই ফিল্টারে পাওয়া যাচ্ছে</span><b id="available-count">{{ $available }}টি প্রশ্ন</b></div>
<label class="check"><input type="checkbox" name="show_result" value="1" checked> পরীক্ষা জমা দেওয়ার পর সঙ্গে সঙ্গে মার্ক দেখাবে</label>
@if($available===0)<p class="error">এই ধরনের কোনো প্রশ্ন পাওয়া যায়নি। আগে সংশ্লিষ্ট পরীক্ষা Submit করুন এবং ফলাফল প্রকাশিত হতে দিন।</p>@endif
@error('question_count')<p class="error">{{ $message }}</p>@enderror @error('duration_minutes')<p class="error">{{ $message }}</p>@enderror
<button class="start" id="start-quiz" type="submit" @disabled($available===0)>পরীক্ষা শুরু করুন →</button>
</form></section>
<section class="panel"><b>প্রশ্নের উৎস</b><div class="muted" style="margin-top:7px;line-height:1.8">শুধু এই Category / Sub-category-তে আপনার Submit করা পরীক্ষার ভুল অথবা উত্তর না দেওয়া প্রশ্ন নেওয়া হবে। একই প্রশ্ন একাধিক পরীক্ষায় থাকলে একবারই গণনা করা হবে। পরীক্ষার নম্বর বর্তমান Exam Section-এর Correct Mark ও Negative Mark অনুযায়ী হিসাব হবে।</div></section>
</main></div>
<script>
(function(){const counts=@json($counts);const mode=document.getElementById('mode'),available=document.getElementById('available-count'),number=document.getElementById('question_count'),start=document.getElementById('start-quiz');function sync(){const n=Number(counts[mode.value]||0);available.textContent=n+'টি প্রশ্ন';number.max=Math.max(1,Math.min(200,n));if(Number(number.value)>n)number.value=n||1;start.disabled=n===0;}mode.addEventListener('change',sync);sync();})();
</script>
@endsection
