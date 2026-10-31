@extends($preview ? 'dashboard' : 'homepage')
@section('title',$cat->name.' — Complete E-Book')
@section('content')
<link rel="stylesheet" href="{{ asset('web/css/lebd-ebook.css') }}">
<link rel="stylesheet" href="{{ asset('web/css/lebd-ebook-full-book-v16.css') }}">
<link rel="stylesheet" href="{{ asset('web/css/lebd-ebook-cover-v19.css') }}">
<style>
/* STEP-64: Mobile full-width A4 viewer. Preserve document pagination and print. */
.lebd-complete-book.lebd-mobile-a4-reader {max-width:100%;box-sizing:border-box}
.lebd-mobile-a4-reader .lebd-mobile-a4-actions{display:none}
@media (max-width:767px){
  .lebd-complete-book.lebd-mobile-a4-reader {width:100%!important;max-width:100%!important;margin:0!important;padding:0 0 24px!important;overflow:visible!important}
  .lebd-mobile-a4-reader .lebd-complete-toolbar{position:relative!important;width:100%!important;max-width:100%!important;display:block!important;padding:12px!important;margin:0!important;box-sizing:border-box}
  .lebd-mobile-a4-reader .lebd-complete-toolbar h2{font-size:16px!important;line-height:1.4!important}
  .lebd-mobile-a4-reader .lebd-complete-toolbar p{font-size:11px!important}
  .lebd-mobile-a4-reader .lebd-complete-buttons{display:flex!important;gap:7px!important;flex-wrap:wrap!important}
  .lebd-mobile-a4-reader .lebd-complete-buttons>a,.lebd-mobile-a4-reader .lebd-complete-buttons>button{min-height:38px!important;padding:9px 10px!important;font-size:12px!important;white-space:normal!important}
  .lebd-mobile-a4-reader .lebd-mobile-a4-actions{display:block;background:#edf5ff;color:#174271;font-size:12px;line-height:1.5;padding:10px 12px;margin:6px 8px 10px;border-radius:8px}
  .lebd-mobile-a4-reader .lebd-a4-scroll{display:block!important;width:100%!important;max-width:100%!important;overflow-x:auto!important;overflow-y:visible!important;-webkit-overflow-scrolling:touch!important;overscroll-behavior-x:contain;padding:0 0 12px!important}
  .lebd-mobile-a4-reader .lebd-a4-scroll>.lebd-a4-scroll-inner{min-width:794px!important;width:max-content!important;margin:0 auto!important;box-sizing:border-box}
  .lebd-mobile-a4-reader .lebd-a4-scroll-inner>.lebd-cover-a4-page,
  .lebd-mobile-a4-reader .lebd-a4-scroll-inner>.lebd-book-toc-preview,
  .lebd-mobile-a4-reader .lebd-a4-scroll-inner>.lebd-book-chapter-section{max-width:none!important;flex-shrink:0!important}
}
@media print{
 .lebd-mobile-a4-reader .lebd-mobile-a4-actions{display:none!important}
 .lebd-mobile-a4-reader .lebd-a4-scroll{overflow:visible!important;width:auto!important;max-width:none!important}
 .lebd-mobile-a4-reader .lebd-a4-scroll>.lebd-a4-scroll-inner{width:auto!important;min-width:0!important}
}
</style>
<div class="lebd-complete-book lebd-mobile-a4-reader">
 <div class="lebd-complete-toolbar">
  <div><h2>{{ $cat->name }} — সম্পূর্ণ বই</h2><p>সূচিপত্র অনুসারে অধ্যায়; নতুন অধ্যায় সর্বদা নতুন A4 পৃষ্ঠা থেকে শুরু</p></div>
  <div class="lebd-complete-buttons">
   @if($preview)
     <a href="{{route('admin.ebook.chapters',['kind'=>$kind,'category'=>$cat->id])}}">← সূচিপত্র</a>
   @else
     <a href="{{route('student.ebook.chapters',['kind'=>$kind,'category'=>$cat->id])}}">← সূচিপত্র</a>
   @endif
   <button type="button" onclick="window.print()">Download / Save PDF</button>
  </div>
 </div>
 <div class="lebd-mobile-a4-actions">📖 A4 বইটি সম্পূর্ণ আকারে রাখা হয়েছে। বামে–ডানে সরিয়ে পড়ুন, প্রয়োজন হলে মোবাইলের Zoom ব্যবহার করুন। PDF রাখতে <b>Download / Save PDF</b> চাপুন।</div>
 <div class="lebd-a4-scroll" role="region" aria-label="Scrollable full-size E-Book pages" tabindex="0"><div class="lebd-a4-scroll-inner">
 @if(!empty($cat->front_cover))
 <section class="lebd-cover-a4-page lebd-front-cover" aria-label="E-Book Front Cover">
  <img src="{{asset($cat->front_cover)}}" alt="{{ $cat->name }} Front Cover">
 </section>
 @endif
 @if(!empty($cat->details_page))
 <section class="lebd-cover-a4-page lebd-details-page" aria-label="E-Book Details Page">
  <img src="{{asset($cat->details_page)}}" alt="{{ $cat->name }} Book Details Page">
 </section>
 @endif
 <div class="lebd-book-toc-preview">
   <h3>সূচিপত্র — স্বয়ংক্রিয় পৃষ্ঠা নম্বর</h3>
   @foreach($bookChapters as $index => $entry)
     <div class="lebd-toc-auto-line"><span>অধ্যায়-{{ str_pad((string)($index+1),2,'0',STR_PAD_LEFT) }}: {{ $entry['ch']->title }}</span><span class="lebd-toc-auto-dots"></span><strong data-toc-range="{{ $entry['ch']->id }}">গণনা হচ্ছে…</strong></div>
   @endforeach
 </div>
 @forelse($bookChapters as $entry)
   @php $ch=$entry['ch']; $blocks=$entry['blocks']; $qs=$entry['qs']; $opts=$entry['opts']; @endphp
   <div data-chapter-id="{{ $ch->id }}" class="lebd-book-chapter-section {{ $loop->first ? 'lebd-first-chapter' : '' }}" id="chapter-{{$ch->id}}">
    <div class="lebd-chapter-preview-head">অধ্যায়-{{str_pad((string)$loop->iteration,2,'0',STR_PAD_LEFT)}}: {{$ch->title}}
     @if($preview)<a href="{{route('admin.ebook.editor',['kind'=>$kind,'chapter'=>$ch->id])}}">Edit Chapter</a>@endif
    </div>
    @include('ebook.partials.blocks', ['isEditor'=>false])
   </div>
 @empty
   <div class="lebd-book-empty">এই Category-তে এখনো কোনো অধ্যায় নেই।</div>
 @endforelse
 @if(!empty($cat->back_cover))
 <section class="lebd-cover-a4-page lebd-back-cover" aria-label="E-Book Back Cover">
  <img src="{{asset($cat->back_cover)}}" alt="{{ $cat->name }} Back Cover">
 </section>
 @endif
 </div></div>
</div>
@endsection
