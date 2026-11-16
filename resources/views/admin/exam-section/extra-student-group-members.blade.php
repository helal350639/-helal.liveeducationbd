@extends('dashboard')
@section('title','Extra Student — Group Members')
@section('content')
<style>
.esgv{padding:16px;background:#edf3f9;min-height:75vh;font-family:'Noto Sans Bengali',Arial,sans-serif;color:#173454}
.esgv *{box-sizing:border-box}.esgv .panel{background:#fff;border:1px solid #d9e5f2;border-radius:9px;padding:15px;margin:12px 0}
.esgv h1{font-size:23px;margin:12px 0 6px}.esgv .muted{color:#617891;font-size:13px}
.esgv .toolbar{display:flex;align-items:end;justify-content:space-between;gap:12px;flex-wrap:wrap}
.esgv .btn{display:inline-flex;align-items:center;gap:6px;padding:8px 12px;color:#fff;background:#087fbf;text-decoration:none;border:0;border-radius:5px;cursor:pointer;font-weight:700}
.esgv .search{display:flex;gap:8px;flex-wrap:wrap}.esgv input{height:36px;padding:7px 10px;border:1px solid #c9d8e8;border-radius:5px;min-width:220px}
.esgv .count{background:#e5f6ec;color:#086f4b;font-weight:700;padding:8px 12px;border-radius:5px}
.esgv .tablewrap{overflow-x:auto}.esgv table{width:100%;border-collapse:collapse;min-width:700px;font-size:13px}
.esgv th,.esgv td{border-bottom:1px solid #e4edf6;padding:7px 10px;text-align:left;vertical-align:middle}
.esgv th{background:#eef4fa;color:#173454}.esgv .pager{margin-top:12px}.esgv .mark-value{font-weight:700}.esgv .save-marks{margin-top:12px}.esgv .notice{padding:10px 12px;background:#fff7df;border:1px solid #f1d58b;border-radius:7px;color:#73530a;margin:10px 0}.esgv .success{padding:10px 12px;background:#e5f6ec;color:#086f4b;border-radius:7px;margin:10px 0;font-weight:700}
</style>
<div class="esgv">
<a class="btn" href="{{ route('admin.exam-section.extra-student-groups.index',['category'=>$cat->id] + ($sub ? ['subcategory_id'=>$sub->id] : [])) }}">← Back to Groups</a>
<h1>Group Students — {{ $current->name }}</h1>
<p class="muted">{{ $cat->name }} @if($sub) / {{ $sub->name }} @endif</p>
<div class="panel">
 @if(session('success'))<div class="success">{{ session('success') }}</div>@endif
 <div class="toolbar">
  <div><span class="count">{{ $memberCount }} Student(s)</span><p class="muted">Merit List Rank Range: {{ $current->merit_from ?? '—' }} – {{ $current->merit_to ?? '—' }} | Allowed Mark Range: {{ $current->mark_from ?? '—' }} – {{ $current->mark_to ?? '—' }}</p><p class="muted">প্রতিটি Exam-এর জন্য আলাদা Simulation Mark থাকবে। নিচের Auto Generate বাটনটি এই Group-এর সব শিক্ষার্থীর জন্য একসাথে Mark তৈরি করবে। আগে থেকে Range-এর মধ্যে থাকা Mark অপরিবর্তিত থাকবে; ফাঁকা বা Range-এর বাইরে থাকা Mark পূরণ/সংশোধন হবে। এগুলো Simulation Data, Official Exam Result নয়।</p></div>
  <form class="search" method="GET" action="{{ route('admin.exam-section.extra-student-groups.view',['category'=>$cat->id,'group'=>$current->id]) }}">
   <select name="exam_item_id" required style="height:36px;padding:7px;border:1px solid #c9d8e8;border-radius:5px;min-width:240px" onchange="this.form.submit()">
    @foreach($examItems as $examOption)<option value="{{ $examOption->id }}" @selected((int)$examItemId === (int)$examOption->id)>{{ $examOption->exam_name }} — {{ $examOption->routine_name }}</option>@endforeach
   </select>
   @if($sub)<input type="hidden" name="subcategory_id" value="{{ $sub->id }}">@endif
   <input type="search" name="q" value="{{ $search }}" placeholder="Search ID, name, department, contact" aria-label="Search group students">
   <button type="submit" class="btn">Search</button>
   <a class="btn" style="background:#617891" href="{{ route('admin.exam-section.extra-student-groups.view',['category'=>$cat->id,'group'=>$current->id] + ($sub ? ['subcategory_id'=>$sub->id] : [])) }}">Reset</a>
  </form>
 </div>
 <div class="notice"><strong>Auto Score:</strong> Mark From/To সীমার বাইরে কোনো Mark রাখা হবে না। এই বাটন চাপলে নির্বাচিত Exam-এর Group-এর সব Student একসাথে প্রসেস হবে; অন্য Exam-এর Mark বদলাবে না।</div>
 <form method="POST" action="{{ route('admin.exam-section.extra-student-groups.marks.auto-generate',['category'=>$cat->id,'group'=>$current->id] + ($sub ? ['subcategory_id'=>$sub->id] : [])) }}" onsubmit="return confirm('এই Exam-এর Group-এর সব Student-এর জন্য Simulation Mark তৈরি/সংশোধন করবেন? Range-এর মধ্যে থাকা আগের Mark অপরিবর্তিত থাকবে।');">@csrf
 @if($selectedExam)<input type="hidden" name="exam_item_id" value="{{ $selectedExam->id }}">@endif
 <div class="tablewrap"><table><thead><tr><th>SL</th><th>Student ID</th><th>Name</th><th>Department</th><th>Contact</th><th>Email</th><th>Status</th><th>Simulation Mark — {{ $selectedExam->exam_name ?? 'Select Exam' }}</th></tr></thead><tbody>
 @forelse($members as $member)
 <tr><td>{{ $loop->iteration }}</td><td>{{ $member->student_id }}</td><td>{{ $member->name }}</td><td>{{ ucfirst($member->department ?? '') }}</td><td>{{ $member->contact }}</td><td>{{ $member->email ?: '—' }}</td><td>{{ $member->status }}</td><td class="mark-value">{{ $member->marks !== null ? number_format((float)$member->marks, 2) : 'Not generated' }}</td></tr>
 @empty<tr><td colspan="8" style="text-align:center;padding:18px">No students found in this Group{{ $search !== '' ? ' for this search' : '' }}.</td></tr>@endforelse
 </tbody></table></div>
 <button class="btn save-marks" type="submit" @disabled(!$selectedExam || $memberCount < 1)>Auto Generate & Save All Simulation Marks for This Exam</button></form>
 @if(!$selectedExam)<p class="muted">এই Category/Sub-category-তে কোনো Published Exam পাওয়া যায়নি।</p>@endif

</div>
</div>
@endsection
