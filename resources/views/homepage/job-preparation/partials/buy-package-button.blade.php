{{-- LEBD-05: MCQ student category purchase shortcut. Display/layout fix only. --}}
<div class="jp-buy-top" id="lebd05-mcq-premium-cta">
    <a href="{{ url('/packages/mcq') }}" class="jp-buy-sticky" aria-label="প্রিমিয়াম MCQ প্যাকেজ দেখুন">
        <i class="fa-solid fa-crown" aria-hidden="true"></i>
        <span>প্রিমিয়াম প্যাকেজ কিনুন</span>
    </a>
</div>
<style>
/* Isolate the fix to the MCQ Job Preparation pages that include this partial.
   The sticky/fixed site navbar was covering the first content row on both screens. */
.jp-st:has(> #lebd05-mcq-premium-cta) {
    /* Leave an initial safe region for the sticky global navigation. */
    padding-top: 0 !important;
}
.jp-st > #lebd05-mcq-premium-cta.jp-buy-top {
    position: relative !important;
    inset: auto !important;
    top: auto !important;
    right: auto !important;
    bottom: auto !important;
    left: auto !important;
    float: none !important;
    clear: both !important;
    transform: none !important;
    display: flex !important;
    justify-content: flex-end !important;
    align-items: center !important;
    box-sizing: border-box !important;
    width: 100% !important;
    min-height: 45px !important;
    margin: 0 0 12px !important;
    padding: 3px 8px !important;
    visibility: visible !important;
    opacity: 1 !important;
    overflow: visible !important;
    z-index: 2 !important;
    pointer-events: none !important;
}
.jp-st > #lebd05-mcq-premium-cta .jp-buy-sticky {
    position: relative !important;
    top: auto !important;
    inset: auto !important;
    transform: none !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 7px !important;
    box-sizing: border-box !important;
    width: auto !important;
    max-width: 100% !important;
    background: linear-gradient(110deg,#f97316,#ff463b) !important;
    color: #fff !important;
    text-decoration: none !important;
    border: 0 !important;
    border-radius: 999px !important;
    padding: 10px 16px !important;
    box-shadow: 0 3px 10px rgba(0,0,0,.13) !important;
    font-weight: 700 !important;
    font-size: 13px !important;
    line-height: 1.25 !important;
    white-space: nowrap !important;
    visibility: visible !important;
    opacity: 1 !important;
    pointer-events: auto !important;
    z-index: 2 !important;
}
.jp-st > #lebd05-mcq-premium-cta .jp-buy-sticky i {font-size:14px;line-height:1}
@media(max-width:600px) {
    .jp-st > #lebd05-mcq-premium-cta.jp-buy-top { min-height: 44px !important; margin-bottom: 9px !important; padding: 3px 7px !important; }
    .jp-st > #lebd05-mcq-premium-cta .jp-buy-sticky { font-size: 12px !important; padding: 10px 12px !important; white-space: normal !important; text-align: center !important; }
}
.jp-access-notice{background:#fff7e9;border:1px solid #f5dbac;border-radius:9px;padding:10px 12px;margin:10px 0;font-size:13px;color:#774514}
.jp-access-notice a{font-weight:700}
</style>
<script>
/* Correct only the initial overlap caused by a fixed/sticky global header.
   No other MCQ, purchase, or navbar behavior is changed. */
(function(){
  'use strict';
  var cta = document.getElementById('lebd05-mcq-premium-cta');
  if(!cta) return;
  var page = cta.closest('.jp-st');
  if(!page) return;
  function adjust(){
    page.style.removeProperty('padding-top');
    page.style.removeProperty('margin-top');
    var navs = document.querySelectorAll('header, .main-header, .site-header, .navbar, .sticky-header, #header');
    var bottom = 0;
    for(var i=0;i<navs.length;i++){
      var node = navs[i];
      if(node === page || page.contains(node)) continue;
      var st = getComputedStyle(node);
      if(st.position !== 'fixed' && st.position !== 'sticky') continue;
      var rect = node.getBoundingClientRect();
      if(rect.top <= 4 && rect.bottom > bottom && rect.bottom < window.innerHeight * .5) bottom = rect.bottom;
    }
    var top = cta.getBoundingClientRect().top;
    // Fallback for themes that position their navigation through a wrapper selector.
    if(bottom < 60 && top < 65) bottom = 85;
    var need = Math.ceil(Math.max(0, bottom + 10 - top));
    if(need > 0) page.style.setProperty('padding-top', need + 'px', 'important');
  }
  if(document.readyState==='loading') document.addEventListener('DOMContentLoaded', adjust, {once:true});
  else adjust();
  window.addEventListener('load', adjust, {once:true});
  window.addEventListener('resize', adjust);
})();
</script>
