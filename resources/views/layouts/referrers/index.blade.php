@php
use \App\Http\Controllers\SourceCtrl;
$source = New SourceCtrl;
@endphp

@extends('dashboard')
@section('title', 'View All Referrers')
@section('content')
<!-- Content Header (Page header) -->
<section class="content-header">
  <h1>All Referrers</h1>
  <ol class="breadcrumb">
    <li><a href="#"><i class="fa fa-dashboard"></i>Dashboard</a></li>
    {{-- <li><a href="#">Tables</a></li> --}}
    <li class="active">All Referrers</li>
  </ol>
</section>
<!-- Main content -->
<section class="content">
  <div class="row">
    <div class="col-xs-12">
      <div class="box">
        <div class="box-header">
          <h3 class="box-title">List of Referrers</h3>
              <div class="box-tools">
                {{-- <form action="{{route('order.index')}}" method="GET" class="input-group input-group-sm" style="float:right; width: 150px;margin-left:15px">
                  <input type="text" name="search" class="form-control pull-right" placeholder="Name, Phone, Email" value="{{request('search')}}">

                  <div class="input-group-btn">
                    <button type="submit" class="btn btn-info"><i class="fa fa-search"></i></button>
                  </div>
                </form> --}}
              </div>
            </div>
            <!-- /.box-header -->
            <div class="box-body table-responsive no-padding">
              <table id="example1" class="table table-bordered table-hover">
                <tr>
                  <th>Id</th>
                  <th>Referrer Name</th>
                  <th>Batch</th>
                  <th>Student</th>
                  <th>Referrer Type</th>
                  <th>Bonus (Tk.)</th>
                  {{-- <th>Status</th>
                  <th width="100">Action</th> --}}
                </tr>
                @foreach($referrers as $value)
                <tr>
                  <td>{{$value->id}}</td>
                  <td>
                    @if($value->referrer_type == 'teacher')
                    {{ $value->teacher ? $value->teacher->name : '' }}
                    @else
                    {{ $value->referrer ? $value->referrer->name : '' }}
                    @endif
                  </td>
                  <td>{{$value->batch ? $value->batch->name : ''}}</td>
                  <td>{{$value->student ? $value->student->name : ''}}</td>
                  <td>{{$value->referrer_type}}</td>
                  <td>{{$value->referrer_bonus}}</td>
                  {{-- <td>
                    @if(Auth::user()->authorizeRoles(['SuperAdmin']))
                    <form style="display: inline" action="{{route('order.destroy', $value->id)}}" method="POST">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this one?')"><i class="fa fa-trash"></i></button>
                    </form>
                    @endif
                  </td> --}}
                </tr>
                @endforeach
              </table>
            </div>
            <!-- /.box-body -->
            <div class="box-footer clearfix">
              <div class="pagination-sm no-margin pull-right">
                {{$referrers->links()}}
              </div>
            </div>
          </div>
          <!-- /.box -->
        </div>
      </div>
    </section>
    <!-- /.content -->
    @endsection
{{-- @section('scripts')
  <script>
    $(function () {
      $('#example1').DataTable()
      $('#example2').DataTable({
        'paging'      : true,
        'lengthChange': false,
        'searching'   : false,
        'ordering'    : true,
        'info'        : true,
        'autoWidth'   : false
      })
    })
  </script>
@endsection --}}