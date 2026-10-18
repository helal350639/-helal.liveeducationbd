@extends('dashboard')
@section('title','MCQ Job Preparation - Category List')
@section('content')
<style>
.jp-catwrap{max-width:1500px;margin:auto}.jp-catbox{background:#fff;border:1px solid #dde4ed;border-top:3px solid #2196d0;border-radius:4px;margin-bottom:20px}.jp-cathead{padding:15px 18px;border-bottom:1px solid #e5eaf0;display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap}.jp-cathead h3{margin:0;font-size:18px;color:#a00000}.jp-catbody{padding:18px}.jp-catactions{display:flex;align-items:center;gap:6px;flex-wrap:wrap}.jp-cattable th{background:#f5f8fb;white-space:nowrap}.jp-cattable td{vertical-align:middle!important}.jp-catmuted{color:#708096}.jp-count{display:inline-block;border-radius:4px;background:#e5f4fc;color:#075a88;font-weight:700;padding:5px 10px}.jp-catfooter{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap}.jp-catinput{max-width:760px}.jp-formrow{margin-bottom:16px}@media(max-width:750px){.jp-cattable{min-width:780px}.jp-catbody{padding:12px}}

/* Category listing refinements: aligned columns and action buttons */
.jp-cattable{width:100%;table-layout:fixed;margin-bottom:18px}
.jp-cattable th,.jp-cattable td{vertical-align:middle!important;padding:12px 10px!important}
.jp-cattable thead th{font-size:13px;color:#30445a;font-weight:700;border-bottom:2px solid #dce6f0}
.jp-cattable tbody tr:hover{background:#f6faff}
.jp-cattable .jp-centered{text-align:center}
.jp-cattable .jp-actions-col{width:530px;text-align:center}
.jp-cattable .jp-action-buttons jp-pretty-actions{display:flex;gap:7px;align-items:center;justify-content:center;flex-wrap:nowrap}
.jp-cattable .jp-action-buttons jp-pretty-actions .btn{display:inline-flex;align-items:center;justify-content:center;gap:5px;min-width:74px;min-height:33px;border-radius:5px;padding:6px 9px;white-space:nowrap;font-weight:600}
.jp-cattable .jp-action-buttons jp-pretty-actions form{display:inline-flex!important;margin:0}
.jp-cattable .jp-date{font-size:12px;color:#475569;white-space:nowrap}
.jp-cattable .jp-catname{color:#15345d;font-size:14px}
@media(max-width:1100px){.jp-cattable{min-width:1150px}}
@media(max-width:650px){.jp-cattable .jp-action-buttons jp-pretty-actions{gap:5px}.jp-cattable .jp-actions-col{width:530px}}
.jp-category-icon{display:inline-flex;align-items:center;justify-content:center;width:36px;height:36px;border-radius:11px;margin-right:8px;vertical-align:middle;font-size:18px}.jp-category-name{vertical-align:middle}@media(max-width:750px){.jp-category-icon{width:32px;height:32px}}
.jp-cattable .jp-action-buttons.jp-pretty-actions{display:flex;gap:7px;align-items:center;justify-content:center;flex-wrap:wrap}
.jp-cattable .jp-action-buttons.jp-pretty-actions .btn{min-height:36px;min-width:82px;padding:8px 10px;font-weight:600}
.jp-cattable .jp-action-buttons.jp-pretty-actions .jp-add-mcq{min-height:40px;min-width:112px;font-size:13px}
.jp-cattable .jp-actions-col{width:530px!important;min-width:430px}
.jp-cattable{width:100%;min-width:1210px}
@media(max-width:700px){.jp-cattable .jp-actions-col{min-width:430px}}
</style>
<style>
/* Full-width management tools: stays inside the existing AdminLTE dashboard */
.jp-fullwide{width:100%!important;max-width:none!important;margin:0!important}
.jp-filters{display:flex;align-items:flex-end;gap:10px;flex-wrap:wrap;margin-bottom:18px;padding:14px;background:#f5f9fd;border:1px solid #e0eaf2;border-radius:7px}
.jp-filters .jp-search{flex:2;min-width:210px}.jp-filters .jp-select{flex:1;min-width:145px}.jp-filters label{display:block;margin-bottom:5px;font-size:12px;color:#536579}
.jp-filters .jp-submit{display:flex;gap:7px}.jp-filters .jp-submit .btn{min-height:35px}
.jp-pretty-actions{display:flex;align-items:center;justify-content:center;gap:7px;flex-wrap:wrap}
.jp-pretty-actions .btn{border-radius:5px!important;min-height:34px;min-width:76px;padding:7px 10px!important;display:inline-flex;align-items:center;justify-content:center;gap:5px;font-weight:600;white-space:nowrap}
.jp-pretty-actions form{margin:0!important;display:inline-flex}
.jp-wide-table{width:100%;margin-bottom:14px}.jp-wide-table th{background:#f4f8fc!important;color:#31445b}.jp-wide-table td,.jp-wide-table th{vertical-align:middle!important;padding:11px 10px!important}
.jp-wide-table tbody tr:hover{background:#f7fbff}
.jp-wide-table .jp-action-cell{min-width:330px;text-align:center}
@media(max-width:880px){.jp-wide-table{min-width:980px}.jp-filters .jp-search{flex-basis:100%}}
</style>
@php $kindName = $kind === 'department' ? 'Department' : 'Non-Department'; @endphp
<section class="content-header"><h1>MCQ Job Preparation <small>{{ $kindName }} / Categories</small></h1></section>
<section class="content jp-catwrap jp-fullwide">
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
@if($errors->any())<div class="alert alert-danger">{{ implode(' | ', $errors->all()) }}</div>@endif
<div class="jp-catbox">
  <div class="jp-cathead"><h3>List of {{ $kindName }} Categories</h3>
    <div class="jp-catactions">
      <a class="btn btn-info" href="{{ route('admin.job-preparation.categories.create',['kind'=>$kind]) }}"><i class="fa fa-plus"></i> Add Category</a>
      <a class="btn btn-primary" href="{{ route('admin.job-preparation.subjects.create-global',['kind'=>$kind]) }}"><i class="fa fa-plus"></i> Add Subject</a>
      <a class="btn btn-success" href="{{ route('admin.job-preparation.chapters.create-global',['kind'=>$kind]) }}"><i class="fa fa-plus"></i> Add Chapter</a>
    </div>
  </div>
  <div class="jp-catbody">
    <form method="GET" class="jp-filters" action="{{ url()->current() }}">
 <div class="jp-search"><label>Search by name</label><input class="form-control" name="search" value="{{ request('search','') }}" placeholder="Search name..."></div>
 <div class="jp-select"><label>Status</label><select class="form-control" name="status"><option value="">All Status</option><option value="Active" {{ request('status')==='Active'?'selected':'' }}>Active</option><option value="Inactive" {{ request('status')==='Inactive'?'selected':'' }}>Inactive</option></select></div>
 <div class="jp-select"><label>Sort By</label><select name="sort" class="form-control"><option value="order" {{ request('sort','order')==='order'?'selected':'' }}>Display Order</option><option value="latest" {{ request('sort')==='latest'?'selected':'' }}>Newest First</option><option value="name" {{ request('sort')==='name'?'selected':'' }}>Name A–Z</option></select></div>
 <div class="jp-submit"><button type="submit" class="btn btn-info"><i class="fa fa-filter"></i> Filter</button><a href="{{ url()->current() }}" class="btn btn-default">Reset</a></div>
</form>
    <div class="table-responsive"><table class="table table-bordered table-hover jp-cattable jp-wide-table">
      <colgroup><col style="width:4%"><col style="width:4%"><col style="width:17%"><col style="width:7%"><col style="width:8%"><col style="width:7%"><col style="width:12%"><col style="width:12%"><col style="width:530px"></colgroup>
      <thead><tr><th style="width:60px">SL</th><th style="width:70px">ID</th><th>Category Name</th><th class="jp-centered">Subjects</th><th class="jp-centered">Direct MCQs</th><th class="jp-centered">Status</th><th>Created At</th><th>Updated At</th><th class="jp-actions-col">Actions</th></tr></thead>
      <tbody>
      @forelse($rows as $row)
      <tr>
        <td>{{ $rows->firstItem()+$loop->index }}</td><td>{{ $row->id }}</td>
        <td><span class="jp-category-icon" style="color:{{ $row->icon_color ?: '#1677ee' }};background:{{ $row->card_color ?: '#ecf4ff' }}"><i class="fa fa-{{ $row->icon ?: 'folder' }}"></i></span><strong class="jp-catname">{{ $row->name }}</strong></td>
        <td class="jp-centered"><span class="jp-count">{{ (int)($subjectCounts[$row->id] ?? 0) }}</span></td>
        <td class="jp-centered"><span class="jp-count">{{ (int)($directCounts[$row->id] ?? 0) }}</span></td>
        <td class="jp-centered"><span class="label {{ $row->status === 'Active' ? 'label-success' : 'label-danger' }}">{{ $row->status }}</span></td>
        <td class="jp-date">{{ $row->created_at ? \Carbon\Carbon::parse($row->created_at)->format('d M Y h:i A') : '—' }}</td>
        <td class="jp-date">{{ $row->updated_at ? \Carbon\Carbon::parse($row->updated_at)->format('d M Y h:i A') : '—' }}</td>
        <td class="jp-actions-col"><div class="jp-action-buttons jp-pretty-actions">
          <a class="btn btn-info btn-sm" title="Category details" href="{{ route('admin.job-preparation.categories.overview',['kind'=>$kind,'category'=>$row->id]) }}"><i class="fa fa-file-text"></i> Details</a>
          <a class="btn btn-primary btn-sm" title="View direct category questions" href="{{ route('admin.job-preparation.questions',['kind'=>$kind,'level'=>'category','entity'=>$row->id]) }}"><i class="fa fa-list"></i> View MCQ</a>
          <a class="btn btn-success btn-sm jp-add-mcq" title="Add questions directly to this category" href="{{ route('admin.job-preparation.questions.picker',['kind'=>$kind,'level'=>'category','entity'=>$row->id]) }}"><i class="fa fa-plus-circle"></i> Add MCQ</a>
          <a class="btn btn-warning btn-sm" title="Edit category" href="{{ route('admin.job-preparation.categories.edit',['kind'=>$kind,'category'=>$row->id]) }}"><i class="fa fa-pencil"></i> Edit</a>
          <form method="POST" action="{{ route('admin.job-preparation.categories.destroy',['kind'=>$kind,'category'=>$row->id]) }}" onsubmit="return confirm('Delete this category? Deletion is blocked when subjects or direct questions exist.')" style="display:inline">@csrf @method('DELETE')<button class="btn btn-danger btn-sm" title="Delete empty category"><i class="fa fa-trash"></i> Delete</button></form>
        </div></td>
      </tr>
      @empty <tr><td colspan="9" class="text-center jp-catmuted">No categories found. Click Add Category to create one.</td></tr>
      @endforelse
      </tbody></table></div>
    <div class="jp-catfooter"><span class="jp-catmuted">Showing {{ $rows->firstItem() ?? 0 }}–{{ $rows->lastItem() ?? 0 }} of {{ $rows->total() }} categories</span>{{ $rows->links() }}</div>
  </div>
</div>
</section>
@endsection
