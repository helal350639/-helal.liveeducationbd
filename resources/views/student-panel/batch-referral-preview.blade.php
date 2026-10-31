@extends('student')
@section('title','Batch Payment Preview')
@section('content')
<style>
.lebdbr-wrap{max-width:940px;margin:28px auto;padding:0 14px 35px;color:#092b68}.lebdbr-panel{background:#fff;border:1px solid #cfe0f5;border-radius:18px;padding:25px;box-shadow:0 12px 30px #092b6810}
.lebdbr-head{display:flex;gap:15px;align-items:center;margin:12px 0 23px}.lebdbr-head i{background:#9342ca;color:#fff;border-radius:50%;padding:18px}.lebdbr-head h1{font-size:28px;margin:0}.lebdbr-head p{margin:5px 0;color:#52719b}
.lebdbr-batch{display:flex;gap:18px;padding:15px;background:#f2f8ff;border:1px solid #d1e5ff;border-radius:12px;align-items:center}.lebdbr-batch img{width:190px;aspect-ratio:16/9;object-fit:cover;border-radius:9px}.lebdbr-batch h2{font-size:21px}
.lebdbr-box{margin-top:20px;border:1px solid #dce7f5;border-radius:14px;padding:15px}.lebdbr-row{display:flex;justify-content:space-between;gap:20px;padding:14px 6px;border-bottom:1px solid #e4ecf5}.lebdbr-row:last-child{border:0}.lebdbr-green{color:#087b42;background:#ecfbf2;border-radius:10px;font-weight:750;padding:16px}.lebdbr-red{color:#b42318}
.lebdbr-btn{display:block;width:100%;border:0;border-radius:10px;padding:15px;font-size:18px;font-weight:700;text-align:center;margin-top:14px;text-decoration:none;cursor:pointer;background:#0875df;color:#fff}.lebdbr-cancel{background:#fff;color:#0954ae;border:1px solid #9dc4f8}
@media(max-width:600px){.lebdbr-panel{padding:13px}.lebdbr-batch{flex-direction:column;align-items:start}.lebdbr-batch img{width:100%}.lebdbr-head h1{font-size:23px}}
</style>
<div class="lebdbr-wrap"><div class="lebdbr-panel">
  <a href="{{ route('students.course.checkout',$batch->id) }}">← প্যাকেজে ফিরে যান</a>
  <div class="lebdbr-head"><i class="fa-solid fa-cart-shopping"></i><div><h1>Payment Preview</h1><p>Referral Balance থেকে টাকা কাটার আগে যাচাই করুন।</p></div></div>
  <div class="lebdbr-batch">
    @if($batch->banner)<img src="{{ $batch->banner }}" alt="{{ $batch->name }}">@endif
    <div><h2>{{ $batch->name }}</h2><p>Department: {{ $department->name }}</p><p>Payment: Referral Balance</p></div>
  </div>
  <div class="lebdbr-box"><h3>পেমেন্টের সারসংক্ষেপ</h3>
    <div class="lebdbr-row"><span>ব্যাচের মোট মূল্য</span><strong>৳{{ number_format($price,2) }}</strong></div>
    <div class="lebdbr-row"><span>আপনার Available Referral Balance</span><strong>৳{{ number_format($available,2) }}</strong></div>
    <div class="lebdbr-row"><span>Referral Balance থেকে কাটা হবে</span><strong class="lebdbr-red">৳{{ number_format($price,2) }}</strong></div>
    <div class="lebdbr-row lebdbr-green"><span>পেমেন্টের পর Available Balance</span><strong>৳{{ number_format($remaining,2) }}</strong></div>
  </div>
  <form action="{{ route('students.batch.referral.confirm') }}" method="POST" onsubmit="this.querySelector('button').disabled=true;">
    @csrf<input type="hidden" name="token" value="{{ $token }}">
    <button type="submit" class="lebdbr-btn">✓ কনফার্ম পেমেন্ট</button>
  </form>
  <a class="lebdbr-btn lebdbr-cancel" href="{{ route('students.course.checkout',$batch->id) }}">← বাতিল করুন</a>
</div></div>
@endsection
