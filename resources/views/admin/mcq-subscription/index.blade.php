@extends('dashboard')
@section('title', 'MCQ Subscription Packages')
@section('content')
<section class="content-header"><h1>MCQ Subscription Packages <small>Manage pricing, category access and limits</small></h1></section>
<section class="content">
 @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
 @php
    // Distinct students from *actual entitlement* records, not pending payment orders.
    // Same student+package through manual and SSLCommerz/referral counts once.
    $mcqStudentCountActive = [];
    $mcqStudentCountAll = [];
    $mcqNow = \Carbon\Carbon::now('Asia/Dhaka')->toDateTimeString();
    $mcqPackageIds = $packages->getCollection()->pluck('id')->map(fn ($id) => (int)$id)->all();
    if ($mcqPackageIds) {
        $mcqPaid = \Illuminate\Support\Facades\DB::table('lebd_mcq_subscriptions')
            ->whereIn('package_id', $mcqPackageIds)
            ->select('package_id','student_id','starts_at','ends_at')->get();
        foreach ($mcqPaid as $mcqRow) {
            $pid = (int)$mcqRow->package_id;
            $sid = (int)$mcqRow->student_id;
            $mcqStudentCountAll[$pid][$sid] = true;
            if ($mcqRow->starts_at <= $mcqNow && $mcqRow->ends_at > $mcqNow) {
                $mcqStudentCountActive[$pid][$sid] = true;
            }
        }
        if (\Illuminate\Support\Facades\Schema::hasTable('lebd_mcq_manual_grants')) {
            $mcqManual = \Illuminate\Support\Facades\DB::table('lebd_mcq_manual_grants')
                ->whereIn('package_id', $mcqPackageIds)
                ->select('package_id','student_id','starts_at','ends_at','status')->get();
            foreach ($mcqManual as $mcqRow) {
                $pid = (int)$mcqRow->package_id;
                $sid = (int)$mcqRow->student_id;
                $mcqStudentCountAll[$pid][$sid] = true;
                if ($mcqRow->status === 'Active' && $mcqRow->starts_at <= $mcqNow && $mcqRow->ends_at > $mcqNow) {
                    $mcqStudentCountActive[$pid][$sid] = true;
                }
            }
        }
    }
 @endphp
 <div class="box box-primary">
  <div class="box-header with-border"><h3 class="box-title">All MCQ Packages</h3><a class="btn btn-primary pull-right" href="{{ route('admin.mcq-subscription.create') }}"><i class="fa fa-plus"></i> Add Package</a></div>
  <div class="box-body table-responsive">
   <table class="table table-bordered table-striped">
    <thead><tr><th>#</th><th>Package</th><th>Access</th><th>Categories</th><th>Validity</th><th>Students</th><th>Price (৳)</th><th>Reading Limit</th><th>Quiz Limit</th><th>Status</th><th>Action</th></tr></thead>
    <tbody>
    @forelse($packages as $p)
      <tr>
       <td>{{ $p->id }}</td>
       <td><strong>{{ $p->name }}</strong><br><small>{{ $p->description }}</small></td>
       <td>{{ ucwords(str_replace('-', ' ', $p->access_type)) }}</td>
       <td>@foreach($links->get($p->id, collect()) as $cat)<span class="label label-info" style="display:inline-block;margin:2px">{{ $cat->name }} ({{ $cat->kind === 'department' ? 'D' : 'N' }})</span>@endforeach</td>
       <td>{{ $p->duration_value }} {{ ucfirst($p->duration_unit) }}</td>
       <td style="white-space:nowrap"><span class="label label-success" style="font-size:12px">Active: {{ count($mcqStudentCountActive[(int)$p->id] ?? []) }}</span><br><small class="text-muted">Total unique: {{ count($mcqStudentCountAll[(int)$p->id] ?? []) }}</small></td>
       <td><small style="text-decoration:line-through;color:#888">৳{{ number_format((float)$p->main_price, 2) }}</small><br><strong style="color:#087a3e">৳{{ number_format((float)$p->price, 2) }}</strong><br><small>Save {{ (float)$p->main_price > 0 ? number_format(((float)$p->discount_amount / (float)$p->main_price) * 100, 2) : '0.00' }}%</small></td>
       <td>{{ $p->reading_limit === null ? 'Unlimited' : $p->reading_limit }}</td>
       <td>{{ $p->quiz_limit === null ? 'Unlimited' : $p->quiz_limit }}</td>
       <td><span class="label {{ $p->status === 'Active' ? 'label-success' : 'label-default' }}">{{ $p->status }}</span></td>
       <td style="min-width:240px;white-space:nowrap"><a class="btn btn-xs btn-info" href="{{ route('admin.mcq-subscription.edit', $p->id) }}">Edit</a>
        <a class="btn btn-xs btn-success" href="{{ route('admin.mcq-subscription.manual-students', $p->id) }}"><i class="fa fa-user-plus"></i> Add Student</a>
        <form method="POST" action="{{ route('admin.mcq-subscription.status', $p->id) }}" style="display:inline">@csrf @method('PATCH')<button class="btn btn-xs btn-default" type="submit">{{ $p->status === 'Active' ? 'Disable' : 'Enable' }}</button></form>
       </td>
      </tr>
    @empty<tr><td colspan="11" class="text-center">এখনো Package তৈরি হয়নি। Add Package চাপুন।</td></tr>@endforelse
    </tbody>
   </table>
  </div>
  <div class="box-footer">{{ $packages->links() }}</div>
 </div>
 <div class="alert alert-info">Step 1: Package management only. Payment / Student access is NOT enabled yet.</div>
</section>
@endsection
