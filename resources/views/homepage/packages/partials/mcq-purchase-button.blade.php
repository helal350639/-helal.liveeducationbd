{{-- LEBD-05: MCQ package buy button, per-student and per-package only. --}}
@php
    $mcqStudent = auth('student')->user();
    $mcqAlreadyPurchased = false;
    if ($mcqStudent && isset($package->id)) {
        $mcqAlreadyPurchased = \Illuminate\Support\Facades\DB::table('lebd_mcq_subscriptions')
            ->where('student_id', $mcqStudent->id)
            ->where('package_id', $package->id)
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>', now())
            ->exists();
    }
@endphp
@if($mcqAlreadyPurchased)
    <button type="button" disabled aria-disabled="true"
            style="background:#e6eef8;color:#24537b;border:1px solid #adc8e5;padding:10px 18px;border-radius:9px;font-weight:700;cursor:not-allowed;opacity:1">
        এই প্যাকেজ কিনেছেন
    </button>
@else
    <a href="{{ route('lebd.packages.checkout', ['section' => $section ?? 'mcq', 'package' => $package->id]) }}"
       class="lebd-mcq-buy-btn" style="display:inline-block;background:#3d27e9;color:white;border-radius:9px;padding:10px 18px;font-weight:700;text-decoration:none">
        প্যাকেজ কিনুন
    </a>
@endif
