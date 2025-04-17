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
<style>
  .checkbox{padding-left: 25px}
</style>

    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1> পরীক্ষা সমূহ {{-- <small>পরীক্ষা সমূহple 2.0</small> --}} </h1>
      <ol class="breadcrumb">
        <li><a href="/"><i class="fa fa-dashboard"></i> হোম</a></li>
        <li><a href="{{route('students.exam')}}">পরীক্ষা সমূহ</a></li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">
      <div class="row">
        <div class="col-md-12 no-padding">
          @if( !empty($student) && $student->batches()->count() )
            @foreach($student->batches()->orderBy('batches.id', 'DESC')->where('status', 'Active')->get() as $batch)
              @php
              $batch_papers = $batch->papers->whereIn('status', ['Published', 'Scheduled']);
              @endphp
              @if($batch_papers)
              <div class="col-md-12 box box-info"><h4>{{$batch->name}}</h4></div>
                @foreach($batch_papers as $key => $paper)
                  @if( $paper->permit == 'Batch' && $student->batches->find($paper->batch_id) || $paper->permit == 'Department' && $student->departments->find($paper->department_id) || $paper->permit == 'Group' && $student->groups->find($paper->group_id))
                    <div class="col-md-3">
                      <a href="{{route('students.check', $paper->id)}}">
                      <div class="panel" style="min-height: 130px">
                        <div class="panel-heading">Live Education BD</div>
                        <div class="panel-body" style="padding-top:0;font-size:22px"><b>{{substr($paper->name, 0, 30)}}...</b>
                        </div>
                        <p style="padding:0 15px">Batch: <b>{{substr( $batch->name, 0, 20)}} ...</b></p>
                        <div class="panel-footer no-padding">
                            <div class="box-group" id="accordion{{$paper->id}}">
                              <div class="p/anel bo/x box-primary">
                                <div class="box-header with-border">
                                  <div class="box-t/itle">
                                    <a data-toggle="collapse" data-parent="#accordion{{$paper->id}}" href="#dept{{$paper->id}}">
                                      বিস্তারিত দেখুন...
                                      <span class="pull-right-container">
                                        <i class="fa fa-chevron-down pull-right"></i>
                                      </span>
                                    </a>
                                  </div>
                                </div>
                                <div id="dept{{$paper->id}}" class="panel-collapse collapse">
                                  <div class="box-body">
                                    @php
                                    $latest_exam = $paper->exams()->orderBy('id', 'DESC')->where('student_id', $user->id)->first();
                                    @endphp
                                    @if($latest_exam)
                                    <a href="{{route('students.exam.paper', $latest_exam->id)}}" class="btn btn-info btn-block"><i class="fa fa-file-o"></i> আপনার এক্সাম পেপার</a>
                                    <a href="{{route('students.solution', $paper->id)}}" class="btn btn-success btn-block"><i class="fa fa-check"></i> সলূশন পেপার</a>
                                    @endif
                                    @if($paper->exams->where('student_id', $user->id)->first() && $paper->result_at < date('Y-m-d'))
                                    <a href="{{route('students.result', $paper->id)}}" class="btn btn-warning cst-btn btn-block">ফলাফল দেখুন</a>
                                    @endif
                                    @if(!$paper->exams->where('student_id', $user->id)->first())
                                    <a class="btn btn-info cst-btn btn-block" href="{{route('students.instruction', $paper->id)}}">পরীক্ষা দিন</a>
                                    @endif
                                  </div>
                                </div>
                              </div>
                            </div>
                        </div>
                      </div>
                    </a>
                  </div>
                  @else
                  {{-- <p style="color:white">Exams will publish soon!</p> --}}
                  @endif
                @endforeach
              @endif
            @endforeach
            <div class="row"></div>
          @else
            <div class="panel panel-default">
              <div class="panel-body">
                <label>আপনের কোন কোর্সে পরীক্ষা চলমান নেই।</label>
                <p><a href="{{route('students.course')}}">চলমান কোর্স সমূহ</a></p>
              </div>
            </div>
          @endif
          @if($papers)
            <div class="col-md-12 box box-info"><h4>Free Exams</h4></div>
              @foreach($papers as $paper)
                <div class="col-md-3">
                  <a href="{{route('students.check', $paper->id)}}">
                    <div class="panel" style="min-height: 130px">
                      <div class="panel-heading">Live Education BD</div>
                      <div class="panel-body" style="padding-top:0;font-size:22px"><b>{{substr($paper->name, 0, 20) }}</b></div>
                      <div class="panel-footer">
                        For: <b>{{$paper->permit}}<b>
                      </div>
                    </div>
                  </a>
                </div>
              @endforeach
            </div>
          @else
          <p>Free exam not available!</p>
          @endif
        </div>
      </div>
    </section> <!-- /.content -->

<script>
  function showPassword(e)
  {
    let elm = e.previousElementSibling;
    if(elm.type == 'password')
    {
      elm.setAttribute('type', 'text');
      e.firstChild.classList.add('fa-eye');
      e.firstChild.classList.remove('fa-eye-slash');
    }
    else 
    {
      elm.setAttribute('type', 'password');
      e.firstChild.classList.add('fa-eye-slash');
      e.firstChild.classList.remove('fa-eye');
    }
  }
</script>
@endsection