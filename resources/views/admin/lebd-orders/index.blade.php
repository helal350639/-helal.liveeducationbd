@extends('dashboard')
@section('title', 'Admin Orders')
@section('content')
@php
    $section = $section ?? 'video';
    $status = $status ?? 'all';
    $source = $source ?? 'all';
    $range = $range ?? 'all';
    $selectedDate = $selectedDate ?? now('Asia/Dhaka')->toDateString();
    $reportLabel = $reportLabel ?? 'All Dates';
    $orders = $orders ?? null;
    $notice = $notice ?? null;
    $batchOrderDetail = $batchOrderDetail ?? null;
    $paidTotal = $paidTotal ?? 0;
    $pendingTotal = $pendingTotal ?? 0;
    $reportCount = $reportCount ?? 0;
@endphp
@include('admin.lebd-orders._style')
<div class="lebd-orders-page lebd-orders-compact-page">
 <div class="lebd-orders-top"><div><h1>Orders</h1><small>Dashboard / Orders / {{ $section === 'batch' ? 'Batch Order' : 'Video Order' }}</small></div></div>
 @include('admin.lebd-orders._navigation')
 <section class="lebd-orders-panel lebd-orders-video-compact">
  <h2 class="lebd-orders-heading">{{ $section === 'batch' ? '🎓 Batch Order' : '▶ Video Order' }}</h2>
  @if($section === 'video')
   <div class="lebd-video-order-toolbar">
    <div class="lebd-order-tabs" aria-label="Order status">
     <a class="{{ $status === 'all' ? 'selected' : '' }}" href="{{ route('admin.lebd-orders.video', ['status'=>'all','source'=>'all','range'=>$range,'date'=>$selectedDate]) }}">All Orders</a>
     <a class="{{ $status === 'paid' ? 'selected' : '' }}" href="{{ route('admin.lebd-orders.video', ['status'=>'paid','source'=>$source,'range'=>$range,'date'=>$selectedDate]) }}">Paid</a>
     <a class="{{ $status === 'pending' ? 'selected' : '' }}" href="{{ route('admin.lebd-orders.video', ['status'=>'pending','source'=>$source,'range'=>$range,'date'=>$selectedDate]) }}">Pending</a>
    </div>
    <div class="lebd-source-label">{{ $source === 'all' ? 'SSLCommerz + Referral Balance' : ($source === 'ssl' ? 'SSLCommerz Orders' : 'Referral Balance Orders') }}</div>
   </div>
   <form method="GET" action="{{ route('admin.lebd-orders.video') }}" class="lebd-orders-filter lebd-orders-dates">
    <input type="hidden" name="status" value="{{ $status }}">
    <input type="hidden" name="source" value="{{ $source }}">
    <label for="lebd-order-date">Order Date</label>
    <input id="lebd-order-date" type="date" name="date" value="{{ $selectedDate }}">
    <button type="submit" name="range" value="day">Show Date</button>
    <a class="lebd-all-dates-link {{ $range === 'all' ? 'lebd-all-active' : '' }}" href="{{ route('admin.lebd-orders.video', ['status'=>$status,'source'=>$source,'range'=>'all']) }}">All Dates</a>
   </form>
   <p class="lebd-orders-report-note">Showing <strong>{{ ucfirst($status) }}</strong> — {{ $source === 'all' ? 'All Payment Sources' : ($source === 'ssl' ? 'SSLCommerz' : 'Referral Balance') }} — {{ $reportLabel }}{{ $range === 'day' ? ' (12:01 AM–11:59 PM)' : '' }}. Latest order first.</p>
  @else
   <div class="lebd-video-order-toolbar">
    <div class="lebd-order-tabs" aria-label="Batch payment status">
     <a class="{{ $status==='all'?'selected':'' }}" href="{{ route('admin.lebd-orders.batch',['status'=>'all','source'=>'all','range'=>$range,'date'=>$selectedDate]) }}">All Orders</a>
     <a class="{{ $status==='paid'?'selected':'' }}" href="{{ route('admin.lebd-orders.batch',['status'=>'paid','source'=>$source,'range'=>$range,'date'=>$selectedDate]) }}">Paid</a>
     <a class="{{ $status==='pending'?'selected':'' }}" href="{{ route('admin.lebd-orders.batch',['status'=>'pending','source'=>$source,'range'=>$range,'date'=>$selectedDate]) }}">Pending</a>
    </div>
    <div class="lebd-source-label">{{ $source==='all'?'SSLCommerz + Referral Balance':($source==='ssl'?'SSLCommerz Orders':'Referral Balance Orders') }}</div>
   </div>
   <form class="lebd-orders-filter lebd-orders-dates" method="GET" action="{{ route('admin.lebd-orders.batch') }}">
    <input type="hidden" name="status" value="{{ $status }}"><input type="hidden" name="source" value="{{ $source }}">
    <label for="lebd-batch-date">Order Date</label><input type="date" id="lebd-batch-date" name="date" value="{{ $selectedDate }}">
    <button type="submit" name="range" value="day">Show Date</button>
    <a class="lebd-all-dates-link {{ $range==='all'?'lebd-all-active':'' }}" href="{{ route('admin.lebd-orders.batch',['status'=>$status,'source'=>$source,'range'=>'all']) }}">All Dates</a>
   </form>
   <p class="lebd-orders-report-note">Showing <strong>{{ ucfirst($status) }}</strong> — {{ $source==='all'?'All Payment Sources':($source==='ssl'?'SSLCommerz':'Referral Balance') }} — {{ $reportLabel }}. Latest orders first. Paid = Processing / Complete.</p>
   @if($batchOrderDetail)
    <div class="lebd-batch-detail"><strong>Order #{{ $batchOrderDetail->id }}</strong> · {{ $batchOrderDetail->batch_name ?? 'Batch not found' }} · {{ $batchOrderDetail->department_name ?? 'No department' }} · {{ $batchOrderDetail->student_name ?? $batchOrderDetail->name }} · {{ $batchOrderDetail->phone ?? '—' }} · ৳{{ number_format((float)$batchOrderDetail->amount,2) }} · {{ $batchOrderDetail->status }}
    <a href="{{ route('admin.lebd-orders.batch',request()->except('order')) }}">Close</a></div>
   @endif
  @endif
  @if($notice)<div class="lebd-orders-empty">{{ $notice }}</div>@endif
  @if($orders && $section==='batch')
   <div class="lebd-orders-table-wrap"><table class="lebd-orders-table"><thead><tr><th>#</th><th>Order ID</th><th>Student</th><th>Mobile</th><th>Batch</th><th>Department</th><th>Amount</th><th>Payment Source</th><th>Status</th><th>Order Date</th><th>Action</th></tr></thead><tbody>
    @forelse($orders as $row)
    <tr><td>{{ $row->id }}</td><td class="lebd-order-id">{{ $row->transaction_id }}</td>
    <td>{{ $row->student_name ?: $row->name }}<div class="lebd-orders-muted">{{ $row->student_email ?: $row->email }}</div></td>
    <td>{{ $row->student_contact ?: $row->phone }}</td>
    <td>{{ $row->batch_name ?: ('Batch #'.$row->batch_id) }}</td>
    <td>{{ $row->department_name ?: ('Department #'.$row->department_id) }}</td>
    <td>৳{{ number_format((float)$row->amount,2) }}</td>
    <td>{{ str_starts_with((string)$row->transaction_id,'LEBDBR')?'Referral Balance':'SSLCommerz / Legacy' }}</td>
    <td><span class="lebd-orders-status {{ strtolower((string)$row->status) }}">{{ $row->status }}</span></td>
    <td>{{ $row->created_at }}</td>
    <td><a class="lebd-orders-action" href="{{ route('admin.lebd-orders.batch',array_merge(request()->except('page'),['order'=>$row->id])) }}">View</a></td></tr>
    @empty <tr><td colspan="11" class="lebd-orders-empty">No batch orders match these filters.</td></tr> @endforelse
   </tbody></table></div>
   <div class="lebd-orders-report-total">
    <div><span>Total Received (Paid Only)</span><strong>৳{{ number_format($paidTotal,2) }}</strong></div>
    <div><span>Pending (Not Received)</span><strong>৳{{ number_format($pendingTotal,2) }}</strong></div>
    <div><span>Total Orders</span><strong>{{ number_format($reportCount) }}</strong></div>
   </div>
   <div class="lebd-orders-source-switch">
    <a href="{{ route('admin.lebd-orders.batch',['source'=>'all','status'=>'all','range'=>'all']) }}">All Sources & Dates</a>
    <a href="{{ route('admin.lebd-orders.batch',['source'=>'ssl','status'=>'all','range'=>'all']) }}">SSLCommerz Orders</a>
    <a href="{{ route('admin.lebd-orders.batch',['source'=>'referral','status'=>'paid','range'=>'all']) }}">Referral Paid Orders</a>
   </div><div class="lebd-orders-pagination">{{ $orders->onEachSide(1)->links() }}</div>
  @endif
  @if($orders && $section==='video')
   <div class="lebd-orders-table-wrap"><table class="lebd-orders-table"><thead><tr><th>#</th><th>Order ID</th><th>Student</th><th>Mobile Number</th><th>Package Name</th><th>Amount</th><th>Payment Source</th><th>Status</th><th>Order Date</th><th>Expiry</th><th>Action</th></tr></thead><tbody>
    @forelse($orders as $row)
     <tr>
      <td>{{ $row->id }}</td>
      <td class="lebd-order-id" title="{{ $row->tran_id }}">{{ $row->tran_id }}</td>
      <td>{{ $row->student_name ?: ('Student #'.$row->student_id) }}<div class="lebd-orders-muted">{{ $row->student_email }}</div></td>
      <td>{{ $row->student_contact ?: '—' }}</td>
      <td>{{ $row->package_name ?: ('Package #'.$row->package_id) }}</td>
      <td>৳{{ number_format((float)$row->amount,2) }}</td>
      <td>{{ str_starts_with((string)$row->tran_id, 'LEBDR') ? 'Referral Balance' : (str_starts_with((string)$row->tran_id, 'LEBDP') ? 'SSLCommerz' : 'Other / Unknown') }}</td>
      <td><span class="lebd-orders-status {{ $row->status }}">{{ ucfirst($row->status) }}</span></td>
      <td>{{ $row->created_at }}</td>
      <td>{{ $row->expires_at ?? '—' }}</td>
      <td><a class="lebd-orders-action" href="{{ route('admin.lebd-orders.video.show',$row->id) }}">View</a></td>
     </tr>
    @empty
     <tr><td colspan="11" class="lebd-orders-empty">No {{ $source==='all'?'':'selected' }} orders found for these filters. Try All Dates.</td></tr>
    @endforelse
   </tbody></table></div>
   <div class="lebd-orders-report-total">
    <div><span>Total Received (Paid Only)</span><strong>৳{{ number_format($paidTotal,2) }}</strong></div>
    <div><span>Pending (Not Received)</span><strong>৳{{ number_format($pendingTotal,2) }}</strong></div>
    <div><span>Total {{ ucfirst($status) }} Orders</span><strong>{{ number_format($reportCount) }}</strong></div>
   </div>
   <div class="lebd-orders-source-switch">
    <a href="{{ route('admin.lebd-orders.video', ['source'=>'all','status'=>'all','range'=>'all']) }}">All Orders — All Sources & Dates</a>
    <a href="{{ route('admin.lebd-orders.video', ['source'=>'ssl','status'=>'all','range'=>'all']) }}">SSLCommerz Orders</a>
    <a href="{{ route('admin.lebd-orders.video', ['source'=>'referral','status'=>'paid','range'=>'all']) }}">Referral Balance Paid Orders</a>
   </div>
   <div class="lebd-orders-pagination">{{ $orders->onEachSide(1)->links() }}</div>
  @endif
 </section>
</div>
@endsection
