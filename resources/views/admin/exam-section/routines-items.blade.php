@extends('dashboard')
@section('title','Exam Section — Routine Items')
@section('content')
<style>
.ex165{padding:12px 14px 32px;background:#edf2f8;min-height:70vh;font-family:Arial,sans-serif;color:#20334a}
.ex165 *{box-sizing:border-box}.ex165 a{text-decoration:none}.ex165 .nav{background:#fff;border:1px solid #dae1e9;border-radius:4px;padding:11px 12px;margin-bottom:17px}.ex165 .nav-label{font-size:12px;font-weight:bold;margin:0 0 10px}.ex165 .bar{display:flex;flex-wrap:wrap;gap:6px;align-items:center}.ex165 .btn,.ex165 button{display:inline-flex;gap:4px;align-items:center;justify-content:center;border:1px solid transparent;border-radius:3px;background:#368dbb;color:white;padding:8px 10px;font-size:12px;cursor:pointer;line-height:1.3;white-space:nowrap}.ex165 .btn.gray{background:#f6f7f9;border-color:#ddd;color:#384960}.ex165 .btn.green,.ex165 button.green{background:#00a45d}.ex165 .btn.blue{background:#126dc9}.ex165 .btn.red,.ex165 button.red{background:#dc4c40}.ex165 .btn.orange,.ex165 button.orange{background:#f09a08}.ex165 .btn.cyan{background:#00b9db}.ex165 h2{font-weight:500;font-size:21px;margin:0 0 12px}.ex165 .panel{background:white;border:1px solid #d5e0ed;border-top:3px solid #12addd;border-radius:4px;margin:12px 0 16px;overflow:hidden}.ex165 .panel>h3{font-size:15px;font-weight:500;color:#bd281e;padding:12px;margin:0;border-bottom:1px solid #e5e9ef}.ex165 .inner{padding:11px}.ex165 .scroll{overflow:auto;width:100%}.ex165 table{border-collapse:collapse;min-width:1180px;width:100%;font-size:12px}.ex165 th,.ex165 td{border:1px solid #dce4eb;padding:11px 8px;text-align:left;vertical-align:middle;height:auto;min-height:0}.ex165 th{background:#eff3f7;font-weight:bold;color:#364251;text-align:center;white-space:nowrap}.ex165 td.topic{text-align:left;min-width:155px;max-width:235px;line-height:1.5}.ex165 td.date{text-align:center;white-space:nowrap;min-width:155px}.ex165 td.action{min-width:86px;white-space:nowrap;text-align:center}.ex165 .actions{display:flex;gap:5px;justify-content:center;align-items:center}.ex165 .actions button{padding:6px 8px}.ex165 .panelhead{display:flex;justify-content:space-between;align-items:center;gap:8px;padding:10px 12px}.ex165 .panelhead h3{margin:0;font-weight:500;color:#bd281e;font-size:16px}.ex165 .muted{color:#687b91;font-size:12px}.ex165 .crumb{float:right;color:#748497;font-size:11px}.ex165 .fields{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px}.ex165 .wide{grid-column:1/-1}.ex165 label{font-weight:600;font-size:12px;display:block;margin:5px 0}.ex165 input,.ex165 select,.ex165 textarea{width:100%;border:1px solid #cbd9e8;border-radius:3px;padding:9px;font-size:13px;background:white}.ex165 .notice{background:#ecfaf0;padding:12px;border:1px solid #b7ebc5;border-radius:3px;margin:8px 0}.ex165 .error{background:#fff0f0;border-color:#f1b2b2}.ex165 #ex165-editor[hidden]{display:none!important}.ex165 .badge{background:#edf7ff;border-radius:3px;padding:4px 6px;color:#25639b}.ex165 .fullbtn{background:#418cb3;color:#fff;border:0;border-radius:3px;padding:8px;font-size:12px;cursor:pointer}.ex165 .pill{background:#ebf5fe;color:#27649a;padding:3px 6px;border-radius:3px}.ex165 .topic small{display:block;font-size:11px;color:#6c7b91}.ex165 .type{font-size:10px;color:#566b80}.ex165 .show-add{display:flex;gap:5px}.ex165 .fullscreen:fullscreen{background:white;padding:16px;overflow:auto}
@media(max-width:820px){.ex165{padding:8px}.ex165 .fields{grid-template-columns:1fr}.ex165 .crumb{display:none}}
.ex165 .ex166-title{font-size:15px;font-weight:650;margin:18px 0 10px;color:#234772;border-bottom:1px solid #dce5f2;padding-bottom:8px}
.ex165 .ex166-topics{display:grid;gap:8px}
.ex165 .ex166-topic{display:grid;grid-template-columns:minmax(210px,2fr) minmax(130px,1fr) minmax(130px,1fr);align-items:center;gap:12px;border:1px solid #dbe5f1;background:#f7faff;padding:10px 13px;border-radius:4px}
.ex165 .ex166-topic-name{display:flex;gap:10px;align-items:center;font-size:13px}
.ex165 .ex166-num{background:#178bd3;color:white;border-radius:3px;padding:5px 8px}
@media(max-width:700px){.ex165 .ex166-topic{grid-template-columns:1fr}}

.ex165 .ex167-picker{display:grid;grid-template-columns:repeat(4,minmax(0,1fr)) auto;gap:10px;align-items:end;background:#f1f6fc;border:1px solid #cfdfef;border-radius:5px;padding:12px}
.ex165 .ex167-picker select{height:39px}.ex165 .ex167-picker button{height:39px}.ex165 .ex167-topic{display:grid;grid-template-columns:minmax(180px,2fr) minmax(90px,1fr) minmax(115px,1fr) minmax(115px,1fr) auto;gap:10px;align-items:center;border:1px solid #dbe5f1;background:#f7faff;padding:12px;border-radius:4px}
.ex165 .ex167-topic .title{font-weight:650}.ex165 .ex167-empty{padding:18px;border:1px dashed #cbd8e7;color:#718097;border-radius:5px}
@media(max-width:920px){.ex165 .ex167-picker{grid-template-columns:repeat(2,minmax(0,1fr))}.ex165 .ex167-topic{grid-template-columns:1fr 1fr}}
/* Fix169: design-only, the existing topic IDs/links and save behavior remain unchanged. */
.ex165 .ex169-exam-row{max-width:700px;display:grid;grid-template-columns:135px minmax(0,1fr);align-items:center;gap:12px}
.ex165 .ex169-exam-row>label{font-size:15px;margin:0}
.ex165 .ex169-exam-row>div select{height:42px;font-size:15px}
.ex165 .ex169-select-card{padding:14px;border:1px solid #d9e3ef;background:#fff;border-radius:5px;margin-top:12px;max-width:850px}
.ex165 .ex169-select-card .ex169-select-title{font-size:15px;font-weight:700;margin:0 0 12px}
.ex165 .ex169-select-card .ex167-picker{display:grid;grid-template-columns:minmax(0,1fr);gap:8px;background:transparent;border:0;padding:0}
.ex165 .ex169-select-card .ex167-picker>div{display:grid;grid-template-columns:140px minmax(0,1fr);align-items:center;gap:10px}
.ex165 .ex169-select-card .ex167-picker label{font-size:13px;margin:0}
.ex165 .ex169-select-card .ex167-picker select{height:38px}
.ex165 .ex169-select-card .ex167-picker button{justify-self:end;height:35px}
.ex165 .ex169-topic-box{max-width:850px}
.ex165 .ex169-topic-box .ex167-topic{grid-template-columns:minmax(0,1.8fr) 120px minmax(95px,1fr) minmax(95px,1fr) auto;box-shadow:0 1px 2px #0000000a}
.ex165 .ex169-topic-box .ex167-topic .title{line-height:1.5;word-break:break-word}
.ex165 .ex169-topic-box .ex167-topic .ex169-tools{display:flex;flex-wrap:wrap;gap:5px}
.ex165 .ex169-topic-box .ex167-topic .ex169-tools button{min-height:32px}
@media(max-width:800px){.ex165 .ex169-exam-row{grid-template-columns:1fr}.ex165 .ex169-select-card .ex167-picker>div{grid-template-columns:1fr}.ex165 .ex169-topic-box .ex167-topic{grid-template-columns:1fr 1fr}}
</style>
<div class="ex165">
<div class="nav"><div class="nav-label">◉ Routine Navigation</div><div class="bar">
<a class="btn gray" href="{{route('admin.exam-section.routine.list',['category'=>$routine->category_id,'subcategory_id'=>$routine->subcategory_id])}}">☷ Routine List</a>
<a class="btn gray" href="{{route('admin.exam-section.routine')}}">⊕ New Routine</a>
<a class="btn gray" href="{{route('admin.exam-section.routine.edit',$routine->id)}}">✎ Edit Routine</a>
<a class="btn green" href="#ex165-table">▤ Show Routine Items</a>
<button type="button" class="gray" data-type="exam">▦ Select Exam Type</button>
<button type="button" data-type="exam">＋ Add Exam</button>
<button type="button" data-type="revision" class="orange">↻ Add Revision</button>
<button type="button" data-type="model" class="cyan">▤ Add Model</button>
<a class="btn gray" href="#ex165-table">◈ Revision / Model List</a>
</div></div>
@if(session('success'))<div class="notice">{{session('success')}}</div>@endif
@if(session('error'))<div class="notice error">{{session('error')}}</div>@endif
@if($errors->any())<div class="notice error">{{implode(' | ',$errors->all())}}</div>@endif
<h2>Show a Routine Item <span class="crumb">Routines › Show Routine</span></h2>
<div class="panel fullscreen" id="ex165-table">
<div class="panelhead"><div><h3 style="display:inline-block">{{ $routine->name }}</h3> <button class="fullbtn" type="button" onclick="let e=document.getElementById('ex165-table');if(document.fullscreenElement){document.exitFullscreen()}else if(e.requestFullscreen){e.requestFullscreen()}">Full Screen</button></div><div class="show-add"><button type="button" data-type="exam">＋</button><a class="btn green" href="{{route('admin.exam-section.routine.list',['category'=>$routine->category_id,'subcategory_id'=>$routine->subcategory_id])}}">☷</a></div></div>
@php
$topicCols=collect($savedTopics)->flatMap(fn($rows)=>$rows->pluck('topic_key'))->unique()->values()->map(fn($k)=>['key'=>$k,'name'=>$topicNameMap[$k]??$k]);
@endphp
<div class="scroll"><table><thead><tr>
<th>ID</th><th>Exam Name/No</th>
@foreach($topicCols as $topic)<th>{{ $topic['name'] }}</th>@endforeach
<th>Date &amp; Time</th><th>Exam End</th><th>Create PDF Date &amp; Time</th><th>Question Create Date &amp; Time</th><th>Action</th>
</tr></thead><tbody>
@forelse($items as $i)
<tr><td style="text-align:center">{{ $i->id }}</td><td><strong>{{ $i->exam_name }}</strong> @if($i->type!=='exam')<small class="type">({{ucfirst($i->type)}})</small>@endif</td>
@foreach($topicCols as $topic)
@php $saved=($savedTopics->get($i->id)??collect())->firstWhere('topic_key',$topic['key']); @endphp
<td class="topic">@if($saved)<strong>{{ $topic['name'] }}</strong>@else<span class="muted">—</span>@endif
@if($saved)<small>Available Questions: {{ $topicCountMap[$topic['key']]??0 }}</small>@endif
@if($saved && ($saved->question_start||$saved->question_end))<small>Question Range: {{ $saved->question_start??'—' }}–{{ $saved->question_end??'—' }}</small>@endif
@if($i->type!=='exam')<small>{{ucfirst($i->type)}} Exam</small>@endif
</td>
@endforeach
<td class="date">{{ $i->exam_start_at??'—' }}</td><td class="date">{{ $i->exam_end_at??'—' }}</td><td class="date">{{ $i->pdf_create_at??'—' }}</td><td class="date">{{ $i->question_create_at??'—' }}</td>
<td class="action"><div class="actions"><button type="button" class="ex157-edit orange" data-item='@json($i)' data-topics='@json(($savedTopics->get($i->id)??collect())->values())'>✎</button><form method="POST" action="{{route('admin.exam-section.routine.items.delete',[$routine->id,$i->id])}}" onsubmit="return confirm('Delete this routine item?')">@csrf @method('DELETE')<button class="red">▣</button></form></div></td></tr>
@empty<tr><td colspan="{{count($topicCols)+7}}" style="text-align:center;padding:25px">No routine items added yet. Use Add Exam to begin.</td></tr>@endforelse
</tbody></table></div></div>
<div id="ex165-editor" hidden>
<div class="panel"><h3>Add Exam / Revision / Model Routine Item</h3><div class="inner">
<form id="ex157-form" method="POST" action="{{route('admin.exam-section.routine.items.store',$routine->id)}}">@csrf
<input type="hidden" name="type" id="ex157-type" value="exam">
<div class="ex169-exam-row"><label for="ex167-exam">Exam No *</label><div><select id="ex167-exam" name="exam_name" required><option value="">Select Exam No</option></select><div class="muted">Exam names come from Routine Edit; other logic is unchanged.</div></div></div>
<div class="ex169-select-card"><div class="ex169-select-title">Category / Sub-category — Topic Selection</div>
<div class="ex167-picker">
<div><label>Department Type</label><select id="ex167-kind"><option value="department">Department</option><option value="non-department">Non-Department</option></select></div>
<div><label>Department Name</label><select id="ex167-dept"><option value="">Select Department</option></select></div>
<div><label>Subject Name</label><select id="ex167-subject"><option value="">Select Subject</option></select></div>
<div><label>Chapter Name</label><select id="ex167-chapter"><option value="">Select Chapter (optional)</option></select></div>
<button type="button" class="green" id="ex167-add">＋ Add Topic</button>
</div>
<div class="muted" style="margin-top:8px">Use the existing Routine Topic Name selection. Category/Sub-category linking will be configured later.</div>
</div>
<h3 class="ex166-title">Selected Category / Sub-category Topics (<span id="ex167-total">0</span>)</h3>
<div class="ex166-topics ex169-topic-box" id="ex167-topic-list"></div>
<div id="ex157-source" hidden><label>Source Exam No (Revision / Model)</label><select name="source_exam_ids[]" multiple size="5">@foreach($items->where('type','exam') as $ex)<option value="{{ $ex->id }}">{{ $ex->exam_name }}</option>@endforeach</select></div>
<h3 class="ex166-title">Routine Dates &amp; Time</h3>
<div class="fields">
<div><label>Exam Date &amp; Time *</label><input name="exam_start_at" type="datetime-local" required></div>
<div><label>Exam End Date &amp; Time *</label><input name="exam_end_at" type="datetime-local" required></div>
<div><label>Create PDF Date &amp; Time</label><input name="pdf_create_at" type="datetime-local" readonly title="Auto: routine creation for Exam-01, previous exam end for later exams"></div>
<div><label>Question Create Date &amp; Time</label><input name="question_create_at" type="datetime-local" readonly title="Auto: routine creation for Exam-01, previous exam end for later exams"></div>
<div class="wide"><label>Notes</label><textarea name="notes" rows="2" placeholder="Optional notes"></textarea></div>
</div>
<div style="margin-top:15px"><button type="submit" class="green">Save Routine Item</button> <button type="button" class="gray" id="ex166-cancel">Cancel</button></div>
</form></div></div></div>
<script>
(function(){
 const editor=document.getElementById('ex165-editor'),form=document.getElementById('ex157-form'),type=document.getElementById('ex157-type'),sources=document.getElementById('ex157-source');
 const saveUrl=@json(route('admin.exam-section.routine.items.store',$routine->id));
 const updateBase=@json(url('/admin/exam-section/routine/'.$routine->id.'/items'));
 function openEditor(kind,item,topics){
   form.reset();list.replaceChildren();indexTopics();setExamOptions(kind,item?.exam_name||'');form.action=item?updateBase+'/'+item.id:saveUrl;
   let method=form.querySelector('input[name="_method"]');if(method)method.remove();
   if(item){method=document.createElement('input');method.type='hidden';method.name='_method';method.value='PUT';form.appendChild(method);}
   type.value=kind;sources.hidden=kind==='exam';
   if(item){
     ['exam_name','exam_start_at','exam_end_at','pdf_create_at','question_create_at','notes'].forEach(n=>{const el=form.elements[n];if(el)el.value=(item[n]||'').toString().replace(' ','T');});
     let ids=[];try{ids=JSON.parse(item.source_exam_ids||'[]')}catch(e){};
     [...sources.querySelectorAll('option')].forEach(o=>o.selected=ids.includes(Number(o.value)));
     (topics||[]).forEach(t=>{if(names[t.topic_key])drawTopic(t.topic_key,t.question_start||'',t.question_end||'');});
   }
   refreshAutoDates();
   editor.hidden=false;editor.scrollIntoView({behavior:'smooth',block:'start'});
 }
 document.querySelectorAll('[data-type]').forEach(b=>b.addEventListener('click',()=>openEditor(b.dataset.type,null,[])));
 document.querySelectorAll('.ex157-edit').forEach(b=>b.addEventListener('click',()=>{let item=JSON.parse(b.dataset.item),topics=JSON.parse(b.dataset.topics||'[]');openEditor(item.type,item,topics)}));
 const dataDepartments=@json($departments);
 const dataSubjects=@json($subjects);
 const dataChapters=@json($chapters);
 const counts=@json($topicCountMap);
 const names=@json($topicNameMap);
 const labelSets=@json($examLabels);
 const routineCreatedAt=@json($routine->created_at);
 const savedExamRows=(@json($items)).map(x=>({id:x.id,type:x.type,exam_name:x.exam_name,exam_end_at:x.exam_end_at}));
 function dateLocal(value){return (value||'').toString().replace(' ','T').slice(0,16);}
 function refreshAutoDates(){
   const selectedType=type.value,selectedName=form.elements['exam_name']?.value||'';
   const labels=labelSets[selectedType]||[];
   const labelNames=labels.map(x=>x.value);
   let idx=labelNames.indexOf(selectedName);
   const ordered=savedExamRows.filter(x=>x.type===selectedType).slice().sort((a,b)=>{
     let x=labelNames.indexOf(a.exam_name),y=labelNames.indexOf(b.exam_name);
     if(x<0)x=99999;if(y<0)y=99999;
     if(x!==y)return x-y;
     const n=t=>Number((String(t).match(/(\d+)\s*$/)||[])[1]||99999);
     return n(a.exam_name)-n(b.exam_name)||a.id-b.id;
   });
   let previous=null;
   if(idx>0){previous=ordered.find(x=>x.exam_name===labelNames[idx-1]);}
   if(idx<0){const n=Number((selectedName.match(/(\d+)\s*$/)||[])[1]||1);previous=ordered.filter(x=>Number((x.exam_name.match(/(\d+)\s*$/)||[])[1]||0)<n).pop();}
   const calculated=previous?.exam_end_at||routineCreatedAt;
   ['pdf_create_at','question_create_at'].forEach(k=>{const el=form.elements[k];if(el)el.value=dateLocal(calculated);});
 }
 document.getElementById('ex167-exam').addEventListener('change',refreshAutoDates);

 const kindInput=document.getElementById('ex167-kind'),deptInput=document.getElementById('ex167-dept'),subInput=document.getElementById('ex167-subject'),chInput=document.getElementById('ex167-chapter'),list=document.getElementById('ex167-topic-list');
 const htmlEscape=s=>String(s??'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
 function selectOptions(el,items,caption){el.innerHTML='<option value="">'+htmlEscape(caption)+'</option>'+items.map(x=>'<option value="'+Number(x.id)+'">'+htmlEscape(x.name)+'</option>').join('');}
 function departments(){selectOptions(deptInput,dataDepartments.filter(x=>x.kind===kindInput.value),'Select Department');subjects();}
 function subjects(){selectOptions(subInput,dataSubjects.filter(x=>Number(x.department_id)===Number(deptInput.value)&&deptInput.value),'Select Subject (optional)');chapters();}
 function chapters(){selectOptions(chInput,dataChapters.filter(x=>Number(x.subject_id)===Number(subInput.value)&&subInput.value),'Select Chapter (optional)');}
 kindInput.onchange=departments;deptInput.onchange=subjects;subInput.onchange=chapters;
 function drawTopic(key,from='',to=''){
   if([...list.children].some(r=>r.dataset.key===key))return;
   let row=document.createElement('div');row.className='ex167-topic';row.dataset.key=key;
   row.innerHTML='<div class="title">📁 '+htmlEscape(names[key]||key)+'</div><div><small>Available Questions</small><strong>'+Number(counts[key]||0)+'</strong></div><div><label>Range From</label><input type="number" min="1" class="ex166-from" value="'+htmlEscape(from)+'" placeholder="1"></div><div><label>Range To</label><input type="number" min="1" class="ex166-to" value="'+htmlEscape(to)+'" placeholder="200"></div><div class="ex169-tools"><button type="button" class="orange ex169-edit">Edit</button><button type="button" class="red ex169-delete">Delete</button></div>';
   row.querySelector('.ex169-delete').onclick=()=>{row.remove();indexTopics();};row.querySelector('.ex169-edit').onclick=()=>{row.querySelector('.ex166-from').focus();row.querySelector('.ex166-from').select();};list.append(row);indexTopics();
 }
 function indexTopics(){[...list.children].forEach((row,i)=>{row.querySelector('.ex166-from').name='topics['+i+'][question_start]';row.querySelector('.ex166-to').name='topics['+i+'][question_end]';let hidden=row.querySelector('input[type=hidden]');if(!hidden){hidden=document.createElement('input');hidden.type='hidden';row.appendChild(hidden)}hidden.name='topics['+i+'][key]';hidden.value=row.dataset.key});document.getElementById('ex167-total').textContent=list.children.length;}
 document.getElementById('ex167-add').onclick=()=>{if(!deptInput.value)return alert('Choose a Department');let level=chInput.value?'chapter':subInput.value?'subject':'department';let id=chInput.value||subInput.value||deptInput.value;drawTopic(level+':'+id);};
 function setExamOptions(kind,selected=''){
   let el=document.getElementById('ex167-exam');let data=(labelSets[kind]||[]).map(x=>x.value);if(selected&&!data.includes(selected))data.push(selected);
   el.innerHTML='<option value="">Select Exam No</option>'+data.map(s=>'<option value="'+htmlEscape(s)+'">'+htmlEscape(s)+'</option>').join('');el.value=selected||'';
 }
 departments();
 form.addEventListener('submit',e=>{if(!list.children.length){e.preventDefault();alert('Add at least one Department / Subject / Chapter topic.');}});
 document.getElementById('ex166-cancel').onclick=()=>{editor.hidden=true};
 @if($errors->any()) editor.hidden=false; @endif
})();
</script>
</div>
@endsection
