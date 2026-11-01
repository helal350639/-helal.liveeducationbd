@extends('dashboard')
@section('title', 'Add New Extra Student')
@section('content')
<style>
.lebd-extra-page{padding:15px;min-height:75vh}.lebd-extra-page .lebd-extra-title{font-size:24px;font-weight:400;margin:0 0 15px}.lebd-extra-page .lebd-extra-card{max-width:850px;background:#fff;border:1px solid #e2e6ea;border-top:3px solid #3c8dbc;border-radius:3px;box-shadow:0 1px 1px rgba(0,0,0,.08)}.lebd-extra-page .lebd-extra-head{padding:12px 13px;color:#9d0500;font-size:18px;border-bottom:1px solid #f0f0f0}.lebd-extra-page .lebd-extra-body{padding:15px 28px 24px}.lebd-extra-page label{display:block;font-weight:700;margin-bottom:7px}.lebd-extra-page .form-group{margin-bottom:18px}.lebd-extra-page .form-control{display:block;width:100%;height:36px;padding:6px 12px;border:1px solid #d2d6de;border-radius:5px;background:#fff;box-sizing:border-box}.lebd-extra-page .lebd-extra-foot{border-top:1px solid #f4f4f4;padding:12px 13px;display:flex;justify-content:space-between;align-items:center}.lebd-extra-page .lebd-extra-help{font-size:12px;color:#d53535;margin-top:8px}.lebd-extra-page .lebd-extra-btn{background:#3c8dbc;border:1px solid #367fa9;color:white;padding:9px 16px;border-radius:3px;text-decoration:none;cursor:pointer}.lebd-extra-page .lebd-extra-btn:hover{color:#fff;background:#367fa9}
</style>
<div class="lebd-extra-page">
  <h1 class="lebd-extra-title">Add New Extra Student</h1>
  <div class="lebd-extra-card">
    <div class="lebd-extra-head">Student Details</div>
    <form method="POST" action="{{ route('admin.extra-students.store') }}" enctype="multipart/form-data">
      @csrf
      <div class="lebd-extra-body">
        @if ($errors->any())
          <div class="alert alert-danger"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif
        <div class="form-group"><label for="extra-department">Department</label><select id="extra-department" class="form-control" name="department" required><option value="">Select Department</option>@foreach($departments as $key => $label)<option value="{{ $key }}" {{ old('department', request('department')) === $key ? 'selected' : '' }}>{{ $label }}</option>@endforeach</select></div>
        <div class="form-group"><label for="extra-name">Name</label><input id="extra-name" class="form-control" name="name" type="text" maxlength="150" value="{{ old('name') }}" required></div>
        <div class="form-group"><label for="extra-email">Email Address</label><input id="extra-email" class="form-control" name="email" type="email" maxlength="190" value="{{ old('email') }}"></div>
        <div class="form-group"><label for="extra-contact">Contact</label><input id="extra-contact" class="form-control" name="contact" type="text" maxlength="30" value="{{ old('contact') }}" required></div>
        <div class="form-group"><label for="extra-password">Password</label><div style="display:flex"><input id="extra-password" class="form-control" style="border-radius:5px 0 0 5px" name="password" type="password" minlength="8" required autocomplete="new-password"><button type="button" id="extra-password-toggle" aria-label="Show password" style="width:45px;border:1px solid #d2d6de;border-left:0;background:#fafafa;cursor:pointer" onclick="var input=document.getElementById('extra-password');var showing=input.type==='password';input.type=showing?'text':'password';this.setAttribute('aria-label',showing?'Hide password':'Show password');"><i class="fa fa-eye"></i></button></div></div>
        <div class="form-group"><label for="extra-photo">Photo</label><input id="extra-photo" class="form-control" type="file" name="photo" accept=".jpg,.jpeg,.png,image/jpeg,image/png"><div class="lebd-extra-help">Image types must be: .jpeg, .jpg, .png and Max size: 500KB</div></div>
      </div>
      <div class="lebd-extra-foot"><a href="{{ route('admin.extra-students.index') }}">View Student</a><button type="submit" class="lebd-extra-btn">Save</button></div>
    </form>
  </div>
</div>
@endsection
