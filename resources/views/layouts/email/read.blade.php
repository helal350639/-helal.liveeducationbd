@php
use \App\Http\Controllers\SourceCtrl;
$source = New SourceCtrl;

function batch($ids)
{
  $batches = \App\Models\Batch::whereIn('id', json_decode($ids))->pluck('name')->toArray();
  return implode(' || ', $batches);
}

function student($ids)
{
  $students = \App\Models\Student::whereIn('id', json_decode($ids))->pluck('name')->toArray();
  return implode(' || ', $students);
}
$value = $email;
@endphp


@extends('dashboard')
@section('title', 'Email Details')
@section('content')
  <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>Email Details</h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Emails</a></li>
        <li class="active">Details</li>
      </ol>    
    </section>

    <!-- Main content -->
  <section class="content">
    <div class="row"><!-- left column -->
      <div class="col-md-8"><!-- general form elements -->
        <div class="box box-primary">
          <div class="box-header with-border">
            <h4 class="box-title">Email Information</h4>
          </div>
          <div class="box-body">
            <div class="col-md-12 text-right toolbar-icon">
              <a href="{{route('email.create')}}" title="Add New" class="label label-info"><i class="fa fa-plus"></i></a>
              <a href="{{route('email.edit', $email->id)}}" class="label label-warning" title="Edit this"><i class="fa fa-edit"></i></a>
            </div>
            <div class="col-md-12">
              <table class="table">
                <tbody>
                  <tr>
                    <th style="width: 200px;">Title:</th>
                    <td>{{$value->title}}</td>
                  </tr>
                  <tr>
                    <th>Details:</th>
                    <td>{!!$value->details!!}</td>
                  </tr>
                  <tr>
                    <th>Batches:</th>
                    <td>{{batch($value->batch_id)}}</td>
                  </tr>
                  <tr>
                    <th>Students:</th>
                    <td>{{student($value->student_id)}}</td>
                  </tr>
                  <tr>
                    <th>Status:</th>
                    <td>
                      @if($value->status == 'Sent')
                      <span class="label label-success">{{$value->status}}</span>
                      @elseif($value->status == 'Scheduled')
                      <span class="label label-info">{{$value->status}}</span>
                      <br>
                      <small>{{$source->dtformat($value->datetime)}}</small>
                      @else
                      <span class="label label-warning">{{$value->status}}</span>
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
          </div>

        </div><!-- /.box -->
      </div><!-- /.col -->
    </div><!--/.row -->
  </section><!-- /.content -->
   
@endsection
