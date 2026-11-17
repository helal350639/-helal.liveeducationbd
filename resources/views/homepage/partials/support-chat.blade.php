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
      <button type="button" class="lebd-help-option lebd-help-live" id="lebdLiveChatOpen" aria-expanded="false" aria-controls="lebdLiveChatRoom">
        <span class="lebd-help-option-icon"><i class="fa-solid fa-headset" aria-hidden="true"></i></span>
        <span class="lebd-help-option-copy"><strong>লাইভ চ্যাটে কথা বলুন</strong><small>অ্যাডমিন সাপোর্ট · এখানে মেসেজ পাঠান</small></span>
        <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
      </button>
      {{-- View is organized under live-chat/visitor. Inline fallback keeps Homepage alive if only a partial deployment was copied. --}}
      @if(\Illuminate\Support\Facades\View::exists('live-chat.visitor.chat-room'))
        @include('live-chat.visitor.chat-room')
      @else
{{-- Live Chat: visitor message room, embedded in existing support widget. --}}
      <div id="lebdLiveChatRoom" class="lebd-live-room" hidden data-list-url="{{ route('lebd.chat.messages') }}" data-send-url="{{ route('lebd.chat.send') }}">
        <div class="lebd-live-room-heading"><strong>Live Chat</strong><button type="button" id="lebdLiveChatBack">← Back</button></div>
        <div class="lebd-live-messages" id="lebdLiveMessages" role="log" aria-live="polite"><p class="lebd-live-welcome">স্বাগতম! আপনার প্রশ্ন লিখুন। আমাদের অ্যাডমিন উত্তর দেবেন।</p></div>
        <form id="lebdLiveForm" class="lebd-live-form">
          @csrf
          <label class="sr-only" for="lebdLiveInput">আপনার মেসেজ</label>
          <textarea id="lebdLiveInput" name="message" maxlength="2000" rows="2" placeholder="আপনার মেসেজ লিখুন..." required></textarea>
          <button type="submit" id="lebdLiveSend"><i class="fa-solid fa-paper-plane"></i> Send</button>
        </form>
        <div class="lebd-live-error" id="lebdLiveError" role="status"></div>
      </div>

      @endif
      <p class="lebd-help-note"><i class="fa-solid fa-circle-info" aria-hidden="true"></i> WhatsApp, Messenger অথবা সরাসরি অ্যাডমিন Live Chat ব্যবহার করুন।</p>
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
