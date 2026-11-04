@extends('dashboard')
@section('title','Add Extra Students to Group')
@section('content')
<style>
.esgpick{padding:16px;background:#edf3f9;min-height:75vh;color:#173454;font-family:'Noto Sans Bengali',Arial,sans-serif}.esgpick *{box-sizing:border-box}.esgpick .box{padding:14px;background:#fff;border:1px solid #d7e2ee;border-radius:9px;margin:12px 0}.esgpick .bar{display:flex;align-items:center;justify-content:space-between;gap:9px;flex-wrap:wrap}.esgpick .btn{display:inline-flex;align-items:center;gap:5px;padding:8px 12px;background:#087fbf;color:#fff;text-decoration:none;border:0;border-radius:5px;cursor:pointer;font-weight:700}.esgpick .btn.green{background:#079c61}.esgpick .btn.purple{background:#7052ba}.esgpick .btn.gray{background:#edf3fa;color:#23476c}.esgpick h1{font-size:24px;margin:12px 0}.esgpick .muted{font-size:13px;color:#60738e}.esgpick .search{display:flex;gap:8px;flex-wrap:wrap;align-items:center;margin:12px 0}.esgpick input[type=search]{border:1px solid #cbd5e1;border-radius:5px;padding:8px;width:300px;max-width:100%}.esgpick table{width:100%;border-collapse:collapse;font-size:13px}.esgpick th,.esgpick td{text-align:left;padding:7px 9px;border-bottom:1px solid #e2eaf2}.esgpick th{background:#f0f6fc}.esgpick .scroll{overflow-x:auto}.esgpick .count{font-weight:700;color:#0d6b53}.esgpick .alert{background:#e0f6e8;padding:10px;margin:8px 0}.esgpick .error{background:#fff0f0;color:#a52020;padding:10px;margin:8px 0}
</style>
<div class="esgpick">
<a class="btn gray" href="{{ route('admin.exam-section.extra-student-groups.index',['category'=>$cat->id] + ($sub ? ['subcategory_id'=>$sub->id] : [])) }}">← Back to Groups</a>
<h1>Add Students — {{ $current->name }}</h1>
<p>{{ $cat->name }} @if($sub) / {{ $sub->name }} @endif</p>
<div class="box bar"><span class="count">Group Members: {{ $memberCount }} &nbsp; | &nbsp; Available: {{ $availableCount }}</span><span class="muted">Only students previously saved in this Category/Sub-category can be added.</span></div>
@if(session('success'))<div class="alert">{{ session('success') }}</div>@endif
@if($errors->any())<div class="error">{{ implode(' | ', $errors->all()) }}</div>@endif
<div class="box">
<form class="search" method="GET" action="{{ route('admin.exam-section.extra-student-groups.students',['category'=>$cat->id,'group'=>$current->id]) }}">
@if($sub)<input type="hidden" name="subcategory_id" value="{{ $sub->id }}">@endif
<input type="search" name="q" value="{{ $search }}" placeholder="Search saved student name, ID, contact" aria-label="Search saved students">
<button class="btn" type="submit">Search</button>
<a class="btn gray" href="{{ route('admin.exam-section.extra-student-groups.students',['category'=>$cat->id,'group'=>$current->id] + ($sub ? ['subcategory_id'=>$sub->id] : [])) }}">Normal List</a>
<a class="btn purple" href="{{ route('admin.exam-section.extra-student-groups.students',['category'=>$cat->id,'group'=>$current->id,'shuffle'=>1] + ($sub ? ['subcategory_id'=>$sub->id] : []) + ($search!=='' ? ['q'=>$search] : [])) }}">↝ Shuffle — Next 25</a>
</form>
<p class="muted">@if($shuffle) Shuffled batch: 25 or fewer. Already shown batches will be skipped until available students are exhausted. @else Normal list: 25 students per page. @endif Already grouped students are excluded.</p>
<form method="POST" action="{{ route('admin.exam-section.extra-student-groups.students.add',['category'=>$cat->id,'group'=>$current->id]) }}">
@csrf @if($sub)<input type="hidden" name="subcategory_id" value="{{ $sub->id }}">@endif
<div class="bar"><label><input type="checkbox" id="esg-check-all"> Select All (This Page)</label><span>Selected: <strong id="esg-selected-count">0</strong></span></div>
<div class="scroll"><table><thead><tr><th>Select</th><th>Student ID</th><th>Name</th><th>Department</th><th>Contact</th></tr></thead><tbody>
@forelse($students as $student)
<tr><td><input class="esg-check" type="checkbox" name="pick_ids[]" value="{{ $student->pick_id }}"></td><td>{{ $student->student_id }}</td><td>{{ $student->name }}</td><td>{{ $student->department }}</td><td>{{ $student->contact }}</td></tr>
@empty <tr><td colspan="5">No unassigned saved students available.</td></tr> @endforelse
</tbody></table></div>
<div style="margin-top:12px"><button class="btn green" type="submit">✓ Done — Add Selected Students</button></div>
</form>
@if(!$shuffle)<div style="margin-top:12px">{{ $students->links() }}</div>@endif
</div></div>
<script>document.addEventListener('DOMContentLoaded',function(){const all=document.getElementById('esg-check-all'),checks=[...document.querySelectorAll('.esg-check')],count=document.getElementById('esg-selected-count');function update(){const n=checks.filter(x=>x.checked).length;count.textContent=n;all.checked=checks.length>0&&n===checks.length;all.indeterminate=n>0&&n<checks.length;}all.addEventListener('change',()=>{checks.forEach(x=>x.checked=all.checked);update();});checks.forEach(x=>x.addEventListener('change',update));update();});</script>
@endsection
