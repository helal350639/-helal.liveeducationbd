@extends('student')
@section('title', 'Merit List - Exams')
@section('stylesheets')
<style>
.lebd-merit-list-page{background:#f1f6fd;min-height:0;padding:18px 12px 28px;color:#142b50}
#Footer{display:none!important}
.lebd-merit-list-wrap{max-width:1100px;margin:0 auto}
.lebd-ml-crumb{display:flex;align-items:center;gap:9px;margin:0 0 14px;color:#58708e;font-size:12px}
.lebd-ml-back{display:inline-flex;align-items:center;gap:7px;padding:8px 13px;border:1px solid #c9def8;border-radius:10px;background:#fff;color:#075fbd!important;text-decoration:none!important;font-weight:800;box-shadow:0 3px 9px #16589d12}
.lebd-ml-hero{display:flex;align-items:center;gap:16px;padding:19px 22px;margin-bottom:15px;border:1px solid #d5e5fa;border-radius:16px;background:linear-gradient(105deg,#fff,#e3f1ff);box-shadow:0 5px 16px #1d4f8910}
.lebd-ml-hero-icon{width:54px;height:54px;flex:0 0 54px;display:grid;place-items:center;border-radius:15px;background:linear-gradient(145deg,#35a6ff,#0865d8);color:#fff;font-size:24px}
.lebd-ml-hero h1{margin:0 0 4px;font-size:27px;font-weight:900;color:#102e5c}.lebd-ml-hero p{margin:0;color:#526985;font-size:13px}
.lebd-ml-heading{display:flex;align-items:center;justify-content:space-between;margin:0 0 10px;color:#163c70;font-size:16px;font-weight:900}
.lebd-ml-list{display:grid;gap:10px}
.lebd-ml-card{display:flex;align-items:center;gap:14px;padding:14px 16px;border:1px solid #d8e6f7;border-radius:14px;background:#fff;box-shadow:0 4px 13px #1b437512;transition:transform .15s,box-shadow .15s}
.lebd-ml-card:hover{transform:translateY(-1px);box-shadow:0 7px 17px #1b43751b}
.lebd-ml-number{width:44px;height:44px;flex:0 0 44px;display:grid;place-items:center;border-radius:12px;background:#e8f3ff;color:#0874d8;font-size:17px;font-weight:900}
.lebd-ml-info{min-width:0;flex:1}.lebd-ml-info h3{margin:0 0 5px;color:#173b6d;font-size:15px;font-weight:900;overflow-wrap:anywhere}.lebd-ml-info p{margin:2px 0;color:#6a7f99;font-size:11px}
.lebd-ml-open{display:inline-flex;align-items:center;justify-content:center;gap:8px;flex:0 0 auto;padding:10px 15px;border-radius:10px;background:linear-gradient(100deg,#0862ce,#0784e9);color:#fff!important;text-decoration:none!important;font-size:12px;font-weight:850;box-shadow:0 4px 10px #0875d52b}
.lebd-ml-open:hover{filter:brightness(1.04)}
.lebd-ml-empty{padding:30px 14px;text-align:center;border:1px dashed #cbdcf0;border-radius:13px;background:#fff;color:#71849d;font-size:13px}
@media(max-width:640px){.lebd-merit-list-page{padding:8px 7px 16px}.lebd-ml-crumb{margin-bottom:8px;font-size:11px}.lebd-ml-back{padding:6px 10px;font-size:11px;border-radius:8px}.lebd-ml-hero{gap:10px;padding:11px 12px;margin-bottom:10px;border-radius:12px}.lebd-ml-hero-icon{width:39px;height:39px;flex-basis:39px;border-radius:10px;font-size:18px}.lebd-ml-hero h1{font-size:19px}.lebd-ml-hero p{font-size:10px;line-height:1.4}.lebd-ml-heading{font-size:13px;margin-bottom:7px}.lebd-ml-list{gap:7px}.lebd-ml-card{gap:8px;padding:9px;border-radius:11px}.lebd-ml-number{width:34px;height:34px;flex-basis:34px;border-radius:9px;font-size:13px}.lebd-ml-info h3{font-size:12px;margin-bottom:3px}.lebd-ml-info p{font-size:9px}.lebd-ml-open{gap:5px;padding:8px 9px;border-radius:8px;font-size:10px;white-space:nowrap}}
@media(max-width:360px){.lebd-ml-card{gap:6px;padding:8px}.lebd-ml-open{padding:7px;font-size:9px}.lebd-ml-info h3{font-size:11px}}
</style>
@endsection
@section('content')
<div class="lebd-merit-list-page">
  <div class="lebd-merit-list-wrap">
    <div class="lebd-ml-crumb">
      <a class="lebd-ml-back" href="{{ route('student.exam-section.browse-exams', ['category'=>$categoryId, 'subcategory_id'=>$subcategoryId]) }}"><i class="fa fa-arrow-left"></i> ফিরে যান</a>
      <span>›</span><strong>Merit List</strong>
    </div>
    <header class="lebd-ml-hero">
      <span class="lebd-ml-hero-icon"><i class="fa fa-trophy"></i></span>
      <div><h1>Merit List</h1><p>{{ $scopeTitle }} — পরীক্ষাভিত্তিক মেধা তালিকা</p></div>
    </header>
    <div class="lebd-ml-heading"><span><i class="fa fa-list-alt" style="color:#0876df;margin-right:7px"></i>পরীক্ষা নির্বাচন করুন</span><small style="font-size:10px;color:#6b8099">মোট {{ $examCards->count() }} টি পরীক্ষা</small></div>
    @if($examCards->isNotEmpty())
      <div class="lebd-ml-list">
        @foreach($examCards as $index => $exam)
          <article class="lebd-ml-card">
            <span class="lebd-ml-number">{{ $index + 1 }}</span>
            <div class="lebd-ml-info">
              <h3>{{ $exam->exam_name ?: 'Exam-' . str_pad((string)($index + 1), 2, '0', STR_PAD_LEFT) }}</h3>
              <p><b>Routine:</b> {{ $exam->routine_name }}</p>
              @if($exam->exam_end_at)<p><i class="fa fa-calendar"></i> {{ \Carbon\Carbon::parse($exam->exam_end_at, 'Asia/Dhaka')->format('d M Y, h:i A') }}</p>@endif
            </div>
            <a class="lebd-ml-open" href="{{ route('student.exam-section.merit-list', ['category'=>$categoryId, 'item'=>$exam->id, 'subcategory_id'=>$subcategoryId]) }}">Merit List <i class="fa fa-arrow-right"></i></a>
          </article>
        @endforeach
      </div>
    @else
      <div class="lebd-ml-empty"><i class="fa fa-info-circle" style="font-size:22px;display:block;margin-bottom:8px"></i>এই Category / Sub-category-তে এখনো কোনো Published Exam পাওয়া যায়নি।</div>
    @endif
  </div>
</div>
@endsection
