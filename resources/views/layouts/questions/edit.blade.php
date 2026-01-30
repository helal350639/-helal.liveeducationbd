@php
$value = $question;
$courseIds = $deptIds = $semIds = $semSubIds = $subIds = $chaptIds = $filterIds = [];
foreach($question->courses as $val)
{
    array_push($courseIds, $val->id);
}
foreach($question->departments as $val)
{
    array_push($deptIds, $val->id);
}
foreach($question->semesters as $val)
{
    array_push($semIds, $val->id);
}
foreach($question->subjects as $val)
{
    array_push($subIds, $val->id);
}
foreach($question->chapters as $val)
{
    array_push($chaptIds, $val->id);
}
foreach($question->filters as $val)
{
    array_push($filterIds, $val->id);
}
@endphp

@extends('dashboard')
@section('stylesheets')
<link href="/assets/summernote/summernote.min.css" rel="stylesheet">
@endsection
@section('title', 'Edit Question')
@section('content')
<style>
    .file_item{display: inline-block; margin-bottom:15px; border:1px solid #ddd}
    .file_action{width:80%;text-align:center}
    .file_action span{cursor: pointer;}
</style>
<section class="content-header">
  <h1>Edit Question</h1>
  <ol class="breadcrumb">
    <li><a href="#"><i class="fa fa-dashboard"></i>Questions</a></li>
    <li class="active">Edit Question</li>
</ol>
</section>

<!-- Main content -->
<section class="content">
  <div class="row"> <!-- left column -->
    <div class="col-md-12"> <!-- general form elements -->
      <div class="box box-primary">
        <div class="box-header with-border">
            <h3 style="color: #800" class="box-title">Question Details</h3>
        </div>
        <form action="{{route('question.update', $question)}}" method="POST" enctype="multipart/form-data" id="mcq_form">
            @csrf
            @method('PUT')
            <div class="box-body">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="type"><span style="font-size:18px" >Question Type (*)</span> (MCQ/Written/Video)</label>
                        <select class="form-control" name="type" id="type" required onchange="selectType(this)">
                            {{-- <option value="">Select One</option> --}}
                            <option value="{{$value->type}}">{{$value->type}}</option>
                            {{-- <option value="MCQ" {{$value->type == 'MCQ'? 'selected': ''}}>MCQ</option>
                            <option value="Written" {{$value->type == 'Written'? 'selected': ''}}>Written</option>
                            <option value="Video" {{$value->type == 'Video'? 'selected': ''}}>Video</option> --}}
                        </select>
                    </div>
                </div>
                {{-- <div class="col-md-12">
                    <hr>
                    <label for="">CATEGORIES</label>
                </div> --}}
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="name">Course Name (*)</label>
                        <select class="form-control select2" multiple name="course_id[]" id="course_id" onchange="getDepartments(this); getFilters(this)" required>
                            @foreach($courses as $val)
                            <option value="{{$val->id}}" {{in_array($val->id, $courseIds)? 'selected':''}}>{{$val->name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="name">Department Name (*)</label>
                        <select class="form-control select2" multiple name="department_id[]" id="department_id" onchange="getSemesters(this); getSubjects(this)" required>
                            @foreach($departments as $val)
                            <option value="{{$val->id}}" {{in_array($val->id, $deptIds)? 'selected':''}}>{{$val->name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                @if($value->semesters)
                <div class="col-md-12" id="semester">
                    <div class="form-group">
                        <label for="name">Semesters</label>
                        <select class="form-control select2" multiple name="semester_id[]" id="semester_id" onchange="getSemSubjects(this)">
                            @foreach($semesters as $val)
                            <option value="{{$val->id}}" {{in_array($val->id, $semIds)? 'selected':''}}>{{$val->name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                @endif
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="name">Subject</label>
                        <select class="form-control select2" multiple name="subject_id[]" id="subject_id" onchange="getChapters(this)">
                            @foreach($subjects as $val)
                            <option value="{{$val->id}}" {{in_array($val->id, $subIds)? 'selected':''}}>{{$val->name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="chapter">Chapter</label>
                        <select class="form-control select2" multiple name="chapter_id[]" id="chapter_id">
                            @foreach($chapters as $val)
                            <option value="{{$val->id}}" {{in_array($val->id, $chaptIds)? 'selected':''}}>{{$val->name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="title">Question Title (*)</label>
                        <textarea class="form-control editor" name="title" id="title" rows="3" required>{{$value->title}}</textarea>
                    </div>
                    <p class="text-danger pull-right">Image types must be: .jpeg, .jpg, .png and Max size: 500KB</p>
                </div>
                <div class="clearfix"></div>
                <div class="col-md-12" id="Answer">
                    @if($value->type == 'MCQ')
                    <div class="form-group">
                        <label for="mcq_items">MCQ Items: 
                         <button type="button" class="btn btn-sm btn-success" data-toggle="modal" data-target="#addMultiMcq"> <i class="fa fa-list"> </i> Add Many</button>
                        <button type="button" class="btn btn-sm btn-info" onclick="addOneField()"><i class="fa fa-plus"></i> Add One</button>
                    </label><br>
                    </div>
                    <p class="text-danger">Image types must be: .jpeg, .jpg, .png and Max size: 500KB</p>
                    <div class="col-md-8 no-padding" id="multiItems">
                        @foreach($value->mcqitems as $key => $val)
                        <div class="form-group">
                            <div class="input-group">
                                <input type="hidden" name="itemid[]" value="{{$val->id}}">
                                <span class="input-group-addon label-danger" onclick="this.parentNode.parentNode.remove()"><i class="fa fa-close"></i></span>
                                <textarea class="form-control short_editor" name="items[]" id="items{{$val->id}}" rows="3">{{$val->item}}</textarea>
                                <span class="input-group-addon">
                                    <img src="{{$val->image}}" alt="" width="54">
                                    <input style="display: none" type="file" name="item_img[]" id="item_{{$val->id}}" onchange="showImg(this)"/>
                                    <label for="item_{{$val->id}}"><i class="fa fa-image"></i></label>
                                </span>
                                <span class="input-group-addon">
                                    <input type="radio" name="correct[]" onchange="checkAns()" required value="{{$key}}" {{$val->correct_answer == 1? 'checked':''}} />
                                </span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @elseif($value->type == 'Written')
                    <div class="col-md-12 no-padding">
                        <div class="form-group">
                            <label for="">Answer</label>
                            <textarea class="form-control editor" name="answer" id="answer" rows="7" placeholder="Write your answer here...">{{$value->answer}}</textarea>

                            <div id="fileArea" style="display: inline-block;">
                                @foreach($value->answerfiles as $val)
                                @if(substr($val->file, -3) == 'pdf')
                                <div class="file_item">
                                    <a target="_blank" href="{{$val->file}}"><img src="/img/file.png" alt="" width="90"></a>
                                    <div class="file_action">
                                        <label style="width:50%" for="{{$val->id}}"><i class="fa fa-image"></i></label>
                                        <span fileid="{{$val->id}}" onclick="removeFile(this)"><i class="fa fa-close text-danger"></i></span>
                                    </div>
                                    <input id="{{$val->id}}" type="file" style="display: none" onchange="changeFile(this)">
                                </div>
                                @else
                                <div class="file_item">
                                    <img width="100" src="{{$val->file}}" alt="">
                                    <div class="file_action">
                                        <label style="width:50%" for="{{$val->id}}"><i class="fa fa-image"></i></label>
                                        <span fileid="{{$val->id}}" onclick="removeFile(this)"><i class="fa fa-close text-danger"></i></span>
                                    </div>
                                    <input id="{{$val->id}}" type="file" style="display: none" onchange="changeFile(this)">
                                </div>
                                @endif
                                @endforeach
                            </div>
                            <label id="filelabel" for="0" class="btn btn-info"><span><i class="fa fa-plus"> </i> Add File</span><input id="0" style="display: none" type="file" onchange="addFile(this)"></label>
                        </div>
                    </div>
                    @else
                    <div class="col-md-4 no-padding">
                      <div class="form-group">
                        <label for="">Video URL Type</label>
                        <select class="form-control" name="url_type" id="url_type" placeholder="Video URL Type" required>
                          <option value="">Select One</option>
                          <option value="Youtube" {{$value->url_type == 'Youtube' ? 'selected' : ''}}>Youtube</option>
                          <option value="Google Drive" {{$value->url_type == 'Google Drive' ? 'selected' : ''}}>Google Drive</option>
                        </select>
                      </div>
                    </div>
                    <div class="col-md-8 no-padding">
                      <div class="form-group">
                          <label for="">Input Video URL</label>
                          <input type="url" class="form-control" name="video" id="video" placeholder="https://" required value="{{$value->url_type == 'Youtube' ? 'https://www.youtube.com/watch?v='.$value->video : 'https://drive.google.com/file/d/'.$value->video.'/preview'}}">
                          </div>
                      </div>
                    @endif
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="">Explanation</label>
                        <textarea class="form-control editor" name="explanation" id="explanation">{{$value->explanation}}</textarea>
                    </div>
                </div>
                <div class="col-md-12">
                    <hr>
                    <label for="">FILTERS:</label>
                </div>
                <div class="col-md-12 no-padding" id="filters">
                    @foreach($filters as $filter)
                    @if(!is_null($filter->subfilter))
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="">{{$filter->name}}</label>
                                <select name="filter[]" id="" class="form-control select2" multiple>
                                    @foreach($filter->subfilter as $val)
                                    <option value="{{$val->id}}" {{in_array($val->id, $filterIds)? 'selected':''}}>{{$val->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    @endif
                    @endforeach
                </div>
                <div class="col-md-12"><hr>
                    <label for="">CREATE LABELS:</label>
                </div>
                <div class="col-md-12 no-padding">
                    @foreach($labels as $label)
                      @if(!is_null($label->subfilter))
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="">{{$label->name}}</label>
                                <select class="form-control select2 label-filters">
                                    <option value="">Select One</option>
                                    @foreach($label->subfilter as $val)
                                    <option value="{{$val->id}}">{{$val->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                      @endif
                    @endforeach
                </div>
                <div class="col-md-12 no-padding" id="label">
                    <button type="button" class="btn btn-info" onclick="addLabel(this)" style="margin-left:15px; margin-bottom:15px">
                      <i class="fa fa-plus"></i> Add Label
                    </button>
                    <div class="clearfix">
                      <label style="margin-left: 15px">LABELS</label>
                    </div>
                    @if($value->getlabels)
                      @foreach($value->getlabels as $val)
                      <div class="col-md-4">
                          <div class="form-group">
                              <div class="input-group">
                                  <input type="text" name="label[]" value="{{$val->label}}" class="form-control" placeholder="Label Name">
                                  <span class="input-group-addon" onclick="this.parentNode.parentNode.parentNode.remove()"><i class="fa fa-close text-danger"></i></span>
                              </div>
                              <input type="hidden" name="labelid[]" value="{{$val->id}}">
                          </div>
                      </div>
                      @endforeach
                    @endif
                </div>
                <div class="col-md-12">
                  <hr>
                    <div class="form-group">
                        <label for="status">Status</label>
                        <br>
                        <label>
                          <input type="radio" name="status" class="form-radio" value="Active" {{$value->status == 'Active'? 'checked':''}}/> Active &nbsp; 
                        </label>
                        <label> 
                          <input type="radio" name="status" class="form-radio" value="Inactive" {{$value->status == 'Inactive'? 'checked':''}}/> Inactive
                        </label>
                    </div>
                </div>
            </div> <!-- /.box body -->
            <div class="box-footer">
                <button type="submit" class="btn btn-primary pull-right" id="submit">
                  <i class="fa fa-save"></i> Save
                </button>
            </div>
        </form>
      </div> <!-- /.box -->
    </div> <!--/.col (left) -->
  </div> <!-- /.row -->
</section> <!-- /.content -->

{{-- modal --}}
<div class="modal fade" id="addMultiMcq">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">
                <textarea class="form-control" name="" id="multiLineText" cols="30" rows="10" placeholder="Input text in line"></textarea>
            </div>
            <div class="modal-footer">
                <button class="btn btn-warning" data-dismiss="modal">Cancel</button>
                <button class="btn btn-success" onclick="addMulitMcq()" data-dismiss="modal">Done</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="showMessage">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body" id="msgArea"></div>
            <div class="modal-footer" id="msg_footer">
                <a class="btn btn-warning" href="{{route('question.create')}}">Reset</a>
                <button class="btn btn-primary" onclick="msgHide()">Continue</button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">

function editor()
{
  $('.editor').summernote({
      height: 150,
      toolbar: [
        ['script', ['subscript', 'superscript']]
    ]
  });
}

// add labels
function addLabel(e)
{
    let label_value = '';
    let label_count = 0;
    let lab = document.getElementById('label');
    let labs = document.getElementsByClassName('label-filters');
    for(let x = 0; labs.length > x; x++)
    {
        let labText = labs[x].options[labs[x].selectedIndex];
        if(labText.value != 0)
        {
            label_value += labText.text+' ';
            label_count++;
        }
    }

    if(label_count == 0)
    {
        alert('Please select source for label');
    }
    else
    {
        let label = document.createElement('div');
        label.setAttribute('class', 'col-md-4');
        label.innerHTML = '<div class="form-group"><div class="input-group"><input type="text" name="label[]" value="'+label_value+'" class="form-control" placeholder="Label Name"><span class="input-group-addon" onclick="this.parentNode.parentNode.parentNode.remove()"><i class="fa fa-close text-danger"></i></span></div></div>';
        e.parentNode.appendChild(label);
    }
}

// on upload show image
function showImg(e)
{
    if(e.parentNode.firstElementChild.tagName == 'IMG')
    {
        e.parentNode.firstElementChild.src = window.URL.createObjectURL(e.files[0]);
    }
    else
    {
        let img = document.createElement('img');
        img.src = window.URL.createObjectURL(e.files[0]);
        img.setAttribute('width', 54);
        img.alt = '';
        e.parentNode.prepend(img);
    }
    
}
// check correct answers
function checkAns()
{
    let answers = document.getElementsByName('correct[]');
    for(let x = 0; answers.length > x; x++)
    {
        answers[x].value = x;        
    }
}

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
    
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

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
                $("#department_id").html('')
                $("#semester_id").html('')
                $("#subject_id").html('')
                $("#chapter_id").html('')
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
    let department = document.getElementById('department_id');
    let preElm = department.parentNode.parentNode;

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $.ajax({
        type: 'GET', //THIS NEEDS TO BE GET
        url: '/get_semesters/' + ids,
        success: function (data) {

            var obj = JSON.parse(JSON.stringify(data));
            var options = '';

            $.each(obj['data'], function (key, val) {
                options += '<option value="'+val.id+'" '+check(val.id)+'>'+val.name+'</option>';
            });

            if(options != "")
            {
                if(preElm.nextElementSibling.id == 'semester')
                {
                    $("#semester_id").html(options)
                }
                else
                {
                    let elm = document.createElement('div');
                    elm.setAttribute('class', 'col-md-12');
                    elm.setAttribute('id', 'semester');
                    elm.innerHTML = '<div class="form-group">'+
                            '<label for="name">Semester Name</label>'+
                            '<select class="form-control select2" multiple name="semester_id[]" id="semester_id" onchange="getSemsSubjects(this)">'+ options +
                                '</select>'+
                            '</div>';
                    preElm.after(elm);
                }

                // select2 execution
                $(function(){ $('.select2').select2(); });
                
            }else{
                $("#semester_id").html('')
                $("#subject_id").html('')
                $("#chapter_id").html('')
                
                if(preElm.nextElementSibling.id == 'semester')
                {
                    preElm.nextElementSibling.remove()
                }
            }
        },
        error: function(data) { 
            console.log('data error');
        }
    });

    
}

function getSubjects(e)
{
    const selectedIds = '{{implode(",", $subIds)}}';
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

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $.ajax({
        type: 'GET', //THIS NEEDS TO BE GET
        url: '/get_subjects/' + ids,
        success: function (data) {

            var obj = JSON.parse(JSON.stringify(data));
            var options = '';

            $.each(obj['data'], function (key, val) {
                options += '<option value="'+val.id+'" '+check(val.id)+'>'+val.name+'</option>';
            });

            if(options != ""){
                $("#subject_id").html(options)
            }else{
                $("#subject_id").html('')
                $("#chapter_id").html('')
            }
        },
        error: function(data) { 
                console.log('data error');
        }
    });
}

function getSemsSubjects(e)
{
    const selectedIds = '{{implode(",", $subIds)}}';
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

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $.ajax({
        type: 'GET', //THIS NEEDS TO BE GET
        url: '/get_sems_subjects/' + ids,
        success: function (data) {

            var obj = JSON.parse(JSON.stringify(data));
            var options = '';

            $.each(obj['data'], function (key, val) {
                options += '<option value="'+val.id+'" '+check(val.id)+'>'+val.name+'</option>';
            });

            if(options != ""){
                $("#subject_id").html(options)
            }else{
                $("#subject_id").html('')
                $("#chapter_id").html('')
            }
        },
        error: function(data) { 
                console.log('data error');
        }
    });
}

function getChapters(e)
{
    const selectedIds = '{{implode(",", $chaptIds)}}';
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

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $.ajax({
        type: 'GET', //THIS NEEDS TO BE GET
        url: '/get_chapters/' + ids,
        success: function (data) {

            var obj = JSON.parse(JSON.stringify(data));
            var options = '';

            $.each(obj['data'], function (key, val) {
                options += '<option value="'+val.id+'" '+check(val.id)+'>'+val.name+'</option>';
            });

            if(options != ""){
                $("#chapter_id").html(options)
            }else{
                $("#chapter_id").html('')
            }
        },
        error: function(data) { 
                console.log('data error');
        }
    });
}

function getFilters(e)
{
    var course_id = e.options[e.options.selectedIndex].value;

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $.ajax({
        type: 'GET', //THIS NEEDS TO BE GET
        url: '/get_filters/' + course_id,
        success: function (data) {

            var obj = JSON.parse(JSON.stringify(data));
            var filtersHtml = '';
            let options = '';

            $.each(obj['data'], function (key, val)
            {
                $.each(val.sub_filter, function(key, sub)
                {
                    options += '<option value="'+sub.id+'">'+sub.name+'</option>';
                });

                filtersHtml += '<div class="col-md-6">'+
                    '<div class="form-group">'+
                        '<label for="">'+val.name+'</label>'+
                        '<select name="filter[]" id="'+val.id+'" class="form-control select2" multiple>'+options+'</select>'+
                    '</div>'+
                '</div>';

                options = '';

                $(function (){ $('.select2').select2() });
            });

            if(filtersHtml != ""){
                $("#filters").html(filtersHtml)
            }else{
                $("#filters").html('<label>Filter Not Found!</label>');
            }
        },
        error: function(data) { 
          console.log('data error');
        }
    });
}

// add multiple mcq items
function addMulitMcq()
{
    let = multiLineText = document.getElementById('multiLineText');
    let = multiItems = document.getElementById('multiItems');
    let Items = '';
    let mcqItems = multiLineText.value.split("\n");
    for(let x = 0; mcqItems.length > x; x++)
    {
        Items += '<div class="form-group">'+
                    '<div class="input-group">'+
                        '<span class="input-group-addon label-danger" onclick="this.parentNode.parentNode.remove()">'+
                        '<i class="fa fa-close"></i>'+
                        '</span>'+
                        '<textarea class="form-control editor" name="items[]" id="items" rows="3" >'+mcqItems[x]+'</textarea>'+
                        '<span class="input-group-addon">'+
                            '<input style="display: none" type="file" name="item_img[]" id="item_'+x+'"/>'+
                            '<label for="item_'+x+'"><i class="fa fa-image"></i></label>'+
                        '</span>'+
                        '<span class="input-group-addon">'+
                            '<input type="radio" name="correct[]" onchange="checkAns()" required/>'+
                        '</span>'+
                    '</div>'+
                '</div>';
    }

    multiItems.innerHTML = Items;

    editor();

};

function addOneField()
{
    let x = 10; x++;
    let = multiItems = document.getElementById('multiItems');
    let field = document.createElement('div');
    field.setAttribute('class', 'form-group');
    field.innerHTML = '<div class="input-group">'+
                        '<span class="input-group-addon label-danger" onclick="this.parentNode.parentNode.remove()">'+
                        '<i class="fa fa-close"></i>'+
                        '</span>'+
                        '<textarea class="form-control editor" name="items[]" id="items" rows="3" ></textarea>'+
                        '<span class="input-group-addon">'+
                            '<input style="display: none" type="file" name="item_img[]" id="item_'+x+'"/>'+
                            '<label for="item_'+x+'"><i class="fa fa-image"></i></label>'+
                        '</span>'+
                        '<span class="input-group-addon">'+
                            '<input type="radio" name="correct[]" onchange="checkAns()" required/>'+
                        '</span>'+
                      '</div>';
    multiItems.appendChild(field);

    editor();

}


function selectType(e)
{
    let Answer = document.getElementById('Answer');
    let type = e.options[e.selectedIndex].value;
    if(type == 'MCQ')
    {
        Answer.innerHTML = '<div class="form-group">'+
            '<label for="mcq_items">MCQ Items: '+
                '<button type="button" class="btn btn-sm btn-success" data-toggle="modal" data-target="#addMultiMcq"> <i class="fa fa-list"> </i> Add Many</button>&nbsp;'+
                '<button type="button" class="btn btn-sm btn-info" onclick="addOneField()"><i class="fa fa-plus"></i> Add One</button>'+
                '</label><br>'+
                '</div>'+
                '<p class="text-danger">Image types must be: .jpeg, .jpg, .png and Max size: 500KB</p>'+
                '<div class="col-md-8 no-padding" id="multiItems">'+
                    '<div class="form-group">'+
                        '<div class="input-group">'+
                            '<span class="input-group-addon label-danger" onclick="this.parentNode.parentNode.remove()">'+
                                '<i class="fa fa-close"></i>'+
                                '</span>'+
                                '<textarea class="form-control" name="items[]" id="items" rows="3"></textarea>'+
                                '<span class="input-group-addon">'+
                                    '<img src="" alt="" width="54">'+
                                    '<input style="display: none" type="file" name="item_img[]" id="item_1" onchange="showImg(this)"/>'+
                                    '<label for="item_1"><i class="fa fa-image"></i></label>'+
                                '</span>'+
                                '<span class="input-group-addon">'+
                                    '<input type="radio" name="correct[]" onchange="checkAns()" required/>'+
                                '</span>'+
                            '</div>'+
                        '</div>'+
                        '<div class="form-group">'+
                            '<div class="input-group">'+
                                '<span class="input-group-addon label-danger" onclick="this.parentNode.parentNode.remove()">'+
                                    '<i class="fa fa-close"></i>'+
                                '</span>'+
                                '<textarea class="form-control" name="items[]" id="items" rows="3"></textarea>'+
                                '<span class="input-group-addon">'+
                                    '<input style="display: none" type="file" name="item_img[]" id="item_2" onchange="showImg(this)"/>'+
                                    '<label for="item_2"><i class="fa fa-image"></i></label>'+
                                '</span>'+
                                '<span class="input-group-addon">'+
                                    '<input type="radio" name="correct[]" onchange="checkAns()" required/>'+
                                '</span>'+
                            '</div>'+
                        '</div>'+
                        '<div class="form-group">'+
                            '<div class="input-group">'+
                                '<span class="input-group-addon label-danger" onclick="this.parentNode.parentNode.remove()">'+
                                    '<i class="fa fa-close"></i>'+
                                '</span>'+
                                '<textarea class="form-control" name="items[]" id="items" rows="3"></textarea>'+
                                '<span class="input-group-addon">'+
                                    '<input style="display: none" type="file" name="item_img[]" id="item_3" onchange="showImg(this)"/>'+
                                    '<label for="item_3"><i class="fa fa-image"></i></label>'+
                                '</span>'+
                                '<span class="input-group-addon">'+
                                    '<input type="radio" name="correct[]" onchange="checkAns()" required/>'+
                                '</span>'+
                            '</div>'+
                        '</div>'+
                        '<div class="form-group">'+
                            '<div class="input-group">'+
                                '<span class="input-group-addon label-danger" onclick="this.parentNode.parentNode.remove()">'+
                                    '<i class="fa fa-close"></i>'+
                                '</span>'+
                                '<textarea class="form-control" name="items[]" id="items" rows="3"></textarea>'+
                                '<span class="input-group-addon">'+
                                    '<input style="display: none" type="file" name="item_img[]" id="item_4" onchange="showImg(this)"/>'+
                                    '<label for="item_4"><i class="fa fa-image"></i></label>'+
                                '</span>'+
                                '<span class="input-group-addon">'+
                                    '<input type="radio" name="correct[]" onchange="checkAns()" required/>'+
                                '</span>'+
                            '</div>'+
                        '</div>'+
                    '</div>';
    }
    else if(type == 'Written')
    {
        Answer.innerHTML = '<div class="col-md-12 no-padding">'+
            '<div class="form-group">'+
                '<label for="">Answer</label>'+
                '<textarea class="form-control editor" name="answer" id="answer" rows="7" placeholder="Write your answer here..." required ></textarea>'+
                '<label>Upload Answer Files:</label>'+
                '<input type="file" multiple name="answer_file[]" class="form-control">'
                '</div>'+
            '</div>';
    }
    else
    {
        Answer.innerHTML = '<div class="col-md-12 no-padding">'+
            '<div class="form-group">'+
                '<label for="">Input Video URL</label>'+
                '<input type="url" class="form-control" name="video" id="video" placeholder="https://www.youtube.com/watch?v=DFDDFE8dDFd" required>'+
                '</div>'+
            '</div>';
    }

    $(document).ready(function() {
        $('.editor').summernote({
            height: 150
        });
    });
}

// ------------------ store data to database ----------------
function submitMCQ(e)
{
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    let submit = document.getElementById('submit');
    submit.setAttribute('disabled', 'disabled');

    var formData = new FormData(e);
    $.ajax({
        url: '{{route("question.store")}}',
        type: 'POST',
        data: formData,
        success: function(data)
        {
            let msgs = '';
            let showMessage = document.getElementById('showMessage');
            let msgArea = document.getElementById('msgArea');
            let msg_footer = document.getElementById('msg_footer');
            if(msg_footer.firstElementChild.innerHTML == 'View')
            {
                msg_footer.firstElementChild.remove();
            }

            if(data.success == true)
            {
                let lastId = data.last_id;
                let viewbtn = document.createElement('a');
                viewbtn.setAttribute('target', '_blank');
                viewbtn.setAttribute('class', 'btn btn-info');
                viewbtn.setAttribute('href', '{{route("question.show",'')}}/'+lastId);
                viewbtn.innerHTML = 'View';
                msg_footer.prepend(viewbtn);

                msgs = data.message;

                showMessage.classList.add('in');
                showMessage.style.display = 'block';
                msgArea.innerHTML = '<label class="text-success">'+msgs+'</label>';
            }

            if(data.success == false)
            {
                $.each(data.errors, function(key, val){
                    msgs += val+'<br>';
                });

                showMessage.classList.add('in');
                showMessage.style.display = 'block';
                msgArea.innerHTML = '<label class="text-danger">'+msgs+'</label>';
            }
        },
        cache: false,
        contentType: false,
        processData: false,
        error: function(data){
            console.log(data);
        }
    });
}

function msgHide()
{
    let showMessage = document.getElementById('showMessage');
    showMessage.style.display = 'none';

    let submit = document.getElementById('submit');
    submit.removeAttribute('disabled');
}

// call get semester method
// getSemesters(document.getElementById('department_id'));

// on select new file do action
function addFile(e)
{
    let filearea = document.getElementById('fileArea');
    if(e.files[0].type == 'image/png' || e.files[0].type == 'image/jpg' || e.files[0].type == 'image/jpeg' || e.files[0].type == 'application/pdf')
    {
        $.ajaxSetup({
            headers: {
               'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
         });

        let formData = new FormData();
        formData.append('question_id', '{{$value->id}}');
        formData.append('file', e.files[0]);

        $.ajax({
            url: '{{route("answer-file.store")}}',
            type: 'POST',
            processData: false,
            contentType: false,
            cache: false,
            data: formData,
            success:function(data){
                if(data.success == 'true')
                {
                    // create new section
                    let html = '';
                    let div = document.createElement('div');
                    div.setAttribute('class', 'file_item');
                    if(e.files[0].type == 'application/pdf'){
                        html += '<a target="_blank" href="'+data.file.file+'"><img src="/img/file.png" width="90"></a><input id="'+data.file.id+'" style="display: none" type="file" onchange="changeFile(this)">';
                    }else{
                        html += '<img src="'+data.file.file+'" alt="" width="100">';
                    }
                    html += '<input id="'+data.file.id+'" style="display: none" type="file" onchange="changeFile(this)"><div class="file_action">'+
                        '<label style="width:50%" for="'+data.file.id+'"><i class="fa fa-image"></i></label>'+
                        '<span onclick="removeFile()" style="position: relative;"><i class="fa fa-close text-danger"></i></span>'+
                        '</div>';
                    div.innerHTML = html;
                    filearea.appendChild(div);
                }
            },
            error:function(data){
                console.log(data);
            }
        });
    }else{
        alert('Only .JPEG .JPG .PNG and .PDF file allowed.');
    }
}

function changeFile(e)
{
    let filearea = document.getElementById('fileArea');
    if(e.files[0].type == 'image/png' || e.files[0].type == 'image/jpg' || e.files[0].type == 'image/jpeg' || e.files[0].type == 'application/pdf')
    {
        $.ajaxSetup({
            headers: {
               'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
         });

        let formData = new FormData();
        formData.append('_method', 'PUT');
        formData.append('file', e.files[0]);

        let id = e.getAttribute('id');

        $.ajax({
            url: '{{route("answer-file.update", '')}}/'+id,
            type: 'POST',
            processData: false,
            contentType: false,
            cache: false,
            data: formData,
            success:function(data){
                if(data.success == 'true')
                {
                    // create new section
                    let html = '';
                    if(e.files[0].type == 'application/pdf'){
                        html += '<a target="_blank" href="'+data.file.file+'"><img src="/img/file.png" width="90"></a>';
                    }else{
                        html += '<img src="'+data.file.file+'" alt="" width="100">';
                    }
                    html += '<input id="'+data.file.id+'" style="display: none" type="file" onchange="changeFile(this)"><div class="file_action">'+
                        '<label style="width:50%" for="'+data.file.id+'"><i class="fa fa-image"></i></label>'+
                        '<span onclick="removeFile(this)" style="position: relative;"><i class="fa fa-close text-danger"></i></span>'+
                        '</div>';
                    e.parentNode.innerHTML = html
                }
            },
            error:function(data){
                console.log(data);
            }
        });
    }else{
        alert('Only .JPEG .JPG .PNG and .PDF file allowed.');
    }
}

function removeFile(e)
{ 
    let id = e.getAttribute('fileid');
    let access = confirm('Are you sure, you want to delete this file?');
    if(access)
    {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $.ajax({
            url: '{{route("answer-file.destroy", '')}}/'+id,
            type: 'POST',
            data:{_method: 'DELETE'},
            success:function(data){
                if(data.success == true){
                    e.parentNode.parentNode.remove();
                }
            },
            error:function(data){}
        });

    }
}

</script>
@endsection

@section('scripts')
<script src="/ckeditor/ckeditor.js"></script>
<script src="/assets/summernote/summernote.min.js"></script>
<script>
    // select 2
    $(function (){ $('.select2').select2() });

    //this script for text editor
    $(document).ready(function() {
        $('.editor').summernote({
            height: 150,
            toolbar: [
              // Default groups
              ['style', ['style']],
              ['font', ['bold', 'underline', 'clear']],
              // ['fontname', ['fontname']],
              ['fontsize', ['fontsize']],
              // ['color', ['color']],
              
              // 👉 Added here
              ['script', ['subscript', 'superscript']],
              
              ['para', ['ul', 'ol', 'paragraph']],
              ['table', ['table']],
              ['insert', ['link', 'picture', 'video', 'math']],
              ['view', ['fullscreen', 'codeview', 'help']]
          ]
        });
    });

    $(document).ready(function() {
        $('.short_editor').summernote({
            height: 100,
            toolbar: [
              ['script', ['subscript', 'superscript']]
          ]
        });
    });
</script>
@endsection