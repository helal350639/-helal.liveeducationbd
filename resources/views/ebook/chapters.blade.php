@extends('homepage')
@section('title',$cat->name.' — সূচিপত্র')
@section('content')
<link rel="stylesheet" href="{{ asset('web/css/lebd-ebook.css') }}">
<main class="ebook-wrap" style="max-width:980px;margin:25px auto">
 <div class="ebook-top"><a href="{{route('student.ebook.categories',['kind'=>$kind])}}">← E-Books</a>
  <h1>{{ $cat->name }}</h1><p>সূচিপত্র</p>
  <a class="ebook-btn" href="{{route('student.ebook.full-book',['kind'=>$kind,'category'=>$cat->id])}}">📖 সম্পূর্ণ বই পড়ুন</a>
 </div>
 <div class="ebook-panel"><ol class="ebook-list">
 @forelse($chapters as $ch)
  <li><span>অধ্যায়-{{ str_pad($loop->iteration,2,'0',STR_PAD_LEFT) }}: {{ $ch->title }}</span>
   <a class="ebook-btn alt" href="{{route('student.ebook.read',['kind'=>$kind,'category'=>$cat->id,'chapter'=>$ch->id])}}">পড়ুন →</a>
  </li>
 @empty<li>এখনো কোনো অধ্যায় নেই।</li>@endforelse
 </ol></div>
</main>
@endsection
