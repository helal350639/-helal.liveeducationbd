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

    <!-- Main content -->
    <section class="our_courses ">
      <div class="container">
        <div class="row">
          <!-- Content Header (Page header) -->
          <div class="content-header">
            <h1> আমার সিলেবাস সমূহ </h1>
          </div>
        </div>
        <div class="row">
          <div class="col-md-12 card p-3 md-3">
            <form action="{{route('students.my-syllabus.post')}}" method="post">
              @csrf
              <div class="row">
                <div class="col-md-8">
                  <div class="form-group">
                    <label for="">Select Batch</label>
                    <select name="batch_id" id="batch_id" class="form-control" required>
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
        </div>
        <div class="row">
          <div class="card p-3 mt-3 col-md-12 no-padding">
            @if(!empty($department_syllabuses))
            <h4> &nbsp; ডিপার্টমেন্ট এর সিলেবাস সমূহ</h4>
            <table class="table">

              @foreach($department_syllabuses as $syllabus)
              <tr>
                <th>{{$syllabus->name}}</th>
                <td>
                  @if($syllabus->pdf)

                    <a href="{{$syllabus->pdf}}" class="btn btn-info"><i class="fa fa-download"></i> ডাউনলোড</a>

                  @else

                  <a class="btn btn-default" href="{{route('student.syllabus', $syllabus->id)}}"><i class="fa fa-eye"></i> View</a>
                    
                  <a href="{{route('students.syllabus.pdf', $syllabus->id)}}" class="btn btn-info"><i class="fa fa-download"></i> ডাউনলোড</a>
                  @endif
                </td>
              </tr>
            @endforeach
          </table>
          <div class="row"></div>
        @else
        <div class=" card-default">
          <div class="card-body">
            <label>ব্যাচ নির্বাচন করে সাবমিট করুন</label>
            <p><a href="{{route('students.course')}}">চলমান কোর্স সমূহ</a></p>
          </div>
        </div>
        @endif
      
        @if( !empty($batch_syllabuses) )
        <h4> &nbsp; ব্যাচের সিলেবাস সমূহ </h4>
        <table class="table">
          @foreach($batch_syllabuses as $syllabus)
          <tr>
            <th>{{$syllabus->name}}</th>
            <td>
              @if($syllabus->pdf)
                
              <a href="{{$syllabus->pdf}}" class="btn btn-info"><i class="fa fa-download"></i> ডাউনলোড</a>

              @else

              <a class="btn btn-default" href="{{route('student.syllabus', $syllabus->id)}}"><i class="fa fa-eye"></i> View</a>
                
              <a href="{{route('students.syllabus.pdf', $syllabus->id)}}" class="btn btn-info"><i class="fa fa-download"></i> ডাউনলোড</a>

              @endif
            </td>
          </tr>
          @endforeach
        </table>
        @else
        <div class="card-default">
          <div class="card-body">
            {{-- <label>সিলেবাস প্রকাশিত হয়নি।</label> --}}
          </div>
        </div>
        @endif
      </div>
    </div>
  </section> <!-- /.content -->
@endsection