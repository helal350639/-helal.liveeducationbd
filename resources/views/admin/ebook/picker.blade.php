@extends('dashboard')
@section('title','E-Book — Select MCQs')
@section('content')
<style>
.jpv7{width:100%}.jpv7 .box{border-top:3px solid #00aee1}.jpv7 .filter-card{border:1px solid #dce6ef;background:#f8fbff;border-radius:6px;padding:16px;margin-bottom:15px}.jpv7 .filter-card label{font-size:12px;color:#244566}.jpv7 .filter-card .form-control{width:100%}.jpv7 .filter-card .row>div{margin-bottom:12px}.jpv7 .jp-side{border:1px solid #d9e5ee;background:#fff;border-radius:5px;padding:12px;max-height:430px;overflow:auto}.jpv7 .jp-side details{border-bottom:1px solid #edf0f4;padding:8px 0}.jpv7 .jp-side summary{cursor:pointer;font-weight:600}.jpv7 .jp-side label{display:block;font-weight:400;padding:5px 6px}.jpv7 .jp-side input{margin-right:7px}.jpv7 .jp-results{border:1px solid #dce5ed;border-radius:5px;overflow:hidden}.jpv7 .jp-question{border-bottom:1px solid #dce5ed;padding:14px 16px;background:#fff}.jpv7 .jp-question:nth-child(even){background:#fcfdff}.jpv7 .jp-qheader{display:flex;gap:12px;align-items:start}.jpv7 .jp-qtitle{flex:1;font-size:15px;line-height:1.75;overflow-wrap:anywhere}.jpv7 .jp-qtitle img,.jpv7 .jp-options img{max-width:100%;height:auto}.jpv7 .jp-options{margin:9px 0 0 34px;display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:7px 18px;line-height:1.7}.jpv7 .jp-right{display:flex;gap:8px;align-items:center;flex-wrap:wrap}.jpv7 .jp-footer{position:sticky;bottom:0;z-index:10;display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap;padding:13px;background:#f3fbff;border:1px solid #d1e8f5;box-shadow:0 -3px 12px rgba(0,0,0,.08)}.jpv7 .jp-footer .btn{min-height:42px;font-size:15px;font-weight:700}.jpv7 .jp-check{width:19px;height:19px;cursor:pointer}.jpv7 .jp-filter-btn{min-width:130px}.jpv7 .jp-total{color:#980000;font-weight:700}.jpv7 .jp-pagination{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px}.jpv7 .jp-pagination .pagination{margin:8px 0}@media(max-width:767px){.jpv7 .jp-options{grid-template-columns:1fr;margin-left:6px}.jpv7 .jp-footer .btn{width:100%}}
</style>
<section class="content-header"><h1>E-Book <small>{{ $cat->name }} / {{ $ch->title }} / Add MCQ</small></h1></section>
<section class="content jpv7">
@if(session('success'))<div class="alert alert-success">{{session('success')}}</div>@endif
@if($errors->any())<div class="alert alert-danger">@foreach($errors->all() as $error)<p>{{$error}}</p>@endforeach</div>@endif
<div class="box box-primary"><div class="box-header with-border"><h3 class="box-title" style="color:#a00000">Main Database — View Questions Filters</h3><div class="pull-right"><a class="btn btn-primary btn-sm" href="{{route('admin.ebook.editor',['kind'=>$kind,'chapter'=>$ch->id])}}"><i class="fa fa-eye"></i> Back to Chapter Editor</a></div></div>
<div class="box-body">
<div class="alert alert-info" style="margin-bottom:14px"><strong>Adding to:</strong> {{$cat->name}} → {{$ch->title}}. Filter and select existing Active MCQs; Done will save links only. The original Question Database is not modified.</div>
<form id="jp-filter-form" method="GET" action="{{route('admin.ebook.picker',['kind'=>$kind,'chapter'=>$ch->id])}}">
<div class="filter-card"><div class="row">
@php
$selectors=[['course_id','Course',$courses],['department_id','Department',$departments],['semester_id','Semester',$semesters],['subject_id','Subjects',$subjects],['chapter_id','Chapter',$chapters]];
@endphp
@foreach($selectors as $field)
<div class="col-md-2 col-sm-4"><label for="jp-{{$field[0]}}">{{$field[1]}}</label><select class="form-control jp-select" name="{{$field[0]}}" id="jp-{{$field[0]}}"><option value="">Select One</option>@foreach($field[2] as $val)<option value="{{$val->id}}" {{(string)request($field[0])===(string)$val->id?'selected':''}}>{{$val->name}}</option>@endforeach</select></div>
@endforeach
<div class="col-md-2 col-sm-4"><label>Type</label><input class="form-control" value="MCQ" disabled></div>
<div class="col-md-2 col-sm-4"><label for="jp-created-by">Created By</label><select class="form-control jp-select" name="created_by" id="jp-created-by"><option value="">Select One</option>@foreach($users as $val)<option value="{{$val->id}}" {{(string)request('created_by')===(string)$val->id?'selected':''}}>{{$val->name}}</option>@endforeach</select></div>
<div class="col-md-2 col-sm-4"><label for="jp-start">Start Date</label><input id="jp-start" class="form-control" type="date" name="start" value="{{request('start')}}"></div>
<div class="col-md-2 col-sm-4"><label for="jp-end">End Date</label><input id="jp-end" class="form-control" type="date" name="end" value="{{request('end')}}"></div>
<div class="col-md-2 col-sm-4"><label for="jp-search">Question Title / ID</label><input id="jp-search" class="form-control" name="search" value="{{$search}}" placeholder="Question text or ID"></div>
<div class="col-md-2 col-sm-4"><label for="jp-batch">Select Batch</label><select class="form-control" name="batch" id="jp-batch"><option value="">Select One</option>@if(request('batch'))<option value="{{request('batch')}}" selected>Previously selected batch #{{request('batch')}}</option>@endif</select></div>
<div class="col-md-2 col-sm-4"><label for="jp-syllabus">Select Syllabus</label><select class="form-control" name="syllabus" id="jp-syllabus"><option value="">Select One</option>@if(request('syllabus'))<option value="{{request('syllabus')}}" selected>Selected syllabus #{{request('syllabus')}}</option>@endif</select></div>
<div class="col-md-2 col-sm-4"><label for="jp-per-page">Questions / Page</label><select name="per_page" id="jp-per-page" class="form-control">@foreach([25,50,100] as $per)<option value="{{$per}}" {{(int)request('per_page',25)===$per?'selected':''}}>{{$per}}</option>@endforeach</select></div>
<div class="col-md-4 col-sm-8" style="padding-top:22px"><button class="btn btn-info jp-filter-btn" type="submit"><i class="fa fa-filter"></i> Apply Filters</button> <a class="btn btn-default" href="{{route('admin.ebook.picker',['kind'=>$kind,'chapter'=>$ch->id])}}">Reset All</a></div>
</div></div>
<div class="row"><div class="col-md-3 col-sm-4"><aside class="jp-side"><h4><i class="fa fa-sliders"></i> Filter / Source</h4><p class="text-muted">Main Database Filter &amp; Sub-Filter</p>
@foreach($filters as $filter)
@if($filter->subfilter && $filter->subfilter->count())<details {{collect($filter->subfilter)->contains(fn($sf)=>in_array((int)$sf->id,$filterIds,true))?'open':''}}><summary>{{$filter->name}}</summary>
@foreach($filter->subfilter as $sub)
<label><input type="checkbox" name="filter_ids[]" value="{{$sub->id}}" {{in_array((int)$sub->id,$filterIds,true)?'checked':''}}> {{$sub->name}}</label>
@endforeach
</details>@endif
@endforeach
</aside></div>
<div class="col-md-9 col-sm-8"><div class="jp-pagination"><h3 class="jp-total">Questions ({{$rows->total()}})</h3><span class="text-muted">Showing {{$rows->firstItem()??0}}–{{$rows->lastItem()??0}} of {{$rows->total()}}</span></div>
<div style="background:#edf7ff;padding:10px 12px;margin-bottom:8px;border:1px solid #d7eaf9"><label style="cursor:pointer"><input id="jp-select-all" type="checkbox" class="jp-check" style="vertical-align:middle"> <strong>Select All Questions on Current Page</strong></label><span class="pull-right text-muted">Select one or many, then Done</span></div>
</form>
<form id="jp-attach-form" method="POST" action="{{route('admin.ebook.attach',['kind'=>$kind,'chapter'=>$ch->id])}}">@csrf
<div class="jp-results">
@forelse($rows as $row)
@php $options=$optionGroups->get($row->id,collect()); $letters=['a','b','c','d','e','f','g','h']; @endphp
<article class="jp-question"><div class="jp-qheader"><input id="jp-question-{{$row->id}}" class="jp-check jp-item-check" type="checkbox" name="question_ids[]" value="{{$row->id}}"><label for="jp-question-{{$row->id}}" class="jp-qtitle"><span style="color:#ab0000;font-weight:800">প্রশ্ন-{{$rows->firstItem()+$loop->index}}:</span> {!!$row->title!!} <span class="text-muted" style="font-size:12px">(ID: {{$row->id}})</span></label><a class="btn btn-xs btn-info" target="_blank" rel="noopener" title="View question in Main Database" href="{{route('question.show',$row->id)}}"><i class="fa fa-eye"></i></a></div>
@if($row->image)<div style="padding-left:31px;margin-top:8px"><img style="max-width:100%;max-height:210px" src="{{\Illuminate\Support\Str::startsWith($row->image,['https://','http://'])?$row->image:asset(ltrim($row->image,'/'))}}" alt="Question Image"></div>@endif
@if($options->count())<div class="jp-options">@foreach($options as $i=>$option)
@php $correct=in_array(strtolower(trim((string)$option->correct_answer)),['1','true','yes','on'],true); @endphp
<div style="{{$correct?'color:#008438;font-weight:700':''}}"><strong>{{$letters[$i]??($i+1)}})</strong> {!!$option->item!!} @if($option->image)<div><img style="max-height:130px;max-width:100%" src="{{\Illuminate\Support\Str::startsWith($option->image,['https://','http://'])?$option->image:asset(ltrim($option->image,'/'))}}" alt="Option image"></div>@endif</div>
@endforeach</div>@else<p class="text-muted" style="padding-left:30px">No MCQ options saved.</p>@endif
</article>
@empty<div class="alert alert-info" style="margin:15px">No unlinked Active MCQs match these filters.</div>
@endforelse
</div>
<div class="jp-footer"><div><strong>Selected:</strong> <span id="jp-selected-count">0</span> question(s)<div class="text-muted" style="font-size:12px">Select All affects this page only. Click Done to save before changing pages.</div></div><button class="btn btn-success btn-lg" type="submit" id="jp-done" disabled><i class="fa fa-check-circle"></i> Done — Add Selected MCQs to E-Book</button></div>
</form>
<div class="jp-pagination">{{ $rows->links() }}</div>
</div></div>
</div></div>
</section>
@endsection
@section('scripts')
<script>
(function(){
var selectAll=document.getElementById('jp-select-all'), boxes=Array.prototype.slice.call(document.querySelectorAll('.jp-item-check')), count=document.getElementById('jp-selected-count'),done=document.getElementById('jp-done'),form=document.getElementById('jp-attach-form');
function refresh(){var n=boxes.filter(function(b){return b.checked}).length;count.textContent=n;done.disabled=n===0;selectAll.checked=boxes.length>0&&n===boxes.length;selectAll.indeterminate=n>0&&n<boxes.length;}
boxes.forEach(function(b){b.addEventListener('change',refresh)});selectAll.addEventListener('change',function(){boxes.forEach(function(b){b.checked=selectAll.checked});refresh()});form.addEventListener('submit',function(e){if(!boxes.some(function(b){return b.checked}))e.preventDefault()});refresh();
// Original View Questions uses these same backend endpoints for cascading selectors.
var urls={departments:@json(url('/get_departments')),semesters:@json(url('/get_semesters')),subjects:@json(url('/get_subjects')),chapters:@json(url('/get_chapters')),batches:@json(route('batch.course-batch')),syllabuses:@json(route('syllabus.get-syllabus'))};
function value(id){var el=document.getElementById(id);return el?el.value:''}
function fill(id,items){var el=document.getElementById(id);if(!el)return;var previous=el.value;el.innerHTML='';var o=document.createElement('option');o.value='';o.textContent='Select One';el.appendChild(o);(items||[]).forEach(function(v){if(v&&v.id!==undefined){var item=document.createElement('option');item.value=v.id;item.textContent=v.name;el.appendChild(item)}});el.value=previous;}
function load(id,url,params,key){if(!url)return;$.getJSON(url,params||{}).done(function(data){fill(id,Array.isArray(data)?data:(data[key]||data.data||[]))}).fail(function(){console.warn('MCQ filter dropdown could not load',id)})}
$('#jp-course_id').on('change',function(){var v=this.value;fill('jp-department_id',[]);fill('jp-semester_id',[]);fill('jp-subject_id',[]);fill('jp-chapter_id',[]);if(v){load('jp-department_id',urls.departments+'/'+encodeURIComponent(v),{},'data');load('jp-batch',urls.batches,{course:v},'batches')}else{fill('jp-batch',[])}fill('jp-syllabus',[])});
$('#jp-department_id').on('change',function(){var v=this.value;fill('jp-semester_id',[]);fill('jp-subject_id',[]);if(v){load('jp-semester_id',urls.semesters+'/'+encodeURIComponent(v),{},'data');load('jp-subject_id',urls.subjects+'/'+encodeURIComponent(v),{},'data')}fill('jp-chapter_id',[]);fill('jp-syllabus',[])});
$('#jp-semester_id').on('change',function(){var v=this.value;if(v){load('jp-subject_id',@json(url('/get_sems_subjects'))+'/'+encodeURIComponent(v),{},'data')}fill('jp-chapter_id',[])});
$('#jp-subject_id').on('change',function(){var v=this.value;if(v){load('jp-chapter_id',urls.chapters+'/'+encodeURIComponent(v),{},'data')}else{fill('jp-chapter_id',[])}});
$('#jp-batch').on('change',function(){var v=this.value;if(v){load('jp-syllabus',urls.syllabuses,{batch_id:v,department_id:value('jp-department_id')},'syllabus')}else fill('jp-syllabus',[])});
})();
</script>
@endsection
