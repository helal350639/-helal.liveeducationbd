@extends('dashboard')
@section('title','E-Book Packages')
@section('content')
<section class="content-header"><h1>E-Book Premium Packages</h1></section><section class="content"><div class="box box-primary"><div class="box-header with-border"><h3 class="box-title">Package List</h3><a href="{{route('admin.ebook-packages.create')}}" class="btn btn-success pull-right">+ Add E-Book Package</a></div><div class="box-body">
@if(session('success'))<div class="alert alert-success">{{session('success')}}</div>@endif
<div class="table-responsive"><table class="table table-bordered table-striped"><thead><tr><th>ID</th><th>Package</th><th>Categories</th><th>Duration</th><th>Main Price</th><th>Discount</th><th>Offer Price</th><th>Status</th><th>Actions</th></tr></thead><tbody>
@forelse($packages as $p)<tr><td>{{$p->id}}</td><td><b>{{$p->name}}</b></td><td>{{$counts[$p->id]??0}}</td><td>{{$p->duration_value}} {{$p->duration_unit}}</td><td>৳{{number_format($p->main_price,2)}}</td><td>৳{{number_format($p->discount_amount,2)}}</td><td><b>৳{{number_format($p->price,2)}}</b></td><td>{{$p->status}}</td><td><a class="btn btn-warning btn-sm" href="{{route('admin.ebook-packages.edit',$p->id)}}">Edit</a> @if($p->status==='Active')<form method="POST" action="{{route('admin.ebook-packages.delete',$p->id)}}" style="display:inline" onsubmit="return confirm('Disable this package?')">@csrf @method('DELETE')<button class="btn btn-danger btn-sm">Disable</button></form>@endif</td></tr>
@empty<tr><td colspan="9">No E-Book package created yet.</td></tr>@endforelse</tbody></table></div></div></div></section>
@endsection
