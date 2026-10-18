@extends('dashboard')
@section('title', 'Student Activity History')
@section('content')
<style>
.lebd-history{background:#f3f7fd;margin:-12px -15px;padding:18px;min-height:100vh;font:13px Arial,'Noto Sans Bengali',sans-serif;color:#13345d}
.lebd-history *{box-sizing:border-box}
.lebd-history .ah-back{color:#1376ee;text-decoration:none;font-weight:800}
.lebd-history h1{font-size:23px;font-weight:850;margin:15px 0 5px;color:#12355c}
.lebd-history .ah-muted{font-size:12px;color:#7c90ad;line-height:1.7}
.lebd-history .ah-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;margin:15px 0}
.lebd-history .ah-stat,.lebd-history .ah-card{background:#fff;border:1px solid #dfe9f7;border-radius:12px;padding:15px;min-width:0;overflow:hidden}
.lebd-history .ah-stat span{font-size:11px;color:#728ba8}.lebd-history .ah-stat b{font-size:21px;color:#166ed7;display:block;margin-top:8px}
.lebd-history .ah-card{margin:13px 0}
.lebd-history .ah-card h2{font-size:16px;margin:0 0 12px;font-weight:800}
.lebd-history .ah-scroll{overflow-x:auto;width:100%}
.lebd-history table{width:100%;min-width:700px;border-collapse:collapse;font-size:12px}
.lebd-history th{background:#f1f6fd;color:#526d94;text-align:left}
.lebd-history td,.lebd-history th{padding:10px 9px;border-bottom:1px solid #e7edf8;vertical-align:top}
.lebd-history a{color:#0e73e8}.lebd-history input{border:1px solid #d3e4f8;border-radius:8px;padding:9px;margin-right:7px}
.lebd-history button{background:#1677ee;color:#fff;border:0;border-radius:8px;padding:10px 15px;cursor:pointer}
.lebd-history .ah-pager{display:flex;flex-wrap:wrap;justify-content:space-between;gap:12px;align-items:center;margin-top:12px}
.lebd-history .ah-pager a{border:1px solid #cee0fa;border-radius:7px;padding:7px 10px}
@media(max-width:700px){.lebd-history .ah-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.lebd-history{margin:0;padding:10px}}
</style>
<div class="lebd-history">
 <a class="ah-back" href="{{ route('admin.recent.activity') }}"><i class="fa fa-arrow-left"></i> Recent Activity Dashboard</a>
 <h1>{{ $student->name }} — Student Activity History</h1>
 <div class="ah-muted">Student ID: {{ $student->reg_id ?: '#'.$student->id }} · Tracking চালুর পরের দিনভিত্তিক সব Page Visit ও Button Click এখানে থাকবে।</div>
 <div class="ah-grid">
  <div class="ah-stat"><span>Total Active Time</span><b>{{ gmdate('H:i:s',(int)($totals->active ?? 0)) }}</b></div>
  <div class="ah-stat"><span>Page Views</span><b>{{ number_format((int)($totals->pages ?? 0)) }}</b></div>
  <div class="ah-stat"><span>Button Clicks</span><b>{{ number_format((int)($totals->clicks ?? 0)) }}</b></div>
  <div class="ah-stat"><span>Last Visit</span><b style="font-size:14px">{{ $totals->last_seen ?: '—' }}</b></div>
 </div>
 <section class="ah-card"><h2>Daily Activity Summary</h2><div class="ah-scroll"><table><thead><tr><th>Date</th><th>Last Seen</th><th>Active Time</th><th>Page Visits</th><th>Button Clicks</th><th>Details</th></tr></thead><tbody>
  @forelse($days as $d)<tr><td>{{ $d->activity_date }}</td><td>{{ $d->last_seen_at }}</td><td>{{ gmdate('H:i:s',(int)$d->active_seconds) }}</td><td>{{ $d->page_views }}</td><td>{{ $d->click_count }}</td><td><a href="{{ route('admin.recent.activity.student',['studentId'=>$student->id,'date'=>$d->activity_date]) }}">View Day</a></td></tr>
  @empty<tr><td colspan="6">Tracking data not available yet.</td></tr>@endforelse
 </tbody></table></div><div class="ah-pager"><span>Days: {{ $days->total() }} · Page {{ $days->currentPage() }}</span><div>@if($days->previousPageUrl())<a href="{{ $days->previousPageUrl() }}">Previous</a>@endif @if($days->nextPageUrl())<a href="{{ $days->nextPageUrl() }}">See More →</a>@endif</div></div></section>
 <section class="ah-card"><h2>Full Page & Button Click History</h2><form method="GET"><label>Date <input type="date" name="date" value="{{ $date }}"></label><button type="submit">Filter</button> <a href="{{ route('admin.recent.activity.student',['studentId'=>$student->id]) }}">Clear</a></form>
 <div class="ah-scroll"><table><thead><tr><th>Date & Time</th><th>Activity Type</th><th>Current Page</th><th>Button / Link</th><th>Target Path</th><th>Device</th><th>Course ID</th><th>Batch ID</th></tr></thead><tbody>
 @forelse($events as $ev)<tr><td>{{ $ev->occurred_at }}</td><td>{{ $ev->event_type==='click'?'Click':'Page Visit' }}</td><td>{{ $ev->path }}</td><td>{{ $ev->target_label ?: '—' }}</td><td>{{ $ev->target_path ?: '—' }}</td><td>{{ $ev->device_type ?: 'Unknown' }}</td><td>{{ $ev->course_id ?: '—' }}</td><td>{{ $ev->batch_id ?: '—' }}</td></tr>
 @empty<tr><td colspan="8">No recorded events for this date.</td></tr>@endforelse
 </tbody></table></div>
 <div class="ah-pager"><span>Events: {{ $events->total() }} · Page {{ $events->currentPage() }}</span><div>@if($events->previousPageUrl())<a href="{{ $events->previousPageUrl() }}">← Previous</a>@endif @if($events->nextPageUrl())<a href="{{ $events->nextPageUrl() }}">See More →</a>@endif</div></div>
 </section>
 <p class="ah-muted">Active Time আনুমানিক; Browser বন্ধ/Background থাকলে নির্ভুল উপস্থিতি নির্ণয় করা যায় না। কোনো শিক্ষার্থীর Password, টাইপ করা লেখা বা MCQ Answer সংরক্ষণ করা হয় না।</p>
</div>
@endsection
