@php
use \App\Http\Controllers\SourceCtrl;
$source = New SourceCtrl;
@endphp

@extends('dashboard')
@section('title', 'View Book Categories')
@section('content')
<style>
  .tools{text-align: right;}
</style>
<!-- Content Header (Page header) -->
<section class="content-header">
  <h1>Book Categories</h1>
  <ol class="breadcrumb">
    <li><a href="#"><i class="fa fa-dashboard"></i>Dashboard</a></li>
    <li class="active">Book Categories</li>
  </ol>
</section>
<!-- Main content -->
<section class="content">
  <div class="row">
    <div class="col-xs-12">

      <div class="box">
        <div class="box-header">
          <h3 class="box-title">List of Category</h3>
              <div class="box-tools">
                <a class="btn btn-sm btn-info">
                  <i class="fa fa-plus"></i> Add
                </a>
              </div>
            </div>
            <!-- /.box-header -->
            <div class="box-body table-responsive no-padding">
              <table id="example1" class="table table-bordered table-hover">
                <tr>
                  <th>Id</th>
                  <th>Name</th>
                  <th>Departments</th>
                  <th>Status</th>
                  <th width="60">Action</th>
                </tr>
              @foreach($bookCategories as $key => $value)
                <tr>
                  <td>{{$key+1}}</td>
                  <td>{{$value->name}}</td>
                  <td>
                    @foreach($value->departments as $dept)
                    <label class="label label-info">{{ $dept->name }}</label>
                    @endforeach
                  </td>
                  <td>
                    @if($value->status == 'Active')
                    <span class="label label-success">{{$value->status}}</span>
                    @else
                    <span class="label label-danger">{{$value->status}}</span>
                    @endif
                  </td>
                  <td>
                    <button class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editModal" data-id="{{$value->id}}" onclick="editForm(this)">
                      <i class="fa fa-edit"></i>
                    </button>
                  </td>
                </tr>
              @endforeach
              </table>
            </div>
            <!-- /.box-body -->
            {{-- <div class="box-footer clearfix">
              <div class="pagination-sm no-margin pull-right">
                {{$syllabus->links()}}
              </div>
            </div> --}}
          </div>
          <!-- /.box -->
        </div>
      </div>
    </section>
    <!-- /.content -->

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal">
      <div class="modal-dialog">
        <form action="#" id="editForm" data-id="" method="POST">
          @csrf
          @method('PUT')
          <div class="modal-content">
              <div class="modal-body">
                <h4>Edit Book Category</h4>
                <br>
                <div class="form-group">
                  <label for="">Category Name:</label>
                  <input type="text" name="name" class="form-control">
                </div>
                <div class="form-group">
                  <label for="">Departments:</label>
                  <select name="department" class="form-control select2" multiple>
                  </select>
                </div>
                <div class="form-group">
                  <label for="">Status:</label>
                  <select name="status" class="form-control">
                    <option value="Active">Active</option>
                    <option value="Deactive">Deactive</option>
                  </select>
                </div>
              </div>
              <div class="modal-footer">
                  <button class="btn btn-warning" data-dismiss="modal">Cancel</button>
                  <button class="btn btn-success" data-dismiss="modal" onclick="editFormSubmit(this)">Done</button>
              </div>
          </div>
        </form>
      </div>
  </div>

@endsection

@section('scripts')
  <script>
    const departments = @json($departments);
    function editForm(e)
    {
      const form = document.getElementById('editForm');

      //set id to the form
      form.dataset.id = e.dataset.id;

      $.ajax({
        type: 'GET',
        url: '{{route("book-category.show", "")}}/'+e.dataset.id,
        success: function(data){
          let departmentOptions = '';
          let statusOptions = '';

          form.elements.name.value = data.bookCategory.name;
          departments.forEach(d => {
            departmentOptions += '<option value="'+d.id+'" '+(data.departments.includes(d.id) ? 'selected':'')+'>'+d.name+'</option>';
          });
          form.elements.department.innerHTML = departmentOptions;

          // form.elements.status.options.forEach(s => {
          //   statusOptions += '<option value="'+s+'">'+s+'</option>';
          // })
          form.elements.status.append('<option value="'+data.bookCategory.status+'" selected>'+data.bookCategory.status+'</option>');
          // console.log(form.elements.status.options);
        },
        error: function(data){
          console.error(data);
        }
      });

    // select 2
    $(function (){ $('.select2').select2() });
    }

    function editFormSubmit(e)
    {
      const form = document.getElementById('editForm');
      let departmentIds = Array.from(form.elements.department.selectedOptions).map(({value}) => value);
      const formdata = new FormData(form);
      formdata.append('department', departmentIds);

      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
      });

      $.ajax({
        type: 'POST',
        url: '{{route("book-category.update", "")}}/'+form.dataset.id,
        data:formdata,
        processData: false,
        contentType: false,
        success: function(data){
          console.log(data);
        },
        error: function(data){
          console.error(data);
        }
      });

    }
  </script>
@endsection