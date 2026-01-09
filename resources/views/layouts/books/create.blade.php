@extends('dashboard')
@section('title', 'Add New Book')
@section('stylesheets')
<link href="/assets/summernote/summernote.min.css" rel="stylesheet">
@endsection
@section('content')
<section class="content-header">
  <h1>Add a Book</h1>
  <ol class="breadcrumb">
    <li><a href="#"><i class="fa fa-dashboard"></i> Books</a></li>
    <li class="active">Add Book</li>
</ol>
</section>

<!-- Main content -->
<section class="content">
  <div class="row"> <!-- left column -->
    <div class="col-md-10"> <!-- general form elements -->
      <div class="box box-primary">
        <div class="box-header with-border">
            <h3 style="color: #800" class="box-title">Book Details</h3>
        </div><div class="col-md-12 text-right toolbar-icon">
          <a href="{{route('book.index')}}" class="label label-success" title="Index">
            <i class="fa fa-list"></i>
          </a>
        </div>
        <form action="{{route('book.store')}}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="box-body">
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="name">Book Name</label>
                        <input type="text" class="form-control" name="name" id="name" required />
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="category_id">Category Name</label>
                        <select class="form-control select2" name="category_id" id="category_id">
                            @foreach($categories as $val)
                            <option value="{{$val->id}}">{{$val->name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="department_id">Department Name</label>
                        <select class="form-control select2" name="department_id" id="department_id">
                            @foreach($departments as $val)
                            <option value="{{$val->id}}">{{$val->name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="chapter_id">Chapters</label>
                        <select class="form-control select2" name="chapter_id[]" id="chapter_id" multiple>
                            @foreach($chapters as $val)
                            <option value="{{$val->id}}">{{$val->name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="front_cover">Fron Cover</label>
                        <input type="file" class="form-control" name="front_cover" id="front_cover" onchange="showImg(this)" />
                        <p style="color:red;padding:5px 0">Image size: 4:6 / 400px X 600px</p>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="back_cover">Back Cover</label>
                        <input type="file" class="form-control" name="back_cover" id="back_cover" onchange="showImg(this)" />
                        <p style="color:red;padding:5px 0">Image size: 4:6 / 400px X 600px</p>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="details">Details</label>
                        <textarea class="form-control editor" name="details" id="details" rows="5"></textarea>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="status">Status:</label>
                        <select class="form-control" name="status" id="status">
                          <option value="Active">Active</option>
                          <option value="Deactive">Deactive</option>
                        </select>
                    </div>
                </div>
            </div> <!-- /.box body -->
            <div class="box-footer">
                <button type="submit" class="btn btn-primary pull-right">
                  <i class="fa fa-save"></i> Save
                </button>
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
  // on upload show image
  function showImg(e)
  {
    if(e.parentNode.firstElementChild.tagName == 'IMG')
    {
      e.parentNode.firstElementChild.src = window.URL.createObjectURL(e.files[0]);
    }
    else
    {
      let img = document.createElement('img');
      img.src = window.URL.createObjectURL(e.files[0]);
      img.setAttribute('style', 'width:100%;max-width:400px; margin-top:15px');
      img.alt = '';
      e.parentNode.appendChild(img);
    }      
  }

$(function(){ $('.select2').select2(); });

//this script for text editor
$(document).ready(function() {
    $('.editor').summernote({
        height: 150
    });
});
</script>
@endsection