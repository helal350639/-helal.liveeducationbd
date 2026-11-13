@extends('homepage')
@section('title', 'Premium E-Book Packages | '.$cat->name)
@section('content')
<link rel="stylesheet" href="{{ asset('web/css/lebd-ebook-student-v22.css') }}">
<link rel="stylesheet" href="{{ asset('web/css/lebd-ebook-premium-v25.css') }}">

@php
    $lebdStudentId = (int) \Illuminate\Support\Facades\Auth::guard('student')->id();
    $lebdEntitlement = $lebdStudentId
        ? \Illuminate\Support\Facades\DB::table('lebd_ebook_entitlements')
            ->where('user_id', $lebdStudentId)
            ->where('category_id', $cat->id)
            ->where('status', 'Active')
            ->where('expires_at', '>', now())
            ->orderByDesc('expires_at')
            ->first()
        : null;
    $durationLabels = ['days' => 'দিন', 'months' => 'মাস', 'years' => 'বছর'];
@endphp

<style>
/* E-Book catalog refresh — presentation only; checkout forms and routes are unchanged. */
.ebpc-page{max-width:980px;width:calc(100% - 28px);margin:22px auto 64px;color:#102f62}
.ebpc-topbar{min-height:58px;display:flex;align-items:center;justify-content:space-between;gap:12px;padding:10px 16px;margin-bottom:18px;background:#fff;border:1px solid #dce7f7;border-radius:14px;box-shadow:0 4px 18px rgba(20,55,100,.06)}
.ebpc-topbar a{color:#164ca5;text-decoration:none;font-size:12px;font-weight:700}
.ebpc-brand{font-size:13px;font-weight:800;color:#315d99;text-align:center}
.ebpc-heading{display:flex;align-items:center;gap:13px;margin:12px 0 16px}
.ebpc-heading-icon{width:45px;height:45px;display:grid;place-items:center;border-radius:13px;background:#fff1d4;font-size:25px}
.ebpc-heading h1{font-size:25px;font-weight:850;line-height:1.25;margin:0;color:#102d5b}
.ebpc-heading p{margin:4px 0 0;color:#687f9d;font-size:11px}
.ebpc-filters{display:flex;gap:7px;flex-wrap:wrap;margin:14px 0 13px}
.ebpc-filter{border:1px solid #d5e1f3;background:#fff;color:#234b83;border-radius:22px;padding:8px 13px;font-size:11px;font-weight:750;cursor:pointer}
.ebpc-filter.is-active{background:#2524c9;border-color:#2524c9;color:#fff;box-shadow:0 3px 9px rgba(37,36,201,.16)}
.ebpc-alert{padding:13px 15px;margin:12px 0;border-radius:10px;background:#fff0f0;color:#a52323;font-size:13px}
.ebpc-owned{padding:15px 17px;margin:12px 0 16px;border:1px solid #b5e7c8;border-radius:12px;background:#edfbf2;color:#08643b;font-size:13px;line-height:1.6}
.ebpc-owned a{display:inline-block;margin-top:7px;padding:8px 12px;border-radius:8px;background:#087b4b;color:#fff;text-decoration:none;font-weight:750}
.ebpc-list{display:flex;flex-direction:column;gap:10px}
.ebpc-card{display:grid;grid-template-columns:minmax(0,1fr) 142px;gap:14px;align-items:stretch;padding:13px;background:#fff;border:1px solid #dce7f5;border-radius:16px;box-shadow:0 5px 17px rgba(20,55,100,.055);transition:border-color .15s,box-shadow .15s}
.ebpc-card:hover{border-color:#b8d1f4;box-shadow:0 7px 20px rgba(20,55,100,.09)}
.ebpc-card[hidden]{display:none!important}
.ebpc-main{min-width:0;display:flex;gap:11px}
.ebpc-icon{width:37px;height:37px;flex:0 0 37px;display:grid;place-items:center;border-radius:11px;background:#edf3ff;font-size:20px}
.ebpc-info{min-width:0;flex:1}
.ebpc-name{font-size:17px;font-weight:850;color:#112f5d;margin:0 0 4px;line-height:1.3}
.ebpc-duration{display:inline-block;background:#f0f3fb;color:#617596;padding:3px 8px;border-radius:20px;font-size:10px;margin-bottom:7px}
.ebpc-tags{display:flex;gap:5px;flex-wrap:wrap;margin:1px 0 6px}
.ebpc-tag{display:inline-block;background:#edf4ff;color:#315f9a;padding:4px 7px;border-radius:5px;font-size:9px;line-height:1.2}
.ebpc-tag.is-purple{background:#f1eaff;color:#6743a6}
.ebpc-description{font-size:11px;color:#5d7392;line-height:1.5;margin:3px 0 6px}
.ebpc-features{display:flex;gap:6px;flex-wrap:wrap;margin-top:7px}
.ebpc-feature{display:inline-flex;align-items:center;gap:4px;padding:4px 7px;border-radius:6px;background:#f0f4fb;color:#3d5e89;font-size:9px}
.ebpc-more{margin-top:5px}
.ebpc-more summary{display:inline-block;color:#1645d4;text-decoration:underline;font-size:10px;font-weight:750;cursor:pointer;list-style:none}
.ebpc-more summary::-webkit-details-marker{display:none}
.ebpc-more p{font-size:11px;color:#5f7594;line-height:1.6;margin:6px 0 0;max-width:680px}
.ebpc-side{border-left:1px solid #e0e9f6;padding:5px 0 5px 12px;display:flex;flex-direction:column;justify-content:center;align-items:stretch;gap:5px;min-width:0}
.ebpc-price{font-size:23px;font-weight:900;letter-spacing:-.4px;color:#102f62;line-height:1.1;white-space:nowrap}
.ebpc-old-price{font-size:10px;color:#9aa8ba;text-decoration:line-through;margin-left:4px}
.ebpc-saving{align-self:flex-start;padding:3px 6px;border-radius:5px;background:#e6f8ed;color:#087a45;font-size:9px;font-weight:800}
.ebpc-buy{display:block;width:100%;padding:8px 7px;border:1px solid #2e35ee;border-radius:9px;background:#f3f5ff;color:#1726bd;text-align:center;text-decoration:none;font-size:10px;font-weight:800;line-height:1.35;cursor:pointer}
.ebpc-buy:hover{background:#2524c9;color:#fff}
.ebpc-buy-form{margin:0}
.ebpc-buy-form button{font-family:inherit}
.ebpc-owned-label{font-size:10px;color:#087343;font-weight:800}
.ebpc-empty{padding:30px 18px;background:#fff;border:1px dashed #cbd8e9;border-radius:13px;text-align:center;color:#657b98;font-size:13px}
.ebpc-footer-note{margin-top:13px;padding:11px 13px;border-radius:9px;background:#fff8e8;color:#9a7440;font-size:10px;line-height:1.5}
@media(max-width:620px){.ebpc-page{width:calc(100% - 18px);margin-top:10px}.ebpc-topbar{padding:9px 11px}.ebpc-heading h1{font-size:21px}.ebpc-card{grid-template-columns:minmax(0,1fr) 112px;gap:8px;padding:10px;border-radius:13px}.ebpc-main{gap:8px}.ebpc-icon{width:32px;height:32px;flex-basis:32px;font-size:17px}.ebpc-name{font-size:14px}.ebpc-side{padding-left:8px}.ebpc-price{font-size:19px}.ebpc-buy{font-size:9px;padding:8px 4px}.ebpc-tag{font-size:8px;padding:4px 5px}}
@media(max-width:390px){.ebpc-card{grid-template-columns:minmax(0,1fr) 99px}.ebpc-price{font-size:17px}}
</style>

<main class="ebpc-page" id="ebpc-package-catalog">
@include('ebook.partials.all-premium-packages-button')

    <nav class="ebpc-topbar" aria-label="Page navigation">
        <a href="{{ route('student.ebook.chapters', ['kind' => $kind, 'category' => $cat->id]) }}">← সূচিপত্রে ফিরুন</a>
        <span class="ebpc-brand">LIVE Education BD</span>
        <a href="{{ url('/') }}">Home</a>
    </nav>

    @if(session('error'))
        <div class="ebpc-alert" role="alert">{{ session('error') }}</div>
    @endif

    <header class="ebpc-heading">
        <span class="ebpc-heading-icon" aria-hidden="true">👑</span>
        <div>
            <h1>প্রিমিয়াম E-Book প্যাকেজ</h1>
            <p>Live Education BD-এর নির্বাচিত E-Book Reading Package | {{ $cat->name }}</p>
        </div>
    </header>

    @if($lebdEntitlement)
        <section class="ebpc-owned" aria-label="Active premium access">
            ✓ আপনার এই বইয়ের Premium Access সক্রিয় আছে।
            <br><small>মেয়াদ: {{ \Carbon\Carbon::parse($lebdEntitlement->expires_at)->format('d/m/Y') }}</small>
            <br><a href="{{ route('student.ebook.full-book', ['kind' => $kind, 'category' => $cat->id]) }}">📖 সম্পূর্ণ বই পড়ুন →</a>
        </section>
    @endif

    <nav class="ebpc-filters" aria-label="Package filters">
        <button type="button" class="ebpc-filter is-active" data-filter="all" aria-pressed="true">সব প্যাকেজ</button>
        <button type="button" class="ebpc-filter" data-filter="department" aria-pressed="false">Department</button>
        <button type="button" class="ebpc-filter" data-filter="non-department" aria-pressed="false">Non-Department</button>
        <button type="button" class="ebpc-filter" data-filter="combined" aria-pressed="false">Combined</button>
    </nav>

    <section class="ebpc-list" aria-label="Available E-Book packages">
        @forelse($packages as $p)
            @php
                $originalPrice = (float) ($p->main_price ?? $p->price);
                $payablePrice = (float) $p->price;
                $discountAmount = max(0, (float) ($p->discount_amount ?? ($originalPrice - $payablePrice)));
                $savingPercent = $originalPrice > 0 ? min(100, ($discountAmount / $originalPrice) * 100) : 0;
                $offerIsActive = empty($p->offer_ends_at) || \Carbon\Carbon::parse($p->offer_ends_at)->endOfDay()->isFuture();
                $filterGroup = $p->catalog_filter_group ?? $kind;
            @endphp
            <article class="ebpc-card" data-package-group="{{ $filterGroup }}">
                <div class="ebpc-main">
                    <span class="ebpc-icon" aria-hidden="true">📚</span>
                    <div class="ebpc-info">
                        <h2 class="ebpc-name">{{ $p->name }}</h2>
                        <span class="ebpc-duration">মেয়াদ: {{ (int) $p->duration_value }} {{ $durationLabels[$p->duration_unit] ?? 'দিন' }}</span>

                        <div class="ebpc-tags">
                            @forelse(($p->catalog_category_labels ?? collect()) as $label)
                                <span class="ebpc-tag">{{ $label }}</span>
                            @empty
                                <span class="ebpc-tag">{{ $kind === 'department' ? 'Department' : 'Non-Department' }}</span>
                            @endforelse
                            @if($filterGroup === 'combined')
                                <span class="ebpc-tag is-purple">Combined</span>
                            @endif
                        </div>

                        @if($p->description)
                            <p class="ebpc-description">{{ \Illuminate\Support\Str::limit($p->description, 145) }}</p>
                        @else
                            <p class="ebpc-description">নির্বাচিত E-Book-এর Premium Reading সুবিধা উপভোগ করুন।</p>
                        @endif

                        <div class="ebpc-features">
                            <span class="ebpc-feature">📖 Premium Reading</span>
                            <span class="ebpc-feature">📚 Full Book Access</span>
                        </div>

                        @if($p->description)
                            <details class="ebpc-more">
                                <summary>See More ›</summary>
                                <p>{{ $p->description }}</p>
                            </details>
                        @endif
                    </div>
                </div>

                <div class="ebpc-side">
                    <div>
                        <span class="ebpc-price">৳{{ number_format($payablePrice, 0) }}</span>
                        @if($discountAmount > 0 && $offerIsActive)
                            <span class="ebpc-old-price">৳{{ number_format($originalPrice, 0) }}</span>
                        @endif
                    </div>

                    @if($discountAmount > 0 && $offerIsActive)
                        <span class="ebpc-saving">Save {{ number_format($savingPercent, 2) }}%</span>
                    @endif

                    <div>
                        @if($p->has_active_access)
                            <span class="ebpc-owned-label">✓ আপনি প্যাকেজ কিনেছেন</span>
                            <a class="ebpc-buy" href="{{ route('student.ebook.full-book', ['kind' => $kind, 'category' => $cat->id]) }}">বই পড়ুন →</a>
                        @else
                            @auth('student')
                                {{-- Existing payment-start POST route and CSRF flow are intentionally preserved. --}}
                                <form action="{{ route('lebd.ebook.payment.start', ['kind' => $kind, 'category' => $cat->id, 'package' => $p->id]) }}" method="POST" class="ebpc-buy-form">
                                    @csrf
                                    <button type="submit" class="ebpc-buy">এই প্যাকেজটি কিনুন</button>
                                </form>
                            @else
                                <a class="ebpc-buy" href="{{ url('/students/login') }}">কিনতে Login করুন →</a>
                            @endauth
                        @endif
                    </div>
                </div>
            </article>
        @empty
            <div class="ebpc-empty">এই বইয়ের জন্য এখনো কোনো Premium Package প্রকাশ করা হয়নি।</div>
        @endforelse
    </section>

    <p class="ebpc-footer-note">Payment Gateway এবং Subscription Activation সম্পন্ন না হওয়া পর্যন্ত Premium E-Book Access চালু হবে না।</p>
</main>

<script>
(function () {
    'use strict';
    var root = document.getElementById('ebpc-package-catalog');
    if (!root) return;
    var filters = root.querySelectorAll('.ebpc-filter');
    var cards = root.querySelectorAll('.ebpc-card');
    filters.forEach(function (button) {
        button.addEventListener('click', function () {
            var selected = button.getAttribute('data-filter');
            filters.forEach(function (item) {
                var active = item === button;
                item.classList.toggle('is-active', active);
                item.setAttribute('aria-pressed', active ? 'true' : 'false');
            });
            cards.forEach(function (card) {
                var group = card.getAttribute('data-package-group');
                card.hidden = !(selected === 'all' || group === selected);
            });
        });
    });
})();
</script>
<link rel="stylesheet" href="{{ asset('web/css/lebd-ebook-pay-button-v34.css') }}">
@endsection
