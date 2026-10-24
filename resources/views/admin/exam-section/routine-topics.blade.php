@extends('dashboard')
@section('title','Exam Section — Routine Topic Name')
@section('content')
<style>
.ex143{background:#eef2f7;padding:16px;min-height:65vh;font-family:'Noto Sans Bengali',Arial,sans-serif;color:#172b4b}.ex143 a{text-decoration:none}.ex143-nav{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:16px}.ex143-nav a{padding:8px 11px;border:1px solid #cfdae7;border-radius:5px;background:white;color:#17467f}.ex143-nav a.on{background:#1363b9;color:white}.ex143-panel{background:white;border:1px solid #d7e1ec;border-top:3px solid #18b7e6;border-radius:6px;margin-bottom:16px;overflow:hidden}.ex143-panel h3{font-size:15px;color:#a11c1c;padding:13px;margin:0;border-bottom:1px solid #e4eaf1}.ex143-pad{padding:14px}.ex143-grid{display:grid;grid-template-columns:minmax(150px,2fr) minmax(120px,1fr) 110px 125px auto;gap:8px;align-items:end}.ex143 label{font-size:12px;display:block;font-weight:700;margin-bottom:5px}.ex143 input,.ex143 select{width:100%;height:36px;border:1px solid #cbd8e6;border-radius:4px;padding:6px 9px;background:#fff}.ex143 button,.ex143 .btn143{border:0;border-radius:4px;background:#0cb8df;color:white;padding:9px 11px;font-size:12px;cursor:pointer;display:inline-block;white-space:nowrap}.ex143 .edit{background:#f2a11a}.ex143 .delete{background:#de493b}.ex143 .mcq{background:#3985bd}.ex143 .muted{background:#6b788e}.ex143 table{width:100%;border-collapse:collapse;font-size:12px}.ex143 th,.ex143 td{border:1px solid #dee5ed;padding:10px;text-align:left;vertical-align:middle}.ex143 th{background:#f3f7fb}.ex143 .actions{display:flex;justify-content:flex-end;gap:6px;flex-wrap:wrap}.ex143 .actions form{margin:0}.ex143 .pill{display:inline-block;background:#12b46b;color:white;padding:3px 6px;border-radius:3px}.ex143 .off{background:#8391a4}.ex143 .editarea{background:#eff7ff;border:1px solid #bed6f2;padding:14px;margin-bottom:14px;border-radius:6px}.ex143 .editarea[hidden]{display:none}.ex143-note{color:#59708c;font-size:12px}.ex143 a.btn143{color:white}@media(max-width:850px){.ex143-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.ex143-grid button{grid-column:1/-1}.ex143{padding:8px}.ex143 table{min-width:670px}}

/* Fix145: explicit compact table cell layout. Kept isolated from the existing dashboard CSS. */
.ex143 .ex145-table-scroll{width:100%;overflow-x:auto;overflow-y:visible}
.ex143 table.ex145-table{width:100%!important;border-collapse:collapse!important;table-layout:auto!important;margin:0!important}
.ex143 table.ex145-table thead{display:table-header-group!important}
.ex143 table.ex145-table tbody{display:table-row-group!important}
.ex143 table.ex145-table tr{display:table-row!important;height:auto!important;min-height:0!important;max-height:none!important}
.ex143 table.ex145-table th,.ex143 table.ex145-table td{display:table-cell!important;vertical-align:middle!important;box-sizing:border-box!important;height:auto!important;min-height:0!important;max-height:none!important;padding:10px 9px!important;line-height:1.5!important;position:static!important}
.ex143 table.ex145-table td>*{max-height:none}
.ex143 table.ex145-table td.ex145-status-cell{width:105px!important;white-space:nowrap!important}
.ex143 table.ex145-table .ex145-badge{display:inline-flex!important;align-items:center!important;justify-content:center!important;width:auto!important;height:auto!important;min-height:0!important;max-height:30px!important;padding:4px 9px!important;margin:0!important;line-height:1.2!important;font-size:11px!important;font-weight:700!important;border-radius:4px!important;position:static!important;float:none!important;box-sizing:border-box!important;vertical-align:middle!important}
.ex143 table.ex145-table td.ex145-actions-cell{width:1%;min-width:315px!important;text-align:right!important}
.ex143 table.ex145-table td.ex145-actions-cell .actions{display:flex!important;flex-direction:row!important;flex-wrap:wrap!important;justify-content:flex-end!important;align-items:center!important;gap:6px!important;height:auto!important;min-height:0!important;max-height:none!important}
.ex143 table.ex145-table td.ex145-actions-cell .actions form{display:inline-flex!important;align-items:center!important;padding:0!important;margin:0!important}
.ex143 table.ex145-table td.ex145-actions-cell .actions button,.ex143 table.ex145-table td.ex145-actions-cell .actions a{display:inline-flex!important;align-items:center!important;justify-content:center!important;width:auto!important;height:auto!important;min-height:30px!important;padding:6px 9px!important;line-height:1.25!important;font-size:11px!important;text-decoration:none!important;vertical-align:middle!important}
.ex143 table.ex145-table td strong{font-weight:650}
@media(max-width:850px){.ex143 table.ex145-table{min-width:770px!important}}

/* Fix146: Isolated CSS-grid list. Avoids dashboard/global TABLE, TD and status pill stretch rules. */
.ex143 .ex146-scroll{overflow-x:auto;width:100%;padding:0;margin:0}
.ex143 .ex146-grid{min-width:900px;width:100%;border-top:1px solid #dbe3ef;border-left:1px solid #dbe3ef;font-family:inherit;font-size:12px}
.ex143 .ex146-row{display:grid!important;grid-template-columns:55px minmax(130px,2fr) 90px 90px 100px minmax(285px,2.7fr);align-items:center!important;min-height:45px!important;height:auto!important;max-height:none!important;position:relative;background:#fff}
.ex143 .ex146-with-parent .ex146-row{grid-template-columns:55px minmax(145px,2fr) minmax(120px,1.3fr) 85px 85px 80px minmax(290px,2.7fr)}
.ex143 .ex146-last .ex146-row{grid-template-columns:55px minmax(150px,2fr) minmax(120px,1.3fr) 90px 90px minmax(265px,2.7fr)}
.ex143 .ex146-row>div{box-sizing:border-box!important;min-width:0;min-height:0!important;height:auto!important;padding:10px 9px!important;border-right:1px solid #dbe3ef;border-bottom:1px solid #dbe3ef;align-self:stretch!important;display:flex!important;align-items:center!important;line-height:1.4!important;word-break:break-word}
.ex143 .ex146-row.ex146-heading{background:#f1f5fa!important;font-weight:700}
.ex143 .ex146-heading>div{background:#f1f5fa!important}
.ex143 .ex146-row .ex146-right{justify-content:flex-end}
.ex143 .ex146-row .ex146-controls{display:flex!important;flex-flow:row wrap!important;justify-content:flex-end!important;align-items:center!important;gap:5px!important}
.ex143 .ex146-status{display:inline-block!important;position:static!important;flex:0 0 auto!important;align-self:center!important;width:auto!important;height:auto!important;min-height:0!important;max-height:none!important;padding:4px 7px!important;border-radius:3px!important;font-size:11px!important;line-height:1.2!important;color:#fff!important;font-weight:700!important}
.ex143 .ex146-active{background:#09aa60!important}.ex143 .ex146-inactive{background:#76869b!important}
.ex143 .ex146-controls form{display:inline-flex!important;flex:0 0 auto!important;margin:0!important;padding:0!important}
.ex143 .ex146-controls .ex146-button{display:inline-flex!important;align-items:center!important;justify-content:center!important;flex:0 0 auto!important;margin:0!important;padding:7px 8px!important;border:0!important;border-radius:4px!important;color:#fff!important;font:inherit!important;font-size:11px!important;line-height:1.3!important;min-height:30px!important;height:auto!important;width:auto!important;text-decoration:none!important;cursor:pointer!important;white-space:nowrap!important}
.ex143 .ex146-viewq{background:#1c6ca8!important}.ex143 .ex146-view{background:#0bb8df!important}.ex143 .ex146-mcq{background:#3685be!important}.ex143 .ex146-edit{background:#e9a11c!important}.ex143 .ex146-delete{background:#d9473d!important}
.ex143 .ex146-empty{text-align:center;padding:14px;border-bottom:1px solid #dbe3ef}
@media(max-width:850px){.ex143 .ex146-grid{min-width:920px}}

/* Fix148: local, live filters - no controller/database query changes */
.ex143 .ex148-filter{display:grid;grid-template-columns:minmax(190px,2fr) minmax(170px,1.2fr) minmax(125px,.8fr) auto;gap:10px;align-items:end;margin-bottom:13px;background:#f5f9ff;border:1px solid #d4e3f5;padding:12px;border-radius:6px}
.ex143 .ex148-filter label{margin-bottom:5px;font-weight:700}
.ex143 .ex148-filter input,.ex143 .ex148-filter select{height:36px;background:#fff}
.ex143 .ex148-filter .ex148-reset{height:36px;background:#6a788b;padding:7px 14px}
.ex143 .ex148-filter-info{margin:0 0 9px;font-size:12px;color:#526988}
.ex143 .ex148-no-match{display:none;text-align:center;background:#fff9ea;border:1px solid #f2d8a1;padding:13px;font-size:12px}
@media(max-width:720px){.ex143 .ex148-filter{grid-template-columns:repeat(2,minmax(0,1fr))}.ex143 .ex148-filter .ex148-reset{width:100%}}

.ex143 .ex150-context{display:flex;gap:20px;align-items:center;background:#e7f4ff;padding:11px 14px;border:1px solid #beddf6;border-radius:6px;margin-bottom:15px}.ex143 .ex150-context a{font-weight:700;color:#075cb4}
/* Fix149: DB-backed cascading topic search */
.ex143 .ex149-finder{background:#f2f7ff;border:1px solid #d3e3f7;border-radius:7px;padding:14px;margin-bottom:14px}
.ex143 .ex149-finder h4,.ex143 .ex149-results h4{margin:0 0 12px;font-size:15px;font-weight:700;color:#12386e}
.ex143 .ex149-fields{display:grid;grid-template-columns:repeat(3,minmax(0,1fr)) auto;gap:10px;align-items:end}
.ex143 .ex149-fields select{height:38px}.ex143 .ex149-buttons{display:flex;gap:6px;align-items:center}
.ex143 .ex149-buttons .ex149-search{background:#0068c9;height:38px;padding:8px 16px}
.ex143 .ex149-clear{background:#718096;color:#fff;border-radius:4px;padding:10px 12px;display:inline-flex;align-items:center;height:38px}
.ex143 .ex149-help{margin:10px 0 0;color:#58718b;font-size:12px}
.ex143 .ex149-results{border:1px solid #d7e6f5;padding:13px;border-radius:7px;margin-bottom:16px;background:#fff}
.ex143 .ex149-cards{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:10px}
.ex143 .ex149-card{padding:12px;border:1px solid #cbdcef;border-radius:6px;display:flex;flex-direction:column;align-items:flex-start;gap:8px;background:#f9fbff}
.ex143 .ex149-card strong{font-size:13px}.ex143 .ex149-card small{color:#596b84}.ex143 .ex149-card span{font-size:12px}
.ex143 .ex149-card-actions{display:flex;flex-wrap:wrap;gap:6px}.ex143 .ex149-card-actions a{padding:7px 10px;color:white;background:#2788ca;border-radius:4px;font-size:11px}.ex143 .ex149-card-actions a+ a{background:#0ea5a7}
.ex143 .ex149-empty{padding:12px;color:#6e5360}.ex143 .ex149-list-label{font-size:12px;color:#61748e;margin:0 0 8px}
@media(max-width:900px){.ex143 .ex149-fields{grid-template-columns:repeat(2,minmax(0,1fr))}.ex143 .ex149-cards{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(max-width:540px){.ex143 .ex149-fields{grid-template-columns:1fr}.ex143 .ex149-cards{grid-template-columns:1fr}}
.ex143 .ex156-count{display:inline-block!important;background:#e8f2ff!important;color:#115aa0!important;border:1px solid #b8d5f5!important;border-radius:20px!important;padding:3px 8px!important;font-size:11px!important;font-weight:700!important;line-height:1.2!important;width:auto!important;height:auto!important;flex:0 0 auto!important}
</style>
<div class="ex143">
<div class="ex150-context"><a href="{{ route('admin.exam-section.routine-topics') }}">← Choose Type</a> <strong>{{ $kind==='department'?'Department':'Non-Department' }} — Routine Topics</strong></div><nav class="ex143-nav"><a href="{{ route('admin.exam-section.categories') }}">Category</a><a href="{{ route('admin.exam-section.subcategories') }}">Sub-category</a><a class="on" href="{{ route('admin.exam-section.routine-topics') }}">Routine Topic Name</a><a href="{{ route('admin.exam-section.routine') }}">Routine</a><a href="{{ route('admin.exam-section.auto-question') }}">Auto Question Create</a><a href="{{ route('admin.exam-section.packages') }}">Package Management</a></nav>
<h2 style="font-size:21px;font-weight:700;margin-bottom:12px">Routine Topic Name — Department &gt; Subject &gt; Chapter</h2>
<nav class="ex143-nav"><a class="{{ $level==='department'?'on':'' }}" href="{{ route('admin.exam-section.routine-topic.index',['level'=>'department','kind'=>$kind]) }}">Department Name</a><a class="{{ $level==='subject'?'on':'' }}" href="{{ route('admin.exam-section.routine-topic.index',['level'=>'subject','kind'=>$kind]) }}">Subject Name</a><a class="{{ $level==='chapter'?'on':'' }}" href="{{ route('admin.exam-section.routine-topic.index',['level'=>'chapter','kind'=>$kind]) }}">Chapter Name</a></nav>
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
@if($errors->any())<div class="alert alert-danger">{{ implode(' | ',$errors->all()) }}</div>@endif
@php $levelLabel=['department'=>'Department','subject'=>'Subject','chapter'=>'Chapter'][$level]; @endphp
<div class="ex143-panel"><h3>Add {{ $levelLabel }} Name</h3><div class="ex143-pad">
<form class="ex143-grid" method="POST" action="{{ route('admin.exam-section.routine-topic.store',$level) }}">@csrf <input type="hidden" name="kind" value="{{ $kind }}">
<div><label>{{ $levelLabel }} Name</label><input name="name" required maxlength="190" value="{{ old('name') }}" placeholder="Enter {{ $levelLabel }} name"></div>
@if($parentKey)<div><label>{{ $level==='subject'?'Department':'Subject' }} Name</label><select name="parent_id" required><option value="">Select</option>@foreach($parents as $p)<option value="{{ $p->id }}" @selected(old('parent_id',$parentId)==$p->id)>{{ $p->name }}</option>@endforeach</select></div>@else<div></div>@endif
<div><label>Sort Order</label><input type="number" name="sort_order" min="0" value="{{ old('sort_order',0) }}" required></div>
<div><label>Status</label><select name="status"><option>Active</option><option>Inactive</option></select></div>
<button type="submit">+ Add {{ $levelLabel }}</button></form></div></div>
<div class="ex143-panel"><h3>{{ $levelLabel }} Names ({{ $rows->count() }})</h3><div class="ex143-pad">
<div id="ex143-edit" class="ex143-editarea" hidden><strong>Edit {{ $levelLabel }}</strong>
<form id="ex143-editform" method="POST" class="ex143-grid" style="margin-top:12px">@csrf <input type="hidden" name="kind" value="{{ $kind }}"> @method('PUT')
<div><label>Name</label><input id="ex143-en" name="name" required></div>
@if($parentKey)<div><label>Parent</label><select id="ex143-ep" name="parent_id" required>@foreach($parents as $p)<option value="{{ $p->id }}">{{ $p->name }}</option>@endforeach</select></div>@else<div></div>@endif
<div><label>Sort Order</label><input id="ex143-es" type="number" min="0" name="sort_order" required></div>
<div><label>Status</label><select id="ex143-et" name="status"><option>Active</option><option>Inactive</option></select></div>
<div><button type="submit">Update</button> <button type="button" class="muted" onclick="document.getElementById('ex143-edit').hidden=true">Cancel</button></div></form></div>
{{-- Fix149: DB-backed Department > Subject > Chapter cascading finder. Does not modify topic data. --}}
@php
  $finderDepartments = \Illuminate\Support\Facades\DB::table('lebd_exam_routine_departments')->where('kind',$kind)->orderBy('sort_order')->orderBy('name')->get(['id','name']);
  $finderSubjects = \Illuminate\Support\Facades\DB::table('lebd_exam_routine_subjects')->whereIn('department_id',$finderDepartments->pluck('id'))->orderBy('sort_order')->orderBy('name')->get(['id','department_id','name']);
  $finderChapters = \Illuminate\Support\Facades\DB::table('lebd_exam_routine_chapters')->whereIn('subject_id',$finderSubjects->pluck('id'))->orderBy('sort_order')->orderBy('name')->get(['id','subject_id','name']);
  $finderDepartmentId = (int)request()->query('find_department',0);
  $finderSubjectId = (int)request()->query('find_subject',0);
  $finderChapterId = (int)request()->query('find_chapter',0);
  $finderSubmitted = request()->query('find') === '1';
  $finderValidDepartment = $finderDepartments->contains('id',$finderDepartmentId);
  $finderValidSubject = $finderSubjects->contains(fn($s)=>(int)$s->id===$finderSubjectId && (int)$s->department_id===$finderDepartmentId);
  $finderValidChapter = $finderChapters->contains(fn($c)=>(int)$c->id===$finderChapterId && (int)$c->subject_id===$finderSubjectId);
  $finderMatches = collect();
  if($finderSubmitted && $finderValidDepartment){
      $subjectIds = $finderSubjects->where('department_id',$finderDepartmentId)->pluck('id')->all();
      $finderMatches = $finderChapters->filter(fn($c)=>in_array((int)$c->subject_id,array_map('intval',$subjectIds),true));
      if($finderSubjectId){ $finderMatches = $finderValidSubject ? $finderMatches->where('subject_id',$finderSubjectId) : collect(); }
      if($finderChapterId){ $finderMatches = $finderValidChapter ? $finderMatches->where('id',$finderChapterId) : collect(); }
  }
  $finderQuestionCounts = $finderMatches->isEmpty() ? [] : \Illuminate\Support\Facades\DB::table('lebd_exam_routine_question_links')
      ->where('topic_level','chapter')->whereIn('topic_id',$finderMatches->pluck('id'))
      ->select('topic_id',\Illuminate\Support\Facades\DB::raw('COUNT(*) as total'))
      ->groupBy('topic_id')->pluck('total','topic_id')->all();
@endphp
<div class="ex149-finder" id="ex149-finder">
  <h4>Department → Subject → Chapter Filter</h4>
  <form method="GET" action="{{ route('admin.exam-section.routine-topic.index',['level'=>$level,'kind'=>$kind]) }}" class="ex149-fields" id="ex149-form">
    <input type="hidden" name="find" value="1"><input type="hidden" name="kind" value="{{ $kind }}">
    <div><label for="ex149-dept">Department Name</label><select name="find_department" id="ex149-dept" required>
      <option value="">Select Department</option>
      @foreach($finderDepartments as $d)<option value="{{ $d->id }}" @selected($finderDepartmentId==(int)$d->id)>{{ $d->name }}</option>@endforeach
    </select></div>
    <div><label for="ex149-subject">Subject Name</label><select name="find_subject" id="ex149-subject" disabled>
      <option value="">All Subjects</option>
    </select></div>
    <div><label for="ex149-chapter">Chapter Name</label><select name="find_chapter" id="ex149-chapter" disabled>
      <option value="">All Chapters</option>
    </select></div>
    <div class="ex149-buttons"><button type="submit" class="ex149-search">🔎 Search</button><a href="{{ route('admin.exam-section.routine-topic.index',['level'=>$level,'kind'=>$kind]) }}" class="ex149-clear">Reset</a></div>
  </form>
  <p class="ex149-help">Department নির্বাচন করলে শুধু সেই Department-এর Subject, এরপর Subject নির্বাচন করলে সংশ্লিষ্ট Chapter দেখাবে। Chapter না বাছলেও সংশ্লিষ্ট সব Chapter খুঁজে পাওয়া যাবে।</p>
</div>
@if($finderSubmitted)
<div class="ex149-results" id="ex149-results">
  <h4>Search Results — {{ $finderMatches->count() }} Chapter</h4>
  @if(!$finderValidDepartment)
    <p class="ex149-empty">সঠিক Department নির্বাচন করে Search করুন।</p>
  @elseif($finderMatches->isEmpty())
    <p class="ex149-empty">নির্বাচিত Department/Subject-এর অধীনে কোনো Chapter পাওয়া যায়নি।</p>
  @else
    <div class="ex149-cards">
    @foreach($finderMatches as $chapter)
      @php $sub=$finderSubjects->firstWhere('id',$chapter->subject_id); @endphp
      <article class="ex149-card">
        <strong>📁 {{ $chapter->name }}</strong>
        <small>{{ $finderDepartments->firstWhere('id',$finderDepartmentId)->name ?? '' }} → {{ $sub->name ?? 'Subject' }}</small>
        <span>Linked MCQ: {{ $finderQuestionCounts[$chapter->id]??0 }}</span>
        <div class="ex149-card-actions">
          <a href="{{ route('admin.exam-section.routine-topic.questions',['level'=>'chapter','id'=>$chapter->id,'kind'=>$kind]) }}">View / Add MCQ</a>
          <a href="{{ route('admin.exam-section.routine-topic.index',['level'=>'chapter','parent_id'=>$chapter->subject_id,'kind'=>$kind]) }}">View Chapter List</a>
        </div>
      </article>
    @endforeach
    </div>
  @endif
</div>
@endif
<p class="ex149-list-label">{{ $levelLabel }} Names ({{ $rows->count() }})</p>
<div class="ex146-scroll" aria-label="{{ $levelLabel }} list">
  <div class="ex146-grid {{ $parentKey ? 'ex146-with-parent' : 'ex146-no-parent' }} {{ $level==='chapter' ? 'ex146-last' : '' }}" role="table">
    <div class="ex146-row ex146-heading" role="row">
      <div role="columnheader">SL</div><div role="columnheader">{{ $levelLabel }} Name</div>
      @if($parentKey)<div role="columnheader">Parent</div>@endif
      <div role="columnheader">Status</div><div role="columnheader">Sort Order</div>
      @if($level!=='chapter')<div role="columnheader">{{ $level==='department'?'Subjects':'Chapters' }}</div>@endif
      <div class="ex146-right" role="columnheader">Actions</div>
    </div>
    @forelse($rows as $r)
      <div class="ex146-row ex146-data" role="row" data-ex148-name="{{ mb_strtolower($r->name) }}" data-ex148-parent="{{ $parentKey ? $r->$parentKey : $r->sort_order }}" data-ex148-status="{{ $r->status }}">
        <div role="cell">{{ $loop->iteration }}</div>
        <div role="cell" style="gap:8px;flex-wrap:wrap"><strong>📁 {{ $r->name }}</strong><span class="ex156-count" title="Directly linked MCQs">{{ $questionCounts[$r->id] ?? 0 }} MCQ</span></div>
        @if($parentKey)<div role="cell">{{ $parents->firstWhere('id',$r->$parentKey)->name ?? '—' }}</div>@endif
        <div role="cell"><span class="ex146-status {{ $r->status==='Active' ? 'ex146-active' : 'ex146-inactive' }}">{{ $r->status }}</span></div>
        <div role="cell">{{ $r->sort_order }}</div>
        @if($level!=='chapter')<div role="cell">{{ $counts[$r->id]??0 }}</div>@endif
        <div class="ex146-controls" role="cell">
          @if($level!=='chapter')<a class="ex146-button ex146-view" href="{{ route('admin.exam-section.routine-topic.index',['level'=>$level==='department'?'subject':'chapter','parent_id'=>$r->id,'kind'=>$kind]) }}">📂 View {{ $level==='department'?'Subjects':'Chapters' }}</a>@endif
          <a class="ex146-button ex146-viewq" href="{{ route('admin.exam-section.routine-topic.questions',['level'=>$level,'id'=>$r->id,'kind'=>$kind,'view'=>'linked']) }}">👁 View Questions ({{ $questionCounts[$r->id] ?? 0 }})</a>
          <a class="ex146-button ex146-mcq" href="{{ route('admin.exam-section.routine-topic.questions',['level'=>$level,'id'=>$r->id,'kind'=>$kind]) }}">+ Add MCQ</a>
          <button class="ex146-button ex146-edit" type="button" data-id="{{ $r->id }}" data-name="{{ $r->name }}" data-sort="{{ $r->sort_order }}" data-status="{{ $r->status }}" data-parent="{{ $parentKey ? $r->$parentKey : '' }}" onclick="ex143Edit(this)">✎ Edit</button>
          <form method="POST" action="{{ route('admin.exam-section.routine-topic.delete',['level'=>$level,'id'=>$r->id,'kind'=>$kind]) }}" onsubmit="return confirm('Delete this {{ $levelLabel }}?')">@csrf <input type="hidden" name="kind" value="{{ $kind }}"> @method('DELETE')<button class="ex146-button ex146-delete" type="submit">Delete</button></form>
        </div>
      </div>
    @empty
      <div class="ex146-empty" role="row">No {{ $levelLabel }} names found.</div>
    @endforelse
  </div>
</div></div></div></div>
<script>function ex143Edit(b){let box=document.getElementById('ex143-edit');document.getElementById('ex143-editform').action=@json(url('/admin/exam-section/routine-topics/'.$level))+'/'+encodeURIComponent(b.dataset.id)+'?kind='+encodeURIComponent(@json($kind));document.getElementById('ex143-en').value=b.dataset.name;document.getElementById('ex143-es').value=b.dataset.sort;document.getElementById('ex143-et').value=b.dataset.status;let p=document.getElementById('ex143-ep');if(p)p.value=b.dataset.parent;box.hidden=false;box.scrollIntoView({behavior:'smooth',block:'center'});}</script>

<script>
(function(){
  const subjects=@json($finderSubjects->map(fn($s)=>['id'=>(int)$s->id,'department_id'=>(int)$s->department_id,'name'=>$s->name])->values());
  const chapters=@json($finderChapters->map(fn($c)=>['id'=>(int)$c->id,'subject_id'=>(int)$c->subject_id,'name'=>$c->name])->values());
  function init(){
    const d=document.getElementById('ex149-dept'),s=document.getElementById('ex149-subject'),c=document.getElementById('ex149-chapter');
    if(!d||!s||!c)return;
    const initialS=@json($finderSubjectId),initialC=@json($finderChapterId);
    const populate=(select,records,placeholder,selected)=>{
      select.replaceChildren(new Option(placeholder,''));
      records.forEach(r=>select.add(new Option(r.name,String(r.id))));
      select.disabled=records.length===0;
      if(selected&&records.some(r=>r.id===Number(selected)))select.value=String(selected);
    };
    const onSubject=(selected)=>populate(c,chapters.filter(r=>r.subject_id===Number(s.value)),'All Chapters',selected);
    const onDepartment=(selectedS,selectedC)=>{
      populate(s,subjects.filter(r=>r.department_id===Number(d.value)),'All Subjects',selectedS);
      if(s.value)onSubject(selectedC);else populate(c,[],'All Chapters',null);
    };
    d.addEventListener('change',()=>onDepartment(null,null));
    s.addEventListener('change',()=>onSubject(null));
    onDepartment(initialS,initialC);
  }
  if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',init);else init();
})();
</script>
@endsection
