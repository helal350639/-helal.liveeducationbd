@extends('dashboard')
@section('title','E-Book — Chapter Content Editor')
@section('content')
<link rel="stylesheet" href="{{ asset('web/css/lebd-ebook-admin-v15.css') }}">
@php
$letters=['ক','খ','গ','ঘ','ঙ','চ','ছ','জ'];
$serial=0;
@endphp
<section class="eb15">
 <header class="eb15-header">
  <a href="{{ route('admin.ebook.chapters',['kind'=>$kind,'category'=>$cat->id]) }}">← সূচিপত্র</a>
  <h1>{{ $cat->name }} — {{ $ch->title }}</h1>
  <p>এটি Admin Editor। Student-এর A4 Reading Page আলাদা থাকবে।</p>
  <div class="eb15-actions">
   <a class="eb15-btn" href="{{ route('admin.ebook.picker',['kind'=>$kind,'chapter'=>$ch->id]) }}">+ Main Database MCQ নির্বাচন</a>
   <a class="eb15-btn eb15-light" target="_blank" rel="noopener" href="{{ route('admin.ebook.preview',['kind'=>$kind,'chapter'=>$ch->id]) }}">A4 Student Preview</a>
  </div>
 </header>
 @if(session('success'))<div class="eb15-message">{{session('success')}}</div>@endif
 @if($errors->any())<div class="eb15-error">{{$errors->first()}}</div>@endif
 <div class="eb15-toolbar"><h2>অধ্যায়ের Content ({{ $blocks->count() }} Items)</h2><span>প্রশ্ন নম্বর স্বয়ংক্রিয়ভাবে হিসাব হয়। Up/Down ও Insert করলে আবার ধারাবাহিক হবে।</span></div>
 @php $afterId=0; @endphp
 @include('admin.ebook.partials.insert-form', ['insertAfter'=>0,'insertLabel'=>'অধ্যায়ের শুরুতে Content যোগ করুন'])
 @foreach($blocks as $block)
 @php
   $isDb=$block->block_type==='database_mcq';
   $question=$isDb ? $qs->get($block->question_id) : null;
   $choices=$isDb ? $opts->get($block->question_id,collect()) : collect();
   $isQuestion=($block->block_type==='custom_question') || ($isDb && $question);
   if($isQuestion) $serial++;
   $options=$block->block_type==='custom_question' ? (json_decode($block->options_json ?? '[]',true) ?: []) : [];
   $answer='';
   if($isDb && $question){foreach($choices as $i=>$choice){if($choice->correct_answer){$answer=$letters[$i]??(string)($i+1);break;}}}
   if($block->block_type==='custom_question') $answer=(string)($block->answer??'');
   $explanation=$isDb && $question ? ($question->solution ?: $question->explanation) : $block->explanation;
 @endphp
 <article class="eb15-item" id="ebook-block-{{$block->id}}">
  <div class="eb15-itembar">
   <strong>@if($isQuestion) প্রশ্ন-{{str_pad((string)$serial,2,'0',STR_PAD_LEFT)}} @else {{ $block->block_type==='heading'?'Heading':($block->block_type==='text'?'Short Description':'Database Question') }} @endif</strong>
   <span>Item {{$loop->iteration}}</span>
   <div class="eb15-controls">
    <form method="POST" action="{{route('admin.ebook.blocks.move',['kind'=>$kind,'chapter'=>$ch->id,'block'=>$block->id])}}">@csrf<input type="hidden" name="direction" value="up"><button type="submit" {{$loop->first?'disabled':''}}>↑ Up</button></form>
    <form method="POST" action="{{route('admin.ebook.blocks.move',['kind'=>$kind,'chapter'=>$ch->id,'block'=>$block->id])}}">@csrf<input type="hidden" name="direction" value="down"><button type="submit" {{$loop->last?'disabled':''}}>↓ Down</button></form>
    <form method="POST" action="{{route('admin.ebook.blocks.delete',['kind'=>$kind,'chapter'=>$ch->id,'block'=>$block->id])}}" onsubmit="return confirm('এই Content টি সরাবেন? Main Database-এর প্রশ্ন মুছবে না।')">@csrf @method('DELETE')<button type="submit" class="eb15-danger">Delete</button></form>
   </div>
  </div>
  <div class="eb15-body">
   @if($block->block_type==='heading')
    <h3>{{$block->title}}</h3>
   @elseif($block->block_type==='text')
    <div class="eb15-prose">{!!nl2br(e($block->content??''))!!}</div>
   @elseif($isQuestion)
    <div class="eb15-qtext">{!! $isDb ? ($question->title??'') : e($block->title??'') !!}</div>
    <div class="eb15-options">
     @if($isDb)
      @foreach($choices as $i=>$choice)<div class="eb15-option"><b>{{$letters[$i]??$i+1}})</b> <span>{!!$choice->item!!}</span></div>@endforeach
     @else
      @foreach($options as $i=>$option)<div class="eb15-option"><b>{{$letters[$i]??$i+1}})</b> <span>{{ $option }}</span></div>@endforeach
     @endif
    </div>
    <div class="eb15-answer"><b>সঠিক উত্তর:</b> {{$answer!==''?$answer:'প্রদান করা হয়নি'}}</div>
    @if(!empty($explanation))<div class="eb15-explanation"><b>ব্যাখ্যা:</b> @if($isDb){!!$explanation!!}@else{{ $explanation }}@endif</div>@endif
   @else
    <div class="eb15-error">মূল Database-এ এই প্রশ্নটি পাওয়া যায়নি। Question ID: {{$block->question_id}}</div>
   @endif
   @if(!$isDb)
    <details class="eb15-edit"><summary>Edit এই Content</summary>
     <form method="POST" action="{{route('admin.ebook.blocks.update',['kind'=>$kind,'chapter'=>$ch->id,'block'=>$block->id])}}">@csrf @method('PUT')
      @if($block->block_type!=='text')<label>Title / Question<input name="title" value="{{$block->title}}" required></label>@endif
      @if($block->block_type==='text')<label>Short Description<textarea name="content" rows="4" required>{{$block->content}}</textarea></label>@endif
      @if($block->block_type==='custom_question')
       <div class="eb15-edit-options">@foreach($options as $option)<input name="options[]" value="{{$option}}" aria-label="Option {{$loop->iteration}}">@endforeach</div>
       <label>সঠিক উত্তর<select name="answer">@foreach($letters as $letter)<option value="{{$letter}}" {{$answer===$letter?'selected':''}}>{{$letter}}</option>@endforeach</select></label>
       <label>ব্যাখ্যা<textarea name="explanation" rows="3">{{$block->explanation}}</textarea></label>
      @endif
      <button class="eb15-btn" type="submit">Save Changes</button>
     </form>
    </details>
   @else
    <small class="eb15-note">এই MCQ Main Database থেকে Live দেখানো হচ্ছে। প্রশ্নের মূল Text/Edit করতে Questions Module ব্যবহার করুন।</small>
   @endif
  </div>
 </article>
 @include('admin.ebook.partials.insert-form', ['insertAfter'=>$block->id,'insertLabel'=>'এই Item-এর পরে Heading / Note / New Question যোগ করুন'])
 @endforeach
 @if($blocks->isEmpty())<p class="eb15-empty">অধ্যায়ে এখনো Content নেই। উপরের Insert Form বা Main Database MCQ Picker থেকে শুরু করুন।</p>@endif
</section>
<script>
document.addEventListener('change',function(e){if(!e.target.matches('.eb15-type'))return;const form=e.target.closest('.eb15-insert');const type=e.target.value;form.querySelector('.eb15-titlefield').hidden=type==='text';form.querySelector('.eb15-textfield').hidden=type!=='text';form.querySelector('.eb15-mcqfields').hidden=type!=='custom_question';});
</script>
@endsection
