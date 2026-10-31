@extends('dashboard')
@section('title','Exam Section Order Details')
@section('content')
@include('admin.lebd-orders._style')
<div class="lebd-orders-page lebd-orders-compact-page">
 <div class="lebd-orders-top"><div><h1>Exam Section Order Details</h1><small>Dashboard / Orders / Exam Section Order</small></div></div>
 @include('admin.lebd-orders._navigation',['section'=>'exam'])
 <section class="lebd-orders-panel">
 <p><a class="lebd-orders-action" href="{{ route('admin.lebd-orders.exam') }}">← Back to Exam Orders</a></p>
 <h2 class="lebd-orders-heading">Order #{{ $row->id }}</h2>
 <div class="lebd-orders-table-wrap"><table class="lebd-orders-table"><tbody>
 @foreach(['Order ID'=>$row->transaction_id,'Student'=>$row->student_name ?: ('Student #'.$row->student_id),'Email'=>$row->student_email,'Mobile'=>$row->student_contact,'Package'=>$row->package_name ?: ('Package #'.$row->package_id),'Amount'=>'৳'.number_format((float)$row->amount,2),'Source'=>$row->method==='wallet'?'Referral Balance':'SSLCommerz','Status'=>ucfirst((string)$row->status),'Created'=>$row->created_at,'Paid At'=>$row->paid_at ?? '—','Expiry'=>$access->ends_at ?? '—'] as $label=>$value)
 <tr><th style="width:220px">{{ $label }}</th><td>{{ $value ?: '—' }}</td></tr>
 @endforeach
 </tbody></table></div>
 </section>
</div>
@endsection
