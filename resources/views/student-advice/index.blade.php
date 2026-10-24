<link rel="stylesheet" href="{{ asset('web/css/lebd-student-advice-fix125.css') }}?v=125">
@extends('homepage')
@section('title','সফল শিক্ষার্থীদের পরামর্শ')
@section('content')
<section class="success-students-section" style="padding:35px 20px"><div class="success-container">
<div class="success-heading"><h2>সফল শিক্ষার্থীদের <span>পরামর্শ</span></h2></div>
<div class="success-grid">
@foreach($items as $item)
<article class="success-card"><div class="student-top"><div class="student-image">@if($item->photo_path)<img src="{{asset($item->photo_path)}}" alt="{{$item->name}}" style="width:100%;height:100%;object-fit:cover">@endif</div><div class="student-info"><h3>{{$item->name}}</h3><p>BB ID: {{$item->bb_id}}</p><p>{{$item->university}}</p></div></div><div class="student-content"><div class="student-position"><div class="position-icon"><i class="fas fa-building"></i></div><div><h4>{{$item->position}}</h4><p>{{$item->organization}}</p></div></div><div class="advice-title"><span></span><strong>পরামর্শ</strong><span></span></div><div class="lebd-advice-expand"><p class="advice-text lebd-advice-copy" id="lebd-advice-{{ $item->id }}">{{$item->advice}}</p><button type="button" class="lebd-advice-toggle" aria-expanded="false" aria-controls="lebd-advice-{{ $item->id }}" hidden>See More <i class="fas fa-chevron-down" aria-hidden="true"></i></button></div></div></article>
@endforeach
</div><div style="margin-top:20px">{{$items->links()}}</div></div></section>
@endsection

<script src="{{ asset('web/js/lebd-student-advice-fix125.js') }}?v=125" defer></script>
