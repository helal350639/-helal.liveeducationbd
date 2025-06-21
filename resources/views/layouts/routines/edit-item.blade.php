@extends('dashboard')
@section('title', 'Edit Routine Item')
@section('stylesheets')
<link href="/assets/summernote/summernote.min.css" rel="stylesheet">
<style>
  .text-right{text-align: right}
</style>
@endsection
@section('content')
<section class="content-header">
  <h1>Edit a Routine Item</h1>
  <ol class="breadcrumb">
    <li><a href="#"><i class="fa fa-dashboard"></i> Routines</a></li>
    <li class="active">Edit Routine</li>
</ol>
</section>

<!-- Main content -->
<section class="content">
  <div class="row">
    <div class="col-md-12">
      <div class="box box-info">
        <div class="box-header with-border">
            <h3 style="color: #800" class="box-title">{{$routine->name}}</h3>
            <div class="toolbar-icon pull-right text-right">
              
              <a href="{{route('routine.index')}}" title="View" class="label label-success"><i class="fa fa-list"></i></a>
            </div>
        </div>
        <table class="table table-bordered">
          <tr>
            <th>ID</th>
            <th>Exam Name/No</th>

            @foreach($subjects as $subject)
            <th>{{$subject->name}}</th>
            @endforeach

            @foreach($departments as $department)
            <th>{{$department->name}}</th>
            @endforeach

            <th>Date & Time</th>
            <th>Action</th>
          </tr>

          @foreach($routine_rows as $key => $item)
          <tr>
            <td>{{$item->id}}</td>
            <td>{{$item->paper_name}}</td>

            @foreach($item->subjects as $subject)
            <td>{!!$subject->chapter!!}</td>
            @endforeach

            @foreach($item->departments as $department)
            <td>{!!$department->details!!}</td>
            @endforeach

            <td>{{$item->exam_at}}</td>
            <td>
              <a class="btn btn-danger btn-sm" href="{{route('routine.item.delete', $item->id)}}" onclick="return confirm('Are you sure, you want to delete this?')">
                <i class="fa fa-trash"></i>
              </a>
            </td>
          </tr>
          @endforeach

        </table>
      </div>
      <div class="clearfix"></div>
    </div>
  </div>

  <div class="row"> <!-- left column -->
    <div class="col-md-12"> <!-- general form elements -->
      <div class="box box-primary">
        <div class="box-header with-border">
            <h3 style="color: #800" class="box-title">{{$routine->name}}</h3>
        </div>
        <form action="{{route('routine.item.update', $routine)}}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <input type="hidden" name="routine_id" value="{{$routine->id}}">
            <div class="box-body">
                <div class="row">

                  <input type="hidden" name="routine_id" value="{{$routine->id}}">

                  <div class="col-md-6">
                    <div class="form-group row">
                      <label class="col-md-4 text-right" for="">Exam No</label>
                      <div class="col-md-8">
                        <input class="form-control" name="paper_name" id="paper_name" required>
                      </div>
                    </div>

                  @foreach($subjects as $subject)
                    <div class="form-group row">
                      <label class="col-md-4 text-right" for="">{{$subject->name}}</label>
                      <div class="col-md-8">
                        <input type="hidden" name="subject_id[]" value="{{$subject->id}}" required value="">
                        <textarea class="form-control editor" name="subject_item[]" rows="5"></textarea>
                      </div>
                    </div>

                  @endforeach
                  </div>
                  
                  <div class="col-md-6">

                  @foreach($departments as $department)
                    <div class="form-group row">
                      <label class="col-md-4 text-right" for="">{{$department->name}}</label>
                      <div class="col-md-8">
                        <input type="hidden" name="department_id[]" value="{{$department->id}}" required>
                        <textarea class="form-control editor" name="department_item[]" rows="5"></textarea>
                      </div>
                    </div>

                  @endforeach
                  
                    <div class="form-group row">
                      <label class="col-md-4 text-right" for="">Date & Time</label>
                      <div class="col-md-8">
                        <input type="datetime-local" class="form-control" name="exam_at" id="exam_at" required>
                      </div>
                    </div>
                  </div>
                  

                  {{-- <form method="POST" action="#">
                    @csrf
                    <table id="dynamicTable" class="table table-bordered">
                        <thead>
                            <tr id="columnHeaders">
                                <!-- Will be filled by JS -->
                            </tr>
                        </thead>
                        <tbody id="tableBody">
                            <!-- Dynamic rows -->
                        </tbody>
                    </table>
                
                    <button type="button" onclick="addColumn()">Add Column</button>
                    <button type="button" onclick="addRow()">Add Row</button>
                    <br><br>
                    <button type="submit">Save Table</button>
                </form> --}}
                
                {{-- <script>
                    let columnCount = 0;
                    let rowCount = 0;
                
                    function addColumn() {
                        columnCount++;
                        const colHeader = document.createElement('th');
                        colHeader.innerHTML = `Column ${columnCount}`;
                        document.getElementById('columnHeaders').appendChild(colHeader);
                
                        const rows = document.getElementById('tableBody').rows;
                        for (let i = 0; i < rows.length; i++) {
                            const cell = rows[i].insertCell(-1);
                            cell.innerHTML = `<input type="text" name="table[${i}][${columnCount - 1}]" class="form-control" />`;
                        }
                    }
                
                    function addRow() {
                        const row = document.createElement('tr');
                        let cols = '';
                        for (let j = 0; j < columnCount; j++) {
                            cols += `<td><input type="text" name="table[${rowCount}][${j}]" class="form-control" /></td>`;
                        }
                        row.innerHTML = cols;
                        document.getElementById('tableBody').appendChild(row);
                        rowCount++;
                    }
                
                    // Optionally add one row/column on load
                    window.onload = () => {
                        addColumn();
                        addRow();
                    };
                </script> --}}
                
                </div>
            </div> <!-- /.box body -->
            <div class="box-footer">
                <button type="submit" class="btn btn-primary pull-right"><i class="fa fa-plus"></i> Add</button>
            </div>
        </form>
      </div> <!-- /.box -->
    </div> <!--/.col (left) -->
  </div> <!-- /.row -->
</section> <!-- /.content -->
@endsection
@section('scripts')
<script src="/assets/summernote/summernote.min.js"></script>
<script>

    // on change course get batches
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
                    $("#batch_id").html(options)
                }else{
                    $("#batch_id").html('')
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
    
    //this script for text editor
    $(document).ready(function() {
        $('.editor').summernote({
          toolbar: [],
            height: 100
        });
    });

    //select 2
    $(function(){$('.select2').select2();});
</script>
@endsection