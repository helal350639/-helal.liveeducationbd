@if(request()->query('ebook_kind') && request()->query('ebook_chapter'))
@php
    $ebookKind = (string) request()->query('ebook_kind');
    $ebookChapter = (int) request()->query('ebook_chapter');
    $ebookModeValid = in_array($ebookKind, ['department','non-department'], true) && $ebookChapter > 0;
@endphp
@if($ebookModeValid)
<style>
#lebd-ebook-bar{background:#eaf5ff;border:1px solid #a8d3fa;border-radius:9px;padding:12px 16px;margin:0 0 14px;position:sticky;top:0;z-index:50;box-shadow:0 4px 16px #0000000b}
#lebd-ebook-bar .btn{margin:2px}#lebd-ebook-bar .count{display:inline-block;padding:7px 12px;font-weight:700;color:#114c7d}
#lebd-ebook-bar .hint{font-size:12px;color:#59718a;display:block}
#lebd-ebook-bar .btn[disabled]{opacity:.5}
</style>
<script>
(function(){
    'use strict';
    const key = 'lebd_ebook_chapter_selection_{{ $ebookKind }}_{{ $ebookChapter }}';
    const csrf = @json(csrf_token());
    const attachUrl = @json(route('admin.ebook.attach', ['kind' => $ebookKind, 'chapter' => $ebookChapter]));
    const editorUrl = @json(route('admin.ebook.editor', ['kind' => $ebookKind, 'chapter' => $ebookChapter]));
    const limit = 100;
    function selected(){
        try{const a=JSON.parse(sessionStorage.getItem(key)||'[]');return Array.isArray(a)?a.map(Number).filter(x=>Number.isInteger(x)&&x>0):[];}
        catch(e){return [];}
    }
    function save(a){sessionStorage.setItem(key,JSON.stringify([...new Set(a)]));}
    function currentBoxes(){return Array.from(document.querySelectorAll('#question_area input.check[type="checkbox"]'));}
    function bar(){return document.getElementById('lebd-ebook-bar');}
    function draw(){
        const set=new Set(selected());
        currentBoxes().forEach(el=>{el.checked=set.has(Number(el.value));});
        const all=document.getElementById('lebd-ebook-all');
        if(all){const bs=currentBoxes();all.checked=!!bs.length&&bs.every(x=>set.has(Number(x.value)));all.indeterminate=bs.some(x=>set.has(Number(x.value)))&&!all.checked;}
        const n=document.getElementById('lebd-ebook-number');if(n)n.textContent=set.size;
        const done=document.getElementById('lebd-ebook-done');if(done)done.disabled=!set.size;
    }
    function makeBar(){
        if(bar())return;
        const area=document.getElementById('question_area');if(!area)return;
        const el=document.createElement('div');el.id='lebd-ebook-bar';
        el.innerHTML='<strong>📘 E-Book Chapter — Question Selection</strong><div style="margin-top:8px"><label style="margin-right:10px"><input id="lebd-ebook-all" type="checkbox"> Select All (this page)</label><span class="count">Selected: <span id="lebd-ebook-number">0</span> / 100</span><button type="button" class="btn btn-default btn-sm" id="lebd-ebook-clear">Clear</button><button type="button" class="btn btn-success" id="lebd-ebook-done">✓ Done — Add Selected Questions</button><a class="btn btn-default btn-sm" href="'+editorUrl+'">Back to Chapter</a></div><span class="hint">একাধিক Page থেকে প্রশ্ন বাছাই করা যাবে। Done চাপলে নির্বাচিত সব প্রশ্ন অধ্যায়ে যুক্ত হবে।</span>';
        area.parentNode.insertBefore(el,area);
        document.getElementById('lebd-ebook-all').addEventListener('change',ev=>{
            const set=new Set(selected());const ids=currentBoxes().map(x=>Number(x.value));
            if(ev.target.checked){if(set.size+ids.filter(x=>!set.has(x)).length>limit){alert('একবারে সর্বোচ্চ ১০০টি প্রশ্ন নির্বাচন করতে পারবেন।');ev.target.checked=false;return;}ids.forEach(x=>set.add(x));}
            else ids.forEach(x=>set.delete(x));save([...set]);draw();
        });
        document.getElementById('lebd-ebook-clear').addEventListener('click',()=>{save([]);draw();});
        document.getElementById('lebd-ebook-done').addEventListener('click',()=>{
            const ids=selected();if(!ids.length)return;
            const form=document.createElement('form');form.method='POST';form.action=attachUrl;
            function field(name,value){const i=document.createElement('input');i.type='hidden';i.name=name;i.value=value;form.appendChild(i);}
            field('_token',csrf);ids.forEach(id=>field('question_ids[]',id));
            document.body.appendChild(form);sessionStorage.removeItem(key);form.submit();
        });
    }
    // Capture prevents the legacy count(this) from sending to paper/book APIs in E-Book mode.
    document.addEventListener('click',function(e){
        const el=e.target;if(!(el instanceof HTMLInputElement) || !el.matches('#question_area input.check[type="checkbox"]'))return;
        e.preventDefault();e.stopPropagation();e.stopImmediatePropagation();
        const set=new Set(selected()),id=Number(el.value);
        if(set.has(id))set.delete(id);
        else{if(set.size>=limit){alert('একবারে সর্বোচ্চ ১০০টি প্রশ্ন নির্বাচন করা যাবে।');draw();return;}set.add(id);}
        save([...set]);draw();
    },true);
    document.addEventListener('DOMContentLoaded',function(){
        makeBar();draw();
        const area=document.getElementById('question_area');
        if(area)new MutationObserver(()=>draw()).observe(area,{childList:true});
    });
})();
</script>
@endif
@endif
