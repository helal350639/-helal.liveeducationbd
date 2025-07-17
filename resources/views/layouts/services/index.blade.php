@php
use \App\Http\Controllers\SourceCtrl;
$source = New SourceCtrl;
@endphp

@extends('dashboard')
@section('title', 'View All Service')
@section('content')
<!-- Content Header (Page header) -->
<section class="content-header">
  <h1>All Service</h1>
  <ol class="breadcrumb">
    <li><a href="#"><i class="fa fa-dashboard"></i>Dashboard</a></li>
    <li class="active">All Service</li>
  </ol>
</section>
<!-- Main content -->
<section class="content">
  <div class="row">
    <div class="col-xs-12">
      <div class="box">
        <div class="box-header">
          <h3 class="box-title">List of Service</h3>
              <div class="box-tools">
                <a href="{{route('service.create')}}" class="btn btn-sm btn-info">
                  <i class="fa fa-plus"></i> Add
                </a>
                <form action="{{route('service.index')}}" method="GET" class="input-group input-group-sm" style="float:right; width: 150px;margin-left:15px">
                  <input type="text" name="search" class="form-control pull-right" placeholder="Name" value="{{request('search')}}">

                  <div class="input-group-btn">
                    <button type="submit" class="btn btn-info"><i class="fa fa-search"></i></button>
                  </div>
                </form>
              </div>
            </div>
            <!-- /.box-header -->
            <div class="box-body table-responsive no-padding">
              <table id="example1" class="table table-bordered table-hover">
                <tr>
                  <th>Id</th>
                  <th>Title</th>
                  <th>Image</th>
                  <th>Status</th>
                  <th>Updated At</th>
                  <th width="170">Action</th>
                </tr>
                @foreach($services as $value)
                <tr>
                  <td>{{$value->id}}</td>
                  <td>{{$value->title}}</td>
                  <td>
                    <a target="_blank" href="{{$value->image}}">
                      <img src="{{$value->image}}" alt="" width="100">
                    </a>
                  </td>
                  <td>
                    <span class="label label-{{$value->status == 'Active'?'success':'danger'}}">{{$value->status}}</span>
                  </td>
                  <td>{{$source->dtformat($value->updated_at)}}</td>
                  <td>
                    <a href="{{route('service.show', $value->id)}}" class="btn btn-info btn-sm" title="Show"><i class="fa fa-eye"></i></a>
                    <a href="{{route('service.edit', $value->id)}}" class="btn btn-warning btn-sm" title="Edit this"><i class="fa fa-edit"></i></a>
                    <form style="display: inline" action="{{route('service.destroy', $value->id)}}" method="POST">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this one?')"><i class="fa fa-trash"></i></button>
                    </form>
                  </td>
                </tr>
                @endforeach
              </table>
            </div>
            <!-- /.box-body -->
            <div class="box-footer clearfix">
              <div class="pagination-sm no-margin pull-right">
                {{$services->links()}}
              </div>
            </div>
          </div>
          <!-- /.box -->
        </div>
      </div>
    </section>
    <!-- /.content -->
    @endsection