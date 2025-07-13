@extends('dashboard')
@section('title', 'Edit Teacher')
@section('stylesheets')
<link href="/assets/summernote/summernote.min.css" rel="stylesheet">
@endsection
@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>Teacher</h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Users</a></li>
        <li class="active">Edit Teacher</li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">
      <div class="row">
        <!-- left column -->
        <div class="col-md-6">
          <!-- general form elements -->
          <div class="box box-primary">
            <div class="box-header with-border">
              <h3 class="box-title">Edit Teacher</h3>
            </div>
            <div class="col-md-12 text-right toolbar-icon">
              <a href="{{route('teacher.show', $user->id)}}" class="label label-info" title="Show"><i class="fa fa-file-text"></i></a>
              <a href="{{route('teacher.index')}}" title="View" class="label label-success"><i class="fa fa-list"></i></a>
            </div>
            <!-- /.box-header -->
            <!-- form start -->
            <form action="{{route('teacher.update', $user)}}" method="POST" enctype="multipart/form-data">
              @csrf
              @method('PUT')
              <div class="box-body">
                <div class="form-group">
                    <label for="">Name</label>
                    <input type="text" name="name" class="form-control" required value="{{$user->name}}" />
                </div>
                <div class="form-group">
                    <label for="designation" >Designation</label>
                    <textarea type="text" name="designation" class="form-control editor" >{{$user->designation}}</textarea>
                </div>
                <div class="form-group">
                    <label for="">Email Address</label>
                    <input type="email" name="email" class="form-control" required value="{{$user->email}}" />
                </div>
                <div class="form-group">
                    <label for="" >Contact</label>
                    <input type="text" name="contact" class="form-control" required value="{{$user->contact}}" />
                </div>
                <div class="form-group">
                  <label for="image">Profile Image</label>
                  <input class="form-control" type="file" id="image" name="image" />
                  <p class="text-danger">Image Dimension: 200 x 200px;<br> Types must be in: .jpeg, .jpg, .png and <br>Max size: 500KB</p>
                
                  <p class="text-center"><img src="{{$user->image}}" alt=""  style="max-width:150px" /></p>
                </div>
                <div class="form-group">
                  <label>
                    <input type="checkbox" name="status" id="status" style="width: 15px; height:15px" {{$user->status == 'Active' ?'checked':''}}>
                    <span style="margin-top:-10px">Status</span>
                  </label>

                    &nbsp;
                
                  <label>
                    <input type="checkbox" name="web" id="web" style="width: 15px; height:15px" {{$user->web == 'Yes' ? 'checked':''}}> 
                    <span style="margin-top:-10px">Web</span>
                  </label>
                </div>

              </div> <!-- /.box-body -->

              <div class="box-footer">
                <button type="submit" class="btn btn-primary pull-right"><i class="fa fa-save"></i> Save</button>
              </div>
            </form>
          </div> <!-- /.box -->

        </div> <!--/.col (left) -->
      </div> <!-- /.row -->
    </section> <!-- /.content -->
@endsection

@section('scripts')
<script src="/assets/summernote/summernote.min.js"></script>
<script>
//this script for text editor
$(document).ready(function() {
  $('.editor').summernote({
    // height: 150,
    toolbar: []
  });
});
</script>
@endsection