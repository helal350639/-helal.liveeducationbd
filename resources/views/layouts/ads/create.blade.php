@extends('dashboard')
@section('title', 'Create Ad')
@section('stylesheets')
<link href="/assets/summernote/summernote.min.css" rel="stylesheet">
@endsection
@section('content')
<section class="content-header">
  <h1>Create a Ad</h1>
  <ol class="breadcrumb">
    <li><a href="#"><i class="fa fa-dashboard"></i> Ads</a></li>
    <li class="active">Create Ad</li>
</ol>
</section>

<!-- Main content -->
<section class="content">
  <div class="row"> <!-- left column -->
    <div class="col-md-10"> <!-- general form elements -->
      <div class="box box-primary">
        <div class="box-header with-border">
            <h3 style="color: #800" class="box-title">Ad Details</h3>
        </div>
        <div class="col-md-12 text-right toolbar-icon">
          <a href="{{route('ad.index')}}" class="label label-success" title="View"><i class="fa fa-list"></i></a>            
        </div>
        <form action="{{route('ad.store')}}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="box-body">
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="image">Image</label>
                        <input type="file" class="form-control" name="image" id="image" required>
                    </div>
                </div>
                <div class="clearfix"></div>
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="details">Details</label>
                        <textarea class="form-control editor" name="details" id="details" rows="5"></textarea>
                    </div>
                </div>
                <div class="col-md-12">
                  <div class="form-group">
                    <label>
                      <input type="checkbox" name="status" id="status" style="width: 15px; height:15px"> 
                      <span style="margin-top:-10px"> Status</span>
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