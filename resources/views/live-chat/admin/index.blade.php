@extends('dashboard')
@section('title','Live Chat — Admin')
@section('stylesheets')
<style>
.lebd-chat-admin{padding:18px}.lebd-chat-admin h1{font-size:26px;margin:0 0 18px;color:#153767;font-weight:800}.lebd-chat-shell{background:#fff;border:1px solid #d8e4f3;border-radius:15px;display:grid;grid-template-columns:300px minmax(0,1fr);min-height:590px;overflow:hidden;box-shadow:0 8px 28px #153b7015}.lebd-chat-list{border-right:1px solid #e1eaf7;background:#f6f9ff;overflow:auto;max-height:640px}.lebd-chat-list-head{padding:14px 16px;background:#e8f1ff;font-weight:800;color:#154075}.lebd-chat-list-head small{font-weight:400}.lebd-chat-thread{width:100%;text-align:left;border:0;border-bottom:1px solid #e1eaf7;background:transparent;padding:13px;display:flex;justify-content:space-between;gap:10px;color:#193858}.lebd-chat-thread.active{background:#dfeeff;border-left:4px solid #1369d6}.lebd-chat-thread small{display:block;color:#7789a3;margin-top:5px}.lebd-chat-unread{background:#ec3d51;color:white;border-radius:20px;padding:3px 7px;font-size:11px;height:22px}.lebd-chat-main{display:flex;flex-direction:column;min-width:0}.lebd-chat-main-head{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:13px 15px;border-bottom:1px solid #dfe8f5;background:#fff}.lebd-chat-main-head strong{color:#123863}.lebd-chat-main-head button{border:1px solid #cbd9ee;background:#f2f7ff;color:#1254aa;border-radius:7px;padding:6px 10px}.lebd-chat-messages{flex:1;height:450px;overflow:auto;padding:17px;display:flex;flex-direction:column;gap:10px;background:#fbfdff}.lebd-chat-msg{max-width:82%;align-self:flex-start;background:#eaf2ff;border-radius:12px;padding:9px 12px;white-space:pre-wrap;overflow-wrap:anywhere;color:#153d70}.lebd-chat-msg.admin{align-self:flex-end;background:#1769d1;color:#fff}.lebd-chat-msg small{display:block;margin-top:6px;opacity:.7;font-size:10px}.lebd-chat-form{display:flex;gap:8px;align-items:flex-end;padding:12px;border-top:1px solid #dfe8f5}.lebd-chat-form textarea{min-width:0;flex:1;padding:10px;border:1px solid #c9d8ed;border-radius:9px;resize:vertical}.lebd-chat-form button{background:#1267d1;color:white;border:0;border-radius:9px;padding:12px 16px;font-weight:700}.lebd-chat-alert{color:#ba2525;min-height:20px;padding:0 15px;font-size:12px}@media(max-width:720px){.lebd-chat-admin{padding:9px}.lebd-chat-shell{display:block}.lebd-chat-list{max-height:190px;border-right:0;border-bottom:1px solid #dde7f5}.lebd-chat-messages{height:310px}.lebd-chat-form textarea{max-height:80px}}
</style>
@endsection
@section('content')
<section class="content lebd-chat-admin">
  <h1><i class="fa fa-comments-o"></i> Live Chat <small>— Student & Visitor Messages</small></h1>
  <div class="lebd-chat-shell" id="lebdAdminChat" data-list="{{ route('admin.live-chat.conversations') }}" data-base="{{ url('/admin/live-chat') }}">
    <aside class="lebd-chat-list"><div class="lebd-chat-list-head">Inbox <small id="lebdTotalUnread">0 unread</small></div><div id="lebdChatConversations">Loading conversations...</div></aside>
    <div class="lebd-chat-main">
      <div class="lebd-chat-main-head"><strong id="lebdChatTitle">Select a conversation</strong><button type="button" id="lebdChatStatusBtn" hidden>Close Chat</button></div>
      <div class="lebd-chat-messages" id="lebdChatMessages">শিক্ষার্থীর মেসেজ দেখতে বাম দিক থেকে একটি চ্যাট নির্বাচন করুন।</div>
      <form class="lebd-chat-form" id="lebdChatReply">@csrf<textarea name="message" id="lebdChatReplyText" rows="2" maxlength="2000" placeholder="আপনার উত্তর লিখুন..." required disabled></textarea><button type="submit" disabled id="lebdChatSend">Send Reply</button></form>
      <div class="lebd-chat-alert" id="lebdChatError"></div>
    </div>
  </div>
</section>
@endsection
@section('scripts')
<script src="{{ asset('web/js/lebd-admin-live-chat-v1.js') }}?v=1"></script>
@endsection
