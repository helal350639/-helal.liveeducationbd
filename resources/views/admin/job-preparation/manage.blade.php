@extends('dashboard')
@section('title','MCQ Job Preparation - Management')
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
.jp-pretty-actions .jp-strong-add{min-height:40px!important;min-width:110px!important;padding:9px 12px!important;font-weight:700!important;font-size:13px!important}
.jp-pretty-actions .btn{min-height:35px!important}
.jp-wide-table .jp-action-cell{min-width:410px!important}
.jp-layout.jp-fullwide{max-width:none!important;width:100%!important}
</style>
@php
$plural = $level==='category' ? 'categories' : ($level==='subject' ? 'subjects' : 'chapters');
$singular = ucfirst($level);
$catId = $level==='subject' ? $parent->id : ($level==='chapter' ? $crumbs[0]->id : null);
$subId = $level==='chapter' ? $parent->id : null;
$path = ['kind'=>$kind];
if($level!=='category') $path['category']=$catId;
if($level==='chapter') $path['subject']=$subId;
$kindTitle = $kind==='department' ? 'Department' : 'Non-Department';
@endphp
<style>
.jp-layout{max-width:1480px;margin:auto}.jp-top{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px}.jp-note{background:#eff7ff;border:1px solid #d6e7ff;color:#36526e;padding:12px 15px;border-radius:5px;margin:12px 0}.jp-card{background:#fff;border:1px solid #dde6ef;border-radius:6px;margin-bottom:18px;overflow:hidden}.jp-card-head{padding:15px 18px;border-bottom:1px solid #e7ecf1;font-weight:bold;color:#214362}.jp-card-body{padding:18px}.jp-grid{display:grid;grid-template-columns:minmax(190px,3fr) 130px 150px auto;gap:10px;align-items:end}.jp-table{width:100%}.jp-table th{background:#f5f9fc!important;color:#36516b}.jp-actions{display:flex;gap:5px;flex-wrap:wrap}.jp-badge{display:inline-block;background:#edf5fd;color:#14629a;padding:5px 8px;border-radius:4px}.jp-edit-row{background:#f8fbfd;padding:10px;border-radius:5px;margin:8px 0}.jp-muted{color:#738296}@media(max-width:800px){.jp-grid{grid-template-columns:1fr 1fr}.jp-grid .jp-wide{grid-column:span 2}.jp-table{min-width:800px}}@media(max-width:480px){.jp-grid{grid-template-columns:1fr}.jp-grid .jp-wide{grid-column:auto}}
</style>
<section class="content-header"><div class="jp-top"><h1>MCQ Job Preparation <small>{{ $kindTitle }} / {{ $singular }} Management</small></h1><a class="btn btn-default" href="{{ route('admin.job-preparation.categories',['kind'=>$kind]) }}"><i class="fa fa-arrow-left"></i> Categories</a></div></section>
<section class="content jp-layout jp-fullwide">
@if(session('success'))<div class="alert alert-success">{{session('success')}}</div>@endif
@if(session('error'))<div class="alert alert-danger">{{session('error')}}</div>@endif
@if($errors->any())<div class="alert alert-danger">{{implode(' | ',$errors->all())}}</div>@endif
@if($level!=='category')
<div class="jp-note"><b>{{ $level==='subject' ? $parent->name : $crumbs[0]->name.' / '.$parent->name }}</b> — Each {{ strtolower($singular) }} has its own questions. Create additional items anytime; previously linked content stays intact.</div>
@endif
<div class="jp-card"><div class="jp-card-head"><i class="fa fa-filter"></i> {{ $level==='subject' ? 'Category → Subject Filter' : 'Category → Subject → Chapter Filter' }}</div><div class="jp-card-body"><div class="row">
<div class="col-sm-4 form-group"><label>Category</label><select class="form-control" aria-label="Select category" onchange="if(this.value)window.location.assign(this.value)">
@foreach($navCategories as $navCat)<option value="{{ route('admin.job-preparation.subjects',['kind'=>$kind,'category'=>$navCat->id]) }}" {{ (int)$catId===(int)$navCat->id?'selected':'' }}>{{ $navCat->name }}</option>@endforeach
</select></div>
@if($level==='chapter')<div class="col-sm-4 form-group"><label>Subject</label><select class="form-control" aria-label="Select subject" onchange="if(this.value)window.location.assign(this.value)">@foreach($navSubjects as $navSub)<option value="{{ route('admin.job-preparation.chapters',['kind'=>$kind,'category'=>$catId,'subject'=>$navSub->id]) }}" {{ (int)$subId===(int)$navSub->id?'selected':'' }}>{{ $navSub->name }}</option>@endforeach</select></div>
@endif
<div class="col-sm-4 form-group"><label>{{ $level==='chapter' ? 'Chapter' : 'Subject' }}</label><form method="GET" action="{{ url()->current() }}"><div class="input-group"><input type="text" class="form-control" name="search" value="{{request('search','')}}" placeholder="Search {{strtolower($singular)}}"><span class="input-group-btn"><button class="btn btn-info" type="submit">Filter</button></span></div></form></div>
</div><small class="text-muted">Select a parent to open its saved items. Existing linked questions are preserved.</small></div></div>
<div class="jp-card"><div class="jp-card-head"><i class="fa fa-plus-circle"></i> Add New {{ $singular }}</div><div class="jp-card-body">
<form method="POST" action="{{route('admin.job-preparation.'.$plural.'.store',$path)}}">@csrf
<div class="jp-grid"><div class="jp-wide"><label>{{ $singular }} Name</label><input class="form-control" name="name" required maxlength="180" placeholder="Enter {{ strtolower($singular) }} name" value="{{old('name')}}"></div><div><label>Sort Order</label><input type="number" min="0" max="999999" name="sort_order" class="form-control" value="{{old('sort_order',0)}}"></div><div><label>Status</label><select name="status" class="form-control"><option>Active</option><option>Inactive</option></select></div><div><button type="submit" class="btn btn-primary btn-block"><i class="fa fa-plus"></i> Add {{ $singular }}</button></div></div>
</form></div></div>
<div class="jp-card"><div class="jp-card-head"><i class="fa fa-list"></i> {{ $singular }} List <span class="jp-badge">{{ $rows->total() }} total</span></div><div class="jp-card-body">
<form method="GET" class="jp-filters" action="{{ url()->current() }}">
 <div class="jp-search"><label>Search by name</label><input class="form-control" name="search" value="{{ request('search','') }}" placeholder="Search name..."></div>
 <div class="jp-select"><label>Status</label><select class="form-control" name="status"><option value="">All Status</option><option value="Active" {{ request('status')==='Active'?'selected':'' }}>Active</option><option value="Inactive" {{ request('status')==='Inactive'?'selected':'' }}>Inactive</option></select></div>
 <div class="jp-select"><label>Sort By</label><select name="sort" class="form-control"><option value="order" {{ request('sort','order')==='order'?'selected':'' }}>Display Order</option><option value="latest" {{ request('sort')==='latest'?'selected':'' }}>Newest First</option><option value="name" {{ request('sort')==='name'?'selected':'' }}>Name A–Z</option></select></div>
 <div class="jp-submit"><button type="submit" class="btn btn-info"><i class="fa fa-filter"></i> Filter</button><a href="{{ url()->current() }}" class="btn btn-default">Reset</a></div>
</form>
<div class="table-responsive"><table class="table table-bordered table-hover jp-table jp-wide-table"><thead><tr><th style="width:70px">SL</th><th>{{ $singular }}</th><th>Status</th><th>Order</th><th>{{ $level==='category' ? 'Subjects' : ($level==='subject'?'Chapters':'') }}</th><th>Direct MCQs</th><th>Actions</th></tr></thead><tbody>
@forelse($rows as $row)
@php $itemPath=$path+[$level=>$row->id]; $count=(int)($directCounts[$row->id]??0); @endphp
<tr><td>{{$rows->firstItem()+$loop->index}}</td><td><strong>{{ $row->name }}</strong><br><small class="jp-muted">ID #{{ $row->id }}</small></td><td><span class="label {{ $row->status==='Active'?'label-success':'label-default' }}">{{ $row->status }}</span></td><td>{{ $row->sort_order }}</td><td>@if($level==='category')<span class="jp-badge">{{(int)($subjectCounts[$row->id]??0)}}</span>@elseif($level==='subject')<span class="jp-badge">{{(int)($chapterCounts[$row->id]??0)}}</span>@else — @endif</td><td><span class="jp-badge">{{ $count }}</span></td><td><div class="jp-actions jp-pretty-actions">
@if($level==='category')<a class="btn btn-primary btn-xs" href="{{route('admin.job-preparation.categories.overview',['kind'=>$kind,'category'=>$row->id])}}"><i class="fa fa-eye"></i> Details</a>
@elseif($level==='subject')<a class="btn btn-primary btn-xs" href="{{route('admin.job-preparation.chapters',['kind'=>$kind,'category'=>$catId,'subject'=>$row->id])}}"><i class="fa fa-book"></i> Chapters</a>
@endif
<a class="btn btn-info btn-xs" href="{{route('admin.job-preparation.questions',['kind'=>$kind,'level'=>$level,'entity'=>$row->id])}}"><i class="fa fa-list"></i> Questions</a>
<a class="btn btn-success btn-sm jp-strong-add" href="{{route('admin.job-preparation.questions.picker',['kind'=>$kind,'level'=>$level,'entity'=>$row->id])}}"><i class="fa fa-plus"></i> Add MCQ</a>
<button type="button" class="btn btn-warning btn-xs" data-toggle="collapse" data-target="#jp-edit-{{$level}}-{{$row->id}}"><i class="fa fa-pencil"></i> Edit</button>
<form style="display:inline" method="POST" action="{{route('admin.job-preparation.'.$plural.'.destroy',$itemPath)}}" onsubmit="return confirm('Delete only if this item is empty? Items with children or questions cannot be deleted.')">@csrf @method('DELETE')<button class="btn btn-danger btn-xs"><i class="fa fa-trash"></i> Delete</button></form>
</div></td></tr>
<tr class="collapse" id="jp-edit-{{$level}}-{{$row->id}}"><td colspan="7"><form class="jp-edit-row form-inline" method="POST" action="{{route('admin.job-preparation.'.$plural.'.update',$itemPath)}}">@csrf @method('PUT')<div class="form-group"><label>Name </label> <input name="name" class="form-control" value="{{$row->name}}" required maxlength="180"></div> <div class="form-group"><label>Order </label> <input type="number" name="sort_order" value="{{$row->sort_order}}" class="form-control" min="0" max="999999" style="width:95px"></div> <div class="form-group"><label>Status </label> <select class="form-control" name="status"><option value="Active" {{$row->status==='Active'?'selected':''}}>Active</option><option value="Inactive" {{$row->status==='Inactive'?'selected':''}}>Inactive</option></select></div> <button class="btn btn-primary"><i class="fa fa-save"></i> Save Changes</button></form></td></tr>
@empty<tr><td colspan="7" class="text-center text-muted">No {{strtolower($singular)}} found. Add one using the form above.</td></tr>@endforelse
</tbody></table></div>
<div class="pull-left text-muted">Showing {{$rows->firstItem()??0}}–{{$rows->lastItem()??0}} of {{$rows->total()}}</div><div class="pull-right">{{$rows->links()}}</div><div class="clearfix"></div>
</div></div>
<div class="jp-note"><i class="fa fa-shield"></i> Existing questions are linked to the Main Database. Deleting a non-empty category, subject or chapter is blocked. Set Status to Inactive if you want to hide it without losing content.</div>
</section>
@endsection
