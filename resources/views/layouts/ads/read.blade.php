@php
use \App\Http\Controllers\SourceCtrl;
$source = new SourceCtrl;
@endphp
@extends('dashboard')
@section('title', 'Ad Details')
@section('content')
  <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>Ad Details</h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Ads</a></li>
        <li class="active">Details</li>
      </ol>    
    </section>

    <!-- Main content -->
  <section class="content">
    <div class="row"><!-- left column -->
      <div class="col-md-8"><!-- general form elements -->
        <div class="box box-primary">
          <div class="box-header with-border">
            <h4 class="box-title">Ad Information</h4>
          </div>
          <div class="col-md-12 text-right toolbar-icon">
            <a href="{{route('ad.create')}}" title="Add New" class="label label-info"><i class="fa fa-plus"></i></a>
            <a href="{{route('ad.edit',$ad->id)}}" class="label label-warning" title="Edit this"><i class="fa fa-edit"></i></a>
            <a href="{{route('ad.index')}}" class="label label-success" title="View"><i class="fa fa-list"></i></a>
          </div>
          <div class="col-md-12">
            <table class="table">
              <tbody>
                <tr>
                  <th style="width: 200px;">Image:</th>
                  <td>
                    <a target="_blank" href="{{$ad->image}}">
                      <img src="{{$ad->image}}" alt="" width="200">
                    </a>
                  </td>
                </tr>
                <tr>
                  <th>Details</th>
                  <td>{!!$ad->details!!}</td>
                </tr>
                <tr>
                  <th>Status:</th>
                  <td>
                    <span class="label label-{{$ad->status == 'Active'? 'success':'danger'}}">{{$ad->status}}</span>
                  </td>
                </tr>
                <tr>
                  <th>Record Updated On:</th>
                  <td>{{$source->dtformat($ad->updated_at)}} </td>
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
