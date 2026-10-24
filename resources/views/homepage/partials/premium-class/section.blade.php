<link rel="stylesheet" href="{{ asset('web/css/lebd-premium-class-fix93.css') }}">
<section class="lebd-program-section" id="lebd-programs" aria-label="লাইভ এবং রেকর্ড ক্লাস">
  <div class="lebd-program-wrap">
    <article class="lebd-program-panel lebd-program-panel-live">
      <div class="lebd-program-heading">
        <span class="lebd-program-pill"><i class="fa-solid fa-chalkboard-user"></i> লাইভ / রেকর্ড ক্লাস(ডিপার্টমেন্ট)</span>
        <h2>ডিপার্টমেন্ট ভিত্তিক অনলাইন ক্লাস</h2>
        <p>আপনার পছন্দের ডিপার্টমেন্ট ক্লাস বেছে নিন</p>
      </div>
      <div class="lebd-program-options" aria-label="ডিপার্টমেন্ট ক্লাস">
        <a class="lebd-program-option" href="{{ route('homepage', ['premium_group' => 'department', 'premium_topic' => 'civil', 'premium_view' => 'choose']) }}"><i class="fa-solid fa-building-columns"></i> Civil Department</a>
        <a class="lebd-program-option" href="{{ route('homepage', ['premium_group' => 'department', 'premium_topic' => 'electrical', 'premium_view' => 'choose']) }}"><i class="fa-solid fa-bolt"></i> Electrical Department</a>
        <a class="lebd-program-option" href="{{ route('homepage', ['premium_group' => 'department', 'premium_topic' => 'mechanical', 'premium_view' => 'choose']) }}"><i class="fa-solid fa-gears"></i> Mechanical Department</a>
        <a class="lebd-program-option" href="{{ route('homepage', ['premium_group' => 'department', 'premium_topic' => 'computer', 'premium_view' => 'choose']) }}"><i class="fa-solid fa-computer"></i> Computer Department</a>
        <a class="lebd-program-option" href="{{ route('homepage', ['premium_group' => 'department', 'premium_topic' => 'electronic', 'premium_view' => 'choose']) }}"><i class="fa-solid fa-microchip"></i> Electronic Department</a>
        <a class="lebd-program-option" href="{{ route('homepage', ['premium_group' => 'department', 'premium_topic' => 'power', 'premium_view' => 'choose']) }}"><i class="fa-solid fa-plug"></i> Power Department</a>
        <a class="lebd-program-option" href="{{ route('homepage', ['premium_group' => 'department', 'premium_topic' => 'rac', 'premium_view' => 'choose']) }}"><i class="fa-solid fa-snowflake"></i> RAC Department</a>
        <a class="lebd-program-option" href="{{ route('homepage', ['premium_group' => 'department', 'premium_topic' => 'architecture', 'premium_view' => 'choose']) }}"><i class="fa-solid fa-drafting-compass"></i> Architecture Department</a>
        <a class="lebd-program-option" href="{{ route('homepage', ['premium_group' => 'department', 'premium_topic' => 'textile', 'premium_view' => 'choose']) }}"><i class="fa-solid fa-shirt"></i> Textile Department</a>
        <a class="lebd-program-option" href="{{ route('homepage', ['premium_group' => 'department', 'premium_topic' => 'agriculture', 'premium_view' => 'choose']) }}"><i class="fa-solid fa-seedling"></i> Agriculture Department</a>
      </div>
      <p class="lebd-program-footnote"><i class="fa-solid fa-circle-info"></i> বিভাগ নির্বাচন করে Live / Recorded Class দেখুন</p>
    </article>
    <article class="lebd-program-panel lebd-program-panel-recorded">
      <div class="lebd-program-heading">
        <span class="lebd-program-pill"><i class="fa-solid fa-circle-play"></i> লাইভ / রেকর্ড ক্লাস(নন)</span>
        <h2>নন ডিপার্টমেন্ট ভিত্তিক লাইভ + রেকর্ড ক্লাস</h2>
        <p>সম্পূর্ণ নতুন কোর্স এক সাথে</p>
      </div>
      <div class="lebd-program-group">
        <h3><i class="fa-solid fa-circle-play"></i> লাইভ / রেকর্ড ক্লাস</h3>
        <div class="lebd-program-options" aria-label="নন ডিপার্টমেন্ট ক্লাস">
        <a class="lebd-program-option" href="{{ route('homepage', ['premium_group' => 'non-department', 'premium_topic' => 'bangla-grammar', 'premium_view' => 'choose']) }}"><i class="fa-solid fa-language"></i> বাংলা ব্যাকরণ</a>
        <a class="lebd-program-option" href="{{ route('homepage', ['premium_group' => 'non-department', 'premium_topic' => 'bangla-literature', 'premium_view' => 'choose']) }}"><i class="fa-solid fa-book-open"></i> বাংলা সাহিত্য ও সংস্কৃতি</a>
        <a class="lebd-program-option" href="{{ route('homepage', ['premium_group' => 'non-department', 'premium_topic' => 'bangladesh', 'premium_view' => 'choose']) }}"><i class="fa-solid fa-landmark"></i> বাংলাদেশ বিষয়াবলি</a>
        <a class="lebd-program-option" href="{{ route('homepage', ['premium_group' => 'non-department', 'premium_topic' => 'international', 'premium_view' => 'choose']) }}"><i class="fa-solid fa-earth-asia"></i> আন্তর্জাতিক বিষয়াবলি</a>
        <a class="lebd-program-option" href="{{ route('homepage', ['premium_group' => 'non-department', 'premium_topic' => 'english', 'premium_view' => 'choose']) }}"><i class="fa-solid fa-spell-check"></i> ইংরেজি</a>
        <a class="lebd-program-option" href="{{ route('homepage', ['premium_group' => 'non-department', 'premium_topic' => 'math', 'premium_view' => 'choose']) }}"><i class="fa-solid fa-calculator"></i> গণিত</a>
        <a class="lebd-program-option" href="{{ route('homepage', ['premium_group' => 'non-department', 'premium_topic' => 'science', 'premium_view' => 'choose']) }}"><i class="fa-solid fa-flask"></i> বিজ্ঞান ও প্রযুক্তি</a>
        <a class="lebd-program-option" href="{{ route('homepage', ['premium_group' => 'non-department', 'premium_topic' => 'others', 'premium_view' => 'choose']) }}"><i class="fa-solid fa-layer-group"></i> অন্যান্য</a>
        </div>
      </div>
      <p class="lebd-program-footnote"><i class="fa-solid fa-circle-info"></i> বিষয় নির্বাচন করে Live / Recorded Class দেখুন</p>
    </article>
  </div>
</section>
