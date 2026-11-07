@extends('dashboard')
@section('title','E-Book Package Students')
@section('content')
<section class="content-header"><h1>E-Book Package Students — {{$p->name}}</h1></section>
<section class="content"><div class="box box-primary"><div class="box-header with-border"><a class="btn btn-default pull-right" href="{{route('admin.ebook-packages.index')}}">← Package List</a><h3 class="box-title">Student Details</h3></div><div class="box-body">
@if(session('success'))<div class="alert alert-success" role="status">{{session('success')}}</div>@endif
<div style="margin-bottom:14px">
@foreach(['all'=>'Total','active'=>'Active','manual'=>'Manual','wallet'=>'Referral Wallet','ssl'=>'SSLCommerz'] as $key=>$label)
<a class="btn btn-sm {{ $method===$key?'btn-primary':'btn-default' }}" href="{{route('admin.ebook-packages.students.report',['package'=>$p->id,'method'=>$key])}}">{{$label}}: {{$count[$key==='all'?'total':$key]??0}}</a>
@endforeach
</div>
<form method="GET" class="form-inline" style="margin:12px 0"><input type="hidden" name="method" value="{{$method}}"><input type="search" class="form-control" name="q" value="{{$q}}" maxlength="120" placeholder="Name, ID or Email"><button class="btn btn-primary">Search</button></form>
<div class="table-responsive"><table class="table table-striped table-bordered"><thead><tr><th>Student ID</th><th>Name</th><th>Email</th><th>Contact</th><th>Studying</th><th>Account</th><th>Access Method</th><th>Premium Access</th><th>Expiry</th><th>View / Manual Remove</th></tr></thead><tbody>
@forelse($rows as $id=>$r)
@php $s=$r['student']; @endphp
<tr><td>{{$id}}</td><td>{{$s->name??'Student not found'}}</td><td>{{$s->email??'—'}}</td><td>{{$s->contact??'—'}}</td><td>{{$s->studying??'—'}}</td><td>{{$s->status??'—'}}</td><td>{{implode(', ',array_keys($r['methods']))}}</td><td>{{$r['active']?'Active':'Expired'}}</td><td>{{$r['expires_at']??'—'}}</td><td>
@if($s)<a class="btn btn-info btn-sm" href="{{route('student.show',$id)}}">View Student</a>@endif
@if(isset($r['methods']['manual']))
<form method="POST" action="{{route('admin.ebook-packages.students.manual.remove',['package'=>$p->id,'student'=>$id])}}" style="display:inline-block;margin:3px 0" onsubmit="return confirm('এই Student-এর শুধু E-Book Manual Access প্রত্যাহার করবেন? Payment/Wallet Order পরিবর্তন হবে না।');">
@csrf
<input type="hidden" name="confirm_remove" value="1">
<button type="submit" class="btn btn-danger btn-sm">Remove Manual</button>
</form>
@endif
</td></tr>
@empty<tr><td colspan="10">এই Filter-এ কোনো Student পাওয়া যায়নি।</td></tr>@endforelse
</tbody></table></div></div></div></section>
@endsection
