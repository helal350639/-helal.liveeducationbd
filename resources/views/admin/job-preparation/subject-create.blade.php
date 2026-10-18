@extends('dashboard')
@section('title', 'Add Subject - MCQ Job Preparation')
@section('content')
<style>.jp-only{max-width:920px;margin:auto}.jp-only .box-header{padding:15px}.jp-only .box-title{color:#9d0000}.jp-only .box-body{padding:22px}.jp-only .form-group{margin-bottom:18px}</style>
<section class="content-header"><h1>Add Subject <small>MCQ Job Preparation / {{ $kind === 'department' ? 'Department' : 'Non-Department' }}</small></h1></section>
<section class="content"><div class="jp-only">
@if($errors->any())<div class="alert alert-danger"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<div class="box box-primary"><div class="box-header with-border"><h3 class="box-title">Subject Details</h3><a class="btn btn-default btn-sm pull-right" href="{{ route('admin.job-preparation.categories',['kind'=>$kind]) }}">Back to Categories</a></div>
<form method="POST" action="{{ route('admin.job-preparation.subjects.store-global',['kind'=>$kind]) }}">@csrf
<div class="box-body">
<div class="form-group"><label>Category (Department / Non-Department category) *</label><select name="category_id" class="form-control" required><option value="">Select Category</option>@foreach($categories as $cat)<option value="{{ $cat->id }}" {{ (string)old('category_id')===(string)$cat->id?'selected':'' }}>{{ $cat->name }}</option>@endforeach</select>@if($categories->isEmpty())<p class="text-danger">Create a Category first before adding a Subject.</p>@endif</div>
<div class="form-group"><label>Subject Name *</label><input name="name" class="form-control" maxlength="180" value="{{old('name')}}" required placeholder="Enter Subject name"></div>
<div class="row"><div class="col-sm-6 form-group"><label>Sort Order</label><input name="sort_order" type="number" min="0" max="999999" class="form-control" value="{{old('sort_order',0)}}"></div><div class="col-sm-6 form-group"><label>Status</label><select name="status" class="form-control"><option value="Active" {{old('status','Active')==='Active'?'selected':''}}>Active</option><option value="Inactive" {{old('status')==='Inactive'?'selected':''}}>Inactive</option></select></div></div>
<p class="text-muted">You can add more Subjects later. Existing records and question links remain unchanged.</p>
</div><div class="box-footer text-right"><a href="{{route('admin.job-preparation.categories',['kind'=>$kind])}}" class="btn btn-default">Cancel</a> <button class="btn btn-primary" type="submit"><i class="fa fa-save"></i> Save Subject</button></div></form></div></div></section>

@endsection
