@php
use \App\Http\Controllers\SourceCtrl;
$source = New SourceCtrl;
@endphp

@extends('dashboard')
@section('title', 'View All Exam')
@section('content')
<!-- Content Header (Page header) -->
<section class="content-header">
  <h1>All Exam</h1>
  <ol class="breadcrumb">
    <li><a href="#"><i class="fa fa-dashboard"></i>Dashboard</a></li>
    {{-- <li><a href="#">Tables</a></li> --}}
    <li class="active">All Exam</li>
  </ol>
</section>
<!-- Main content -->
<section class="content">
  <div class="row">
    <div class="col-xs-12">
      <div class="box">
        <div class="box-body">
          <form action="{{route('exam.index')}}" method="GET">
            <div class="col-md-2">
              <div class="form-group">
                <label for="course_id">Course</label>
                <select name="course_id" id="course_id" class="form-control" onchange="getBatches(this)">
                  <option value="">Select One</option>
                  @foreach($courses as $course)
                  <option value="{{$course->id}}" {{request('course_id') == $course->id ? 'selected': ''}}>{{$course->name}}</option>
                  @endforeach
                </select>
              </div>
            </div>
            <div class="col-md-2">
              <div class="form-group">
                <label for="batch_id">Batch</label>
                <select name="batch_id" id="batch_id" class="form-control" onchange="getDepartments(this)">
                  <option value="">Select One</option>
                  @foreach($batches as $batch)
                  <option value="{{$batch->id}}" {{request('batch_id') == $batch->id ? 'selected': ''}}>{{$batch->name}}</option>
                  @endforeach
                </select>
              </div>
            </div>
            <div class="col-md-2">
              <div class="form-group">
                <label for="department_id">Department</label>
                <select name="department_id" id="department_id" class="form-control" onchange="getPapers(this)">
                  <option value="">Select One</option>
                  @foreach($departments as $department)
                  <option value="{{$department->id}}" {{request('department_id') == $department->id ? 'selected': ''}}>{{$department->name}}</option>
                  @endforeach
                </select>
              </div>
            </div>
            <div class="col-md-2">
              <div class="form-group">
                <label for="paper_id">Paper</label>
                <select name="paper_id" id="paper_id" class="form-control">
                  <option value="">Select One</option>
                  @foreach($papers as $paper)
                  <option value="{{$paper->id}}" {{request('paper_id') == $paper->id ? 'selected': ''}}>{{$paper->name}}</option>
                  @endforeach
                </select>
              </div>
            </div>
            <div class="col-md-2"><br>
              <button type="submit" class="btn btn-primary">Submit</button>
            </div>
          </form>
        </div>
      </div>

      <div class="box">
        <div class="box-header">
          <h3 class="box-title">List of Exam</h3>
          <div class="box-tools">
              {{-- <a href="{{route('exam.create')}}" class="btn btn-sm btn-info">
                <i class="fa fa-plus"></i> Add
              </a> --}}
              {{-- <div class="input-group input-group-sm" style="float:right; width: 150px;margin-left:15px">
                <input type="text" name="table_search" class="form-control pull-right" placeholder="Search">

                <div class="input-group-btn">
                  <button type="submit" class="btn btn-default"><i class="fa fa-search"></i></button>
                </div>
              </div> --}}
            </div>
          </div>
          <!-- /.box-header -->
            <div class="box-body table-responsive no-padding">
              <table id="print" class="table table-bordered table-hover">
                <tr>
                  <th>SL No.</th>
                  <th>Student Name</th>
                  <th>Registration ID</th>
                  <th>Department</th>
                  <th>Start & End Time</th>
                  <th>Status</th>
                  {{-- <th style="text-align: right">Correct</th>
                  <th style="text-align: right">Wrong</th>
                  <th style="text-align: right">Blank</th> --}}
                  <th style="text-align: right">Total Mark</th>
                  <th>Actions</th>
                </tr>
                @foreach($exams as $key => $value)
                <tr>
                  <td>{{$key+1}}</td>
                  <td>{{$value->student?$value->student->name:''}}</td>
                  <td>{{str_pad($value->student?$value->student->id:'', 6, '0', STR_PAD_LEFT)}}</td>
                  <td>{{!empty($value->paper->department) ?$value->paper->department->name : ''}}</td>
                  <td>{{$source->tformat($value->start_at).' - '.$source->tformat($value->end_at)}}</td>
                  <td>
                    @if($value->status == 'Live')
                    <label class="label label-info">{{$value->status}}</label>
                    @elseif($value->status == 'Completed')
                    <label class="label label-success"> {{$value->status}}</label>
                    @endif
                  </td>
                  {{-- <td style="text-align: right">{{$value->correct}}</td>
                  <td style="text-align: right">{{$value->wrong}}</td>
                  <td style="text-align: right">{{$value->no_answer}}</td> --}}
                  <td style="text-align: right">{{$value->mark}}</td>
                  <td>
                    <a class="btn btn-info btn-sm" href="{{route('exam.paper', $value->id)}}"><i class="fa fa-th"></i></a>
                    @if(Auth::user()->authorizeRoles(['SuperAdmin']))
                    <form style="display: inline" action="{{route('exam.destroy', $value->id)}}" method="POST">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this one? If you delete this exam, the candidate will lose his/her result that\'s will not get back later.')" title="Delete This!"><i class="fa fa-trash"></i></button>
                    </form>
                    @endif
                    @if($value->mark == 0)
                    {{-- <a class="btn btn-success btn-sm" href="{{route('exam.paper', $value->id)}}" title="Re-Check Result"><i class="fa fa-check"></i></a> --}}
                    @endif
                  </td>
                </tr>
                @endforeach
              </table>
            </div>
            <!-- /.box-body -->
            <div class="box-footer clearfix">
              <div class="pagination-sm no-margin pull-right">
                {{$exams->links()}}
              </div>
            </div>
          </div>
          <!-- /.box -->
        </div>
      </div>
    </section>
    <!-- /.content -->
    @endsection
