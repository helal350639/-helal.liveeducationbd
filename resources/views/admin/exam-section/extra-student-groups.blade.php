@extends('dashboard')
@section('title','Extra Student Group Management')
@section('content')
<style>
.esg{padding:16px;background:#edf3f9;min-height:75vh;color:#173454;font-family:'Noto Sans Bengali',Arial,sans-serif}
.esg *{box-sizing:border-box}.esg .panel{background:#fff;border:1px solid #d9e5f2;border-radius:9px;padding:15px;margin:12px 0}
.esg h1{font-size:24px;margin:10px 0}.esg h2{font-size:18px;margin:0 0 12px}.esg a.btn,.esg button.btn{display:inline-block;background:#087fbf;color:#fff;border:0;border-radius:5px;padding:8px 12px;text-decoration:none;cursor:pointer;font-weight:700}
.esg .btn.red{background:#bf3a3a}.esg .btn.green{background:#079d68}.esg .grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(145px,1fr));gap:10px;align-items:end}
.esg label{display:block;font-weight:700;font-size:12px;margin-bottom:5px}.esg input{width:100%;height:36px;border:1px solid #c9d8e8;border-radius:5px;padding:7px}
.esg .cards{display:grid;grid-template-columns:1fr;gap:12px}.esg .group{border:1px solid #d2dfef;border-left:5px solid #7356bd;border-radius:8px;padding:13px;background:#fff}
.esg .group .heading{display:flex;justify-content:space-between;align-items:center;gap:8px;flex-wrap:wrap}.esg .meta{color:#526c86;margin:5px 0;font-size:13px}
.esg .actions{display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-top:10px}.esg details{margin-top:9px;border-top:1px solid #e7eef7;padding-top:9px}
.esg details summary{color:#0a5d9a;font-weight:700;cursor:pointer}.esg .alert{padding:10px;border-radius:5px;background:#e4f6eb;margin:10px 0}.esg .error{background:#fff0f0;color:#a72323}
.esg .group{width:100%;min-height:90px}.esg .group .heading{align-items:flex-start;flex-wrap:wrap}.esg .group-right{display:flex;align-items:center;justify-content:flex-end;gap:8px;flex-wrap:wrap;margin-left:auto}.esg .member-counter{font-weight:750;background:#e5f6ec;color:#086f4b;padding:7px 10px;border-radius:5px;white-space:nowrap}.esg .edit-right{margin:0;padding:0;border:0}.esg .edit-right summary{list-style:none;background:#087fbf;color:#fff;border-radius:5px;padding:8px 12px;cursor:pointer}.esg .edit-right summary::-webkit-details-marker{display:none}.esg .edit-right[open]{flex-basis:100%;order:10}.esg .edit-right form{min-width:min(700px,75vw);margin-top:12px}.esg .delete-right{margin:0}.esg .group .heading>div:first-child{flex:1 1 320px}
</style>
<div class="esg">
<a class="btn" href="{{ route('admin.exam-section.extra-students.index',['category'=>$cat->id] + ($sub ? ['subcategory_id'=>$sub->id] : [])) }}">← Back to Selected Students</a>
<h1>Extra Student — Group Management</h1>
<p>{{ $cat->name }} @if($sub) / {{ $sub->name }} @endif</p>
@if(session('success'))<div class="alert">{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert error">{{ implode(' | ', $errors->all()) }}</div>@endif
<div class="panel"><h2>+ Add Group Name</h2>
<form method="POST" action="{{ route('admin.exam-section.extra-student-groups.store',['category'=>$cat->id]) }}">@csrf
@if($sub)<input type="hidden" name="subcategory_id" value="{{ $sub->id }}">@endif
<div class="grid">
<div><label>Group Name *</label><input name="name" required maxlength="150" value="{{ old('name') }}" placeholder="e.g. Group A"></div>
<div><label>Merit List From</label><input type="number" min="1" name="merit_from" value="{{ old('merit_from') }}"></div>
<div><label>Merit List To</label><input type="number" min="1" name="merit_to" value="{{ old('merit_to') }}"></div>
<div><label>Mark From</label><input type="number" min="0" step="0.01" name="mark_from" value="{{ old('mark_from') }}"></div>
<div><label>Mark To</label><input type="number" min="0" step="0.01" name="mark_to" value="{{ old('mark_to') }}"></div>
<div><label>Sort Order</label><input type="number" min="0" name="sort_order" value="{{ old('sort_order',0) }}"></div>
<div><button type="submit" class="btn green">Save Group</button></div>
</div></form><p class="meta">Range তথ্য শুধু সংরক্ষিত হবে; স্বয়ংক্রিয় Merit/Result Calculation এখন যুক্ত হচ্ছে না।</p></div>
<div class="panel"><h2>Groups ({{ $groups->count() }})</h2><div class="cards">
@forelse($groups as $g)
<div class="group">
<div class="heading">
 <div><strong>{{ $g->name }}</strong><div class="meta">Merit List Range: <b>{{ $g->merit_from ?? '—' }} – {{ $g->merit_to ?? '—' }}</b> &nbsp; | &nbsp; Mark Range: <b>{{ $g->mark_from ?? '—' }} – {{ $g->mark_to ?? '—' }}</b></div></div>
 <div class="group-right"><span class="member-counter">{{ $g->member_count }} Student(s)</span>
 <a class="btn" style="background:#366ca4" href="{{ route('admin.exam-section.extra-student-groups.view',['category'=>$cat->id,'group'=>$g->id] + ($sub ? ['subcategory_id'=>$sub->id] : [])) }}"><i class="fa fa-eye"></i> View Students</a>
 <a class="btn green" href="{{ route('admin.exam-section.extra-student-groups.students',['category'=>$cat->id,'group'=>$g->id] + ($sub ? ['subcategory_id'=>$sub->id] : [])) }}">+ Add Students</a>
 <details class="edit-right"><summary class="btn">✎ Edit</summary>
 <form method="POST" action="{{ route('admin.exam-section.extra-student-groups.update',['category'=>$cat->id,'group'=>$g->id]) }}">@csrf @method('PUT')
 @if($sub)<input type="hidden" name="subcategory_id" value="{{ $sub->id }}">@endif
 <div class="grid">
 <div><label>Group Name</label><input name="name" required maxlength="150" value="{{ $g->name }}"></div>
 <div><label>Merit From</label><input type="number" min="1" name="merit_from" value="{{ $g->merit_from }}"></div>
 <div><label>Merit To</label><input type="number" min="1" name="merit_to" value="{{ $g->merit_to }}"></div>
 <div><label>Mark From</label><input type="number" min="0" step="0.01" name="mark_from" value="{{ $g->mark_from }}"></div>
 <div><label>Mark To</label><input type="number" min="0" step="0.01" name="mark_to" value="{{ $g->mark_to }}"></div>
 <div><label>Sort Order</label><input type="number" min="0" name="sort_order" value="{{ $g->sort_order }}"></div>
 <div><button type="submit" class="btn">Update Group</button></div>
 </div></form></details>
 <form class="delete-right" method="POST" action="{{ route('admin.exam-section.extra-student-groups.destroy',['category'=>$cat->id,'group'=>$g->id]) }}" onsubmit="return confirm('Delete group and unassign its members? Saved Extra Student selections will remain.');">@csrf @method('DELETE')
 @if($sub)<input type="hidden" name="subcategory_id" value="{{ $sub->id }}">@endif
 <button type="submit" class="btn red">Delete</button></form>
 </div>
</div>
</div>
@empty <p>No groups created yet.</p> @endforelse
</div></div>
</div>
@endsection
