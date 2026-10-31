{{-- LEBD-05 additive student dashboard component: purchased MCQ subscription cards.
     Shows only the authenticated student's active paid subscriptions.
     Does NOT bypass or create content permission routes. --}}
@php
    $lebdMcqStudent = auth('student')->user();
    $lebdMcqSubscriptions = collect();
    if ($lebdMcqStudent && \Illuminate\Support\Facades\Schema::hasTable('lebd_mcq_subscriptions')
        && \Illuminate\Support\Facades\Schema::hasTable('mcq_subscription_packages')) {
        $lebdMcqSubscriptions = \Illuminate\Support\Facades\DB::table('lebd_mcq_subscriptions as sub')
            ->join('mcq_subscription_packages as pkg', 'pkg.id', '=', 'sub.package_id')
            ->where('sub.student_id', $lebdMcqStudent->id)
            ->where('sub.starts_at', '<=', now())
            ->where('sub.ends_at', '>', now())
            ->select('sub.package_id', 'sub.ends_at', 'pkg.name', 'pkg.access_type',
                'pkg.reading_limit', 'pkg.quiz_limit')
            ->orderBy('sub.ends_at')
            ->get();
    }
    $lebdMcqTypeNames = ['department' => 'Department', 'non-department' => 'Non-Department', 'both' => 'Combined'];
@endphp
<section class="lebd-mcq-purchased" aria-label="MCQ Study আমার কেনা প্যাকেজ">
    <style>
        .lebd-mcq-purchased{max-width:1180px;margin:22px auto;padding:0 12px;color:#12315c}
        .lebd-mcq-purchased__top{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:15px}
        .lebd-mcq-purchased__top h2{font-size:23px;font-weight:800;margin:0}
        .lebd-mcq-purchased__top p{margin:5px 0 0;color:#456590;font-size:13px}
        .lebd-mcq-purchased__new{background:#086bce;color:white;border-radius:9px;padding:10px 14px;text-decoration:none;font-weight:700}
        .lebd-mcq-purchased__grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:13px}
        .lebd-mcq-purchased__card{min-width:0;background:#fff;border:1px solid #d4e5ff;border-radius:13px;padding:17px;box-shadow:0 6px 18px rgba(11,60,115,.05)}
        .lebd-mcq-purchased__card h3{font-size:17px;font-weight:750;margin:0 0 8px;overflow-wrap:anywhere}
        .lebd-mcq-purchased__active{display:inline-block;background:#dcfbea;color:#087545;border-radius:20px;padding:4px 9px;font-size:11px;font-weight:800}
        .lebd-mcq-purchased__meta{font-size:13px;color:#345782;margin:9px 0}
        .lebd-mcq-purchased__empty{padding:20px;background:#edf6ff;border-radius:12px;color:#426184}
        .lebd-mcq-purchased__link{display:block;text-align:center;background:#0d71df;color:#fff;border-radius:8px;padding:10px;text-decoration:none;font-weight:750;margin-top:15px}
        @media(max-width:800px){.lebd-mcq-purchased__grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
        @media(max-width:550px){.lebd-mcq-purchased__grid{grid-template-columns:1fr}.lebd-mcq-purchased__top h2{font-size:20px}}
    </style>
    <div class="lebd-mcq-purchased__top">
        <div><h2>▣ MCQ Study — আমার কেনা প্যাকেজ</h2>
             <p>আপনার সক্রিয় MCQ Subscription Package সমূহ</p></div>
        <a class="lebd-mcq-purchased__new" href="{{ route('lebd.packages.index', ['section' => 'mcq']) }}">＋ নতুন প্রিমিয়াম প্যাকেজ দেখুন</a>
    </div>
    @if($lebdMcqSubscriptions->isEmpty())
        <div class="lebd-mcq-purchased__empty">এখন কোনো সক্রিয় MCQ Study Package নেই।</div>
    @else
        <div class="lebd-mcq-purchased__grid">
        @foreach($lebdMcqSubscriptions as $lebdMcqSub)
            <article class="lebd-mcq-purchased__card">
                <h3>{{ $lebdMcqSub->name }}</h3>
                <span class="lebd-mcq-purchased__active">Active</span>
                <div class="lebd-mcq-purchased__meta">মেয়াদ: {{ \Illuminate\Support\Carbon::parse($lebdMcqSub->ends_at)->format('d M Y, h:i A') }}</div>
                <div class="lebd-mcq-purchased__meta">প্যাকেজ ধরন: {{ $lebdMcqTypeNames[$lebdMcqSub->access_type] ?? $lebdMcqSub->access_type }}</div>
                <div class="lebd-mcq-purchased__meta">Reading: {{ $lebdMcqSub->reading_limit === null ? 'Unlimited' : $lebdMcqSub->reading_limit }}</div>
                <div class="lebd-mcq-purchased__meta">Quiz: {{ $lebdMcqSub->quiz_limit === null ? 'Unlimited' : $lebdMcqSub->quiz_limit }}</div>
                <a class="lebd-mcq-purchased__link" href="{{ route('lebd.packages.details', ['section' => 'mcq','package' => $lebdMcqSub->package_id]) }}">প্যাকেজের বিস্তারিত দেখুন</a>
            </article>
        @endforeach
        </div>
    @endif
</section>
