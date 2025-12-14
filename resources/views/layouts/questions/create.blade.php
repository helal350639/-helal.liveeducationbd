@extends('dashboard')
@section('stylesheets')
<link href="/assets/summernote/summernote.min.css" rel="stylesheet">
<style>
   span:has(> input[name="correct[]"]){padding:5px}
    input[name="correct[]"]{width:20px; height: 20px;}
    .loading{display:none; text-align: center; background:rgba(0,0,0,0.3);position:fixed; z-index:99999; top:0; right:0; bottom:0;left:0; padding-top:10%;}
    .loading span{font-size:42px; position: absolute;top:20px; right:20px}
</style>
@endsection
@section('title', 'Add New Question')
@section('content')
<section class="content-header">
  <h1>Add a Question</h1>
  <ol class="breadcrumb">
    <li><a href="#"><i class="fa fa-dashboard"></i>Questions</a></li>
    <li class="active">Add a Question</li>
</ol>
</section>

<!-- loading section -->
<div id="loading" class="loading">
    <span onclick="this.parentNode.style.display='none'"><i class="fa fa-times"></i></span>
    <img src="/img/loading.webp" alt="">
</div>

<!-- Main content -->
<section class="content">
  <div class="row"> <!-- left column -->
    <div class="col-md-12"> <!-- general form elements -->
      <div class="box box-primary">
        <div class="box-header with-border">
            <h3 style="color: #800" class="box-title">Question Details</h3>
        </div>
        <form onsubmit="event.preventDefault(); submitMCQ(this)" action="{{route('question.store')}}" method="POST" enctype="multipart/form-data" id="mcq_form">
            @csrf
            <div class="box-body">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="type"><span style="font-size:18px" >Question Type (*)</span> (MCQ/Written/Video)</label>
                        <select class="form-control" name="type" id="type" required onchange="selectType(this)">
                            <option value="">Select One</option>
                            <option value="MCQ">MCQ</option>
                            <option value="Written">Written</option>
                            <option value="Video">Video</option>
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
                            <option value="{{$val->id}}">{{$val->name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="name">Department Name (*)</label>
                        <select class="form-control select2" multiple name="department_id[]" id="department_id" onchange="getSemesters(this); getSubjects(this)" required>
                        </select>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="name">Subject</label>
                        <select class="form-control select2" multiple name="subject_id[]" id="subject_id" onchange="getChapters(this)">
                        </select>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="chapter">Chapter</label>
                        <select class="form-control select2" multiple name="chapter_id[]" id="chapter_id">
                        </select>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="title">Question Title (*)</label>
                        {{-- <textarea class="form-control editor" name="title" id="title" rows="3" required></textarea> --}}
                        <textarea class="ckeditor form-control" name="title" id="title" rows="3" required></textarea>

                        {{-- <div class="input-group"> --}}
                            {{-- <span class="input-group-addon">
                                <img src="" alt="" width="54">
                                <input type="file" id="q_title" name="image" style="display: none" onchange="showImg(this)" />
                                <label for="q_title"><i class="fa fa-image"></i></label>
                            </span>
                            <span class="input-group-addon">
                                <i class="fa fa-gear"></i>
                            </span> --}}
                        {{-- </div> --}}
                    </div>
                    <p class="text-danger pull-right">Image types must be: .jpeg, .jpg, .png and Max size: 500KB</p>
                </div>
                <div class="clearfix"></div>
                <div class="col-md-12" id="Answer"> </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="">Explanation</label>
                        <textarea class="form-control ckeditor" name="explanation" id="explanation"></textarea>
                    </div>
                </div>
                <div class="col-md-12">
                    <hr>
                    <label for="">FILTERS: </label>
                </div>
                <div class="col-md-12 no-padding" id="filters">
                    @foreach($filters as $filter)
                    @if(!is_null($filter->subfilter))
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="">{{$filter->name}}</label>
                                <div class="input-group">
                                    <select name="filter[]" id="" class="form-control select2" multiple>
                                        @foreach($filter->subfilter as $val)
                                        <option value="{{$val->id}}">{{$val->name}}</option>
                                        @endforeach
                                    </select>
                                    <span class="input-group-addon">
                                        <input type="checkbox">
                                    </span>
                                </div>
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
                        <div class="col-md-3">
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
                    <div class="col-md-3"><br>
                        <button type="button" class="btn btn-info" onclick="addLabel(this)" style="margin-left:15px; margin-bottom:15px"><i class="fa fa-plus"></i> Add Label</button>
                    </div>
                    <div class="clearfix"></div>
                    <label style="margin-left: 15px">LABELS</label>
                </div>
                <div class="col-md-12 no-padding" id="label">
                </div>
            </div> <!-- /.box body -->
            <div class="box-footer">
                {{-- <button type="button" onclick="resetFields()">Reset Fields</button> --}}
                <button type="btnSubmit" class="btn btn-primary pull-right" id="btnSubmit" onclick="formValidation()"><i class="fa fa-save"></i> Save</button>
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
            <div class="modal-body" id="msgArea" style="max-height:500px; overflow-y:auto"></div>
            <div class="modal-footer" id="msg_footer"></div>
        </div>
    </div>
</div>

<script type="text/javascript">
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
        lab.appendChild(label);
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
                options += '<option value="'+val.id+'">'+val.name+'</option>';
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
                options += '<option value="'+val.id+'">'+val.name+'</option>';
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
                options += '<option value="'+val.id+'">'+val.name+'</option>';
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
                options += '<option value="'+val.id+'">'+val.name+'</option>';
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
                options += '<option value="'+val.id+'">'+val.name+'</option>';
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
                        '<div class="input-group">'+
                            '<select name="filter[]" id="'+val.id+'" class="form-control select2" multiple>'+options+'</select>'+
                            '<span class="input-group-addon">'+
                                '<input type="checkbox">'+
                            '</span>'+
                        '</div>'+
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
    let mcqItems = multiLineText.value.trim().split("\n");
    for(let x = 0; mcqItems.length > x; x++)
    {
        Items += '<div class="form-group">'+
                    '<div class="input-group">'+
                        '<span class="input-group-addon label-danger" onclick="this.parentNode.parentNode.remove()">'+
                        '<i class="fa fa-close"></i>'+
                        '</span>'+
                        '<textarea class="form-control" name="items[]" id="items" rows="3" >'+mcqItems[x]+'</textarea>'+
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

}

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
                            '<textarea class="form-control" name="mcq_items[]" id="mcq_items" rows="3" ></textarea>'+
                            '<span class="input-group-addon">'+
                                '<input style="display: none" type="file" name="item_img[]" id="item_'+x+'"/>'+
                                '<label for="item_'+x+'"><i class="fa fa-image"></i></label>'+
                            '</span>'+
                            '<span class="input-group-addon">'+
                                '<input type="radio" name="correct[]" onchange="checkAns()" required/>'+
                                '</span>'+
                        '</div>';
    multiItems.appendChild(field);

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
                                '<textarea class="form-control ckeditor" name="items[]" id="items" rows="3"></textarea>'+
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
                '<textarea class="form-control editor" name="answer" id="answer" rows="7" placeholder="Write your answer here..." ></textarea>'+
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

function formValidation()
{
    // let title = document.getElementById('title');

    // if(title.value == ''){
    //     alert('Question title field is required.');
    // }
    title.focus();
}

// ------------------ store data to database ----------------

// if question is exist to the database then access will overwrite true.
let access = false;
function submitMCQ(e)
{
    //------------------
    let submit = document.getElementById('btnSubmit');
    let title = document.getElementById('title');
    
    let msgs = '';
    let showMessage = document.getElementById('showMessage');
    let msgArea = document.getElementById('msgArea');
    let msg_footer = document.getElementById('msg_footer');
    let loading = document.getElementById('loading');
    loading.style.display = 'block';

    var formData = new FormData(e);

    if(access == false)
    {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        // check question exist on the database
        $.ajax({
            url: '{{route("questions.title")}}',
            type: 'POST',
            data: {_token: formData.get('_token'), type: formData.get('type'), title: formData.get('title')},
            success:function(data){

                loading.style.display = 'none';

                if(data.questions.length > 0)
                {
                    let html = '<label class="text-danger">The Question already exists, are you sure, you want to confirm add new one?</label><hr>';
                    let title = '';
                    var question = JSON.parse(JSON.stringify(data.questions));

                    $.each(question, function(key, val)
                    {
                        title = 'প্রশ্ন নং- '+val.id+': '+val.title;

                        let answer = '';
                        if(val.type == 'MCQ')
                        {
                            answer += '<ul>';
                            $.each(val.mcqitems, function(key, item){
                                answer += '<li '+(item.correct_answer == true ?  'style="font-weight:bold"':'')+'>'+item.item+'</li>';
                            });
                            answer+= '</ul>';
                        }
                        else
                        {
                            answer = val.answer;
                        }
                        html += '<div class="panel panel-warning">'+
                            '<div class="panel-heading"><b>'+title+'</b></div>'+
                            '<div class="panel-body">'+answer+'</div>'+
                            '<div class="panel-footer">'+
                                '<a target="_blank" class="btn btn-sm btn-info" href="{{route("question.show", "")}}/'+val.id+'">View </a> '+
                                '&nbsp; <a target="_blank" class="btn btn-sm btn-warning" href="/question/'+val.id+'/edit"> Edit</a>'+
                            '</div>'+
                        '</div>';
                    });

                    showMessage.classList.add('in');
                    showMessage.style.display = 'block';
                    msgArea.innerHTML = html;
                    msg_footer.innerHTML = '<button class="btn btn-primary" onclick="resetFields(); msgHide();">Close</button>'+
                    '<button class="btn btn-warning" onclick="resubmitForm(); msgHide();">Confirm</button>';
                }
                else
                {
                    access = true;
                    resubmitForm();
                    // console.log('No, questions has not');
                }
                // console.log(data);
            },
            error:function(data){
                console.log(data);
            }
        });
    }
    else
    {

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $.ajax({
            url: '{{route("question.store")}}',
            type: 'POST',
            data: formData,
            success: function(data)
            {
                loading.style.display = 'none';
                
                if(data.success == true)
                {
                    let html = '';
                    let lastId = data.last_id;

                    let val = JSON.parse(JSON.stringify(data.question));

                    html += '<label class="text-success">'+data.message+'</label><hr>';

                    title = 'প্রশ্ন নং- '+val.id+': '+val.title;

                        let answer = '';
                        if(val.type == 'MCQ')
                        {
                            answer += '<ul>';
                            $.each(val.mcqitems, function(key, item){
                                answer += '<li '+(item.correct_answer == true ?  'style="font-weight:bold"':'')+'>'+item.item+'</li>';
                            });
                            answer+= '</ul>';
                        }
                        else
                        {
                            answer = val.answer;
                        }
                        html += '<div class="panel panel-warning">'+
                            '<div class="panel-heading"><b>'+title+'</b></div>'+
                            '<div class="panel-body">'+answer+'</div>'+
                            '<div class="panel-footer">'+
                                '<a target="_blank" class="btn btn-sm btn-info" href="{{route("question.show", "")}}/'+val.id+'">View </a> '+
                                '&nbsp; <a target="_blank" class="btn btn-sm btn-warning" href="/question/'+val.id+'/edit"> Edit</a>'+
                            '</div>'+
                        '</div>';

                    showMessage.classList.add('in');
                    showMessage.style.display = 'block';
                    msgArea.innerHTML = html;
                    msg_footer.innerHTML = '<a class="btn btn-warning" href="{{route("question.create")}}">Reset</a>'+
                    '<button class="btn btn-primary" onclick="resetFields(); msgHide();">Continue</button>';
                }

                if(data.success == false)
                {
                    $.each(data.errors, function(key, val){
                        msgs += val+'<br>';
                    });

                    showMessage.classList.add('in');
                    showMessage.style.display = 'block';
                    msgArea.innerHTML = '<label class="text-danger">'+msgs+'</label>';
                    msg_footer.innerHTML = '<a class="btn btn-warning" href="{{route("question.create")}}">Reset</a>'+
                    '<button class="btn btn-primary" onclick="msgHide();">Continue</button>';
                }
            },
            cache: false,
            contentType: false,
            processData: false,
            error: function(data){
                console.log(data);
            }
        });
        access = false;
    }
    
}

function resubmitForm()
{
    // change access by true
    access = true;
    submitMCQ(document.getElementById('mcq_form'));
}

function msgHide()
{
    let showMessage = document.getElementById('showMessage');
    showMessage.style.display = 'none';

    let submit = document.getElementById('btnSubmit');
    submit.removeAttribute('disabled');

    document.getElementById("mcq_form").scrollIntoView();

    // console.log();
}

function resetFields()
{
    let label = document.getElementById('label');
    // label.innerHTML = '';

    // reset all filters fields
    let filters = document.getElementsByName('filter[]');
    for(let x = 0; filters.length > x; x++)
    {
        if(filters[x].nextElementSibling.nextElementSibling.firstElementChild.checked == false)
        {
            filters[x].value = [];
        }
    }

    //reset all label fields
    // let labelFields = document.getElementsByClassName('label-filters');
    // for(let l = 0; labelFields.length > l; l++)
    // {
    //     labelFields[l].value = [];
    // }

    //reset question title and explanation fields
    // $('#title').summernote('reset');
    // $('#explanation').summernote('reset');

    $(function (){ $('.select2').select2() });
}

</script>
@endsection

@section('scripts')
<script src="/assets/summernote/summernote.min.js"></script>
<script>
    // select 2
    $(function (){ $('.select2').select2() });

    //this script for text editor
    $(document).ready(function() {
        $('.editor').summernote({
            height: 150
        });
    });
</script>

<script src="/ckeditor/ckeditor.js"></script>
<script>
  CKEDITOR.replace('ckeditor');
</script>

@endsection