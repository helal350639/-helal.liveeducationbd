@php
    $quotaIsQuiz = ($noticeType ?? 'reading') === 'quiz';
@endphp
<section class="lebd-limit-notice" role="status" aria-live="polite">
    <div class="lebd-limit-symbol" aria-hidden="true"><i class="fa fa-lock"></i></div>
    <div class="lebd-limit-copy">
        <h3>{{ $quotaIsQuiz ? 'আপনার Free Quiz Limit শেষ!' : 'আজকের Free MCQ Reading Limit শেষ!' }}</h3>
        <p>{{ $quotaIsQuiz ? 'এই মাসের Free Quiz ব্যবহার শেষ হয়েছে। নতুন Quiz দিতে একটি MCQ Subscription Package নির্বাচন করুন।' : 'আজকের Free MCQ Reading Limit শেষ হয়েছে। আরও নতুন প্রশ্ন পড়তে একটি MCQ Subscription Package নির্বাচন করুন।' }}</p>
        <small>{{ $quotaIsQuiz ? 'পরবর্তী মাসের ১ তারিখে Free Quiz Limit আবার চালু হবে।' : 'বাংলাদেশ সময় রাত ১২টার পরে দৈনিক Free Reading Limit আবার চালু হবে।' }}</small>
    </div>
    <a class="lebd-limit-buy" href="{{ url('/packages/mcq') }}"><i class="fa fa-shopping-bag" aria-hidden="true"></i> প্যাকেজ কিনুন <i class="fa fa-arrow-right" aria-hidden="true"></i></a>
</section>
