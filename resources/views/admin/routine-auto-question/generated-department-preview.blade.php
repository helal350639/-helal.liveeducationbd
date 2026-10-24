@extends('dashboard')
@section('title','Department Paper Preview')
@section('content')
<style>
.lebd-paper-head{display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap}
.lebd-paper-summary{background:#f4f7fb;border:1px solid #dce4ec;border-radius:8px;padding:13px 16px;margin:15px 0}
.lebd-paper-subject{background:#e9f2fc;border-left:5px solid #2473ac;border-radius:4px;padding:12px 14px;margin:22px 0 12px;display:flex;justify-content:space-between;gap:12px;align-items:center}
.lebd-mcq-card{border:1px solid #e0e4ea;border-radius:8px;padding:15px 17px;margin:12px 0;background:#fff;break-inside:avoid}
.lebd-mcq-title{font-size:15px;font-weight:600;line-height:1.65;margin-bottom:12px;overflow-wrap:anywhere}
.lebd-mcq-options{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:9px;margin:8px 0}
.lebd-mcq-option{padding:9px 12px;border:1px solid #e1e6eb;border-radius:6px;line-height:1.5;overflow-wrap:anywhere}
.lebd-mcq-answer{margin-top:10px;color:#17633c;font-weight:600}
.lebd-mcq-muted{color:#6a7680;font-size:12px}
.lebd-mcq-image{max-width:100%;max-height:320px;margin:8px 0;object-fit:contain}
@media(max-width:640px){.lebd-mcq-options{grid-template-columns:1fr}.lebd-paper-subject{display:block}}
@media print{.main-header,.main-sidebar,.content-header .btn,.lebd-no-print{display:none!important}.content-wrapper{margin-left:0!important}.lebd-mcq-card{break-inside:avoid}.lebd-paper-subject{print-color-adjust:exact}}
</style>
<section class="content-header">
    <div class="lebd-paper-head">
        <h1>Department Full MCQ Paper — {{ $department->name }}</h1>
        <a class="btn btn-default btn-sm lebd-no-print" href="{{ route('admin.routine-auto-question.create-question',['courseId'=>$course->id,'batchId'=>$batch->id,'examId'=>$exam->exam_id]) }}">Back</a>
    </div>
</section>
<section class="content">
  <div class="box box-primary">
    <div class="box-header with-border lebd-paper-head">
       <h3 class="box-title">{{ $exam->exam_name }} / {{ $batch->name }}</h3>
       <button type="button" class="btn btn-default btn-sm lebd-no-print" onclick="window.print()">Print Preview</button>
    </div>
    <div class="box-body">
      <div class="lebd-paper-summary">
        <strong>Common:</strong> {{ count(array_unique($common)) }}
        &nbsp; | &nbsp; <strong>Department:</strong> {{ count(array_unique($own)) }}
        &nbsp; | &nbsp; <strong>Overlapping IDs:</strong> {{ $duplicateCount }}
        &nbsp; | &nbsp; <strong>Total Unique:</strong> {{ count($ids) }}
        <div class="lebd-mcq-muted" style="margin-top:6px">Common + Department − Overlapping IDs = Total Unique. A repeated question is displayed once.</div>
      </div>
      @if(count($ids) === 0)
        <div class="alert alert-info">No questions saved yet. Save your question selection in Auto Add Questions first.</div>
      @endif
      @php $serial = 0; @endphp
      @foreach($groups as $group)
        <div class="lebd-paper-subject">
          <strong>{{ $group['title'] }}</strong>
          <span>{{ count($group['ids']) }} MCQ &nbsp;·&nbsp; {{ $group['part'] }}</span>
        </div>
        @foreach($group['ids'] as $id)
          @php
            $serial++;
            $question = $questions->get($id);
            $options = $optionSets[$id] ?? [];
            $answer = $answers[$id] ?? null;
            $rawImage = $imagePaths[$id] ?? null;
            $imageUrl = null;
            if (is_string($rawImage) && $rawImage !== '' && !str_contains($rawImage, '..') && !preg_match('/^(?:https?:)?\/\//i', $rawImage)) {
                $imageUrl = asset(ltrim($rawImage, '/'));
            }
          @endphp
          <article class="lebd-mcq-card">
            <div class="lebd-mcq-title">
              {{ $serial }}. {!! $question ? ($question->title ?? 'Question title missing') : 'Question no longer found' !!}
            </div>
            @if($imageUrl)<img class="lebd-mcq-image" src="{{ $imageUrl }}" alt="Question image">@endif
            @if(count($options))
              <div class="lebd-mcq-options">
                @foreach($options as $letter => $text)
                  <div class="lebd-mcq-option"><strong>{{ $letter }})</strong> {!! $text !!}
                    @php
                      $rawOptionImage = $optionImages[$id][$letter] ?? null;
                      $optionImageUrl = null;
                      if (is_string($rawOptionImage) && $rawOptionImage !== '' && !str_contains($rawOptionImage, '..') && !preg_match('/^(?:https?:)?\/\//i', $rawOptionImage)) {
                          $optionImageUrl = asset(ltrim($rawOptionImage, '/'));
                      }
                    @endphp
                    @if($optionImageUrl)<div><img class="lebd-mcq-image" src="{{ $optionImageUrl }}" alt="Option {{ $letter }} image"></div>@endif
                  </div>
                @endforeach
              </div>
            @else
              <div class="lebd-mcq-muted">No MCQ options are saved for this question.</div>
            @endif
            @if($answer !== null)
              <div class="lebd-mcq-answer">Correct Answer: {{ $answer }}</div>
            @else
              <div class="lebd-mcq-muted">No correct option is marked in the saved MCQ items.</div>
            @endif
            <div class="lebd-mcq-muted" style="margin-top:8px">Question ID: {{ $id }} · {{ in_array($id,$common,true) ? 'Common' : 'Department' }}
              @if(!empty($group['chapters'][$id])) · Chapter: {{ $group['chapters'][$id] }} @endif
            </div>
          </article>
        @endforeach
      @endforeach
      <p class="text-muted">Admin-only preview based on saved question IDs. No exam data is changed by opening this page.</p>
    </div>
  </div>
</section>
@endsection
