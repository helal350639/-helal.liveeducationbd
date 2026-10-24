@extends('dashboard')
@section('title','Auto Add Questions')
@section('content')
<style>
.ex180{background:#f1f6fc;color:#17355d;padding:18px;min-height:76vh}.ex180 *{box-sizing:border-box}.ex180 h2{font-weight:750;font-size:24px;margin:3px 0 12px}.ex180 .block{background:white;border:1px solid #d4e4f2;border-radius:8px;margin:13px 0;padding:14px}.ex180 h3{margin:0 0 12px;font-size:17px;color:#bc2c2c}.ex180 .meta{display:flex;gap:20px;flex-wrap:wrap;font-size:13px}.ex180 .meta>div{min-width:160px}.ex180 .muted{font-size:12px;color:#66809b}.ex180 .notice{padding:11px;background:#fff2dc;border-left:4px solid #ed9b19;font-size:13px;margin:10px 0}.ex180 .okay{background:#e5f9ee;color:#0d7040;padding:11px}.ex180 .bad{background:#ffebeb;color:#af2537;padding:11px}.ex180 .table-wrap{overflow-x:auto}.ex180 table{width:100%;min-width:880px;border-collapse:collapse;font-size:13px}.ex180 th,.ex180 td{border:1px solid #e4ebf3;padding:10px;vertical-align:middle}.ex180 th{background:#f0f4f9}.ex180 input[type=number]{width:100px;padding:8px;border:1px solid #b8ccde;border-radius:4px}.ex180 .actions{display:flex;align-items:center;gap:10px;justify-content:flex-end;flex-wrap:wrap}.ex180 button,.ex180 .btn{border:0;border-radius:5px;padding:10px 14px;color:#fff;background:#0a91bf;cursor:pointer;text-decoration:none}.ex180 .btn.gray{background:#71869b}.ex180 button.done{background:#07a66b}.ex180 .summary{background:#eaf8ff;padding:12px;margin-top:10px;border:1px solid #b8e4fa;font-weight:bold}.ex180 .pill{display:inline-block;padding:4px 7px;background:#17b4d3;border-radius:4px;color:#fff}
</style>
<div class="ex180">
<h2>✎ Auto Add Questions</h2>
<a class="btn gray" href="{{ route('admin.exam-section.auto-question.exams',['category'=>$cat->id]+($sub?['subcategory_id'=>$sub->id]:[])) }}">← Back to Exam List</a>
<div class="block"><h3>Question Paper Information</h3><div class="meta"><div><b>Exam Paper No</b><br>{{ $exam }}</div><div><b>Category</b><br>{{ $cat->name }}</div>@if($sub)<div><b>Sub-category</b><br>{{ $sub->name }}</div>@endif<div><b>Routine</b><br>{{ $r->name }}</div><div><b>Exam Start</b><br>{{ $item->exam_start_at ?? 'Not scheduled' }}</div><div><b>Current Question Pool</b><br><span class="pill">{{ $saved->count() }}</span></div><div><b>Shared Questions Limit</b><br>{{ $groupTotal ?: 'Not set' }}</div></div>
<p class="muted" style="margin-top:12px">Topic, Department, Subject, Chapter এবং Range সরাসরি Saved Routine Item থেকে এসেছে। এখানে Filter পরিবর্তন করা যাবে না।</p></div>
@if(session('success'))<p class="okay">{{ session('success') }}</p>@endif
@if($errors->any())<div class="bad"><b>Save করা যায়নি:</b><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
@if(!$item)<div class="notice">এই Exam-এর Routine Item এখনো তৈরি হয়নি। আগে Routine View থেকে Exam Item ও Topics Save করুন।</div>@elseif(!count($rows))<div class="notice">Saved Routine Item-এ কোনো বৈধ Department/Subject/Chapter Topic নেই। আগে Routine Topic নির্বাচন করুন।</div>@else
<form method="POST" action="{{ route('admin.exam-section.auto-question.create.save',['category'=>$cat->id,'routine'=>$r->id]) }}">@csrf
<input type="hidden" name="exam" value="{{ $exam }}">
<div class="block"><h3>Routine Topic Selection — Auto Filters</h3><div class="table-wrap"><table><thead><tr><th>Category / Section</th><th>Department</th><th>Subject</th><th>Chapter</th><th>Routine Range</th><th>Available</th><th>Total Questions (Set Manually)</th><th>Difficulty</th></tr></thead><tbody>
@foreach($rows as $row)
<tr><td>{{ $row['kind'] === 'department' ? 'Department' : 'Non-Department' }}</td><td>{{ $row['department'] }}</td><td>{{ $row['subject'] }}</td><td>{{ $row['chapter'] }}</td><td>{{ $row['range'] }}</td><td><span class="pill">{{ $row['available'] }}</span></td><td><input class="ex180-qty" type="number" name="quantities[{{ $row['key'] }}]" min="0" max="{{ $row['available'] }}" required value="{{ old('quantities.'.$row['key'],$counts[$row['key']]??0) }}" data-available="{{ $row['available'] }}"></td><td class="muted">Later</td></tr>
@endforeach
</tbody></table></div><div class="summary">সব Row-তে নির্বাচিত প্রশ্ন: <span id="ex180-total">{{ $saved->count() }}</span> টি @if($groupTotal) · Shared Paper Limit: {{ $groupTotal }} @endif</div><p class="muted">একই MCQ ID একটি Exam-এ দ্বিতীয়বার নেওয়া হবে না। Quantity 0 দিলে ওই Topic থেকে কোনো প্রশ্ন যোগ হবে না। Available গণনা হলো Routine Range-এর ভেতর Active MCQ ID; পূর্বের Row-এর সঙ্গে মিল থাকলে বাস্তবে কম প্রশ্ন পাওয়া যেতে পারে।</p></div>
<div class="actions"><button type="submit" name="mode" value="save">Save Question Selection</button><button type="submit" class="done" name="mode" value="done">Save &amp; Done → Exam List</button></div></form>
@endif
@if($saved->isNotEmpty())<div class="block"><h3>Saved Question IDs ({{ $saved->count() }})</h3><div class="muted">{{ $saved->pluck('question_id')->implode(', ') }}</div></div>@endif
</div>
<script>document.addEventListener('DOMContentLoaded',function(){const qs=document.querySelectorAll('.ex180-qty');const total=document.getElementById('ex180-total');function update(){if(total)total.textContent=Array.from(qs).reduce((sum,el)=>sum+(Number(el.value)||0),0);}qs.forEach(el=>el.addEventListener('input',update));update();});</script>
@endsection
