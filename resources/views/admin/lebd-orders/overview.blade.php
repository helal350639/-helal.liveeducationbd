@extends('dashboard')
@section('title','Orders — Overview All Package')
@section('content')
@include('admin.lebd-orders._style')
<style>
.lebd-overview{max-width:1300px;margin:18px auto 48px;padding:0 14px;color:#0d2d5b}.lebd-overview h1{font-size:25px;font-weight:800;margin:0}.lebd-overview .muted{color:#677e9e;font-size:12px}.lebd-overview .panel{background:#fff;border:1px solid #d8e5f7;border-radius:12px;padding:15px;margin-top:13px;box-shadow:0 3px 14px #09346508}.lebd-overview .grid5{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:10px}.lebd-overview .grid4{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px}.lebd-overview .metric{display:block;background:#f5f9ff;border:1px solid #dce8f6;border-radius:10px;padding:14px;text-decoration:none;color:#123a70}.lebd-overview .metric strong{display:block;font-size:21px;margin:8px 0}.lebd-overview .metric .good{color:#078a59}.lebd-overview .metric .warn{color:#d35558}.lebd-overview .metric small{display:block;font-size:11px;margin-top:4px}.lebd-overview .filters{display:flex;gap:9px;flex-wrap:wrap;align-items:end}.lebd-overview label{font-size:12px;font-weight:700}.lebd-overview input,.lebd-overview select{display:block;padding:9px;border:1px solid #d2e2f6;border-radius:7px;margin-top:4px}.lebd-overview button{padding:10px 18px;background:#096be0;color:white;border:0;border-radius:7px;cursor:pointer}.lebd-overview .table-wrap{overflow-x:auto}.lebd-overview table{width:100%;border-collapse:collapse;font-size:12px}.lebd-overview th{background:#edf3fa;text-align:left}.lebd-overview th,.lebd-overview td{padding:10px;border-bottom:1px solid #e3edf8;white-space:nowrap}.lebd-overview .pill{display:inline-block;border-radius:7px;background:#e8f2ff;color:#0b5bb6;padding:4px 7px}.lebd-overview .paid{background:#dff6e9;color:#0b7545}.lebd-overview .pending{background:#fff0d8;color:#9b5e05}.lebd-overview .actions a{color:#0868d5;font-weight:bold}@media(max-width:900px){.lebd-overview .grid5{grid-template-columns:repeat(2,minmax(0,1fr))}.lebd-overview .grid4{grid-template-columns:repeat(2,minmax(0,1fr))}}@media(max-width:520px){.lebd-overview .grid5,.lebd-overview .grid4{grid-template-columns:1fr 1fr}.lebd-overview .metric{padding:9px}.lebd-overview .metric strong{font-size:16px}}

/* Overview time presets and print controls; scoped to this page only. */
.lebd-overview .lebd-presets{display:flex;flex-wrap:wrap;align-items:center;gap:9px;margin-bottom:11px}
.lebd-overview .lebd-period{display:inline-flex;align-items:center;gap:6px;border:1px solid #bed8f7;background:#f2f8ff;border-radius:8px;padding:9px 12px;cursor:pointer;font-size:12px;color:#18508b;font-weight:750}
.lebd-overview .lebd-period input{display:inline-block;margin:0;accent-color:#075fd6;width:15px;height:15px}
.lebd-overview .lebd-period:has(input:checked){background:#e0efff;border-color:#0b68d9;box-shadow:inset 0 0 0 1px #0b68d9}
.lebd-overview .lebd-period.saved::after{content:'Saved';font-size:10px;color:#127b54;margin-left:3px}
.lebd-overview .lebd-save-btn{background:#14745d}.lebd-overview .lebd-print-btn{background:#224b84}.lebd-overview .lebd-clear{padding:9px;color:#1463bb;text-decoration:none}
.lebd-overview .lebd-filter-form{display:block}.lebd-overview .lebd-filter-lines{display:flex;gap:9px;flex-wrap:wrap;align-items:end}
.lebd-overview .lebd-print-title{display:none}
@page{size:A4 portrait;margin:11mm}
@media print{
 html,body{margin:0!important;background:#fff!important;color:#000!important;font-size:9pt!important}
 body *{visibility:hidden!important}
 .lebd-overview,.lebd-overview *{visibility:visible!important}
 .lebd-overview{position:absolute!important;left:0!important;top:0!important;width:100%!important;max-width:none!important;margin:0!important;padding:0!important;color:#000!important}
 .lebd-overview .lebd-print-title{display:block!important;font-size:10pt!important;margin:0 0 8px!important}
 .lebd-overview .lebd-no-print{display:none!important}
 .lebd-overview .panel{box-shadow:none!important;border:1px solid #bbb!important;break-inside:avoid;padding:8px!important;margin-top:8px!important}
 .lebd-overview .grid5{grid-template-columns:repeat(5,minmax(0,1fr))!important;gap:4px!important}
 .lebd-overview .grid4{grid-template-columns:repeat(4,minmax(0,1fr))!important;gap:4px!important}
 .lebd-overview .metric{padding:6px!important;box-shadow:none!important;break-inside:avoid}
 .lebd-overview .metric strong{font-size:11pt!important;margin:4px 0!important}
 .lebd-overview table{font-size:7pt!important;table-layout:auto!important;width:100%!important}
 .lebd-overview th,.lebd-overview td{padding:4px 3px!important;white-space:normal!important;overflow-wrap:anywhere!important}
 .lebd-overview thead{display:table-header-group}
 .lebd-overview tr{break-inside:avoid}
 .lebd-overview .table-wrap{overflow:visible!important}
 .lebd-overview .actions{display:none!important}
 .lebd-overview th:last-child{display:none!important}
 .lebd-overview h1{font-size:16pt!important}
}
</style>
<div class="lebd-overview">
 <h1>Orders — Overview All Package</h1><div class="muted">Dashboard / Orders / Overview · Actual database figures, no sample data</div>
 @php $section='overview'; @endphp
 @include('admin.lebd-orders._navigation')
 <form class="panel lebd-filter-form lebd-no-print" method="GET" action="{{ route('admin.lebd-orders.overview') }}" id="lebdOverviewFilter">
  <div class="lebd-presets" role="radiogroup" aria-label="Quick date filter">
   @foreach(['1d'=>'১ দিন','7d'=>'৭ দিন','30d'=>'৩০ দিন','1y'=>'১ বছর','custom'=>'Custom Dates','all'=>'All Dates'] as $value=>$label)
    <label class="lebd-period {{ $savedPeriod===$value?'saved':'' }}"><input type="radio" name="period" value="{{ $value }}" {{ $period===$value?'checked':'' }}><span>{{ $label }}</span></label>
   @endforeach
   <button type="submit" name="save_default" value="1" class="lebd-save-btn">✓ Save Default</button>
   <button type="button" class="lebd-print-btn" onclick="window.print()">Print A4 / Save PDF</button>
  </div>
  <div class="lebd-filter-lines">
   <label>From<input type="date" name="from" value="{{ $from }}" id="lebdOvFrom"></label>
   <label>To<input type="date" name="to" value="{{ $to }}" id="lebdOvTo"></label>
   <label>Status<select name="status"><option value="all" @selected($status==='all')>All Orders</option><option value="paid" @selected($status==='paid')>Paid</option><option value="pending" @selected($status==='pending')>Pending</option></select></label>
   <button type="submit">Show Orders</button>
   <a class="lebd-clear" href="{{ route('admin.lebd-orders.overview',['period'=>'all']) }}">All Dates</a>
  </div>
  <div class="muted" style="margin-top:8px">Selected: {{ ['1d'=>'Today','7d'=>'Last 7 days','30d'=>'Last 30 days','1y'=>'Last 1 year','custom'=>'Custom range','all'=>'All dates'][$period] ?? 'All dates' }} · Saved default: {{ ['1d'=>'১ দিন','7d'=>'৭ দিন','30d'=>'৩০ দিন','1y'=>'১ বছর','custom'=>'Custom','all'=>'All Dates'][$savedPeriod] ?? 'All Dates' }} (this admin session)</div>
 </form>
 <div class="lebd-print-title">Orders Overview — {{ $from ?: 'All dates' }} to {{ $to ?: 'All dates' }} · {{ ucfirst($status) }} orders · Printed {{ now('Asia/Dhaka')->format('d M Y, h:i A') }} · Recent Orders: latest 15 matching results</div>
 <script>
 (function(){'use strict';
   var form=document.getElementById('lebdOverviewFilter'); if(!form)return;
   ['lebdOvFrom','lebdOvTo'].forEach(function(id){var input=document.getElementById(id);if(input)input.addEventListener('change',function(){var custom=form.querySelector('input[name="period"][value="custom"]');if(custom)custom.checked=true;});});
 })();
 </script>
 <section class="panel"><h2 style="font-size:17px;margin:0 0 13px">Five Order Sections</h2><div class="grid5">
 @foreach($cards as $sectionKey=>$c)
 <a class="metric" href="{{ \Illuminate\Support\Facades\Route::has($c['route']) ? route($c['route']) : '#' }}"><b>{{ $c['label'] }} Orders</b><strong>{{ number_format($c['count']) }}</strong><small class="good">Paid ৳{{ number_format($c['paid'],2) }}</small><small class="warn">Pending ৳{{ number_format($c['pending'],2) }}</small>@unless($c['available'])<small>Table unavailable</small>@endunless</a>
 @endforeach
 </div></section>
 <section class="panel"><h2 style="font-size:17px;margin:0 0 13px">Payment Summary</h2><div class="grid4">
  <div class="metric"><b>Total Orders</b><strong>{{ number_format($totalOrders) }}</strong></div>
  <div class="metric"><b>Total Received (Paid Only)</b><strong class="good">৳{{ number_format($received,2) }}</strong></div>
  <div class="metric"><b>Pending (Not Received)</b><strong class="warn">৳{{ number_format($pending,2) }}</strong></div>
  <div class="metric"><b>Recent Payment Sources</b><strong>{{ number_format($sources['ssl']) }} SSL</strong><small>{{ number_format($sources['referral']) }} Referral / {{ number_format($sources['other']) }} Other</small><small>Only the recent rows below; not all-time totals</small></div>
  <div class="metric"><b>SSLCommerz Income</b><strong class="good">৳{{ number_format($sslIncome,2) }}</strong><small>Paid orders only · matching filters</small></div>
  <div class="metric"><b>Referral Income</b><strong class="good">৳{{ number_format($referralIncome,2) }}</strong><small>Paid Referral Balance orders only</small></div>
  <div class="metric"><b>Total Income</b><strong class="good">৳{{ number_format($received,2) }}</strong><small>All Paid orders · matching filters</small>@if($unclassifiedIncome>0.005)<small>Includes ৳{{ number_format($unclassifiedIncome,2) }} with unclassified payment source</small>@endif</div>
 </div></section>
 <section class="panel"><h2 style="font-size:17px;margin:0 0 13px">Recent Orders <span class="muted">(latest 15, matching filters)</span></h2><div class="table-wrap"><table><thead><tr><th>Section</th><th>Order ID</th><th>Student</th><th>Amount</th><th>Payment Source</th><th>Status</th><th>Order Date</th><th>Action</th></tr></thead><tbody>
 @forelse($recent as $r)
 <tr><td><span class="pill">{{ $r->label }}</span></td><td>{{ $r->ref }}</td><td>{{ $r->student_name ?? ('Student #'.$r->student_id) }}</td><td>৳{{ number_format((float)$r->amount,2) }}</td><td>{{ $r->method==='ssl'?'SSLCommerz':($r->method==='referral'?'Referral Balance':'Other / Unknown') }}</td><td><span class="pill {{ $r->is_paid?'paid':(strtolower($r->status)==='pending'?'pending':'') }}">{{ $r->is_paid ? 'Paid' : 'Pending' }}</span></td><td>{{ $r->created_at }}</td><td class="actions"><a href="{{ \Illuminate\Support\Facades\Route::has($r->route) ? route($r->route) : '#' }}">View Section</a></td></tr>
 @empty<tr><td colspan="8">No matching orders found.</td></tr>@endforelse
 </tbody></table></div></section>
</div>
@endsection
