@extends('dashboard')
@section('title','Premium Video Complaints')
@section('content')
<link rel="stylesheet" href="{{ asset('web/css/lebd-video-complaints-admin-fix116.css') }}">
<div class="lebd-complaints-admin">
<h2>Premium Recorded Classes — Complaints</h2>
<p class="help-block">Department → Subject → Chapter → Video অনুযায়ী অভিযোগ যাচাই করুন। এটি Student-এর পাঠানো মতামত; যাচাই ছাড়া সত্য ধরে নেওয়া হবে না।</p>
@if(session('success'))<div class="alert alert-success">{{session('success')}}</div>@endif
<form method="GET" action="{{ route('admin.premium-video-complaints.index') }}" class="lebd-complaints-filters">
<label>Status <select name="status"><option value="">All</option>@foreach(['pending'=>'Pending','reviewed'=>'Reviewed','resolved'=>'Resolved'] as $key=>$name)<option value="{{$key}}" @selected(($filters['status']??'')===$key)>{{$name}}</option>@endforeach</select></label>
<label>Group <select name="group"><option value="">All</option><option value="department" @selected(($filters['group']??'')==='department')>Department</option><option value="non-department" @selected(($filters['group']??'')==='non-department')>Non-Department</option></select></label>
<label>Video ID <input name="video_id" type="number" min="1" value="{{$filters['video_id']??''}}" placeholder="Video ID"></label>
<button type="submit" class="btn btn-primary">Filter</button><a href="{{ route('admin.premium-video-complaints.index') }}" class="btn btn-default">Reset</a>
</form>
<div class="lebd-complaints-tablewrap"><table class="table table-bordered table-striped lebd-complaints-table">
<thead><tr><th>ID / Time</th><th>Video Location</th><th>Student ID</th><th>অভিযোগ</th><th>Verify / Action</th></tr></thead>
<tbody>
@forelse($complaints as $item)
<tr>
<td>#{{$item->id}}<br><small>{{$item->created_at?->format('d M Y, h:i A')}}</small></td>
<td><strong>{{ $item->premium_group === 'department' ? 'Department' : 'Non-Department' }}: {{ $item->premium_topic }}</strong>
<div class="lebd-complaint-breadcrumb">{{ $item->premium_topic }} → {{ $item->subject_title ?: 'Direct Videos' }} → {{ $item->chapter_title ?: 'No Chapter' }} → <b>{{ $item->video_title }}</b></div>
<small>Video ID: {{$item->video_id}}</small><br>
@if($item->video && \Illuminate\Support\Facades\Route::has('admin.premium-recordings.videos.view'))<a class="btn btn-info btn-xs" href="{{route('admin.premium-recordings.videos.view',['video'=>$item->video_id])}}" target="_blank" rel="noopener">View Video</a>@else<small>ভিডিও আর পাওয়া যাচ্ছে না</small>@endif
</td>
<td>{{$item->student_id}}</td>
<td><b>{{ ['unclear_video'=>'ভিডিও ক্লিয়ার না','poor_explanation'=>'শিক্ষক ভালো করে বুঝান না','other'=>'Others'][$item->reason]??$item->reason }}</b><p class="lebd-complaint-comment">{{$item->comment ?: 'অতিরিক্ত মন্তব্য নেই'}}</p></td>
<td>
<form method="POST" action="{{route('admin.premium-video-complaints.update',['complaint'=>$item->id])}}">@csrf @method('PATCH')
<select name="status" aria-label="Complaint Status"><option value="pending" @selected($item->status==='pending')>Pending</option><option value="reviewed" @selected($item->status==='reviewed')>Reviewed</option><option value="resolved" @selected($item->status==='resolved')>Resolved</option></select>
<textarea name="admin_note" rows="2" maxlength="2000" placeholder="যাচাইয়ের নোট">{{$item->admin_note}}</textarea>
<button type="submit" class="btn btn-success btn-xs">Save</button>
</form></td>
</tr>
@empty<tr><td colspan="5" class="text-center">এখনো কোনো অভিযোগ পাওয়া যায়নি।</td></tr>@endforelse
</tbody></table></div>
<div>{{$complaints->links()}}</div>
</div>
@endsection
