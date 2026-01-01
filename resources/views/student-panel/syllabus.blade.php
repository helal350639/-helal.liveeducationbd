@php
use \App\Http\Controllers\SourceCtrl;
$source = New SourceCtrl;
$qcount = 0; 
@endphp

@extends('student')
@section('title', 'Syllabus')
@section('content')
<style>
  .mcqitems{list-style: none; padding-left: 10px}
  .mcqitems li{padding: 10px}
  .banner{margin-top:15px}
  .banner img{width:100%}

  .department{font-size:12px; margin: 0 auto; padding-bottom:30px}
  .dept_title{border:1px solid #ddd}
  .sub_title{border-bottom:1px solid #000}
  .chap_title{border-bottom:1px dashed #000}
  .q_number{display: inline; font-weight:bold;float:left; padding-right:10px; text-align:justify}
  .q-title{display: inline; font-size:14px}
  .mcqitems{list-style: none}
  .exp-title{font-weight: bold}
  .explain{border:1px dotted #ddd}

  @media screen and (min-width:767px){
    .department{width:850px; columns:400px 2; column-gap:30px; column-rule: 1px solid #888; }
    .mcqitems{width:380px; column-width:170px;}
  }
  /* @media screen and (min-width:768px){
    .department{width:850px; columns:400px 2; column-gap:30px; column-rule: 1px solid #888; }
  } */
   #pdfLoading{
    position: fixed; top:0; left:0; bottom:0; width: 100%; height: 100%; background: rgba(0,0,0,0.1);
    text-align: center;
    padding-top: 20%
   }
</style>

  <!-- Main content -->
  <section class="our_courses">
    <div class="container">

      <!-- Content Header (Page header) -->
      <div class="title-area text-center">
        <h1>Exam PDF</h1>
      </div>
  
      <div class="row"><!-- left column -->
        <div class="col-md-12"><!-- general form elements -->
          <div class="box box-primary">
            <div class="box-header with-border">
              <h4 class="box-title" style="display: inline">Questions (<b>{{count($syllabus->questions)}}</b>)</h4>
              <div class="text-right toolbar-icon pull-right" style="display: inline">
                {{-- <a href="{{route('students.syllabus.pdf', $syllabus->id)}}" class="label label-info" title="Download"><i class="fa fa-download"></i> Download PDF </a> --}}
                <a href="javascript:void(0)" id="generatePdfBtn" class="label label-info" title="Download" data-url="{{route('students.syllabus.pdf', $syllabus->id)}}" onclick="pdfGenerate(this)">
                  <i class="fa fa-download"></i> Download PDF 
                </a>
              
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-md-12">
          <div class="box" id="print">
            <div class="col-md-12">
              <div class="banner">
                <img src="{{$syllabus->banner}}" alt=""/>
              </div>
              <div class="header" style="text-align:center">
                <h2>LIVE EDUCATION BD</h2>
                <p style="text-align: center"><b>কোর্সঃ  </b>{{$syllabus->course?$syllabus->course->name:''}}</p>
                <b>ব্যাচঃ  </b> {{$syllabus->batch->name}}
                {!! $syllabus->header !!} </div>
  
                <hr>
  
                <div class="department">
                  @foreach($groupedData as $department => $subjects)
                    @if($department)
                    <h3 class="dept_title">{{ $department }}</h3> <!-- Department Name -->
                    @endif
  
                    @foreach($subjects as $subject => $chapters)
                      @if($subject)
                      <h4 class="sub_title"><i class="fa fa-book"></i> {{ $subject }}</h4> <!-- Subject Name -->
                      <p style="position: absolute; margin-top:150px; margin-left: 120px; transform:rotate(-30deg); opacity: 0.2 ; font-size:25px">LiveEducationBD.com</p>
                      @endif
  
                      @foreach($chapters as $chapter => $questions)
                        @if($chapter)
                        <h5 class="chap_title"><i class="fa fa-file"></i> {{ $chapter }}</h5> <!-- Chapter Name -->
                        @endif
  
                        @foreach($questions as $questionId => $questionData)
                          <div class="question">
                            <div class="q_number">প্রশ্ন {{$qcount+1}}.</div>
                            <div class="q-title">{!! $questionData['question'] !!}</div>
                            @php
                            $correct_ans = '';
                            $qcount++;
                            @endphp
                            <ul class="mcqitems">
                              @foreach($questionData['mcqs'] as $k => $mcq)
                              @php
                              if($mcq['correct_answer'])
                              {
                                $correct_ans = $source->mcqlist()[$syllabus->format][$k].' '.$mcq['item'];
                              }
                              @endphp
                              <li><span> {{$source->mcqlist()[$syllabus->format][$k]}} </span>{{$mcq['item']}}</li>
                              @endforeach
                              <div class="clearfix"></div>
                            </ul>
                            <div style="color:green; clear:top; padding:10px 0;">সঠিক উত্তরঃ <b>{{$correct_ans}}</b></div>
                            @if(isset($questionData['explain']))
                            <div class="explain"><span class="exp-title">ব্যাখাঃ </span>{!! substr($questionData['explain'], 3, -4) !!}</div>
                            <br>
                            @endif
                          </div>
                        @endforeach
  
                      @endforeach
                    @endforeach
                  @endforeach
                </div><!--.department-->
              </div><!--.header-->
              <div class="clearfix"></div>
            </div><!--/.col -->
            <div class="clearfix"></div>
          </div><!-- /.box -->
        </div><!-- /.col -->
      </div><!-- /.row -->
        
      @include('partials.back-button')
      
    </div>
  </section><!-- /.content -->
  
  <div id="pdfLoading" style="display:none;">
    <img src="/img/loading.gif" alt="">
  </div>
   
@endsection
@section('scripts')
  
<script>
  function pdfGenerate(e)
  {
    let preloader = document.getElementById('pdfLoading');
    preloader.style.display = 'block';
    
    let url = e.dataset.url;

    fetch(url, {
        method: "GET",
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {

      preloader.style.display = 'none';

        if (data.status === 'success')
        {
            // Redirect to PDF file
            window.location.href = data.url;
        } else {
            alert('Failed to generate PDF');
        }
    })
    .catch(error => {
        document.getElementById('pdfLoading').style.display = 'none';
        alert('Something went wrong');
    });
  }

  function printDiv()
  {
    // document.getElementById('heading').style.display = 'block';
    var divToPrint = document.getElementById('print');
    var htmlToPrint = '' +
        '<style type="text/css">' +
        '.heading{display:block}'+
        '.pageheader{font-size:15px}'+
        'table { border-collapse:collapse; font-size:15px;width:100%}' +
        '.table tr th, .table tr td { padding: 10px; border:1px solid #ddd; text-align:left}' +
        'table tr{background: #ddd}'+
        '.receipt{display:none}'+
        '</style>';
    htmlToPrint += divToPrint.outerHTML;
    newWin = window.open(htmlToPrint);
    newWin.document.write(htmlToPrint);
    newWin.print();
    newWin.close();
    // document.getElementById('heading').style.display = 'none';
  }
</script>
@endsection