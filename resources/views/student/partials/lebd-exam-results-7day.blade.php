@php
    // NOTE: Data sources must be mapped to verified student-scoped records in config/lebd_seven_day.php.
    $lebdSevenDayFeed = \App\Services\StudentDashboardSevenDayFeed::get($user);
    $lebdUpcomingSevenDays = $lebdSevenDayFeed['upcoming'];
    $lebdResultsSevenDays = $lebdSevenDayFeed['results'];
    $lebdScheduledResults = $lebdSevenDayFeed['scheduled_results'] ?? [];
    $lebdSubmittedResults = $lebdSevenDayFeed['submitted_results'] ?? [];
@endphp
<section class="container mt-4 lebd-section-panel lebd-results-panel" id="lebd-seven-day-feed">
    <div class="lebd-section-head">
        <span class="lebd-head-icon"><i class="fa fa-chart-line"></i></span>
        <div class="lebd-head-text"><h3>পরীক্ষা ও ফলাফল</h3><p>আগামী ৭ দিনের পরীক্ষা, সাবমিট করা পরীক্ষার অপেক্ষমাণ ফলাফল এবং প্রকাশিত ফলাফল</p></div>
    </div>
    <div class="exam-result-grid">
        <div class="dashboard-box">
            <div class="box-title"><h3>📅 আসন্ন পরীক্ষা</h3><small>আগামী ৭ দিন</small></div>
            <div class="lebd-feed-list" id="lebd-upcoming-list" data-initial-visible="3">
                @forelse($lebdUpcomingSevenDays as $exam)
                    <div class="exam-item lebd-feed-item" @if($loop->index >= 3) hidden @endif>
                        <div class="date-box"><strong>{{ \Carbon\Carbon::createFromTimestamp($exam['sort'],'Asia/Dhaka')->format('d') }}</strong><span>{{ \Carbon\Carbon::createFromTimestamp($exam['sort'],'Asia/Dhaka')->locale('bn')->translatedFormat('M') }}</span></div>
                        <div class="exam-info"><h4>{{ $exam['title'] }}</h4><p>◉ {{ $exam['time'] }}</p>@if($exam['subtitle'])<small>{{ $exam['subtitle'] }}</small>@endif</div>
                    </div>
                @empty
                    <p class="lebd-feed-empty">আগামী ৭ দিনে কোনো নির্ধারিত পরীক্ষা পাওয়া যায়নি।</p>
                @endforelse
            </div>
            @if(count($lebdUpcomingSevenDays) > 3)<button type="button" class="lebd-feed-toggle" data-feed-target="lebd-upcoming-list" aria-expanded="false">আরো দেখুন ({{ count($lebdUpcomingSevenDays)-3 }}) ↓</button>@endif
        </div>
        <div class="dashboard-box">
            <div class="box-title"><h3>🏆 আসন্ন ফলাফল</h3><small>জমা দেওয়া পরীক্ষাসমূহ</small></div>
            <div class="lebd-feed-list" id="lebd-results-list" data-initial-visible="4">
                {{-- Only exams this student actually attended and submitted are listed here.
                     The result publish date/time comes from that exam's Routine End Date & Time. --}}
                @forelse($lebdSubmittedResults as $attempt)
                    <div class="result-item lebd-feed-item" data-kind="submitted" @if($loop->index >= 4) hidden @endif>
                        <h4>{{ $attempt['title'] }}</h4>
                        @if($attempt['subtitle'])<p>{{ $attempt['subtitle'] }}@if(!empty($attempt['subcategory'])) — {{ $attempt['subcategory'] }}@endif</p>@endif
                        @if(!empty($attempt['result_visible']))
                            <p class="lebd-feed-published">✅ ফলাফল প্রকাশ হয়েছে</p>
                            <a class="lebd-result-view-btn" href="{{ $attempt['result_url'] }}">ফলাফল দেখুন</a>
                        @else
                            <p class="lebd-feed-future">⏳ পরীক্ষা জমা হয়েছে — ফলাফল অপেক্ষমাণ</p>
                        @endif
                        <small>জমা: {{ $attempt['submitted'] }}</small>
                        <small style="display:block">ফলাফল প্রকাশের নির্ধারিত সময়: {{ $attempt['result_publish'] ?? (isset($attempt['exam_end_at']) && $attempt['exam_end_at'] ? \Carbon\Carbon::parse($attempt['exam_end_at'], 'Asia/Dhaka')->locale('bn')->translatedFormat('d F Y, h:i A') : 'Routine-এ Exam End Date & Time সেট করা নেই') }}</small>
                    </div>
                @empty
                    <p class="lebd-feed-empty">আপনি এখনো কোনো পরীক্ষা অংশগ্রহণ করে Submit করেননি।</p>
                @endforelse
            </div>
            @if(count($lebdSubmittedResults) > 4)
                <button type="button" class="lebd-feed-toggle" data-feed-target="lebd-results-list" aria-expanded="false">আরো দেখুন ({{ count($lebdSubmittedResults) - 4 }}) ↓</button>
            @endif
        </div>
    </div>
</section>
<style>
#lebd-seven-day-feed .lebd-feed-item[hidden]{display:none!important}
#lebd-seven-day-feed .dashboard-box{height:auto;align-self:start}
#lebd-seven-day-feed .lebd-feed-list{min-width:0}
#lebd-seven-day-feed .lebd-feed-empty{padding:15px 0;color:#71829c;font-size:12px}
#lebd-seven-day-feed .lebd-feed-toggle{display:block;width:100%;border:1px solid #d9e9fb;background:#f4f9ff;color:#095eae;border-radius:9px;padding:9px;margin-top:11px;cursor:pointer;font-size:12px;font-weight:700}
#lebd-seven-day-feed .exam-item,#lebd-seven-day-feed .result-item{overflow-wrap:anywhere}
#lebd-seven-day-feed .date-box{flex-shrink:0}
#lebd-seven-day-feed .lebd-feed-future{color:#ae7105;font-weight:700}
#lebd-seven-day-feed .lebd-feed-published{color:#07834f;font-weight:700}
#lebd-seven-day-feed .lebd-result-view-btn{display:inline-block;margin:5px 0 8px;padding:7px 13px;border-radius:7px;background:#0878cf;color:#fff!important;text-decoration:none;font-size:12px;font-weight:700}
#lebd-seven-day-feed .lebd-result-view-btn:hover{background:#075fa5;color:#fff!important}
</style>
<script>
(function(){
  var root=document.getElementById('lebd-seven-day-feed');if(!root)return;
  root.querySelectorAll('.lebd-feed-list').forEach(function(list){
    var initialVisible=parseInt(list.getAttribute('data-initial-visible')||'3',10);
    list.querySelectorAll('.lebd-feed-item').forEach(function(item,index){item.hidden=index>=initialVisible;});
  });
  root.querySelectorAll('[data-feed-target]').forEach(function(button){
    button.addEventListener('click',function(){
      var list=document.getElementById(button.getAttribute('data-feed-target'));if(!list)return;
      var expanded=button.getAttribute('aria-expanded')==='true';
      var initialVisible=parseInt(list.getAttribute('data-initial-visible')||'3',10);
      list.querySelectorAll('.lebd-feed-item').forEach(function(item,index){item.hidden=expanded && index>=initialVisible;});
      button.setAttribute('aria-expanded',expanded?'false':'true');
      button.textContent=expanded?'আরো দেখুন ↓':'কম দেখুন ↑';
    });
  });
})();
</script>
