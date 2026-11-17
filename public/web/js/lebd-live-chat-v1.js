/* LEBD Live Chat v3 — reliable form-encoded send + useful server error reporting. */
(function(){
  'use strict';
  if (window.__lebdLiveChatBoundV3) return;
  window.__lebdLiveChatBoundV3 = true;
  function init(){
    var root=document.getElementById('lebdHelp'); if(!root)return;
    var room=root.querySelector('#lebdLiveChatRoom'),open=root.querySelector('#lebdLiveChatOpen'),back=root.querySelector('#lebdLiveChatBack');
    var form=root.querySelector('#lebdLiveForm'),messages=root.querySelector('#lebdLiveMessages'),error=root.querySelector('#lebdLiveError'),btn=root.querySelector('#lebdLiveSend');
    if(!room||!open||!form||!messages||!error||!btn)return;
    var last='',busy=false,pollTimer=null;
    function esc(s){var d=document.createElement('div');d.textContent=s||'';return d.innerHTML;}
    function render(rows){
      var html='';
      if(!rows.length) html='<p class="lebd-live-welcome">স্বাগতম! আপনার প্রশ্ন লিখুন। আমাদের অ্যাডমিন উত্তর দেবেন।</p>';
      rows.forEach(function(m){html+='<div class="lebd-live-bubble'+(m.sender==='visitor'?' mine':'')+'">'+esc(m.body)+'</div>';});
      if(html!==last){var atBottom=messages.scrollHeight-messages.scrollTop-messages.clientHeight<90;messages.innerHTML=html;last=html;if(atBottom||rows.length<=2)messages.scrollTop=messages.scrollHeight;}
    }
    function responseError(r,body){
      if(r.status===419)return 'সেশন শেষ হয়েছে। পেজটি রিফ্রেশ করে আবার মেসেজ পাঠান।';
      if(r.status===422)return 'মেসেজটি সঠিকভাবে লিখুন (সর্বোচ্চ ২০০০ অক্ষর)।';
      if(r.status===429)return 'খুব দ্রুত মেসেজ পাঠানো হচ্ছে। এক মিনিট পরে আবার চেষ্টা করুন।';
      if(r.status>=500)return 'সার্ভারে সমস্যা হয়েছে। Live Chat database/table তৈরি আছে কি না Admin-কে পরীক্ষা করতে হবে।';
      return 'মেসেজ পাঠানো যায়নি (HTTP '+r.status+'). আবার চেষ্টা করুন।';
    }
    function poll(){
      if(room.hidden||document.hidden||busy)return;
      if(!room.dataset.listUrl){error.textContent='Live Chat route পাওয়া যায়নি।';return;}
      fetch(room.dataset.listUrl,{credentials:'same-origin',headers:{'Accept':'application/json','X-Requested-With':'XMLHttpRequest'}})
      .then(function(r){return r.text().then(function(t){if(!r.ok)throw {status:r.status,body:t};try{return JSON.parse(t);}catch(e){throw {status:r.status||500,body:t};}});})
      .then(function(d){render(d.messages||[]);error.textContent='';})
      .catch(function(e){error.textContent=responseError({status:e.status||0},e.body);});
    }
    open.addEventListener('click',function(){room.hidden=false;root.classList.add('lebd-chat-mode');open.setAttribute('aria-expanded','true');error.textContent='';poll();setTimeout(function(){var input=form.querySelector('textarea');if(input)input.focus();},80);});
    if(back)back.addEventListener('click',function(){room.hidden=true;root.classList.remove('lebd-chat-mode');open.setAttribute('aria-expanded','false');});
    form.addEventListener('submit',function(e){
      e.preventDefault();var input=form.querySelector('textarea'),value=input.value.trim();if(!value||busy)return;
      busy=true;btn.disabled=true;error.textContent='';
      if(!room.dataset.sendUrl){error.textContent='Message send route পাওয়া যায়নি।';busy=false;btn.disabled=false;return;}
      var tokenField=form.querySelector('input[name="_token"]');
      var token=(tokenField&&tokenField.value)||(document.querySelector('meta[name="csrf-token"]')||{}).content||'';
      var payload=new FormData();payload.append('message',value);if(token)payload.append('_token',token);
      fetch(room.dataset.sendUrl,{method:'POST',credentials:'same-origin',headers:{'Accept':'application/json','X-Requested-With':'XMLHttpRequest','X-CSRF-TOKEN':token},body:payload})
      .then(function(r){return r.text().then(function(t){var d={};try{d=JSON.parse(t);}catch(e){}if(!r.ok)throw {status:r.status,body:t};return d;});})
      .then(function(d){if(d.ok!==true)throw {status:500};input.value='';last='';error.textContent='';poll();})
      .catch(function(e){error.textContent=responseError({status:e.status||0},e.body);})
      .finally(function(){busy=false;btn.disabled=false;input.focus();});
    });
    if(pollTimer)clearInterval(pollTimer);pollTimer=setInterval(poll,5000);
  }
  if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',init);else init();
})();
