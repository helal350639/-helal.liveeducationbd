@php
  $lebdLearningItems = \App\Services\StudentLearningProgress::build(Auth::guard('student')->user());
@endphp
<section class="container mt-4 lebd-section-panel lebd-progress-panel" id="lebd-real-learning-progress">
  <div class="section-title lebd-progress-header">
    <h3>📊 আমার শেখার অগ্রগতি</h3>
    <button type="button" class="lebd-progress-all" id="lebd-progress-more" hidden aria-expanded="false">সব দেখুন ↓</button>
  </div>
  <p class="lebd-progress-caption">আপনার পরীক্ষায় ফলাফলের উপর আপনার প্রগ্রেস দেখুন।</p>
  @if(count($lebdLearningItems))
  <div class="progress-grid lebd-progress-real-grid" id="lebd-progress-real-grid">
    @foreach($lebdLearningItems as $scope)
    <article class="lebd-progress-entry" data-index="{{ $loop->index }}">
      <button type="button" class="lebd-progress-card-button" aria-expanded="false" aria-controls="lebd-progress-detail-{{ $loop->index }}">
        <span class="lebd-progress-pct" style="--pct:{{ $scope['percent'] }}">{{ $scope['percent'] }}%</span>
        <span class="lebd-progress-card-info"><strong>{{ $scope['name'] }}</strong><small>{{ $scope['completed'] }} / {{ $scope['total'] }} পরীক্ষা জমা</small></span>
        <span class="lebd-progress-chevron" aria-hidden="true">⌄</span>
      </button>
      <div class="lebd-progress-details" id="lebd-progress-detail-{{ $loop->index }}" hidden>
        <p class="lebd-progress-note">অগ্রগতি = জমা দেওয়া পরীক্ষা ÷ মোট প্রকাশিত পরীক্ষা। নম্বরের ভিত্তিতে Pass/Fail কেবল যাচাইকৃত পাসের নিয়ম পাওয়া গেলে নির্ধারিত হবে।</p>
        <div class="lebd-progress-table-scroll"><table>
          <thead><tr><th>পরীক্ষা</th><th>তারিখ</th><th>নম্বর</th><th>ফলাফল</th></tr></thead>
          <tbody>
          @forelse($scope['exams'] as $exam)
          <tr><td>{{ $exam['title'] }}</td><td>{{ $exam['date'] }}</td>
          <td>{{ $exam['submitted'] ? ($exam['score'] ?? '—') : '—' }}</td>
          <td>{{ $exam['submitted'] ? 'পাস/ফেল নীতি অনির্ধারিত' : 'পরীক্ষা বাকি' }}</td></tr>
          @empty<tr><td colspan="4">এই বিভাগে প্রকাশিত পরীক্ষা পাওয়া যায়নি।</td></tr>@endforelse
          </tbody>
        </table></div>
        <div class="lebd-progress-advice"><strong>উন্নতির পরামর্শ</strong><p>{{ $scope['advice'] }}</p></div>
      </div>
    </article>
    @endforeach
  </div>
  @else
  <p class="lebd-progress-empty">এখন পর্যন্ত আপনে কোন এক্সাম কোর্সে অংশগ্রহন করেন নাই। অংশগ্রহন করলে আপনার কোর্স ভিত্তিক Progress দেখাবে।</p>
  @endif
