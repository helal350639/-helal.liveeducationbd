@php
use Carbon\Carbon;
use \App\Http\Controllers\SourceCtrl;
$source = New SourceCtrl;
$user = Auth::guard('student')->user();
// $end_time = date('Y-m-d H:i:s', strtotime('+'.$paper->time.' minutes', strtotime(now())));
// $end_time = now()->addMinutes($source->timeIncrease($paper->time));
$end_time = now()->addMinutes($paper->time);
$questions = $paper->questions;
if($paper->random == 'Yes')
{
  // $questions = $paper->questions()->inRandomOrder()->get();
}
$start_at = strtotime(now());

$exam_id = null;
if($exam)
{
  $exam_id = $exam->id;
}
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link rel="icon" href="/img/logo.png" />
  <title>Exam</title>
  <link rel="stylesheet" href="{{'/web/assets/vendors/slick/slick.css'}}" />
  <link rel="stylesheet" href="{{'/web/assets/vendors/slick/slick-theme.css'}}"/>
  <link rel="stylesheet" href="{{'/web/assets/css/bootstrap.min.css'}}" />
  <link rel="stylesheet" href="{{'/web/css/style.css?v=1.3.3'}}" />
  <link rel="stylesheet" href="{{'/web/assets/css/style2b24.css?v=3.8.25'}}" />
  <style>
    .checkbox{padding-left: 25px}
    .panel ::-webkit-scrollbar{width: 5px;}
    ::-webkit-scrollbar-thumb{background-color: #ddd}
    .mcqitems{list-style: none; padding-left: 10px}
    .mcqitems li{padding:5px; max-width: 300px;}
    .mcqitems li:first-child{margin-top:10px}
    .mcqitems li label{ display: bl/ock;border-radius: 15px;border:1px solid #ddd; padding: 5px 15px; color: #444; cursor: pointer; font-weight: normal; max-width: 300px; width: 100%; padding-top: 10px}
    .mcqitems li input[type="radio"]{width: 20px;height: 20px; margin-right: 5px; padding-top:5px}
    .mcqitems li span{vertical-align: top}
    .banner{margin-top:15px}
    .banner img{width:100%}
    .timer{font-weight: bold; font-size: 20px; text-align: center; color:#666; border:2px solid; border-radius:10px}
    .time span{ border:2px solid #444; color:ddd}
    .sticky{position: fixed; top:0px; left: 0; right: 0; z-index: 999999;}
    #fixed{text-align: center}
    .selected{background:lightblue;}
    .result table th{text-align: right}
    .loading{display:none; text-align: center; background:rgba(0,0,0,0.6);position:fixed; z-index:99999; top:0; right:0; bottom:0;left:0; padding-top:10%;}
    .loading span{font-size:42px; position: absolute;top:20px; right:20px}
    .loading p{color:#fff; margin:-50px auto; font-size:18px; max-width:300px; text-align:center}
    .exam-name{text-align: center;width:100%;font-weight:bold;padding-bottom:15px}
    .question-title{display: inline; font-weight:bold;float:left; padding-right:5px}
    .question-title-text{display: inline;text-align:justify}
    .question-title-main{background-color:none}
  </style>
</head>
<body>

    <!-- Main content -->
    <section class="our_courses" id="content">
      <div class="container">
        @if($exam_id)
          <div class="card" id="fixed">
            <div class="row">
              <div class="col-4">
                Questions: <b>{{$paper->questions->count()}}</b>
              </div>
              <div class="col-4">
                <div class="timer"><span id="timer">00:00</span></div>
              </div>
              <div class="col-4">
                Solved: <b id="solved">0</b>
              </div>
              <div class="clearfix"></div>
            </div>
          </div>
          <div class="row" id="questions_panel">
            <div class="banner">
              <img src="{{$paper->banner}}" alt=""/>
            </div>
            <div class="box col-md-12">
              <div class="header" style="text-align:center">{!! $paper->header !!} </div>
              <p class="exam-name">Exam No: {{$paper->name}}</p>
            </div>

            <!-- multiple questions area -->
            <div class="col-md-12 no-padding" id="questions-area">
              <!-- load all questions here -->
            </div> <!--/.col -->

            <button class="btn btn-info btn-lg pull-right" onclick="submitExam()" style="display: none" id="submit-btn">Submit</button>
          </div><!-- /.row -->
        @else
          <p class="text-center">Exam limit exited. 
            <a href="{{route('students.dashboard')}}">Go back to the Dashboard</a>
          </p>

        @endif
        
      </div>
    </section> <!-- /.content -->

    <!-- loading section -->
    <div id="loading" class="loading">
      <img src="/img/loading.webp" alt="">
      <p>আপনার রেজালটশীট ও সলুশন পেপার প্রস্তুত করা হচ্ছে। <br>অনুগ্রহ পূর্বক অপেক্ষা করুন...</p>
    </div>
    <div class="footer" id="Footer" style="background: #fff"></div>

{{-- @endsection
@section('scripts') --}}

<script src="{{'/web/assets/js/jquery-3.6.0.min.js'}}"></script>
<script src="{{'/web/assets/js/bootstrap.bundle.min.js'}}" defer></script>
<script src="{{'/web/assets/vendors/slick/slick.min.js'}}" defer></script>
<script src="{{'/web/assets/js/main.js'}}" defer></script>

<script>
  //load all questions
  let questions_area = document.getElementById('questions-area');
  function loadQuestions()
  {
    let paper_id = '{{$paper->id}}';

    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
      });

    $.ajax({
      url: '{{route("students.exam.questions", "")}}/'+paper_id,
      type: 'GET',
      cache: false,
      contentType: false,
      processData: false,
      success: function(data){
        if(data.success == true && data.questions.data.length > 0){
          // call to the data writer on the html
          writeQuestion(data);
        }
      },
      error: function(data){
        console.log(data);
      },
    });
  }

  //execute questions
  loadQuestions();

// onscroll load questions according to the pagination
  var page = 2;

  $(document).ready(function()
  {
    // see if we're at the bottom of the page to potentially load more content
    $(window).on('scroll', scrollProducts);

    function scrollProducts()
    {
      // let last_page = 3;
      let paper_id = '{{$paper->id}}';
      var end = $("#Footer").offset().top;
      var viewEnd = $(window).scrollTop() + $(window).height();
      var distance = end - viewEnd;

      // when we're almost at the bottom
      if (distance < 600){
        // unbind to prevent excessive firing
        $(window).off('scroll', scrollProducts);

        $.ajax({
          type: 'GET',
          url: '{{route("students.exam.questions", "")}}/'+paper_id+'?page=' + page,
          success: function(data) {
            // rebind after successful update
            if(data.success == true && data.questions.data.length > 0)
            {
              // call to the data writer on the html
              writeQuestion(data);
            }

            $(window).on('scroll', scrollProducts);
            page++;
          }
        });
      }
      // console.log(page);
    }
  });
  // on scroll load questions end
  
  let index = 1;
  let total_question = 0;
  // data writer on the question area
  function writeQuestion(data)
  {
    data.questions.data.forEach((e, n) => 
    {
      // console.log(data.questions.total);
      let mcqs = '';
      let display_type = '{{$paper->display}}';
      let q_display = display_type == 'One' && index != 1 ? 'hide' : '';
      let nextBtn = display_type == 'One' ?'<div class="panel-footer">'+
        '<button class="btn btn-success" onclick="showNextQuestion(this)">Next <i class="fa fa-long-arrow-right"></i></button>'+
      '</div>':'';
      
      e.mcqitems.forEach((i, n) => {
        mcqs += '<li>'+
          '<label class="">'+
            '<input onclick="answer(this)" type="radio" name="'+e.id+'" id="'+e.id+i.id+'" check="0" value="'+i.id+'"/>'+
            '<span> '+data.format[n]+' '+i.item+'</span>'+
            '</label>'+
          '</li>';
      });

      let question = document.createElement('div');
      question.setAttribute('class', 'panel panel-default '+q_display);
      question.innerHTML = '<div class="panel-heading question-title-main">'+
        '<div class="question-title">প্রশ্ন '+Number(index)+'.</div>'+
        '<div class="question-title-text">'+e.title+'</div>'+
      '</div>'+
      '<ul class="mcqitems" id="'+e.id+'">'+mcqs+'</ul>'+nextBtn;

      questions_area.append(question);
      index++;

      //enable submit button after reach to the last question
      if(data.questions.total == index && display_type != 'One')
      {
        document.getElementById('submit-btn').style.display = 'block';
      }
    });
  }

  //show next question
  function showNextQuestion(e)
  {
    let questionArea = document.getElementById('questions-area');
    e.parentNode.parentNode.classList.add('hide');
    if(e.parentNode.parentNode.nextElementSibling)
    {
      e.parentNode.parentNode.nextElementSibling.classList.remove('hide');
    }
    else
    {
      questionArea.innerHTML = '<div class="panel panel-default">'+
        '<div class="panel-body"> Exam Completed. You can Submit now! </div>'+
        '<button class="btn btn-info btn-lg pull-right" onclick="submitExam()" id="submit-btn" style="margin-top:15px">Submit</button>'+
        '</div>';
    }
  }
  
  @if($end_time)
  // Set the date we're counting down to
  // date format: "Oct 27, 2024 12:47:25";
  var countDownDate = new Date("{{$source->dtcformat($end_time)}}").getTime();
  
  // Update the count down every 1 second
  var x = setInterval(function() {
  
    // Get today's date and time
    var now = new Date().getTime();
      
    // Find the distance between now and the count down date
    var distance = countDownDate - now;
      
    // Time calculations for days, hours, minutes and seconds
    var days = Math.floor(distance / (1000 * 60 * 60 * 24));
    var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
    var seconds = Math.floor((distance % (1000 * 60)) / 1000);
      
    // Output the result in an element with id="demo"
    let d = h = m = s = '';
    if(days != 0)
    {
      d = days+"d ";
    }
    if(hours != 0)
    {
      h = hours+" : ";
    }
    if(minutes != 0)
    {
      m = minutes+" :";
    }
    if(seconds != 0)
    {
      s = seconds+" ";
    }
    // document.getElementById("timer").innerHTML = hours + "h "
    // + minutes + "m " + seconds + "s ";
    document.getElementById("timer").innerHTML = d + " " + h + " "
    + m + " " + s + " ";

    // console.log(distance);
      
    // If the count down is over, write some text 
    if (distance < 60000 && distance > 59000) {
      // alert('10 Seconds Left!');
      document.getElementById('timer').style.color='red';

    }
    if (distance < 0)
    {      
      // submit the form automatically
      submitExam();

      clearInterval(x);
      document.getElementById("timer").innerHTML = '<span style="color:#d00">সময় শেষ</span>';
      document.getElementById('questions_panel').innerHTML = '<div class="box box-warning"><a class="btn btn-warning" href="{{route("students.exam")}}">Back</div>';
    }
  }, 1000);
  @else
  alert('We are getting an issue. Please contact with service provider');
  @endif

  // navbar fixed on scroll
  window.onscroll = function(){
    let filterParent = document.getElementById('fixed');
    if(document.body.scrollTop >= 0 || document.documentElement.scrollTop >= 0)
    {
      filterParent.classList.add('sticky');
    }
    else
    {
      filterParent.classList.remove('sticky');
    }
  }

  // select mcq items
  let parentIds = [];
  function answer(e)
  {
    const parent = e.parentNode.parentNode.parentNode;
    const items = parent.children;
    
    let solved = document.getElementById('solved');
    let parenId = parent.getAttribute('id');
    if(e.getAttribute('check') == 0)
    {
      e.checked = true;
      e.setAttribute('check', 1);
      if(!parentIds.includes(parenId))
      {
        solved.innerHTML = Number(solved.innerHTML) + 1;
        parentIds.push(parenId);
      }
    }
    else
    {
      e.checked = false;
      e.setAttribute('check', 0);
      solved.innerHTML = Number(solved.innerHTML) - 1;
      parentIds.splice(parentIds.indexOf(parenId))
    }

    for(let x = 0; x < items.length; x++)
    {
      if(items[x].firstElementChild.firstElementChild.checked == true)
      {
        items[x].firstElementChild.firstElementChild.setAttribute('check', 1);
        items[x].firstElementChild.classList.add('selected');
      }
      else
      {
        items[x].firstElementChild.firstElementChild.setAttribute('check', 0);
        items[x].firstElementChild.classList.remove('selected');
      }
    }
  }

  // submit exam
  function submitExam()
  {
    let qids = [];
    let mcqids = [];
    let loading = document.getElementById('loading');
    let mcqitems = document.getElementsByClassName('mcqitems');
    for(let x = 0; x < mcqitems.length; x++)
    {
      for(let y = 0; y < mcqitems[x].children.length; y++)
      {
        if(mcqitems[x].children[y].firstElementChild.firstElementChild.checked == true)
        {
          let choice = mcqitems[x].children[y].firstElementChild.firstElementChild;
          qids.push(Number(choice.name));
          mcqids.push(Number(choice.value));
        }
      }
    }
    
    let formData = new FormData();
    formData.append('paper_id', '{{$paper->id}}');
    formData.append('exam_id', '{{$exam_id}}');
    formData.append('question_id', qids);
    formData.append('mcq_id', mcqids);
    formData.append('start_at', '{{$start_at}}');

    //storage to the local
    localStorage.setItem('formdata', formData);

    //display the loading
    loading.style.display = 'block';
    
    // push to the database
    pushToDatabase(formData);
    // console.log(qids);
  }

  let submitted = false;

  // push to the database method
  function pushToDatabase(formData)
  {
    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
      });

    $.ajax({
      url: '{{route("student.exam.add")}}',
      type: 'POST',
      data: formData,
      cache: false,
      contentType: false,
      processData: false,
      success: function(data){
        if(data.success == true)
        {
          submitted = true;
          let message = document.getElementById('message');
          let msg = 'পরীক্ষা সম্পূর্ণ হয়েছে, পরবর্তী ধাপে যাওয়ার জন্য ok করুন';

          if(data.message != null)
          {
            msg = data.message;
          }
          
          window.location.href = '{{route("students.result", [$paper->id, "after"])}}';
          loading.style.display = 'none';
        }
        else
        {
          retryPushtoDatabase(formData);
        }
      },
      error: function(data){
        // if fail to store retry to push it
        retryPushtoDatabase(formData);
        console.log(data);
      },
    });
  }

  // if fail retry to push to the database
  function retryPushtoDatabase(formData)
  {
    setTimeout(() => {
      // push to database
      pushToDatabase(formData);
      
    }, 10000);
  }

  // show result
  function showResult($data)
  {
    let content = document.getElementById('content');
  }

  //back button navigation is disabled
  history.pushState(null, null, window.location.href);
  window.onpopstate = function () {
      history.pushState(null, null, window.location.href);
      alert("Back button navigation is disabled.");
  };

  window.addEventListener('beforeunload', function (e) {
    if(submitted == false)
    {
      e.preventDefault();
      e.returnValue = ''; // For most browsers
    }
  });


  </script>
  
</body>
</html>