@extends('dashboard')
@section('title','Premium Package Students')
@section('content')
<style>
.pms-report{margin:15px auto;max-width:1400px;padding:0 12px;color:#163452}
.pms-report .panel{background:#fff;border:1px solid #d7e4f3;border-radius:8px;padding:16px;margin-top:14px}
.pms-report .pms-filters{display:flex;gap:7px;flex-wrap:wrap;margin:12px 0}
.pms-report .pms-filters a{padding:7px 12px;border:1px solid #b9d2f0;border-radius:6px;text-decoration:none}
.pms-report .pms-filters .active{background:#096bd5;color:#fff}
.pms-report .pms-table{width:100%;border-collapse:collapse;white-space:nowrap}
.pms-report .pms-table th,.pms-report .pms-table td{padding:10px;border:1px solid #dbe5ef;text-align:left}
.pms-report .pms-table th{background:#f2f6fc}
.pms-report .pms-search{display:flex;gap:8px;flex-wrap:wrap}
.pms-report .pms-search input{min-width:200px;padding:8px;border:1px solid #c4d6e9;border-radius:5px}
.pms-report .pms-btn{display:inline-block;padding:7px 12px;background:#0874dc;color:#fff;border:0;border-radius:5px;text-decoration:none}
.pms-report .pms-active{color:#087a3e;font-weight:700}
.pms-report .pms-expired{color:#b04c24;font-weight:700}
@media(max-width:680px){.pms-report{padding:0 7px}.pms-report .panel{padding:10px}}
</style>
<div class="pms-report">
 <a href="{{route('admin.premium-class-packages.index')}}">← Premium Package List</a>
 <h2>{{ $package->name }} — Students</h2>
 <p>একজন Student একাধিক পদ্ধতিতে Access পেলে Total-এ একবার গণনা হয়। Counts-এ মেয়াদোত্তীর্ণ Student-ও অন্তর্ভুক্ত; Active আলাদাভাবে দেখানো হয়েছে।</p>
 <section class="panel">
  <div class="pms-filters">
  @foreach(['all'=>'Total','manual'=>'Manual','wallet'=>'Referral Wallet','ssl'=>'SSLCommerz','other'=>'Other Paid'] as $key=>$label)
   @if($key!=='other'||$counts['other']>0)
    <a class="{{ $method===$key?'active':'' }}" href="{{route('admin.premium-class-packages.students.report',['package'=>$package->id,'method'=>$key])}}">{{ $label }}: {{ $counts[$key === 'all' ? 'total' : $key] ?? 0 }}</a>
   @endif
  @endforeach
  </div>
  <p><b>Currently Active:</b> {{ $counts['active'] }} &nbsp;|&nbsp; <b>Unique Total:</b> {{ $counts['total'] }}</p>
  <form class="pms-search" method="GET" action="{{route('admin.premium-class-packages.students.report',$package->id)}}">
   <input type="hidden" name="method" value="{{ $method }}">
   <input name="q" maxlength="120" value="{{ $search }}" placeholder="Search Name / ID / Email / Contact">
   <button class="pms-btn" type="submit">Search</button>
   <a href="{{route('admin.premium-class-packages.students.report',['package'=>$package->id,'method'=>$method])}}">Clear</a>
  </form>
 </section>
 <section class="panel" style="overflow-x:auto">
  <table class="pms-table">
   <thead><tr><th>#</th><th>Student ID</th><th>Name</th><th>Email</th><th>Contact</th><th>Studying</th><th>Account</th><th>Access Method</th><th>Premium Access</th><th>Latest Expiry</th><th>Action</th></tr></thead>
   <tbody>
    @forelse($students as $row)
     @php $s=$row['student']; @endphp
     <tr>
      <td>{{ $students->firstItem()+$loop->index }}</td>
      <td>{{ $row['id'] }}</td>
      <td>{{ $s->name??'Deleted student' }}</td>
      <td>{{ $s->email??'—' }}</td>
      <td>{{ $s->contact??'—' }}</td>
      <td>{{ $s->studying??'—' }}</td>
      <td>{{ $s->status??'—' }}</td>
      <td>
       @foreach(array_keys($row['methods']) as $key)
        <span style="display:inline-block;padding:2px 6px;margin:2px;background:#e9f2ff;border-radius:4px">{{ \App\Services\PremiumClass\PremiumPackageStudentReport::METHODS[$key] }}</span>
       @endforeach
      </td>
      <td class="{{ $row['active']?'pms-active':'pms-expired' }}">{{ $row['active']?'Active':'Expired' }}</td>
      <td>{{ $row['latest_expires']?:'—' }}</td>
      <td>@if($s)<a class="pms-btn" href="{{route('student.show',$row['id'])}}">View Student</a>@endif</td>
     </tr>
    @empty
     <tr><td colspan="11">এই Filter-এর জন্য Student পাওয়া যায়নি।</td></tr>
    @endforelse
   </tbody>
  </table>
  <div style="margin-top:15px">{{ $students->links() }}</div>
 </section>
</div>
@endsection
