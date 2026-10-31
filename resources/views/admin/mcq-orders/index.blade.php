@extends('homepage')
@section('title', 'MCQ Study Orders')
@section('content')
@include('admin.lebd-orders._style')
<div class="lebd-orders-page lebd-orders-compact-page">
 <div class="lebd-orders-top"><div><h1>Orders</h1><small>Dashboard / Orders / MCQ Study Order</small></div></div>
 @include('admin.lebd-orders._navigation')
 <section class="lebd-orders-panel">
  <h2 class="lebd-orders-heading">▣ MCQ Study Order</h2>
  <div class="lebd-video-order-toolbar">
   <div class="lebd-order-tabs">
    <a class="{{ $state === 'all' ? 'selected' : '' }}" href="{{ route('admin.lebd.mcq-orders.index',array_merge(request()->except('page'),['status'=>'all'])) }}">All Orders</a>
    <a class="{{ $state === 'paid' ? 'selected' : '' }}" href="{{ route('admin.lebd.mcq-orders.index',array_merge(request()->except('page'),['status'=>'paid'])) }}">Paid</a>
    <a class="{{ $state === 'pending' ? 'selected' : '' }}" href="{{ route('admin.lebd.mcq-orders.index',array_merge(request()->except('page'),['status'=>'pending'])) }}">Pending</a>
   </div>
   <span class="lebd-source-label">SSLCommerz + Referral Balance</span>
  </div>
  @if($notice)<div class="lebd-orders-empty">{{ $notice }}</div>@endif
  <form class="lebd-orders-filter lebd-orders-dates" method="GET" action="{{ route('admin.lebd.mcq-orders.index') }}">
   <input type="hidden" name="status" value="{{ $state }}">
   <input type="hidden" name="range" value="{{ $range }}">
   <input type="search" name="search" value="{{ $search }}" placeholder="Order ID, Student, Mobile, Package">
   <label>Order Date <input type="date" name="date" value="{{ $date }}"></label>
   <label>Payment Source <select name="source">
    <option value="all" @selected($source === 'all')>All Sources</option>
    <option value="ssl" @selected($source === 'ssl')>SSLCommerz</option>
    <option value="referral" @selected($source === 'referral')>Referral Balance</option>
   </select></label>
   <button type="submit">Show Date</button>
   <a class="lebd-all-dates-link {{ $range === 'all' ? 'lebd-all-active' : '' }}" href="{{ route('admin.lebd.mcq-orders.index', array_merge(request()->except('page','date'),['range'=>'all'])) }}">All Dates</a>
   <a class="lebd-all-dates-link {{ $range === 'day' ? 'lebd-all-active' : '' }}" href="{{ route('admin.lebd.mcq-orders.index', array_merge(request()->except('page'),['range'=>'day'])) }}">Selected Date</a>
  </form>
  <p class="lebd-orders-report-note">Latest MCQ orders first. Expiry is from the current student subscription.</p>
  <div class="lebd-orders-table-wrap"><table class="lebd-orders-table"><thead><tr><th>#</th><th>Order ID</th><th>Student</th><th>Mobile Number</th><th>Package Name</th><th>Amount</th><th>Payment Source</th><th>Status</th><th>Order Date</th><th>Expiry</th><th>Action</th></tr></thead><tbody>
  @forelse(($orders ?? []) as $order)
   <tr>
    <td>{{ $order->id }}</td>
    <td class="lebd-order-id">{{ $order->transaction_id }}</td>
    <td>{{ $order->student_name ?: '—' }}<div class="lebd-orders-muted">{{ $order->student_email ?: '—' }}</div></td>
    <td>{{ $order->student_phone ?: '—' }}</td>
    <td>{{ $order->package_name }}</td>
    <td>৳{{ number_format((float)$order->amount,2) }}</td>
    <td>{{ str_starts_with((string)$order->transaction_id,'MCQR') ? 'Referral Balance' : (str_starts_with((string)$order->transaction_id,'MCQS') ? 'SSLCommerz' : 'Other') }}</td>
    <td><span class="lebd-orders-status {{ $order->status === 'Complete' ? 'paid' : strtolower((string)$order->status) }}">{{ $order->status === 'Complete' ? 'Paid' : $order->status }}</span></td>
    <td>{{ $order->created_at }}</td>
    <td>{{ $order->status === 'Complete' ? ($order->expires_at ?: '—') : '—' }}</td>
    <td><a class="lebd-orders-action" href="{{ route('admin.lebd.mcq-orders.show', ['order' => $order->id]) }}">View</a></td>
   </tr>
  @empty
   <tr><td colspan="11" class="lebd-orders-empty">কোনো MCQ Study Order পাওয়া যায়নি।</td></tr>
  @endforelse
  </tbody></table></div>
  @if($orders)<div class="lebd-orders-pagination">{{ $orders->links() }}</div>@endif
  <div class="lebd-orders-report-total">
    <div><span>Orders (filtered)</span><strong>{{ number_format($reportCount) }}</strong></div>
    <div><span>Paid Total</span><strong>৳{{ number_format($paidTotal,2) }}</strong></div>
    <div><span>Pending Total</span><strong>৳{{ number_format($pendingTotal,2) }}</strong></div>
  </div>
 </section>
</div>
@endsection
