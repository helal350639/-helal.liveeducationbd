@extends('homepage')
@section('title','আমার কেনা E-Book / PDF')
@section('content')
@php
    $lebdStudent = \Illuminate\Support\Facades\Auth::guard('student')->user();
    $lebdPurchasedBooks = collect();
    if ($lebdStudent) {
        $lebdPurchasedBooks = \Illuminate\Support\Facades\DB::table('lebd_ebook_entitlements as e')
            ->join('lebd_ebook_categories as c', 'c.id', '=', 'e.category_id')
            ->where('e.user_id', $lebdStudent->id)
            ->where('e.status', 'Active')
            ->where('e.expires_at', '>', now())
            ->where('c.status', 'Active')
            ->whereIn('c.kind', ['department','non-department'])
            ->select('c.id','c.kind','c.name','c.icon','c.front_cover',
                     \Illuminate\Support\Facades\DB::raw('MAX(e.expires_at) as access_expires_at'))
            ->groupBy('c.id','c.kind','c.name','c.icon','c.front_cover')
            ->orderBy('c.name')
            ->get();
    }
    $lebdEbookGroups = [
        ['kind'=>'department','title'=>'E-Book / PDF (Diploma)','description'=>'আপনার কেনা ডিপ্লোমা ও ডিপার্টমেন্টভিত্তিক বই','class'=>'diploma'],
        ['kind'=>'non-department','title'=>'E-Book / PDF (General)','description'=>'আপনার কেনা জেনারেল ই-বুক','class'=>'general'],
    ];
@endphp
<style>
/* Scoped styles, no unbounded 100vw panels; keeps homepage layout/footer intact. */
.lebd-eb63{box-sizing:border-box!important;width:100%!important;max-width:1140px!important;min-width:0!important;margin:20px auto 0!important;padding:0 14px 55px!important;min-height:calc(100vh - 310px);color:#0b2d63;display:block;float:none!important}
.lebd-eb63 *{box-sizing:border-box}
.lebd-eb63 .back{display:inline-block;color:#1467da;text-decoration:none;font-size:13px;font-weight:700;margin-bottom:13px}
.lebd-eb63 .intro h1{font-size:22px;font-weight:800;margin:0 0 4px}
.lebd-eb63 .intro p{font-size:12px;color:#6a819f;margin:0 0 16px}
.lebd-eb63 .group{border:1px solid #d0dffa;border-radius:11px;overflow:hidden;background:#fff;box-shadow:0 4px 14px #092f6710;margin:0 0 14px}
.lebd-eb63 .head{display:flex;justify-content:space-between;align-items:center;gap:8px;padding:11px 13px;background:linear-gradient(100deg,#19469a,#3271e3);color:#fff}
.lebd-eb63 .general .head{background:linear-gradient(100deg,#55359d,#7949d3)}
.lebd-eb63 .head strong{display:block;font-size:15px;font-weight:800;line-height:1.3}
.lebd-eb63 .head small{font-size:10px;opacity:.9;display:block}
.lebd-eb63 .count{font-size:11px;border:1px solid #ffffff70;border-radius:30px;padding:4px 10px;white-space:nowrap}
.lebd-eb63 .grid{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:9px;padding:11px}
.lebd-eb63 .tile{min-width:0;min-height:108px;text-decoration:none!important;color:#0b2d63;background:#edf4ff;border:1px solid #d7e5fb;border-radius:9px;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;gap:6px;padding:11px 7px;transition:transform .15s,border-color .15s}
.lebd-eb63 .tile:hover{transform:translateY(-2px);border-color:#89baf8}
.lebd-eb63 .cover{width:37px;height:39px;border-radius:8px;background:#fff;display:flex;align-items:center;justify-content:center;color:#126eec;font-size:18px;overflow:hidden}
.lebd-eb63 .cover img{width:100%;height:100%;object-fit:cover}
.lebd-eb63 .tile strong{font-size:11px;line-height:1.3;font-weight:800;overflow-wrap:anywhere}
.lebd-eb63 .tile small{font-size:10px;color:#168653}
.lebd-eb63 .tile em{font-style:normal;font-size:9px;color:#62799b}
.lebd-eb63 .empty{margin:11px;padding:22px 12px;text-align:center;color:#667e9c;background:#f6f9ff;border:1px dashed #c6daf8;border-radius:9px;font-size:12px}
@media(max-width:700px){.lebd-eb63{padding:0 10px 40px!important;min-height:calc(100vh - 220px)}.lebd-eb63 .grid{grid-template-columns:repeat(3,minmax(0,1fr));gap:7px;padding:8px}.lebd-eb63 .head strong{font-size:12px}.lebd-eb63 .intro h1{font-size:19px}}
@media(max-width:365px){.lebd-eb63 .grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
</style>
<main class="lebd-eb63">
<a class="back" href="{{route('students.dashboard')}}">← Student Dashboard-এ ফিরুন</a>
<div class="intro">
  <h1>আমার কেনা E-Book / PDF</h1>
  <p>শুধু আপনার সক্রিয় ও মেয়াদ থাকা Premium E-Book এখানে দেখাবে।</p>
</div>
@foreach($lebdEbookGroups as $lebdGroup)
    @php $lebdItems=$lebdPurchasedBooks->where('kind',$lebdGroup['kind']); @endphp
    <section class="group {{ $lebdGroup['class'] }}">
        <header class="head">
            <div>
                <strong><i class="fa fa-book-open" aria-hidden="true"></i> {{$lebdGroup['title']}}</strong>
                <small>{{$lebdGroup['description']}}</small>
            </div>
            <span class="count">{{$lebdItems->count()}}টি কেনা বই</span>
        </header>
        @if($lebdItems->isEmpty())
            <div class="empty">এই বিভাগে আপনার কোনো সক্রিয় কেনা E-Book নেই।</div>
        @else
            <div class="grid">
            @foreach($lebdItems as $lebdBook)
                <a class="tile" href="{{route('student.ebook.chapters',['kind'=>$lebdBook->kind,'category'=>$lebdBook->id])}}">
                    <span class="cover">
                        @if(!empty($lebdBook->front_cover))
                            <img src="{{asset($lebdBook->front_cover)}}" alt="" loading="lazy">
                        @else
                            <i class="fa {{preg_match('/^fa-[a-z0-9-]+$/',(string)$lebdBook->icon)?$lebdBook->icon:'fa-book'}}" aria-hidden="true"></i>
                        @endif
                    </span>
                    <strong>{{$lebdBook->name}}</strong>
                    <small>✓ Premium Active</small>
                    <em>মেয়াদ: {{\Carbon\Carbon::parse($lebdBook->access_expires_at)->format('d/m/Y')}}</em>
                </a>
            @endforeach
            </div>
        @endif
    </section>
@endforeach
</main>
@endsection
