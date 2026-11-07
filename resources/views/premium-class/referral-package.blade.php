@extends('homepage')
@section('title', 'Premium Package Referral — Live Education BD')
@section('content')
<style>
/* LEBD-05 V12: Scoped responsive UI only; checkout URL and referral values are unchanged. */
.lebd-rp12,.lebd-rp12 *{box-sizing:border-box}
.lebd-rp12{--rp-blue:#0875df;--rp-ink:#17385e;--rp-line:#d9e6f5;max-width:890px;width:100%;margin:20px auto 40px;padding:0 15px;color:var(--rp-ink);font-family:inherit}
.lebd-rp12 a{text-decoration:none}
.lebd-rp12-head{background:linear-gradient(105deg,#102758,#075d99);border-radius:10px;color:#fff;padding:17px 21px;margin-bottom:13px}
.lebd-rp12-kicker{display:block;font-size:10px;font-weight:800;letter-spacing:.06em;opacity:.86;margin-bottom:5px}
.lebd-rp12-head h1{font-size:clamp(18px,2.3vw,23px);font-weight:800;line-height:1.3;color:#fff;margin:0 0 5px}
.lebd-rp12-head p{font-size:12px;line-height:1.5;margin:0;color:#e9f4ff}
.lebd-rp12-card{background:#fff;border:1px solid var(--rp-line);box-shadow:0 3px 12px rgba(21,59,100,.05);border-radius:10px;overflow:hidden}
.lebd-rp12-cardhead{background:#eef5fc;border-bottom:1px solid var(--rp-line);padding:12px 17px;display:flex;align-items:center;justify-content:space-between;gap:10px}
.lebd-rp12-cardhead h2{margin:0;color:#133963;font-size:18px;font-weight:800;line-height:1.35;overflow-wrap:anywhere}
.lebd-rp12-type{font-size:11px;font-weight:700;color:#175caa;border:1px solid #c4dafa;background:#fff;border-radius:5px;padding:5px 8px;white-space:nowrap}
.lebd-rp12-body{padding:15px 17px 16px}
.lebd-rp12-details{display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1fr);gap:16px}
.lebd-rp12-info{min-width:0;border-right:1px solid var(--rp-line);padding-right:16px}
.lebd-rp12-identity{background:#eff6ff;border-radius:7px;padding:10px 11px;display:flex;align-items:center;gap:8px;min-width:0;font-size:12px}
.lebd-rp12-identity span{color:#436486;white-space:nowrap}.lebd-rp12-identity strong{color:#143b66;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;min-width:0}
.lebd-rp12-meta{font-size:12px;color:#496580;margin:10px 0 0;line-height:1.6}
.lebd-rp12-meta strong{color:#19446d}
.lebd-rp12-prices{min-width:0}
.lebd-rp12-line{display:flex;justify-content:space-between;align-items:center;gap:9px;padding:9px 0;border-bottom:1px solid var(--rp-line);font-size:12px;line-height:1.35}
.lebd-rp12-line span{min-width:0}.lebd-rp12-line strong{font-size:13px;font-variant-numeric:tabular-nums;white-space:nowrap}
.lebd-rp12-line.discount strong{color:#087845}
.lebd-rp12-line.total{background:#e9f8ef;border:0;border-radius:7px;margin-top:9px;padding:11px;color:#087845;font-weight:800;font-size:14px}
.lebd-rp12-line.total strong{font-size:19px}
.lebd-rp12-actions{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:14px}
.lebd-rp12-actions a{min-height:41px;border-radius:7px;padding:10px 9px;display:flex;align-items:center;justify-content:center;text-align:center;font-weight:750;font-size:12px;line-height:1.45;overflow-wrap:anywhere}
.lebd-rp12-buy{color:#fff!important;background:var(--rp-blue)}.lebd-rp12-buy:hover{background:#075db3}
.lebd-rp12-more{color:#145994!important;background:#f5f9fd;border:1px solid #bcd6f5}
.lebd-rp12-notice{font-size:11px;line-height:1.55;color:#4d6783;background:#f4f8ff;border-radius:6px;padding:9px 11px;margin:12px 0 0}
@media(max-width:640px){
 .lebd-rp12{margin:8px auto 20px;padding:0 9px}
 .lebd-rp12-head{padding:12px 13px;margin-bottom:9px;border-radius:8px}
 .lebd-rp12-kicker{font-size:9px;margin-bottom:3px}
 .lebd-rp12-head h1{font-size:16px;margin-bottom:3px}
 .lebd-rp12-head p{font-size:10.5px;line-height:1.45}
 .lebd-rp12-card{border-radius:8px}
 .lebd-rp12-cardhead{padding:9px 11px;gap:6px}
 .lebd-rp12-cardhead h2{font-size:15px}
 .lebd-rp12-type{font-size:10px;padding:3px 6px}
 .lebd-rp12-body{padding:10px 11px 12px}
 .lebd-rp12-details{grid-template-columns:minmax(0,1fr);gap:6px}
 .lebd-rp12-info{border-right:0;padding-right:0}
 .lebd-rp12-identity{padding:8px 9px;font-size:11px}
 .lebd-rp12-meta{font-size:11px;margin:7px 0 3px}
 .lebd-rp12-line{font-size:11.5px;padding:7px 0}
 .lebd-rp12-line strong{font-size:12px}
 .lebd-rp12-line.total{padding:9px;margin-top:7px;font-size:13px}
 .lebd-rp12-line.total strong{font-size:17px}
 .lebd-rp12-actions{gap:7px;margin-top:11px}
 .lebd-rp12-actions a{min-height:38px;padding:8px 6px;font-size:11px;border-radius:6px}
 .lebd-rp12-notice{font-size:10px;padding:8px 9px;margin-top:9px}
}
@media(max-width:340px){.lebd-rp12-actions{grid-template-columns:1fr}.lebd-rp12-actions a{min-height:35px}.lebd-rp12-identity{flex-wrap:wrap}.lebd-rp12-identity strong{max-width:100%}}
</style>
<main class="lebd-rp12">
 <header class="lebd-rp12-head">
  <span class="lebd-rp12-kicker">LIVE EDUCATION BD · REFERRAL PACKAGE</span>
  <h1>আপনার পছন্দের প্যাকেজ বিশেষ ছাড়ে কিনুন</h1>
  <p>এই Referral Link-এ শুধু নিচের প্যাকেজটির জন্য ছাড় প্রযোজ্য।</p>
 </header>
 <section class="lebd-rp12-card" aria-label="Referral package summary">
  <div class="lebd-rp12-cardhead">
   <h2>{{ $package->name }}</h2>
   <span class="lebd-rp12-type">{{ ucwords(str_replace(['-','_'],' ',(string) $package->package_type)) }}</span>
  </div>
  <div class="lebd-rp12-body">
   <div class="lebd-rp12-details">
    <div class="lebd-rp12-info">
     <div class="lebd-rp12-identity"><span>Referred By (Student)</span><strong>{{ $owner->name ?: 'Premium Student' }}</strong></div>
     <p class="lebd-rp12-meta"><strong>মেয়াদ:</strong> {{ $package->duration_value ?? $package->validity_days }} {{ $package->duration_unit ?? 'days' }}</p>
    </div>
    <div class="lebd-rp12-prices">
     <div class="lebd-rp12-line"><span>Package Price</span><strong>৳{{ number_format($basePrice, 2) }}</strong></div>
     @if($extraFee > 0)
      <div class="lebd-rp12-line"><span>Extra Fee</span><strong>+ ৳{{ number_format($extraFee, 2) }}</strong></div>
     @endif
     <div class="lebd-rp12-line discount"><span>Referral Discount</span><strong>− ৳{{ number_format($referralDiscount, 2) }}</strong></div>
     <div class="lebd-rp12-line total"><span>Payable</span><strong>৳{{ number_format($payable, 2) }}</strong></div>
    </div>
   </div>
   <div class="lebd-rp12-actions">
    <a class="lebd-rp12-buy" href="{{ $purchaseUrl }}">এই প্যাকেজটি কিনুন →</a>
    <a class="lebd-rp12-more" href="{{ route('premium.recordings.packages') }}">আরও প্যাকেজ দেখুন</a>
   </div>
   <p class="lebd-rp12-notice">অন্য প্যাকেজে এই Referral Discount প্রযোজ্য নয়। Checkout-এ Student Login ও Referral Eligibility আবার যাচাই করা হবে।</p>
  </div>
 </section>
</main>
@endsection
