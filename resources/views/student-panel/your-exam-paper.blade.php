@php
use \App\Http\Controllers\SourceCtrl;
$source = New SourceCtrl;
$user = Auth::guard('student')->user();
// dd($exam->choices->where('question_id', 65)->where('mcq_id', 139));
@endphp

@extends('student')
@section('title', 'Exam Paper')
@section('content')
<style>
  .checkbox{padding-left: 25px}
  .panel ::-webkit-scrollbar{width: 5px;}
  ::-webkit-scrollbar-thumb{background-color: #ddd}
  .mcqitems{list-style: none; padding-left: 10px}
  .mcqitems li{padding:5px; margin: 10px; max-width: 300px;}
  .mcqitems li label{ display: bl/ock;border-radius: 15px;border:1px solid #ddd; padding: 5px 15px; color: #444; cursor: pointer; font-weight: normal; min-width: 270px;}
  .mcqitems li input[type="radio"]{width: 20px;height: 20px; margin-right: 5px; padding-top:5px}
  .mcqitems li span{vertical-align: top}
  .banner{margin-top:15px}
  .banner img{width:100%}
  .timer{font-weight: bold; font-size: 20px; text-align: center; color:#666; border:2px solid; border-radius:10px}
  .time span{ border:2px solid #444; color:ddd}
  .sticky{position: fixed; top:50px; left: 0; right: 0; z-index: 999999;}
  #fixed{text-align: center}
  .selected{background:lightblue;}
  .selected input[type="radio"]{background:lightblue;}
  .result table th{text-align: right}
</style>

    <!-- Content Header (Page header) -->
    <section class="content-header"></section>

    <!-- Main content -->
    <section class="our_courses" id="content">
      <div class="container">
        <div class="row">
          <div class="title-area text-center">
            <h3>আপনার এক্সাম পেপার</h3>
          </div>
        </div>
      <div class="row p-3">
        <div class="col-md-12 no-padding">
          @foreach($paper->questions as $key => $value)
          @php
          $choice = $right_answer = $correct_id = '';
          $choiced_mcq = $exam->choices->where('question_id', $value->id)->first();
          if($choiced_mcq)
          {
            $choice = $choiced_mcq->mcq_id;
          }
          // if(!empty($choices[$value->id]))
          // {
          //   $choice = $choices[$value->id];
          // }
          @endphp
          <div class="card panel-default p-3 mt-3">
            <div class="card-heading" style="background-color:none">
              <div style="display: inline; font-weight:bold;float:left; padding-right:5px">প্রশ্ন {{$key+1}}.</div>
              <div style="display: inline;text-align:justify">{!! $value->title !!}</div>
            </div>
            <ul class="card-body mcqitems" id="{{$value->id}}">
              @foreach($value->mcqitems as $k => $val)
                @if($k <= 4)
                  @php
                  if($val->correct_answer)
                  {
                    $right_answer = $source->mcqlist()[$paper->format][$k].' '. $val->item;
                    $correct_id = $val->id;
                  }
                  @endphp
                  <li>
                    <label class="{{ $choice == $val->id ? 'selected':''}}">
                      @if($choice == $val->id && $correct_id != $choice)
                      <span style="font-size: 22px; color:red;"><i class="fa fa-times"></i></span>
                      @endif
                      <input {{ $choice != $val->id ? 'disabled':'checked'}} type="radio" />
                      <span> {{$source->mcqlist()[$paper->format][$k]}} {{$val->item}}</span>
                    </label>
                  </li>
                @endif
              @endforeach
            </ul>
            <div class="card-footer" style="color: #0a0">
              সঠিক উত্তরঃ <b>{{$right_answer}}</b>
            </div>
          </div>
          @endforeach
        </div> <!--/.col -->
      </div><!-- /.row -->
      
      @include('partials.back-button')
        
    </div>
    </section> <!-- /.content -->
@endsection
@section('scripts')
<script>
  </script>
@endsection