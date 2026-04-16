@php
$value = $notice;
@endphp

@extends('dashboard')
@section('title', 'Edit Notice')

@section('stylesheets')
<link href="/assets/summernote/summernote.min.css" rel="stylesheet">
@endsection

@section('content')
<section class="content-header">
  <h1>Edit Notice</h1>
  <ol class="breadcrumb">
    <li><a href="#"><i class="fa fa-dashboard"></i> Notices</a></li>
    <li class="active">Edit Notice</li>
</ol>
</section>

<!-- Main content -->
<section class="content">
  <div class="row"> <!-- left column -->
    <div class="col-md-8"> <!-- general form elements -->
      <div class="box box-primary">
        <div class="box-header with-border">
            <h3 style="color: #800" class="box-title">Notice Details</h3>
        </div>
        <div class="col-md-12 text-right toolbar-icon">
        </div>

        <form action="{{route('notice.update', $notice->id)}}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="box-body">
              <div class="col-md-12">
              <div class="col-md-12">
                  <div class="form-group">
                      <label for="title">Title</label>
                      <input type="text" class="form-control" name="title" id="title" required value="{{$value->title}}">
                  </div>
              </div>
              <div class="col-md-12">
                  <div class="form-group">
                      <label for="details">Details</label>
                      <textarea class="form-control editor" name="details" id="details" rows="10">{{$value->details}}</textarea>
                  </div>
              </div>
              {{-- <div class="col-md-12">
                  <div class="form-group">
                      <label for="image">Image</label>
                      <input type="file" class="form-control" name="image" id="image" multiple>
                  </div>
                  <p>
                    @if($value->image)
                      <a target="_blank" href="{{$image}}">
                        <img src="{{$image}}" alt="" width="100">
                      </a>
                    @endif
                  </p>
              </div>
              <div class="col-md-6">
                  <div class="form-group">
                      <label for="start_at">Start Date</label>
                      <input type="datetime-local" class="form-control" name="start_at" id="start_at" value="{{$value->start_at}}">
                  </div>
              </div>
              <div class="col-md-6">
                  <div class="form-group">
                      <label for="end_at">End Date</label>
                      <input type="datetime-local" class="form-control" name="end_at" id="end_at" value="{{$value->end_at}}">
                  </div>
              </div> --}}
              <div class="col-md-6">
                  <div class="form-group">
                      <label for="status">Status</label>
                      <select class="form-control" name="status" id="status">
                        <option value="">Select One</option>
                        <option value="Published" {{$value->status == 'Published' ? 'selected':''}}>Published</option>
                        <option value="Unpublished" {{$value->status == 'Unpublished' ? 'selected':''}}>Unpublished</option>
                      </select>
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