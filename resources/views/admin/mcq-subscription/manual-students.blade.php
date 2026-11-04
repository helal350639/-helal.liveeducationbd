@extends('dashboard')
@section('title', 'Manual Package Access')
@section('content')
<section class="content-header"><h1>Add Student / Manual Access <small>{{ $record->name }}</small></h1></section>
<section class="content">
 <a href="{{ route('admin.mcq-subscription.index') }}" class="btn btn-default" style="margin-bottom:15px">← Package List</a>
 @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
 @if($errors->any())<div class="alert alert-danger"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
 <div class="alert alert-info">Only Admin manual grants. No purchase order, payment transaction, wallet deduction or referral bonus. Student reading/quiz connection requires the existing access service integration.</div>
 <div class="box box-primary"><div class="box-header with-border"><h3 class="box-title">Select Students</h3></div><div class="box-body">
 <form method="GET" action="{{ route('admin.mcq-subscription.manual-students',$record->id) }}" class="form-inline" style="margin-bottom:14px"><input class="form-control" style="min-width:310px" name="search" value="{{ $search }}" placeholder="Student ID, name, mobile or email"> <button class="btn btn-info">Search</button></form>
 <form method="POST" action="{{ route('admin.mcq-subscription.manual-store',$record->id) }}">@csrf
 <div class="table-responsive"><table class="table table-striped table-bordered"><thead><tr><th>Select</th><th>ID</th><th>Student</th><th>Contact</th><th>Account</th><th>Package Access</th></tr></thead><tbody>
 @forelse($students as $student)
 @php $hasPaid = isset($paidIds[(int)$student->id]); $hasGrant = isset($activeIds[(int)$student->id]); $available = $student->status === 'Active' && !$hasPaid && !$hasGrant; @endphp
 <tr><td><input type="checkbox" name="student_ids[]" value="{{ $student->id }}" @disabled(!$available) @checked(in_array($student->id,old('student_ids',[])))></td><td>{{ $student->id }}<br><small>{{ $student->reg_id }}</small></td><td>{{ $student->name }}</td><td>{{ $student->contact }}<br><small>{{ $student->email }}</small></td><td>{{ $student->status }}</td><td>@if($hasPaid)<span class="label label-info">Paid subscription active</span>@elseif($hasGrant)<span class="label label-success">Already Added</span>@else<span class="label label-default">Not Added</span>@endif</td></tr>
 @empty<tr><td colspan="6" class="text-center">No students found.</td></tr>@endforelse
 </tbody></table></div>
 <p class="help-block">Duration: {{ $record->duration_value }} {{ $record->duration_unit }}. Validity begins when this grant is saved.</p>
 <div class="form-group"><label>Admin Note (optional)</label><input type="text" name="note" maxlength="500" class="form-control" value="{{ old('note') }}" placeholder="Reason for granting access"></div>
 <div class="form-group" style="display:flex;flex-wrap:wrap;align-items:center;gap:8px;margin-top:12px">
  <button class="btn btn-success" type="submit" name="save_action" value="continue" onclick="return confirm('Save access for selected students and stay on this page? No order will be created.')"><i class="fa fa-save" aria-hidden="true"></i> Save &amp; Add More</button>
  <button class="btn btn-primary" type="submit" name="save_action" value="done" onclick="return confirm('Save access for selected students and return to Package List? No order will be created.')"><i class="fa fa-check" aria-hidden="true"></i> Save &amp; Done</button>
  <a class="btn btn-default" href="{{ route('admin.mcq-subscription.index') }}">Cancel</a>
 </div>
 </form><div>{{ $students->links() }}</div></div></div>
 <div class="box box-info"><div class="box-header"><h3 class="box-title">Manual Access List</h3></div><div class="box-body table-responsive"><table class="table table-bordered table-striped"><thead><tr><th>Student</th><th>Starts</th><th>Ends</th><th>Status</th><th>Note</th><th>Action</th></tr></thead><tbody>
 @forelse($grants as $g)<tr><td>{{ $g->name }} ({{ $g->student_id }}) <small>{{ $g->contact }}</small></td><td>{{ $g->starts_at }}</td><td>{{ $g->ends_at }}</td><td>{{ $g->status }}</td><td>{{ $g->note }}</td><td>@if($g->status==='Active')<form method="POST" action="{{ route('admin.mcq-subscription.manual-revoke',[$record->id,$g->id]) }}">@csrf @method('PATCH')<button class="btn btn-xs btn-danger" onclick="return confirm('Revoke this manual grant?')">Revoke</button></form>@endif</td></tr>
 @empty<tr><td colspan="6" class="text-center">No manual grants yet.</td></tr>@endforelse
 </tbody></table>{{ $grants->links() }}</div></div>
</section>
@endsection
