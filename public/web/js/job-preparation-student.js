document.addEventListener('click', async function(e){
const toggle=e.target.closest('[data-jp-toggle]');if(toggle){const box=document.getElementById(toggle.dataset.jpToggle);if(box){box.hidden=!box.hidden;toggle.setAttribute('aria-expanded',String(!box.hidden));}return;}
const focus=e.target.closest('[data-jp-focus]');if(focus){document.getElementById(focus.dataset.jpFocus)?.focus();return;}
const share=e.target.closest('[data-jp-share]');if(share){const url=share.dataset.jpShare,title=share.dataset.jpTitle;if(navigator.share){try{await navigator.share({title,url});return;}catch(err){if(err.name==='AbortError')return;}}
try{await navigator.clipboard.writeText(url);alert('Question link copied');}catch(err){window.prompt('Copy question link:',url);} }
});
