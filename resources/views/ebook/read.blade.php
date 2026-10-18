@extends($preview ? 'dashboard' : 'homepage')
@section('title', $cat->name.' — '.$ch->title.' | E-Book')
@section('content')
<link rel="stylesheet" href="{{ asset('web/css/lebd-ebook.css') }}">
<main class="ebook-wrap lebd-ebook-reading-page" style="max-width:none">
  <div class="ebook-top lebd-ebook-reading-actions" style="max-width:850px;margin:0 auto 18px">
    <h1>{{ $cat->name }} — {{ $ch->title }}</h1>
    @if($preview)
      <a class="ebook-btn alt" href="{{ route('admin.ebook.editor',['kind'=>$kind,'chapter'=>$ch->id]) }}">← Admin Editor</a>
    @else
      <a class="ebook-btn alt" href="{{ route('student.ebook.chapters',['kind'=>$kind,'category'=>$cat->id]) }}">← সূচিপত্র</a>
    @endif
    <button type="button" class="ebook-btn" onclick="window.print()">Print / Save as PDF (A4)</button>
  </div>
  @include('ebook.partials.blocks',['isEditor'=>false])
</main>
<style>@media print {.lebd-ebook-reading-actions{display:none!important}.content-wrapper,.ebook-wrap{margin:0!important;padding:0!important;max-width:none!important;background:white!important}}</style>
@endsection
