@extends('student')
@section('title', 'My MCQ Orders')
@section('content')
<div class="container" style="max-width:1060px;margin:28px auto 60px;padding:0 14px">
  <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap">
    <h2 style="color:#103366;font-weight:800">আমার MCQ Study Order List</h2>
    <a href="{{ url('/student/dashboard') }}" style="color:#076ed5">← Student Dashboard</a>
  </div>
  @if(session('success'))<div role="status" style="padding:14px 17px;background:#e5f8ed;color:#09683d;border-radius:10px;margin-bottom:14px;font-weight:700">✓ {{ session('success') }}</div>@endif
  @if(session('error'))<div role="alert" style="padding:14px 17px;background:#fff0ef;color:#a12820;border-radius:10px;margin-bottom:14px">{{ session('error') }}</div>@endif
  <div style="background:white;border:1px solid #d7e5f6;border-radius:14px;overflow-x:auto">
    <table style="width:100%;border-collapse:collapse;min-width:620px;color:#15325a">
      <thead><tr style="background:#eff6ff;text-align:left"><th style="padding:13px">Order / Date</th><th>Package</th><th>Amount</th><th>Status</th><th>Access Until</th></tr></thead>
      <tbody>
      @forelse($orders as $order)
      <tr style="border-top:1px solid #e7eef8">
        <td style="padding:12px"><strong>#{{ $order->id }}</strong><div style="font-size:12px;color:#607a9b">{{ $order->created_at }}</div></td>
        <td>{{ $order->package_name }}</td>
        <td>৳{{ number_format((float)$order->amount,2) }}</td>
        <td><strong style="color:{{ $order->status === 'Complete' ? '#087e4e' : '#a35a0c' }}">{{ $order->status }}</strong></td>
        <td>{{ $order->status === 'Complete' && $order->ends_at ? \Carbon\Carbon::parse($order->ends_at)->format('d M Y') : '—' }}</td>
      </tr>
      @empty
      <tr><td colspan="5" style="padding:25px;text-align:center">এখনো কোনো MCQ Study Order নেই।</td></tr>
      @endforelse
      </tbody>
    </table>
  </div>
  <div style="margin-top:15px">{{ $orders->links() }}</div>
</div>
@endsection
