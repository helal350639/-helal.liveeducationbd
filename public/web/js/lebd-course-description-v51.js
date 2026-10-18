(function () {
'use strict';
function ready(fn) { if(document.readyState === 'loading') document.addEventListener('DOMContentLoaded', fn); else fn(); }
ready(function () {
 var root=document.getElementById('lebd-v49-builder'); if(!root) return;
 function val(key){ var el=root.querySelector('[data-desc="'+key+'"]');return el ? el.value.trim():''; }
 function esc(x){return String(x).replace(/[&<>"']/g,function(c){return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c];});}
 function lines(key){return val(key).split(/\r?\n/).map(function(x){return x.trim();}).filter(Boolean);}
 function url(key){var v=val(key);if(!v)return '';try{var u=new URL(v);return ['https:','http:'].indexOf(u.protocol)>=0 ? u.href:'';}catch(e){return '';}}
 function paragraph(key){return val(key)?'<p>'+esc(val(key)).replace(/\n/g,'<br>')+'</p>':'';}
 function card(icon,label,body){return body?'<section class="lebd-v49-card"><h3><span class="lebd-v49-icon">'+icon+'</span>'+esc(label)+'</h3>'+body+'</section>':'';}
 function list(key,type){var arr=lines(key);if(!arr.length)return '';return '<ul class="lebd-v49-'+type+'">'+arr.map(function(x){return '<li>'+esc(x)+'</li>';}).join('')+'</ul>';}
 function link(key,label,ico){var u=url(key);return u?'<a class="lebd-v49-link" href="'+esc(u)+'" target="_blank" rel="noopener noreferrer"><span>'+ico+'</span>'+esc(label)+' <span aria-hidden="true">↗</span></a>':'';}
 function build(){
  var title=val('title');var intro=paragraph('intro');var exam=val('exam');
  var links=[link('buyVideo','কোর্স কেনার ভিডিও','▶'),link('demo','ডেমো পরীক্ষা','✎'),link('examVideo','পরীক্ষা দেওয়ার ভিডিও','▶'),link('pdfVideo','PDF ডাউনলোড নির্দেশনা','▣')].filter(Boolean);
  var socials=[link('website','আমাদের ওয়েবসাইট','⌂'),link('facebook','ফেসবুক পেজ','f'),link('group','ফেসবুক গ্রুপ','♧')].filter(Boolean);
  var whatsapp=val('whatsapp'),phone=val('phone');
  var contact=(whatsapp?'<div class="lebd-v49-contact">WhatsApp: <strong>'+esc(whatsapp)+'</strong></div>':'')+(phone?'<div class="lebd-v49-contact">কল করুন: <strong>'+esc(phone)+'</strong></div>':'');
  var data={};root.querySelectorAll('[data-desc]').forEach(function(el){data[el.getAttribute('data-desc')]=el.value;});
  var payload=encodeURIComponent(JSON.stringify(data));
  var html='<div class="lebd-v49-description" data-lebd-builder="'+esc(payload)+'">';
  if(title)html+='<div class="lebd-v49-banner"><span>LIVE EDUCATION BD · কোর্স বিস্তারিত</span><h2>'+esc(title)+'</h2>'+(exam?'<p>◷ '+esc(exam)+'</p>':'')+'</div>';
  html+=card('◈','কোর্স পরিচিতি',intro);
  html+=card('✓','কোর্সের বৈশিষ্ট্য',list('features','checks'));
  html+=card('▦','যে বিভাগগুলোর জন্য',list('departments','departments'));
  html+=card('▶','ভিডিও ও ডেমো',links.length?'<div class="lebd-v49-links">'+links.join('')+'</div>':'');
  html+=card('⌂','আমাদের সঙ্গে যুক্ত থাকুন',socials.length?'<div class="lebd-v49-links">'+socials.join('')+'</div>':'');
  html+=card('☎','যোগাযোগ ও সহায়তা',contact);
  html+=card('ⓘ','গুরুত্বপূর্ণ তথ্য',paragraph('notice'));
  html+='<div class="lebd-v49-pay-note"><strong>নিরাপদ অনলাইন পেমেন্ট</strong><p>bKash · Nagad · Rocket · CellFin · Cards · Net Banking সহ যেসব পদ্ধতি Checkout-এ সক্রিয় আছে, সেগুলোতে পেমেন্ট করা যাবে। প্রকৃত প্রাপ্যতা Checkout-এ যাচাই করুন।</p><p>সঠিক কোর্স মূল্য ও ছাড় দেখতে পেজের <strong>কোর্সটি কিনুন</strong> অংশ ব্যবহার করুন।</p></div></div>';
  return html;
 }
 var preview=document.getElementById('lebd-v49-preview-box');
 var previewButton=document.getElementById('lebd-v49-preview');
 var insertButton=document.getElementById('lebd-v49-insert');
 var details=document.getElementById('details');
 var form=details ? details.closest('form') : null;
 var builderEdited=false, lastInserted='';
 // Rehydrate previously saved builder fields, preserving the original text for further editing.
 function restore(){
   var source=readDetails(), match=source.match(/data-lebd-builder=[\"']([^\"']+)[\"']/i);
   if(!match) return;
   try {var data=JSON.parse(decodeURIComponent(match[1].replace(/&quot;/g,'\"').replace(/&amp;/g,'&')));
     Object.keys(data).forEach(function(key){var el=root.querySelector('[data-desc=\"'+key+'\"]');if(el) el.value=String(data[key]??'');});
     lastInserted=source;
   }catch(e){console.warn('LEBD builder saved data is unreadable',e);}
 }
 // The admin editor is initialized earlier in its own DOMContentLoaded listener.
 restore();
 function hasBuilderData(){return Array.from(root.querySelectorAll('[data-desc]')).some(function(e){return e.value.trim()!=='';});}
 function setDetails(markup){
   if (!details) return;
   details.value=markup;
   if(window.jQuery&&window.jQuery.fn&&window.jQuery.fn.summernote){
     var jq=window.jQuery(details);
     if(jq.next('.note-editor').length){jq.summernote('code',markup);details.value=markup;}
   }
 }
 function readDetails(){
   if(!details)return '';
   if(window.jQuery&&window.jQuery.fn&&window.jQuery.fn.summernote){
     var jq=window.jQuery(details);
     if(jq.next('.note-editor').length)return jq.summernote('code') || '';
   }
   return details.value || '';
 }
 function confirmReplace(){
   var existing=readDetails();
   if (!existing || !existing.replace(/<[^>]*>/g,'').trim() || existing===lastInserted)return true;
   return window.confirm('আগের বিস্তারিত বিবরণ নতুন Builder-এর তথ্য দিয়ে বদলানো হবে। আপনি কি নিশ্চিত?');
 }
 function insert(silent){
   if(!hasBuilderData())return true;
   if(!confirmReplace())return false;
   var markup=build();setDetails(markup);lastInserted=markup;
   if(preview){preview.innerHTML=markup;preview.hidden=false;}
   if(!silent)alert('Description Editor-এ তথ্য বসানো হয়েছে। এখন ব্যাচ সংরক্ষণ করুন।');
   return true;
 }
 root.addEventListener('input',function(e){if(e.target.matches('[data-desc]')){builderEdited=true;var status=document.getElementById('lebd-v51-status');if(status)status.textContent='পরিবর্তন করা হয়েছে — ব্যাচ সংরক্ষণ করুন';}});
 if(previewButton)previewButton.addEventListener('click',function(){if(preview){preview.innerHTML=build();preview.hidden=false;}});
 if(insertButton)insertButton.addEventListener('click',function(){insert(false);});
 // Use capture phase so the textarea is populated before other submit handlers.
 if(form){
   form.addEventListener('submit',function(e){
     if(builderEdited && hasBuilderData()){
       if(!insert(true)){e.preventDefault();e.stopImmediatePropagation();return;}
       setDetails(build());
     }
   },true);
   // Native constraint validation silently blocks submission if required fields are inside another tab.
   form.addEventListener('invalid',function(e){
     var parent=e.target.closest('[data-batch-panel]');
     if(!parent)return;
     var tab=parent.getAttribute('data-batch-panel');
     var button=document.querySelector('[data-batch-tab="'+tab+'"]');
     if(button)button.click();
   },true);
 }
});
})();
