@extends('dashboard')
@section('title', 'Edit Complain')
@section('stylesheets')
<link href="/assets/summernote/summernote.min.css" rel="stylesheet">
@endsection
@section('content')
<section class="content-header">
  <h1>Edit Complain</h1>
  <ol class="breadcrumb">
    <li><a href="#"><i class="fa fa-dashboard"></i> Complains</a></li>
    <li class="active">Edit Complain</li>
</ol>
</section>

<!-- Main content -->
<section class="content">
  <div class="row"> <!-- left column -->
    <div class="col-md-10"> <!-- general form elements -->
      <div class="box box-primary">
        <div class="box-header with-border">
            <h3 style="color: #800" class="box-title">Complain Details</h3>
        </div>
        <form action="{{route('complain.update', $complain)}}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="box-body">
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="name">Student Name</label>
                        <input type="text" class="form-control" name="name" id="name" required value="{{$complain->name}}">
                    </div>
                </div>
                <div class="clearfix"></div>
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="details">Details</label>
                        <textarea class="form-control editor" name="details" id="details" rows="5">{{$complain->details}}</textarea>
                    </div>
                </div>
                <div class="col-md-12">
                  <div class="form-group">
                      <label for="status">
                          <input type="checkbox" name="status" id="status" {{$complain->status == 'Active'? 'checked':''}} /> Status
                      </label>
                      &nbsp; 
                      <label for="status">
                        <input type="checkbox" name="web" id="web" {{$complain->web == 'Yes'? 'checked':''}} /> Web
                    </label>
                  </div>
                </div>
            </div> <!-- /.box body -->
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
<script type="text/javascript">
//this script for text editor
    $(document).ready(function() {
        $('.editor').summernote({
            height: 150
        });
    });
</script>
@endsection