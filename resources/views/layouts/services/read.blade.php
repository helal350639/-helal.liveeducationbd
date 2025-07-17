@php
use \App\Http\Controllers\SourceCtrl;
$source = new SourceCtrl;
@endphp
@extends('dashboard')
@section('title', 'Service Details')
@section('content')
  <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>Service Details</h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Services</a></li>
        <li class="active">Details</li>
      </ol>    
    </section>

    <!-- Main content -->
  <section class="content">
    <div class="row"><!-- left column -->
      <div class="col-md-8"><!-- general form elements -->
        <div class="box box-primary">
          <div class="box-header with-border">
            <h4 class="box-title">Service Information</h4>
          </div>
          <div class="col-md-12 text-right toolbar-icon">
            <a href="{{route('service.create')}}" title="Add New" class="label label-info"><i class="fa fa-plus"></i></a>
            <a href="{{route('service.edit',$service->id)}}" class="label label-warning" title="Edit this"><i class="fa fa-edit"></i></a>
            <a href="{{route('service.index')}}" class="label label-success" title="View"><i class="fa fa-list"></i></a>
          </div>
          <div class="col-md-12">
            <table class="table">
              <tbody>
                <tr>
                  <th style="width: 200px;">Title:</th>
                  <td>
                    {{$service->title}}
                  </td>
                </tr>
                <tr>
                  <th style="width: 200px;">Image:</th>
                  <td>
                    <a target="_blank" href="{{$service->image}}">
                      <img src="{{$service->image}}" alt="" width="200">
                    </a>
                  </td>
                </tr>
                <tr>
                  <th>Details</th>
                  <td>
                    {!!$service->details!!}
                  </td>
                </tr>
                <tr>
                  <th>Status:</th>
                  <td>
                    <span class="label label-{{$service->status == 'Active'? 'success':'danger'}}">{{$service->status}}</span>
                  </td>
                </tr>
                <tr>
                  <th>Record Updated On:</th>
                  <td>{{$source->dtformat($service->updated_at)}} </td>
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
