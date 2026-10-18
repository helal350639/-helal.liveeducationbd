@extends('homepage')
@section('title',$cat->name.' — '.$ch->title.' | E-Book')
@section('content')
<link rel="stylesheet" href="{{asset('web/css/lebd-ebook.css')}}">
<link rel="stylesheet" href="{{asset('web/css/lebd-ebook-student-v22.css')}}">
<main class="leb-lib"><div class="leb-read-nav"><a href="{{route('student.ebook.chapters',['kind'=>$kind,'category'=>$cat->id])}}">← সূচিপত্র</a><strong>{{$ch->title}}</strong> @if(!$freePreview)<button onclick="window.print()" type="button">Print</button>@endif</div>
@if($freePreview)<div class="leb-alert">Free Preview — অধ্যায়ের প্রথম অংশ দেখানো হচ্ছে। সম্পূর্ণ অধ্যায় Premium Access-এ পাওয়া যাবে।</div>@endif
@include('ebook.partials.blocks',['isEditor'=>false])
@if($freePreview)
<script>
(function(){
 const trim=function(){document.querySelectorAll('.leb-lib .lebd-a4-pages').forEach(function(container){Array.from(container.children).slice(3).forEach(function(page){page.remove();});});};
 document.addEventListener('DOMContentLoaded',function(){const host=document.querySelector('.leb-lib .lebd-a4-pages');if(!host)return;const observer=new MutationObserver(trim);observer.observe(host,{childList:true});trim();});
})();
</script>
<div class="leb-paywall"><h2>🔒 বাকি অধ্যায় পড়তে Premium Access প্রয়োজন</h2><a class="leb-btn" href="{{route('student.ebook.chapters',['kind'=>$kind,'category'=>$cat->id])}}">Package ও সূচিপত্র দেখুন</a></div>@endif
</main>
@endsection
