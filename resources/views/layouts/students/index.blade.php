@php
use \App\Http\Controllers\SourceCtrl;
$source = New SourceCtrl;
function check($id)
{
  $xids = [];
  if(!is_null(Session::get('_object'))){
    $xids = Session::get('_object')['xids'];
  }
  if(in_array($id, $xids))
  {
    return 'checked';
  }
}
@endphp

@extends('dashboard')
@section('title', 'View All Students')
@section('content')

<style>
  .object{position: fixed;right: 0; bottom: 0; max-width: 300px}
  .add_checkbox{width:20px;height: 20px;}
</style>

  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>Students Accounts</h1>
    <ol class="breadcrumb">
      <li><a href="#"><i class="fa fa-dashboard"></i> Students</a></li>
      {{-- <li><a href="#">Tables</a></li> --}}
      <li class="active">Students Accounts</li>
    </ol>
  </section>

  <!-- Main content -->
  <section class="content">
    <div class="row">
      <div class="col-xs-12">
        <div class="box">
          <div class="box-header">
            <h3 class="box-title">List of Students</h3>
            <div class="box-tools">
              <form action="{{route('student.search')}}" method="post" style="width: 250px; float:left">
                @csrf
                <div class="input-group input-group-sm">
                  <input type="text" name="value" class="form-control pull-right" placeholder="Search" required>
  
                  <div class="input-group-btn">
                    <button type="submit" class="btn btn-default"><i class="fa fa-search"></i></button>
                  </div>
                </div>
              </form>
              <a href="{{route('student.create')}}" class="btn btn-sm btn-info" style="margin-left:15px"><i class="fa fa-plus"></i> Add</a>
              <a href="{{route('student.index')}}" class="btn btn-sm btn-success" style=""><i class="fa fa-list"></i> All</a>
            </div>
          </div>
          <!-- /.box-header -->
          <div class="box-body table-responsive no-padding">
            <table id="example1" class="table table-bordered table-hover">
              <thead>
                <tr>
                  <th style="width:32px">#</th>
                  <th>ID</th>
                  <th>Name</th>
                  <th>Email</th>
                  <th>Contact</th>
                  <th>Studying</th>
                  <th>Status</th>
                  <th>Created By</th>
                  <th>Created On</th>
                  <th width="180">Action</th>
                </tr>
              </thead>

              @foreach($students as $value)

              <tr>
                <td>
                  <input class="add_checkbox" type="checkbox" onchange="check(this)" value="{{$value->id}}" {{check($value->id)}}/>
                </td>
                <td>{{$value->id}}</td>
                <td>{{$value->name}}</td>
                <td>{{$value->email}}</td>
                <td>{{$value->contact}}</td>
                <td>{{$value->studying}}</td>
                <td>
                  @if($value->status == "Active")
                  <span class="label label-success">{{$value->status}}</span>
                  @else
                  <span class="label label-warning">{{$value->status}}</span>
                  @endif
                </td>
                <td>{{ $value->admin_name }}</td>
                <td>{{ $source->dtformat($value->created_at) }}</td>
                <td>
                  <a href="{{route('student.show', $value->id)}}" class="btn btn-info" title="User Details"><i class="fa fa-file-text"></i></a>
                  <a href="{{route('student.edit', $value->id)}}" class="btn btn-warning" title="Edit this User"><i class="fa fa-edit"></i></a>
                  @if(Auth::user()->AuthorizeRoles(['SuperAdmin', 'Admin']))
                  <a href="{{route('student.login-by-id', $value->id)}}" class="btn btn-success" title="Login to this account" target="_blank"><i class="fa fa-user"></i></a>
                  @endif
                  @if($value->status == 'Inactive')
                  <a href="{{route('send.verify.email', $value->id)}}" class="btn btn-default" onclick="return confirm('Are you sure you want to resend email verification to this student?')" title="Resend verification email."><i class="fa fa-envelope-o"></i></a>
                  @endif
                  @if($value->status == 3)
                  {{-- <a href="/admin/user/{{$value->id}}/restore" class="label label-success" title="Restore the account" onclick="return confirm('Are you sure you want to restore the account?')"><i class="fa fa-undo"></i></a> --}}
                  @endif
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