@extends('dashboard')
@section('title','MCQ Job Preparation - Category Details')
@section('content')
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
.jp-overview{max-width:none!important;width:100%!important}.jp-overview .jp-actions .btn-success{min-height:40px;min-width:115px;font-size:13px;font-weight:700}.jp-overview .jp-actions{justify-content:flex-end;gap:8px}
</style>
<style>.jp-overview{max-width:1480px;margin:auto}.jp-overview .jp-toolbar{display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap}.jp-overview .jp-intro{padding:14px 16px;background:#eef7ff;border:1px solid #d9e9f9;border-radius:6px;margin-bottom:16px;color:#36516c}.jp-overview .jp-panel{border:1px solid #dbe4ed;background:white;border-radius:6px;margin:16px 0;overflow:hidden}.jp-overview .jp-panel-title{padding:14px 18px;border-bottom:1px solid #e5edf2;font-size:16px;font-weight:bold;color:#1f4c70}.jp-overview .jp-panel-body{padding:18px}.jp-overview .jp-actions{display:flex;gap:5px;flex-wrap:wrap}.jp-overview table th{background:#f4f9fd}.jp-overview table{min-width:700px}.jp-overview .jp-kpi{border-radius:6px;padding:18px;background:#fff;border:1px solid #dde6f0;margin-bottom:15px}.jp-overview .jp-kpi strong{display:block;font-size:26px;color:#174e8b}.jp-overview .jp-kpi small{color:#61768d}</style>
<section class="content-header"><div class="jp-toolbar"><h1>MCQ Job Preparation <small>{{ $kind==='department'?'Department':'Non-Department' }} / {{ $cat->name }}</small></h1><a class="btn btn-default" href="{{route('admin.job-preparation.categories',['kind'=>$kind])}}"><i class="fa fa-arrow-left"></i> All Categories</a></div></section>
<section class="content jp-overview jp-fullwide">
@if(session('success'))<div class="alert alert-success">{{session('success')}}</div>@endif
@if(session('error'))<div class="alert alert-danger">{{session('error')}}</div>@endif
@if($errors->any())<div class="alert alert-danger">{{implode(' | ',$errors->all())}}</div>@endif
<div class="row"><div class="col-sm-3"><div class="jp-kpi"><strong>{{$subjectCount}}</strong><small>Total Subjects</small></div></div><div class="col-sm-3"><div class="jp-kpi"><strong>{{$chapterCount}}</strong><small>Total Chapters</small></div></div><div class="col-sm-3"><div class="jp-kpi"><strong>{{$directCount}}</strong><small>Direct Category MCQs</small></div></div><div class="col-sm-3"><div class="jp-kpi"><strong>{{$totalLinks}}</strong><small>All Linked Questions</small></div></div></div>
<div class="jp-intro"><b>{{$cat->name}}</b> · {{$cat->status}} — Add subjects and questions over many days. Subjects and chapters are optional. Existing question records are always read live from the Main Database.</div>
<div class="jp-panel"><div class="jp-panel-title jp-toolbar"><span><i class="fa fa-question-circle"></i> Direct Category Questions ({{$directCount}})</span><div class="jp-actions jp-pretty-actions"><a class="btn btn-info btn-sm" href="{{route('admin.job-preparation.questions',['kind'=>$kind,'level'=>'category','entity'=>$cat->id])}}">View Full Questions</a><a class="btn btn-success btn-lg" href="{{route('admin.job-preparation.questions.picker',['kind'=>$kind,'level'=>'category','entity'=>$cat->id])}}"><i class="fa fa-plus"></i> Add Direct MCQ</a></div></div></div>
<div class="jp-panel"><div class="jp-panel-title jp-toolbar"><span><i class="fa fa-book"></i> Subjects under {{$cat->name}} ({{$subjectCount}})</span><a href="{{route('admin.job-preparation.subjects',['kind'=>$kind,'category'=>$cat->id])}}" class="btn btn-primary btn-sm"><i class="fa fa-plus"></i> Manage / Add Subjects</a></div><div class="jp-panel-body">
<form method="GET" class="jp-filters" action="{{ url()->current() }}">
 <div class="jp-search"><label>Search by name</label><input class="form-control" name="search" value="{{ request('search','') }}" placeholder="Search name..."></div>
 <div class="jp-select"><label>Status</label><select class="form-control" name="status"><option value="">All Status</option><option value="Active" {{ request('status')==='Active'?'selected':'' }}>Active</option><option value="Inactive" {{ request('status')==='Inactive'?'selected':'' }}>Inactive</option></select></div>
 <div class="jp-select"><label>Sort By</label><select name="sort" class="form-control"><option value="order" {{ request('sort','order')==='order'?'selected':'' }}>Display Order</option><option value="latest" {{ request('sort')==='latest'?'selected':'' }}>Newest First</option><option value="name" {{ request('sort')==='name'?'selected':'' }}>Name A–Z</option></select></div>
 <div class="jp-submit"><button type="submit" class="btn btn-info"><i class="fa fa-filter"></i> Filter</button><a href="{{ url()->current() }}" class="btn btn-default">Reset</a></div>
</form>
<div class="table-responsive"><table class="table table-bordered table-hover jp-wide-table"><thead><tr><th>SL</th><th>Subject Name</th><th>Status</th><th>Chapters</th><th>Direct Questions</th><th>Management</th></tr></thead><tbody>
@forelse($subjects as $subject)
<tr><td>{{$subjects->firstItem()+$loop->index}}</td><td><strong>{{$subject->name}}</strong></td><td><span class="label {{$subject->status==='Active'?'label-success':'label-default'}}">{{$subject->status}}</span></td><td>{{(int)($chapterCounts[$subject->id]??0)}}</td><td>{{(int)($questionCounts[$subject->id]??0)}}</td><td><div class="jp-actions jp-pretty-actions"><a class="btn btn-primary btn-xs" href="{{route('admin.job-preparation.chapters',['kind'=>$kind,'category'=>$cat->id,'subject'=>$subject->id])}}"><i class="fa fa-list"></i> View Chapters</a><a class="btn btn-info btn-xs" href="{{route('admin.job-preparation.questions',['kind'=>$kind,'level'=>'subject','entity'=>$subject->id])}}">View MCQ</a><a class="btn btn-success btn-sm" href="{{route('admin.job-preparation.questions.picker',['kind'=>$kind,'level'=>'subject','entity'=>$subject->id])}}">+ MCQ</a><a class="btn btn-default btn-xs" href="{{route('admin.job-preparation.subjects',['kind'=>$kind,'category'=>$cat->id])}}">Edit</a></div></td></tr>
@empty<tr><td colspan="6" class="text-center text-muted">No subjects. Use Manage / Add Subjects above, or attach MCQs directly to this category.</td></tr>@endforelse
</tbody></table></div>
<div class="pull-left text-muted">Showing {{$subjects->firstItem()??0}}–{{$subjects->lastItem()??0}} of {{$subjects->total()}}</div><div class="pull-right">{{$subjects->links()}}</div><div class="clearfix"></div>
</div></div>
<div class="jp-intro"><i class="fa fa-info-circle"></i> Open any subject to manage its chapters; open a chapter to view its linked MCQs. Every list has its own page. No existing question is duplicated or overwritten.</div>
</section>
@endsection
