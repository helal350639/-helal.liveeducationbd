@php
use \App\Http\Controllers\SourceCtrl;
$source = New SourceCtrl;
@endphp

@extends('dashboard')
@section('title', 'Group Details')
@section('content')
  <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>Group Details</h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Groups</a></li>
        <li class="active">Details</li>
      </ol>    
    </section>

    <!-- Main content -->
  <section class="content">
    <div class="row"><!-- left column -->
      <div class="col-md-8"><!-- general form elements -->
        <div class="box box-primary">
          <div class="box-header with-border">
            <h4 class="box-title">Group Information</h4>
          </div>
          <div class="col-md-12 text-right toolbar-icon">
            <a href="{{route('group.create')}}" title="Add New" class="label label-info"><i class="fa fa-plus"></i></a>
            <a href="{{route('group.edit', $group->id)}}" class="label label-warning" title="Edit this"><i class="fa fa-edit"></i></a>
          </div>
          <div class="col-md-12">
            <table class="table">
                <tbody>
                  <tr>
                    <th style="width: 200px;">Name:</th>
                    <td>{{$group->name}}</td>
                  </tr>
                  <tr>
                    <th>Details:</th>
                    <td>{{$group->details}}</td>
                  </tr>
                
                   <tr>
                    <th>Status:</th>
                    <td>
                      <span class="label label-primary">{{$group->status}}</span>
                    </td>
                  </tr>
                  <tr>
                    <th>Record Created On:</th>
                    <td>{{$source->dtformat($group->created_at)}} </td>
                  </tr>
                  <tr>
                    <th>Record Updated On:</th>
                    <td>{{$source->dtformat($group->updated_at)}} </td>
                  </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div><!-- /.box -->
    </div><!--/.col (left) -->
  </section><!-- /.content -->
   
@endsection
