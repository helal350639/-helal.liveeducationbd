@extends('dashboard')
@section('title','পরীক্ষা বিভাগ — Auto Question')
@section('content')
<style>
.aq172{padding:18px 20px;background:#f3f7fd;min-height:74vh;color:#112b50;font-family:'Noto Sans Bengali',Arial,sans-serif}.aq172 *{box-sizing:border-box}.aq172 h1{font-size:29px;font-weight:750;margin:0 0 17px;color:#07295f}.aq172 .tabs{display:flex;flex-wrap:wrap;gap:10px;margin-bottom:20px}.aq172 .tabs a{border:1px solid #c8daf6;border-radius:10px;padding:10px 16px;background:white;color:#125298;text-decoration:none}.aq172 .tabs a.active{background:#0b58bb;color:#fff}.aq172 .panel{background:#fff;border:1px solid #dbe6f3;border-radius:15px;padding:18px;box-shadow:0 6px 22px #123c6a09}.aq172 .head{display:flex;justify-content:space-between;flex-wrap:wrap;align-items:center;gap:8px;margin-bottom:14px}.aq172 h2{font-size:19px;font-weight:700;margin:0}.aq172 .filters{display:grid;grid-template-columns:minmax(210px,2fr) minmax(130px,.8fr) auto auto;gap:9px;margin-bottom:15px;align-items:end}.aq172 label{font-size:12px;font-weight:700;margin-bottom:5px;display:block}.aq172 input,.aq172 select{height:39px;border:1px solid #cfdae9;border-radius:6px;background:#fff;padding:7px 10px;width:100%;color:#263e5a}.aq172 .btn{display:inline-flex;align-items:center;justify-content:center;border:0;border-radius:5px;padding:9px 12px;white-space:nowrap;text-decoration:none;font-size:12px;font-weight:700;cursor:pointer;color:#fff;gap:5px}.aq172 .btn.filter,.aq172 .btn.details{background:#00b8dd}.aq172 .btn.reset{background:#eff4fa;color:#344d6f;border:1px solid #d2dfef}.aq172 .btn.view{background:#448db7}.aq172 .btn.add{background:#07a860}.aq172 .btn.edit{background:#f2a005}.aq172 .btn.del{background:#dd483a}.aq172 .btn.setting{background:#7c56c5}.aq172 .btn:disabled{opacity:.55;cursor:not-allowed}.aq172 .tablewrap{overflow-x:auto}.aq172 table{border-collapse:collapse;width:100%;min-width:800px;font-size:12px}.aq172 th,.aq172 td{padding:12px 9px;border:1px solid #e7edf5;text-align:left;vertical-align:middle}.aq172 th{background:#f1f5fa;color:#1a3657;font-weight:700}.aq172 .rowname{display:flex;align-items:center;gap:9px}.aq172 .folder{width:35px;height:35px;border-radius:10px;background:#eaf2ff;display:grid;place-items:center;font-size:18px}.aq172 .count{background:#e0f3fe;color:#12628f;padding:5px 9px;border-radius:4px;display:inline-block;font-weight:700}.aq172 .status{background:#09ad5c;color:white;border-radius:4px;padding:4px 7px;font-size:10px;font-weight:700}.aq172 .actions{display:flex;gap:6px;align-items:center;justify-content:flex-end}.aq172 .actions form{margin:0}.aq172 .actions .btn.details{width:239px;min-width:239px;text-align:center;flex-shrink:0}.aq172 .muted{color:#6881a2;font-size:12px}.aq172 .editbox{border:1px solid #acd2ed;background:#f1f8ff;padding:14px;border-radius:9px;margin-bottom:16px}.aq172 .editbox[hidden]{display:none!important}.aq172 .editfields{display:grid;grid-template-columns:2fr .8fr .8fr auto;gap:10px;align-items:end}.aq172 .alert{padding:11px;border-radius:6px;margin-bottom:12px}.aq172 .success{background:#e5f8ec;color:#176b35}.aq172 .error{background:#ffeded;color:#a32121}@media(max-width:850px){.aq172{padding:12px}.aq172 .filters,.aq172 .editfields{grid-template-columns:1fr 1fr}.aq172 h1{font-size:23px}.aq172 .actions{justify-content:flex-start}}
</style>
<div class="aq172">
<h1>পরীক্ষা বিভাগ — Auto Question</h1>
<div class="tabs">
<a href="{{ route('admin.exam-section.categories') }}">Category</a>
<a href="{{ route('admin.exam-section.subcategories') }}">Sub-category</a>
<a href="{{ route('admin.exam-section.routine') }}">Routine</a>
<a class="active" href="{{ route('admin.exam-section.auto-question') }}">Auto Question</a>
</div>
<div class="panel">
<div class="head"><h2>Active Categories ({{ $categories->total() }})</h2><span class="muted">Category-তে Sub-category থাকলে Sub-category, না থাকলে Exam List দেখাবে।</span></div>
@if(session('success'))<div class="alert success">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert error">{{ session('error') }}</div>@endif
@if($errors->any())<div class="alert error">{{ implode(' | ', $errors->all()) }}</div>@endif
<form method="GET" action="{{ route('admin.exam-section.auto-question') }}" class="filters">
<div><label for="aq172search">Category Name</label><input id="aq172search" name="search" value="{{ $search }}" placeholder="Search category name..."></div>
<div><label for="aq172order">Display Order</label><select name="order" id="aq172order"><option value="sort" @selected($order==='sort')>Display Order</option><option value="newest" @selected($order==='newest')>Newest First</option><option value="oldest" @selected($order==='oldest')>Oldest First</option><option value="name" @selected($order==='name')>Name A–Z</option></select></div>
<button type="submit" class="btn filter">⚑ Filter</button><a class="btn reset" href="{{ route('admin.exam-section.auto-question') }}">Reset</a>
</form>
<div class="tablewrap"><table><thead><tr><th>SL</th><th>ID</th><th>Category Name</th><th>Sub-categories</th><th>Students</th><th>Total Exam</th><th>Complete Exam</th><th>Pending Exam</th><th>Status</th><th>Created At</th><th>Updated At</th><th style="text-align:right">Actions</th></tr></thead><tbody>
@forelse($categories as $row)
<tr><td>{{ $categories->firstItem()+$loop->index }}</td><td>{{ $row->id }}</td>
<td><div class="rowname"><span class="folder">📁</span><strong>{{ $row->name }}</strong></div></td>
<td><span class="count">{{ $row->subcategory_count }}</span></td>
<td><span class="count">{{ $studentCounts['categories'][$row->id] ?? 0 }}</span></td>
<td><span class="count">{{ $examCounts[$row->id]['total'] ?? 0 }}</span></td>
<td><span class="count" style="background:#e1f7eb;color:#087948">{{ $examCounts[$row->id]['complete'] ?? 0 }}</span></td>
<td><span class="count" style="background:#fff2da;color:#9c6200">{{ $examCounts[$row->id]['pending'] ?? 0 }}</span></td>
<td><span class="status">Active</span></td>
<td>{{ $row->created_at ? \Carbon\Carbon::parse($row->created_at)->format('d M Y h:i A') : '—' }}</td><td>{{ $row->updated_at ? \Carbon\Carbon::parse($row->updated_at)->format('d M Y h:i A') : '—' }}</td>
<td><div class="actions"><a class="btn add" title="Manage Students" href="{{ route('admin.exam-section.student-enrollments',$row->id) }}"><i class="fa fa-users"></i> Students</a>
@if($row->subcategory_count > 0)
<a class="btn details" href="{{ route('admin.exam-section.auto-question.subcategories', $row->id) }}">▤ View Sub-category</a>
@else
<a class="btn view" href="{{ route('admin.exam-section.auto-question.exams', $row->id) }}">▤ View Exam List</a>
<a class="btn setting" title="Shared Paper Settings" href="{{ route('admin.exam-section.auto-question.paper-settings', $row->id) }}"><i class="fa fa-cog"></i> Paper Setting</a>
@endif
</div></td></tr>
@empty <tr><td colspan="12" style="text-align:center;padding:24px">No active categories found.</td></tr> @endforelse
</tbody></table></div>
<div style="margin-top:14px">{{ $categories->links() }}</div>
</div></div>
@endsection
