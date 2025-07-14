@php
use \App\Http\Controllers\SourceCtrl;
$source = new SourceCtrl;
@endphp
@extends('dashboard')
@section('title', 'Course Details')
@section('content')
  <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>Course Details</h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Courses</a></li>
        <li class="active">Details</li>
      </ol>    
    </section>

    <!-- Main content -->
  <section class="content">
    <div class="row"><!-- left column -->
      <div class="col-md-8"><!-- general form elements -->
        <div class="box box-primary">
          <div class="box-header with-border">
            <h4 class="box-title">Course Information</h4>
          </div>
          <div class="col-md-12 text-right toolbar-icon">
            <a href="{{route('course.create')}}" title="Add New" class="label label-info"><i class="fa fa-plus"></i></a>
            <a href="{{route('course.edit',$course->id)}}" class="label label-warning" title="Edit this"><i class="fa fa-edit"></i></a>
            
          </div>
          <div class="col-md-12">
            <table class="table">
              <tbody>
                <tr>
                  <th style="width: 200px;">Name:</th>
                  <td>{{$course->name}}</td>
                </tr>
                <tr>
                  <th>Batches:</th>
                  <td>
                    @if($course->batches)
                    @foreach($course->batches as $value)
                    <label class="label label-primary">{{$value->name}}</label>
                    @endforeach
                    @endif
                  </td>
                </tr>
                <tr>
                  <th>Departments:</th>
                  <td>
                    @if($course->departments)
                    @foreach($course->departments as $value)
                    <label class="label label-primary">{{$value->name}}</label>
                    @endforeach
                    @endif
                  </td>
                </tr>
                <tr>
                <tr>
                  <th>Syllabuses:</th>
                  <td>
                    @if($course->syllabuses)
                    @foreach($course->syllabuses as $value)
                    <label class="label label-info">{{$value->name}}</label>
                    @endforeach
                    @endif
                  </td>
                </tr>
                <tr>
                  <th>Papers:</th>
                  <td>
                    @if($course->papers)
                    @foreach($course->papers as $value)
                    <label class="label label-warning">{{$value->name}}</label>
                    @endforeach
                    @endif
                  </td>
                </tr>
                <tr>
                  <th>Students</th>
                  <td><b>{{$course->students->count()}}</b></td>
                </tr>
                <tr>
                  <th>Teachers:</th>
                  <td></td>
                </tr>
                <tr>
                  <th>Details:</th>
                  <td>{!!$course->details!!}</td>
                </tr>
                <tr>
                  <th>Status:</th>
                  <td>
                    <span class="label label-{{$course->status == 'Active'? 'success':'danger'}}">{{$course->status}}</span>
                  </td>
                </tr>
                <tr>
                  <th>Record Created On:</th>
                  <td>{{$source->dtformat($course->created_at)}} </td>
                </tr>
                <tr>
                  <th>Record Updated On:</th>
                  <td>{{$source->dtformat($course->updated_at)}} </td>
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
