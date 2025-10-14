@extends('dashboard')
@section('title', 'Edit referrer')
@section('stylesheets')
<link href="/assets/summernote/summernote.min.css" rel="stylesheet">
@endsection
@section('content')
<section class="content-header">
  <h1>Edit referrer</h1>
  <ol class="breadcrumb">
    <li><a href="#"><i class="fa fa-dashboard"></i> referrers</a></li>
    <li class="active">Edit referrer</li>
</ol>
</section>

<!-- Main content -->
<section class="content">
  <div class="row"> <!-- left column -->
    <div class="col-md-10"> <!-- general form elements -->
      <div class="box box-primary">
        <div class="box-header with-border">
            <h3 style="color: #800" class="box-title">referrer Details</h3>
        </div>
        <form action="{{route('referrer.update', $referrer)}}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PATCH')
            <div class="box-body">
              <div class="col-md-12">
                <div class="form-group">
                    <label for="">Batch ID</label>
                    <input type="text" class="form-control" name="batch_id" id="batch_id" value="{{$referrer->batch_id}}">
                </div>
                <div class="form-group">
                    <label for="">Referrer ID</label>
                    <input type="text" class="form-control" name="referrer_id" id="referrer_id" value="{{$referrer->referrer_id}}">
                </div>
                <div class="form-group">
                    <label for="">Referrer Bonus</label>
                    <input type="text" class="form-control" name="referrer_bonus" id="referrer_bonus" value="{{$referrer->referrer_bonus}}">
                </div>
                <div class="form-group">
                    <label for="">Referrer type</label>
                    <input type="text" class="form-control" name="referrer_type" id="referrer_type" value="{{$referrer->referrer_type}}">
                </div>
              </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="">Student ID</label>
                        <input type="text" class="form-control" name="student_id" id="student_id" value="{{$referrer->student_id}}">
                    </div>
                </div>
            </div> <!-- /.box body -->
            <div class="box-footer">
                <button type="submit" class="btn btn-primary pull-right"><i class="fa fa-save"></i> Update</button>
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