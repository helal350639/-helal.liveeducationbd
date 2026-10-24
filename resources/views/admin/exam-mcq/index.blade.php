@extends('dashboard')
@section('content')
<section class="content-header">
    <h1>MCQ for Exam — {{ $kind === 'department' ? 'Department' : 'Non-Department' }}</h1>
</section>
<section class="content">
<style>
.lebd66-tabs{display:flex;gap:12px;flex-wrap:wrap;margin-bottom:18px}
.lebd66-tabs .btn{font-weight:700;padding:11px 22px}
.lebd66-card{border:1px solid #d7e4f2;border-radius:9px;background:#f9fbff;margin:10px 0;padding:14px}
.lebd66-card .head{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap}
.lebd66-card .name{font-size:16px;font-weight:700;color:#133a65}
.lebd66-card .actions{display:flex;flex-wrap:wrap;align-items:center;gap:8px;margin-top:9px}
.lebd67-manage-actions{display:flex;align-items:center;flex-wrap:wrap;gap:8px;margin-top:11px;padding-top:9px;border-top:1px solid #e2eaf4}
.lebd67-manage-actions .lebd67-label{font-size:12px;font-weight:700;color:#59718a;margin-right:5px}
.lebd66-card .meta{color:#60748a;font-size:13px;margin-top:5px}
.lebd66-card .count{display:inline-block;border-radius:5px;background:#e2efff;color:#175da2;font-weight:700;padding:4px 8px;margin-left:6px}
.lebd66-edit{display:none;padding:13px;margin-top:12px;background:#fff;border:1px solid #cad9e7;border-radius:7px}
.lebd66-edit.is-open{display:block}
.lebd66-edit .fields{display:grid;grid-template-columns:minmax(180px,2fr) minmax(90px,1fr) minmax(120px,1fr);gap:10px}
.lebd66-edit label{display:block;font-size:12px;margin-bottom:4px}
.lebd66-edit .form-control{width:100%}
.lebd66-add{display:grid;grid-template-columns:2fr 1fr 1fr auto;gap:12px;align-items:end}
@media(max-width:760px){.lebd66-add,.lebd66-edit .fields{grid-template-columns:1fr}}
</style>
<style>
.lebd70-scroll{overflow-x:auto}.lebd70-table{width:100%;border-collapse:collapse;background:#fff;min-width:950px}
.lebd70-table th,.lebd70-table td{border:1px solid #e0e5eb;padding:12px 10px;vertical-align:middle}
.lebd70-table th{background:#f6f8fa;color:#253040;white-space:nowrap}.lebd70-table tbody tr:hover{background:#f8fbff}
.lebd70-name{font-weight:700;min-width:190px;word-break:break-word}.lebd70-actions{display:flex;gap:6px;align-items:center;justify-content:flex-end;flex-wrap:wrap}
.lebd70-actions form{display:inline;margin:0}.lebd70-action-cell{min-width:480px;text-align:right}
.lebd70-edit-row{display:none;background:#f5faff}.lebd70-edit-row.is-open{display:table-row}
.lebd70-edit-fields{display:flex;flex-wrap:wrap;gap:10px;align-items:end}.lebd70-edit-fields>div{flex:1;min-width:130px}.lebd70-edit-fields label{display:block;font-size:12px}
</style>

<div class="lebd66-tabs">
    <a class="btn {{ $kind === 'department' ? 'btn-primary' : 'btn-default' }}" href="{{ route('admin.exam-mcq.index',['kind'=>'department']) }}"><i class="fa fa-building"></i> Department</a>
    <a class="btn {{ $kind === 'non-department' ? 'btn-primary' : 'btn-default' }}" href="{{ route('admin.exam-mcq.index',['kind'=>'non-department']) }}"><i class="fa fa-folder-open"></i> Non-Department</a>
    <a class="btn btn-default" href="{{ route('admin.exam-mcq.manage.index',['kind'=>$kind]) }}"><i class="fa fa-pencil"></i> All Category / Subject / Chapter Edit &amp; Delete</a>
</div>
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
@if($errors->any())<div class="alert alert-danger">{{ implode(' | ',$errors->all()) }}</div>@endif
<div class="box box-primary">
 <div class="box-header with-border"><h3 class="box-title">MCQ for Exam — {{ $kind === 'department' ? 'Department' : 'Non-Department' }}</h3></div>
 <div class="box-body">
  <form method="POST" action="{{ route('admin.exam-mcq.categories.store',['kind'=>$kind]) }}">@csrf
   <div class="lebd66-add">
    <div><label>Category Name</label><input name="name" class="form-control" maxlength="180" placeholder="Category name" required></div>
    <div><label>Sort Order</label><input name="sort_order" type="number" min="0" max="999999" value="0" class="form-control"></div>
    <div><label>Status</label><select name="status" class="form-control"><option value="Active">Active</option><option value="Inactive">Inactive</option></select></div>
    <div><button type="submit" class="btn btn-info"><i class="fa fa-plus"></i> Add Category</button></div>
   </div>
  </form>
 </div>
</div>
<div class="box box-primary"><div class="box-header with-border"><h3 class="box-title">Categories ({{ $categories->count() }})</h3></div>
<div class="box-body">
@if($categories->isEmpty())<p class="text-muted">No categories have been created yet.</p>@else
<div class="lebd70-scroll"><table class="lebd70-table"><thead><tr><th>SL</th><th>Category Name</th><th>Status</th><th>Subjects</th><th>Linked MCQs</th><th>Sort Order</th><th class="lebd70-action-cell">Actions</th></tr></thead><tbody>
@foreach($categories as $category)
<tr><td>{{ $loop->iteration }}</td>
<td class="lebd70-name"><i class="fa fa-folder-open text-primary"></i> {{ $category->name }}</td>
<td><span class="label {{ $category->status === 'Active' ? 'label-success' : 'label-default' }}">{{ $category->status }}</span></td>
<td>{{ $subjectCounts[$category->id] ?? 0 }}</td><td>{{ $categoryCounts[$category->id] ?? 0 }}</td><td>{{ $category->sort_order ?? 0 }}</td>
<td class="lebd70-action-cell"><div class="lebd70-actions">
<a class="btn btn-info btn-sm" href="{{ route('admin.exam-mcq.detail',['kind'=>$kind,'categoryId'=>$category->id]) }}"><i class="fa fa-folder-open"></i> View Subjects</a>
<a class="btn btn-default btn-sm" href="{{ route('admin.exam-mcq.questions',['kind'=>$kind,'categoryId'=>$category->id]) }}">Direct Questions</a>
<a class="btn btn-primary btn-sm" href="{{ route('admin.exam-mcq.picker',['kind'=>$kind,'categoryId'=>$category->id]) }}"><i class="fa fa-plus"></i> Direct MCQ</a>
<button type="button" class="btn btn-warning btn-sm" onclick="document.getElementById('lebd70-cat-edit-{{ $category->id }}').classList.toggle('is-open')"><i class="fa fa-pencil"></i> Edit</button>
<form method="POST" action="{{ route('admin.exam-mcq.manage.destroy',['kind'=>$kind,'level'=>'category','id'=>$category->id]) }}" onsubmit="return confirm('Delete this category? Subjects, linked MCQs and Routine selections will block deletion.');">@csrf @method('DELETE')<button class="btn btn-danger btn-sm" type="submit"><i class="fa fa-trash"></i> Delete</button></form>
</div></td></tr>
<tr class="lebd70-edit-row" id="lebd70-cat-edit-{{ $category->id }}"><td colspan="7">
<form method="POST" action="{{ route('admin.exam-mcq.manage.update',['kind'=>$kind,'level'=>'category','id'=>$category->id]) }}">@csrf @method('PATCH')
<div class="lebd70-edit-fields">
<div><label>Category Name</label><input class="form-control" name="name" value="{{ $category->name }}" maxlength="180" required></div>
<div><label>Sort Order</label><input class="form-control" type="number" name="sort_order" min="0" max="999999" required value="{{ $category->sort_order ?? 0 }}"></div>
<div><label>Status</label><select class="form-control" name="status"><option value="Active" @selected($category->status === 'Active')>Active</option><option value="Inactive" @selected($category->status === 'Inactive')>Inactive</option></select></div>
<div><button class="btn btn-primary" type="submit">Save Changes</button> <button class="btn btn-default" type="button" onclick="document.getElementById('lebd70-cat-edit-{{ $category->id }}').classList.remove('is-open')">Cancel</button></div>
</div></form></td></tr>
@endforeach
</tbody></table></div>
@endif
</div></div>
</section>
@endsection
