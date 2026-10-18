@extends('homepage.packages.layout')
@section('page_title', 'Payment Status')
@section('body')
<div class="lebd-order-page"><section class="lebd-order-box" style="max-width:620px;margin:24px auto;text-align:center">
@if($state === 'success')<h1>পেমেন্ট সফল হয়েছে</h1><p>আপনার MCQ Subscription চালু হয়েছে।</p>
@elseif($state === 'failed')<h1>পেমেন্ট ব্যর্থ হয়েছে</h1><p>কোনো Subscription চালু করা হয়নি।</p>
@elseif($state === 'canceled')<h1>পেমেন্ট বাতিল হয়েছে</h1><p>আপনি আবার চেষ্টা করতে পারবেন।</p>
@else<h1>পেমেন্ট যাচাই চলছে</h1><p>অনুগ্রহ করে অপেক্ষা করুন; যাচাই ছাড়া Subscription চালু করা হবে না।</p>@endif
<a class="up-inline-back" href="{{ route('lebd.packages.index',['section'=>'mcq']) }}">← প্যাকেজ তালিকায় ফিরুন</a>
</section></div>
@endsection
