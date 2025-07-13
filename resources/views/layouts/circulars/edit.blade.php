@php
$value = $circular;
@endphp

@extends('dashboard')
@section('title', 'Edit Circular')
@section('content')
<section class="content-header">
  <h1>Edit Circular</h1>
  <ol class="breadcrumb">
    <li><a href="#"><i class="fa fa-dashboard"></i> Circulars</a></li>
    <li class="active">Edit Circular</li>
</ol>
</section>

<!-- Main content -->
<section class="content">
  <div class="row"> <!-- left column -->
    <div class="col-md-8"> <!-- general form elements -->
      <div class="box box-primary">
        <div class="box-header with-border">
            <h3 style="color: #800" class="box-title">Circular Details</h3>
        </div>
        <form action="{{route('circular.update', $circular)}}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="box-body">
              <div class="col-md-12">
                  <div class="form-group">
                      <label for="name">Circular Type</label>
                      <select class="form-control select2" name="type" id="type" required>
                          <option value="">Select One</option>
                          @foreach($circulars as $key => $val)
                          <option value="{{$key}}" {{$key == $value->type ? 'selected': ''}}>{{$val}}</option>
                          @endforeach
                      </select>
                  </div>
              </div>
              <div class="col-md-12">
                  <div class="form-group">
                      <label for="title">Title</label>
                      <input type="text" class="form-control" name="title" id="title" required value="{{$value->title}}">
                  </div>
              </div>
              <div class="col-md-4">
                  <div class="form-group">
                      <label for="published_at">Publish Date</label>
                      <input type="date" class="form-control" name="published_at" id="published_at" required value="{{$value->published_at}}">
                  </div>
              </div>
              <div class="col-md-4">
                  <div class="form-group">
                      <label for="start_at">Application Start</label>
                      <input type="date" class="form-control" name="start_at" id="start_at" required value="{{$value->start_at}}">
                  </div>
              </div>
              <div class="col-md-4">
                  <div class="form-group">
                      <label for="end_at">Deadline</label>
                      <input type="date" class="form-control" name="end_at" id="end_at" required value="{{$value->end_at}}">
                  </div>
              </div>
              <div class="col-md-12">
                  <div class="form-group">
                      <label for="admit_to">Institute Name</label>
                      <input type="text" class="form-control" name="admit_to" id="admit_to" required value="{{$value->admit_to}}">
                  </div>
              </div>
              <div class="col-md-6">
                  <div class="form-group">
                      <label for="position">Position</label>
                      <input type="text" class="form-control" name="position" id="position" required value="{{$value->position}}">
                  </div>
              </div>
              <div class="col-md-6">
                  <div class="form-group">
                      <label for="fee">Application Fee</label>
                      <input type="number" class="form-control" name="fee" id="fee" required placeholder="00.00" value="{{$value->fee}}">
                  </div>
              </div>
              <div class="col-md-12">
                  <div class="form-group">
                      <label for="url">Link/url</label>
                      <input type="url" class="form-control" name="url" id="url" value="{{$value->url}}">
                  </div>
              </div>
              <div class="col-md-12">
                  <div class="form-group">
                      <label for="image">Image</label>
                      <input type="file" class="form-control" name="image[]" id="image" multiple>
                  </div>
                  <p>
                    @if($value->image)
                      @php
                      $img_array = json_decode($value->image);
                      @endphp
                      @foreach($img_array as $image)
                      <a target="_blank" href="{{$image}}">
                        <img src="{{$image}}" alt="" width="100">
                      </a>
                      @endforeach
                    @endif
                  </p>
              </div>
              <div class="col-md-12">
                  <div class="form-group">
                      <label for="pdf">PDF File</label>
                      <input type="file" class="form-control" name="pdf" id="pdf" accept="application/pdf">
                  </div>
              </div>
              <div class="col-md-12">
                  <div class="form-group">
                      <label for="details">Details</label>
                      <textarea class="form-control" name="details" id="details" rows="5">{{$value->details}}</textarea>
                  </div>
              </div>
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
<script type="text/javascript">
    function getsubcats(elm){

        var catid = elm.options[elm.options.selectedIndex].value;

        $.ajax({
            type: 'GET', //THIS NEEDS TO BE GET
            url: '/get_sub_cats/'+catid,
            success: function (data) {

                var obj = JSON.parse(JSON.stringify(data));
                var sub_cat_html = "";

                $.each(obj['subcats'], function (key, val) {
                   sub_cat_html += "<option value="+val.id+">"+val.name+"</option>";
                });

                if(sub_cat_html != ""){
                    $("#sub_cat").html('<option value="">Select SubCategory</option>'+sub_cat_html)
                }else{
                    $("#sub_cat").html('<option value="">No SubCategory</option>')
                }
            },
            error: function(data) { 
                 console.log('data error');
            }
        });
    }

    $(function (){ $('.select2').select2() });
</script>
@endsection