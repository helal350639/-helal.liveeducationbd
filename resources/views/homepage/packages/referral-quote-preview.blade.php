{{-- Optional include for existing MCQ review page. Visual quote only.
     Do not show this as payable or enable submit until BOTH payment controllers
     use the same validated server-side amount and referrer snapshot. --}}
@php
    $lebdBuyerId = \Illuminate\Support\Facades\Auth::guard('student')->id();
    $lebdCode = request()->query('ref', '');
    $lebdQuote = \App\Services\MCQSubscription\ReferralQuote::resolve(
        $package,
        $lebdBuyerId ? (int) $lebdBuyerId : null,
        is_string($lebdCode) ? $lebdCode : null
    );
@endphp
<div class="lebd-ref-quote" style="border:1px solid #d9e5f4;border-radius:12px;padding:16px;margin:14px 0;background:#fff">
    <h3 style="margin:0 0 12px">Referral Price Preview</h3>
    <div style="display:flex;justify-content:space-between"><span>Package Price</span><strong>৳{{ $lebdQuote['base_price'] }}</strong></div>
    <div style="display:flex;justify-content:space-between"><span>Extra Fee</span><strong>৳{{ $lebdQuote['extra_fee'] }}</strong></div>
    <div style="display:flex;justify-content:space-between;color:#087a3e"><span>Referral Discount</span><strong>− ৳{{ $lebdQuote['referral_discount'] }}</strong></div>
    <hr>
    <div style="display:flex;justify-content:space-between"><strong>Estimated After Referral</strong><strong>৳{{ $lebdQuote['payable'] }}</strong></div>
    @if($lebdQuote['valid'])
        <p style="color:#087a3e;margin-top:10px">Referral code is eligible for this package. Referrer bonus after confirmed purchase: ৳{{ $lebdQuote['referrer_bonus_on_success'] }}.</p>
    @elseif(is_string($lebdCode) && $lebdCode !== '')
        <p role="alert" style="color:#aa4d00;margin-top:10px">Referral is not eligible ({{ $lebdQuote['reason'] }}). No referral discount applied.</p>
    @endif
    <p style="color:#9a4b00;font-size:12px;margin-top:10px">Preview only: the referral amount must be integrated into BOTH payment controllers before it can be charged. Current payment buttons must continue to use their existing confirmed amounts.</p>
</div>
