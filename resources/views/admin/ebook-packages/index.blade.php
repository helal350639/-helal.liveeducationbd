@extends('dashboard')
@section('title','E-Book Packages')
@section('content')
<section class="content-header"><h1>E-Book Premium Packages</h1></section><section class="content"><div class="box box-primary"><div class="box-header with-border"><h3 class="box-title">Package List</h3><a href="{{route('admin.ebook-packages.create')}}" class="btn btn-success pull-right">+ Add E-Book Package</a></div><div class="box-body">
@if(session('success'))<div class="alert alert-success">{{session('success')}}</div>@endif
@if($errors->any())<div class="alert alert-danger">{{$errors->first()}}</div>@endif
<div class="table-responsive"><table class="table table-bordered table-striped"><thead><tr><th>ID</th><th>Package</th><th>Categories</th><th>Duration</th><th>Main Price</th><th>Discount</th><th>Offer Price</th><th>Status</th><th>Students (Unique)</th><th>Actions</th></tr></thead><tbody>
@forelse($packages as $p)
@php $c=$studentCounts[$p->id]??['total'=>0,'active'=>0,'manual'=>0,'wallet'=>0,'ssl'=>0]; $details=fn($method)=>route('admin.ebook-packages.students.report',['package'=>$p->id,'method'=>$method]); @endphp
<tr><td>{{$p->id}}</td><td><b>{{$p->name}}</b></td><td>{{$counts[$p->id]??0}}</td><td>{{$p->duration_value}} {{$p->duration_unit}}</td><td>৳{{number_format($p->main_price,2)}}</td><td>৳{{number_format($p->discount_amount,2)}}</td><td><b>৳{{number_format($p->price,2)}}</b></td><td>{{$p->status}}</td>
<td>
<a href="{{$details('all')}}" class="btn btn-info btn-xs" title="View Package Students" aria-label="View Package Students" style="margin:0 5px 5px 0;vertical-align:middle"><i class="fa fa-eye" aria-hidden="true"></i></a>
<a href="{{$details('all')}}"><b>Total: {{$c['total']}}</b></a> · <a href="{{$details('active')}}">Active: {{$c['active']}}</a><br><a href="{{$details('manual')}}">Manual: {{$c['manual']}}</a> · <a href="{{$details('wallet')}}">Wallet: {{$c['wallet']}}</a> · <a href="{{$details('ssl')}}">SSL: {{$c['ssl']}}</a></td>
<td><a class="btn btn-warning btn-sm" href="{{route('admin.ebook-packages.edit',$p->id)}}">Edit</a> <a class="btn btn-primary btn-sm" href="{{route('admin.ebook-packages.students.add',$p->id)}}">Add Student</a> <a class="btn btn-info btn-sm" href="{{$details('all')}}">View</a> @if($p->status==='Active')<form method="POST" action="{{route('admin.ebook-packages.delete',$p->id)}}" style="display:inline" onsubmit="return confirm('Disable this package?')">@csrf @method('DELETE')<button class="btn btn-danger btn-sm">Disable</button></form>@endif</td></tr>
@empty<tr><td colspan="10">No E-Book package created yet.</td></tr>@endforelse</tbody></table></div></div></div></section>
@endsection
