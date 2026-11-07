@extends('homepage')
@section('title','My Premium Referral Links')
@section('content')
<div style="max-width:900px;margin:35px auto;padding:18px;color:#12345b">
 <h1>My Premium Video Referral Links</h1>
 <p>শুধু আপনার কেনা ও এখনো Active Premium Package-এর Referral Link শেয়ার করতে পারবেন। নতুন Student Link দিয়ে সফলভাবে কিনলে Discount পাবে এবং আপনি Bonus পাবেন।</p>
 @if(!filled($student->referral_id))<p>আপনার Referral Code তৈরি হয়নি। Admin Support-এর সঙ্গে যোগাযোগ করুন।</p>@endif
 @forelse($packages as $package)
  @php $url=$student->referral_id ? route('premium.referral.package', ['package' => (int) $package->id]).'?ref='.rawurlencode((string)$student->referral_id) : '';  @endphp
  <article style="background:#fff;border:1px solid #d6e5f5;padding:18px;margin:14px 0;border-radius:12px">
    <h3>{{ $package->name }}</h3>
    <p>Referral Discount: ৳{{ number_format((float)($package->referral_discount ?? 0),2) }} | Referrer Bonus: ৳{{ number_format((float)($package->referrer_bonus ?? 0),2) }}</p>
    <label>Copy and share ONLY this package referral link
     <input type="text" readonly style="width:100%;padding:10px;margin-top:8px" value="{{ $url }}" onclick="this.select()">
    </label>
    @if($url !== '')<button type="button" onclick="navigator.clipboard && navigator.clipboard.writeText(this.previousElementSibling.querySelector('input').value).then(()=>{this.textContent='✓ Copied'},()=>{this.previousElementSibling.querySelector('input').select()})" style="margin-top:10px;padding:9px 14px;border:0;border-radius:7px;background:#0968c3;color:#fff;cursor:pointer">Copy Full Referral Link</button>@endif
    <p style="font-size:13px;margin-top:8px">অন্য Student Account থেকে সম্পূর্ণ Link খুলবেন। URL-এ <code>?ref=...</code> থাকতে হবে; সাধারণ Package Link-এ Discount নেই।</p>
  </article>
 @empty
  <p>Active purchased Premium Package নেই, তাই এখন Referral Link নেই।</p>
 @endforelse
 <a href="{{ route('premium.recordings.packages') }}">← Premium Packages</a>
</div>
@endsection
