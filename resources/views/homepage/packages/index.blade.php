@extends('homepage.packages.layout')
@section('page_title', 'MCQ Subscription Packages')
@section('body')
<section class="up-intro"><div class="up-crown" aria-hidden="true">♛</div><div><h1>প্রিমিয়াম MCQ প্যাকেজ</h1><p>Live Education BD-এর নির্বাচিত MCQ Reading ও Quiz Practice Package।</p></div></section>
<div class="up-tabs" role="group" aria-label="Package type">
<button type="button" class="up-tab is-selected" data-filter="all" aria-pressed="true">সব প্যাকেজ</button>
<button type="button" class="up-tab" data-filter="department" aria-pressed="false">Department</button>
<button type="button" class="up-tab" data-filter="non-department" aria-pressed="false">Non-Department</button>
<button type="button" class="up-tab" data-filter="both" aria-pressed="false">Combined</button></div>
<div class="up-list">
@php
  // Matched to the card order below. The controller already merges paid/manual active access.
  $lebdMcqOwnedCardFlags = [];
@endphp
@forelse($packages as $package)
  @php
    $lebdMcqOwnedCardFlags[] = isset($activePackageAccess)
      && $activePackageAccess
      && $activePackageAccess->has((int) $package->id);
  @endphp
  @include('components.lebd-package.card', ['package'=>$package, 'categories'=>$categories, 'section'=>$section])
@empty
<div class="up-empty">এখন কোনো Active Package নেই। Admin Panel থেকে Package তৈরি ও Active করলে এখানে দেখা যাবে।</div>
@endforelse
<div class="up-empty up-filter-empty" hidden>এই Category-তে বর্তমানে কোনো Package নেই।</div>
</div>
<p class="up-disclaimer">Payment Gateway এবং Subscription Activation যুক্ত না হওয়া পর্যন্ত Buy Package বন্ধ থাকবে।</p>
<script>
(function(){
  'use strict';
  // The existing shared card partial stays untouched to protect other storefronts.
  // Paid and manual grants are checked on the server by ActivePackageAccess.
  var ownedFlags = @json($lebdMcqOwnedCardFlags);
  var cards = [].slice.call(document.querySelectorAll('.up-list .up-card'));
  cards.forEach(function(card, index){
    if (!ownedFlags[index]) return;
    var targets = [].slice.call(card.querySelectorAll('a, button'));
    targets.forEach(function(target){
      var label = (target.textContent || '').replace(/\s+/g, ' ').trim();
      var href = target.getAttribute('href') || '';
      var isBuy = label.indexOf('প্যাকেজ কিনুন') !== -1
        || label.indexOf('Buy Package') !== -1
        || /\/packages\/mcq\/\d+\/checkout(?:[?#]|$)/.test(href);
      if (!isBuy) return;
      // Do not allow a visually active checkout link for already-owned packages.
      target.removeAttribute('href');
      target.removeAttribute('onclick');
      if (target.tagName.toLowerCase() === 'button') target.disabled = true;
      target.setAttribute('aria-disabled', 'true');
      target.setAttribute('title', 'আপনি ইতিমধ্যে এই প্যাকেজে যুক্ত আছেন');
      target.textContent = 'আপনি এই প্যাকেজে যুক্ত আছেন';
      target.style.pointerEvents = 'none';
      target.style.cursor = 'default';
      target.style.background = '#e7f6eb';
      target.style.color = '#176a39';
      target.style.borderColor = '#c5e8d0';
      target.style.whiteSpace = 'normal';
      target.style.textAlign = 'center';
      target.style.lineHeight = '1.35';
    });
  });
  // Existing category filter behavior, unchanged.
  var tabs=document.querySelectorAll('.up-tab');var empty=document.querySelector('.up-filter-empty');
  tabs.forEach(function(tab){tab.addEventListener('click',function(){var selected=tab.getAttribute('data-filter');tabs.forEach(function(t){var yes=t===tab;t.classList.toggle('is-selected',yes);t.setAttribute('aria-pressed',yes?'true':'false')});var visible=0;cards.forEach(function(card){var show=selected==='all'||card.getAttribute('data-kind')===selected;card.hidden=!show;if(show)visible++});if(empty)empty.hidden=visible!==0;});});
})();
</script>
@endsection
