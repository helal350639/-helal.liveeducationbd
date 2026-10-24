@extends('dashboard')
@section('content')
<section class="content-header"><h1>{{ $category->name }} — Subjects</h1></section>
<section class="content">
<style>
.lebd69-head{display:flex;gap:9px;flex-wrap:wrap;align-items:center;margin-bottom:15px}
.lebd69-scroll{overflow-x:auto}
.lebd69-table{width:100%;border-collapse:collapse;background:#fff}
.lebd69-table th,.lebd69-table td{padding:12px 11px;border:1px solid #e2e6ea;vertical-align:middle}
.lebd69-table th{background:#f7f8fa;white-space:nowrap;color:#222}
.lebd69-table tbody tr:hover{background:#f8fbff}
.lebd69-name{font-weight:600;word-break:break-word;min-width:160px}
.lebd69-actions{display:flex;gap:6px;flex-wrap:wrap;align-items:center;justify-content:flex-end}
.lebd69-actions form{display:inline;margin:0}
.lebd69-table th:last-child,.lebd69-table td:last-child{text-align:right;min-width:470px}
.lebd69-edit-row{display:none;background:#f7fbff}
.lebd69-edit-row.is-open{display:table-row}
.lebd69-edit-fields{display:flex;flex-wrap:wrap;gap:10px;align-items:end}
.lebd69-edit-fields label{display:block;font-size:12px}
.lebd69-edit-fields>div{min-width:150px;flex:1}
.lebd69-edit-fields input,.lebd69-edit-fields select{width:100%;max-width:350px}
</style>
<div class="lebd68-head">
<a class="btn btn-default" href="{{ route('admin.exam-mcq.index',['kind'=>$kind]) }}"><i class="fa fa-arrow-left"></i> Categories</a>
<strong>{{ $category->name }}</strong>
<a class="btn btn-default" href="{{ route('admin.exam-mcq.questions',['kind'=>$kind,'categoryId'=>$category->id]) }}">Category Direct Questions</a>
</div>
@if(session('success'))<div class="alert alert-success">{{session('success')}}</div>@endif
@if(session('error'))<div class="alert alert-danger">{{session('error')}}</div>@endif
@if($errors->any())<div class="alert alert-danger">{{implode(' | ',$errors->all())}}</div>@endif
<div class="box box-primary"><div class="box-header with-border"><h3 class="box-title">Create Subject</h3></div>
<div class="box-body"><form method="POST" action="{{ route('admin.exam-mcq.subjects.store',['kind'=>$kind,'categoryId'=>$category->id]) }}">@csrf
<div class="row"><div class="col-sm-5 form-group"><label>Subject Name</label><input type="text" name="name" class="form-control" maxlength="180" required></div>
<div class="col-sm-2 form-group"><label>Sort Order</label><input type="number" name="sort_order" value="0" min="0" max="999999" class="form-control"></div>
<div class="col-sm-3 form-group"><label>Status</label><select name="status" class="form-control"><option value="Active">Active</option><option value="Inactive">Inactive</option></select></div>
<div class="col-sm-2 form-group"><label>&nbsp;</label><button type="submit" class="btn btn-info form-control"><i class="fa fa-plus"></i> Add Subject</button></div></div>
</form></div></div>
<div class="box box-primary"><div class="box-header with-border"><h3 class="box-title">Subjects ({{count($subjects)}})</h3></div><div class="box-body">
@if($subjects->isEmpty())<p class="text-muted">No subjects created yet.</p>@else
<div class="lebd69-scroll"><table class="lebd69-table"><thead><tr><th>SL</th><th>Subject Name</th><th>Status</th><th>Chapters</th><th>Sort Order</th><th>Actions</th></tr></thead><tbody>
@foreach($subjects as $subject)
@php $chapterCount=($chapters[$subject->id] ?? collect())->count(); @endphp
<tr><td>{{ $loop->iteration }}</td><td class="lebd69-name"><i class="fa fa-folder text-primary"></i> {{ $subject->name }}</td><td><span class="label label-{{ $subject->status==='Active'?'success':'default' }}">{{ $subject->status }}</span></td><td>{{ $chapterCount }}</td><td>{{ $subject->sort_order ?? 0 }}</td>
<td><div class="lebd69-actions">
<a class="btn btn-info btn-sm" href="{{ route('admin.exam-mcq.subject.chapters',['kind'=>$kind,'categoryId'=>$category->id,'subjectId'=>$subject->id]) }}"><i class="fa fa-folder-open"></i> View Chapters</a>
<a class="btn btn-default btn-sm" href="{{route('admin.exam-mcq.questions',['kind'=>$kind,'categoryId'=>$category->id,'subject_id'=>$subject->id])}}">Subject Questions</a>
<a class="btn btn-primary btn-sm" href="{{route('admin.exam-mcq.picker',['kind'=>$kind,'categoryId'=>$category->id,'subject_id'=>$subject->id])}}">+ Direct MCQ</a>
<button type="button" class="btn btn-warning btn-sm" onclick="document.getElementById('lebd69-sub-edit-{{$subject->id}}').classList.toggle('is-open')"><i class="fa fa-pencil"></i> Edit</button>
<form method="POST" action="{{ route('admin.exam-mcq.manage.destroy',['kind'=>$kind,'level'=>'subject','id'=>$subject->id]) }}" onsubmit="return confirm('Delete this subject? Linked MCQs, Chapters and Routine selections will block deletion.');">@csrf @method('DELETE')<button class="btn btn-danger btn-sm" type="submit"><i class="fa fa-trash"></i> Delete</button></form>
</div></td></tr>
<tr class="lebd69-edit-row" id="lebd69-sub-edit-{{$subject->id}}"><td colspan="6"><form method="POST" action="{{route('admin.exam-mcq.manage.update',['kind'=>$kind,'level'=>'subject','id'=>$subject->id])}}">@csrf @method('PATCH')
<div class="lebd69-edit-fields"><div><label>Name</label><input class="form-control" name="name" value="{{$subject->name}}" maxlength="180" required></div><div><label>Sort Order</label><input class="form-control" type="number" name="sort_order" value="{{$subject->sort_order ?? 0}}" min="0" max="999999" required></div><div><label>Status</label><select name="status" class="form-control"><option value="Active" @selected($subject->status==='Active')>Active</option><option value="Inactive" @selected($subject->status==='Inactive')>Inactive</option></select></div><div><button class="btn btn-primary" type="submit">Save Changes</button></div></div></form></td></tr>
@endforeach
</tbody></table></div>@endif
</div></div>
</section>
@endsection
