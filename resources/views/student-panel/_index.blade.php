@php
$student  = [];
$user = Auth::guard('student')->user();
if($user)
{
  $student = \App\Models\Student::find($user->id);
}
@endphp
@extends('student')
@section('title', 'হোম')
@section('content')
{{-- <script src='https://www.google.com/recaptcha/api.js' async defer></script> --}}
<style>
  .checkbox{padding-left: 25px}
  .box-header{margin-bottom:15px}
  .box-header .box-title{text-align: center; display: block}
  .course-image{width:100%}
  .hover{border:2px solid #ddd}
  .hover:hover{border-color: #080}
  .about{font-size: 15px}
  .about p{text-align: justify}
</style>

    <!-- Main content -->
    <section class="content">
      <div class="row-fluid">
        <div class="hero" style="background: url(/img/online-education.jpg) 100%; min-height:200px">
          
        </div>
        {{-- <img src="" alt="" style="width:100%"> --}}
        <br>
        <div class="box box-info">
          <div class="box-header with-border">
            <h3 class="box-title">About Us - Live Education BD</h3>
          </div>
          <div class="box-body about">
            Welcome to <b>Live Education BD</b>, a leading online education platform designed to empower students and job seekers in Bangladesh. Our mission is to provide high-quality educational resources, structured learning paths, and effective exam preparation to help students achieve their academic and career goals.
            <p>We specialize in <b>polytechnic education</b> and <b>job admission preparation</b>, offering a wide range of courses tailored to meet the needs of aspiring professionals. Our platform provides:</p>
            <p><i class="fa fa-check"></i> <b>Course Sales</b> – Access premium courses curated by expert educators to enhance your knowledge and skills.</p>
            <p><i class="fa fa-check"></i> <b>Online Study</b> – Learn at your own pace with interactive materials, video lectures, and structured syllabi.</p>
            <p><i class="fa fa-check"></i> <b>Syllabus & Exam Preparation</b> – Get a well-organized syllabus and practice exams that align with your academic and competitive exam requirements.</p>
            <p><i class="fa fa-check"></i> <b>Results & Performance Tracking</b> – Analyze your progress with real-time results and detailed performance reports.</p>
            <p>At <b>Live Education BD</b>, we are committed to making education accessible, affordable, and effective for every learner. Whether you're a <b>polytechnic student</b> looking for advanced learning materials or a <b>job candidate</b> preparing for competitive exams, our platform is designed to support your journey to success.</p>
            <p>Join us today and take a step closer to achieving your dreams!</p>
          </div>
        </div>
      </div>
      <div class="box box-info">
        <div class="box-header with-border">
          <h3 class="box-title">কোর্স সমূহ</h3>
        </div>
      </div> <!-- /.box -->
      <div class="row" style="margin-bottom:35px;">
        @foreach($batches as $value)
        <div class="col-md-3">
          <a href="{{route('home.course.show', $value->id)}}">
          <div class="panel panel-default">
            <div class="penel-heading hover no-padding" style="text-align: center; padding: 15px; min-height:150px">
                <img class="course-image" src="{{ $value->banner? $value->banner : '/img/course.png'}}" alt="" />
            </div>
          </div>
        </a>
        </div>
        @endforeach
      </div> <!-- /.row -->

      @if(!empty($user) && count($mybatches))      
      <div class="box box-warning">
        <div class="box-header with-border">
          <h3 class="box-title">আমার কোর্স</h3>
        </div>
      </div> <!-- /.box -->
      <div class="row" style="margin-bottom:35px">
        @foreach($mybatches as $value)
        <div class="col-md-3">
          <a href="{{route('students.course.show', $value->id)}}">
          <div class="panel panel-default">
            <div class="penel-heading no-padding" style="text-align: center; padding:15px">
              <img class="course-image" src="{{ $value->banner? $value->banner : '/img/course.png'}}" alt="" />
            </div>
            <div class="panel-heading"><b>{{substr($value->name, 0, 30)}} ...</b></div>
          </div>
          </a>
        </div>
        @endforeach
      </div> <!-- /.row -->
      @endif

      @if(!empty($student) && count($student->batches()->get()))
      <div class="box box-danger">
        <div class="box-header with-border">
          <h3 class="box-title">পরীক্ষা</h3>
        </div>
      </div> <!-- /.box -->
      <div class="row" style="margin-bottom:35px">
        @foreach($student->batches()->get() as $course)
        @if($course->paper)
        @php
        $paper = $course->paper;
        @endphp
          @if( $paper->permit == 'Batch' && $student->batches->find($paper->batch_id) || $paper->permit == 'Department' && $student->departments->find($paper->department_id) || $paper->permit == 'Group' && $student->groups->find($paper->group_id))
          <div class="col-md-3">
            <a class="" href="{{route('students.check', $paper->id)}}">
            <div class="panel">
              <div class="panel-heading">Live Education BD</div>
              <div class="panel-body" style="padding-top:0;font-size:22px"><b>{{$paper->name}}</b></div>
              <div class="panel-footer">
                Batch: <b>{{substr($course->name, 0, 30)}} ...<b>
              </div>
            </div>
          </a>
          </div>
          @endif
        @endif
        @endforeach
        @if($papers)
        @foreach($papers as $paper)
        <div class="col-md-3">
          <a class="" href="{{route('students.check', $paper->id)}}">
          <div class="panel">
            <div class="panel-heading">Live Education BD</div>
            <div class="panel-body" style="padding-top:0;font-size:22px"><b>{{$paper->name}}</b></div>
            <div class="panel-footer">
              For: <b>{{$paper->permit}}<b>
            </div>
          </div>
        </a>
        </div>
        @endforeach
        @endif
      </div> <!-- /.row -->
      @endif
      
      <div class="box box-primary">
        <div class="box-header with-border">
          <h3 class="box-title">বুক স্টোরেজ</h3>
        </div>
      {{-- </div> <!-- /.box -->
      <div class="box" style="margin-bottom:35px"> --}}
        <div class="col-md-12">
          <h3>Coming soon!</h3>
        </div>
        <div class="clearfix"></div>
      </div>
      
      <div class="box box-primary">
        <div class="box-header with-border">
          <h3 class="box-title">আপডেট তথ্য</h3>
        </div>
      {{-- </div> <!-- /.box -->
      <div class="box" style="margin-bottom:35px"> --}}
        <div class="col-md-12">
          <h3>Coming soon!</h3>
        </div>
        <div class="clearfix"></div>
      </div>

      <div class="box box-primary">
        <div class="box-header with-border">
          <h3 class="box-title">নিয়োগ বিজ্ঞপ্তি</h3>
        </div>
      {{-- </div> <!-- /.box -->
      <div class="box" style="margin-bottom:35px"> --}}
        <div class="col-md-12">
          <h3>Coming soon!</h3>
        </div>
        <div class="clearfix"></div>
      </div>
    </section> <!-- /.content -->
@endsection