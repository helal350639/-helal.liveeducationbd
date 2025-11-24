@php
$value = $subject;
$courseIds = $deptIds = $semIds = [];
foreach($subject->courses as $val)
{
    array_push($courseIds, $val->id);
}
foreach($subject->departments as $val)
{
    array_push($deptIds, $val->id);
}
foreach($subject->semesters as $val)
{
    array_push($semIds, $val->id);
}
@endphp

@extends('dashboard')
@section('title', 'Edit Subject')
@section('content')
<section class="content-header">
  <h1>Edit Subject</h1>
  <ol class="breadcrumb">
    <li><a href="#"><i class="fa fa-dashboard"></i> Subjects</a></li>
    <li class="active">Edit Subject</li>
</ol>
</section>

<!-- Main content -->
<section class="content">
  <div class="row"> <!-- left column -->
    <div class="col-md-8"> <!-- general form elements -->
      <div class="box box-primary">
        <div class="box-header with-border">
            <h3 style="color: #800" class="box-title">Subject Details</h3>
        </div>
        <form action="{{route('subject.update', $value)}}" method="POST">
            @csrf
            @method('PUT')
            <div class="box-body">
                <div class="col-md-8">
                    <div class="form-group">
                        <label for="name">Subject Name</label>
                        <input type="text" class="form-control" name="name" id="name" required value="{{$value->name}}">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="code">Subject Code (Optional)</label>
                        <input type="text" class="form-control" name="code" id="code" value="{{$value->code}}" />
                    </div>
                </div>
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
                        <select class="form-control select2" multiple name="department_id[]" id="department_id" required onchange="getSemesters(this)">
                            @foreach($departments as $val)
                            <option value="{{$val->id}}" {{in_array($val->id, $deptIds)? 'selected':''}}>{{$val->name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="semester_id">Semester Name</label>
                        <select class="form-control select2" multiple name="semester_id[]" id="semester_id">
                            @foreach($semesters as $val)
                            <option value="{{$val->id}}" {{in_array($val->id, $semIds)? 'selected':''}}>{{$val->name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="clearfix"></div>
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="details">Details</label>
                        <textarea class="form-control" name="details" id="details" rows="5">{{$value->details}}</textarea>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="status">Status:</label>
                        <select class="form-control" name="status" id="status">
                          <option value="Active" {{$value->status == 'Active' ? 'selected' : ''}}>Active</option>
                          <option value="Deactive" {{$value->status == 'Deactive' ? 'selected' : ''}}>Deactive</option>
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

function getDepartments(e)
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
    let ids = Array.from(e.selectedOptions).map(({value}) => value);

    $.ajax({
        type: 'GET', //THIS NEEDS TO BE GET
        url: '/get_departments/' + ids,
        success: function (data) {

            var obj = JSON.parse(JSON.stringify(data));
            var options = '';

            $.each(obj['data'], function (key, val) {
                options += '<option value="'+val.id+'" '+check(val.id)+'>'+val.name+'</option>';
            });

            if(options != ""){
                $("#department_id").html(options)
            }else{
                $("#department_id").html('<option value="">No One</option>')
            }
        },
        error: function(data) { 
                console.log('data error');
        }
    });
}

function getSemesters(e)
{
    const selectedIds = '{{implode(",", $semIds)}}';
    const selectedIdsArray = selectedIds.split(',');
    function check(e)
    {
        if(selectedIdsArray.includes(e.toString()))
        {
            return 'selected';
        }
        return '';
    }
    let ids = Array.from(e.selectedOptions).map(({value}) => value);

    $.ajax({
        type: 'GET', //THIS NEEDS TO BE GET
        url: '/get_semesters/' + ids,
        success: function (data) {

            var obj = JSON.parse(JSON.stringify(data));
            var options = '';

            $.each(obj['data'], function (key, val) {
                options += '<option value="'+val.id+'" '+check(val.id)+'>'+val.name+'</option>';
            });

            if(options != ""){
                $("#semester_id").html(options)
            }else{
                $("#semester_id").html('<option value="">No One</option>')
            }
        },
        error: function(data) { 
                console.log('data error');
        }
    });
}

$(function(){ $('.select2').select2(); });
</script>
@endsection