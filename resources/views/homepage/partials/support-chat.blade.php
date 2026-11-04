<div class="lebd-help" id="lebdHelp" data-lebd-help>
  <section class="lebd-help-panel" id="lebdHelpPanel" role="dialog" aria-modal="false" aria-label="Live Education BD সাপোর্ট" aria-hidden="true" hidden>
    <div class="lebd-help-banner">
      <div class="lebd-help-bar">
        <div class="lebd-help-brand"><span class="lebd-help-logo" aria-hidden="true"><i class="fa-solid fa-graduation-cap"></i></span><span>LIVE EDUCATION BD <small>সাপোর্ট সেন্টার</small></span></div>
        <button type="button" class="lebd-help-close" id="lebdHelpClose" aria-label="চ্যাট বন্ধ করুন"><i class="fa-solid fa-xmark"></i></button>
      </div>
      <h2>আসসালামু আলাইকুম! 👋</h2>
      <p>কোর্স, ভর্তি, পেমেন্ট বা পরীক্ষা সংক্রান্ত সহায়তায় যোগাযোগ করুন।</p>
    </div>
    <div class="lebd-help-body">
      <span class="lebd-help-caption">যেভাবে কথা বলতে চান</span>
      <a class="lebd-help-option lebd-help-whatsapp" href="https://wa.me/8801718778184" target="_blank" rel="noopener noreferrer">
        <span class="lebd-help-option-icon"><i class="fa-brands fa-whatsapp" aria-hidden="true"></i></span><span class="lebd-help-option-copy"><strong>WhatsApp-এ কথা বলুন</strong><small>01718-778184 · মেসেজ পাঠান</small></span><i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i>
      </a>
      <a class="lebd-help-option lebd-help-messenger" href="https://m.me/saeonlineexamgroup" target="_blank" rel="noopener noreferrer">
        <span class="lebd-help-option-icon"><i class="fa-brands fa-facebook-messenger" aria-hidden="true"></i></span><span class="lebd-help-option-copy"><strong>Messenger-এ কথা বলুন</strong><small>Live Education BD Facebook</small></span><i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i>
      </a>
      {{-- Future internal Admin live chat: presentation only; no backend or WebSocket connected. --}}
      <button type="button" class="lebd-help-option lebd-help-live lebd-help-live-disabled" disabled aria-disabled="true" title="লাইভ চ্যাট শীঘ্রই চালু হবে">
        <span class="lebd-help-option-icon"><i class="fa-solid fa-headset" aria-hidden="true"></i></span>
        <span class="lebd-help-option-copy"><strong>লাইভ চ্যাটে কথা বলুন</strong><small>অ্যাডমিন সাপোর্ট · শীঘ্রই আসছে</small></span>
        <span class="lebd-help-soon">শীঘ্রই</span>
      </button>
      <p class="lebd-help-note"><i class="fa-solid fa-circle-info" aria-hidden="true"></i> WhatsApp ও Messenger এখনই ব্যবহার করতে পারবেন। লাইভ চ্যাটটি পরবর্তীতে Admin Panel-এর সঙ্গে যুক্ত হবে।</p>
    </div>
  </section>

  {{-- Icon-only trigger: original IDs and JS hooks preserved. --}}
  <button type="button" class="lebd-help-trigger" id="lebdHelpToggle" aria-expanded="false" aria-controls="lebdHelpPanel" aria-label="সাপোর্ট চ্যাট খুলুন">
    <i class="fa-solid fa-comments" aria-hidden="true"></i>
  </button>
</div>

<style id="lebd-support-icon-only-fix">
/* Scopes ONLY to the homepage floating support toggle; no change to the panel. */
#lebdHelp #lebdHelpToggle,
#lebdHelp #lebdHelpToggle:hover,
#lebdHelp #lebdHelpToggle:focus,
#lebdHelp #lebdHelpToggle:active {
    background: transparent !important;
    background-color: transparent !important;
    background-image: none !important;
    border: 0 !important;
    box-shadow: none !important;
    outline: 0 !important;
    border-radius: 0 !important;
    width: 44px !important;
    height: 44px !important;
    min-width: 44px !important;
    min-height: 44px !important;
    padding: 0 !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    cursor: pointer !important;
    font-size: 28px !important;
    color: #075bc4 !important;
}
#lebdHelp #lebdHelpToggle i.fa-comments { color: #075bc4 !important; font-size: 28px !important; }
#lebdHelp #lebdHelpToggle:focus-visible { outline: 2px solid #075bc4 !important; outline-offset: 3px !important; }
</style>
