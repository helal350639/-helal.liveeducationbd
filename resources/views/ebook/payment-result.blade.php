@extends('homepage')
@section('title','E-Book Payment Result')
@section('content')
<main style="max-width:650px;margin:50px auto;padding:24px;text-align:center;border:1px solid #dce5f0;border-radius:15px">
<h1>@if($state==='success')✅ Payment Verified @elseif($state==='pending')⏳ Verification Pending @elseif($state==='canceled')Payment Canceled @else Payment Failed @endif</h1>
<p>@if($state==='success')Premium Access সক্রিয় হয়েছে। এখন সংশ্লিষ্ট E-Book পড়তে পারবেন।@elseif($state==='pending')Gateway Verification এখনো নিশ্চিত নয়। Premium Access এখনো সক্রিয় হয়নি।@else Premium Access সক্রিয় হয়নি।@endif</p>
<p>Order: {{ $order->transaction_id ?? '' }}</p>
<a href="{{route('student.ebook.chapters',['kind'=>DB::table('lebd_ebook_categories')->where('id',$order->category_id)->value('kind') ?? 'department','category'=>$order->category_id])}}">বইয়ের সূচিপত্রে ফিরুন</a>
</main>
@endsection
