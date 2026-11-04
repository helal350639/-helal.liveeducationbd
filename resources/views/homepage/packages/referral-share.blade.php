{{-- Optional, read-only share link for an active MCQ package holder. No order/payment writes. --}}
@php
    $lebdRefStudent = \Illuminate\Support\Facades\Auth::guard('student')->user();
    $lebdCanRefer = $lebdRefStudent && !empty($lebdRefStudent->referral_id)
        && \App\Services\MCQSubscription\ReferralQuote::ownsActivePackage((int) $lebdRefStudent->id, (int) $package->id);
    $lebdReferralUrl = $lebdCanRefer
        ? route('lebd.packages.details', ['section' => 'mcq', 'package' => (int) $package->id, 'ref' => (string) $lebdRefStudent->referral_id])
        : null;
@endphp
@if($lebdCanRefer)
<section class="lebd-mcq-referral-share" style="border:1px solid #d6e8db;border-radius:12px;padding:14px;margin:16px 0;background:#f5fff7">
    <strong>এই প্যাকেজটি বন্ধুদের রেফার করুন</strong>
    <p style="margin:8px 0">আপনার রেফারেল লিংক:</p>
    <input type="text" class="form-control" readonly value="{{ $lebdReferralUrl }}" aria-label="Referral Link" onclick="this.select()">
    <p style="font-size:12px;margin:8px 0 0">আপনার বন্ধু এই লিংক দিয়ে প্যাকেজ কিনলে যোগ্যতার ভিত্তিতে ছাড় পাবেন। বোনাস কেবল সফল ও যাচাইকৃত পেমেন্টের পর প্রযোজ্য হবে, যখন Payment Integration সম্পূর্ণ হবে।</p>
</section>
@endif
