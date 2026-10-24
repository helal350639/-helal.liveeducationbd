@extends('dashboard')
@section('title','Exam Package Management')
@section('content')
<style>
.ex183{padding:22px;color:#16396c}.ex183 h1{font-size:30px;font-weight:750;margin:0 0 18px}.ex183 .panel{background:white;border:1px solid #d8e4f5;border-radius:12px;padding:18px}.ex183 .bar{display:flex;justify-content:space-between;align-items:center;margin-bottom:15px}.ex183 .btn{display:inline-block;padding:9px 14px;border-radius:6px;text-decoration:none;border:0;cursor:pointer;font-weight:600}.ex183 .primary{background:#057fb6;color:white}.ex183 .edit{background:#f59e0b;color:white}.ex183 .delete{background:#e74c3c;color:white}.ex183 table{width:100%;border-collapse:collapse}.ex183 th,.ex183 td{border:1px solid #e4ebf3;padding:12px;text-align:left}.ex183 th{background:#f0f5fa}.ex183 .hint{font-size:12px;color:#64748b}.ex183 .message{padding:12px;background:#ecfdf5;border:1px solid #a7f3d0;margin-bottom:12px;border-radius:5px}
</style>
<div class="ex183">
<h1>Exam Section — Package Management</h1>
<div class="panel">
<div class="bar"><strong>Package List</strong><a href="{{ route('admin.exam-section.packages.create') }}" class="btn primary">＋ Create Package</a></div>
@if(session('success'))<div class="message">{{ session('success') }}</div>@endif
<div style="overflow-x:auto"><table><thead><tr><th>ID</th><th>Package Name</th><th>Selected Category/Sub-category</th><th>Duration</th><th>Main Price</th><th>Discount</th><th>Final Price</th><th>Status</th><th>Actions</th></tr></thead><tbody>
@forelse($packages as $p)
<tr><td>{{ $p->id }}</td><td><strong>{{ $p->name }}</strong><div class="hint">{{ \Illuminate\Support\Str::limit($p->description,60) }}</div></td><td>{{ $counts[$p->id] ?? 0 }} selected</td><td>{{ $p->duration }} {{ $p->duration_unit }}</td><td>৳{{ number_format($p->main_price,2) }}</td><td>৳{{ number_format($p->discount_amount,2) }}</td><td><strong>৳{{ number_format($p->final_price,2) }}</strong></td><td>{{ $p->status }}</td><td style="white-space:nowrap"><a class="btn edit" href="{{ route('admin.exam-section.packages.edit',$p->id) }}">✎ Edit</a> <form method="POST" action="{{ route('admin.exam-section.packages.delete',$p->id) }}" style="display:inline" onsubmit="return confirm('Delete this package?')">@csrf @method('DELETE')<button class="btn delete" type="submit">Delete</button></form></td></tr>
@empty<tr><td colspan="9" style="text-align:center">No exam packages yet. Click Create Package.</td></tr>@endforelse
</tbody></table></div><div style="margin-top:14px">{{ $packages->links() }}</div>
<p class="hint">Package configuration is saved independently. Student purchase, payment gateway, and exam access enforcement will be integrated separately.</p>
</div></div>
@endsection
