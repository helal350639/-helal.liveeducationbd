@extends('homepage')
@section('title', 'আমার কেনা E-Book / PDF')
@section('content')
<style>
.lebd-eb-owned{min-height:65vh;background:#f3f7fd;padding:22px 14px 55px;color:#123363}
.lebd-eb-owned *{box-sizing:border-box}
.lebd-eb-owned .wrap{max-width:1140px;margin:0 auto}
.lebd-eb-owned a{text-decoration:none}
.lebd-eb-owned .top{display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:15px}
.lebd-eb-owned .back{color:#0860be;font-size:13px;font-weight:750}
.lebd-eb-owned .shop{padding:10px 13px;border-radius:9px;background:#096fd6;color:#fff;font-weight:750;font-size:12px}
.lebd-eb-owned .hero{padding:20px;border-radius:15px;background:#dcebff;margin-bottom:17px}
.lebd-eb-owned .hero h1{margin:0;font-size:23px;font-weight:800;color:#123363}
.lebd-eb-owned .hero p{margin:4px 0 0;font-size:12px;color:#56739a}
.lebd-eb-owned .cards{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:13px}
.lebd-eb-owned .pkg{display:flex;flex-direction:column;min-width:0;background:#fff;border:1px solid #cfdef5;border-radius:12px;padding:17px;box-shadow:0 7px 17px #15345610;color:#123363;transition:border-color .15s,transform .15s}
.lebd-eb-owned .pkg:hover{border-color:#83b8f4;transform:translateY(-2px)}
.lebd-eb-owned .pkg h2{font-size:17px;line-height:1.4;margin:0 0 7px;font-weight:800;overflow-wrap:anywhere}
.lebd-eb-owned .active{display:inline-block;align-self:flex-start;background:#dcf9e7;color:#087a43;border-radius:20px;padding:4px 9px;font-size:11px;font-weight:800}
.lebd-eb-owned .meta{margin:10px 0;color:#385b86;font-size:12px}
.lebd-eb-owned .open{display:block;margin-top:auto;background:#0b72de;color:#fff;padding:11px 12px;text-align:center;border-radius:8px;font-weight:800;font-size:13px}
.lebd-eb-owned .group{background:#fff;border:1px solid #d4e2f6;border-radius:13px;padding:15px;margin-bottom:15px;box-shadow:0 6px 16px #1534560a}
.lebd-eb-owned .group.non{background:#f4f4ff}
.lebd-eb-owned .banner{margin:0 auto 13px;display:block;width:max-content;max-width:100%;padding:11px 24px;border-radius:24px;background:linear-gradient(100deg,#ff9100,#ff414d);color:#fff;font-weight:850;text-align:center;font-size:17px}
.lebd-eb-owned .group.non .banner{background:linear-gradient(100deg,#3988ff,#6041ce)}
.lebd-eb-owned .grid{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:9px}
.lebd-eb-owned .tile{min-width:0;min-height:110px;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:7px;padding:11px 7px;text-align:center;background:#eaf2ff;border:1px solid #d0e0f5;border-radius:10px;color:#103967}
.lebd-eb-owned .tile:hover{background:#d9e9ff;border-color:#8cb9ef}
.lebd-eb-owned .cover{width:39px;height:40px;border-radius:8px;background:#fff;display:flex;align-items:center;justify-content:center;color:#1375ff;font-size:22px;overflow:hidden}
.lebd-eb-owned .cover img{width:100%;height:100%;object-fit:cover}
.lebd-eb-owned .tile strong{font-size:12px;line-height:1.35;font-weight:800;overflow-wrap:anywhere}
.lebd-eb-owned .tile small{font-size:10px;color:#087a43}
.lebd-eb-owned .tile em{font-size:10px;color:#62799b;font-style:normal}
.lebd-eb-owned .empty{background:#fff;padding:22px;border:1px dashed #c6daf8;border-radius:12px;color:#61748c;text-align:center;font-size:13px}
.lebd-eb-owned .notice{margin:0 0 14px;padding:12px 14px;border:1px solid #9be2b8;border-radius:10px;background:#effcf4;color:#087443;font-size:13px;line-height:1.6}
@media(max-width:860px){.lebd-eb-owned .cards{grid-template-columns:repeat(2,minmax(0,1fr))}.lebd-eb-owned .grid{grid-template-columns:repeat(3,minmax(0,1fr))}}
@media(max-width:550px){.lebd-eb-owned{padding:15px 9px 40px}.lebd-eb-owned .cards{grid-template-columns:1fr}.lebd-eb-owned .grid{grid-template-columns:repeat(2,minmax(0,1fr));gap:7px}.lebd-eb-owned .hero{padding:16px}.lebd-eb-owned .hero h1{font-size:19px}.lebd-eb-owned .banner{font-size:14px}}
</style>
<main class="lebd-eb-owned"><div class="wrap">
    <div class="top">
        <a class="back" href="{{ $selectedPackage ? route('student.ebook.dashboard') : route('students.dashboard') }}">← {{ $selectedPackage ? 'আমার কেনা প্যাকেজ' : 'Student Dashboard' }}</a>
        @if(\Illuminate\Support\Facades\Route::has('student.ebook.all-packages'))
            <a class="shop" href="{{ route('student.ebook.all-packages') }}">＋ নতুন Premium প্যাকেজ দেখুন</a>
        @endif
    </div>

    @if(session('ebook_purchase_success'))
        @php $ebookPurchaseNotice = session('ebook_purchase_success'); @endphp
        <div class="notice" role="status">
            <strong>✓ প্যাকেজ সফলভাবে কেনা হয়েছে!</strong><br>
            {{ $ebookPurchaseNotice['package_name'] ?? 'আপনার E-Book প্যাকেজ' }} এখন আপনার লাইব্রেরিতে যুক্ত হয়েছে। নিচের প্যাকেজে ক্লিক করে পড়া শুরু করুন।
        </div>
    @endif

    @if($selectedPackage)
        <header class="hero">
            <h1>📚 {{ $selectedPackage->name }}</h1>
            <p>এই প্যাকেজে অন্তর্ভুক্ত E-Book / PDF বিভাগ নির্বাচন করুন।</p>
        </header>
        @foreach(['department'=>'E-Book / PDF (Diploma)','non-department'=>'E-Book / PDF (General)'] as $kind=>$heading)
            @php $groupCats = $categories->where('kind', $kind); @endphp
            @if($groupCats->isNotEmpty())
                <section class="group {{ $kind === 'non-department' ? 'non' : '' }}">
                    <h2 class="banner">{{ $heading }}</h2>
                    <div class="grid">
                        @foreach($groupCats as $cat)
                            <a class="tile" href="{{ route('student.ebook.chapters', ['kind'=>$cat->kind,'category'=>$cat->id]) }}">
                                <span class="cover">
                                    @if(!empty($cat->front_cover))
                                        <img src="{{ asset($cat->front_cover) }}" alt="" loading="lazy">
                                    @else
                                        <i class="fa {{ preg_match('/^fa-[a-z0-9-]+$/', (string)$cat->icon) ? $cat->icon : 'fa-book' }}" aria-hidden="true"></i>
                                    @endif
                                </span>
                                <strong>{{ $cat->name }}</strong>
                                <small>✓ Premium Active</small>
                                <em>মেয়াদ: {{ \Carbon\Carbon::parse($cat->access_expires_at)->format('d/m/Y') }}</em>
                            </a>
                        @endforeach
                    </div>
                </section>
            @endif
        @endforeach
        @if($categories->isEmpty())
            <div class="empty">এই প্যাকেজে বর্তমানে কোনো Active E-Book Category পাওয়া যায়নি। Admin-এর Package Mapping যাচাই করুন।</div>
        @endif
    @else
        <header class="hero">
            <h1>আমার কেনা E-Book / PDF</h1>
            <p>আপনার সক্রিয় Premium E-Book প্যাকেজ নির্বাচন করে পড়া শুরু করুন।</p>
        </header>
        @if($packages->isNotEmpty())
            <div class="cards">
                @foreach($packages as $pkg)
                    <article class="pkg">
                        <h2>{{ $pkg->name }}</h2>
                        <span class="active">
                            @if($pkg->has_paid_purchase)
                                প্যাকেজ কিনেছেন
                            @elseif($pkg->has_manual_access)
                                Admin থেকে দেওয়া
                            @else
                                Active Access
                            @endif
                        </span>
                        <div class="meta">
                            মেয়াদ: {{ \Carbon\Carbon::parse($pkg->ends_at)->format('d M Y, h:i A') }}<br>
                            অন্তর্ভুক্ত বিভাগ: {{ (int)$pkg->category_count }}টি
                        </div>
                        <a class="open" href="{{ route('student.ebook.dashboard', ['package'=>$pkg->package_id]) }}">প্যাকেজ দেখুন ▶</a>
                    </article>
                @endforeach
            </div>
        @elseif($legacyCategories->isNotEmpty())
            <div class="empty" style="margin-bottom:14px">আপনার কিছু পুরোনো Category-based Access রয়েছে। নিচে সেগুলো থেকে পড়তে পারবেন।</div>
            @foreach(['department'=>'E-Book / PDF (Diploma)','non-department'=>'E-Book / PDF (General)'] as $kind=>$heading)
                @php $groupCats = $legacyCategories->where('kind', $kind); @endphp
                @if($groupCats->isNotEmpty())
                    <section class="group {{ $kind === 'non-department' ? 'non' : '' }}">
                        <h2 class="banner">{{ $heading }}</h2>
                        <div class="grid">
                            @foreach($groupCats as $cat)
                                <a class="tile" href="{{ route('student.ebook.chapters', ['kind'=>$cat->kind,'category'=>$cat->id]) }}">
                                    <span class="cover"><i class="fa {{ preg_match('/^fa-[a-z0-9-]+$/', (string)$cat->icon) ? $cat->icon : 'fa-book' }}" aria-hidden="true"></i></span>
                                    <strong>{{ $cat->name }}</strong><small>✓ Premium Active</small>
                                    <em>মেয়াদ: {{ \Carbon\Carbon::parse($cat->access_expires_at)->format('d/m/Y') }}</em>
                                </a>
                            @endforeach
                        </div>
                    </section>
                @endif
            @endforeach
        @else
            <div class="empty">আপনার বর্তমানে কোনো সক্রিয় E-Book প্যাকেজ নেই। নতুন Premium প্যাকেজ নির্বাচন করুন।</div>
        @endif
    @endif
</div></main>
@endsection
