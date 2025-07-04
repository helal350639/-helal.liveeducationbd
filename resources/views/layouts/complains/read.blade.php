@php
use \App\Http\Controllers\SourceCtrl;
$source = New SourceCtrl;
$value = $complain;
@endphp

@extends('dashboard')
@section('title', 'Complain Details')
@section('content')
  <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>Complain Details</h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Complains</a></li>
        <li class="active">Details</li>
      </ol>    
    </section>

    <!-- Main content -->
  <section class="content">
    <div class="row"><!-- left column -->
      <div class="col-md-8"><!-- general form elements -->
        <div class="box box-primary">
          <div class="box-header with-border">
            <h4 class="box-title">Complain Information</h4>
          </div>
          <div class="col-md-12 text-right toolbar-icon">
            {{-- <a href="{{route('complain.create')}}" title="Add New" class="label label-info"><i class="fa fa-plus"></i></a> --}}
            {{-- <a href="{{route('complain.edit', $value->id)}}" class="label label-warning" title="Edit this"><i class="fa fa-edit"></i></a> --}}
            <a href="{{route('complain.index')}}" class="label label-success" title="View List"><i class="fa fa-list"></i></a>
          </div>
          <div class="col-md-12">
            <table class="table">
                <tbody>
                  <tr>
                    <th style="width: 200px;">Name:</th>
                    <td>{{$value->name}}</td>
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
                    <th>Details:</th>
                    <td>{!! $value->details !!}</td>
                  </tr>
                  <tr>
                    <th>Solution:</th>
                    <td>{!! $value->solution !!}</td>
                  </tr>
                
                   <tr>
                    <th>Status:</th>
                    <td>
                      @if($value->status == 'Replied')
                      <span class="label label-success">{{$value->status}}</span>
                      @elseif($value->status == 'New')
                      <span class="label label-warning">{{$value->status}}</span>
                      @elseif($value->status == 'Read')
                      <span class="label label-primary">{{$value->status}}</span>
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
      <div class="col-md-4">
        <div class="box col-md-12">
          <form action="{{route('complain.reply')}}" method="post">
            @csrf
            <input type="hidden" name="complain_id" value="{{$value->id}}">
            <div class="form-group">
              <label for="">Reply to this message</label>
              <textarea name="reply_message" id="" cols="30" rows="10" class="form-control" placeholder="Type your message..."></textarea>
            </div>
            <button type="submit" class="btn btn-info">Reply</button>
            <br><br>
          </form>
        </div>
      </div>
    </div><!--/.col (left) -->
  </section><!-- /.content -->
   
@endsection
