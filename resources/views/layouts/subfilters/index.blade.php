@extends('dashboard')
@section('title','Manage Sub-filters')
@section('content')
<section class="content-header"><h1>Manage Sub-filters — {{ $managedFilter->name }}</h1></section>
<section class="content"><div class="box box-primary">
<div class="box-header with-border"><h3 class="box-title">Manage your Sub-filters</h3>
<div class="box-tools"><a href="{{ route('filter.index') }}" class="btn btn-default btn-sm">Back to Main Filters</a> <a href="{{ route('sub-filter.create', ['filter_id'=>$managedFilter->id]) }}" class="btn btn-primary btn-sm"><i class="fa fa-plus"></i> Create Sub-filter</a></div>
</div><div class="box-body">
@if(!$visibilityConfigured)<div class="alert alert-warning">Run the Fix 12 SQL before creating new Sub-filters.</div>@endif
<form method="GET" action="{{ route('sub-filter.index') }}" class="form-inline" style="margin-bottom:16px">
<input type="hidden" name="view" value="manage"><input type="hidden" name="filter_id" value="{{ $managedFilter->id }}">
<input name="search" class="form-control" style="min-width:240px" value="{{ $search }}" placeholder="Search all items by name or ID">
<select name="per_page" class="form-control">@foreach([25,50,100] as $n)<option value="{{ $n }}" {{ $perPage===$n?'selected':'' }}>{{ $n }} per page</option>@endforeach</select>
<button type="submit" class="btn btn-info">Search</button>
<a class="btn btn-default" href="{{ route('sub-filter.index', ['view'=>'manage','filter_id'=>$managedFilter->id]) }}">Clear</a></form>
<div class="table-responsive"><table class="table table-bordered table-hover"><thead><tr><th>ID</th><th>Sub-filter</th><th>Details</th><th>Status</th><th>Action</th></tr></thead><tbody>
@forelse($subfilters as $item)<tr><td>{{ $item->id }}</td><td>{{ $item->name }}</td><td>{{ $item->details }}</td><td>{{ $item->status }}</td>
<td><a href="{{ route('sub-filter.edit',$item->id) }}" class="btn btn-warning btn-sm"><i class="fa fa-edit"></i> Edit</a>
<form method="POST" action="{{ route('sub-filter.destroy',$item->id) }}" style="display:inline" onsubmit="return confirm('Delete this Sub-filter?')">@csrf @method('DELETE')<button class="btn btn-danger btn-sm" type="submit"><i class="fa fa-trash"></i> Delete</button></form></td></tr>
@empty<tr><td colspan="5" class="text-muted text-center">No matching Sub-filters yet. Create one from the main filter page.</td></tr>@endforelse
</tbody></table></div>{{ $subfilters->links() }}
</div></div></section>
@endsection
