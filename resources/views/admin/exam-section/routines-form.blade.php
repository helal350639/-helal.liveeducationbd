@extends('dashboard')
@section('title','Exam Section — Routine')
@section('content')
<style>.xr{padding:14px;background:#edf2f8;min-height:70vh;font:14px Arial,sans-serif;color:#183153}.xr .bar{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:16px}.xr .btn,.xr button{border:0;border-radius:4px;padding:9px 13px;background:#0daeda;color:#fff;text-decoration:none;display:inline-block;cursor:pointer;font-size:13px}.xr .btn.blue{background:#1066c1}.xr .btn.green,.xr button.green{background:#09a65e}.xr .btn.red,.xr button.red{background:#d8463b}.xr .btn.gray{background:#748396}.xr .panel{background:#fff;border:1px solid #d2ddea;border-top:2px solid #1baee5;border-radius:5px;margin:12px 0 18px;overflow:hidden}.xr .panel>h3{font-size:16px;font-weight:500;color:#aa221b;padding:14px;margin:0;border-bottom:1px solid #e7ecf2}.xr .inner{padding:15px}.xr .cards{display:grid;grid-template-columns:repeat(auto-fit,minmax(230px,1fr));gap:15px}.xr .tile{display:block;background:white;border:1px solid #d5e0ec;border-radius:8px;padding:25px;color:#143869;text-decoration:none;box-shadow:0 2px 6px #c6d2df55}.xr .tile strong{display:block;font-size:20px;margin-bottom:12px}.xr label{font-weight:650;font-size:12px;display:block;margin:8px 0 5px}.xr input,.xr select,.xr textarea{width:100%;border:1px solid #ccd7e5;border-radius:4px;padding:10px;font:inherit;background:white;box-sizing:border-box}.xr .fields{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px}.xr .wide{grid-column:1/-1}.xr .scroll{overflow-x:auto}.xr table{border-collapse:collapse;width:100%;font-size:13px}.xr th,.xr td{border:1px solid #dbe4f0;text-align:left;padding:10px;vertical-align:middle;height:auto}.xr th{background:#f1f5fa}.xr .actions{display:flex;gap:6px;justify-content:flex-end;flex-wrap:wrap}.xr .notice{padding:12px;background:#e8f8ef;border:1px solid #b1e3c7;border-radius:5px;margin:8px 0}.xr .error{background:#fff1f1;border-color:#f2b7b7}.xr .split{display:grid;grid-template-columns:1fr 1fr;gap:16px}.xr .mini{font-size:12px;color:#637897}.xr .muted{color:#64748b}@media(max-width:780px){.xr .fields,.xr .split{grid-template-columns:1fr}.xr{padding:8px}}

.xr .xr-toolbar{border:1px solid #ccd7e5;background:#f3f6fa;padding:6px;display:flex;gap:5px;flex-wrap:wrap}.xr .xr-toolbar button{background:#fff;color:#173963;border:1px solid #d7e0eb;padding:6px 10px}
.xr .xr-editor{min-height:120px;border:1px solid #ccd7e5;background:#fff;padding:12px;overflow:auto;white-space:pre-wrap;outline:none}
.xr .xr-selected-topic{padding:12px;border:1px solid #d5e3f1;border-radius:5px;background:#f2f7fc}.xr .xr-selected-topic strong{display:block;margin-bottom:8px}.xr .xr-selected-topic .xr-pill{background:#116db8}.xr .xr-tagbox{border:1px solid #dde5ed;padding:12px;background:#fff;border-radius:4px}.xr .xr-tagbox h3{font-size:14px;margin:0 0 12px}.xr .xr-tagline{margin-bottom:16px}.xr .xr-pills{display:flex;flex-wrap:wrap;gap:5px;margin:6px 0;padding:7px;min-height:38px;border:1px solid #dbe3ed}.xr .xr-pill{background:#398bbf;color:white;border-radius:3px;padding:4px 7px;display:inline-flex;gap:6px;align-items:center;font-size:12px}.xr .xr-pill-remove{background:transparent!important;color:white!important;border:0!important;padding:0 2px!important;font-size:16px!important}.xr .xr-choose{display:flex;gap:8px}.xr .xr-choose input,.xr .xr-choose select{flex:1;min-width:0}.xr .xr-choose button{background:#09a65e}.xr .xr-choose button{white-space:nowrap}@media(max-width:640px){.xr .xr-choose{flex-wrap:wrap}.xr .xr-choose button{width:100%}}
</style>
<div class="xr"><div class="bar"><a class="btn gray" href="{{ route('admin.exam-section.routine') }}">Category</a><a class="btn gray" href="{{ route('admin.exam-section.categories') }}">Manage Categories</a><a class="btn gray" href="{{ route('admin.exam-section.subcategories') }}">Manage Sub-categories</a><a class="btn gray" href="{{ route('admin.exam-section.routine-topics') }}">Routine Topic Name</a></div>@if(session('success'))<div class="notice">{{session('success')}}</div>@endif @if(session('error'))<div class="notice error">{{session('error')}}</div>@endif @if($errors->any())<div class="notice error">{{implode(' | ',$errors->all())}}</div>@endif<h2>{{ $routine?'Edit Routine':'Create a Routine' }}</h2><div class="panel"><h3>Routine Details</h3><div class="inner"><form method="POST" enctype="multipart/form-data" action="{{ $routine?route('admin.exam-section.routine.update',$routine->id):route('admin.exam-section.routine.store') }}">@csrf @if($routine) @method('PUT') @endif<div class="fields">
<div><label for="xr-category">Category *</label>
<select id="xr-category" name="category_id" required @if($routine) disabled @endif>
<option value="">Select Category</option>
@foreach($categories as $categoryOption)
<option value="{{ $categoryOption->id }}" @selected((int)old('category_id',$routine->category_id??$cat->id)===(int)$categoryOption->id)>{{ $categoryOption->name }}</option>
@endforeach
</select>
@if($routine)<input type="hidden" name="category_id" value="{{ $routine->category_id }}">@endif
</div>
<div><label for="xr-subcategory">Sub-category <span id="xr-sub-required" style="color:#c53030"></span></label>
<select id="xr-subcategory" name="subcategory_id" @if($routine) disabled @endif>
<option value="">No Sub-category (Direct Category)</option>
@foreach($allSubcategories as $subOption)
<option value="{{ $subOption->id }}" data-category="{{ $subOption->category_id }}" @selected((int)old('subcategory_id',$routine->subcategory_id??$subId)===(int)$subOption->id)>{{ $subOption->name }}</option>
@endforeach
</select>
@if($routine)<input type="hidden" name="subcategory_id" value="{{ $routine->subcategory_id }}">@endif
<small id="xr-subhelp" style="color:#536987;display:block;margin-top:5px"></small>
</div>
<div><label>Routine Name *</label><input name="name" value="{{old('name',$routine->name??'')}}" required></div>
<div class="wide"><label>Header</label><div class="xr-toolbar"><button type="button" data-command="bold"><b>B</b></button><button type="button" data-command="italic"><i>I</i></button><button type="button" data-command="underline"><u>U</u></button><button type="button" data-command="insertUnorderedList">• List</button><button type="button" data-command="insertOrderedList">1. List</button><button type="button" data-command="createLink">Link</button></div><div id="xr-editor" class="xr-editor" contenteditable="true">{{ old('header',$routine->header??'') }}</div><textarea id="xr-header" name="header" hidden>{{old('header',$routine->header??'')}}</textarea></div>
<div class="wide xr-selected-topic" id="xr-topic-display">
<strong>Subject — Category / Sub-category</strong>
<div class="xr-pills"><span class="xr-pill" id="xr-topic-label">{{ $routine ? (($routine->subcategory_id && ($selectedSubcategory = ($allSubcategories->firstWhere('id',(int)$routine->subcategory_id))) ? $selectedSubcategory->name : $cat->name)) : ($subId && ($selectedSubcategory = $allSubcategories->firstWhere('id',(int)$subId)) ? $selectedSubcategory->name : $cat->name) }}</span></div>
<small class="xr-mini">Sub-category থাকলে সেটির নাম, না থাকলে Category-এর নাম দেখাবে। Department নির্বাচন প্রয়োজন নেই।</small>
</div>
@if($routine)
<div class="wide xr-tagbox"><h3>Exam Name</h3>
@foreach(['exam'=>'Main Exam Name','revision'=>'Revision Exam Name','model'=>'Model Exam Name'] as $kind=>$label)
<div class="xr-tagline"><label>{{ $label }}</label><div class="xr-pills" data-kind="{{ $kind }}">@foreach(($editLabels[$kind]??collect()) as $entry)<span class="xr-pill" data-value="{{ $entry->value }}"><span>{{ $entry->value }}</span><button type="button" class="xr-pill-remove" aria-label="Remove">×</button></span>@endforeach</div><div class="xr-choose"><input class="xr-new-tag" data-kind="{{ $kind }}" maxlength="190" placeholder="{{ $label }} লিখুন"><button type="button" class="xr-add-tag" data-kind="{{ $kind }}">+ Add {{ $label }}</button></div></div>
@endforeach
</div>
@endif
<div><label>Full PDF</label>@if($routine && $routine->pdf_path)<div><a target="_blank" rel="noopener" href="{{ asset('storage/'.$routine->pdf_path) }}">View current PDF</a></div>@endif<input type="file" name="pdf" accept=".pdf,application/pdf"></div><div><label>Status</label><select name="status"><option @selected(old('status',$routine->status??'Draft')==='Draft')>Draft</option><option @selected(old('status',$routine->status??'Draft')==='Published')>Published</option></select></div><div style="display:flex;align-items:end"><button class="green">{{ $routine ? 'Save Changes' : 'Save Routine' }}</button></div></div></form></div></div><script>(function(){
const c=document.getElementById('xr-category'),s=document.getElementById('xr-subcategory');
const label=document.getElementById('xr-sub-required'),help=document.getElementById('xr-subhelp');
function sync(){
 const previouslySelected=s.value;let any=false;
 Array.from(s.options).forEach(o=>{if(!o.value)return;let same=o.dataset.category===c.value;o.hidden=!same;o.disabled=!same;if(same)any=true;});
 if(s.selectedOptions.length&&s.selectedOptions[0].disabled)s.value='';
 s.required=any && !s.disabled;
 label.textContent=any?'*':'';
 help.textContent=any?'এই Category-তে Sub-category আছে, একটি নির্বাচন করুন।':'এই Category-তে Sub-category নেই; Routine সরাসরি Category-এর অধীনে Save হবে।';
}
const topicLabel=document.getElementById('xr-topic-label');
function syncTopic(){if(!topicLabel)return;const sub=s.selectedOptions[0];const cat=c.selectedOptions[0];topicLabel.textContent=(sub&&sub.value&&!sub.disabled?sub.textContent:(cat&&cat.value?cat.textContent:'Select Category'))||'Select Category';}
c.addEventListener('change',()=>{s.value='';sync();syncTopic()});s.addEventListener('change',syncTopic);sync();syncTopic();
})();
(function(){
 const form=document.querySelector('.xr form[method="POST"]');if(!form)return;
 const editor=document.getElementById('xr-editor'),raw=document.getElementById('xr-header');
 if(editor&&raw){editor.innerHTML=raw.value;form.addEventListener('submit',()=>{raw.value=editor.innerHTML});
 document.querySelectorAll('.xr-toolbar button').forEach(btn=>btn.addEventListener('click',()=>{editor.focus();if(btn.dataset.command==='createLink'){const url=prompt('Enter URL');if(url && /^https?:\/\//i.test(url))document.execCommand('createLink',false,url)}else document.execCommand(btn.dataset.command,false,null)}));}
 function addTag(kind,value,label){const list=document.querySelector('.xr-pills[data-kind="'+kind+'"]');if(!list||!value||list.querySelector('[data-value="'+CSS.escape(value)+'"]'))return;const pill=document.createElement('span');pill.className='xr-pill';pill.dataset.value=value;const text=document.createElement('span');text.textContent=label||value;const remove=document.createElement('button');remove.type='button';remove.className='xr-pill-remove';remove.textContent='×';remove.setAttribute('aria-label','Remove');pill.append(text,remove);list.append(pill)}
 document.querySelectorAll('.xr-add-tag').forEach(b=>b.addEventListener('click',()=>{const kind=b.dataset.kind,select=document.querySelector('.xr-add-select[data-kind="'+kind+'"]'),input=document.querySelector('.xr-new-tag[data-kind="'+kind+'"]');if(select){const op=select.selectedOptions[0];if(op&&op.value)addTag(kind,op.value,op.textContent);select.value=''}else if(input){const val=input.value.trim();if(val)addTag(kind,val,val);input.value=''}}));
 document.querySelectorAll('.xr-pills').forEach(list=>list.addEventListener('click',e=>{if(e.target.classList.contains('xr-pill-remove'))e.target.closest('.xr-pill').remove()}));
 form.addEventListener('submit',()=>{document.querySelectorAll('.xr-pills').forEach(list=>{const kind=list.dataset.kind;list.querySelectorAll('.xr-pill').forEach(p=>{const inp=document.createElement('input');inp.type='hidden';inp.name='labels['+kind+'][]';inp.value=p.dataset.value;form.append(inp)})})});
})();</script></div>
@endsection
