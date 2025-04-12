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
      <h1> আমার সিলেবাস সমূহ {{-- <small>সিলেবাস সমূহple 2.0</small> --}} </h1>
      <ol class="breadcrumb">
        <li><a href="/"><i class="fa fa-dashboard"></i> হোম</a></li>
        <li><a href="{{route('students.exam')}}">সিলেবাস সমূহ</a></li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">
      <div class="box col-md-12">
          <form action="{{route('students.my-syllabus.post')}}" method="post">
            @csrf
            <div class="row">
              <div class="col-md-8">
                <div class="form-group">
                  <label for="">Select Batch</label>
                  <select name="batch_id" id="batch_id" class="form-control">
                    <option value="">Select One</option>
                    @foreach($batches as $value)
                      <option value="{{$value->id}}" {{!empty($batch) && $batch->id == $value->id ? 'selected':''}}>{{$value->name}}</option>
                    @endforeach
                  </select>
                </div>
              </div>
              <div class="col-md-4">
                <br>
                <button class="btn btn-info btn-block">Submit</button>
              </div>
            </div>
          </form>
      </div>
      <div class="ro/w">
        <div class="box col-md-12 no-padding">
          @if( !empty($syllabuses) )
          <h4> &nbsp; সিলেবাস সমূহ</h4>
          <table class="table">
            @php
            $batch_syllabus = $batch->syllabuses()->where('department_id', null)->get();
            @endphp

            @if($batch_syllabus)
            @foreach($batch_syllabus as $syllabus)
            <tr>
              <th>{{$syllabus->name}}</th>
              <td>
                @if($syllabus->pdf)

                <a class="btn btn-default" href="{{$syllabus->pdf}}"><i class="fa fa-eye"></i> View</a>
                  
                <a href="{{$syllabus->pdf}}" class="btn btn-info"><i class="fa fa-download"></i> ডাউনলোড</a>

                @else

                <a class="btn btn-default" href="{{route('student.syllabus', $syllabus->id)}}"><i class="fa fa-eye"></i> View</a>
                  
                <a href="{{route('students.syllabus.pdf', $syllabus->id)}}" class="btn btn-info"><i class="fa fa-download"></i> ডাউনলোড</a>

                @endif
              </td>
            </tr>
            @endforeach
            @endif
            @foreach($syllabuses as $syllabus)
            <tr>
              <th>{{$syllabus->name}}</th>
              <td>
                <a class="btn btn-default" href="{{route('student.syllabus', $syllabus->id)}}"><i class="fa fa-eye"></i> View</a>
                  
                <a href="{{route('students.syllabus.pdf', $syllabus->id)}}" class="btn btn-info"><i class="fa fa-download"></i> ডাউনলোড</a>
              </td>
            </tr>
          @endforeach
        </table>
        <div class="row"></div>
      @else
      <div class="panel panel-default">
        <div class="panel-body">
          <label>আপনের কোন কোর্সে সিলেবাস প্রকাশিত হয়নি।</label>
          <p><a href="{{route('students.course')}}">চলমান কোর্স সমূহ</a></p>
        </div>
      </div>
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