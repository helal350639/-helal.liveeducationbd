@extends('dashboard')
@section('title','Exam Section Orders')
@section('content')
@include('admin.lebd-orders._style')
<div class="lebd-orders-page lebd-orders-compact-page">
 <div class="lebd-orders-top"><div><h1>Orders</h1><small>Dashboard / Orders / Exam Section Order</small></div></div>
 @include('admin.lebd-orders._navigation', ['section'=>'exam'])
 <section class="lebd-orders-panel lebd-orders-video-compact">
  <h2 class="lebd-orders-heading">▤ Exam Section Order</h2>
  <div class="lebd-video-order-toolbar">
   <div class="lebd-order-tabs">
    @foreach(['all'=>'All Orders','paid'=>'Paid','pending'=>'Pending','failed'=>'Failed','cancelled'=>'Cancelled'] as $v=>$label)
    <a class="{{ $status===$v ? 'selected':'' }}" href="{{ route('admin.lebd-orders.exam',array_merge(request()->except(['page','status']),['status'=>$v])) }}">{{ $label }}</a>
    @endforeach
   </div>
   <div class="lebd-source-label">SSLCommerz + Referral Balance</div>
  </div>
  <form method="GET" action="{{ route('admin.lebd-orders.exam') }}" class="lebd-orders-filter lebd-orders-dates" style="display:flex;flex-wrap:wrap;gap:8px;align-items:center">
   <input type="hidden" name="status" value="{{ $status }}">
   <input type="text" name="search" value="{{ $search }}" placeholder="Order ID, Student, Mobile, Package" aria-label="Search exam orders" style="max-width:260px">
   <select name="source" aria-label="Payment source"><option value="all" {{ $source==='all'?'selected':'' }}>All Sources</option><option value="ssl" {{ $source==='ssl'?'selected':'' }}>SSLCommerz</option><option value="referral" {{ $source==='referral'?'selected':'' }}>Referral Balance</option></select>
   <input type="date" name="date" value="{{ $selectedDate }}" aria-label="Order date">
   <button type="submit" name="range" value="day">Show Date</button>
   <a class="lebd-all-dates-link {{ $range==='all'?'lebd-all-active':'' }}" href="{{ route('admin.lebd-orders.exam',['status'=>$status,'source'=>$source,'range'=>'all','search'=>$search]) }}">All Dates</a>
  </form>
  @if($notice)<div class="lebd-orders-empty">{{ $notice }}</div>@endif
  @if($orders)
  <p class="lebd-orders-report-note">Latest orders first. Paid totals count only verified paid orders; Pending is not received.</p>
  <div class="lebd-orders-table-wrap"><table class="lebd-orders-table"><thead><tr><th>#</th><th>Order ID</th><th>Student</th><th>Mobile Number</th><th>Package Name</th><th>Amount</th><th>Payment Source</th><th>Status</th><th>Order Date</th><th>Expiry</th><th>Action</th></tr></thead><tbody>
  @forelse($orders as $row)<tr>
   <td>{{ $row->id }}</td><td class="lebd-order-id" title="{{ $row->transaction_id }}">{{ $row->transaction_id }}</td>
   <td>{{ $row->student_name ?: ('Student #'.$row->student_id) }}<div class="lebd-orders-muted">{{ $row->student_email }}</div></td>
   <td>{{ $row->student_contact ?: '—' }}</td><td>{{ $row->package_name ?: ('Package #'.$row->package_id) }}</td>
   <td>৳{{ number_format((float)$row->amount,2) }}</td>
   <td>{{ $row->method==='wallet'?'Referral Balance':($row->method==='sslcommerz'?'SSLCommerz':'Other / Unknown') }}</td>
   <td><span class="lebd-orders-status {{ strtolower((string)$row->status) }}">{{ ucfirst((string)$row->status) }}</span></td>
   <td>{{ $row->created_at }}</td><td>{{ $row->access_expiry ?? '—' }}</td>
   <td><a class="lebd-orders-action" href="{{ route('admin.lebd-orders.exam.show',$row->id) }}">View</a></td>
  </tr>@empty<tr><td colspan="11" class="lebd-orders-empty">No Exam Section orders match the selected filters.</td></tr>@endforelse
  </tbody></table></div>
  <div class="lebd-orders-report-total">
   <div><span>Total Received (Paid Only)</span><strong>৳{{ number_format((float)($totals->paid??0),2) }}</strong></div>
   <div><span>Pending (Not Received)</span><strong>৳{{ number_format((float)($totals->pending??0),2) }}</strong></div>
   <div><span>Total Orders</span><strong>{{ number_format((int)($totals->total??0)) }}</strong></div>
  </div>
  <div class="lebd-orders-source-switch">
   <a href="{{ route('admin.lebd-orders.exam',['source'=>'all','status'=>'all','range'=>'all']) }}">All Orders — All Sources &amp; Dates</a>
   <a href="{{ route('admin.lebd-orders.exam',['source'=>'ssl','status'=>'all','range'=>'all']) }}">SSLCommerz Orders</a>
   <a href="{{ route('admin.lebd-orders.exam',['source'=>'referral','status'=>'paid','range'=>'all']) }}">Referral Balance Paid Orders</a>
  </div>
  <div class="lebd-orders-pagination">{{ $orders->onEachSide(1)->links() }}</div>
  @endif
 </section>
</div>
@endsection
