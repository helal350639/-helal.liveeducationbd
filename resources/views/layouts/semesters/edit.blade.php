@php
$courseIds = $deptIds = [];
foreach($semester->courses as $val)
{
    array_push($courseIds, $val->id);
}
foreach($semester->departments as $val)
{
    array_push($deptIds, $val->id);
}
@endphp

@extends('dashboard')
@section('title', 'Edit Semester')
@section('content')
<section class="content-header">
  <h1>Edit Semester</h1>
  <ol class="breadcrumb">
    <li><a href="#"><i class="fa fa-dashboard"></i> Semesters</a></li>
    <li class="active">Edit Semester</li>
</ol>
</section>

<!-- Main content -->
<section class="content">
  <div class="row"> <!-- left column -->
    <div class="col-md-8"> <!-- general form elements -->
      <div class="box box-primary">
        <div class="box-header with-border">
            <h3 style="color: #800" class="box-title">Semester Details</h3>
        </div>
        <form action="{{route('semester.update', $semester)}}" method="POST">
            @csrf
            @method('PUT')
            <div class="box-body">
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="name">Course Name</label>
                        <select class="form-control select2" multiple name="course_id[]" id="course_id" required onchange="getDepartments(this)">
                            @foreach($courses as $val)
                            <option value="{{$val->id}}" {{in_array($val->id, $courseIds)? 'selected':''}}>{{$val->name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="name">Department Name</label>
                        <select class="form-control select2" multiple name="department_id[]" id="department_id" required>
                            @foreach($departments as $val)
                            <option value="{{$val->id}}" {{in_array($val->id, $deptIds)? 'selected':''}}>{{$val->name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="name">Semester Name</label>
                        <input type="text" class="form-control" name="name" id="name" required value="{{$semester->name}}">
                    </div>
                </div>
                <div class="clearfix"></div>
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="details">Details</label>
                        <textarea class="form-control" name="details" id="details" rows="5">{{$semester->details}}</textarea>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="status">Status:</label>
                        <select class="form-control" name="status" id="status">
                          <option value="Active" {{$semester->status == 'Active' ? 'selected' : ''}}>Active</option>
                          <option value="Deactive" {{$semester->status == 'Deactive' ? 'selected' : ''}}>Deactive</option>
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

<script type="text/javascript">
    function getDepartments(elm)
    {
        const selectedIds = '{{implode(",", $deptIds)}}';
        const selectedIdsArray = selectedIds.split(',');
        function check(e)
        {
            if(selectedIdsArray.includes(e.toString()))
            {
                return 'selected';
            }
            return '';
        }

        var ids = Array.from(elm.selectedOptions).map(({value}) => value);

        $.ajax({
            type: 'GET', //THIS NEEDS TO BE GET
            url: '/get_departments/'+ids,
            success: function (data) {

                var obj = JSON.parse(JSON.stringify(data));
                var html = "";

                $.each(obj['data'], function (key, val) {
                   html += "<option value="+val.id+" "+check(val.id)+">"+val.name+"</option>";
                });

                if(html != ""){
                    $("#department_id").html(html)
                }else{
                    $("#department_id").html('')
                }
            },
            error: function(data) { 
                 console.log('data error');
            }
        });
    }
</script>
@endsection

@section('scripts')
<script type="text/javascript">
    $(function(){ $('.select2').select2() });
</script>
@endsection