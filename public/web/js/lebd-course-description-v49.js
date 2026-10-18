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
  var html='<div class="lebd-v49-description">';
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
 var preview=document.getElementById('lebd-v49-preview-box');document.getElementById('lebd-v49-preview').addEventListener('click',function(){preview.innerHTML=build();preview.hidden=false;});
 document.getElementById('lebd-v49-insert').addEventListener('click',function(){
  var el=document.getElementById('details');if(!el)return;
  var old=(window.jQuery&&window.jQuery(el).next('.note-editor').length)?window.jQuery(el).summernote('code'):el.value;
  if(old&&old.replace(/<[^>]*>/g,'').trim() && !window.confirm('আগের Description-এর লেখা পরিবর্তন হবে। আপনি কি নতুন ডিজাইন বসাতে চান?'))return;
  var html=build();if(window.jQuery&&window.jQuery.fn.summernote&&window.jQuery(el).next('.note-editor').length){window.jQuery(el).summernote('code',html);}else{el.value=html;}
  preview.innerHTML=html;preview.hidden=false;
  root.scrollIntoView({behavior:'smooth',block:'start'});
  alert('Description Editor-এ ডিজাইন বসানো হয়েছে। স্থায়ীভাবে সংরক্ষণ করতে নিচের “ব্যাচ সংরক্ষণ” বাটনে ক্লিক করুন।');
 });
});
})();
