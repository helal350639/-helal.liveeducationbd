@extends('student')
@section('title', 'Notifications | Live Education BD')

@section('stylesheets')
<style>
.lebd-notifications-page,.lebd-notifications-page *{box-sizing:border-box}
.lebd-notifications-page{min-height:72vh;padding:24px 14px 48px;background:#f1f6fd;color:#17345c;font-family:system-ui,-apple-system,'Segoe UI','Noto Sans Bengali',sans-serif}
.lebd-notifications-page .ln-wrap{max-width:1050px;margin:0 auto}
.lebd-notifications-page .ln-back{display:inline-flex;align-items:center;gap:8px;margin:0 0 14px;padding:9px 13px;border:1px solid #d7e5f8;border-radius:10px;background:#fff;color:#1767c8;text-decoration:none;font-size:13px;font-weight:750}
.lebd-notifications-page .ln-head{display:flex;align-items:center;justify-content:space-between;gap:14px;padding:19px 21px;margin-bottom:14px;border:1px solid #d8e5f7;border-radius:18px;background:#fff;box-shadow:0 5px 18px #1644770b}
.lebd-notifications-page .ln-title-wrap{display:flex;align-items:center;gap:13px;min-width:0}
.lebd-notifications-page .ln-title-icon{display:grid;place-items:center;flex:0 0 48px;width:48px;height:48px;border-radius:14px;background:#e8f2ff;color:#0870df;font-size:21px}
.lebd-notifications-page h1{margin:0;color:#173d75;font-size:23px;line-height:1.3;font-weight:850}
.lebd-notifications-page .ln-subtitle{margin:4px 0 0;color:#7186a3;font-size:12px;line-height:1.5}
.lebd-notifications-page .ln-counts{display:flex;gap:8px;flex-wrap:wrap;justify-content:flex-end}
.lebd-notifications-page .ln-count{min-width:83px;padding:8px 11px;border:1px solid #e0eafa;border-radius:11px;background:#f7faff;text-align:center}
.lebd-notifications-page .ln-count b{display:block;color:#145fc4;font-size:18px;line-height:1.2}
.lebd-notifications-page .ln-count span{font-size:10px;color:#7186a3;font-weight:700}
.lebd-notifications-page .ln-toolbar{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:13px 15px;margin-bottom:12px;border:1px solid #d8e5f7;border-radius:15px;background:#fff}
.lebd-notifications-page .ln-filters{display:flex;gap:7px;flex-wrap:wrap}
.lebd-notifications-page .ln-filter{border:1px solid #dbe7f8;border-radius:22px;padding:8px 14px;background:#f5f8fe;color:#526b8e;font-size:12px;font-weight:800;cursor:pointer}
.lebd-notifications-page .ln-filter.active{background:#e8f1ff;border-color:#b9d3ff;color:#075fd2}
.lebd-notifications-page .ln-mark-all{border:0;border-radius:9px;padding:9px 13px;background:#edf4ff;color:#075fd2;font-size:12px;font-weight:800;cursor:pointer}
.lebd-notifications-page .ln-list{display:grid;gap:10px}
.lebd-notifications-page .ln-card{display:flex;gap:13px;padding:15px 16px;border:1px solid #f1e5bb;border-radius:15px;background:#fff9ea;box-shadow:0 4px 13px #1b477008}
.lebd-notifications-page .ln-card.is-seen{background:#fff;border-color:#dfe8f5}
.lebd-notifications-page .ln-icon{display:grid;place-items:center;flex:0 0 44px;width:44px;height:44px;border-radius:13px;background:#fff0cb;color:#d68a08;font-size:18px}
.lebd-notifications-page .ln-icon.lebd-icon-result{background:#fff0cb;color:#d68a08}
.lebd-notifications-page .ln-icon.lebd-icon-notice{background:#e8f1ff;color:#2672d8}
.lebd-notifications-page .ln-body{flex:1;min-width:0}
.lebd-notifications-page .ln-top{display:flex;align-items:flex-start;justify-content:space-between;gap:10px}
.lebd-notifications-page .ln-name{color:#1f2d3d;font-size:14px;line-height:1.4;font-weight:850}
.lebd-notifications-page .ln-badge{flex:0 0 auto;padding:4px 9px;border-radius:20px;background:#ff5a5f;color:#fff;font-size:10px;font-weight:850}
.lebd-notifications-page .ln-card.is-seen .ln-badge{background:#edf1f7;color:#718096}
.lebd-notifications-page .ln-message{margin-top:5px;color:#526781;font-size:13px;line-height:1.65;overflow-wrap:anywhere}
.lebd-notifications-page .ln-bottom{display:flex;align-items:center;justify-content:space-between;gap:10px;margin-top:10px}
.lebd-notifications-page .ln-time{color:#8291a6;font-size:11px}
.lebd-notifications-page .ln-actions{display:flex;gap:7px;flex-wrap:wrap}
.lebd-notifications-page .ln-action{display:inline-flex;align-items:center;justify-content:center;min-height:32px;padding:6px 12px;border:1px solid #b8d0ff;border-radius:9px;background:#f8fbff;color:#1766d3;text-decoration:none;font-size:11px;font-weight:850;cursor:pointer}
.lebd-notifications-page .ln-action:disabled{opacity:.6;cursor:default}
.lebd-notifications-page .ln-empty,.lebd-notifications-page .ln-loading{padding:35px 18px;border:1px dashed #cad9ee;border-radius:15px;background:#fff;color:#7186a3;text-align:center;font-size:13px;line-height:1.7}
.lebd-notifications-page .ln-error{color:#b42335}
@media(max-width:650px){
 .lebd-notifications-page{padding:13px 9px 32px}
 .lebd-notifications-page .ln-head{display:block;padding:14px;border-radius:15px}
 .lebd-notifications-page .ln-title-icon{width:42px;height:42px;flex-basis:42px;font-size:18px}
 .lebd-notifications-page h1{font-size:20px}
 .lebd-notifications-page .ln-counts{justify-content:flex-start;margin-top:12px}
 .lebd-notifications-page .ln-count{flex:1;min-width:0;padding:7px 8px}
 .lebd-notifications-page .ln-toolbar{align-items:flex-start;flex-direction:column;padding:11px}
 .lebd-notifications-page .ln-mark-all{width:100%}
 .lebd-notifications-page .ln-card{gap:9px;padding:12px;border-radius:13px}
 .lebd-notifications-page .ln-icon{width:38px;height:38px;flex-basis:38px;font-size:16px}
 .lebd-notifications-page .ln-name{font-size:13px}
 .lebd-notifications-page .ln-message{font-size:12px}
 .lebd-notifications-page .ln-bottom{align-items:flex-start;flex-direction:column}
 .lebd-notifications-page .ln-actions{width:100%}
 .lebd-notifications-page .ln-action{flex:1}
}
</style>
@endsection

@section('content')
<div class="lebd-notifications-page" id="lebdNotificationsPage"
     data-feed-url="{{ route('students.header-notifications') }}"
     data-seen-url="{{ route('students.header-notifications.seen') }}"
     data-seen-all-url="{{ route('students.header-notifications.seen-all') }}"
     data-csrf="{{ csrf_token() }}">
  <main class="ln-wrap">
    <a class="ln-back" href="{{ route('students.dashboard') }}"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Dashboard</a>
    <header class="ln-head">
      <div class="ln-title-wrap">
        <span class="ln-title-icon"><i class="fa-solid fa-bell" aria-hidden="true"></i></span>
        <div><h1>Notifications</h1><p class="ln-subtitle">আপনার পরীক্ষা, ফলাফল ও গুরুত্বপূর্ণ নোটিশের সব আপডেট এখানে পাবেন।</p></div>
      </div>
      <div class="ln-counts" aria-live="polite">
        <div class="ln-count"><b id="lnTotalCount">0</b><span>মোট</span></div>
        <div class="ln-count"><b id="lnUnreadCount">0</b><span>Unread</span></div>
        <div class="ln-count"><b id="lnSeenCount">0</b><span>Seen</span></div>
      </div>
    </header>
    <section class="ln-toolbar" aria-label="Notification controls">
      <div class="ln-filters" role="tablist" aria-label="Filter notifications">
        <button type="button" class="ln-filter active" data-filter="all" aria-pressed="true">All</button>
        <button type="button" class="ln-filter" data-filter="unread" aria-pressed="false">Unread</button>
        <button type="button" class="ln-filter" data-filter="seen" aria-pressed="false">Seen</button>
      </div>
      <button type="button" class="ln-mark-all" id="lnMarkAllRead"><i class="fa-solid fa-check-double" aria-hidden="true"></i> Mark all as read</button>
    </section>
    <section class="ln-list" id="lnNotificationList" aria-live="polite">
      <div class="ln-loading">নোটিফিকেশন লোড হচ্ছে...</div>
    </section>
  </main>
</div>
<script>
(function(){
 'use strict';
 var root=document.getElementById('lebdNotificationsPage');
 if(!root || root.dataset.initialized==='1')return;
 root.dataset.initialized='1';
 var list=document.getElementById('lnNotificationList'), filter='all', items=[];
 var feedUrl=root.dataset.feedUrl, seenUrl=root.dataset.seenUrl, seenAllUrl=root.dataset.seenAllUrl, csrf=root.dataset.csrf;
 var totalEl=document.getElementById('lnTotalCount'), unreadEl=document.getElementById('lnUnreadCount'), seenEl=document.getElementById('lnSeenCount');
 function el(tag,cls,text){var n=document.createElement(tag);if(cls)n.className=cls;if(text!==undefined)n.textContent=text;return n;}
 function request(url,body){return fetch(url,{method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':csrf,'X-Requested-With':'XMLHttpRequest'},body:JSON.stringify(body||{})}).then(function(r){if(!r.ok)throw new Error('Request failed');return r.json();});}
 function counts(){var unread=items.filter(function(n){return !n.seen;}).length;totalEl.textContent=items.length;unreadEl.textContent=unread;seenEl.textContent=items.length-unread;}
 function render(){
  list.replaceChildren();
  var shown=items.filter(function(n){return filter==='all'||(filter==='seen'?n.seen:!n.seen);});
  if(!shown.length){list.appendChild(el('div','ln-empty',filter==='unread'?'সব নোটিফিকেশন Seen করা হয়েছে।':'এখানে কোনো নোটিফিকেশন নেই।'));return;}
  shown.forEach(function(n){
   var card=el('article','ln-card'+(n.seen?' is-seen':''));
   var icon=el('div','ln-icon '+(n.tone==='result'?'lebd-icon-result':'lebd-icon-notice'));
   icon.appendChild(el('i','fa-solid '+(n.icon||'fa-bell')));
   var body=el('div','ln-body'), top=el('div','ln-top');
   top.appendChild(el('div','ln-name',n.title||'Notification'));
   top.appendChild(el('span','ln-badge',n.seen?'Seen':'New'));
   body.appendChild(top);body.appendChild(el('div','ln-message',n.message||''));
   if(n.image){var image=el('img','');image.src=n.image;image.alt=n.title||'Notification image';image.style.cssText='display:block;max-width:100%;max-height:220px;margin-top:10px;border-radius:10px';body.appendChild(image);}
   var bottom=el('div','ln-bottom');bottom.appendChild(el('small','ln-time',n.time||'Recently'));
   var actions=el('div','ln-actions');
   if(n.url){var view=el('a','ln-action','View');view.href=n.url;view.addEventListener('click',function(){if(!n.seen)request(seenUrl,{key:n.key}).catch(function(){});});actions.appendChild(view);}
   var seen=el('button','ln-action',n.seen?'Seen ✓':'Seen');seen.type='button';seen.disabled=!!n.seen;
   seen.addEventListener('click',function(){seen.disabled=true;request(seenUrl,{key:n.key}).then(load).catch(function(){seen.disabled=false;alert('Seen status save করা যায়নি। আবার চেষ্টা করুন।');});});
   actions.appendChild(seen);bottom.appendChild(actions);body.appendChild(bottom);card.appendChild(icon);card.appendChild(body);list.appendChild(card);
  });
 }
 function load(){
  return fetch(feedUrl,{credentials:'same-origin',headers:{'Accept':'application/json','X-Requested-With':'XMLHttpRequest'}})
   .then(function(r){if(!r.ok)throw new Error('Feed unavailable');return r.json();})
   .then(function(data){items=data.notifications||[];counts();render();})
   .catch(function(){if(!items.length){list.replaceChildren(el('div','ln-empty ln-error','নোটিফিকেশন লোড করা যাচ্ছে না। পেজটি Refresh করে আবার চেষ্টা করুন।'));}});
 }
 root.querySelectorAll('.ln-filter').forEach(function(btn){btn.addEventListener('click',function(){filter=btn.dataset.filter||'all';root.querySelectorAll('.ln-filter').forEach(function(b){var active=b===btn;b.classList.toggle('active',active);b.setAttribute('aria-pressed',active?'true':'false');});render();});});
 document.getElementById('lnMarkAllRead').addEventListener('click',function(){var button=this;button.disabled=true;request(seenAllUrl,{}).then(load).catch(function(){alert('Seen status save করা যায়নি।');}).finally(function(){button.disabled=false;});});
 load();window.setInterval(load,30000);
})();
</script>
@endsection