@section('scripts')
  <script>
    function getBatches(e)
{
    let ids = Array.from(e.selectedOptions).map(({value}) => value);

    $.ajax({
        type: 'GET', //THIS NEEDS TO BE GET
        url: '/get_batches/' + ids,
        success: function (data) {

            var obj = JSON.parse(JSON.stringify(data));
            var options = '<option value="">Select One</option>';

            $.each(obj['data'], function (key, val) {
                options += '<option value="'+val.id+'">'+val.name+'</option>';
            });

            if(options != ""){
                $("#batch_id").html(options);
                $("#department_id").html('<option value="">Select One</option>');
            }else{
                $("#batch_id").html('');
            }
        },
        error: function(data) { 
                console.log('data error');
        }
    });
}

// on change batch get departments
function getDepartments(e)
{
    let ids = Array.from(e.selectedOptions).map(({value}) => value);

    $.ajax({
        type: 'GET', //THIS NEEDS TO BE GET
        url: '/get_departments_by_batch/' + ids,
        success: function (data) {

            var obj = JSON.parse(JSON.stringify(data));
            var options = '<option value="">Select One</option>';

            $.each(obj['data'], function (key, val) {
                options += '<option value="'+val.id+'">'+val.name+'</option>';
            });

            if(options != ""){
                $("#department_id").html(options)
            }else{
                $("#department_id").html('')
            }
        },
        error: function(data) { 
          console.log('data error');
        }
    });
}

  // on change batch get departments
  function getPapers(e)
  {
    let current_paper = "{{request('paper_id')}}";
    // let ids = Array.from(e.selectedOptions).map(({value}) => value);
    let id = e.options[e.selectedIndex].value;

    $.ajax({
      type: 'GET', //THIS NEEDS TO BE GET
      url: '/get-papers/' + id,
      success: function (data) {

          var obj = JSON.parse(JSON.stringify(data));
          var options = '<option value="">Select One</option>';

          $.each(obj['papers'], function (key, val) {
              options += '<option value="'+val.id+'">'+val.name+'</option>';
          });

          if(options != ""){
              $("#paper_id").html(options)
          }else{
              $("#paper_id").html('')
          }
        },
        error: function(data) { 
          console.log('data error');
        }
    });
  }
  // onload call
  // getPapers(document.getElementById('paper_id'));

    // $(function () {
    //   $('#example1').DataTable()
    //   $('#example2').DataTable({
    //     'paging'      : true,
    //     'lengthChange': false,
    //     'searching'   : false,
    //     'ordering'    : true,
    //     'info'        : true,
    //     'autoWidth'   : false
    //   })
    // })
  </script>
@endsection