</section>
<style>
#lebd-real-learning-progress .lebd-progress-header{display:flex!important;align-items:center!important;gap:10px}
#lebd-real-learning-progress .lebd-progress-all{border:0;background:#e8f2ff;color:#1269c3;font-size:12px;border-radius:7px;padding:6px 9px;cursor:pointer}
#lebd-real-learning-progress .lebd-progress-caption{font-size:12px;color:#637b9c;margin-bottom:12px}
#lebd-real-learning-progress .lebd-progress-real-grid{display:grid!important;grid-template-columns:repeat(3,minmax(0,1fr))!important;gap:11px!important}
#lebd-real-learning-progress .lebd-progress-entry{border:1px solid #dfebfa;border-radius:13px;background:white;min-width:0;overflow:hidden;box-shadow:0 2px 9px rgba(22,54,93,.045)}
#lebd-real-learning-progress .lebd-progress-card-button{display:flex;align-items:center;gap:9px;width:100%;min-height:70px;padding:10px;text-align:left;border:0;background:transparent;cursor:pointer;color:#133b68}
#lebd-real-learning-progress .lebd-progress-pct{--pct:0;width:41px;height:41px;flex:0 0 41px;border-radius:50%;display:grid;place-items:center;font-size:11px;font-weight:800;background:conic-gradient(#0875e1 calc(var(--pct) * 1%),#e5effb 0);border:4px solid #eff5fc;color:#103c79}
#lebd-real-learning-progress .lebd-progress-card-info{flex:1;min-width:0}
#lebd-real-learning-progress .lebd-progress-card-info strong{display:block;font-size:12px;line-height:1.4;overflow-wrap:anywhere}
#lebd-real-learning-progress .lebd-progress-card-info small{font-size:10px;color:#71849d}
#lebd-real-learning-progress .lebd-progress-chevron{font-size:20px;color:#5180ad}
#lebd-real-learning-progress .lebd-progress-details{padding:9px;border-top:1px solid #edf1f7}
#lebd-real-learning-progress .lebd-progress-note{font-size:10px;color:#73829a;line-height:1.5}
#lebd-real-learning-progress .lebd-progress-table-scroll{overflow-x:auto}
#lebd-real-learning-progress table{width:100%;border-collapse:collapse;font-size:11px}
#lebd-real-learning-progress th,#lebd-real-learning-progress td{border-bottom:1px solid #edf1f7;padding:7px 5px;text-align:left}
#lebd-real-learning-progress th{background:#f2f7fe;color:#154376}
#lebd-real-learning-progress .lebd-progress-advice{background:#eef6ff;border-radius:9px;padding:9px;margin-top:9px;font-size:11px;color:#235280}
#lebd-real-learning-progress .lebd-progress-advice p{margin:3px 0 0;line-height:1.5}
#lebd-real-learning-progress .lebd-progress-empty{padding:16px;font-size:12px;color:#6a7f9d}
#lebd-real-learning-progress .lebd-progress-entry[data-index]:nth-child(n+10){display:none}
#lebd-real-learning-progress.lebd-progress-expanded .lebd-progress-entry[data-index]{display:block}
@media(max-width:768px){#lebd-real-learning-progress .lebd-progress-real-grid{grid-template-columns:repeat(2,minmax(0,1fr))!important;gap:8px!important}#lebd-real-learning-progress .lebd-progress-card-button{padding:7px;gap:5px;min-height:62px}#lebd-real-learning-progress .lebd-progress-pct{width:33px;height:33px;flex-basis:33px;font-size:10px}#lebd-real-learning-progress .lebd-progress-card-info strong{font-size:10px}#lebd-real-learning-progress .lebd-progress-card-info small{font-size:9px}#lebd-real-learning-progress .lebd-progress-entry[data-index]:nth-child(n+9){display:none}#lebd-real-learning-progress.lebd-progress-expanded .lebd-progress-entry[data-index]{display:block}}
</style>
<script>
(function(){function boot(){const root=document.getElementById('lebd-real-learning-progress');if(!root)return;const cards=[...root.querySelectorAll('.lebd-progress-entry')];const more=root.querySelector('#lebd-progress-more');function layout(){const max=window.matchMedia('(max-width:768px)').matches?8:9;more.hidden=cards.length<=max;if(!root.classList.contains('lebd-progress-expanded'))more.setAttribute('aria-expanded','false')}if(more){more.addEventListener('click',function(){const isExpanded=root.classList.toggle('lebd-progress-expanded');more.textContent=isExpanded?'কম দেখুন ↑':'সব দেখুন ↓';more.setAttribute('aria-expanded',isExpanded?'true':'false')})}cards.forEach(function(card){const btn=card.querySelector('.lebd-progress-card-button');const detail=card.querySelector('.lebd-progress-details');btn.addEventListener('click',function(){detail.hidden=!detail.hidden;btn.setAttribute('aria-expanded',detail.hidden?'false':'true')})});layout();window.addEventListener('resize',layout)}if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',boot);else boot()})();
</script>
