@php
    $lebdSuccessDataReady = \Illuminate\Support\Facades\Schema::hasTable('lebd_home_successes');
    $lebdSuccessItems = $lebdSuccessDataReady ? \App\Models\HomeSuccess::where('is_active',1)->orderBy('sort_order')->orderBy('id')->get() : collect();
    $lebdSuccessInfos = $lebdSuccessItems->where('type','info');
    $lebdSuccessPhoto = $lebdSuccessItems->firstWhere('type','photo');
    $lebdSuccessFeatures = $lebdSuccessItems->where('type','feature');
@endphp
<link rel="stylesheet" href="{{ asset('web/css/lebd-home-success-v35.css') }}">
<section class="lebd-success-v35" aria-label="আমাদের সাফল্য" id="our-success">
 <div class="lebd-success-v35-inner">
  <header class="lebd-success-v35-heading"><span class="lebd-success-v35-eyebrow"><i class="fa fa-graduation-cap"></i> LIVE EDUCATION BD</span><h2>আমাদের <span>সাফল্য</span></h2><p>আপনার স্বপ্ন, আমাদের অঙ্গীকার — একটি সফল শিক্ষার ভবিষ্যতের জন্য</p></header>
  <div class="lebd-success-v35-main">
   <div class="lebd-success-v35-info">
   @forelse($lebdSuccessInfos as $item)
    <article class="lebd-success-v35-row">
     <div class="lebd-success-v35-icon">@if($item->image_path)<img src="{{ asset($item->image_path) }}" alt="" style="max-width:90%;max-height:90%;object-fit:contain">@else<i class="fa {{ $item->icon ?: 'fa-star' }}" aria-hidden="true"></i>@endif</div>
     <div><h3>{{ $item->title }}</h3><p>{{ $item->description }}</p></div>
    </article>
   @empty
    <div class="lebd-success-v35-empty">শিগগিরই আমাদের সাফল্যের তথ্য প্রকাশ করা হবে।</div>
   @endforelse
   </div>
   <div class="lebd-success-v35-photo">
   @if($lebdSuccessPhoto && $lebdSuccessPhoto->image_path)
     <img src="{{ asset($lebdSuccessPhoto->image_path) }}" alt="{{ $lebdSuccessPhoto->title }}" loading="lazy">
   @elseif(!$lebdSuccessDataReady)
     <img src="{{ asset('web/img/success-story.jpg') }}" alt="Live Education BD Success" loading="lazy" onerror="this.style.display='none'">
   @else
     <div class="lebd-success-v35-empty">ছবি যোগ করা হয়নি।</div>
   @endif
   @if($lebdSuccessPhoto && ($lebdSuccessPhoto->title || $lebdSuccessPhoto->subtitle))<div class="lebd-success-v35-photo-caption"><strong>{{ $lebdSuccessPhoto->title }}</strong><span>{{ $lebdSuccessPhoto->subtitle }}</span></div>@endif
   </div>
  </div>
  @if($lebdSuccessFeatures->count())
  <div class="lebd-success-v35-features">
   @foreach($lebdSuccessFeatures as $feature)
    <div class="lebd-success-v35-feature">@if($feature->image_path)<img src="{{ asset($feature->image_path) }}" alt="" style="width:30px;height:30px;object-fit:contain">@else<i class="fa {{ $feature->icon ?: 'fa-check-circle' }}" aria-hidden="true"></i>@endif<span><strong>{{ $feature->title }}</strong><small>{{ $feature->subtitle ?: $feature->description }}</small></span></div>
   @endforeach
  </div>
  @endif
 </div>
</section>
