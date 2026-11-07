{{-- Embedded into the existing Exam Section category card design; not a separate page. --}}
@php
    $lebdOwnedExam = app(\App\Services\ExamSection\StudentPurchasedExamPackages::class)->forCurrentStudent();
    $lebdOwnedPackages = $lebdOwnedExam['packages'];
    $lebdOwnedScopes = $lebdOwnedExam['scopes'];
@endphp
@if($lebdOwnedPackages->isNotEmpty())
<style>
#lebd-exam-owned{margin-top:18px;border-top:1px solid #ffffff32;padding-top:16px}
#lebd-exam-owned .owned-heading{display:flex;align-items:center;gap:10px;margin:0 0 12px;color:#fff;font-size:18px;font-weight:750;flex-wrap:wrap}
#lebd-exam-owned .owned-heading small{font-size:11px;font-weight:650;background:#ffffff24;padding:4px 9px;border-radius:999px}
#lebd-exam-owned .owned-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:11px}
#lebd-exam-owned .owned-card{min-width:0;background:#fff;color:#15325b;border:1px solid #dce9ff;border-radius:12px;padding:15px;box-shadow:0 4px 12px #031d4220}
#lebd-exam-owned .owned-card-top{display:flex;align-items:flex-start;justify-content:space-between;gap:8px}
#lebd-exam-owned .owned-name{font-size:15px;font-weight:800;overflow-wrap:anywhere}
#lebd-exam-owned .owned-status{font-size:11px;font-weight:750;color:#176e46;background:#dcf8e9;border-radius:999px;padding:5px 8px;white-space:nowrap}
#lebd-exam-owned .owned-expiry{font-size:12px;color:#526987;margin:7px 0 12px}
#lebd-exam-owned .owned-scope-label{font-size:12px;font-weight:750;color:#233e62;margin-bottom:7px}
#lebd-exam-owned .owned-scopes{display:flex;flex-wrap:wrap;gap:7px}
#lebd-exam-owned .owned-scope{display:inline-flex;align-items:center;gap:5px;background:#edf5ff;border:1px solid #d4e5ff;color:#105baf!important;text-decoration:none!important;border-radius:8px;padding:8px 10px;font-size:12px;font-weight:700;overflow-wrap:anywhere;max-width:100%;line-height:1.4}
#lebd-exam-owned .owned-scope:hover,#lebd-exam-owned .owned-scope:focus{background:#dcecff}
#lebd-exam-owned .owned-empty{color:#526987;font-size:12px;margin:0}
@media(max-width:650px){#lebd-exam-owned .owned-grid{grid-template-columns:1fr}#lebd-exam-owned .owned-card{padding:12px}#lebd-exam-owned .owned-heading{font-size:16px}}
</style>
<div id="lebd-exam-owned" aria-label="আমার কেনা Exam Section প্যাকেজ">
  <h3 class="owned-heading">✓ আমার কেনা Exam Packages <small>{{ $lebdOwnedPackages->count() }}টি Active</small></h3>
  <div class="owned-grid">
  @foreach($lebdOwnedPackages as $lebdOwned)
    <article class="owned-card">
      <div class="owned-card-top">
        <strong class="owned-name">{{ $lebdOwned->name }}</strong>
        <span class="owned-status">✓ প্যাকেজ কিনেছে</span>
      </div>
      <div class="owned-expiry">মেয়াদ: {{ \Carbon\Carbon::parse($lebdOwned->ends_at)->format('d M Y, h:i A') }} পর্যন্ত</div>
      <div class="owned-scope-label">এই প্যাকেজে আপনার Exam Access:</div>
      <div class="owned-scopes">
      @forelse($lebdOwnedScopes->get($lebdOwned->package_id, collect()) as $lebdScope)
        @if($lebdScope->subcategory_id)
          <a class="owned-scope" href="{{ route('student.exam-section.browse-exams', ['category' => $lebdScope->category_id, 'subcategory_id' => $lebdScope->subcategory_id]) }}">▣ {{ $lebdScope->category_name }} → {{ $lebdScope->subcategory_name }}</a>
        @else
          <a class="owned-scope" href="{{ route('student.exam-section.browse-category', ['category' => $lebdScope->category_id]) }}">▣ {{ $lebdScope->category_name }}</a>
        @endif
      @empty
        <p class="owned-empty">বর্তমানে এই প্যাকেজের কোনো Active Category/Subcategory পাওয়া যায়নি।</p>
      @endforelse
      </div>
    </article>
  @endforeach
  </div>
</div>
@endif
