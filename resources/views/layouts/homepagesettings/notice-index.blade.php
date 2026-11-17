@extends('dashboard')
@section('title', 'Notice Management')
@section('content')
<style>
.lebd-notice-admin{padding:18px 18px 28px}.lebd-notice-admin .page-title{font-size:28px;font-weight:800;color:#111827;margin:4px 0 18px}.lebd-notice-admin .notice-toolbar{display:flex;justify-content:space-between;align-items:center;gap:12px;margin-bottom:16px}.lebd-notice-admin .notice-panel{background:#fff;border:1px solid #e4eaf4;border-radius:13px;box-shadow:0 8px 24px rgba(20,45,90,.08);overflow:hidden}.lebd-notice-admin .notice-panel-head{background:linear-gradient(90deg,#e8efff,#dce7fb);padding:14px 20px;font-size:19px;font-weight:800;color:#101d50}.lebd-notice-admin .table{margin:0}.lebd-notice-admin .table th{background:#f1f5fd;color:#243b66}.lebd-notice-admin .notice-thumb{width:58px;height:48px;object-fit:cover;border-radius:7px;border:1px solid #e2e8f0}.lebd-notice-admin .status-pill{display:inline-block;padding:5px 10px;border-radius:20px;font-size:11px;font-weight:800;background:#e6f8ef;color:#07834e}.lebd-notice-admin .status-off{background:#fff0e8;color:#bd4b1c}.lebd-notice-admin .empty{padding:35px;text-align:center;color:#718096}@media(max-width:767px){.lebd-notice-admin{padding:12px 8px}.lebd-notice-admin .notice-toolbar{align-items:stretch;flex-direction:column}.lebd-notice-admin .page-title{font-size:23px}}
</style>
<div class="lebd-notice-admin">
  <div class="notice-toolbar">
    <h1 class="page-title"><i class="fa fa-bullhorn" style="color:#1749bd"></i> Notice Management</h1>
    <a href="{{ route('notice.create') }}" class="btn btn-primary btn-lg"><i class="fa fa-plus"></i> Add Notice</a>
  </div>
  @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
  <div class="notice-panel">
    <div class="notice-panel-head"><i class="fa fa-list-alt"></i> All Notices</div>
    <div class="table-responsive">
      <table class="table table-hover table-bordered">
        <thead><tr><th width="60">SL</th><th>Notice</th><th>Type</th><th>Validity</th><th>Website</th><th>Status</th><th width="110">Action</th></tr></thead>
        <tbody>
        @forelse($notices as $index => $item)
          <tr>
            <td>{{ $notices->firstItem() + $index }}</td>
            <td><strong>{{ $item->title }}</strong>@if($item->image)<br><img class="notice-thumb" src="{{ asset($item->image) }}" alt="">@endif @if($item->url)<br><small><i class="fa fa-link"></i> URL attached</small>@endif</td>
            <td>{{ ['text'=>'Text','image'=>'Image Only','text_image'=>'Text + Image'][$item->notice_type ?? 'text'] ?? 'Text' }}</td>
            <td><small>{{ $item->start_at ? \Carbon\Carbon::parse($item->start_at)->format('d M Y, h:i A') : 'Immediately' }}<br>to {{ $item->end_at ? \Carbon\Carbon::parse($item->end_at)->format('d M Y, h:i A') : 'No end date' }}</small></td>
            <td><span class="status-pill {{ ($item->show_on_website ?? true) ? '' : 'status-off' }}">{{ ($item->show_on_website ?? true) ? 'Visible' : 'Hidden' }}</span></td>
            <td><span class="status-pill {{ strtolower($item->status) === 'published' ? '' : 'status-off' }}">{{ $item->status }}</span></td>
            <td><a href="{{ route('notice.edit', $item->id) }}" class="btn btn-sm btn-info"><i class="fa fa-edit"></i> Edit</a></td>
          </tr>
        @empty
          <tr><td colspan="7" class="empty"><i class="fa fa-bell-slash fa-2x"></i><br>No notice added yet.</td></tr>
        @endforelse
        </tbody>
      </table>
    </div>
    <div class="text-center" style="padding:12px">{{ $notices->links() }}</div>
  </div>
</div>
@endsection
