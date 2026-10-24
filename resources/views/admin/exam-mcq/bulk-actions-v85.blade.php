{{-- LEBD-3 v85 add-on. No existing picker markup or styles are replaced. --}}
<script>
(function () {
  function init() {
    const root=document.querySelector('.emcq-wrap') || document;
    const forms=Array.from(root.querySelectorAll('form'));
    const filterForm=forms.find(f => f.method.toLowerCase()==='get' && f.querySelector('[name="course_id"]'));
    const addForm=forms.find(f => f.method.toLowerCase()==='post' && f.querySelector('[name="question_ids[]"]'));
    if(!filterForm || !addForm || document.getElementById('emcq-v85-actions')) return;
    const searchBtn=Array.from(filterForm.querySelectorAll('button')).find(b=>/search questions/i.test(b.textContent));
    if(!searchBtn) return;
    const actionBox=document.createElement('span');
    actionBox.id='emcq-v85-actions';
    actionBox.style.cssText='display:inline-flex;gap:8px;align-items:center;flex-wrap:wrap;margin-left:8px;';
    const done=document.createElement('button');
    done.type='button';done.className='btn btn-success';done.textContent='Done';
    actionBox.appendChild(done);
    searchBtn.parentNode.insertBefore(actionBox,searchBtn.nextSibling);

    const bulkRow=document.createElement('div');bulkRow.id='emcq-v85-bulk-row';
    bulkRow.style.cssText='display:flex;align-items:center;gap:12px;flex-wrap:wrap;margin:10px 0;';
    const current=document.createElement('button');current.type='button';current.className='btn btn-primary';current.textContent='Add Current 25';
    const allLabel=document.createElement('label');allLabel.style.cssText='display:inline-flex;gap:6px;align-items:center;margin:0;cursor:pointer;font-weight:600;';
    const all=document.createElement('input');all.type='checkbox';all.id='emcq-v85-all-results';
    allLabel.appendChild(all);allLabel.appendChild(document.createTextNode(' Select All Search Results (all pages)'));
    bulkRow.appendChild(current);bulkRow.appendChild(allLabel);
    addForm.insertBefore(bulkRow,addForm.firstChild.nextSibling);

    const checks=()=>Array.from(addForm.querySelectorAll('input[name="question_ids[]"]'));
    all.addEventListener('change',()=>checks().forEach(c=>{if(!c.disabled)c.checked=all.checked;}));
    function bulk(mode) {
      if(mode==='all' && !confirm('Add ALL matching search results from every page?')) return;
      if(mode==='page' && !confirm('Add all matching questions on this page (up to 25)?')) return;
      const form=document.createElement('form');form.method='POST';
      form.action=@json(route('admin.exam-mcq.bulk-v85',['kind'=>$kind,'categoryId'=>$category->id]));
      form.style.display='none';
      const payload=new FormData(filterForm);
      const context=new FormData(addForm);
      for(const key of ['subject_id','chapter_id']) if(context.has(key)) payload.set(key,context.get(key));
      payload.set('_token', @json(csrf_token()));
      payload.set('mode',mode);payload.set('page',new URLSearchParams(location.search).get('page') || '1');
      payload.forEach((value,key)=>{const input=document.createElement('input');input.type='hidden';input.name=key;input.value=value;form.appendChild(input);});
      document.body.appendChild(form);form.submit();
    }
    current.addEventListener('click',()=>bulk('page'));
    done.addEventListener('click',()=>{
      if(all.checked) return bulk('all');
      if(!checks().some(c=>c.checked&&!c.disabled)) {alert('Select a question, Add Current 25, or Select All Search Results.');return;}
      addForm.requestSubmit ? addForm.requestSubmit() : addForm.submit();
    });
  }
  if(document.readyState==='loading') document.addEventListener('DOMContentLoaded',init);
  else init();
})();
</script>
