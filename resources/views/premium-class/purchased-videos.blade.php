@extends('homepage')
@section('title', 'Purchased Premium Videos')
@section('content')
<style>
.lebd-purchased-wrap{max-width:1080px;margin:32px auto;padding:18px;color:#12345b}
.lebd-purchased-wrap h1{font-size:27px}.lebd-purchased-list{display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:16px;margin-top:20px}
.lebd-purchased-card{border:1px solid #d6e5f5;border-radius:12px;padding:18px;background:#fff}
.lebd-purchased-card a{display:inline-block;margin-top:12px;padding:9px 14px;border-radius:7px;background:#0875df;color:#fff;text-decoration:none}
</style>
<div class="lebd-purchased-wrap">
 <h1>{{ $packageName }} — ভিডিও ক্লাস</h1>
 <p>আপনার কেনা প্যাকেজের অন্তর্ভুক্ত প্রকাশিত ক্লাসগুলো নিচে রয়েছে।</p>
 @if($videos->count())
  <div class="lebd-purchased-list">
   @foreach($videos as $video)
    <article class="lebd-purchased-card">
     <strong>{{ $video->title ?? $video->name ?? ('Video #'.$video->id) }}</strong>
     <p>{{ $video->premium_group }} / {{ $video->premium_topic }}</p>
     <a href="{{ route('premium.recordings.watch', ['video' => $video->id]) }}">ভিডিও দেখুন →</a>
    </article>
   @endforeach
  </div>
  <div style="margin-top:20px">{{ $videos->links() }}</div>
 @else
  <p>এই প্যাকেজের জন্য প্রকাশিত ভিডিও পাওয়া যায়নি। প্যাকেজের Category Mapping পরীক্ষা করুন।</p>
 @endif
</div>
@endsection
