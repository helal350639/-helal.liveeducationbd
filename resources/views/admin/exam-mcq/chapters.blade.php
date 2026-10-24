@extends('dashboard')
@section('content')
<section class="content-header"><h1>{{ $subject->name }} — Chapters</h1></section>
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
<div style="display:flex;gap:9px;flex-wrap:wrap;align-items:center;margin-bottom:15px">
<a href="{{route('admin.exam-mcq.detail',['kind'=>$kind,'categoryId'=>$category->id])}}" class="btn btn-default"><i class="fa fa-arrow-left"></i> Subjects</a>
<strong>{{ $category->name }} / {{ $subject->name }}</strong>
<a class="btn btn-default" href="{{route('admin.exam-mcq.questions',['kind'=>$kind,'categoryId'=>$category->id,'subject_id'=>$subject->id])}}">Subject Direct Questions</a>
</div>
@if(session('success'))<div class="alert alert-success">{{session('success')}}</div>@endif
@if(session('error'))<div class="alert alert-danger">{{session('error')}}</div>@endif
@if($errors->any())<div class="alert alert-danger">{{implode(' | ',$errors->all())}}</div>@endif
<div class="box box-primary"><div class="box-header with-border"><h3 class="box-title">Create Chapter</h3></div>
<div class="box-body"><form method="POST" action="{{route('admin.exam-mcq.chapters.store',['kind'=>$kind,'categoryId'=>$category->id,'subjectId'=>$subject->id])}}">@csrf
<div class="row"><div class="col-sm-5 form-group"><label>Chapter Name</label><input class="form-control" name="name" required maxlength="180"></div>
<div class="col-sm-2 form-group"><label>Sort Order</label><input class="form-control" type="number" name="sort_order" min="0" max="999999" value="0"></div>
<div class="col-sm-3 form-group"><label>Status</label><select class="form-control" name="status"><option value="Active">Active</option><option value="Inactive">Inactive</option></select></div>
<div class="col-sm-2 form-group"><label>&nbsp;</label><button class="btn btn-info form-control"><i class="fa fa-plus"></i> Add Chapter</button></div></div>
</form></div></div>
<div class="box box-primary"><div class="box-header with-border"><h3 class="box-title">Chapters ({{count($chapters)}})</h3></div><div class="box-body">
@if($chapters->isEmpty())<p class="text-muted">No chapters added yet.</p>@else
<div class="lebd69-scroll"><table class="lebd69-table"><thead><tr><th>SL</th><th>Chapter Name</th><th>Status</th><th>Linked MCQs</th><th>Sort Order</th><th>Actions</th></tr></thead><tbody>
@foreach($chapters as $chapter)
<tr><td>{{$loop->iteration}}</td><td class="lebd69-name"><i class="fa fa-book text-primary"></i> {{$chapter->name}}</td><td><span class="label label-{{$chapter->status==='Active'?'success':'default'}}">{{$chapter->status}}</span></td><td>{{(int)($chapterCounts[$chapter->id] ?? 0)}}</td><td>{{$chapter->sort_order ?? 0}}</td><td><div class="lebd69-actions">
<a class="btn btn-info btn-sm" href="{{route('admin.exam-mcq.questions',['kind'=>$kind,'categoryId'=>$category->id,'subject_id'=>$subject->id,'chapter_id'=>$chapter->id])}}"><i class="fa fa-list"></i> View All Questions</a>
<a class="btn btn-primary btn-sm" href="{{route('admin.exam-mcq.picker',['kind'=>$kind,'categoryId'=>$category->id,'subject_id'=>$subject->id,'chapter_id'=>$chapter->id])}}"><i class="fa fa-plus"></i> Add MCQ</a>
<button class="btn btn-warning btn-sm" type="button" onclick="document.getElementById('lebd69-ch-edit-{{$chapter->id}}').classList.toggle('is-open')"><i class="fa fa-pencil"></i> Edit</button>
<form method="POST" action="{{route('admin.exam-mcq.manage.destroy',['kind'=>$kind,'level'=>'chapter','id'=>$chapter->id])}}" onsubmit="return confirm('Delete this chapter? Linked MCQs and Routine selections will block deletion.');">@csrf @method('DELETE')<button class="btn btn-danger btn-sm" type="submit"><i class="fa fa-trash"></i> Delete</button></form>
</div></td></tr>
<tr class="lebd69-edit-row" id="lebd69-ch-edit-{{$chapter->id}}"><td colspan="6"><form method="POST" action="{{route('admin.exam-mcq.manage.update',['kind'=>$kind,'level'=>'chapter','id'=>$chapter->id])}}">@csrf @method('PATCH')
<div class="lebd69-edit-fields"><div><label>Name</label><input name="name" class="form-control" maxlength="180" required value="{{$chapter->name}}"></div><div><label>Sort Order</label><input name="sort_order" class="form-control" type="number" min="0" max="999999" required value="{{$chapter->sort_order ?? 0}}"></div><div><label>Status</label><select class="form-control" name="status"><option value="Active" @selected($chapter->status==='Active')>Active</option><option value="Inactive" @selected($chapter->status==='Inactive')>Inactive</option></select></div><div><button class="btn btn-primary" type="submit">Save Changes</button></div></div></form></td></tr>
@endforeach
</tbody></table></div>@endif
</div></div>
</section>
@endsection
