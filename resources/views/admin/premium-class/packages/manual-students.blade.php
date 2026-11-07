@extends('dashboard')
@section('title','Add Students — Premium Package')
@section('content')
<style>
.pmadd{max-width:1100px;margin:20px auto;padding:14px;color:#193a5b}.pmadd-panel{background:#fff;border:1px solid #d7e5f5;border-radius:12px;padding:18px;margin-top:15px}.pmadd table{width:100%;border-collapse:collapse}.pmadd th,.pmadd td{text-align:left;padding:11px;border-bottom:1px solid #e1e8f0}.pmadd .btn{display:inline-block;background:#096dd9;color:white;padding:10px 15px;border-radius:8px;border:0;text-decoration:none;cursor:pointer}.pmadd input[type=text]{width:100%;max-width:350px;padding:10px;border:1px solid #b9cde1;border-radius:7px}.pmadd .muted{color:#667d96}@media(max-width:550px){.pmadd{padding:7px}.pmadd-panel{padding:10px}.pmadd th,.pmadd td{padding:8px 4px}}
</style>
<main class="pmadd">
<a href="{{route('admin.premium-class-packages.index')}}">← Premium Package List</a>
<h2>Add Student — {{ $package->name }}</h2>
<p class="muted">Manual grants create no payment order, wallet debit, or referral bonus. Access expires after {{ $package->validity_days }} days.</p>
@if(session('success'))<div class="alert alert-success">{{session('success')}}</div>@endif
@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
<div class="pmadd-panel">
<form method="GET"><label>Search Student by Name or ID <input name="q" type="text" value="{{ $search }}" placeholder="Name or student ID"></label> <button class="btn">Search</button></form>
</div>
<div class="pmadd-panel"><form method="POST" action="{{route('admin.premium-class-packages.manual.store',$package->id)}}">@csrf
<table><thead><tr><th>Select</th><th>Student ID</th><th>Student Name</th><th>Package Access</th></tr></thead><tbody>
@forelse($students as $student)
@php $grant=$grants->get($student->id); $manualActive=$grant && \Illuminate\Support\Carbon::parse($grant->starts_at)->lessThanOrEqualTo(now()) && \Illuminate\Support\Carbon::parse($grant->ends_at)->greaterThan(now()); $paid=in_array($student->id,$paidIds); @endphp
<tr><td><input type="checkbox" name="student_ids[]" value="{{$student->id}}" @disabled($manualActive||$paid)></td><td>{{$student->id}}</td><td>{{$student->name}}</td><td>@if($paid)Already purchased (active)@elseif($manualActive)Manual access active until {{$grant->ends_at}}@else{{ $grant ? 'Expired — can grant again':'Not assigned' }}@endif</td></tr>
@empty<tr><td colspan="4">No students found.</td></tr>@endforelse
</tbody></table><div style="margin:15px 0">{{$students->links()}}</div>
<button class="btn" type="submit">Done / Save Selected Students</button>
</form></div></main>
@endsection
