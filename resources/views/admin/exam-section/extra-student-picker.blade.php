@extends('dashboard')
@section('title', 'Exam Section — Extra Student Picker')
@section('content')
<style>
.lebd-pick{padding:18px;background:#edf3f9;min-height:75vh;color:#16344e}
.lebd-pick *{box-sizing:border-box}.lebd-pick .box{background:#fff;border:1px solid #d7e2f0;border-radius:10px;padding:17px;margin:12px 0}
.lebd-pick a{color:#0e6aaf}.lebd-pick .btn{display:inline-block;padding:10px 15px;border:0;border-radius:6px;background:#087fbf;color:#fff;text-decoration:none;cursor:pointer}
.lebd-pick .btn.danger{background:#bb3a38}.lebd-pick .row{display:flex;gap:9px;flex-wrap:wrap;align-items:center}.lebd-pick input[type=text]{padding:10px;border:1px solid #cbd5e1;border-radius:6px;width:min(100%,360px)}
.lebd-pick .scroll{overflow-x:auto}.lebd-pick table{border-collapse:collapse;width:100%;min-width:680px}.lebd-pick th,.lebd-pick td{border-bottom:1px solid #e0e8f1;padding:10px;text-align:left}.lebd-pick th{background:#f0f6fc}
.lebd-pick .success{padding:10px;background:#dff6e8}.lebd-pick .error{padding:10px;background:#ffe6e6}.lebd-pick .hint{color:#5e7187;font-size:13px}
.lebd-pick .lebd-pick-add-actions{display:flex;flex-wrap:wrap;gap:12px;align-items:center;margin:10px 0 16px}.lebd-pick .lebd-pick-add-actions .btn{background:#0b9d67;font-weight:700}.lebd-pick .lebd-pick-add-actions .hint{flex:1 1 260px;line-height:1.6}
.lebd-pick .lebd-pick-more{margin:16px 0}.lebd-pick .lebd-pick-more > summary{list-style:none;display:inline-block}.lebd-pick .lebd-pick-more > summary::-webkit-details-marker{display:none}.lebd-pick .lebd-pick-search-head{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:8px}
.lebd-pick .lebd-pick-search-head h2{margin:0}
.lebd-pick .lebd-pick-add-inline{background:#0b9d67;font-weight:700}
@media(max-width:600px){.lebd-pick .lebd-pick-search-head{align-items:flex-start}.lebd-pick .lebd-pick-add-inline{width:100%;text-align:center}}

/* LEBD-05 compact selected-student rows — visual-only, no PHP/JS/route changes. */
.lebd-pick .lebd-selected-compact { padding:12px 14px; margin:10px 0; }
.lebd-pick .lebd-selected-compact > h2 { font-size:19px; line-height:1.25; margin:0 0 9px; }
.lebd-pick .lebd-selected-compact .scroll table { table-layout:fixed; font-size:12px; min-width:600px; }
.lebd-pick .lebd-selected-compact .scroll th,
.lebd-pick .lebd-selected-compact .scroll td { padding:3px 8px; line-height:1.2; vertical-align:middle; overflow-wrap:anywhere; }
.lebd-pick .lebd-selected-compact .scroll tbody tr { height:28px; }
.lebd-pick .lebd-selected-compact .scroll th:nth-child(1) { width:8%; }
.lebd-pick .lebd-selected-compact .scroll th:nth-child(2) { width:34%; }
.lebd-pick .lebd-selected-compact .scroll th:nth-child(3) { width:19%; }
.lebd-pick .lebd-selected-compact .scroll th:nth-child(4) { width:25%; }
.lebd-pick .lebd-selected-compact .scroll th:nth-child(5) { width:14%; }
.lebd-pick .lebd-selected-compact .scroll td form { margin:0; }
.lebd-pick .lebd-selected-compact .scroll td .btn.danger { padding:4px 9px; border-radius:4px; font-size:12px; line-height:1.2; white-space:nowrap; }
/* LEBD-05 scoped group UI; compact cards and assignment controls */
.lebd-pick .esg-toolbar{display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin:10px 0}
.lebd-pick .esg-toolbar .btn{background:#7155b6;font-weight:700}
.lebd-pick .esg-cards{display:grid;grid-template-columns:minmax(0,1fr);gap:9px;margin:10px 0}
.lebd-pick .esg-card{padding:10px 12px;border:1px solid #d4deec;border-left:5px solid #6952ba;background:#fff;border-radius:7px;min-height:74px}
.lebd-pick .esg-card strong{display:block;font-size:15px}
.lebd-pick .esg-card small{display:block;color:#5e7187;margin-top:4px}
.lebd-pick .esg-controls{display:flex;gap:9px;align-items:center;flex-wrap:wrap;margin:8px 0}
.lebd-pick .esg-controls select{height:33px;border:1px solid #bbcde0;border-radius:5px;max-width:270px;padding:4px}
.lebd-pick .esg-controls button{padding:7px 11px;font-size:12px}
.lebd-pick .lebd-selected-compact .scroll th:nth-child(n){width:auto}
.lebd-pick > h1 { margin:8px 0 10px; font-size:26px; line-height:1.2; }
.lebd-pick .lebd-pick-add-actions { margin:7px 0 9px; }
.lebd-pick > p.hint { margin:7px 0; }
@media(max-width:760px) {
 .lebd-pick .lebd-selected-compact .scroll th,
 .lebd-pick .lebd-selected-compact .scroll td { padding:4px 6px; }
}
</style>
<div class="lebd-pick">
 <a class="btn" href="{{ $sub ? route('admin.exam-section.subcategories',['category_id'=>$cat->id]) : route('admin.exam-section.categories') }}">← Back</a>
 <h1>Extra Student — {{ $cat->name }} @if($sub) / {{ $sub->name }} @endif</h1>
 <div class="lebd-pick-add-actions">
  <a class="btn" href="{{ route('admin.extra-students.index', ['exam_category' => $cat->id] + ($sub ? ['exam_subcategory' => $sub->id] : [])) }}" title="Open Extra Student module to select department and add a student"><i class="fa fa-user-plus" aria-hidden="true"></i> Add Extra Student</a>
  <span class="hint">Extra Student Module-এ গিয়ে Department নির্বাচন করে Student যোগ করুন। এরপর এই পেজে ফিরে এসে Pick Students থেকে Save করুন।</span>
 </div>
 <div class="esg-toolbar"><a class="btn" href="{{ route('admin.exam-section.extra-student-groups.index', ['category'=>$cat->id] + ($sub ? ['subcategory_id'=>$sub->id] : [])) }}">+ Add Group Name / Manage Groups</a><span class="hint">প্রতিটি Group-এর Merit List Range, Mark Range, Edit ও Delete আলাদা পেজে থাকবে।</span></div>
 @if($groups->isNotEmpty())<div class="esg-cards">
 @foreach($groups as $group)<div class="esg-card"><strong>{{ $group->name }}</strong><small>Merit List: {{ $group->merit_from ?? '—' }} – {{ $group->merit_to ?? '—' }}</small><small>Mark: {{ $group->mark_from ?? '—' }} – {{ $group->mark_to ?? '—' }}</small><small><b>{{ $group->member_count }} Student(s)</b></small><a href="{{ route('admin.exam-section.extra-student-groups.students',['category'=>$cat->id,'group'=>$group->id] + ($sub ? ['subcategory_id'=>$sub->id] : [])) }}">+ Add Students</a></div>@endforeach
 </div>@endif
 <p class="hint">Save করা Student-রা নিচের Selected Extra Students তালিকায় আছেন। আরও Student নিতে Add More Students খুলুন। মূল Student Data পরিবর্তন হয় না।</p>
 @if(session('success'))<p class="success">{{ session('success') }}</p>@endif
 @if($errors->any())<p class="error">{{ implode(' | ', $errors->all()) }}</p>@endif
 <div class="box lebd-selected-compact"><h2>Selected Extra Students ({{ $saved->count() }})</h2>
 @if($saved->isNotEmpty() && $groups->isNotEmpty())
 <form id="esg-assign-form" method="POST" action="{{ route('admin.exam-section.extra-student-groups.assign',['category'=>$cat->id]) }}">@csrf
 @if($sub)<input type="hidden" name="subcategory_id" value="{{ $sub->id }}">@endif
 <div class="esg-controls"><label for="esg-target">Move checked students to Group:</label><select id="esg-target" name="group_id"><option value="">Unassigned (no group)</option>@foreach($groups as $group)<option value="{{ $group->id }}">{{ $group->name }}</option>@endforeach</select><button class="btn" type="submit">Assign Selected</button><span class="hint">টেবিলের checkbox দিয়ে Student নির্বাচন করুন</span></div></form>
 @endif
 <div class="scroll"><table><thead><tr>@if($groups->isNotEmpty())<th style="width:42px"><input type="checkbox" aria-label="Select all saved students" onclick="document.querySelectorAll('.esg-select-pick').forEach(x=>x.checked=this.checked)"></th>@endif<th>ID</th><th>Name</th><th>Department</th><th>Contact</th><th>Group</th><th>Action</th></tr></thead><tbody>
 @forelse($saved as $record)<tr>@if($groups->isNotEmpty())<td><input class="esg-select-pick" form="esg-assign-form" type="checkbox" name="pick_ids[]" value="{{ $record->pick_id }}"></td>@endif<td>{{ $record->id }}</td><td>{{ $record->name }}</td><td>{{ $record->department ?? '—' }}</td><td>{{ $record->contact }}</td><td>{{ $assignedGroups[$record->pick_id] ?? 'Unassigned' }}</td><td>
 <form method="POST" action="{{ route('admin.exam-section.extra-students.remove',['category'=>$cat->id,'pick'=>$record->pick_id]) }}" onsubmit="return confirm('Remove this selection only?')">@csrf @method('DELETE')
 @if($sub)<input type="hidden" name="subcategory_id" value="{{ $sub->id }}">@endif
 <button type="submit" class="btn danger">Remove</button></form></td></tr>
 @empty<tr><td colspan="{{ $groups->isNotEmpty() ? 7 : 6 }}">No Extra Student selected in this Category/Sub-category.</td></tr>@endforelse
 </tbody></table></div></div>
 <details class="lebd-pick-more"><summary class="btn" style="background:#086fb4;font-weight:700">+ Add More Students / নতুন Student নির্বাচন</summary>
 <div class="box"><div class="lebd-pick-search-head"><h2>Search Extra Students</h2><a class="btn lebd-pick-add-inline" href="{{ route('admin.extra-students.index', ['exam_category' => $cat->id] + ($sub ? ['exam_subcategory' => $sub->id] : [])) }}"><i class="fa fa-user-plus" aria-hidden="true"></i> Add Extra Student</a></div>
  <form method="GET" class="row" action="{{ route('admin.exam-section.extra-students.index',['category'=>$cat->id]) }}">
   @if($sub)<input type="hidden" name="subcategory_id" value="{{ $sub->id }}">@endif
   <input name="q" type="text" value="{{ $q }}" placeholder="Name, ID, Contact, Email" aria-label="Search Extra Students">
   <button class="btn" type="submit">Search</button><a href="{{ route('admin.exam-section.extra-students.index',['category'=>$cat->id] + ($sub ? ['subcategory_id'=>$sub->id] : [])) }}">Reset</a>
  </form></div>
 <div class="box"><h2>Pick Students</h2>
  <form method="POST" action="{{ route('admin.exam-section.extra-students.store',['category'=>$cat->id]) }}">@csrf
   @if($sub)<input type="hidden" name="subcategory_id" value="{{ $sub->id }}">@endif
   <div class="scroll"><table><thead><tr><th>Select</th><th>ID</th><th>Name</th><th>Department</th><th>Contact</th></tr></thead><tbody>
   @forelse($students as $student)
    <tr><td><input type="checkbox" name="student_ids[]" value="{{ $student->id }}" @disabled(in_array((int)$student->id,$selectedIds,true))></td><td>{{ $student->id }}</td><td>{{ $student->name }} @if(in_array((int)$student->id,$selectedIds,true))<small>(Already selected)</small>@endif</td><td>{{ $student->department ?? '—' }}</td><td>{{ $student->contact }}</td></tr>
   @empty<tr><td colspan="5">No active Extra Student found.</td></tr>@endforelse
   </tbody></table></div><p><button class="btn" type="submit">Save Selected Students</button></p>
  </form><div>{{ $students->links() }}</div><p class="hint">এখানে শুধু এই Category/Sub-category-তে এখনো Save হয়নি এমন Student দেখানো হবে।</p>
 </div>
 </details>

</div>
@endsection
