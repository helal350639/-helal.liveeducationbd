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
    body {
      font-family: 'lohitbengali', sans-serif;
      font-size: 13px;
      line-height: 1.55;
    }
    .question, .mcqitems, .explain, .header, .dept_title, .sub_title, .chap_title {
      font-family: 'lohitbengali', sans-serif;
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
                      @php
                        $correct_ans = '';
                        $qcount++;
                        $questionText = (string) ($questionData['question'] ?? '');
                        // Strip a serial already typed into the saved title so the PDF
                        // always displays exactly one generated question number.
                        $questionText = preg_replace(
                          '/^\\s*(?:<[^>]+>\\s*)*(?:(?:প্রশ্ন\\s*)?[০-৯0-9]+\\s*[.\\)\\-।]\\s*)/u',
                          '',
                          $questionText,
                          1
                        );
                        // Clean malformed imported titles such as:
                        // "Civil (Question 10)1. Civil (Question 10)Question 10)"
                        $questionText = preg_replace('/(?<=\\))\\s*[০-৯0-9]+\\.\\s*/u', ' ', $questionText, 1);
                        $questionText = preg_replace('/(.{8,}?)\\s*\\1/u', '$1', $questionText, 1);
                        $questionText = preg_replace('/\\(([^()]{4,})\\)\\s*\\1\\)/u', '($1)', $questionText, 1);
                      @endphp
                      <div class="question-line">
                        <span class="q_number">প্রশ্ন {{$qcount}}.</span>
                        <span>{!! preg_replace('/<\\/?p[^>]*>/i', '', $questionText) !!}</span>
                      </div>
                      <div class="mcqitems">
                        @foreach($questionData['mcqs'] as $k => $mcq)
                        @php
                          $optionLabel = $source->mcqlist()[$syllabus->format][$k] ?? '';
                          $optionText = (string) ($mcq['item'] ?? '');
                          // Some saved MCQ rows already contain their A./ক. label.
                          // Remove one embedded copy and print the configured label once.
                          if ($optionLabel !== '') {
                            $labelPattern = '/(?<![A-Za-z])' . preg_quote(trim($optionLabel), '/') . '(?![A-Za-z])/iu';
                            $optionText = preg_replace($labelPattern, '', $optionText);
                          }
                          $optionText = preg_replace('/(.{4,}?)\\s*\\1/u', '$1', trim($optionText), 1);
                          $optionText = trim($optionText);
                          if ($mcq['correct_answer']) {
                            $correct_ans = trim($optionLabel . ' ' . strip_tags($optionText));
                          }
                        @endphp
                        <div>
                          @if($optionLabel !== '')<span>{{$optionLabel}} </span>@endif
                          {!! $optionText !!}
                        </div>
                        @endforeach
                        <div class="clearfix"></div>
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