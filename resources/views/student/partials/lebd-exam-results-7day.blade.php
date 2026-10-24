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
            <div class="lebd-feed-list" id="lebd-upcoming-list">
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
            <div class="lebd-feed-list" id="lebd-results-list">
                @foreach($lebdSubmittedResults as $attempt)
                    <div class="result-item lebd-feed-item" data-kind="submitted">
                        <h4>{{ $attempt['title'] }}</h4>
                        @if($attempt['subtitle'])<p>{{ $attempt['subtitle'] }}</p>@endif
                        <p class="lebd-feed-future">⏳ পরীক্ষা জমা হয়েছে — ফলাফল অপেক্ষমাণ</p>
                        <small>জমা: {{ $attempt['submitted'] }}</small>
                        @if($attempt['exam_end'])
                            <small style="display:block">পরীক্ষা শেষের সময়: {{ $attempt['exam_end'] }}</small>
                        @endif
                        <small style="display:block">ফলাফল প্রকাশের নির্দিষ্ট সময় এখনো যাচাই হয়নি</small>
                    </div>
                @endforeach
                @forelse($lebdResultsSevenDays as $result)
                    <div class="result-item lebd-feed-item" data-kind="published">
                        <h4>{{ $result['title'] }}</h4>
                        <p>@if($result['score'] !== null)নম্বর: {{ $result['score'] }}@if($result['total'] !== null)/{{ $result['total'] }}@endif @endif @if($result['rank'] !== null) | র‍্যাংক: {{ $result['rank'] }} @endif</p>
                        <small>✅ প্রকাশিত: {{ $result['published'] }}</small>
                    </div>
                @empty
                @endforelse
                @foreach($lebdScheduledResults as $result)
                    <div class="result-item lebd-feed-item" data-kind="scheduled">
                        <h4>{{ $result['title'] }}</h4>
                        <p class="lebd-feed-future">⏳ ফলাফল প্রকাশের অপেক্ষায়</p>
                        <small>নির্ধারিত প্রকাশ: {{ $result['scheduled'] }}</small>
                    </div>
                @endforeach
                @if(empty($lebdSubmittedResults) && empty($lebdResultsSevenDays) && empty($lebdScheduledResults))
                    <p class="lebd-feed-empty">আপনার জমা দেওয়া কোনো পরীক্ষা বা প্রকাশিত ফলাফল পাওয়া যায়নি।</p>
                @endif
            </div>
            @if(count($lebdSubmittedResults) + count($lebdResultsSevenDays) + count($lebdScheduledResults) > 3)
                <button type="button" class="lebd-feed-toggle" data-feed-target="lebd-results-list" aria-expanded="false">আরো দেখুন ({{ count($lebdSubmittedResults) + count($lebdResultsSevenDays) + count($lebdScheduledResults) - 3 }}) ↓</button>
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
</style>
<script>
(function(){
  var root=document.getElementById('lebd-seven-day-feed');if(!root)return;
  root.querySelectorAll('.lebd-feed-list').forEach(function(list){
    list.querySelectorAll('.lebd-feed-item').forEach(function(item,index){item.hidden=index>=3;});
  });
  root.querySelectorAll('[data-feed-target]').forEach(function(button){
    button.addEventListener('click',function(){
      var list=document.getElementById(button.getAttribute('data-feed-target'));if(!list)return;
      var expanded=button.getAttribute('aria-expanded')==='true';
      list.querySelectorAll('.lebd-feed-item').forEach(function(item,index){item.hidden=expanded && index>=3;});
      button.setAttribute('aria-expanded',expanded?'false':'true');
      button.textContent=expanded?'আরো দেখুন ↓':'কম দেখুন ↑';
    });
  });
})();
</script>
