@extends('dashboard')
@section('title', 'Edit Extra Student Status')
@section('content')
<style>
.lebd-esedit{padding:20px;min-height:65vh;font-family:'Noto Sans Bengali',Arial,sans-serif;color:#17345c}
.lebd-esedit .card{max-width:740px;background:#fff;border:1px solid #d8e5f2;border-top:3px solid #3c8dbc;border-radius:7px;padding:22px}
.lebd-esedit h1{font-size:24px;margin:0 0 17px}.lebd-esedit .field{margin:12px 0}.lebd-esedit label{display:block;font-weight:700;margin-bottom:6px}
.lebd-esedit select{width:100%;max-width:330px;padding:9px;border:1px solid #cbd6e2;border-radius:5px}
.lebd-esedit .btn{display:inline-block;background:#167ab6;color:#fff;border:0;padding:10px 16px;border-radius:5px;text-decoration:none;cursor:pointer}
.lebd-esedit .back{background:#60758b}.lebd-esedit .muted{color:#66788c}
</style>
<div class="lebd-esedit">
<h1>Edit Extra Student Status</h1>
<div class="card">
@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
<p><b>Student ID:</b> {{ $extraStudent->id }}</p>
<p><b>Name:</b> {{ $extraStudent->name }}</p>
<p><b>Department:</b> {{ $departments[$extraStudent->department] ?? ucfirst($extraStudent->department) }}</p>
<p><b>Contact:</b> {{ $extraStudent->contact }}</p>
<p><b>Email:</b> {{ $extraStudent->email ?: '—' }}</p>
<form method="POST" action="{{ route('admin.extra-students.update', ['student' => $extraStudent->id]) }}">
@csrf
@method('PUT')
<div class="field"><label for="es-status">Account Status</label>
<select name="status" id="es-status" required>
<option value="Active" @selected(old('status', $extraStudent->status) === 'Active')>Active</option>
<option value="Inactive" @selected(old('status', $extraStudent->status) === 'Inactive')>Inactive</option>
</select></div>
<p class="muted">শুধু Account Status পরিবর্তন হবে। Student তথ্য, Password এবং Exam Selection পরিবর্তন হবে না।</p>
<button class="btn" type="submit">Save Changes</button>
<a class="btn back" href="{{ route('admin.extra-students.index', ['department' => $extraStudent->department]) }}">Back to Students</a>
</form>
</div></div>
@endsection
