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
