@php
$course_ids = [];
foreach($department->courses as $val)
{
    array_push($course_ids, $val->id);
}
@endphp

@extends('dashboard')
@section('title', 'Edit Department')
@section('content')
<section class="content-header">
  <h1>Edit Department</h1>
  <ol class="breadcrumb">
    <li><a href="#"><i class="fa fa-dashboard"></i> Departments</a></li>
    <li class="active">Edit Department</li>
</ol>
</section>

<!-- Main content -->
<section class="content">
  <div class="row"> <!-- left column -->
    <div class="col-md-8"> <!-- general form elements -->
      <div class="box box-primary">
        <div class="box-header with-border">
            <h3 style="color: #800" class="box-title">Department Details</h3>
        </div>
        <form action="{{route('department.update', $department)}}" method="POST">
            @csrf
            @method('PUT')
            <div class="box-body">
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="name">Course Name</label>
                        <select class="form-control select2" multiple name="course_id[]" id="course_id[]" required>
                            @foreach($courses as $val)
                            <option value="{{$val->id}}" {{in_array($val->id, $course_ids)? 'selected':''}}>{{$val->name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="name">Department Name</label>
                        <input type="text" class="form-control" name="name" id="name" required value="{{$department->name}}">
                    </div>
                </div>
                <div class="clearfix"></div>
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="details">Details</label>
                        <textarea class="form-control" name="details" id="details" rows="5">{{$department->details}}</textarea>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="status">Status:</label>
                        <select class="form-control" name="status" id="status">
                          <option value="Active" {{$department->status == 'Active' ? 'selected' : ''}}>Active</option>
                          <option value="Deactive" {{$department->status == 'Deactive' ? 'selected' : ''}}>Deactive</option>
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

                // console.log(obj['subcats'].count());

                // $("#sub_cat").append(you_html); //// For Append
                   //// For replace with previous one
            },
            error: function(data) { 
                 console.log('data error');
            }
        });
    }

    // select2 execution
    $(function(){ $('.select2').select2(); });
</script>
@endsection