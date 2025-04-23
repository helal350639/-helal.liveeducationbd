@php
use \App\Http\Controllers\SourceCtrl;
$source = New SourceCtrl;
$value = $mcq;
$correct_answer = '';
@endphp

@extends('dashboard')
@section('title', 'Question Details')
@section('content')
  <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>Question Details</h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Questions</a></li>
        <li class="active">Details</li>
      </ol>    
    </section>

    <!-- Main content -->
  <section class="content">
    <div class="row"><!-- left column -->
      <div class="col-md-8"><!-- general form elements -->
        <div class="box box-primary">
          <div class="box-header with-border">
            <h4 class="box-title">Question Information</h4>
          </div>
          <div class="col-md-12 text-right toolbar-icon">
            <a href="{{route('question.create')}}" title="Add New" class="label label-info"><i class="fa fa-plus"></i></a>
            <a href="{{route('question.index')}}" title="List" class="label label-success"><i class="fa fa-list"></i></a>
            <a href="{{route('question.view')}}" title="View" class="label label-primary"><i class="fa fa-th-large"></i></a>
            <a href="{{route('question.edit', $value->id)}}" class="label label-warning" title="Edit this"><i class="fa fa-edit"></i></a>
            
          </div>
          <div class="col-md-12">
            <table class="table">
                <tbody>
                <tr>
                    <th>Course Name:</th>
                    <td>
                      @foreach($mcq->courses as $val)
                      <label class="label label-primary"> {{$val->name}}</label>
                      @endforeach
                    </td>
                  </tr>
                <tr>
                    <th>Department Name:</th>
                    <td>
                      @foreach($mcq->departments as $val)
                      <label class="label label-primary"> {{$val->name}}</label>
                      @endforeach
                    </td>
                  </tr>
                  <tr>
                    <th>Semester Name:</th>
                    <td>
                      @foreach($mcq->semesters as $val)
                      <label class="label label-primary"> {{$val->name}}</label>
                      @endforeach
                    </td>
                  </tr>
                  <tr>
                    <th>Subject Name:</th>
                    <td>
                      @foreach($mcq->subjects as $val)
                      <label class="label label-primary"> {{$val->name}}</label>
                      @endforeach
                    </td>
                  </tr>
                  <tr>
                  <tr>
                      <th>Chapter Name:</th>
                      <td>
                        @foreach($mcq->chapters as $val)
                        <label class="label label-primary"> {{$val->name}}</label>
                        @endforeach
                      </td>
                    </tr>
                  <tr>
                  <tr>
                    <th style="width: 200px;">Question Title:</th>
                    <td><label style="font-size:16px">{{$value->title}}</label> <br><img src="{{$value->image}}" alt="" width="150"></td>
                  </tr>
                  @if($mcq->type == 'Written')
                  <tr>
                    <th>Answer Files</th>
                    <td>
                      @foreach($mcq->answerfiles as $val)
                      @if(substr($val->file, -3) == 'pdf')
                      <a target="_blank" href="{{$val->file}}"><i class="fa fa-file"></i></a>
                      @else
                      <img width="100" src="{{$val->file}}" alt="">
                      @endif
                      @endforeach
                    </td>
                  </tr>
                  <tr>
                    @endif
                      <th>Labels:</th>
                      <td>
                        <p>
                          @foreach($value->getlabels as $lab)
                          <label for="" class="label label-info">{{$lab->label}}</label>
                          @endforeach
                        </p></td>
                    </tr>
                  <tr>
                  @if($value->type == 'MCQ')
                  <tr>
                    <th>MCQ Items:</th>
                    <td>
                      @foreach($value->getitems as $val)
                        @if($val->correct_answer == true)
                          @php
                          $correct_answer = $val->item;
                          @endphp
                        @endif
                      <label for="">{{$val->item}}</label> <br><img src="{{$val->image}}" alt="" width="150"><br>
                      @endforeach
                    </td>
                  </tr>
                  <tr>
                    <th>Correct Answer:</th>
                    <td><label class="text-info" for="">{{$correct_answer}}</label></td>
                  </tr>
                  @elseif($value->type == 'Written')
                  <tr>
                    <th>Answer:</th>
                    <td> {{$value->answer}}</td>
                  </tr>
                  @else
                  <tr>
                    <th>Video:</th>
                    <td>{!! $value->video !!}</td>
                  </tr>
                  @endif
                  <tr>
                    <th>Explanation:</th>
                    <td>{{$value->explanation}}</td>
                  </tr>
                  <tr>
                    <th>Filters:</th>
                    <td>
                      @foreach($value->filters as $val)
                        <label for="" class="label label-primary">{{$val->name}}</label>
                      @endforeach
                    </td>
                  </tr>                
                   <tr>
                    <th>Status:</th>
                    <td>
                      @if($value->status == 'Active')
                      <span class="label label-success">Active</span>
                      @elseif($value->status == 'Inactive')
                      <span class="label label-warning">Inactive</span>
                      @endif
                    </td>
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
