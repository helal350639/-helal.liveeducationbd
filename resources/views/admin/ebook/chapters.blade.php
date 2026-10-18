@extends('dashboard')
@section('title', 'E-Book সূচিপত্র')
@section('content')
<style>
.lebd-toc-shell{max-width:1200px;margin:12px auto 36px;padding:0 14px;font-family:inherit;color:#132b46}
.lebd-toc-actions{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:12px;align-items:center}
.lebd-toc-btn{display:inline-flex;align-items:center;justify-content:center;gap:5px;background:#0877bc;color:white!important;border:0;border-radius:6px;padding:9px 13px;text-decoration:none!important;font-weight:600;cursor:pointer;font-size:13px}
.lebd-toc-btn:hover{background:#075f99}.lebd-toc-btn.alt{background:#f3f7fb;color:#16628c!important;border:1px solid #cddde9}.lebd-toc-btn.edit{background:#eb9a08}.lebd-toc-btn.delete{background:#d63c34}
.lebd-toc-formpanel{background:#fff;border:1px solid #dbe5ef;border-radius:12px;margin-bottom:18px;padding:18px 20px}
.lebd-toc-formrow{display:flex;flex-wrap:wrap;gap:9px;align-items:end}
.lebd-toc-formrow label{display:flex;flex-direction:column;gap:5px;font-weight:600;font-size:13px}
.lebd-toc-formrow input,.lebd-toc-formrow select,.lebd-toc-inline input,.lebd-toc-inline select{padding:9px 10px;border:1px solid #cad8e5;border-radius:6px;background:#fff;color:#142b45;max-width:100%}
.lebd-toc-book{background:white;border:1px solid #dce4ee;box-shadow:0 4px 26px rgba(16,45,80,.07);padding:45px clamp(16px,5vw,75px) 65px;min-height:520px}
.lebd-toc-publisher{text-align:center;font-size:21px;font-weight:750;color:#050505}
.lebd-toc-title{text-align:center;color:#000;font-size:29px;font-weight:800;margin:16px 0 70px;line-height:1.4}
.lebd-toc-heading{text-align:center;color:#000;font-size:28px;font-weight:750;margin:0 0 24px}
.lebd-toc-entry{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:8px;align-items:center;margin:0 0 11px}
.lebd-toc-main{display:flex;align-items:baseline;gap:12px;min-width:0;color:#000;font-size:17px}
.lebd-toc-prefix{width:112px;flex-shrink:0}.lebd-toc-chname{min-width:0;white-space:normal;overflow-wrap:anywhere}.lebd-toc-dots{border-bottom:2px dotted #77818c;flex:1;min-width:18px;transform:translateY(-4px)}.lebd-toc-page{min-width:30px;text-align:right;font-weight:700}
.lebd-toc-rowactions{display:flex;gap:5px;align-items:center;flex-wrap:wrap;justify-content:flex-end}
.lebd-toc-rowactions .lebd-toc-btn{padding:7px 9px;font-size:12px;white-space:nowrap}
.lebd-toc-editrow{border:1px solid #e2e8f0;background:#f8fbfd;border-radius:8px;padding:13px;margin:-2px 0 12px}
.lebd-toc-inline{display:flex;flex-wrap:wrap;gap:8px;align-items:end}.lebd-toc-inline label{display:flex;flex-direction:column;gap:4px;font-size:12px;font-weight:700}
.lebd-toc-note{font-size:12px;color:#6b7280;margin:8px 0 18px}.lebd-toc-flash{padding:10px 14px;background:#eaf8ef;border:1px solid #b8e5c5;color:#126434;border-radius:7px;margin-bottom:12px}.lebd-toc-flash.err{background:#fff1f1;border-color:#f5bbbb;color:#a31313}
.lebd-toc-add{background:#fff;border:1px solid #d6e1ed;border-radius:10px;padding:16px;margin:0 0 18px}
@media(max-width:900px){.lebd-toc-entry{grid-template-columns:1fr}.lebd-toc-rowactions{justify-content:flex-start;padding:0 0 8px;border-bottom:1px solid #edf1f6}.lebd-toc-title{margin-bottom:46px}.lebd-toc-main{font-size:15px;gap:6px}.lebd-toc-prefix{width:88px}.lebd-toc-book{padding:24px 14px}}
@media print{.lebd-toc-actions,.lebd-toc-add,.lebd-toc-rowactions,.lebd-toc-editrow,.lebd-toc-note{display:none!important}.lebd-toc-book{box-shadow:none;border:0;padding:0}.lebd-toc-entry{grid-template-columns:1fr}.lebd-toc-title{margin-bottom:80px}}
/* Offscreen rather than display:none: browser must measure true A4 sheet heights. */
.lebd-page-measure-shell{position:absolute!important;left:-20000px!important;top:0!important;width:220mm!important;visibility:hidden!important;pointer-events:none!important;overflow:visible!important}
.lebd-page-measure-shell .lebd-a4-reader{width:210mm!important}
@media print{.lebd-page-measure-shell{display:none!important}}
</style>
<div class="lebd-toc-shell">
 <div class="lebd-toc-actions">
  <a class="lebd-toc-btn alt" href="{{ route('admin.ebook.categories', ['kind'=>$kind]) }}">← {{ $kind==='department' ? 'Department' : 'Non-Department' }} Categories</a>
  <a class="lebd-toc-btn" href="{{route('admin.ebook.full-book',['kind'=>$kind,'category'=>$cat->id])}}"><i class="fa fa-book"></i> সম্পূর্ণ E-Book View (A4)</a>
  <button class="lebd-toc-btn" type="button" onclick="document.getElementById('lebd-toc-add').hidden=!document.getElementById('lebd-toc-add').hidden">+ Add Chapter</button>
 </div>
 @if(session('success'))<div class="lebd-toc-flash">{{session('success')}}</div>@endif
 @if($errors->any())<div class="lebd-toc-flash err">{{$errors->first()}}</div>@endif
 <div id="lebd-toc-add" class="lebd-toc-add" hidden>
  <h3 style="margin:0 0 12px">নতুন অধ্যায় যোগ করুন</h3>
  <form action="{{route('admin.ebook.chapters.store',['kind'=>$kind,'category'=>$cat->id])}}" method="POST">@csrf
   <div class="lebd-toc-formrow">
    <label style="flex:2;min-width:200px">অধ্যায়ের নাম<input name="title" placeholder="সমাস" maxlength="255" required></label>
    
    <label>Sort Order<input name="sort_order" type="number" min="0" value="{{ ((int)$chapters->max('sort_order'))+1 }}" style="width:100px"></label>
    <label>Status<select name="status"><option value="Active">Active</option><option value="Inactive">Inactive</option></select></label>
    <button class="lebd-toc-btn" type="submit">Save Chapter</button>
   </div>
  </form>
 </div>
 <div class="lebd-toc-book">
  <div class="lebd-toc-publisher">Live Education BD</div>
  <h1 class="lebd-toc-title">{{ $cat->name }}</h1>
  <h2 class="lebd-toc-heading">সূচিপত্র</h2>
  @forelse($chapters as $chapter)
   @php
     $chapterIndex = $loop->iteration;
     $bnDigits = ['০','১','২','৩','৪','৫','৬','৭','৮','৯'];
     $bn = function ($value) use ($bnDigits) { return strtr((string)$value, array_combine(range(0,9),$bnDigits)); };
     // A4 page range is measured from the actual chapter layout, not stored manually.
   @endphp
   <div class="lebd-toc-entry">
    <div class="lebd-toc-main">
     <span class="lebd-toc-prefix">অধ্যায়-{{ $bn(str_pad($chapterIndex,2,'0',STR_PAD_LEFT)) }}:</span>
     <span class="lebd-toc-chname">{{ preg_replace('/^অধ্যায়\s*[-–:]?\s*[০-৯0-9]+\s*[:ঃ\-–]?\s*/u', '', $chapter->title) ?: $chapter->title }}</span>
     <span class="lebd-toc-dots" aria-hidden="true"></span>
     <span class="lebd-toc-page"><span data-toc-range="{{ $chapter->id }}">গণনা হচ্ছে…</span></span>
    </div>
    <div class="lebd-toc-rowactions">
      <a class="lebd-toc-btn" href="{{ route('admin.ebook.editor',['kind'=>$kind,'chapter'=>$chapter->id]) }}"><i class="fa fa-plus"></i> Add Question</a>
      <button class="lebd-toc-btn edit" type="button" onclick="var el=document.getElementById('toc-edit-{{$chapter->id}}');el.hidden=!el.hidden"><i class="fa fa-edit"></i> Edit</button>
      <form action="{{ route('admin.ebook.chapters.delete',['kind'=>$kind,'category'=>$cat->id,'chapter'=>$chapter->id]) }}" method="POST" style="display:inline" onsubmit="return confirm('এই অধ্যায় এবং এর সকল Content মুছে ফেলবেন?')">@csrf @method('DELETE')<button class="lebd-toc-btn delete" type="submit"><i class="fa fa-trash"></i> Delete</button></form>
    </div>
   </div>
   <div id="toc-edit-{{$chapter->id}}" class="lebd-toc-editrow" hidden>
    <form action="{{ route('admin.ebook.chapters.update',['kind'=>$kind,'category'=>$cat->id,'chapter'=>$chapter->id]) }}" method="POST">@csrf @method('PUT')
     <div class="lebd-toc-inline">
       <label style="flex:2;min-width:185px">অধ্যায়ের নাম<input name="title" maxlength="255" value="{{$chapter->title}}" required></label>
       
       <label>Sort Order<input type="number" name="sort_order" min="0" value="{{$chapter->sort_order}}" style="width:95px"></label>
       <label>Status<select name="status"><option value="Active" @selected($chapter->status==='Active')>Active</option><option value="Inactive" @selected($chapter->status==='Inactive')>Inactive</option></select></label>
       <button class="lebd-toc-btn" type="submit">Update</button>
     </div>
    </form>
   </div>
  @empty
   <p style="text-align:center;padding:30px 0;color:#64748b">সূচিপত্রে এখনো কোনো অধ্যায় যোগ করা হয়নি। উপরের Add Chapter থেকে শুরু করুন।</p>
  @endforelse
  <p class="lebd-toc-note">A4 Layout অনুযায়ী পৃষ্ঠা-রেঞ্জ স্বয়ংক্রিয়ভাবে গণনা হয়। প্রশ্ন বা লেখা যোগ/বিয়োগ হলে পরের অধ্যায়গুলোর পৃষ্ঠা নম্বরও বদলে যাবে। Sort Order অনুসারে অধ্যায় দেখানো হয়।</p>
 </div>
 <div class="lebd-page-measure-shell" aria-hidden="true">
   @foreach($bookChapters as $entry)
     @php $ch=$entry['ch'];$blocks=$entry['blocks'];$qs=$entry['qs'];$opts=$entry['opts']; @endphp
     <div class="lebd-book-chapter-section" data-chapter-id="{{ $ch->id }}">
       @include('ebook.partials.blocks', ['isEditor'=>false])
     </div>
   @endforeach
 </div>
</div>
@endsection
