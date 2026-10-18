{{--
  Live Education BD — Admin Daily Update, Step 7: Comment Management (DEMO).
  File: resources/views/admin/daily-update-comments.blade.php
  Safe with existing Admin dashboard layout. No database writes, API calls, or new routes.
  All actions are browser-only and reset when this page is refreshed.
--}}
@extends('dashboard')
@section('title', 'Daily Update Comments | Live Education BD')
@section('content')
<style>
.lebd-du-comments{--cm-ink:#183b63;--cm-muted:#7289a6;--cm-edge:#e0e9f5;--cm-blue:#176ce8;max-width:1460px;padding:16px 15px 38px;margin:0 auto;color:var(--cm-ink);font-family:inherit;font-size:13px}
.lebd-du-comments,.lebd-du-comments *,.lebd-du-comments *::before,.lebd-du-comments *::after{box-sizing:border-box}
.lebd-du-comments button,.lebd-du-comments input,.lebd-du-comments select,.lebd-du-comments textarea{font:inherit}
.lebd-du-comments button{cursor:pointer}.lebd-du-comments a{text-decoration:none}
.lebd-du-comments .cm-head{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:15px}
.lebd-du-comments .cm-head h1{font-size:23px;font-weight:850;color:#15355e;margin:0 0 4px}
.lebd-du-comments .cm-breadcrumb{font-size:11px;color:#8b9db4;margin:0}
.lebd-du-comments .cm-actions{display:flex;align-items:center;gap:8px;flex-wrap:wrap}
.lebd-du-comments .cm-btn{display:inline-flex;align-items:center;justify-content:center;gap:6px;border-radius:9px;border:1px solid #d9e5f3;background:white;color:#365e87;min-height:39px;padding:9px 13px;font-size:12px;font-weight:800;line-height:1.2;white-space:nowrap}
.lebd-du-comments .cm-btn.primary{background:linear-gradient(135deg,#278bf7,#0b59d4);border-color:transparent;color:#fff;box-shadow:0 5px 13px #2879e627}
.lebd-du-comments .cm-btn.green{background:#e8f8ef;border-color:#c6ebd8;color:#088659}
.lebd-du-comments .cm-btn.orange{background:#fff3e4;border-color:#f7dec1;color:#ae6c18}
.lebd-du-comments .cm-btn.red{background:#fff0ef;border-color:#f5d0cf;color:#bd4248}
.lebd-du-comments .cm-note{padding:11px 14px;border:1px solid #f2dca8;border-left:4px solid #e7a52e;background:#fffbf2;border-radius:10px;color:#785b31;font-size:12px;line-height:1.7;margin-bottom:14px}
.lebd-du-comments .cm-alert{display:none;background:#e8f8f0;border:1px solid #c4ead5;border-radius:10px;color:#14734f;padding:11px 14px;margin-bottom:14px;font-weight:750}
.lebd-du-comments .cm-alert.visible{display:block}
.lebd-du-comments .cm-stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;margin-bottom:14px}
.lebd-du-comments .cm-stat{display:flex;align-items:center;gap:12px;border:1px solid var(--cm-edge);border-radius:13px;padding:15px;background:#fff;box-shadow:0 4px 18px #234d8708;min-width:0}
.lebd-du-comments .cm-stat-icon{height:44px;width:44px;flex:none;display:grid;place-items:center;border-radius:12px;font-size:20px;font-weight:850;background:#e8f2ff;color:#116dde}
.lebd-du-comments .cm-stat-icon.orange{background:#fff4e2;color:#cb8a24}.lebd-du-comments .cm-stat-icon.green{background:#e4f8ed;color:#078d5e}.lebd-du-comments .cm-stat-icon.red{background:#fff0ed;color:#c7524a}
.lebd-du-comments .cm-stat-label{font-size:11px;color:#7489a4;font-weight:700}.lebd-du-comments .cm-stat-value{display:block;font-size:26px;font-weight:850;color:#1a3c64;line-height:1.25;font-variant-numeric:tabular-nums}
.lebd-du-comments .cm-panel{background:#fff;border:1px solid var(--cm-edge);border-radius:14px;overflow:hidden;box-shadow:0 5px 18px #234d870b}
.lebd-du-comments .cm-paneltop{display:flex;align-items:center;justify-content:space-between;gap:8px;flex-wrap:wrap;padding:16px 16px 14px}
.lebd-du-comments .cm-paneltop h2{margin:0 0 4px;color:#163c68;font-weight:850;font-size:16px}.lebd-du-comments .cm-paneltop p{margin:0;color:#8b9eb5;font-size:11px}
.lebd-du-comments .cm-demo-tag{background:#edf4ff;border-radius:8px;padding:7px 10px;color:#1469cc;font-size:10px;font-weight:850}
.lebd-du-comments .cm-tabs{display:flex;flex-wrap:wrap;gap:6px;padding:0 15px 13px;border-bottom:1px solid #eef3f9}
.lebd-du-comments .cm-tab{border:1px solid #dce8f5;background:#f9fbfe;border-radius:8px;padding:8px 12px;font-size:12px;font-weight:800;color:#5a7596}
.lebd-du-comments .cm-tab[aria-selected="true"]{background:#e7f1ff;border-color:#a7cbf8;color:#0d61ce}
.lebd-du-comments .cm-filters{display:grid;grid-template-columns:minmax(180px,1.8fr) repeat(2,minmax(150px,1fr));gap:10px;padding:14px 16px}
.lebd-du-comments .cm-input{width:100%;height:41px;border:1px solid #d9e5f4;border-radius:9px;background:#fbfdff;padding:0 12px;color:#264e78;font-size:12px!important;outline:none;min-width:0}
.lebd-du-comments .cm-input:focus,.lebd-du-comments .cm-reply-input:focus{border-color:#3186e9;box-shadow:0 0 0 3px #2377dd17}
.lebd-du-comments .cm-tablewrap{overflow-x:auto}.lebd-du-comments .cm-table{border-collapse:collapse;width:100%;min-width:940px}
.lebd-du-comments .cm-table th{background:#f4f8fd;color:#607c9c;font-size:11px;text-align:left;font-weight:850;white-space:nowrap}
.lebd-du-comments .cm-table td,.lebd-du-comments .cm-table th{border-bottom:1px solid #ecf2f9;padding:13px 13px;vertical-align:middle}
.lebd-du-comments .cm-table tbody tr:last-child td{border-bottom:0}
.lebd-du-comments .cm-author{font-size:12px;color:#204671;font-weight:850;line-height:1.45}.lebd-du-comments .cm-email{font-size:10px;color:#90a1b5;margin-top:4px;overflow-wrap:anywhere}
.lebd-du-comments .cm-message{font-size:12px;color:#2b4d75;line-height:1.6;max-width:295px;white-space:normal;overflow-wrap:anywhere}.lebd-du-comments .cm-question{font-size:11px;color:#557395;max-width:190px;line-height:1.55}
.lebd-du-comments .cm-date{font-size:11px;color:#718bab;white-space:nowrap}
.lebd-du-comments .cm-pill{display:inline-flex;border-radius:7px;padding:6px 9px;font-size:10px;font-weight:850;white-space:nowrap}
.lebd-du-comments .cm-pill.pending{background:#fff0da;color:#a96e14}.lebd-du-comments .cm-pill.approved{background:#e1f7eb;color:#068758}.lebd-du-comments .cm-pill.rejected{background:#ffeded;color:#bc4647}
.lebd-du-comments .cm-rowact{display:flex;align-items:center;gap:5px;white-space:nowrap}
.lebd-du-comments .cm-action{padding:7px 9px;line-height:1.1;border-radius:7px;font-size:11px;font-weight:850;border:1px solid #d8e6f5;color:#186bc9;background:#ecf5ff}
.lebd-du-comments .cm-action.approve{background:#e8f8ef;border-color:#c9ead8;color:#0a875e}.lebd-du-comments .cm-action.reject{background:#fff2e6;border-color:#f6ddc0;color:#b5711b}.lebd-du-comments .cm-action.delete{background:#fff0ef;border-color:#f4cece;color:#c04951}
.lebd-du-comments .cm-foot{padding:12px 16px;display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;background:#fcfdff;border-top:1px solid #eef3f9;color:#7790ad;font-size:11px}
.lebd-du-comments .cm-pagination{display:flex;gap:5px;align-items:center}
.lebd-du-comments .cm-page{background:#fff;border:1px solid #dce6f4;border-radius:7px;padding:7px 11px;color:#4d6d94;font-size:11px;font-weight:850}.lebd-du-comments .cm-page:disabled{opacity:.4;cursor:not-allowed}
.lebd-du-comments .cm-empty{text-align:center;padding:32px;color:#7892ac;font-size:12px}
.lebd-du-comments .cm-overlay{position:fixed;inset:0;z-index:100050;background:#0e264db8;display:none;align-items:center;justify-content:center;padding:15px}
.lebd-du-comments .cm-overlay.open{display:flex}
.lebd-du-comments .cm-modal{width:min(610px,100%);max-height:90vh;overflow:auto;background:#fff;border-radius:15px;padding:22px;box-shadow:0 28px 85px #0c1e3c6b}
.lebd-du-comments .cm-modalhead{display:flex;justify-content:space-between;align-items:flex-start;gap:12px;margin-bottom:15px}
.lebd-du-comments .cm-modal h3{margin:0;font-weight:850;color:#173963;font-size:19px}.lebd-du-comments .cm-modal-sub{margin:5px 0 0;font-size:11px;color:#8a9db4}
.lebd-du-comments .cm-close{background:#f3f8ff;border:1px solid #dce6f4;color:#58718b;font-size:20px;border-radius:8px;line-height:1;padding:6px 11px}
.lebd-du-comments .cm-detail{border:1px solid #e1eaf6;border-radius:10px;background:#f9fcff;padding:13px;margin-bottom:12px;color:#345777;font-size:12px;line-height:1.8}
.lebd-du-comments .cm-detail-label{color:#768eaa;font-size:10px;display:block;margin-bottom:3px;font-weight:750}
.lebd-du-comments .cm-reply-label{font-size:12px;color:#315579;font-weight:850;display:block;margin-bottom:7px}
.lebd-du-comments .cm-reply-input{border:1px solid #d8e6f5;border-radius:9px;background:#fff;width:100%;padding:10px 12px;min-height:92px;color:#2d5179;resize:vertical;outline:none;font-size:12px;line-height:1.7}
.lebd-du-comments .cm-reply-count{margin:5px 0 0;color:#8c9eb6;font-size:10px}
.lebd-du-comments .cm-modalfoot{display:flex;align-items:center;justify-content:flex-end;gap:8px;flex-wrap:wrap;padding-top:15px;border-top:1px solid #edf2f9;margin-top:16px}
.lebd-du-comments .cm-confirm-text{font-size:13px;line-height:1.7;color:#456489;margin:6px 0 0}
@media(max-width:960px){.lebd-du-comments .cm-stats{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(max-width:640px){.lebd-du-comments{padding:10px 9px 28px}.lebd-du-comments .cm-head h1{font-size:19px}.lebd-du-comments .cm-stat{padding:11px;gap:8px}.lebd-du-comments .cm-stat-icon{width:36px;height:36px;font-size:17px}.lebd-du-comments .cm-stat-value{font-size:22px}.lebd-du-comments .cm-paneltop{padding:13px 12px}.lebd-du-comments .cm-filters{grid-template-columns:1fr;padding:11px}.lebd-du-comments .cm-tabs{padding:0 11px 11px}.lebd-du-comments .cm-tab{padding:8px 10px}.lebd-du-comments .cm-modal{padding:16px}.lebd-du-comments .cm-modalfoot .cm-btn{flex:1}}
@media(max-width:370px){.lebd-du-comments .cm-stats{grid-template-columns:1fr 1fr}.lebd-du-comments .cm-stat-icon{display:none}}
</style>
<main class="lebd-du-comments" id="lebdDuComments">
    <header class="cm-head">
        <div>
            <h1>◉ Comment Management</h1>
            <p class="cm-breadcrumb">Admin &nbsp;›&nbsp; Daily Update &nbsp;›&nbsp; Comments</p>
        </div>
        <div class="cm-actions">
            <a href="{{ route('admin.daily-update.index') }}" class="cm-btn">← Dashboard</a>
            <a href="{{ route('admin.daily-update.all') }}" class="cm-btn primary">▤ All Updates</a>
        </div>
    </header>

    <div class="cm-note"><strong>Demo Comment Management:</strong> এখানে সব মন্তব্য নমুনা। Approve, Reject, Reply ও Delete শুধু এই পেজে কাজ করে। <strong>Database-এ কোনো তথ্য Save/Update/Delete হয় না।</strong> Page Refresh করলে Demo Data ফিরে আসবে।</div>
    <div class="cm-alert" id="cmAlert" role="status" aria-live="polite"></div>

    <div class="cm-stats" aria-label="Sample comment statistics">
        <div class="cm-stat"><span class="cm-stat-icon">▣</span><div><span class="cm-stat-label">Total Comments</span><strong id="cmTotal" class="cm-stat-value">0</strong></div></div>
        <div class="cm-stat"><span class="cm-stat-icon orange">◴</span><div><span class="cm-stat-label">Pending Review</span><strong id="cmPending" class="cm-stat-value">0</strong></div></div>
        <div class="cm-stat"><span class="cm-stat-icon green">✓</span><div><span class="cm-stat-label">Approved</span><strong id="cmApproved" class="cm-stat-value">0</strong></div></div>
        <div class="cm-stat"><span class="cm-stat-icon red">✕</span><div><span class="cm-stat-label">Rejected</span><strong id="cmRejected" class="cm-stat-value">0</strong></div></div>
    </div>

    <section class="cm-panel" aria-label="All demo daily update comments">
        <div class="cm-paneltop">
            <div><h2>Daily Update Comments</h2><p>শিক্ষার্থীর নাম, প্রশ্ন, মন্তব্য, সময় ও Moderation Actions</p></div>
            <span class="cm-demo-tag">SAMPLE COMMENTS</span>
        </div>
        <div class="cm-tabs" role="tablist" aria-label="Comment status quick filter">
            <button type="button" class="cm-tab" data-tab="all" role="tab" aria-selected="true">All Comments</button>
            <button type="button" class="cm-tab" data-tab="pending" role="tab" aria-selected="false">Pending</button>
            <button type="button" class="cm-tab" data-tab="approved" role="tab" aria-selected="false">Approved</button>
            <button type="button" class="cm-tab" data-tab="rejected" role="tab" aria-selected="false">Rejected</button>
        </div>
        <div class="cm-filters">
            <input id="cmSearch" class="cm-input" type="search" placeholder="শিক্ষার্থী, প্রশ্ন বা মন্তব্য খুঁজুন..." aria-label="Search comments">
            <select class="cm-input" id="cmCategory" aria-label="Filter by category"><option value="all">All Categories</option><option value="কারেন্ট অ্যাফেয়ার্স">কারেন্ট অ্যাফেয়ার্স</option><option value="মাসিক আপডেট">মাসিক আপডেট</option><option value="সাধারণ জ্ঞান">সাধারণ জ্ঞান</option><option value="আন্তর্জাতিক">আন্তর্জাতিক</option></select>
            <select class="cm-input" id="cmSort" aria-label="Sort comments"><option value="newest">Newest First</option><option value="oldest">Oldest First</option><option value="student">Student Name (A–Z)</option></select>
        </div>
        <div class="cm-tablewrap">
            <table class="cm-table">
                <thead><tr><th>Student</th><th>Comment</th><th>Related Question</th><th>Date</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody id="cmRows"></tbody>
            </table>
        </div>
        <p class="cm-empty" id="cmEmpty" hidden>এই Filter অনুযায়ী কোনো Demo Comment পাওয়া যায়নি।</p>
        <div class="cm-foot"><span id="cmResultText">Showing 0 sample comments</span><div class="cm-pagination"><button class="cm-page" id="cmPrev" type="button">← Prev</button><span id="cmPageText">1 / 1</span><button class="cm-page" id="cmNext" type="button">Next →</button></div></div>
    </section>

    <div class="cm-overlay" id="cmReplyOverlay" role="presentation">
        <section class="cm-modal" role="dialog" aria-modal="true" aria-labelledby="cmReplyTitle" tabindex="-1">
            <div class="cm-modalhead"><div><h3 id="cmReplyTitle">View & Reply</h3><p class="cm-modal-sub">Demo-only reply. Student will not receive a notification.</p></div><button type="button" class="cm-close" data-close-modal="reply" aria-label="Close dialog">×</button></div>
            <div class="cm-detail"><span class="cm-detail-label">Student</span><strong id="cmDetailStudent"></strong></div>
            <div class="cm-detail"><span class="cm-detail-label">Question</span><span id="cmDetailQuestion"></span></div>
            <div class="cm-detail"><span class="cm-detail-label">Comment</span><span id="cmDetailComment"></span></div>
            <label for="cmReplyInput" class="cm-reply-label">Admin Reply (Demo)</label>
            <textarea class="cm-reply-input" id="cmReplyInput" maxlength="1000" placeholder="এখানে উত্তর লিখুন..."></textarea>
            <p class="cm-reply-count" id="cmReplyCount">0 / 1000 characters</p>
            <div class="cm-modalfoot"><button type="button" class="cm-btn" data-close-modal="reply">Cancel</button><button type="button" id="cmSaveReply" class="cm-btn primary">Save Demo Reply</button></div>
        </section>
    </div>

    <div class="cm-overlay" id="cmDeleteOverlay" role="presentation">
        <section class="cm-modal" role="dialog" aria-modal="true" aria-labelledby="cmDeleteTitle" tabindex="-1" style="max-width:440px">
            <div class="cm-modalhead"><div><h3 id="cmDeleteTitle">Delete Demo Comment?</h3><p class="cm-modal-sub">This action is not saved to the server.</p></div><button type="button" class="cm-close" data-close-modal="delete" aria-label="Close dialog">×</button></div>
            <p class="cm-confirm-text" id="cmDeleteMessage">আপনি কি নিশ্চিত এই Demo Comment সরাতে চান?</p>
            <div class="cm-modalfoot"><button type="button" class="cm-btn" data-close-modal="delete">Cancel</button><button type="button" id="cmConfirmDelete" class="cm-btn red">Delete Demo Comment</button></div>
        </section>
    </div>
</main>
<script>
(function(){
    'use strict';
    const root = document.getElementById('lebdDuComments');
    if (!root) return;
    const byId = id => root.querySelector('#' + id);
    const sample = [
        {id:108, student:'নুসরাত জাহান', email:'student108@example.test', category:'কারেন্ট অ্যাফেয়ার্স', question:'বাংলাদেশের রাজধানীর নাম কী?', text:'প্রশ্নটির সঠিক উত্তর ঢাকা। বিস্তারিত ব্যাখ্যা দিলে ভালো হয়।', date:'2026-10-13 10:15', status:'pending', reply:''},
        {id:107, student:'মাহিন ইসলাম', email:'student107@example.test', category:'মাসিক আপডেট', question:'অক্টোবর মাসের গুরুত্বপূর্ণ দিবস কোনগুলো?', text:'এই টপিকের আরও কয়েকটি প্রশ্ন যোগ করা যাবে?', date:'2026-10-13 09:55', status:'pending', reply:''},
        {id:106, student:'রিমা আক্তার', email:'student106@example.test', category:'সাধারণ জ্ঞান', question:'জাতিসংঘের সদর দপ্তর কোথায়?', text:'খুব সুন্দরভাবে ব্যাখ্যা করা হয়েছে।', date:'2026-10-12 18:30', status:'approved', reply:'ধন্যবাদ!'},
        {id:105, student:'আরিফুল হক', email:'student105@example.test', category:'কারেন্ট অ্যাফেয়ার্স', question:'বাংলাদেশের জাতীয় ফুল কোনটি?', text:'শাপলা কি সঠিক উত্তর?', date:'2026-10-12 16:10', status:'approved', reply:''},
        {id:104, student:'সাব্বির রহমান', email:'student104@example.test', category:'আন্তর্জাতিক', question:'বিশ্ব স্বাস্থ্য সংস্থার সদর দপ্তর কোথায়?', text:'এই প্রশ্নটি আবার যাচাই করুন, অনুগ্রহ করে।', date:'2026-10-11 11:20', status:'pending', reply:''},
        {id:103, student:'তানজিলা নাহার', email:'student103@example.test', category:'মাসিক আপডেট', question:'মাসিক সাম্প্রতিক তথ্য', text:'অপ্রাসঙ্গিক পরীক্ষামূলক মন্তব্য।', date:'2026-10-11 08:40', status:'rejected', reply:''},
        {id:102, student:'ইয়াসিন আহমেদ', email:'student102@example.test', category:'সাধারণ জ্ঞান', question:'পৃথিবীর বৃহত্তম মহাসাগর কোনটি?', text:'উত্তর ও ব্যাখ্যাটি উপকারী। ধন্যবাদ।', date:'2026-10-10 22:30', status:'approved', reply:''},
        {id:101, student:'ফারিহা রহমান', email:'student101@example.test', category:'কারেন্ট অ্যাফেয়ার্স', question:'বাংলাদেশের জাতীয় সংসদ ভবন কোথায়?', text:'এখানে আরও বিস্তারিত ব্যাখ্যা দিন।', date:'2026-10-10 19:05', status:'pending', reply:''}
    ];
    let comments = sample.map(item=>({...item}));
    let tab = 'all', page = 1, activeId = null, deleteId = null;
    const pageSize = 6;
    const statuses = {pending:'Pending', approved:'Approved', rejected:'Rejected'};
    const elt = (tag, className, txt) => {
        const element = document.createElement(tag);
        if (className) element.className = className;
        if (txt !== undefined) element.textContent = String(txt);
        return element;
    };
    function notify(message){
        const node=byId('cmAlert');node.textContent=message;node.classList.add('visible');
    }
    function filtered(){
        const needle=byId('cmSearch').value.trim().toLocaleLowerCase();
        const cat=byId('cmCategory').value;
        const result=comments.filter(c=>(tab==='all'||c.status===tab)&&(cat==='all'||cat===c.category)&&[c.student,c.email,c.category,c.question,c.text,c.reply].join(' ').toLocaleLowerCase().includes(needle));
        const sort=byId('cmSort').value;
        result.sort((a,b)=>sort==='student'?a.student.localeCompare(b.student,'bn'):sort==='oldest'?a.id-b.id:b.id-a.id);
        return result;
    }
    function updateCounts(){
        byId('cmTotal').textContent=comments.length;
        ['pending','approved','rejected'].forEach(s=>byId('cm'+s.charAt(0).toUpperCase()+s.slice(1)).textContent=comments.filter(c=>c.status===s).length);
    }
    function actionBtn(text,action,id,kind){
        const button=elt('button','cm-action'+(kind?' '+kind:''),text);
        button.type='button';button.dataset.action=action;button.dataset.id=String(id);
        return button;
    }
    function render(){
        updateCounts();const items=filtered();const pages=Math.max(1,Math.ceil(items.length/pageSize));page=Math.min(page,pages);
        byId('cmRows').replaceChildren();
        items.slice((page-1)*pageSize,page*pageSize).forEach(c=>{
            const tr=elt('tr');
            const student=elt('td'),author=elt('div','cm-author',c.student),email=elt('div','cm-email',c.email);
            student.append(author,email);
            const message=elt('td');message.append(elt('div','cm-message',c.text));
            if(c.reply){message.append(elt('div','cm-email','↳ Admin Reply (Demo): '+c.reply));}
            const question=elt('td');question.append(elt('div','cm-question',c.question),elt('div','cm-email',c.category));
            const date=elt('td','cm-date',c.date);
            const status=elt('td');status.append(elt('span','cm-pill '+c.status,statuses[c.status]));
            const actions=elt('td'),bar=elt('div','cm-rowact');
            bar.append(actionBtn('View / Reply','reply',c.id,''));
            if(c.status!=='approved')bar.append(actionBtn('✓','approve',c.id,'approve'));
            if(c.status!=='rejected')bar.append(actionBtn('✕','reject',c.id,'reject'));
            bar.append(actionBtn('Delete','delete',c.id,'delete'));actions.append(bar);
            tr.append(student,message,question,date,status,actions);byId('cmRows').append(tr);
        });
        byId('cmEmpty').hidden=items.length!==0;
        byId('cmResultText').textContent='Showing '+(items.length?((page-1)*pageSize+1):0)+'–'+Math.min(page*pageSize,items.length)+' of '+items.length+' filtered demo comments';
        byId('cmPageText').textContent=page+' / '+pages;
        byId('cmPrev').disabled=page===1;byId('cmNext').disabled=page===pages;
        root.querySelectorAll('[data-tab]').forEach(btn=>btn.setAttribute('aria-selected',btn.dataset.tab===tab?'true':'false'));
    }
    function setModal(name,open){
        const overlay=byId(name==='reply'?'cmReplyOverlay':'cmDeleteOverlay');overlay.classList.toggle('open',open);
        if(open)overlay.querySelector('.cm-modal').focus();
    }
    root.addEventListener('click',event=>{
        const button=event.target.closest('button');if(!button||!root.contains(button))return;
        if(button.dataset.tab){tab=button.dataset.tab;page=1;render();return;}
        if(button.dataset.closeModal){setModal(button.dataset.closeModal,false);return;}
        const action=button.dataset.action;const id=Number(button.dataset.id);
        const comment=comments.find(c=>c.id===id);
        if(!comment)return;
        if(action==='approve'||action==='reject'){
            comment.status=action==='approve'?'approved':'rejected';notify('Demo: Comment #'+id+' marked '+statuses[comment.status]+'. এটি Database-এ Save হয়নি।');render();
        }
        if(action==='reply'){
            activeId=id;byId('cmDetailStudent').textContent=comment.student+' ('+comment.email+')';
            byId('cmDetailQuestion').textContent=comment.question+' · '+comment.category;
            byId('cmDetailComment').textContent=comment.text;
            byId('cmReplyInput').value=comment.reply;byId('cmReplyCount').textContent=comment.reply.length+' / 1000 characters';
            setModal('reply',true);
        }
        if(action==='delete'){
            deleteId=id;byId('cmDeleteMessage').textContent='Demo Comment #'+id+' ('+comment.student+') কি এই পেজ থেকে সরিয়ে দেবেন?';setModal('delete',true);
        }
    });
    ['cmSearch','cmCategory','cmSort'].forEach(id=>{
        byId(id).addEventListener(id==='cmSearch'?'input':'change',()=>{page=1;render();});
    });
    byId('cmPrev').addEventListener('click',()=>{if(page>1){page--;render();}});
    byId('cmNext').addEventListener('click',()=>{if(page<Math.ceil(filtered().length/pageSize)){page++;render();}});
    byId('cmReplyInput').addEventListener('input',()=>{byId('cmReplyCount').textContent=byId('cmReplyInput').value.length+' / 1000 characters';});
    byId('cmSaveReply').addEventListener('click',()=>{
        const comment=comments.find(c=>c.id===activeId);if(!comment)return;
        const reply=byId('cmReplyInput').value.trim();
        if(!reply){byId('cmReplyInput').focus();notify('উত্তর লিখুন, তারপর Demo Reply Save করুন।');return;}
        comment.reply=reply;setModal('reply',false);notify('Demo Reply saved in this browser page only. কোনো Student-কে পাঠানো হয়নি।');render();
    });
    byId('cmConfirmDelete').addEventListener('click',()=>{
        const id=deleteId;if(id===null)return;
        comments=comments.filter(c=>c.id!==id);deleteId=null;setModal('delete',false);notify('Demo Comment #'+id+' এই পেজ থেকে সরানো হয়েছে। Database অপরিবর্তিত।');render();
    });
    ['cmReplyOverlay','cmDeleteOverlay'].forEach(id=>byId(id).addEventListener('click',event=>{if(event.target===byId(id))setModal(id==='cmReplyOverlay'?'reply':'delete',false);}));
    document.addEventListener('keydown',event=>{if(event.key==='Escape'){setModal('reply',false);setModal('delete',false);}});
    render();
})();
</script>
@endsection
