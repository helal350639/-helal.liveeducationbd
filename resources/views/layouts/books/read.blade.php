@php
use \App\Http\Controllers\SourceCtrl;
$source = New SourceCtrl;
$value = $book;
@endphp

@extends('dashboard')
@section('title', 'Book Details')
@section('content')
  <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>Book Details</h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Books</a></li>
        <li class="active">Details</li>
      </ol>    
    </section>

    <!-- Main content -->
  <section class="content">
    <div class="row"><!-- left column -->
      <div class="col-md-8"><!-- general form elements -->
        <div class="box box-primary">
          <div class="box-header with-border">
            <h4 class="box-title">Book Information</h4>
          </div>
          <div class="col-md-12 text-right toolbar-icon">
            <a href="{{route('book.add.question', ['book', $value->id])}}" title="Add Questions" class="btn btn-info">
              <i class="fa fa-plus"></i> Add Questions
            </a>
            <a href="{{route('book.create')}}" title="Add New" class="btn btn-info">
              <i class="fa fa-plus"></i>
            </a>
            <a href="{{route('book.view', $value->id)}}" title="Read" class="btn btn-default">
              <i class="fa fa-eye"></i>
            </a>
            <a href="{{route('book.edit', $value->id)}}" class="btn btn-warning" title="Edit this">
              <i class="fa fa-edit"></i>
            </a>
            <a href="{{route('book.index', $value->id)}}" class="btn btn-success" title="Index">
              <i class="fa fa-list"></i>
            </a>
          </div>
          <div class="col-md-12">
            <table class="table">
                <tbody>
                  <tr>
                    <th style="width: 200px;">Name:</th>
                    <td>{{$value->name}}</td>
                  </tr>
                  <tr>
                    <th>Department Name:</th>
                    <td>{{$value->department->name}}</td>
                  </tr>
                  <tr>
                    <th>Category Name:</th>
                    <td>{{$value->category->name}}</td>
                  </tr>
                  <tr>
                    <th>Chapters:</th>
                    <td>
                      @foreach($value->chapters as $val)
                      <label class="label label-info">{{$val->name}}</label>
                      @endforeach
                    </td>
                  </tr>
                  <tr>
                    <th>Question Count:</th>
                    <th>{{$value->questions()->count()}}</th>
                  </tr>
                  <tr>
                    <th>Front Cover:</th>
                    <td>
                      <a target="_blank" href="{{$value->front_cover}}">
                        <img width="250" src="{{$value->front_cover}}" alt="">
                      </a>
                    </td>
                  </tr>
                  <tr>
                    <th>Back Cover:</th>
                    <td>
                      <a target="_blank" href="{{$value->back_cover}}">
                        <img width="250" src="{{$value->back_cover}}" alt="">
                      </a>
                    </td>
                  </tr>
                  <tr>
                    <th>Details:</th>
                    <td>{{$value->details}}</td>
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
