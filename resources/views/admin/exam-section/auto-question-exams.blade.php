@extends('dashboard')
@section('title','Auto Question — Exam List')
@section('content')
<style>
.aq174{padding:20px;background:#f3f7fd;min-height:70vh;color:#17345c;font-family:'Noto Sans Bengali',Arial,sans-serif}.aq174 *{box-sizing:border-box}.aq174 h1{margin:0 0 15px;font-size:27px;font-weight:750;color:#082d61}.aq174 .back{display:inline-block;padding:9px 13px;border-radius:6px;background:#e9f1fc;color:#165caa;text-decoration:none;margin-bottom:15px}.aq174 .panel{background:white;border:1px solid #d9e5f2;border-radius:13px;padding:18px;box-shadow:0 6px 18px #143c6a0b}.aq174 .caption{font-size:18px;font-weight:750;margin-bottom:6px}.aq174 .muted{font-size:12px;color:#67809c}.aq174 .filter{display:flex;gap:9px;flex-wrap:wrap;align-items:end;margin:17px 0}.aq174 .filter label{display:block;font-size:12px;font-weight:650;margin-bottom:5px}.aq174 .filter input,.aq174 .filter select{padding:9px 11px;min-width:190px;max-width:100%;border:1px solid #cfdae9;border-radius:6px;background:white;height:38px}.aq174 .btn{display:inline-flex;align-items:center;justify-content:center;padding:9px 11px;font-size:12px;font-weight:700;border:0;border-radius:5px;text-decoration:none;white-space:nowrap;color:white;background:#448db7;cursor:pointer}.aq174 .btn.info{background:#05b4d7}.aq174 .btn.primary{background:#0787ba}.aq174 .btn.green{background:#0aa66b}.aq174 .btn.orange{background:#ee9a00}.aq174 .btn.red{background:#e64a40}.aq174 .btn.gray{background:#edf3fa;color:#34506f}.aq174 .btn.disabled{opacity:.55;cursor:not-allowed}.aq174 .tablewrap{overflow-x:auto}.aq174 table{width:100%;border-collapse:collapse;min-width:850px;font-size:12px}.aq174 th,.aq174 td{padding:11px 10px;border:1px solid #e9eff5;vertical-align:middle;text-align:left}.aq174 th{background:#f0f4fa;color:#233d60}.aq174 .name{font-weight:700}.aq174 .badge{background:#e5f3fc;color:#1670aa;padding:5px 8px;border-radius:4px;font-weight:750}.aq174 .status{background:#08a962;color:#fff;border-radius:4px;padding:4px 8px;font-size:11px}.aq174 .actions{display:flex;gap:6px;flex-wrap:wrap;justify-content:flex-end}.aq174 .empty{text-align:center;padding:25px!important;color:#7188a3}.aq174 .folder{display:inline-grid;place-items:center;background:#eaf1ff;border-radius:8px;width:33px;height:33px;margin-right:8px}.aq174 .routine{background:#f7faff;font-weight:750}.aq174 .note{margin:14px 0 0;color:#6e8199;font-size:12px}@media(max-width:750px){.aq174{padding:10px}.aq174 h1{font-size:22px}.aq174 .actions{justify-content:flex-start}}
</style>
<div class="aq174"><h1>Auto Question — Exam List</h1>
<a class="back" href="{{ $sub ? route('admin.exam-section.auto-question.subcategories',$cat->id) : route('admin.exam-section.auto-question') }}">← {{ $sub ? 'Sub-categories' : 'All Categories' }}</a>
<div class="panel"><div class="caption">{{ $cat->name }} @if($sub) → {{ $sub->name }} @endif</div><div class="muted">Routine Edit-এ সংরক্ষিত Exam No অনুযায়ী তালিকা।</div>
<div class="filter"><div><label>Exam Name / No</label><input type="search" id="aq174-exam-search" placeholder="Search Exam-01..."></div><div><label>Routine</label><select id="aq174-routine-filter"><option value="">All Routines</option>@foreach($routines as $routine)<option value="{{ $routine->id }}">{{ $routine->name }}</option>@endforeach</select></div><button class="btn gray" type="button" id="aq174-exam-reset">Reset</button></div>
@if(session('success'))<div style="padding:10px;background:#e4f8ec;color:#08733b;margin-bottom:12px">{{ session('success') }}</div>@endif
<div class="tablewrap"><table id="aq174-exam-table"><thead><tr><th>SL</th><th>Routine ID</th><th>Routine Name</th><th>Exam Name / No</th><th>Type</th><th style="text-align:right">Actions</th></tr></thead><tbody>
@php $serial=0; @endphp
@foreach($routines as $routine)
@foreach(($labels[$routine->id] ?? collect()) as $label)
@php $serial++; @endphp
<tr data-search="{{ mb_strtolower($label->value.' '.$routine->name) }}" data-routine="{{ $routine->id }}"><td>{{ $serial }}</td><td>{{ $routine->id }}</td><td class="name">{{ $routine->name }}</td><td><span class="badge">{{ $label->value }}</span></td><td>Exam</td><td><div class="actions">
<a class="btn orange" href="{{ route('admin.exam-section.routine.items',$routine->id) }}" title="Open this Routine's Edit/Item page">✎ Edit</a>

<a class="btn primary" href="{{ route('admin.exam-section.auto-question.details',['category'=>$cat->id,'routine'=>$routine->id,'exam'=>$label->value]) }}">▤ Details View</a>
<a class="btn green" href="{{ route('admin.exam-section.auto-question.create',['category'=>$cat->id,'routine'=>$routine->id,'exam'=>$label->value]) }}">✚ Create Question</a>
<form method="POST" action="{{ route('admin.exam-section.auto-question.reset',['category'=>$cat->id,'routine'=>$routine->id]) }}" onsubmit="return confirm('Reset ONLY saved questions for {{ $label->value }}? Original question bank will not be deleted.')">@csrf<input type="hidden" name="exam" value="{{ $label->value }}"><button class="btn red" type="submit">↻ Reset Questions</button></form>
</div></td></tr>
@endforeach
@endforeach
@if($serial===0)<tr><td colspan="6" class="empty">No Exam Name has been added to the Routines here.</td></tr>@endif
</tbody></table></div>
<div class="note">Edit সংশ্লিষ্ট Routine Item পেজে যায়। Details View saved MCQ প্রদর্শন করে। Reset শুধু এই Exam-এর Question Links সরাবে; Create Question random unique MCQ সংরক্ষণ করে; এই ডিজাইন পরিবর্তনে কোনো Exam বা Question Delete হয় না।</div>
</div></div>
<script>document.addEventListener('DOMContentLoaded',function(){const q=document.getElementById('aq174-exam-search'),f=document.getElementById('aq174-routine-filter'),rows=document.querySelectorAll('#aq174-exam-table tbody tr[data-search]');function run(){let v=q.value.toLocaleLowerCase().trim(),r=f.value;rows.forEach(row=>{row.hidden=!(row.dataset.search.includes(v)&&(!r||row.dataset.routine===r));});}q.addEventListener('input',run);f.addEventListener('change',run);document.getElementById('aq174-exam-reset').addEventListener('click',function(){q.value='';f.value='';run();});});</script>
@endsection
