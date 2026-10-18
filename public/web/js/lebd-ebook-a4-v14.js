(function(){'use strict';
if(window.__lebdEbookPaginatorV17)return;window.__lebdEbookPaginatorV17=true;
let bookPageCounter=0;
function startReader(reader){
  const source=reader.querySelector('[data-ebook-source]'), destination=reader.querySelector('[data-ebook-pages]');
  if(!source||!destination)return;
  const items=Array.from(source.querySelectorAll(':scope > [data-page-item]'));
  if(items.length===0){items.push(Object.assign(document.createElement('article'),{className:'lebd-book-item',textContent:'এই অধ্যায়ে এখনো কোনো প্রশ্ন বা লেখা যোগ করা হয়নি।'}));}
  reader.dataset.rendering='true';destination.innerHTML='';
  const editorList=reader.querySelector('[data-editor-list]');
  if(editorList){items.forEach(item=>editorList.appendChild(item.cloneNode(true)));}let pageNumber=0,sheet,columns,left,right,key,answers,active,questionKeys=[];
  function newPage(){
    pageNumber++;bookPageCounter++;questionKeys=[];
    sheet=document.createElement('section');sheet.className='lebd-a4-sheet';sheet.setAttribute('aria-label','A4 page '+pageNumber);
    sheet.innerHTML='<div class="lebd-a4-title"></div><div class="lebd-a4-columns"><div class="lebd-a4-column"></div><div class="lebd-a4-column"></div></div><div class="lebd-a4-answers"><strong>সঠিক উত্তর</strong><div class="lebd-a4-answer-grid"></div></div><div class="lebd-a4-page-no"></div>';
    sheet.querySelector('.lebd-a4-title').textContent=(reader.dataset.chapterTitle||'');
    sheet.querySelector('.lebd-a4-page-no').textContent=String(bookPageCounter);
    columns=sheet.querySelector('.lebd-a4-columns');[left,right]=columns.children;active=left;
    answers=sheet.querySelector('.lebd-a4-answers');key=sheet.querySelector('.lebd-a4-answer-grid');destination.appendChild(sheet);
  }
  function appendAnswer(item){const number=item.dataset.qNumber,answer=item.dataset.qAnswer;if(!number)return;const entry=document.createElement('span');entry.textContent='Q-'+number+') '+(answer||'—');key.appendChild(entry);}
  function fits(){return active.scrollHeight <= active.clientHeight + 1;}
  newPage();
  for(const item of items){
    const clone=item.cloneNode(true);clone.querySelectorAll('[data-editor-controls],.lebd-a4-edit-details').forEach(node=>node.remove());
    active.appendChild(clone);
    if(!fits()){
      active.removeChild(clone);
      if(active===left){active=right;active.appendChild(clone);if(!fits()){active.removeChild(clone);newPage();active.appendChild(clone);}}
      else{newPage();active.appendChild(clone);}
    }
    // Keep exceptionally tall content visible and indicate that it needs editing.
    if(!fits()) {clone.classList.add('lebd-oversize');clone.style.fontSize='9pt';clone.style.lineHeight='1.25';}
    appendAnswer(item);
  }
  reader.dataset.rendering='false';
  reader.dataset.pageCount=String(pageNumber);
  const chapterNode=reader.closest('[data-chapter-id]');
  if(chapterNode)chapterNode.dataset.pageCount=String(pageNumber);
  if(window.com&&com.wiris&&com.wiris.js&&com.wiris.js.JsPluginViewer){try{com.wiris.js.JsPluginViewer.parseElement(destination);}catch(e){}}
}
function bd(n){return String(n).replace(/[0-9]/g,c=>'০১২৩৪৫৬৭৮৯'[Number(c)]);}
function updateChapterRanges(){
 let cursor=1;
 const chapters=Array.from(document.querySelectorAll('[data-chapter-id]'));
 chapters.forEach(section=>{
  const count=Math.max(1,Number(section.dataset.pageCount)||section.querySelectorAll('.lebd-a4-sheet').length||1);
  const start=cursor,end=cursor+count-1,id=section.dataset.chapterId;
  document.querySelectorAll('[data-toc-range="'+id+'"]').forEach(el=>{el.textContent=bd(start)+(end>start?'–'+bd(end):'');el.dataset.start=String(start);el.dataset.end=String(end);});
  section.querySelectorAll('.lebd-a4-page-no').forEach((el,i)=>{el.textContent=bd(start+i);});
  cursor=end+1;
 });
}
let layoutRunning=false;
function boot(){
 if(layoutRunning)return;
 layoutRunning=true;
 bookPageCounter=0;
 document.querySelectorAll('[data-ebook-reader]').forEach(startReader);
 updateChapterRanges();
 layoutRunning=false;
}
if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',boot,{once:true});else boot();
// Images and webfonts can change page heights after the initial layout.
window.addEventListener('load',boot,{once:true});
if(document.fonts&&document.fonts.ready)document.fonts.ready.then(boot).catch(()=>{});
window.addEventListener('beforeprint',boot);
})();
