@extends('student')

@section('title', 'আমার ব্যাচ সমূহ')

@section('stylesheets')
<style>
/* LEBD My Course - isolated layout: never changes the shared homepage header */
.lebd-my-course,
.lebd-my-course * { box-sizing: border-box; }
.lebd-my-course {
    --my-navy: #0d2857;
    --my-blue: #1169de;
    --my-light: #edf5ff;
    --my-green: #08a56c;
    width: 100%;
    min-width: 0;
    min-height: 60vh;
    padding: 22px 16px 42px;
    background: #f5f8fd;
    color: var(--my-navy);
    font-family: inherit;
}
.lebd-my-course .my-course-shell {
    max-width: 1250px;
    width: 100%;
    margin-inline: auto;
}
.lebd-my-course .dashboard-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
    padding: 0 1px;
    margin-bottom: 17px;
}
.lebd-my-course .page-title h1 {
    font-size: clamp(21px, 2vw, 26px);
    font-weight: 800;
    margin: 0 0 3px;
    line-height: 1.25;
}
.lebd-my-course .page-title p {
    font-size: 12px;
    color: #70809e;
    margin: 0;
}
.lebd-my-course .referral-id {
    background: #fff;
    border: 1px solid #deebfa;
    box-shadow: 0 4px 14px rgba(22, 70, 145, .05);
    border-radius: 14px;
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 8px 11px 8px 15px;
    min-width: 0;
}
.lebd-my-course .referral-text { min-width: 0; }
.lebd-my-course .referral-text small { color: #7586a0; font-size: 11px; display: block; }
.lebd-my-course .referral-text strong { font-size: 14px; color: #123765; overflow-wrap: anywhere; }
.lebd-my-course .referral-id button {
    border: 0;
    border-radius: 9px;
    background: var(--my-blue);
    color: #fff;
    padding: 9px 12px;
    font-size: 12px;
    font-weight: 700;
    cursor: pointer;
    white-space: nowrap;
}
.lebd-my-course .my-section {
    padding: 19px 20px 21px;
    border-radius: 20px;
    border: 1px solid #e2ebf8;
    margin-bottom: 18px;
    box-shadow: 0 5px 20px rgba(29, 70, 127, .05);
}
.lebd-my-course .my-section.purchased-section { background: linear-gradient(135deg, #edf6ff, #f9fcff); }
.lebd-my-course .my-section.running-section { background: #f1fbf8; border-color: #dcefe8; }
.lebd-my-course .my-section.free-section { background: #f7f5ff; border-color: #ebe4ff; }
.lebd-my-course .section-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
    margin-bottom: 17px;
}
.lebd-my-course .section-left { display: flex; align-items: center; gap: 11px; min-width: 0; }
.lebd-my-course .section-icon {
    flex: 0 0 38px;
    width: 38px;
    height: 38px;
    display: grid;
    place-items: center;
    border-radius: 12px;
    color: #fff;
    background: linear-gradient(140deg, #0d74f5, #064db6);
    font-size: 18px;
}
.lebd-my-course .running-section .section-icon { background: linear-gradient(140deg,#10bd89,#078859); }
.lebd-my-course .free-section .section-icon { background: linear-gradient(140deg,#8f68ef,#6040c0); }
.lebd-my-course .section-left h2 { margin: 0; font-size: 18px; font-weight: 800; line-height: 1.4; }
.lebd-my-course .section-left p { margin: 2px 0 0; color: #6b7f9d; font-size: 11px; line-height: 1.5; }
.lebd-my-course .count-box {
    color: #1754a0;
    background: #dfedff;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 700;
    padding: 7px 13px;
    white-space: nowrap;
}
.lebd-my-course .running-section .count-box { background: #d6f9e9; color: #08734f; }
.lebd-my-course .free-section .count-box { background: #eae1ff; color: #6740ad; }
.lebd-my-course .course-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 15px;
    align-items: stretch;
}
.lebd-my-course .course-card {
    width: 100%;
    min-width: 0;
    height: 100%;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    border: 1px solid #e0e9f7;
    border-radius: 15px;
    background: #fff;
    box-shadow: 0 5px 12px rgba(17, 57, 106, .07);
    transition: transform .2s ease, box-shadow .2s ease;
}
.lebd-my-course .course-card:hover { transform: translateY(-3px); box-shadow: 0 10px 22px rgba(17, 57, 106, .12); }
.lebd-my-course .course-image {
    position: relative;
    aspect-ratio: 16 / 9;
    overflow: hidden;
    background: linear-gradient(135deg, #dfefff, #eef5fd 60%, #e7fbf6);
    display: grid;
    place-items: center;
}
.lebd-my-course .image-fallback { color: #267ac5; opacity: .30; font-size: 42px; }
.lebd-my-course .course-image img {
    position: absolute;
    inset: 0;
    display: block;
    width: 100%;
    height: 100%;
    object-fit: cover;
    z-index: 1;
}
.lebd-my-course .course-info {
    display: flex;
    flex-direction: column;
    flex: 1;
    gap: 7px;
    padding: 12px;
    min-width: 0;
}
.lebd-my-course .course-info h3 {
    color: #102f60;
    font-size: 14px;
    font-weight: 800;
    line-height: 1.45;
    margin: 0;
    overflow-wrap: anywhere;
    min-height: 2.9em;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.lebd-my-course .course-info p {
    margin: 0;
    color: #697c99;
    font-size: 11px;
    line-height: 1.5;
    overflow-wrap: anywhere;
    min-height: 2.9em;
}
.lebd-my-course .running-status {
    margin-top: auto;
    border-radius: 8px;
    background: #fff0f0;
    color: #db4750;
    padding: 7px 9px;
    font-size: 11px;
    line-height: 1.45;
    font-weight: 600;
}
.lebd-my-course .reference-actions { display: flex; gap: 7px; min-width: 0; }
.lebd-my-course .reference-actions button,
.lebd-my-course .detail-btn {
    font-family: inherit;
    font-weight: 700;
    font-size: 11px;
    border: none;
    text-decoration: none;
    border-radius: 8px;
    padding: 9px 7px;
    min-height: 34px;
    text-align: center;
    line-height: 1.4;
    cursor: pointer;
}
.lebd-my-course .reference-actions button { flex: 1; min-width: 0; }
.lebd-my-course .copy-reference-btn { background: #e5f0ff; color: #155fc0; }
.lebd-my-course .share-reference-btn,
.lebd-my-course .detail-btn { background: #08a66b; color: #fff; }
.lebd-my-course .detail-btn { display: block; width: 100%; }
.lebd-my-course .detail-btn:hover { color: #fff; background: #078b59; }
.lebd-my-course .reference-actions button:hover { filter: brightness(.96); }
.lebd-my-course .empty-box {
    grid-column: 1 / -1;
    border: 1px dashed #bcd1ed;
    border-radius: 12px;
    text-align: center;
    padding: 35px 14px;
    background: #fff;
    color: #68809b;
    font-size: 13px;
}
.lebd-my-course .notice-section {
    background: #fff;
    border-radius: 17px;
    padding: 16px 20px;
    border: 1px solid #e3ebf5;
    box-shadow: 0 4px 12px rgba(17, 57, 106, .04);
}
.lebd-my-course .notice-section h2 { color: #244777; font-size: 16px; font-weight: 800; margin: 0 0 9px; }
.lebd-my-course .notice-section p { font-size: 12px; line-height: 1.7; margin: 0 0 7px; color: #4e647f; }
.lebd-my-course .notice-box { font-size: 12px; color: #a36517; background: #fff6e7; border-radius: 8px; padding: 9px 11px; }
#reference-copy-notice {
    position: fixed;
    left: 50%;
    bottom: 26px;
    z-index: 99999;
    max-width: min(92vw, 460px);
    transform: translate(-50%, 20px);
    opacity: 0;
    visibility: hidden;
    transition: opacity .2s ease, transform .2s ease, visibility .2s ease;
    background: #087e59;
    color: #fff;
    padding: 11px 17px;
    border-radius: 11px;
    box-shadow: 0 8px 24px rgba(0, 0, 0, .16);
    font-size: 12px;
    text-align: center;
}
#reference-copy-notice.show { opacity: 1; visibility: visible; transform: translate(-50%, 0); }
@media (min-width: 1500px) {
    .lebd-my-course .my-course-shell { max-width: 1320px; }
}
@media (max-width: 1050px) {
    .lebd-my-course .course-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; }
}
@media (max-width: 720px) {
    .lebd-my-course { padding: 14px 10px 28px; }
    .lebd-my-course .dashboard-top { align-items: flex-start; flex-direction: column; gap: 10px; }
    .lebd-my-course .page-title h1 { font-size: 19px; }
    .lebd-my-course .referral-id { width: 100%; justify-content: space-between; }
    .lebd-my-course .my-section { padding: 13px 11px 15px; margin-bottom: 13px; border-radius: 15px; }
    .lebd-my-course .section-header { margin-bottom: 11px; gap: 8px; }
    .lebd-my-course .section-left { gap: 8px; }
    .lebd-my-course .section-icon { flex-basis: 31px; width: 31px; height: 31px; font-size: 15px; border-radius: 9px; }
    .lebd-my-course .section-left h2 { font-size: 15px; }
    .lebd-my-course .section-left p { font-size: 10px; }
    .lebd-my-course .count-box { font-size: 10px; padding: 6px 9px; }
    .lebd-my-course .course-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 9px; }
    .lebd-my-course .course-info { padding: 8px; gap: 6px; }
    .lebd-my-course .course-info h3 { font-size: 12px; line-height: 1.35; }
    .lebd-my-course .course-info p { font-size: 10px; }
    .lebd-my-course .running-status { padding: 6px; font-size: 10px; }
    .lebd-my-course .reference-actions { gap: 5px; }
    .lebd-my-course .reference-actions button, .lebd-my-course .detail-btn { font-size: 10px; padding: 7px 4px; min-height: 32px; }
    .lebd-my-course .notice-section { padding: 13px; }
}
@media (max-width: 345px) {
    .lebd-my-course .section-header { align-items: flex-start; }
    .lebd-my-course .count-box { margin-left: auto; }
    .lebd-my-course .reference-actions button { font-size: 9px; }
}
@media (prefers-reduced-motion: reduce) {
    .lebd-my-course .course-card { transition: none; }
}
</style>
@endsection

@section('content')
@php
    $user = Auth::guard('student')->user();
@endphp

<div class="lebd-my-course">
    <div class="my-course-shell">
        <div class="dashboard-top">
            <div class="page-title">
                <h1>আমার কোর্স ও ব্যাচ</h1>
                <p>শুধু আপনার কেনা অথবা এনরোল করা ব্যাচ এবং সেগুলোর পরীক্ষা</p>
            </div>
            <div class="referral-id">
                <div class="referral-text">
                    <small>আপনার রেফারেল ID</small>
                    <strong>{{ $user->referral_id ?? 'LEBD000020' }}</strong>
                </div>
                <button type="button" class="copy-referral-id" data-copy="{{ $user->referral_id ?? 'LEBD000020' }}">
                    <i class="fa fa-copy" aria-hidden="true"></i> Copy
                </button>
            </div>
        </div>

        {{-- MY PURCHASED BATCHES: existing collection and existing referral URLs are preserved --}}
        <section class="my-section purchased-section">
            <div class="section-header">
                <div class="section-left">
                    <span class="section-icon"><i class="fa fa-graduation-cap" aria-hidden="true"></i></span>
                    <div>
                        <h2>আমার কেনা ব্যাচ</h2>
                        <p>শুধু আপনার কেনা অথবা এনরোল করা ব্যাচ এখানে দেখানো হচ্ছে।</p>
                    </div>
                </div>
                <span class="count-box">ব্যাচ সংখ্যা: {{ $purchasedCourses->count() }}</span>
            </div>
            <div class="course-grid">
                @forelse($purchasedCourses as $batch)
                    <article class="course-card">
                        <div class="course-image">
                            <span class="image-fallback"><i class="fa fa-book-open" aria-hidden="true"></i></span>
                            <img src="{{ $batch->banner ?? '/img/course.png' }}" alt="{{ $batch->name }}" loading="lazy" onerror="this.style.display='none'">
                        </div>
                        <div class="course-info">
                            <h3 title="{{ $batch->name }}">{{ $batch->name }}</h3>
                            <p><i class="fa fa-calendar-days" aria-hidden="true"></i> {{ $batch->subtitle ?? 'Available Batch' }}</p>
                            <div class="running-status"><i class="fa fa-gift" aria-hidden="true"></i> Referrer Bonus: ৳{{ $batch->referrer_bonus ?? 0 }}</div>
                            <div class="reference-actions">
                                <button type="button" class="copy-reference-btn" data-url="{{ url('/register?ref='.$user->referral_id.'&batch='.$batch->id) }}">
                                    <i class="fa fa-link" aria-hidden="true"></i> Copy Link
                                </button>
                                <button type="button" class="share-reference-btn" data-title="{{ $batch->name }}" data-url="{{ url('/register?ref='.$user->referral_id.'&batch='.$batch->id) }}">
                                    <i class="fa fa-share-nodes" aria-hidden="true"></i> Share
                                </button>
                            </div>
                            <a class="detail-btn" style="display:block;text-align:center;margin-top:12px" href="{{ route('students.batch.purchased-exams', $batch->id) }}"><i class="fa fa-clipboard-list" aria-hidden="true"></i> ব্যাচের পরীক্ষা দেখুন</a>
                        </div>
                    </article>
                @empty
                    <div class="empty-box">আপনার অ্যাকাউন্টে এখনো কোনো কেনা বা এনরোল করা ব্যাচ নেই।</div>
                @endforelse
            </div>
        </section>

        <section class="notice-section">
            <h2><i class="fa fa-circle-info" aria-hidden="true"></i> নোটিশ</h2>
            <p>আপনি শুধু সেই সকল ব্যাচের লিংক কপি এবং শেয়ার করতে পারবেন যেগুলো আপনি Purchase করেছেন।</p>
            <div class="notice-box">বিস্তারিত জানতে এডমিন প্যানেলের সাথে যোগাযোগ করুন।</div>
        </section>
    </div>
</div>
<div id="reference-copy-notice" role="status" aria-live="polite"></div>
@endsection

@section('scripts')
<script>
(function () {
    'use strict';
    var noticeTimer;
    function showCopyNotice(message) {
        var notice = document.getElementById('reference-copy-notice');
        if (!notice) return;
        notice.textContent = message;
        notice.classList.add('show');
        window.clearTimeout(noticeTimer);
        noticeTimer = window.setTimeout(function () { notice.classList.remove('show'); }, 2600);
    }
    function fallbackCopy(text) {
        var area = document.createElement('textarea');
        area.value = text;
        area.setAttribute('readonly', '');
        area.style.position = 'fixed';
        area.style.opacity = '0';
        document.body.appendChild(area);
        area.select();
        var success = document.execCommand('copy');
        area.remove();
        if (!success) throw new Error('Copy failed');
    }
    function copyText(text) {
        if (navigator.clipboard && window.isSecureContext) {
            return navigator.clipboard.writeText(text).catch(function () { fallbackCopy(text); });
        }
        return new Promise(function (resolve, reject) {
            try { fallbackCopy(text); resolve(); } catch (e) { reject(e); }
        });
    }
    var root = document.querySelector('.lebd-my-course');
    if (!root) return;
    root.addEventListener('click', function (event) {
        var btn = event.target.closest('button[data-copy], button.copy-reference-btn, button.share-reference-btn');
        if (!btn || !root.contains(btn)) return;
        if (btn.classList.contains('share-reference-btn')) {
            var title = btn.getAttribute('data-title') || '';
            var shareUrl = btn.getAttribute('data-url') || '';
            if (navigator.share) {
                navigator.share({ title: title, text: 'এই ব্যাচটি সম্পর্কে বিস্তারিত জানতে নিচের লিংকে ক্লিক করুন:', url: shareUrl })
                    .then(function () { showCopyNotice('✅ Link shared successfully!'); })
                    .catch(function (e) { if (e.name !== 'AbortError') showCopyNotice('❌ Share করা যায়নি'); });
            } else {
                copyText(shareUrl).then(function () { showCopyNotice('✅ Link copied successfully!'); })
                    .catch(function () { showCopyNotice('❌ Link Copy করা যায়নি'); });
            }
            return;
        }
        var text = btn.getAttribute('data-copy') || btn.getAttribute('data-url') || '';
        copyText(text).then(function () { showCopyNotice('✅ Copied successfully!'); })
            .catch(function () { showCopyNotice('❌ Copy করা যায়নি'); });
    });
})();
</script>
@endsection
