@php
$durationUnits = ['days' => 'দিন', 'months' => 'মাস', 'years' => 'বছর'];
$unit = $durationUnits[$package->duration_unit] ?? $package->duration_unit;
$included = $categories[(int) $package->id] ?? [];
$departmentNames = array_values((array) ($included['department'] ?? []));
$nonDepartmentNames = array_values((array) ($included['non-department'] ?? []));
$reading = $package->reading_limit === null ? 'Unlimited Reading' : 'প্রতিদিন '.(int) $package->reading_limit.' MCQ';
$quizzes = $package->quiz_limit === null ? 'Unlimited Quiz' : 'মাসে '.(int) $package->quiz_limit.' Quiz';
$mainPrice = (float) ($package->main_price ?? $package->price);
$discount = (float) ($package->discount_amount ?? 0);
$savePct = $mainPrice > 0 ? ($discount / $mainPrice) * 100 : 0;
$detailsUrl = route('lebd.packages.details', ['section' => $section, 'package' => $package->id]);
$description = trim((string) ($package->description ?? ''));

// Only this logged-in student and this package; an expired subscription is not active.
$mcqStudent = \Illuminate\Support\Facades\Auth::guard('student')->user();
$mcqAlreadyPurchased = false;
if ($section === 'mcq' && $mcqStudent) {
    $mcqAlreadyPurchased = \Illuminate\Support\Facades\DB::table('lebd_mcq_subscriptions')
        ->where('student_id', $mcqStudent->id)
        ->where('package_id', $package->id)
        ->where('starts_at', '<=', now())
        ->where('ends_at', '>', now())
        ->exists();
}
@endphp
<article class="up-card" data-kind="{{ $package->access_type }}" data-package-id="{{ (int) $package->id }}">
  <div class="up-left">
    <div class="up-card-heading">
      <span class="up-card-icon" aria-hidden="true">{{ $package->access_type === 'both' ? '📚' : ($package->access_type === 'department' ? '🎓' : '📖') }}</span>
      <div class="up-card-title">
        <h2>{{ $package->name }}</h2>
        <span class="up-period">▣ মেয়াদ: {{ (int) $package->duration_value }} {{ $unit }}</span>
      </div>
    </div>
    <div class="up-card-info">
      <div class="up-selected-categories" aria-label="এই প্যাকেজে অন্তর্ভুক্ত ক্যাটাগরি">
        @foreach($departmentNames as $name)
          <span class="up-category-chip up-category-department">{{ $name }}</span>
        @endforeach
        @foreach($nonDepartmentNames as $name)
          <span class="up-category-chip up-category-nondepartment">{{ $name }}</span>
        @endforeach
        @if(empty($departmentNames) && empty($nonDepartmentNames))
          <span class="up-category-chip">Category নির্বাচন করা হয়নি</span>
        @endif
      </div>
      <div class="up-description-line">
        <p class="up-description up-description-preview">{{ $description !== '' ? $description : 'নির্বাচিত MCQ Category-তে Reading এবং Quiz Practice-এর সুবিধা।' }}</p>
        <a class="up-inline-more" href="{{ $detailsUrl }}" aria-label="{{ $package->name }}-এর বিস্তারিত দেখুন">See More ›</a>
      </div>
      <div class="up-features"><span>📖 {{ $reading }}</span><span>📝 {{ $quizzes }}</span></div>
    </div>
  </div>
  <div class="up-right">
    <div class="up-price-block">
      <strong class="up-price">৳{{ number_format((float) $package->price, (float) $package->price == floor((float) $package->price) ? 0 : 2) }}</strong>
      @if($discount > 0 && $mainPrice > 0)
        <span class="up-original-price">৳{{ number_format($mainPrice, $mainPrice == floor($mainPrice) ? 0 : 2) }}</span>
        <span class="up-save">Save {{ number_format($savePct, 2) }}%</span>
      @endif
    </div>
    @if($mcqAlreadyPurchased)
      <button type="button" class="up-action up-buy" disabled aria-disabled="true" style="white-space:nowrap;display:flex;align-items:center;justify-content:center;cursor:not-allowed;background:#e2e8f0;color:#475569;opacity:1;">এই প্যাকেজ কিনেছেন</button>
    @else
      <a href="{{ route('lebd.packages.checkout', ['section' => $section, 'package' => $package->id]) }}" class="up-action up-buy" style="text-decoration:none;white-space:nowrap;display:flex;align-items:center;justify-content:center;">প্যাকেজ কিনুন</a>
    @endif
  </div>
</article>
