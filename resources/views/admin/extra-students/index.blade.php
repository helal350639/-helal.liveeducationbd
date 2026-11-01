@extends('dashboard')
@section('title', 'Extra Students Accounts')
@section('content')
<style>
.lebd-extra-list{padding:15px;min-height:75vh}.lebd-extra-list h1{font-size:24px;font-weight:400;margin:0 0 13px}.lebd-extra-list .lebd-list-card{background:#fff;border:1px solid #e1e5eb;border-top:3px solid #3c8dbc;border-radius:3px}.lebd-extra-list .lebd-list-head{padding:12px 13px;display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;border-bottom:1px solid #eee;color:#a00;font-size:16px}.lebd-extra-list .lebd-list-body{padding:13px}.lebd-extra-list .btn-extra{border:0;border-radius:3px;background:#00a65a;color:white;padding:8px 12px;text-decoration:none}.lebd-extra-list .table-responsive{overflow-x:auto}.lebd-extra-list table{width:100%;border-collapse:collapse}.lebd-extra-list th,.lebd-extra-list td{border:1px solid #eef0f2;padding:10px;text-align:left;font-size:13px}.lebd-extra-list th{background:#f8fafc}.lebd-extra-list .lebd-search{display:flex;gap:5px;flex-wrap:wrap;margin-bottom:12px}.lebd-extra-list input{border:1px solid #ccd3dd;border-radius:3px;padding:7px 9px}.lebd-extra-list button{background:#3c8dbc;color:#fff;border:0;border-radius:3px;padding:7px 12px;cursor:pointer}.lebd-extra-list .department-buttons{display:grid;grid-template-columns:repeat(auto-fit,minmax(130px,1fr));gap:9px;margin:0 0 16px}.lebd-extra-list .department-buttons a{display:block;text-align:center;padding:11px 7px;border:1px solid #cbdcf3;border-radius:5px;background:#f2f7fd;color:#08447c;text-decoration:none;font-weight:600}.lebd-extra-list .department-buttons a.active{background:#1464ac;border-color:#1464ac;color:#fff}.lebd-extra-list .department-buttons a:hover{background:#dbeafa}.lebd-extra-list .department-buttons a.active:hover{background:#115694}.lebd-extra-list .label-active{background:#00a65a;color:white;padding:3px 6px;border-radius:2px}
</style>
<div class="lebd-extra-list">
  <h1>Extra Students Accounts</h1>
  <div class="lebd-list-card">
    <div class="lebd-list-head"><span>List of Extra Students</span><a class="btn-extra" href="{{ route('admin.extra-students.create', $selectedDepartment ? ['department' => $selectedDepartment] : []) }}"><i class="fa fa-plus"></i> Add Student</a></div>
    <div class="lebd-list-body">
      @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
      <nav class="department-buttons" aria-label="Extra Student Departments">
        @foreach($departments as $key => $label)
          <a class="{{ $selectedDepartment === $key ? 'active' : '' }}" href="{{ route('admin.extra-students.index', ['department' => $key]) }}">{{ $label }} <small>({{ $departmentCounts[$key] ?? 0 }})</small></a>
        @endforeach
      </nav>
      <h3 style="font-size:17px;margin:0 0 12px">{{ $selectedDepartment ? $departments[$selectedDepartment] . ' — Students' : 'All Extra Students' }}</h3>
      <form class="lebd-search" method="GET" action="{{ route('admin.extra-students.index') }}">@if($selectedDepartment)<input type="hidden" name="department" value="{{ $selectedDepartment }}">@endif<input type="search" name="search" value="{{ request('search') }}" placeholder="Search name, email, contact" aria-label="Search extra students"><button type="submit"><i class="fa fa-search"></i> Search</button></form>
      <div class="table-responsive"><table><thead><tr><th>ID</th><th>Department</th><th>Name</th><th>Email</th><th>Contact</th><th>Status</th><th>Created On</th></tr></thead><tbody>
      @forelse($students as $student)
      <tr><td>{{ $student->shared_student_id ?? $student->id }}</td><td>{{ $departments[$student->department] ?? ucfirst($student->department) }}</td><td>{{ $student->name }}</td><td>{{ $student->email ?: '-' }}</td><td>{{ $student->contact }}</td><td><span class="{{ $student->status === 'Active' ? 'label-active' : '' }}">{{ $student->status }}</span></td><td>{{ $student->created_at }}</td></tr>
      @empty<tr><td colspan="7" style="text-align:center">No extra students found.</td></tr>@endforelse
      </tbody></table></div>
      {{ $students->links() }}
    </div>
  </div>
</div>
@endsection
