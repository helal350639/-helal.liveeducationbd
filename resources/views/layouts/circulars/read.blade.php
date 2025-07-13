@php
use \App\Http\Controllers\CircularCtrl;
$circulars = New CircularCtrl;

use \App\Http\Controllers\SourceCtrl;
$source = New SourceCtrl;
$value = $circular;
@endphp

@extends('dashboard')
@section('title', 'Circular Details')
@section('content')
  <style>
    .circular-image img{border:5px solid #ddd}
  </style>
  <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>Circular Details</h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Circulars</a></li>
        <li class="active">Details</li>
      </ol>    
    </section>

    <!-- Main content -->
  <section class="content">
    <div class="row"><!-- left column -->
      <div class="col-md-8"><!-- general form elements -->
        <div class="box box-primary">
          <div class="box-header with-border">
            <h4 class="box-title">Circular Information</h4>
          </div>
          <div class="col-md-12 text-right toolbar-icon">
            <a href="{{route('circular.create')}}" title="Add New One" class="label label-info"><i class="fa fa-plus"></i></a>
            <a href="{{route('circular.index')}}" title="View" class="label label-success"><i class="fa fa-list"></i></a>
            <a href="{{route('circular.edit', $value->id)}}" class="label label-warning" title="Edit this"><i class="fa fa-edit"></i></a>
            
            <form style="display: inline" action="{{route('circular.destroy', $value->id)}}" method="POST">
              @csrf
              @method('DELETE')
              <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this one?')"><i class="fa fa-trash"></i></button>
            </form>
            
          </div>
          <div class="col-md-12">
            <table class="table">
                <tbody>
                  <tr>
                    <th style="width: 200px;">Title:</th>
                    <td>{{$value->title}}</td>
                  </tr>
                  <tr>
                    <th>Type:</th>
                    <td>{{$circulars->circulars($value->type)}}</td>
                  </tr>
                  <tr>
                    <th>Publish Date</th>
                    <td>{{$source->dformat($value->published_at)}}</td>
                  </tr>
                  <tr>
                    <th>Apply Start</th>
                    <td>{{$source->dformat($value->start_at)}}</td>
                  </tr>
                <tr>
                  <th>Deadline</th>
                    <td>{{$source->dformat($value->end_at)}}</td>
                  </tr>
                <tr>
                  <th>Institute</th>
                    <td>{{$value->admit_to}}</td>
                  </tr>
                <tr>
                  <th>Position</th>
                    <td>{{$value->position}}</td>
                  </tr>
                <tr>
                <tr>
                  <th>Fee</th>
                  <td>{{$value->fee}}</td>
                </tr>
                <tr>
                  <th>URL</th>
                  <td>{{$value->url}}</td>
                </tr>
                <tr>
                  <th>Details</th>
                  <td>{{$value->details}}</td>
                </tr>
                <tr>
                  <th>Image/s</th>
                  <td>
                    @if($value->image)
                      @php
                      $img_array = json_decode($value->image);
                      @endphp

                      @foreach($img_array as $image)
                      <a target="_blank" href="{{$image}}" class="circular-image">
                        <img src="{{$image}}" alt="" width="150">
                      </a>
                      @endforeach
                    @endif
                  </td>
                </tr>
                <tr>
                  <th>PDF</th>
                  <td>
                    @if($value->pdf)
                    <h3>
                      <a class="text-danger" target="_blank" href="{{$value->pdf}}">
                        <i class="fa fa-file-pdf-o"></i>
                      </a>
                      <h3>
                    @endif
                  </td>
                </tr>
                
                <tr>
                  <th>Status:</th>
                  <td>
                    @if($value->status == 'Published')
                    <span class="label label-success">{{$value->status}}</span>
                    @else
                    <span class="label label-danger">{{$value->status}}</span>
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
