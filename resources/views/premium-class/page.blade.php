<link rel="stylesheet" href="{{ asset('web/css/lebd-premium-class-fix93.css') }}">
<link rel="stylesheet" href="{{ asset('web/css/lebd-premium-student-fix119.css') }}?v=119">
@php
$premiumLabels = [
  'civil' => 'Civil Department',
  'electrical' => 'Electrical Department',
  'mechanical' => 'Mechanical Department',
  'computer' => 'Computer Department',
  'electronic' => 'Electronic Department',
  'power' => 'Power Department',
  'rac' => 'RAC Department',
  'architecture' => 'Architecture Department',
  'textile' => 'Textile Department',
  'agriculture' => 'Agriculture Department',
  'bangla-grammar' => 'বাংলা ব্যাকরণ',
  'bangla-literature' => 'বাংলা সাহিত্য ও সংস্কৃতি',
  'bangladesh' => 'বাংলাদেশ বিষয়াবলি',
  'international' => 'আন্তর্জাতিক বিষয়াবলি',
  'english' => 'ইংরেজি',
  'math' => 'গণিত',
  'science' => 'বিজ্ঞান ও প্রযুক্তি',
  'others' => 'অন্যান্য'
];
$premiumName = $premiumLabels[$premiumTopic] ?? 'Class';
$premiumBase = ['premium_group' => $premiumGroup, 'premium_topic' => $premiumTopic];
@endphp
<main class="lebd-premium-page">
<div class="lebd-premium-shell">
  <a class="lebd-premium-back" href="{{ route('homepage') }}#lebd-programs"><i class="fa-solid fa-arrow-left"></i> হোমপেজে ফিরে যান</a>
  <div class="lebd-premium-heading">
    <span class="lebd-premium-eyebrow">LIVE EDUCATION BD • Premium Classes</span>
    <h1>{{ $premiumName }}</h1>
    <p>{{ $premiumGroup === 'department' ? 'ডিপার্টমেন্ট ক্লাস' : 'নন-ডিপার্টমেন্ট ক্লাস' }}</p>
  </div>
  @if($premiumView === 'choose')
    <h2 class="lebd-premium-subheading">কোন ধরনের ক্লাস দেখতে চান?</h2>
    <div class="lebd-premium-choice-grid">
      <a class="lebd-premium-choice" href="{{ route('homepage', $premiumBase + ['premium_view'=>'live']) }}">
        <i class="fa-solid fa-video"></i><strong>Live Class</strong><span>সরাসরি অনলাইন ক্লাস</span><b>লাইভ ক্লাস <i class="fa-solid fa-arrow-right"></i></b>
      </a>
      <a class="lebd-premium-choice" href="{{ route('homepage', $premiumBase + ['premium_view'=>'recorded']) }}">
        <i class="fa-solid fa-circle-play"></i><strong>Recorded Class</strong><span>রেকর্ড করা ক্লাসসমূহ</span><b>রেকর্ড ক্লাস <i class="fa-solid fa-arrow-right"></i></b>
      </a>
    </div>
  @elseif($premiumView === 'recorded')
    @php
      // Student view follows the Admin hierarchy: topic -> active subject -> active chapter -> published videos.
      // Do not display legacy/unassigned chapters or empty cards as extra subject rows.
      $sections = collect();
      $hasTables = \Illuminate\Support\Facades\Schema::hasTable('lebd_recorded_videos')
          && \Illuminate\Support\Facades\Schema::hasTable('lebd_recorded_chapters')
          && \Illuminate\Support\Facades\Schema::hasTable('lebd_recorded_subjects');
      if ($hasTables && \Illuminate\Support\Facades\Schema::hasColumn('lebd_recorded_videos', 'subject_id')
          && \Illuminate\Support\Facades\Schema::hasColumn('lebd_recorded_chapters', 'subject_id')) {
        $sectionsMap = [];
        $subjects = \App\Models\PremiumClass\LebdRecordedSubject::query()
          ->where('premium_group', $premiumGroup)
          ->where('premium_topic', $premiumTopic)
          ->where('is_active', 1)
          ->orderBy('sort_order')->orderBy('id')->get();
        foreach ($subjects as $subject) {
          $directVideos = \App\Models\PremiumClass\LebdRecordedVideo::query()
            ->where('subject_id', $subject->id)->whereNull('chapter_id')
            ->where('is_published', 1)->orderBy('sort_order')->orderBy('id')->get();
          if ($directVideos->isNotEmpty()) {
            $displayName = trim((string) $subject->title);
            $displayKey = 'name:' . \Illuminate\Support\Str::lower(preg_replace('/\s+/u', ' ', $displayName));
            if (!isset($sectionsMap[$displayKey])) {
              $sectionsMap[$displayKey] = ['name' => $displayName, 'type' => 'subject', 'videos' => collect(), 'key' => 'subject:'.$subject->id];
            }
            $sectionsMap[$displayKey]['videos'] = $sectionsMap[$displayKey]['videos']->concat($directVideos)->unique('id')->values();
          }
          $chapters = \App\Models\PremiumClass\LebdRecordedChapter::query()
            ->where('subject_id', $subject->id)->where('is_active', 1)
            ->orderBy('sort_order')->orderBy('id')->get();
          foreach ($chapters as $chapter) {
            $videos = \App\Models\PremiumClass\LebdRecordedVideo::query()
              ->where('chapter_id', $chapter->id)->where('is_published', 1)
              ->orderBy('sort_order')->orderBy('id')->get();
            if ($videos->isNotEmpty()) {
              $displayName = trim((string) $chapter->title);
              $displayKey = 'name:' . \Illuminate\Support\Str::lower(preg_replace('/\s+/u', ' ', $displayName));
              if (!isset($sectionsMap[$displayKey])) {
                $sectionsMap[$displayKey] = ['name' => $displayName, 'type' => 'chapter', 'videos' => collect(), 'key' => 'chapter:'.$chapter->id];
              }
              $sectionsMap[$displayKey]['videos'] = $sectionsMap[$displayKey]['videos']->concat($videos)->unique('id')->values();
            }
          }
        }
        $sections = collect(array_values($sectionsMap))->filter(function($section){
          return !empty($section['name']) && collect($section['videos'] ?? [])->isNotEmpty();
        })->values();
      }
      $packagesUrl = route('premium.recordings.packages',['premium_group'=>$premiumGroup,'premium_topic'=>$premiumTopic]);
    @endphp
    @if(session('lebd_complaint_success'))<div class="lebd-complaint-flash" role="status">{{ session('lebd_complaint_success') }}</div>@endif
    @if($errors->has('video_id') || $errors->has('reason') || $errors->has('comment'))<div class="lebd-complaint-flash lebd-complaint-error" role="alert">{{ $errors->first() }}</div>@endif
    <a class="lebd-premium-back lebd-premium-back-secondary" href="{{ route('homepage',$premiumBase + ['premium_view'=>'choose']) }}">← ক্লাস নির্বাচনে ফিরে যান</a>
    <div class="lebd-student-intro"><h2>{{ $premiumName }} — Recorded Classes</h2><a href="{{ $packagesUrl }}">প্রিমিয়াম প্যাকেজ দেখুন →</a></div>
    @forelse($sections as $section)
      @php $videos=collect($section['videos']); @endphp
      <section class="lebd-student-record-row" aria-label="{{ $section['name'] }}">
        <div class="lebd-student-record-label"><div class="lebd-student-record-heading"><i class="fa fa-folder-open" aria-hidden="true"></i><strong>{{ $section['name'] }}</strong></div><small>মোট {{ $videos->count() }}টি ভিডিও · প্রথম ভিডিও ফ্রি</small></div>
        <div class="lebd-student-record-right">
          <button type="button" class="lebd-rec-arrow" data-arrow="prev" aria-label="আগের ক্লাস">‹</button>
          <div class="lebd-student-record-track">
            @foreach($videos as $videoIndex=>$video)
              @php $free=$videoIndex===0; @endphp
              <div class="lebd-student-rec-card lebd-student-complaint-card">
                <a class="lebd-student-rec-open" href="{{ $free ? route('premium.recordings.watch',['video'=>$video->id]) : $packagesUrl }}">
                <div class="lebd-student-rec-thumb">
                  @if($video->thumbnail_url)<img src="{{ $video->thumbnail_url }}" alt="" loading="lazy">@endif
                  <span><i class="fa {{ $free ? 'fa-play-circle' : 'fa-lock' }}"></i></span>
                </div>
                <div class="lebd-student-rec-meta"><b>{{ $video->title }}</b><small>{{ $free ? 'ফ্রি ভিডিও দেখুন' : 'Premium • প্যাকেজ প্রয়োজন' }}</small></div>
                </a>
                <button type="button" class="lebd-student-complain-button" data-lebd-complain-video="{{ $video->id }}" data-lebd-complain-title="{{ $video->title }}" aria-label="{{ $video->title }} সম্পর্কে অভিযোগ করুন"><i class="fa fa-exclamation-circle" aria-hidden="true"></i> অভিযোগ</button>
              </div>
            @endforeach
          </div>
          <button type="button" class="lebd-rec-arrow" data-arrow="next" aria-label="পরের ক্লাস">›</button>
        </div>
      </section>
    @empty
      <div class="lebd-premium-empty"><h3>এখনো কোনো ক্লাস প্রকাশিত হয়নি</h3><p>Admin থেকে ভিডিও Published করলে এখানে দেখা যাবে।</p></div>
    @endforelse

    {{-- Dedicated complaint modal: no premium purchase is required; student authentication on submission. --}}
    <div class="lebd-complain-modal" id="lebdStudentVideoComplaint" hidden>
      <div class="lebd-complain-backdrop" data-lebd-close></div>
      <div class="lebd-complain-dialog" role="dialog" aria-modal="true" aria-labelledby="lebdComplainTitle" tabindex="-1">
        <div class="lebd-complain-dialog-heading"><h3 id="lebdComplainTitle">ভিডিও সম্পর্কে অভিযোগ</h3><button type="button" data-lebd-close aria-label="বন্ধ করুন">×</button></div>
        <p class="lebd-complain-selected" id="lebdComplainVideoName"></p>
        <form method="POST" action="{{ route('premium.recordings.complaints.store') }}" id="lebdComplaintForm">
          @csrf
          <input type="hidden" name="video_id" id="lebdComplaintVideoId">
          <label for="lebdComplaintKind">অভিযোগের ধরন <span>*</span></label>
          <select name="reason" id="lebdComplaintKind" required>
            <option value="">নির্বাচন করুন</option>
            <option value="unclear_video">ভিডিও ক্লিয়ার না</option>
            <option value="poor_explanation">শিক্ষক ভালো করে বুঝান না</option>
            <option value="other">Others</option>
          </select>
          <div id="lebdComplaintCommentWrap" hidden>
            <label for="lebdComplaintComment">আপনার অভিযোগ লিখুন <span>*</span></label>
            <textarea name="comment" id="lebdComplaintComment" rows="4" maxlength="2000" placeholder="সমস্যাটি বিস্তারিত লিখুন"></textarea>
          </div>
          <p class="lebd-complain-footnote">অভিযোগ জমা দিতে Student Login প্রয়োজন। ভিডিওটি Premium হলেও অভিযোগ করা যাবে।</p>
          <div class="lebd-complain-actions"><button type="button" data-lebd-close>বাতিল</button><button type="submit">অভিযোগ জমা দিন</button></div>
        </form>
      </div>
    </div>
    <link rel="stylesheet" href="{{ asset('web/css/lebd-video-complaints-fix116.css') }}">
    <script src="{{ asset('web/js/lebd-video-complaints-fix116.js') }}" defer></script>
    <script>document.addEventListener('DOMContentLoaded',function(){document.querySelectorAll('.lebd-student-record-right').forEach(function(r){var t=r.querySelector('.lebd-student-record-track');r.querySelectorAll('[data-arrow]').forEach(function(b){b.addEventListener('click',function(){t.scrollBy({left:(b.dataset.arrow==='next'?1:-1)*Math.max(240,t.clientWidth*.8),behavior:'smooth'});});});});});</script>
  @else
    <a class="lebd-premium-back" href="{{ route('homepage', $premiumBase + ['premium_view'=>'choose']) }}">← ক্লাস নির্বাচনে ফিরে যান</a>
    <h2 class="lebd-premium-subheading">{{ $premiumName }} — Live Class</h2>
    <div class="lebd-premium-empty"><i class="fa-solid fa-video"></i><h3>Live Class শীঘ্রই যুক্ত হবে</h3><p>এই অংশের নিয়ম পরবর্তী ধাপে নির্ধারণ করা হবে।</p></div>
  @endif
</div>
</main>
