@extends('dashboard')
@section('title','Exam Section — Main Database MCQ Picker')
@section('content')
<style>
.ex152{background:#eef3f9;min-height:65vh;padding:20px;font-family:'Noto Sans Bengali',Arial,sans-serif;color:#102c53}
.ex152 *{box-sizing:border-box}.ex152-panel{background:#fff;border:1px solid #d4e3f4;border-radius:13px;padding:20px;margin-bottom:17px}
.ex152 h2{font-size:20px;font-weight:750;margin:0 0 12px}.ex152 .muted{font-size:12px;color:#657a98}
.ex152-filter{display:grid;grid-template-columns:repeat(6,minmax(0,1fr));gap:11px}.ex152 label{display:block;font-size:12px;font-weight:700;margin-bottom:6px}
.ex152 input:not([type=checkbox]),.ex152 select{width:100%;height:40px;border:1px solid #cad8e9;border-radius:6px;padding:8px 10px;background:white;color:#1b3658}
.ex152 button,.ex152 .btn152{display:inline-block;border:0;border-radius:5px;padding:10px 12px;cursor:pointer;color:white;background:#0bb7db;font-size:12px;text-decoration:none;white-space:nowrap}
.ex152 .blue{background:#136bd0}.ex152 .green{background:#05a961}.ex152 .grey{background:#718096}.ex152 .red{background:#e34b42}
.ex152-actions{display:flex;gap:8px;align-items:center;flex-wrap:wrap;margin-top:14px}
.ex152-options{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-top:10px}.ex152-option{border:1px solid #d6e3f1;border-radius:7px;padding:12px;background:#fbfdff;font-size:13px}.ex152-correct{background:#e0f7e9;border-color:#9fe3b8}
.ex154-answer{margin-top:10px;padding:10px 12px;background:#e5f8ed;border:1px solid #8ed7af;border-radius:7px;color:#075d38;font-size:13px}.ex154-noanswer{background:#fff5e8;border-color:#e7c17c;color:#80551f}.ex152-question{border:1px solid #dce7f7;border-radius:10px;padding:15px;margin:12px 0;background:#fcfdff}.ex152-question header{display:flex;gap:10px;align-items:center;margin-bottom:8px}
.ex152-sub{display:flex;gap:9px;align-items:center;flex-wrap:wrap;margin-bottom:12px}.ex152-tag{border:1px solid #cde0f7;background:#edf6ff;color:#0b61a9;border-radius:6px;padding:7px 10px;font-size:12px}
.ex152-hr{border:0;border-top:1px solid #e8eff7;margin:13px 0}.ex152-footer{display:flex;flex-wrap:wrap;gap:8px}
.ex152 [type=checkbox]{width:16px;height:16px;vertical-align:middle;margin-right:5px}
@media(max-width:980px){.ex152-filter{grid-template-columns:repeat(3,minmax(0,1fr))}}@media(max-width:600px){.ex152{padding:8px}.ex152-panel{padding:12px}.ex152-filter{grid-template-columns:repeat(2,minmax(0,1fr))}.ex152-options{grid-template-columns:1fr}}
</style>
<div class="ex152">
<div class="ex152-sub"><a class="btn152 blue" href="{{ route('admin.exam-section.routine-topic.index',['level'=>$level,'kind'=>$kind]) }}">← Department / Subject / Chapter</a><strong>{{ $kind==='department'?'Department':'Non-Department' }}</strong></div>
<div class="ex152-panel">
<div style="display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;align-items:center">
<div><h2>{{ request('view')==='linked' ? 'Linked Questions — Full View' : 'Main Database MCQ Picker' }}</h2><p class="muted">{{ ucfirst($level) }}: {{ $topic->name }} — Original Question IDs linked, not copied.</p></div>
<div class="ex152-actions" style="margin:0"><a class="btn152 blue" href="{{ route('admin.exam-section.routine-topic.questions',['level'=>$level,'id'=>$topic->id,'kind'=>$kind,'view'=>'linked']) }}">👁 View Linked Questions ({{ count($linked) }})</a><a class="btn152 blue" href="{{ route('admin.exam-section.routine-topic.questions',['level'=>$level,'id'=>$topic->id,'kind'=>$kind]) }}">+ Add MCQ</a><a class="btn152 green" href="{{ route('admin.exam-section.routine-topic.index',['level'=>$level,'kind'=>$kind]) }}">✓ Done (Return)</a></div>
</div>
@if(session('success'))<div class="alert alert-success">{{session('success')}}</div>@endif
@if($errors->any())<div class="alert alert-danger">{{implode(' | ',$errors->all())}}</div>@endif
@if(!$sourceTable)<div class="alert alert-warning">Main questions table not available.</div>@else
<form method="GET" id="ex152-filters"><input type="hidden" name="kind" value="{{ $kind }}">
<div class="ex152-filter">
<div><label>Course</label><select id="ex153-course" name="course_id"><option value="">All Courses</option>@foreach($courses as $o)<option value="{{ $o->id }}" @selected(request('course_id')==$o->id)>{{ $o->name }}</option>@endforeach</select></div>
<div><label>Department</label><select id="ex153-department" name="department_id"><option value="">All Departments</option>@foreach($departments as $o)<option value="{{ $o->id }}" @selected(request('department_id')==$o->id)>{{ $o->name }}</option>@endforeach</select></div>
<div><label>Semester</label><select name="semester_id"><option value="">All Semesters</option>@foreach($semesters as $o)<option value="{{ $o->id }}" @selected(request('semester_id')==$o->id)>{{ $o->name }}</option>@endforeach</select></div>
<div><label>Subject</label><select name="subject_id" id="ex152-subject"><option value="">All Subjects</option>@foreach($subjects as $o)<option value="{{ $o->id }}" @selected(request('subject_id')==$o->id)>{{ $o->name }}</option>@endforeach</select></div>
<div><label>Chapter</label><select name="chapter_id" id="ex152-chapter"><option value="">All Chapters</option>@foreach($chapters as $o)<option value="{{ $o->id }}" data-subject="{{ $o->subject_id }}" @selected(request('chapter_id')==$o->id)>{{ $o->name }}</option>@endforeach</select></div>
<div><label>Main DB Subject / Chapter / Filter</label><select name="sub_filter_id"><option value="">All Subjects & Chapters</option>@foreach($filterGroups as $group)@php $members=$subFilters->where('filter_id',$group->id); @endphp@if($members->count())<optgroup label="{{ $group->name }}">@foreach($members as $o)<option value="{{ $o->id }}" @selected(request('sub_filter_id')==$o->id)>{{ $o->name }}</option>@endforeach</optgroup>@endif @endforeach</select></div>
<div><label>Created By</label><select name="created_by"><option value="">All Creators</option>@foreach($creators as $o)<option value="{{ $o->id }}" @selected(request('created_by')==$o->id)>{{ $o->name }}</option>@endforeach</select></div>
<div><label>Question Text / ID</label><input name="q" value="{{ request('q') }}" placeholder="Search question"></div>
<div><label>From Date</label><input type="date" name="date_from" value="{{ request('date_from') }}"></div>
<div><label>To Date</label><input type="date" name="date_to" value="{{ request('date_to') }}"></div>
</div>
<div class="ex152-actions"><button type="submit">🔎 Search Questions</button><a class="btn152 grey" href="{{ route('admin.exam-section.routine-topic.questions',['level'=>$level,'id'=>$topic->id,'kind'=>$kind]) }}">Clear</a></div>
</form>
@endif
</div>
@if($sourceTable)
<div class="ex152-panel">
<div style="display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap"><strong>{{ $questions->total() }} matching questions · 25 per page</strong><button type="button" class="btn152 grey" onclick="document.querySelectorAll('.ex152-page-select').forEach(x=>x.checked=!Array.from(document.querySelectorAll('.ex152-page-select')).every(v=>v.checked))">Select / Unselect Page</button></div>
<form method="POST" action="{{ route('admin.exam-section.routine-topic.questions.attach',['level'=>$level,'id'=>$topic->id,'kind'=>$kind]) }}" id="ex152-attach">@csrf
<input type="hidden" name="kind" value="{{ $kind }}">
@foreach(['q','course_id','department_id','semester_id','subject_id','chapter_id','sub_filter_id','created_by','date_from','date_to'] as $field)<input type="hidden" name="{{ $field }}" value="{{ request($field) }}">@endforeach
<div class="ex152-actions"><button type="submit" name="mode" value="page" class="btn152 blue">Add Selected Questions</button><button type="submit" name="finish" value="1" class="btn152 green">✓ Save &amp; Done</button><label style="display:inline-flex;gap:6px;align-items:center;margin:0"><input type="checkbox" id="ex152-all"> Select All Search Results (all pages)</label><span class="muted">Links existing IDs only</span></div>
<input type="hidden" name="all_results" value="0" id="ex152-all-value">
@forelse($questions as $q)
<article class="ex152-question"><header><label style="margin:0"><input class="ex152-page-select" type="checkbox" name="question_ids[]" value="{{ $q->id }}" @disabled(in_array((int)$q->id,$linked))> <strong>Q-{{ ($questions->currentPage()-1)*25+$loop->iteration }}</strong></label><span class="muted">Question ID: {{ $q->id }}</span>@if(in_array((int)$q->id,$linked))<strong style="color:green">Already Linked</strong>@endif</header>
<div style="font-size:14px">{!! $q->title !!}</div>
@if($q->image)<div style="margin-top:8px"><img loading="lazy" style="max-width:100%;max-height:240px" src="{{ asset(ltrim($q->image,'/')) }}" alt="Question illustration"></div>@endif
@php
  $items=$optionItems->get($q->id,collect());
  // The original main bank stores correctness per mcq_items row; preserve its choice ID.
  $rightChoices=$items->filter(fn($item)=>(string)$item->correct_answer==='1');
@endphp
@if($items->count())
<div class="ex152-options">@foreach($items as $i=>$option)
<div class="ex152-option {{ (string)$option->correct_answer==='1' ? 'ex152-correct' : '' }}"><strong>{{ ['ক','খ','গ','ঘ'][$i] ?? ($i+1) }}.</strong> {!! $option->item !!}
@if($option->image)<div><img loading="lazy" style="max-height:90px;max-width:100%" alt="Option image" src="{{ asset(ltrim($option->image,'/')) }}"></div>@endif
@if((string)$option->correct_answer==='1') <strong style="color:#087945">✓ সঠিক উত্তর</strong>@endif</div>
@endforeach</div>
@if($rightChoices->count()===1)
  @php $right=$rightChoices->first(); $rightIndex=$items->values()->search(fn($item)=>(int)$item->id===(int)$right->id); @endphp
  <div class="ex154-answer"><strong>✓ সঠিক উত্তর: {{ ['ক','খ','গ','ঘ'][$rightIndex] ?? ($rightIndex+1) }}.</strong> {!! $right->item !!} <small>(মূল Option ID: {{ $right->id }})</small></div>
@elseif($rightChoices->count()>1)
  <div class="ex154-answer ex154-noanswer"><strong>⚠ একাধিক সঠিক উত্তর চিহ্নিত:</strong> মূল Question → View Question-এ যাচাই করুন। Auto Question-এর জন্য এই MCQ যাচাই প্রয়োজন।</div>
@else
  <div class="ex154-answer ex154-noanswer"><strong>⚠ সঠিক উত্তর নির্ধারিত নেই।</strong> মূল Question → View Question-এ Answer নির্ধারণ করুন; অনুমান করে কোনো উত্তর নির্বাচন করা হয়নি।</div>
@endif
@elseif($q->answer)
 <div class="ex154-answer"><strong>মূল প্রশ্নের Answer:</strong> {!! $q->answer !!}</div>
@else
 <div class="ex154-answer ex154-noanswer">⚠ সঠিক উত্তর পাওয়া যায়নি; মূল প্রশ্নের Options যাচাই করুন।</div>
@endif
@if($q->explanation || $q->solution)<details style="margin-top:8px"><summary>Explanation / Solution</summary>{!! $q->explanation ?: $q->solution !!}</details>@endif
</article>
@empty <p>No questions match these filters.</p>@endforelse
</form>
<div class="ex152-actions"><a class="btn152 green" href="{{ route('admin.exam-section.routine-topic.index',['level'=>$level,'kind'=>$kind]) }}">✓ Done — Return to Topics</a></div><div style="margin-top:12px">{{ $questions->links() }}</div>
</div>
@endif
<div class="ex152-panel" id="linked-mcqs"><h2>Linked MCQs ({{ count($linked) }})</h2><p class="muted">Unlink does not delete original questions.</p><div class="ex152-footer">@forelse($linked as $qid)<form method="POST" action="{{ route('admin.exam-section.routine-topic.questions.detach',['level'=>$level,'id'=>$topic->id,'questionId'=>$qid,'kind'=>$kind]) }}" onsubmit="return confirm('Unlink Question #{{ $qid }}?')">@csrf @method('DELETE')<button class="btn152 red" type="submit">Q #{{ $qid }} ×</button></form>@empty<span class="muted">No linked questions.</span>@endforelse</div></div>
</div>
<script>
(function(){
const cd=@json($courseDepartmentMap);const sq=@json($subjectQuestionLinks);const dq=@json($departmentQuestionLinks);const cq=@json($courseQuestionLinks);const chq=@json($chapterQuestionLinks);const chapters=@json($chapters);
const course=document.getElementById('ex153-course'),dep=document.getElementById('ex153-department'),subject=document.getElementById('ex152-subject'),chapter=document.getElementById('ex152-chapter');
const current={course:course.value,dep:dep.value,subject:subject.value,chapter:chapter.value};
const idsFor=(links,key,id)=>new Set(links.filter(x=>String(x[key])===String(id)).map(x=>String(x.question_id)));
const intersect=(a,b)=>new Set([...a].filter(x=>b.has(x)));
function hideUnrelated(select,allowed){[...select.options].forEach(o=>{if(!o.value)return;o.hidden=!allowed.has(String(o.value));o.disabled=o.hidden;});if(select.value&&select.selectedOptions[0]?.disabled)select.value='';}
function populateDep(){const cid=course.value;const allowed=new Set(cd.filter(x=>!cid||String(x.course_id)===cid).map(x=>String(x.department_id)));hideUnrelated(dep,allowed);}
function populateSubject(){let allowedQuestions=null;
if(course.value)allowedQuestions=idsFor(cq,'course_id',course.value);
if(dep.value){const d=idsFor(dq,'department_id',dep.value);allowedQuestions=allowedQuestions===null?d:intersect(allowedQuestions,d);}
const allowed=new Set(sq.filter(x=>allowedQuestions===null||allowedQuestions.has(String(x.question_id))).map(x=>String(x.subject_id)));
hideUnrelated(subject,allowed);
}
function populateChapter(){let allowedQuestions=null;
if(course.value)allowedQuestions=idsFor(cq,'course_id',course.value);
if(dep.value){const d=idsFor(dq,'department_id',dep.value);allowedQuestions=allowedQuestions===null?d:intersect(allowedQuestions,d);}
if(subject.value){const s=idsFor(sq,'subject_id',subject.value);allowedQuestions=allowedQuestions===null?s:intersect(allowedQuestions,s);}
const used=new Set(chq.filter(x=>allowedQuestions===null||allowedQuestions.has(String(x.question_id))).map(x=>String(x.chapter_id)));
const allowed=new Set(chapters.filter(x=>(!subject.value||String(x.subject_id)===subject.value)&&used.has(String(x.id))).map(x=>String(x.id)));
hideUnrelated(chapter,allowed);
}
course.addEventListener('change',()=>{dep.value='';subject.value='';chapter.value='';populateDep();populateSubject();populateChapter();});
dep.addEventListener('change',()=>{subject.value='';chapter.value='';populateSubject();populateChapter();});
subject.addEventListener('change',()=>{chapter.value='';populateChapter();});
populateDep();populateSubject();populateChapter();
document.getElementById('ex152-all')?.addEventListener('change',function(){document.getElementById('ex152-all-value').value=this.checked?'1':'0';document.querySelectorAll('.ex152-page-select').forEach(el=>el.disabled=this.checked);});
})();
</script>
@endsection
