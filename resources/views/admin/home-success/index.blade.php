@extends('dashboard')
@section('title','Our Success Manager')
@section('content')
<section class="content-header"><h1>Our Success Management</h1></section>
<section class="content"><div class="box box-primary"><div class="box-header with-border">
<a class="btn btn-primary" href="{{ route('admin.home-success.create') }}"><i class="fa fa-plus"></i> Add New Success Item</a>
<a class="btn btn-default" href="{{ url('/') }}" target="_blank">View Homepage</a>
</div><div class="box-body table-responsive">
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
<table class="table table-bordered table-striped"><thead><tr><th>Order</th><th>Type</th><th>Preview</th><th>Title</th><th>Status</th><th>Actions</th></tr></thead><tbody>
@forelse($items as $item)<tr><td>{{ $item->sort_order }}</td><td>{{ ucfirst($item->type) }}</td><td>@if($item->image_path)<img src="{{ asset($item->image_path) }}" style="width:65px;height:50px;object-fit:contain">@elseif($item->icon)<i class="fa {{ $item->icon }} fa-lg"></i>@endif</td><td>{{ $item->title }}</td><td>{{ $item->is_active?'Active':'Hidden' }}</td><td><a class="btn btn-primary btn-xs" href="{{ route('admin.home-success.edit',$item) }}">Edit</a> <form action="{{ route('admin.home-success.destroy',$item) }}" method="POST" style="display:inline" onsubmit="return confirm('Delete this item permanently?')">@csrf @method('DELETE')<button class="btn btn-danger btn-xs">Delete</button></form></td></tr>
@empty<tr><td colspan="6">No items yet. Import initial data from the SQL file.</td></tr>@endforelse
</tbody></table></div></div></section>
@endsection
