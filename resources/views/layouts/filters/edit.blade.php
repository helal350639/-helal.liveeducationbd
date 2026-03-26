@php
$course_ids = [];
foreach($filter->courses as $val)
{
    array_push($course_ids, $val->id);
}
@endphp

@extends('dashboard')
@section('title', 'Edit Filter')
@section('content')
<section class="content-header">
  <h1>Edit Filter</h1>
  <ol class="breadcrumb">
    <li><a href="#"><i class="fa fa-dashboard"></i> Filters</a></li>
    <li class="active">Edit Filter</li>
</ol>
</section>

<!-- Main content -->
<section class="content">
  <div class="row"> <!-- left column -->
    <div class="col-md-8"> <!-- general form elements -->
      <div class="box box-primary">
        <div class="box-header with-border">
            <h3 style="color: #800" class="box-title">Filter Details</h3>
        </div>
        <form action="{{route('filter.update', $filter)}}" method="POST">
            @csrf
            @method('PUT')
            <div class="box-body">
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="name">Course Name</label>
                        <select class="form-control select2" multiple name="course_id[]" id="course_id" required>
                            @foreach($courses as $val)
                            <option value="{{$val->id}}" {{in_array($val->id, $course_ids)? 'selected':''}}>{{$val->name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="name">Filter Name</label>
                        <input type="text" class="form-control" name="name" id="name" required value="{{$filter->name}}">
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="slug">Filter Slug</label>
                        <input type="text" class="form-control" name="slug" id="slug" value="{{$filter->slug}}" {{$filter->slug ? 'readonly':''}}>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="order_number">Order Number</label>
                        <input type="text" class="form-control" name="order_number" id="order_number" value="{{$filter->order_number}}">
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="label" class="btn btn-default">
                            <input type="checkbox" class="form-/control" name="label" id="label" value="Yes" {{$filter->label == 'Yes'? 'checked':''}} /> Use as Label</label>
                    </div>
                </div>
                <div class="clearfix"></div>
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="details">Details</label>
                        <textarea class="form-control" name="details" id="details" rows="5">{{$filter->details}}</textarea>
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