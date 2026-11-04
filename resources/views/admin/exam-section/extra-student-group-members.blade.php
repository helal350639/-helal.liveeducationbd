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
.esgv th{background:#eef4fa;color:#173454}.esgv .pager{margin-top:12px}
</style>
<div class="esgv">
<a class="btn" href="{{ route('admin.exam-section.extra-student-groups.index',['category'=>$cat->id] + ($sub ? ['subcategory_id'=>$sub->id] : [])) }}">← Back to Groups</a>
<h1>Group Students — {{ $current->name }}</h1>
<p class="muted">{{ $cat->name }} @if($sub) / {{ $sub->name }} @endif</p>
<div class="panel">
 <div class="toolbar">
  <div><span class="count">{{ $memberCount }} Student(s)</span><p class="muted">Merit List: {{ $current->merit_from ?? '—' }} – {{ $current->merit_to ?? '—' }} | Mark Range: {{ $current->mark_from ?? '—' }} – {{ $current->mark_to ?? '—' }}</p></div>
  <form class="search" method="GET" action="{{ route('admin.exam-section.extra-student-groups.view',['category'=>$cat->id,'group'=>$current->id]) }}">
   @if($sub)<input type="hidden" name="subcategory_id" value="{{ $sub->id }}">@endif
   <input type="search" name="q" value="{{ $search }}" placeholder="Search ID, name, department, contact" aria-label="Search group students">
   <button type="submit" class="btn">Search</button>
   <a class="btn" style="background:#617891" href="{{ route('admin.exam-section.extra-student-groups.view',['category'=>$cat->id,'group'=>$current->id] + ($sub ? ['subcategory_id'=>$sub->id] : [])) }}">Reset</a>
  </form>
 </div>
 <div class="tablewrap"><table><thead><tr><th>SL</th><th>Student ID</th><th>Name</th><th>Department</th><th>Contact</th><th>Email</th><th>Status</th></tr></thead><tbody>
 @forelse($members as $member)
 <tr><td>{{ $members->firstItem() + $loop->index }}</td><td>{{ $member->student_id }}</td><td>{{ $member->name }}</td><td>{{ ucfirst($member->department ?? '') }}</td><td>{{ $member->contact }}</td><td>{{ $member->email ?: '—' }}</td><td>{{ $member->status }}</td></tr>
 @empty<tr><td colspan="7" style="text-align:center;padding:18px">No students found in this Group{{ $search !== '' ? ' for this search' : '' }}.</td></tr>@endforelse
 </tbody></table></div>
 <div class="pager">{{ $members->links() }}</div>
</div>
</div>
@endsection
