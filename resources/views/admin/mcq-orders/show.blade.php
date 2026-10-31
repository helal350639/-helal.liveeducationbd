@extends('homepage')
@section('title', 'MCQ Study Order Details')
@section('content')
@include('admin.lebd-orders._style')
<div class="lebd-orders-page lebd-orders-compact-page">
 <div class="lebd-orders-top">
  <div><h1>MCQ Study Order Details</h1><small>Dashboard / Orders / MCQ Study Order / #{{ $record->id }}</small></div>
  <a class="lebd-orders-action" href="{{ route('admin.lebd.mcq-orders.index') }}">← All MCQ Orders</a>
 </div>
 <section class="lebd-orders-panel">
  <h2 class="lebd-orders-heading">Order #{{ $record->id }}
   <span class="lebd-orders-status {{ $record->status === 'Complete' ? 'paid' : strtolower((string)$record->status) }}">{{ $record->status === 'Complete' ? 'Paid' : $record->status }}</span>
  </h2>
  <dl class="lebd-orders-summary">
   <div><dt>Transaction ID</dt><dd>{{ $record->transaction_id }}</dd></div>
   <div><dt>Student</dt><dd>{{ $record->student_name ?: '—' }} (#{{ $record->student_id }})</dd></div>
   <div><dt>Email</dt><dd>{{ $record->student_email ?: '—' }}</dd></div>
   <div><dt>Contact</dt><dd>{{ $record->student_phone ?: '—' }}</dd></div>
   <div><dt>Package</dt><dd>{{ $record->package_name ?: ('#'.$record->package_id) }}</dd></div>
   <div><dt>Amount</dt><dd>৳{{ number_format((float)$record->amount,2) }} {{ $record->currency }}</dd></div>
   <div><dt>Payment Source</dt><dd>{{ str_starts_with((string)$record->transaction_id,'MCQR') ? 'Referral Balance' : (str_starts_with((string)$record->transaction_id,'MCQS') ? 'SSLCommerz' : 'Other') }}</dd></div>
   <div><dt>Payment Status</dt><dd>{{ $record->status === 'Complete' ? 'Paid' : $record->status }}</dd></div>
   <div><dt>Paid At</dt><dd>{{ $record->paid_at ?: '—' }}</dd></div>
   <div><dt>Order Date</dt><dd>{{ $record->created_at }}</dd></div>
   <div><dt>Duration</dt><dd>{{ (int)$record->duration_value }} {{ $record->duration_unit }}</dd></div>
   <div><dt>Current Subscription Expiry</dt><dd>{{ $record->status === 'Complete' ? ($expiresAt ?: '—') : '—' }}</dd></div>
  </dl>
  <p class="lebd-orders-muted">Expiry reflects the current subscription and may include subsequent renewals.</p>
 </section>
</div>
@endsection
