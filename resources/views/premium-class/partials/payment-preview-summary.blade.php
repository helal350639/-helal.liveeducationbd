{{-- Reusable read-only display for E-Book, MCQ, Batch, Video and Exam integrations.
Required: $lebdTitle, $lebdPrice, $lebdBalance, $lebdRemaining, $lebdMethod; optional $lebdOriginalPrice. --}}
<div class="lebdpv-panel">
  <div class="lebdpv-heading"><span class="lebdpv-icon">▣</span><div><h2>পেমেন্টের সারসংক্ষেপ</h2><p>Confirm করার আগে মূল্য ও ব্যালেন্স যাচাই করুন</p></div></div>
  @if(isset($lebdOriginalPrice) && $lebdOriginalPrice > $lebdPrice)
   <div class="lebdpv-line"><span>মূল দাম</span><strong>৳{{number_format($lebdOriginalPrice,2)}}</strong></div>
   <div class="lebdpv-line"><span>ছাড়</span><strong>− ৳{{number_format($lebdOriginalPrice-$lebdPrice,2)}}</strong></div>
  @endif
  <div class="lebdpv-line"><span>প্যাকেজের মূল্য (সর্বমোট)</span><strong>৳{{number_format($lebdPrice,2)}}</strong></div>
  <div class="lebdpv-line"><span>আপনার Available Referral Balance</span><strong>৳{{number_format($lebdBalance,2)}}</strong></div>
  <div class="lebdpv-line"><span>{{ $lebdMethod === 'referral' ? 'Confirm করলে ব্যালেন্স থেকে কাটবে' : 'SSLCommerz-এ পরিশোধযোগ্য' }}</span><strong>৳{{number_format($lebdPrice,2)}}</strong></div>
  <div class="lebdpv-remaining"><span>{{ $lebdMethod === 'referral' ? 'পেমেন্টের পর অবশিষ্ট Referral Balance' : 'SSLCommerz পেমেন্টে Referral Balance অপরিবর্তিত' }}</span><strong>৳{{number_format($lebdRemaining,2)}}</strong></div>
</div>
