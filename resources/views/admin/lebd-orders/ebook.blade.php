@extends('dashboard')
@section('title', 'Admin E-Book Orders')
@section('content')
@php
  $ebStatus = request('status', 'all');
  $ebSource = request('source', 'all');
  $ebDate = request('date', now('Asia/Dhaka')->toDateString());
  $ebSearch = request('search', '');
  $ebParams = request()->except('page');
  $section = 'ebook';
  $notice = $notice ?? null;
  $orders = $orders ?? null;
@endphp
@include('admin.lebd-orders._style')
<style>
/* Same compact Order layout as Video Order, scoped to E-Book-specific extras. */
.lebd-ebook-compact .lebd-eb-extra{display:flex;gap:6px;align-items:center;flex-wrap:wrap;margin:5px 0 9px}
.lebd-ebook-compact .lebd-eb-extra input,.lebd-ebook-compact .lebd-eb-extra select{min-height:29px;border:1px solid #bcd4ff;border-radius:6px;background:#fff;padding:4px 7px;font-size:11px;color:#0b315d}
.lebd-ebook-compact .lebd-eb-extra button{border:1px solid #1577e6;color:#fff;background:#0a73df;border-radius:6px;padding:6px 12px;font-size:11px;font-weight:700}
.lebd-ebook-compact .lebd-order-source-pill{display:inline-block;background:#eef5ff;color:#1b4b84;border-radius:7px;padding:6px 8px;font-size:10px;font-weight:700}
.lebd-ebook-compact .lebd-orders-table{min-width:900px}
.lebd-ebook-compact .lebd-order-paid{background:#def8e9;color:#0c743e;border-radius:7px;padding:4px 8px;font-size:10px;font-weight:700}
.lebd-ebook-compact .lebd-order-wait{background:#fff1d4;color:#8f5900;border-radius:7px;padding:4px 8px;font-size:10px;font-weight:700}
.lebd-ebook-compact .lebd-order-failed{background:#ffe4e5;color:#a52a38;border-radius:7px;padding:4px 8px;font-size:10px;font-weight:700}
.lebd-ebook-compact .lebd-orders-pagination{margin-top:14px}
@media(max-width:680px){.lebd-ebook-compact .lebd-eb-extra input[type=search]{flex:1 1 150px}.lebd-ebook-compact .lebd-orders-report-total{flex-wrap:wrap}}
</style>
<div class="lebd-orders-page lebd-orders-compact-page lebd-ebook-compact">
 <div class="lebd-orders-top"><div><h1>Orders</h1><small>Dashboard / Orders / E-Book Order</small></div></div>
 @include('admin.lebd-orders._navigation')
 <section class="lebd-orders-panel lebd-orders-video-compact">
  <h2 class="lebd-orders-heading">📖 E-Book Order</h2>
  <div class="lebd-video-order-toolbar">
   <div class="lebd-order-tabs" aria-label="E-Book order status">
    <a class="{{ $ebStatus==='all' ? 'selected' : '' }}" href="{{route('admin.lebd-orders.ebook', array_merge($ebParams,['status'=>'all']))}}">All Orders</a>
    <a class="{{ $ebStatus==='Complete' ? 'selected' : '' }}" href="{{route('admin.lebd-orders.ebook', array_merge($ebParams,['status'=>'Complete']))}}">Paid</a>
    <a class="{{ $ebStatus==='Pending' ? 'selected' : '' }}" href="{{route('admin.lebd-orders.ebook', array_merge($ebParams,['status'=>'Pending']))}}">Pending</a>
   </div>
   <div class="lebd-source-label">{{ $ebSource==='all' ? 'SSLCommerz + Referral Balance' : ($ebSource==='ssl' ? 'SSLCommerz Orders' : 'Referral Balance Orders') }}</div>
  </div>
  <form method="GET" action="{{route('admin.lebd-orders.ebook')}}" class="lebd-orders-filter lebd-orders-dates lebd-eb-extra">
   <input type="hidden" name="status" value="{{$ebStatus}}">
   <label for="lebd-eb-date">Order Date</label>
   <input type="date" id="lebd-eb-date" name="date" value="{{request('date','')}}">
   <select name="source" aria-label="Payment source"><option value="all">All Payment Sources</option><option value="ssl" @selected($ebSource==='ssl')>SSLCommerz</option><option value="referral" @selected($ebSource==='referral')>Referral Balance</option></select>
   <input type="search" name="search" value="{{$ebSearch}}" placeholder="Order ID, student, E-Book" aria-label="Search orders">
   <button type="submit">Show Date / Search</button>
   <a class="lebd-all-dates-link {{request('date')?'':'lebd-all-active'}}" href="{{route('admin.lebd-orders.ebook',['status'=>$ebStatus,'source'=>$ebSource,'search'=>$ebSearch])}}">All Dates</a>
  </form>
  <p class="lebd-orders-report-note">Showing <strong>{{ $ebStatus==='Complete'?'Paid':($ebStatus==='Pending'?'Pending':($ebStatus==='all'?'All':ucfirst($ebStatus))) }}</strong> — {{ $ebSource==='all'?'All Payment Sources':($ebSource==='ssl'?'SSLCommerz':'Referral Balance') }} — {{request('date')?:'All Dates'}}. Latest orders first.</p>
  @if($notice)<div class="lebd-orders-empty">{{$notice}}</div>@endif
  @if($orders)
   <div class="lebd-orders-table-wrap"><table class="lebd-orders-table"><thead><tr><th>#</th><th>Order ID</th><th>Student</th><th>Mobile Number</th><th>E-Book / Package Name</th><th>Amount</th><th>Payment Source</th><th>Status</th><th>Order Date</th><th>Paid Date</th></tr></thead><tbody>
   @forelse($orders as $order)
    <tr><td>{{$order->id}}</td><td class="lebd-order-id" title="{{$order->transaction_id}}">{{$order->transaction_id}}</td>
    <td>{{$order->student_name ?: ('Student #'.$order->student_id)}}<div class="lebd-orders-muted">{{$order->student_email}}</div></td>
    <td>{{$order->student_contact ?: '—'}}</td>
    <td>{{$order->book_name ?: ('Book #'.$order->category_id)}}<div class="lebd-orders-muted">{{$order->package_name}}</div></td>
    <td>৳{{number_format((float)$order->amount,2)}}</td>
    <td>{{ (strtoupper(trim((string)($order->gateway_validation_id ?? ''))) === 'REFERRAL_BALANCE' || str_starts_with((string)$order->transaction_id,'EBR')) ? 'Referral Balance' : 'SSLCommerz' }}</td>
    <td><span class="{{ $order->status==='Complete'?'lebd-order-paid':($order->status==='Pending'?'lebd-order-wait':'lebd-order-failed') }}">{{$order->status==='Complete'?'Paid':$order->status}}</span></td>
    <td>{{$order->created_at}}</td><td>{{$order->paid_at??'—'}}</td></tr>
   @empty <tr><td colspan="10" class="lebd-orders-empty">No E-Book orders match these filters.</td></tr>
   @endforelse
   </tbody></table></div>
   <div class="lebd-orders-report-total">
    <div><span>Total Received (Paid Only)</span><strong>৳{{number_format((float)($totals->paid??0),2)}}</strong></div>
    <div><span>Pending (Not Received)</span><strong>৳{{number_format((float)($totals->pending??0),2)}}</strong></div>
    <div><span>Total All Orders</span><strong>{{number_format((int)($totals->total??0))}}</strong></div>
   </div>
   <div class="lebd-orders-source-switch">
    <a href="{{route('admin.lebd-orders.ebook',['status'=>'all','source'=>'all'])}}">All Orders — All Sources &amp; Dates</a>
    <a href="{{route('admin.lebd-orders.ebook',['status'=>'all','source'=>'ssl'])}}">SSLCommerz Orders</a>
    <a href="{{route('admin.lebd-orders.ebook',['status'=>'Complete','source'=>'referral'])}}">Referral Balance Paid Orders</a>
   </div>
   <div class="lebd-orders-pagination">{{$orders->onEachSide(1)->links()}}</div>
  @endif
 </section>
</div>
@endsection
