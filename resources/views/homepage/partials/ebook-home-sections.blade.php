@php
 $lebdHomeEbooks=\Illuminate\Support\Facades\DB::table('lebd_ebook_categories')->where('status','Active')->orderBy('sort_order')->orderBy('id')->get();
@endphp
<link rel="stylesheet" href="{{asset('web/css/lebd-ebook-student-v22.css')}}">
<section class="leb-home-wrap" aria-label="E-Book Library">
@foreach(['department'=>'E-Book / PDF ( Diploma )','non-department'=>'E-Book / PDF ( General )'] as $lebdKind=>$lebdHeading)
 <div class="leb-home-group"><header><h3>📚 {{$lebdHeading}}</h3><a href="{{route('student.ebook.categories',['kind'=>$lebdKind])}}">সব দেখুন →</a></header>
 <div class="leb-home-cards">
 @foreach($lebdHomeEbooks->where('kind',$lebdKind)->take(10) as $lebdCat)
  <a class="leb-home-card" href="{{route('student.ebook.chapters',['kind'=>$lebdKind,'category'=>$lebdCat->id])}}" style="background:{{ $lebdCat->card_color }}">
   @if($lebdCat->front_cover)<img src="{{asset($lebdCat->front_cover)}}" alt="{{$lebdCat->name}} cover" loading="lazy">@else<i class="fa {{$lebdCat->icon}}" style="color:{{$lebdCat->icon_color}}"></i>@endif
   <strong>{{$lebdCat->name}}</strong>
  </a>
 @endforeach
 </div></div>
@endforeach
</section>
