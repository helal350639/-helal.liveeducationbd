@php
use \App\Http\Controllers\SourceCtrl;
$source = New SourceCtrl;
$user = Auth::guard('student')->user();
$student = [];
if($user)
{
  $student = \App\Models\Student::find($user->id);
}
@endphp

@extends('student')
@section('title', 'পরীক্ষা সমূহ')
@section('content')
<link rel="stylesheet" href="{{'/web/css/item-list.css'}}" />
<style>
/* Custom CSS for mobile devices */
@media (max-width: 575.98px) {

  .video-container h5 {
    font-size: 14px; /* Smaller font size for mobile */
    text-align: center;
  }

  .playButton svg {
    width: 30px; /* Smaller play button for mobile */
    height: 30px;
  }
}

/* Custom CSS for tablets and larger devices */
@media (min-width: 576px) {

  .video-container h5 {
      font-size: 18px;
      text-align: center;
  }

  .playButton svg {
      width: 45px; /* Larger play button for tablets and desktops */
      height: 45px;
  }
}
</style>

    <!-- Main content -->
    <section class="our_courses">
      <div class="container">
        <div class="row">
          <!-- Content Header (Page header) -->
          <div class="title-area text-center">
            <h1> {{$batch->name}} </h1>
          </div>
        </div>

        <div class="row">
          <div class="col-md-12 no-padding">
            <div class="all-courses-area">
              <div class="row justify-content-center g-3">

                @foreach($papers as $key => $paper)
                  @if( !empty($student) && $student->batches()->count() )

                
                    @if( $paper->permit == 'Batch' && $student->batches->find($paper->batch_id) || $paper->permit == 'Department' && $student->departments->find($paper->department_id))

                    <div class="col-lg-8 col-md-10 col-sm-12">
                      <div class="border accordion-body-content d-flex justify-content-between align-items-center flex-wrap p-3 py-2">
                        <div class="text d-flex align-items-center list-content">
                          <div class="course_exam-icon">
                            <div class="circular-progress" title="Provided Answer" data-inner-circle-color="#def3ff" data-percentage="6" data-progress-color="rgb(137 24 26)" data-bg-color="rgb(137 24 26 / 70%)" style="background: #fff; font-size:22px; border:1px solid #ddd; color:#1CAB55">
                              <i class="fa fa-lock"></i>
                            </div>
                          </div>
                          <p class="exam-title ps-1" style="width: 100%">
                            <span>{{ $paper->name }}</span>
                            <span style="float:right;">{{ $source->dformat($paper->open) }} </span>
                            <br>
                            <span style="font-size: 12px; font-weight: 700; color: #1CAB55;"> (প্রশ্ন -{{$paper->questions()->count()}}টি, সময়-{{$paper->time}}মিনিট) </span>
                          </p>
                        </div>
                        <div class="v-button d-flex flex-wrap gap-2">
                          <a href="{{route('students.instruction', $paper->id)}}" class="btn btn-sm btn-warning"> পরীক্ষা দিন </a>
                          <a href="{{route('students.result', $paper->id)}}" class="btn btn-sm btn-success"> উত্তর</a>
                          <a href="#" class="btn btn-sm btn-info text-white"> মেধাক্রম </a>
                          @if($paper->close < date('Y-m-d H:i:s'))
                          <p></p>
                          @endif
                        </div>
                      </div>
                    </div>

                    

                    @else
                      <p style="color:white">Exams will publish soon!</p>
                    @endif
                
                  @else
                    <div class="card card-default">
                      <div class="card-body">
                        <label>আপনের কোন কোর্সে পরীক্ষা চলমান নেই।</label>
                        <p><a href="{{route('students.course')}}">চলমান কোর্স সমূহ</a></p>
                      </div>
                    </div>
                  @endif

                @endforeach

                {{-- @if( !empty($student) && $student->batches()->count() ) --}}
                  @foreach($student->batches()->orderBy('batches.id', 'DESC')->where('status', 'Active')->get() as $key => $batch)

                    @php
                    $batch_papers = $batch->papers()->whereIn('status', ['Published', 'Scheduled'])->orderBy('id', 'DESC')->get();
                    @endphp

                    @if($batch_papers)
                    
                    
                    <div class="accordion-item mt-3 md-3">

                      <div id="batch{{$key}}" class="accordion-collapse collapse show">
                        <div class="row justify-content-center p-3">

                          @foreach($batch_papers as $key => $paper)

                            

                          
                            <!--
                              <div class="col-md-3">
                                <a href="{{route('students.check', $paper->id)}}">
                                  <div class="card" style="min-height: 130px">
                                    <div class="card-body p-3" style="padding-top:0;font-size:22px">
                                      <h4>{{substr($paper->name, 0, 30)}}</h4>
                                    </div>
                                    <p style="padding:0 15px"><b>{{substr( $batch->name, 0, 18)}}</b></p>
                                    <div class="d-grid gap-2 card-footer">
                                      @php
                                      $latest_exam = $paper->exams()->orderBy('id', 'DESC')->where('student_id', $user->id)->first();
                                      @endphp
            
                                      @if($latest_exam)
                                      <a href="{{route('students.exam.paper', $latest_exam->id)}}" class="btn btn-info btn-block"><i class="fa fa-file-o"></i> আপনার এক্সাম পেপার</a>
                                      @else
                                      <a href="#" class="btn btn-block btn-disable">No Exam Paper</a>
                                      @endif
            
                                      @if($latest_exam || $paper->close < date('Y-m-d H:i:s'))
                                      <a href="{{route('students.solution', $paper->id)}}" class="btn btn-success btn-block"><i class="fa fa-check"></i> সলূশন পেপার</a>
                                      @else
                                      <a href="#" class="btn btn-block btn-disable">No Solution Paper</a>
                                      @endif
            
                                      @if($paper->exams->where('student_id', $user->id)->first())
                                      <a href="{{route('students.result', $paper->id)}}" class="btn btn-warning cst-btn btn-block">ফলাফল দেখুন</a>
                                      @else
                                      {{-- <a href="#" class="btn btn-default btn-block">No Result</a>
                                      @endif
            
                                      @if(!$paper->exams->where('student_id', $user->id)->first()) --}}
                                      <a class="btn btn-info cst-btn btn-block" href="{{route('students.instruction', $paper->id)}}">পরীক্ষা দিন</a>
                                      @endif
                                    </div>
                                  </div>
                                </a>
                              </div>
                            -->
                          @endforeach
                        </div>
                      </div>
                      
                    </div>
                    @endif
                  @endforeach
                  <div class="row"></div>
                
                {{-- @if($papers)
                <div class="accordion-item mt-3">
                  <div class="accordion-header">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#free-exam" aria-expanded="true" aria-controls="free-exam">
                      Free Exams
                    </button>
                  </div>
                  <div id="free-exam" class="accordion-collapse collapse show p-3">
                    @foreach($papers as $paper)
                      <div class="col-md-3">
                        <a href="{{route('students.check', $paper->id)}}">
                          <div class="card" style="min-height: 130px">
                            <div class="card-heading p-3">
                              <h4>{{substr($paper->name, 0, 20) }}</h4>
                            </div>
                            <div class="card-body" style="padding-top:0;">
                              
                              For: <b>{{$paper->permit}}<b>
                            </div>
                            <div class="card-footer d-grid gap-2">
                              @php
                              $latest_exam = $paper->exams()->orderBy('id', 'DESC')->where('student_id', $user->id)->first();
                              @endphp
                              @if($latest_exam)
                              <a href="{{route('students.exam.paper', $latest_exam->id)}}" class="btn btn-info btn-block"><i class="fa fa-file-o"></i> আপনার এক্সাম পেপার</a>
                              <a href="{{route('students.solution', $paper->id)}}" class="btn btn-success btn-block"><i class="fa fa-check"></i> সলূশন পেপার</a>
                              @else
                              <a href="#" class="btn btn-block btn-disable">No Exam Paper</a>
                              <a href="#" class="btn btn-block btn-disable">No Solution Paper</a>
                              @endif
                              @if($paper->exams->where('student_id', $user->id)->first() && $paper->result_at < date('Y-m-d'))
                              <a href="{{route('students.result', $paper->id)}}" class="btn btn-warning cst-btn btn-block">ফলাফল দেখুন</a>
                              @endif
                              @if(!$paper->exams->where('student_id', $user->id)->first())
                              <a class="btn btn-info cst-btn btn-block" href="{{route('students.instruction', $paper->id)}}">পরীক্ষা দিন</a>
                              @endif
                            </div>
                          </div>
                        </a>
                      </div>
                    @endforeach
                  </div>

                </div>
                  </div>
                @else
                <p>Free exam not available!</p>
                @endif --}}
              </div>
            </div>
          </div>
        </div> <!-- /.row -->
      </div> <!-- /.container -->
    </section> <!-- /.our_courses -->
@endsection