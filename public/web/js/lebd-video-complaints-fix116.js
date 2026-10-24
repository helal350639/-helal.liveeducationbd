document.addEventListener('DOMContentLoaded', function(){
 const modal=document.getElementById('lebdStudentVideoComplaint');if(!modal)return;
 const title=document.getElementById('lebdComplainVideoName');
 const videoId=document.getElementById('lebdComplaintVideoId');
 const reason=document.getElementById('lebdComplaintKind');
 const commentWrap=document.getElementById('lebdComplaintCommentWrap');
 const comment=document.getElementById('lebdComplaintComment');
 const dialog=modal.querySelector('.lebd-complain-dialog');let lastTrigger=null;
 function update(){const needed=reason.value==='other';commentWrap.hidden=!needed;comment.required=needed;if(!needed)comment.value='';}
 function close(){modal.hidden=true;document.body.style.overflow='';if(lastTrigger)lastTrigger.focus();}
 document.querySelectorAll('[data-lebd-complain-video]').forEach(btn=>btn.addEventListener('click',()=>{lastTrigger=btn;videoId.value=btn.dataset.lebdComplainVideo;title.textContent=btn.dataset.lebdComplainTitle||'';reason.value='';comment.value='';update();modal.hidden=false;document.body.style.overflow='hidden';dialog.focus();}));
 reason.addEventListener('change',update);
 modal.querySelectorAll('[data-lebd-close]').forEach(el=>el.addEventListener('click',close));
 document.addEventListener('keydown',event=>{if(event.key==='Escape'&&!modal.hidden)close();});
});
