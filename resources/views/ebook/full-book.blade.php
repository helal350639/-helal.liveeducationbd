@extends($preview ? 'dashboard' : 'homepage')
@section('title',$cat->name.' — Complete E-Book')
@section('content')
<link rel="stylesheet" href="{{ asset('web/css/lebd-ebook.css') }}">
<link rel="stylesheet" href="{{ asset('web/css/lebd-ebook-full-book-v16.css') }}">
<link rel="stylesheet" href="{{ asset('web/css/lebd-ebook-cover-v19.css') }}">
<div class="lebd-complete-book">
 <div class="lebd-complete-toolbar">
  <div><h2>{{ $cat->name }} — সম্পূর্ণ বই</h2><p>সূচিপত্র অনুসারে অধ্যায়; নতুন অধ্যায় সর্বদা নতুন A4 পৃষ্ঠা থেকে শুরু</p></div>
  <div class="lebd-complete-buttons">
   @if($preview)
     <a href="{{route('admin.ebook.chapters',['kind'=>$kind,'category'=>$cat->id])}}">← সূচিপত্র</a>
   @else
     <a href="{{route('student.ebook.chapters',['kind'=>$kind,'category'=>$cat->id])}}">← সূচিপত্র</a>
   @endif
   <button type="button" onclick="window.print()">Print / Save PDF</button>
  </div>
 </div>

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
</div>
@endsection
