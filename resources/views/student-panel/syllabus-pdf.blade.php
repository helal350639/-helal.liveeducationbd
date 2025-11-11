@php
use \App\Http\Controllers\SourceCtrl;
$source = New SourceCtrl;
$qcount = 0; 
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
  <title>Syllabus</title>
  <style type="text/css">
    @font-face {
      font-family: 'nikosh';
      src: url('{{ storage_path("fonts/Nikosh.ttf") }}') format('truetype');
      font-weight: normal;
      font-style: normal;
    }
    body {
      font-family: 'nikosh', georgia;
      font-size: 14px;
    }

    .mcqitems{list-style: none; padding-left: 10px}
    .mcqitems li{padding: 10px}
    .banner{margin-top:15px}
    .banner img{width:100%}

    .department{border: 1px solid #888; font-size:14px; margin: 0 auto; padding-bottom:30px}
    .dept_title{border:1px solid}
    .sub_title{border-bottom:1px solid}
    .chap_title{border-bottom:1px dashed}
    .q_number{font-size:14px; padding-right:10px; text-align:justify}
    .q-title{font-size:14px}
    .mcqitems{width:380px; column-width:170px;list-style: none}
    .exp-title{font-weight: bold}
    .explain{border:1px dotted #ddd}
  </style>
</head>
<body>

<div class="content-wrapper">
  <div class="container">

    <!-- Main content -->
  <section class="content">
    <div class="row">
      <div class="col-md-12">

        <div class="box" id="print">
        <div class="col-md-12">
          @if($syllabus->banner)
          <div class="banner">
            <img src="{{$syllabus->banner}}" alt=""/>
          </div>
          @endif
          <div class="header" style="text-align:center">
            <h2>LIVE EDUCATION BD</h2>
            <p style="text-align: center">Course: <b>{{$syllabus->course?$syllabus->course->name:''}}</b></p>
            <b>Batch:</b> {{$syllabus->batch->name}}
            {!! $syllabus->header !!} 
          </div>
          <hr>
            <columns column-count="2" vAlign="J" column-gap="7" column-rule="1 solid #333">
              @foreach($groupedData as $department => $subjects)
              <h3 class="dept_title">ডিপার্টমেন্টঃ {{ $department }}</h3> <!-- Department Name -->

              @foreach($subjects as $subject => $chapters)
                <h4 class="sub_title">বিষয়ঃ {{ $subject }}</h4> <!-- Subject Name -->

                @foreach($chapters as $chapter => $questions)
                 @if($chapter)
                  <h5 class="chap_title">আধায়ঃ {{ $chapter }}</h5> <!-- Chapter Name -->
                  @endif

                    @foreach($questions as $questionId => $questionData)
                    <div class="question">
                      <span style="display: inline-flex">
                        <span class="q_number">প্রশ্ন {{$qcount+1}}.</span>
                        <span>{!! preg_replace('/<\/?p>/i', '', $questionData['question']) !!}</span>
                      </span>
                      @php
                      $correct_ans = '';
                      $qcount++;
                      @endphp
                      <div class="mcqitems">
                        @foreach($questionData['mcqs'] as $k => $mcq)
                        @php
                        if($mcq['correct_answer'])
                        {
                          $correct_ans = $source->mcqlist()[$syllabus->format][$k].' '.$mcq['item'];
                        }
                        @endphp
                        <div>
                          <span> {{$source->mcqlist()[$syllabus->format][$k]}} </span>{{$mcq['item']}}
                        </div>
                        @endforeach
                        <div class="clearfix"></div>
                      </div>
                      <div style="color:green; clear:top; padding:10px 0;">সঠিক উত্তরঃ <b>{{$correct_ans}}</b></div>
                      @if(isset($questionData['explain']))
                      <div class="explain">
                        <span class="exp-title">ব্যাখাঃ </span>
                        {!! Str::substr($questionData['explain'], 3, -4) !!}
                      </div>
                      <br>
                      @endif
                    </div>
                    @endforeach

                @endforeach
              @endforeach
            @endforeach
          </columns>
          </div>
          <div class="clearfix"></div>
        </div><!--/.col -->
      </div><!-- /.col -->

      </div><!-- /.col -->
    </div><!-- /.row -->
  </section><!-- /.content -->
</div> <!-- /.container -->
</div>
  
</body>
</html>