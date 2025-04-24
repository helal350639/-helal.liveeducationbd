@php
use \App\Http\Controllers\SourceCtrl;
$source = New SourceCtrl;
$value = $paper;
@endphp

@extends('dashboard')
@section('title', 'Question Paper Details')
@section('content')
  <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>Question Paper Details</h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Question Papers</a></li>
        <li class="active">Details</li>
      </ol>    
    </section>

    <!-- Main content -->
  <section class="content">
    <div class="row"><!-- left column -->
      <div class="col-md-8"><!-- general form elements -->
        <div class="box box-primary">
          <div class="box-header with-border">
            <h4 class="box-title">Question Paper Information[ <a>{{route('students.instruction', $paper->id)}}</a> ]</h4>
          </div>
          <div class="col-md-12 text-right toolbar-icon">

            <button id="text" title="Copy URL" class="btn btn-default btn-sm" onclick="handleClick();" href="{{route('students.instruction', $paper->id)}}"><i class="fa fa-copy"></i> Copy URL </button>

            <a href="{{route('paper.add.question', $paper->id)}}" title="Add Questions" class="label label-info"><i class="fa fa-plus"></i> Add Questions</a>

            <a href="{{route('paper.create')}}" title="Add" class="label label-primary"><i class="fa fa-pencil"></i> Create</a>

            <a href="{{route('paper.copy', $paper->id)}}" title="Make a Copy" class="label label-default"><i class="fa fa-copy"></i> Copy</a>

            <a href="{{route('paper.solution', $paper->id)}}" title="Solution" class="label label-info"><i class="fa fa-file-o"> Solution</i></a>

            <a href="{{route('paper.exam', $paper->id)}}" title="Exam" class="label label-primary"><i class="fa fa-list"> Exams</i></a>

            <a href="{{route('paper.result', $paper->id)}}" title="Result" class="label label-warning"><i class="fa fa-list-o"> Result</i></a>

            <a href="{{route('paper.create')}}" title="Add New" class="label label-info"><i class="fa fa-plus"></i></a>

            <a href="{{route('paper.view', $value->id)}}" title="View" class="label label-primary"><i class="fa fa-th"></i></a>

            <a href="{{route('paper.index')}}" title="View" class="label label-success"><i class="fa fa-list"></i></a>

            <a href="{{route('paper.edit', $value->id)}}" class="label label-warning" title="Edit this"><i class="fa fa-gear"></i></a>

            <form action="{{route('paper.destroy', $value->id)}}" method="post" style="display: inline">
              @csrf
              @method('DELETE')
              <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this question paper?')" title="Delete"><i class="fa fa-trash"></i></button>
            </form>
          </div>
          <div class="col-md-12">
            <table class="table">
              <tbody>
                <tr>
                  <th style="width: 200px;">Question Paper No.:</th>
                  <td>{{$value->name}}</td>
                </tr>
                <tr>
                  <th>Course:</th>
                  <td>{{$value->course ? $value->course->name : ''}}</td>
                </tr>
                <tr>
                  <th>Batch:</th>
                  <td>{{$value->batch ? $value->batch->name : ''}}</td>
                </tr>
                <tr>
                  <th>Department:</th>
                  <td>{{$value->department ? $value->department->name : ''}}</td>
                </tr>
                <tr>
                <tr>
                  <th>Banner:</th>
                  <td><img src="{{$value->banner}}" alt="" style="width: 100%"></td>
                </tr>
                <tr>
                  <th>Header:</th>
                  <td>{!!$value->header !!}</td>
                </tr>
                <tr>
                  <th>Details:</th>
                  <td>{{$value->details}}</td>
                </tr>
                <tr>
                  <th>Max Questions Entry:</th>
                  <td>{{$value->max}}</td>
                </tr>
                <tr>
                  <th>Question Count:</th>
                  <td>{{count($value->questions->select('id'))}}</td>
                </tr>
                <tr>
                  <th>Status:</th>
                  <td>
                    <span class="label label-warning">{{$value->status}}</span>
                  </td>
                </tr>
                <tr>
                  <th>Exam Start At:</th>
                  <td>{{$source->dtformat($value->open)}}</td>
                </tr>
                <tr>
                  <th>Exma Finish At:</th>
                  <td>{{$source->dtformat($value->close)}}</td>
                </tr>
                <tr>
                  <th>List Format:</th>
                  <td>{{$value->format}}</td>
                </tr>
                <tr>
                  <th>Time (in Minutes):</th>
                  <td>{{$value->time}} </td>
                </tr>
                <tr>
                  <th>Mark (For Correct Answer):</th>
                  <td>{{$value->mark}} </td>
                </tr>
                <tr>
                  <th>Mark (Negative for wrong Answer):</th>
                  <td>{{$value->minus}} </td>
                </tr>
                <tr>
                  <th>Student Can View Result After Exam?:</th>
                  <td>{{$value->result_view}} </td>
                </tr>
                <tr>
                  <th>Result Publish At:</th>
                  <td>{{$source->dtformat($value->result_at)}} <br> DataBase date: {{$value->result_at}}<br> Current Date: &nbsp; &nbsp;  {{date('Y-m-d H:i:s')}}</td>
                </tr>
                <tr>
                  <th>How many times can a student take the exam?:</th>
                  <td>{{$value->exam_limit}} </td>
                </tr>
                <tr>
                  <th>Show Random Questions?:</th>
                  <td>{{$value->random}} </td>
                </tr>
                <tr>
                  <th>Display Question?:</th>
                  <td>{{$value->display}} </td>
                </tr>
                <tr>
                  <th>Who can exam?:</th>
                  <td>{{$value->permit}} </td>
                </tr>
                <tr>
                  <th>Record Created On:</th>
                  <td>{{$source->dtformat($value->created_at)}} </td>
                </tr>
                <tr>
                  <th>Record Updated On:</th>
                  <td>{{$source->dtformat($value->updated_at)}} </td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="clearfix"></div>
        </div>
      </div><!-- /.box -->
    </div><!--/.col (left) -->
  </section><!-- /.content -->
   
@endsection
@section('scripts')
<script>
  function copyToClipBoard(str){
    const element = document.createElement('textarea');
    element.value = str;
    document.body.appendChild(element);
    element.select();
    document.execCommand('copy');
    document.body.removeChild(element);
    alert('Link Copied to the clipboard');
  }

  function handleClick(){
    let text = document.querySelector('#text');
    copyToClipBoard(text.getAttribute('href'));
  }
</script>
@endsection
