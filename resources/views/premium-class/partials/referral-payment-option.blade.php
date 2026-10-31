{{-- Include INSIDE @auth('student') after the SSLCommerz form; do not nest forms. --}}
@php
    $lebdStudent = auth('student')->user();
    $lebdPending = \App\Models\WithdrawRequest::where('student_id', $lebdStudent->id)
        ->where('status', 'pending')->sum('amount');
    $lebdAvailable = max(0, round((float) $lebdStudent->referral_balance - (float) $lebdPending, 2));
    $lebdPrice = round((float) ($package->final_price ?? $package->price), 2);
@endphp
<div class="lebd-referral-option" style="border:1px solid #d2e4fa;border-radius:12px;padding:16px;margin-top:16px;background:#f7fbff;">
  <h3 style="margin:0 0 8px;font-size:18px;">Use Your Referral Balance</h3>
  <p style="margin:0 0 8px;">Available: <strong>৳{{number_format($lebdAvailable,2)}}</strong></p>
  <p style="margin:0 0 12px;">Package: <strong>৳{{number_format($lebdPrice,2)}}</strong></p>
  <form method="POST" action="{{route('premium.payment.referral.checkout', $package)}}">
    @csrf
    <button type="submit" class="lebd114-buy" {{ $lebdAvailable < $lebdPrice ? 'disabled' : '' }}
       style="width:100%;{{ $lebdAvailable < $lebdPrice ? 'opacity:.5;cursor:not-allowed;' : '' }}">
       {{ $lebdAvailable >= $lebdPrice ? 'Pay with Referral Balance →' : 'Insufficient Referral Balance' }}
    </button>
  </form>
</div>
