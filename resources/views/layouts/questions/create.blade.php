@extends('dashboard')
@section('stylesheets')
<link href="/assets/summernote/summernote.min.css" rel="stylesheet">
<style>
   span:has(> input[name="correct[]"]){padding:5px}
    input[name="correct[]"]{width:20px; height: 20px;}
    .loading{display:none; text-align: center; background:rgba(0,0,0,0.3);position:fixed; z-index:99999; top:0; right:0; bottom:0;left:0; padding-top:10%;}
    .loading span{font-size:42px; position: absolute;top:20px; right:20px}

/* Only the 11 LEBD filter dropdowns. Other form controls remain untouched. */
.lebd-choice {position:relative;width:100%;min-width:0}
.lebd-choice-button {width:100%;min-height:34px;text-align:left;background:#fff;border:1px solid #d2d6de;border-radius:3px;padding:6px 28px 6px 10px;position:relative;cursor:pointer;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.lebd-choice-button:after {content:'▾';position:absolute;right:9px;top:6px;color:#777}
.lebd-choice-panel {display:none;position:absolute;z-index:1100;top:100%;left:0;right:0;background:white;border:1px solid #aaa;border-radius:3px;box-shadow:0 3px 9px #0002;padding:6px}
.lebd-choice.open .lebd-choice-panel {display:block}
.lebd-choice-search {width:100%;padding:6px 8px;border:1px solid #bbb;margin-bottom:5px;border-radius:3px}
.lebd-choice-list {max-height:230px;overflow-y:auto}
.lebd-choice-item {display:block;width:100%;text-align:left;background:white;border:0;padding:7px 8px;cursor:pointer;white-space:normal}
.lebd-choice-item:hover,.lebd-choice-item:focus {background:#e8f3fb}
.lebd-choice-empty {padding:9px;color:#777}

/* Checkboxes for the original question fields only; existing filter-chain UI unchanged. */
.lebd-top-keep { display:inline-flex; align-items:center; gap:5px; white-space:nowrap;
  font-weight:normal; margin:7px 0 0 2px; cursor:pointer; font-size:12px; color:#48556a; }
.lebd-top-keep input { margin:0; cursor:pointer; }
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
            <button type="button" id="lebd-csv-with-context" onclick="lebdGoToCsvWithContext()" class="btn btn-info btn-sm pull-right" style="margin-left:8px;"><i class="fa fa-upload"></i> Bulk CSV Upload</button>
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
                        <label class="lebd-top-keep" title="Keep this value after Save / Continue"><input type="checkbox" class="lebd-top-keep-box" data-keep-for="type" aria-label="Keep type selection"> <span>Keep</span></label>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="name">Course Name (*)</label>
                        <select class="form-control select2" multiple name="course_id[]" id="course_id" onchange="getDepartments(this)" required>
                            @foreach($courses as $val)
                            <option value="{{$val->id}}">{{$val->name}}</option>
                            @endforeach
                        </select>
                        <label class="lebd-top-keep" title="Keep this value after Save / Continue"><input type="checkbox" class="lebd-top-keep-box" data-keep-for="course_id" aria-label="Keep course_id selection"> <span>Keep</span></label>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="name">Department Name (*)</label>
                        <select class="form-control select2" multiple name="department_id[]" id="department_id" onchange="getSemesters(this); getSubjects(this)" required>
                        </select>
                        <label class="lebd-top-keep" title="Keep this value after Save / Continue"><input type="checkbox" class="lebd-top-keep-box" data-keep-for="department_id" aria-label="Keep department_id selection"> <span>Keep</span></label>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="name">Subject</label>
                        <select class="form-control select2" multiple name="subject_id[]" id="subject_id" onchange="getChapters(this)">
                        </select>
                        <label class="lebd-top-keep" title="Keep this value after Save / Continue"><input type="checkbox" class="lebd-top-keep-box" data-keep-for="subject_id" aria-label="Keep subject_id selection"> <span>Keep</span></label>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="chapter">Chapter</label>
                        <select class="form-control select2" multiple name="chapter_id[]" id="chapter_id">
                        </select>
                        <label class="lebd-top-keep" title="Keep this value after Save / Continue"><input type="checkbox" class="lebd-top-keep-box" data-keep-for="chapter_id" aria-label="Keep chapter_id selection"> <span>Keep</span></label>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="title">Question Title (*)</label>
                        {{-- <textarea class="form-control editor" name="title" id="title" rows="3" required></textarea> --}}
                        <textarea class="editor form-control" name="title" id="title" rows="3" required></textarea>

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
                        <textarea class="form-control editor" name="explanation" id="explanation"></textarea>
                    </div>
                </div>
                <div class="col-md-12">
                    <hr>
                    <label for="">FILTERS: </label>
                </div>
                {{-- Fix30: keep working server options and native select; custom searchable dropdown only. --}}
                @php
                    $lebdNames = ['প্রশ্ন টাইপ','কোর্সের নাম','প্রশ্ন টপিক','ডিপার্টমেন্ট নাম','বুক প্রকাশনির নাম','সাবজেক্ট নাম','চ্যাপ্টার নাম','প্রশ্ন সোর্স','সাব-সোর্স','পরীক্ষার সন','Question Quality filter'];
                    $lebdOrdered = \App\Models\Filter::where('status','Active')->orderBy('order_number')->orderBy('id')->get(['id']);
                    $lebdStages = collect($lebdNames)->map(function ($name, $i) use ($lebdOrdered) {
                        return ['step'=>$i+1,'label'=>$name,'filter_id'=>(int) optional($lebdOrdered->get($i))->id];
                    });
                    // On this LEBD-04 database, new options started at ID 19 (verified in phpMyAdmin).
                    // Never use MAX(id) as fallback: id > MAX(id) hides every existing option.
                    $lebdBoundary = 18;
                    $lebdNewItems = \App\Models\SubFilter::whereIn('filter_id', $lebdStages->pluck('filter_id')->filter()->all())
                        ->where('id','>', $lebdBoundary)->where('status','Active')
                        ->orderBy('id')->get(['id','filter_id','name','parent_id']);
                    $lebdLinks = collect();
                    if (\Illuminate\Support\Facades\Schema::hasTable('sub_filter_connections') && $lebdNewItems->isNotEmpty()) {
                        $lebdLinks = \Illuminate\Support\Facades\DB::table('sub_filter_connections')
                            ->whereIn('child_sub_filter_id',$lebdNewItems->pluck('id')->all())
                            ->get(['child_sub_filter_id','parent_sub_filter_id']);
                    }
                    $lebdLinkMap = [];
                    foreach ($lebdLinks as $link) {
                        $lebdLinkMap[(int)$link->child_sub_filter_id][] = (int)$link->parent_sub_filter_id;
                    }
                    foreach ($lebdNewItems as $item) {
                        if ($item->parent_id) $lebdLinkMap[(int)$item->id][] = (int)$item->parent_id;
                    }
                @endphp
                <div class="col-md-12 no-padding" id="filters">
                    @foreach($lebdStages as $stage)
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="lebd-filter-{{ $stage['step'] }}">{{ $stage['label'] }}</label>
                            <div class="input-group">
                                <select name="filter[]" id="lebd-filter-{{ $stage['step'] }}"
                                        class="form-control lebd-question-filter"
                                        data-position="{{ $stage['step'] }}" multiple style="display:none">
                                    @foreach($lebdNewItems->where('filter_id',$stage['filter_id']) as $item)
                                    <option value="{{ $item->id }}" data-parent-ids="{{ implode(',',array_unique($lebdLinkMap[(int)$item->id]??[])) }}"
                                        @if($stage['step'] > 1 && $stage['step'] < 11) hidden disabled @endif>{{ $item->name }}</option>
                                    @endforeach
                                </select>
                                <span class="input-group-addon"><input type="checkbox" title="Keep this filter selection when adding another question"></span>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                <div class="col-md-12" id="lebd-fresh-filter-notice" aria-live="polite">
                    <small class="text-muted">Loaded new sub-filters: {{ $lebdNewItems->count() }}; Question Type: {{ $lebdNewItems->where('filter_id', $lebdStages[0]['filter_id'])->count() }}.</small>
                </div>
                <div class="col-md-12"><hr>
                    <label for="">CREATE LABELS:</label>
                </div>
                <div class="col-md-12 no-padding">
                    {{-- Fix31: labels are selected from the same FRESH fixed-filter data as Fix30. --}}
                    @foreach([9 => 'সাব-সোর্স', 10 => 'পরীক্ষার সন'] as $lebdLabelStep => $lebdLabelTitle)
                        @php
                            $lebdLabelFilterId = (int) ($lebdStages[$lebdLabelStep - 1]['filter_id'] ?? 0);
                            $lebdLabelOptions = $lebdNewItems->where('filter_id', $lebdLabelFilterId);
                        @endphp
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="lebd-label-step-{{ $lebdLabelStep }}">{{ $lebdLabelTitle }}</label>
                                <select id="lebd-label-step-{{ $lebdLabelStep }}" class="form-control select2 label-filters">
                                    <option value="">Select One</option>
                                    {{-- Options are mirrored from the connected FILTERS above by JavaScript.
                                         Do not show unfiltered options before the complete chain is selected. --}}
                                </select>
                                <small class="text-muted" id="lebd-label-hint-{{ $lebdLabelStep }}">আগের Filter-গুলো নির্বাচন করলে Option আসবে।</small>
                            </div>
                        </div>
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
                <button class="btn btn-success" onclick="addMultiMcq()" data-dismiss="modal">Done</button>
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
        // Keep the original editable label[] payload; add an independent keep checkbox.
        label.innerHTML = '<div class="form-group"><div class="input-group"><input type="text" name="label[]" class="form-control" placeholder="Label Name"><span class="input-group-addon" title="Keep this label for the next question"><input type="checkbox" class="lebd-label-keep" aria-label="Keep this label for the next question"></span><span class="input-group-addon" role="button" title="Remove label" onclick="this.parentNode.parentNode.parentNode.remove()"><i class="fa fa-close text-danger"></i></span></div></div>';
        label.querySelector('input[name="label[]"]').value = label_value.trim();
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

// LEBD-04 Fix34: restore fast Course -> Department -> Subject -> Chapter.
// Retain existing Laravel endpoints, Select2, keep checkboxes and original form names.
var lebdHierarchyCache = {};
var lebdHierarchyPending = {};
var lebdHierarchyVersion = {department_id:0, semester_id:0, subject_id:0, chapter_id:0};

function lebdSelectedIds(el) {
    return el ? Array.from(el.selectedOptions).map(function(o){return o.value;}).filter(Boolean).sort() : [];
}
function lebdClear(id) {
    var el = document.getElementById(id);
    if (!el) return;
    lebdHierarchyVersion[id] = (lebdHierarchyVersion[id] || 0) + 1;
    el.innerHTML = '';
    if (window.jQuery && $.fn.select2 && $(el).data('select2')) $(el).trigger('change.select2');
}
function lebdSetItems(id, rows) {
    var el = document.getElementById(id);
    if (!el) return;
    var previous = lebdSelectedIds(el);
    el.innerHTML = '';
    (rows || []).forEach(function(row){
        if (!row || row.id == null) return;
        var opt = new Option(String(row.name == null ? '' : row.name), String(row.id));
        opt.selected = previous.indexOf(String(row.id)) !== -1;
        el.add(opt);
    });
    if (window.jQuery && $.fn.select2 && $(el).data('select2')) $(el).trigger('change.select2');
}
function lebdGetRows(endpoint, ids, done) {
    if (!ids.length) {done([]); return;}
    var key = endpoint + ':' + ids.join(',');
    if (Object.prototype.hasOwnProperty.call(lebdHierarchyCache,key)) {
        done(lebdHierarchyCache[key]); return;
    }
    if (lebdHierarchyPending[key]) {lebdHierarchyPending[key].push(done); return;}
    lebdHierarchyPending[key] = [done];
    $.ajax({url: endpoint + '/' + encodeURIComponent(ids.join(',')), type:'GET', dataType:'json', timeout:16000})
    .done(function(resp){
        var rows = resp && Array.isArray(resp.data) ? resp.data : [];
        lebdHierarchyCache[key] = rows;
        (lebdHierarchyPending[key] || []).forEach(function(cb){cb(rows);});
    }).fail(function(xhr,status){
        console.warn('LEBD Question hierarchy request failed:', endpoint, status, xhr.status);
        (lebdHierarchyPending[key] || []).forEach(function(cb){cb(null);});
    }).always(function(){delete lebdHierarchyPending[key];});
}
function lebdLoad(id, endpoint, ids, after) {
    var version = lebdHierarchyVersion[id] = (lebdHierarchyVersion[id] || 0) + 1;
    var el = document.getElementById(id);
    if (!el) return;
    if (!ids.length) {lebdSetItems(id, []); if(after) after(); return;}
    lebdGetRows(endpoint,ids,function(rows){
        if (version !== lebdHierarchyVersion[id]) return; // reject stale replies
        if (rows === null) return; // leave current data on transient network failure
        lebdSetItems(id, rows);
        if (after) after();
    });
}
function getDepartments(e) {
    var ids = lebdSelectedIds(e);
    lebdClear('department_id'); lebdClear('semester_id');
    lebdClear('subject_id'); lebdClear('chapter_id');
    lebdLoad('department_id','/get_departments',ids);
}
function getSemesters(e) {
    var ids = lebdSelectedIds(e);
    lebdClear('semester_id');
    if (!ids.length) {
        var oldSection = document.getElementById('semester');
        if (oldSection) oldSection.remove();
        return;
    }
    var token = lebdHierarchyVersion.semester_id = (lebdHierarchyVersion.semester_id || 0) + 1;
    lebdGetRows('/get_semesters',ids,function(rows){
        if (token !== lebdHierarchyVersion.semester_id || rows === null) return;
        var section = document.getElementById('semester');
        if (!rows.length) { if(section) section.remove(); return; }
        if (!section) {
            section = document.createElement('div');
            section.className = 'col-md-12'; section.id = 'semester';
            section.innerHTML = '<div class="form-group"><label for="semester_id">Semester Name</label>' +
              '<select class="form-control select2" multiple name="semester_id[]" id="semester_id" onchange="getSemsSubjects(this)"></select>' +
              '<label class="lebd-top-keep" title="Keep this value after Save / Continue"><input type="checkbox" class="lebd-top-keep-box" data-keep-for="semester_id" aria-label="Keep semester selection"> <span>Keep</span></label></div>';
            var parent = document.getElementById('department_id');
            if (parent) {
                var wrapper = parent.closest('.col-md-12');
                if (wrapper) wrapper.after(section);
            }
        }
        lebdSetItems('semester_id', rows);
        if ($.fn.select2 && !$('#semester_id').data('select2')) $('#semester_id').select2({width:'100%'});
    });
}
function getSubjects(e) {
    var ids = lebdSelectedIds(e);
    lebdClear('subject_id'); lebdClear('chapter_id');
    lebdLoad('subject_id','/get_subjects',ids);
}
function getSemsSubjects(e) {
    var ids = lebdSelectedIds(e);
    lebdClear('subject_id'); lebdClear('chapter_id');
    lebdLoad('subject_id','/get_sems_subjects',ids);
}
function getChapters(e) {
    var ids = lebdSelectedIds(e);
    lebdClear('chapter_id');
    lebdLoad('chapter_id','/get_chapters',ids);
}

// The new fixed 11-filter section is NOT rebuilt on course changes.
// The existing Course/Department/Subject fields still operate as before.
function getFilters(e) { return; }

// add multiple mcq items
function addMultiMcq()
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
    $(document).ready(function() {
      $('.editor').summernote({
          height: 150,
          toolbar: [
            ['script', ['subscript', 'superscript']]
        ]
      });
    });

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
                            '<textarea class="form-control editor" name="mcq_items[]" id="mcq_items" rows="3" ></textarea>'+
                            '<span class="input-group-addon">'+
                                '<input style="display: none" type="file" name="item_img[]" id="item_'+x+'"/>'+
                                '<label for="item_'+x+'"><i class="fa fa-image"></i></label>'+
                            '</span>'+
                            '<span class="input-group-addon">'+
                                '<input type="radio" name="correct[]" onchange="checkAns()" required/>'+
                                '</span>'+
                        '</div>';
    multiItems.appendChild(field);

    $(document).ready(function() {
      $('.editor').summernote({
          height: 150,
          toolbar: [
            ['script', ['subscript', 'superscript']]
        ]
      });
    });

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
                                '<textarea class="form-control editor" name="items[]" id="items" rows="3"></textarea>'+
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
                                '<textarea class="form-control editor" name="items[]" id="items" rows="3"></textarea>'+
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
                                '<textarea class="form-control editor" name="items[]" id="items" rows="3"></textarea>'+
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
                                '<textarea class="form-control editor" name="items[]" id="items" rows="3"></textarea>'+
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
              '<label for="">Video URL Type</label>'+
              '<select class="form-control" name="url_type" id="url_type" placeholder="Video URL Type" required>'+
                '<option value="">Select One</option>'+
                '<option value="Youtube">Youtube</option>'+
                '<option value="Google Drive">Google Drive</option>'+
              '</select>'+
            '</div>'+
            '<div class="form-group">'+
              '<label for="">Input Video URL</label>'+
              '<input type="url" class="form-control" name="video" id="video" required>'+
            '</div>'+
          '</div>';
    }

    $(document).ready(function() {
        $('.editor').summernote({
            height: 150,
            toolbar: [
              ['script', ['subscript', 'superscript']]
          ]
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
    // Original Question Type, Course, Department, Semester, Subject and Chapter:
    // preserve only fields whose Keep checkbox is ticked on Save -> Continue.
    ['type', 'course_id', 'department_id', 'semester_id', 'subject_id', 'chapter_id'].forEach(function(id) {
        var field = document.getElementById(id);
        var keep = document.querySelector('.lebd-top-keep-box[data-keep-for="' + id + '"]');
        if (!field || (keep && keep.checked)) return;
        if (field.multiple) {
            Array.from(field.options).forEach(function(option) { option.selected = false; });
        } else {
            field.value = '';
        }
        // Update only the visual Select2 state; avoid legacy AJAX requests during reset.
        if (window.jQuery) $(field).trigger('change.select2');
        if (id === 'type') {
            var answerBox = document.getElementById('Answer');
            if (answerBox) answerBox.innerHTML = '';
        }
    });

    // Retain checked labels; remove unchecked labels when continuing after save.
    Array.from(label.children).forEach(function (row) {
        var keep = row.querySelector('input.lebd-label-keep');
        if (!keep || !keep.checked) row.remove();
    });

         // Preserve explicitly checked filters; clear others without disturbing Select2.
     document.querySelectorAll('.lebd-question-filter').forEach(function(el) {
         var group = el.closest('.input-group');
         var check = group ? group.querySelector('input[type="checkbox"]') : null;
         if (!check || !check.checked) {
             Array.from(el.options).forEach(function(option) { option.selected = false; });
             el.dispatchEvent(new Event('change', {bubbles:true}));
         }
     });
     // The label sources depend on the actual filter chain; they are refreshed
     // by the same change events. A full page reload starts with no selection.

//reset all label fields
    // let labelFields = document.getElementsByClassName('label-filters');
    // for(let l = 0; labelFields.length > l; l++)
    // {
    //     labelFields[l].value = [];
    // }

    //reset question title and explanation fields
    // $('#title').summernote('reset');
    // $('#explanation').summernote('reset');

    // The original Select2 instances remain attached; do not initialize them twice.
}


</script>
@endsection

@section('scripts')
<script src="/assets/summernote/summernote.min.js"></script>
<script defer src="{{ asset('web/js/lebd-equation-editor.js') }}"></script>
<script>
/* Fix30: no Select2 on LEBD question filter fields. Native working options
   stay in the DOM for form submission; the searchable popover is only a UI. */
(function () {
 'use strict';
 function init() {
  var elements = {}, wrappers = {};
  function selections(n) {
   var el=elements[n];
   return el ? Array.from(el.selectedOptions).map(function(o){return String(o.value);}) : [];
  }
  function syncButton(n) {
   var el=elements[n], widget=wrappers[n];
   if (!el||!widget) return;
   var labels=Array.from(el.selectedOptions).map(function(o){return o.textContent;});
   widget.button.textContent=labels.length ? labels.join(', ') : 'Select sub-filter(s)';
   widget.button.title=labels.join(', ');
  }
  function renderList(n) {
   var el=elements[n], widget=wrappers[n];
   if (!el||!widget) return;
   var q=widget.search.value.trim().toLocaleLowerCase();
   widget.list.textContent='';
   var count=0;
   Array.from(el.options).forEach(function(opt){
    if (opt.disabled || opt.hidden || opt.textContent.toLocaleLowerCase().indexOf(q)<0) return;
    count++;
    var item=document.createElement('button');
    item.type='button'; item.className='lebd-choice-item';
    item.textContent=(opt.selected ? '✓  ' : '')+opt.textContent;
    item.setAttribute('role','option'); item.setAttribute('aria-selected',String(opt.selected));
    item.addEventListener('click',function(){
     opt.selected=!opt.selected;
     // Close after every choice, including when multiple selection is desired.
     closeAll();
     el.dispatchEvent(new Event('change',{bubbles:true}));
    });
    widget.list.appendChild(item);
   });
   if (!count) {
    var empty=document.createElement('div');empty.className='lebd-choice-empty';
    empty.textContent='No connected options';widget.list.appendChild(empty);
   }
  }
  function closeAll() {
   Object.keys(wrappers).forEach(function(k){wrappers[k].root.classList.remove('open');wrappers[k].button.setAttribute('aria-expanded','false');});
  }
  function updateLabelSources() {
   [9, 10].forEach(function(step) {
    var label=document.getElementById('lebd-label-step-'+step);
    var hint=document.getElementById('lebd-label-hint-'+step);
    var source=elements[step];
    if(!label||!source)return;
    var complete=true;
    for(var previous=1;previous<step;previous++) {
     if(selections(previous).length===0){complete=false;break;}
    }
    var current=String(label.value||'');
    var available=complete ? Array.from(source.options).filter(function(option){
     return !option.disabled && !option.hidden;
    }) : [];
    // Mirror exactly the currently available connected options from the upper
    // Sub-source/Exam Year filter. Do not query legacy records or duplicate logic.
    label.replaceChildren(new Option('Select One',''));
    available.forEach(function(option){
     label.add(new Option(option.textContent,option.value));
    });
    if(available.some(function(option){return String(option.value)===current;})) label.value=current;
    else label.value='';
    label.disabled=!complete || available.length===0;
    if(hint) hint.textContent=!complete ? 'আগের Filter ১–'+(step-1)+' সম্পূর্ণ নির্বাচন করুন।' :
      (!available.length ? 'এই Connection-এ কোনো Sub-filter নেই।' : 'উপরের Filter-এর '+available.length+'টি সংযুক্ত Option পাওয়া গেছে।');
    if(window.jQuery && window.jQuery.fn.select2 && window.jQuery(label).data('select2')) {
     window.jQuery(label).trigger('change.select2');
    }
   });
  }
  function refreshFrom(step) {
   for (var n=step+1;n<=10;n++) {
    var el=elements[n];if (!el) continue;
    var parents=selections(n-1);
    Array.from(el.options).forEach(function(opt){
     var related=(opt.getAttribute('data-parent-ids')||'').split(',');
     var available=parents.length>0 && related.some(function(id){return parents.indexOf(id)!==-1;});
     opt.hidden=!available;opt.disabled=!available;
     if(!available)opt.selected=false;
    });
    syncButton(n);
   }
   updateLabelSources();
  }
  for(var n=1;n<=11;n++) {
   (function(step){
    var el=document.getElementById('lebd-filter-'+step);
    if(!el)return;
    elements[step]=el;
    // Protect the native source from global Select2 init.
    el.classList.remove('select2');
    el.style.display='none';
    var root=document.createElement('div');root.className='lebd-choice';
    var button=document.createElement('button');button.type='button';button.className='lebd-choice-button';
    button.setAttribute('aria-expanded','false');
    var panel=document.createElement('div');panel.className='lebd-choice-panel';
    var search=document.createElement('input');search.type='text';search.className='lebd-choice-search';search.placeholder='Search sub-filter…';
    var list=document.createElement('div');list.className='lebd-choice-list';
    panel.appendChild(search);panel.appendChild(list);root.appendChild(button);root.appendChild(panel);
    el.parentNode.insertBefore(root,el.nextSibling);
    wrappers[step]={root:root,button:button,search:search,list:list};
    button.addEventListener('click',function(){
     var opening=!root.classList.contains('open');closeAll();
     if(opening){root.classList.add('open');button.setAttribute('aria-expanded','true');search.value='';renderList(step);search.focus();}
    });
    search.addEventListener('input',function(){renderList(step);});
    search.addEventListener('keydown',function(e){if(e.key==='Escape'){closeAll();button.focus();}});
    el.addEventListener('change',function(){syncButton(step);if(step<10)refreshFrom(step);else updateLabelSources();});
    syncButton(step);
   })(n);
  }
  document.addEventListener('click',function(event){
   if(!event.target.closest('.lebd-choice'))closeAll();
  });
  document.addEventListener('keydown',function(event){if(event.key==='Escape')closeAll();});
  refreshFrom(1);
 }
 if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',init);else init();
})();
</script>

<script>
    // select 2
    $(function (){ $('.select2').not('.lebd-question-filter').select2() });

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
</script>



{{-- Fix43: read the already-selected manual question hierarchy + all 11 filter stages and labels. --}}
<script>
function lebdGoToCsvWithContext() {
    var main = document.getElementById('mcq_form');
    if (!main) { alert('Question form not found.'); return; }
    var pick = function(name) {
        return Array.prototype.slice.call(main.querySelectorAll('select[name="' + name + '"] option:checked')).filter(function(o){ return !o.disabled && o.value !== ''; }).map(function(o){return o.value;});
    };
    var type = document.getElementById('type');
    if (!type || type.value !== 'MCQ') { alert('Question Type থেকে MCQ নির্বাচন করুন।'); return; }
    if (!pick('course_id[]').length || !pick('department_id[]').length) {
        alert('প্রথমে Course এবং Department নির্বাচন করুন।'); return;
    }
    var chosen = [];
    Array.prototype.forEach.call(main.querySelectorAll('select.lebd-question-filter'), function(select){
        Array.prototype.forEach.call(select.selectedOptions, function(option){
            if (option.value && !option.disabled) chosen.push(option.value);
        });
    });
    var labels = Array.prototype.slice.call(main.querySelectorAll('input[name="label[]"]')).map(function(input){return input.value.trim();}).filter(Boolean);
    var fields = {'course_ids':pick('course_id[]'),'department_ids':pick('department_id[]'),
        'semester_ids':pick('semester_id[]'),'subject_ids':pick('subject_id[]'),
        'chapter_ids':pick('chapter_id[]'),'sub_filter_ids':Array.from(new Set(chosen)), 'labels':labels};
    var f=document.createElement('form');f.method='POST';f.action=@json(route('admin.question-csv.context'));
    var csrf=document.createElement('input');csrf.type='hidden';csrf.name='_token';csrf.value=main.querySelector('input[name="_token"]').value;f.appendChild(csrf);
    Object.keys(fields).forEach(function(name){fields[name].forEach(function(value){var i=document.createElement('input');i.type='hidden';i.name=name+'[]';i.value=value;f.appendChild(i);});});
    document.body.appendChild(f);f.submit();
}
</script>
@endsection