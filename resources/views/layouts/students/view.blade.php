@php
use \App\Http\Controllers\SourceCtrl;
$source = New SourceCtrl;
$xids = $object->students()->pluck('id')->toArray();

// $object = $object;
// function check($id)
// {
//   global $object;
//   $xids = [];
//   if(!is_null($object)){
//     $xids = $object->students()->pluck('id')->toArray();
//   }
//   if(in_array($id, $xids))
//   {
//     return 'checked';
//   }
// }
// dd($object->students()->pluck('id')->toArray());
@endphp

@extends('dashboard')
@section('title', 'View All Students')
@section('content')

<style>
  .object{position: fixed; right: 0; bottom: 0; max-width: 300px}
</style>

  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>Students</h1>
    <ol class="breadcrumb">
      <li><a href="#"><i class="fa fa-dashboard"></i> Students</a></li>
      {{-- <li><a href="#">Tables</a></li> --}}
      <li class="active">Students</li>
    </ol>
  </section>

  <!-- Main content -->
  <section class="content">
    <div class="row">
      <div class="col-xs-12">
        <div class="box">
          <div class="box-header">
            <h3 class="box-title">List of Students ({{$students->total()}})</h3>
            <div class="box-tools">
              {{-- <a href="{{route('student.create')}}" class="btn btn-sm btn-info"><i class="fa fa-plus"></i> Add</a> --}}
              <a href="{{route($name.'.index')}}" class="btn btn-sm btn-info"><i class="fa fa-arrow-left"></i> Back</a>
              {{-- <div class="input-group input-group-sm" style="width: 150px;">
                <input type="text" name="table_search" class="form-control pull-right" placeholder="Search">

                <div class="input-group-btn">
                  <button type="submit" class="btn btn-default"><i class="fa fa-search"></i></button>
                </div>
              </div> --}}
            </div>
          </div>
          <!-- /.box-header -->
          <div class="box-body table-responsive no-padding">
            <table id="example1" class="table table-bordered table-hover">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Name</th>
                  <th>Email</th>
                  <th>Contact</th>
                  <th>Department</th>
                  {{-- <th>Status</th>
                  <th>Created On</th> --}}
                  <th width="110">Action</th>
                </tr>
              </thead>

              @foreach($students as $value)

              <tr>
                {{-- <td><input type="checkbox" onchange="check(this)" value="{{$value->id}}" {{in_array($value->id, $xids) ? 'checked' : ''}}/></td> --}}
                <td>{{$value->id}}</td>
                <td>{{$value->name}}</td>
                <td>{{$value->email}}</td>
                <td>{{$value->contact}}</td>
                <td>
                  @foreach($value->departments as $department)
                  <label class="label label-info">{{$department->name}}</label>
                  @endforeach
                </td>
                {{-- <td>
                  @if($value->status == "Active")
                  <span class="label label-success">{{$value->status}}</span>
                  @else
                  <span class="label label-warning">{{$value->status}}</span>
                  @endif
                </td>
                <td>{{ $source->dtformat($value->created_at) }}</td> --}}
                <td>
                  {{-- <a href="{{route('student.show', $value->id)}}" class="btn btn-info" title="User Details"><i class="fa fa-file-text"></i></a> --}}
                  <a href="{{route('student.remove', [$value->id, $name, $object->id])}}" class="btn btn-danger" title="Remove" onclick="return confirm('Are you sure you want to remove this student?')"><i class="fa fa-times"></i></a>
                </td>
              </tr>

              @endforeach
            </table>
          </div>
          <!-- /.box-body -->
          <div class="box-footer clearfix">
            <div class="pagination-sm no-margin pull-right">
              {{$students->links()}}
            </div>
          </div>
        </div> <!-- /.box -->
      </div>
    </div>
  </section> <!-- /.content -->
  @if(!is_null(Session::get('_object')))
  @php
  $object = Session::get('_object');
  @endphp
  <div class="panel object">
    <div class="panel-primary">
      <div class="panel-heading input-group">
        <label class="">{{$object['name']}}</label>
        <span class="input-group-addon" id="counter">{{$object['counter']}}</span>
        <a class="btn btn-info input-group-addon" href="{{route('students.add.complete')}}"><i class="fa fa-check"></i> Done</a>
      </div>
    </div>
  </div>
  @endif
@endsection
@section('scripts')
  <script>
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

    function check(e)
    {
      let counter = document.getElementById('counter');
      let action = '';
      if(e.checked == true)
      {
        action = 'add';
        counter.innerHTML = Number(counter.innerHTML)+1;
      }
      else
      {
        action = 'remove';
        counter.innerHTML = Number(counter.innerHTML)-1;
      }

      let formData = new FormData();
      formData.append('id', e.value);
      formData.append('action', action);
      
      $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
      });

      $.ajax({
        url: '{{route("students.add.object")}}',
        type: 'POST',
        data: formData,
        cache: false,
        contentType: false,
        processData: false,
        success: function(data){
          if(data.success == true)
        {
          //
        }
          // console.log(data);
        },
        error: function(data){
          console.log(data);
        },
      });
    }
  </script>
@endsection