{{-- Live Education BD — Step 15, real database-backed All Updates. Uses current Admin layout only. --}}
@extends('dashboard')
@section('title', 'All Daily Updates | Live Education BD')
@section('content')
<style>
.lebd-du-all{--du-navy:#17375f;--du-blue:#1168e8;--du-muted:#7087a2;--du-border:#e1eaf6;max-width:1460px;margin:0 auto;padding:16px 16px 40px;font:inherit;font-size:13px;color:var(--du-navy)}
.lebd-du-all,.lebd-du-all *,.lebd-du-all *::before,.lebd-du-all *::after{box-sizing:border-box}
.lebd-du-all button,.lebd-du-all select,.lebd-du-all input,.lebd-du-all textarea{font:inherit}
.lebd-du-all button{cursor:pointer}.lebd-du-all a{text-decoration:none}
.lebd-du-all .da-head{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:15px}
.lebd-du-all .da-head h1{margin:0 0 4px;font-size:23px;font-weight:850;color:#153760}
.lebd-du-all .da-crumb{font-size:11px;color:#8298b1;margin:0}
.lebd-du-all .da-actions{display:flex;gap:8px;flex-wrap:wrap;align-items:center}
.lebd-du-all .da-btn{display:inline-flex;align-items:center;justify-content:center;gap:7px;border:1px solid transparent;border-radius:9px;padding:9px 13px;min-height:38px;font-size:12px;font-weight:800;line-height:1.25;white-space:nowrap}
.lebd-du-all .da-btn.blue{color:#fff;background:linear-gradient(120deg,#258afa,#0d57cd);box-shadow:0 6px 15px #1c72e625}
.lebd-du-all .da-btn.light{color:#335b83;background:#fff;border-color:#dce7f4}
.lebd-du-all .da-btn.danger{background:#d64245;color:#fff}
.lebd-du-all .da-banner{background:#fffbf2;border:1px solid #f3dca5;border-left:4px solid #e6ac30;border-radius:10px;padding:11px 14px;color:#805c21;font-size:12px;line-height:1.65;margin-bottom:15px}
.lebd-du-all .da-statgrid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;margin-bottom:15px}
.lebd-du-all .da-stat{display:flex;align-items:center;gap:13px;padding:15px;background:#fff;border:1px solid var(--du-border);border-radius:13px;box-shadow:0 6px 18px #254f8507;min-width:0}
.lebd-du-all .da-icon{display:grid;place-items:center;flex:none;width:46px;height:46px;border-radius:12px;background:#e7f0ff;color:#1968d6;font-size:21px;font-weight:850}
.lebd-du-all .da-icon.green{color:#069764;background:#e4f8ef}.lebd-du-all .da-icon.orange{color:#d48b09;background:#fff4db}.lebd-du-all .da-icon.purple{color:#804bce;background:#f1eaff}
.lebd-du-all .da-stat span{display:block;font-size:11px;color:#778da8;font-weight:700}.lebd-du-all .da-stat strong{display:block;font-size:26px;font-weight:850;color:#183e6c;line-height:1.35;font-variant-numeric:tabular-nums}
.lebd-du-all .da-panel{border:1px solid var(--du-border);border-radius:14px;background:#fff;box-shadow:0 6px 20px #244f850a;overflow:hidden}
.lebd-du-all .da-paneltop{display:flex;gap:10px;align-items:center;justify-content:space-between;padding:16px 16px 13px;flex-wrap:wrap}
.lebd-du-all .da-paneltop h2{font-size:16px;color:#183b63;font-weight:850;margin:0 0 3px}.lebd-du-all .da-paneltop p{font-size:11px;color:#8094ae;margin:0}
.lebd-du-all .da-demo{font-size:10px;font-weight:800;background:#edf4ff;color:#1a68c7;border-radius:8px;padding:6px 9px}
.lebd-du-all .da-filterbox{display:grid;grid-template-columns:minmax(180px,2fr) repeat(4,minmax(115px,1fr));gap:9px;padding:0 16px 15px}
.lebd-du-all .da-field{height:40px;width:100%;border:1px solid #d7e5f5;background:#fbfdff;border-radius:8px;padding:0 11px;font-size:12px!important;color:#315276;min-width:0;outline:none}
.lebd-du-all .da-field:focus,.lebd-du-all textarea:focus{border-color:#2587f3;box-shadow:0 0 0 3px #2587f21a}
.lebd-du-all .da-tablebox{overflow-x:auto}.lebd-du-all .da-table{width:100%;min-width:950px;border-collapse:collapse}
.lebd-du-all .da-table thead{background:#f3f7fc}.lebd-du-all .da-table th{color:#5e7593;font-size:11px;font-weight:850;text-align:left;white-space:nowrap}
.lebd-du-all .da-table td,.lebd-du-all .da-table th{padding:13px 12px;border-bottom:1px solid #edf2f8;vertical-align:middle}
.lebd-du-all .da-table tbody tr:last-child td{border-bottom:none}
.lebd-du-all .da-id{font-weight:800;color:#7991ac}.lebd-du-all .da-question{font-weight:800;color:#1b416c;max-width:320px;line-height:1.5}.lebd-du-all .da-sub{display:block;font-size:10px;color:#89a0b7;font-weight:550;margin-top:3px}
.lebd-du-all .da-badge{display:inline-flex;border-radius:7px;padding:5px 9px;font-size:10px;font-weight:850;white-space:nowrap}.lebd-du-all .da-badge.published{background:#e0f8ed;color:#08865a}.lebd-du-all .da-badge.draft{background:#fff0ec;color:#ce6240}
.lebd-du-all .da-cat{color:#537497;font-size:11px}.lebd-du-all .da-dt{color:#5e7896;font-size:11px;white-space:nowrap}
.lebd-du-all .da-rowactions{display:flex;gap:5px;flex-wrap:nowrap}
.lebd-du-all .da-mini{border:1px solid #d5e4f7;border-radius:7px;background:#eff6ff;color:#1b68cc;font-size:11px;font-weight:800;padding:7px 9px;white-space:nowrap;line-height:1.15}
.lebd-du-all .da-mini.warn{background:#fff5e5;border-color:#f5dba8;color:#a2690e}
.lebd-du-all .da-mini.success{background:#e8f8ef;border-color:#c9ecd9;color:#0c905e}
.lebd-du-all .da-mini.delete{background:#fff0f0;border-color:#ffd5d5;color:#c53d42}
.lebd-du-all .da-empty{padding:32px 15px;color:#708dab;text-align:center;font-size:13px}
.lebd-du-all .da-foot{padding:13px 16px;background:#fcfdff;border-top:1px solid #edf2f8;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;color:#7c92ad;font-size:11px}
.lebd-du-all .da-pages{display:flex;gap:6px;align-items:center}.lebd-du-all .da-page{background:white;border:1px solid #dbe7f5;border-radius:7px;padding:7px 11px;color:#35618f;font-weight:800;font-size:11px}
.lebd-du-all .da-page:disabled{cursor:not-allowed;opacity:.5}
.lebd-du-all .da-toast{display:none;margin-bottom:13px;background:#e9f8f0;border:1px solid #c0ebd3;color:#136e50;border-radius:9px;padding:11px 13px;font-weight:750;font-size:12px}
.lebd-du-all .da-toast.show{display:block}
.lebd-du-all .da-overlay{position:fixed;inset:0;z-index:100020;background:#09264caa;display:none;align-items:center;justify-content:center;padding:12px}
.lebd-du-all .da-overlay.open{display:flex}
.lebd-du-all .da-modal{width:min(680px,100%);max-height:92vh;overflow-y:auto;padding:21px;background:#fff;border-radius:16px;box-shadow:0 30px 85px #08193655;color:#16365d}
.lebd-du-all .da-modalhead{display:flex;justify-content:space-between;align-items:flex-start;gap:12px;margin-bottom:14px}
.lebd-du-all .da-modal h3{font-size:19px;font-weight:850;margin:0;color:#183b66}.lebd-du-all .da-modalnote{font-size:11px;color:#7890a9;margin:4px 0 0}
.lebd-du-all .da-close{border:1px solid #d9e5f4;border-radius:8px;background:#f4f8fe;padding:4px 11px;color:#517195;font-size:20px;line-height:1.2}
.lebd-du-all .da-previewq{font-size:17px;font-weight:850;padding:13px;background:#edf5ff;border-radius:9px;margin-bottom:11px;line-height:1.6}
.lebd-du-all .da-options{display:grid;grid-template-columns:1fr 1fr;gap:9px;margin:10px 0}
.lebd-du-all .da-option{border:1px solid #dce8f5;background:#fbfdff;padding:11px;border-radius:8px;font-size:12px;line-height:1.5}.lebd-du-all .da-option.right{background:#e6f8ee;border-color:#a8dfc3;color:#08754e;font-weight:850}
.lebd-du-all .da-answer{margin:10px 0;background:#eaf3ff;color:#135cae;padding:12px;border-radius:9px;font-weight:800}
.lebd-du-all .da-explanation{font-size:12px;color:#597693;line-height:1.8}
.lebd-du-all .da-editgrid{display:grid;grid-template-columns:1fr 1fr;gap:11px}
.lebd-du-all .da-formfield{display:block;min-width:0}.lebd-du-all .da-formfield.wide{grid-column:1/-1}.lebd-du-all .da-formfield label{display:block;font-weight:850;font-size:11px;color:#3e5e83;margin-bottom:6px}
.lebd-du-all .da-formfield textarea{min-height:80px;resize:vertical;padding:11px;width:100%;border:1px solid #d7e5f5;border-radius:8px;background:#fbfdff;color:#315276;outline:none;font-size:12px}
.lebd-du-all .da-formfield .da-field{height:39px}.lebd-du-all .da-error{display:none;border-radius:8px;padding:10px;background:#fff0ee;color:#b33d35;margin-top:10px;font-size:11px}.lebd-du-all .da-error.show{display:block}
.lebd-du-all .da-modalfoot{display:flex;justify-content:flex-end;gap:9px;flex-wrap:wrap;margin-top:18px;padding-top:13px;border-top:1px solid #ebf1f8}
@media(max-width:1200px){.lebd-du-all .da-filterbox{grid-template-columns:repeat(3,minmax(0,1fr))}.lebd-du-all .da-filterbox input{grid-column:span 2}}
@media(max-width:700px){.lebd-du-all{padding:10px 9px 28px}.lebd-du-all .da-head h1{font-size:19px}.lebd-du-all .da-statgrid{grid-template-columns:repeat(2,minmax(0,1fr));gap:8px}.lebd-du-all .da-stat{gap:8px;padding:11px}.lebd-du-all .da-icon{width:35px;height:35px;font-size:18px}.lebd-du-all .da-stat strong{font-size:21px}.lebd-du-all .da-filterbox{grid-template-columns:1fr 1fr;padding:0 11px 12px}.lebd-du-all .da-filterbox input{grid-column:span 2}.lebd-du-all .da-paneltop{padding:12px}.lebd-du-all .da-editgrid,.lebd-du-all .da-options{grid-template-columns:1fr}.lebd-du-all .da-modal{padding:16px}.lebd-du-all .da-modalfoot .da-btn{flex:1}}
@media(max-width:430px){.lebd-du-all .da-filterbox{grid-template-columns:1fr}.lebd-du-all .da-filterbox input{grid-column:span 1}}
</style>
<style>
.lebd-du-all .da-banner{background:#eef9f4;border-color:#bcead1;border-left-color:#14a372;color:#177149}
.lebd-du-all .da-filterbox{grid-template-columns:minmax(170px,2fr) repeat(4,minmax(105px,1fr)) auto}
.lebd-du-all .da-filterbox .da-btn{width:100%;height:40px}
.lebd-du-all .da-rowactions form{display:inline;margin:0}
.lebd-du-all .da-mini{display:inline-block;text-decoration:none!important;cursor:pointer}
.lebd-du-all .da-mini:disabled{opacity:.5}
.lebd-du-all .da-question{overflow-wrap:anywhere}
.lebd-du-all .da-modal{white-space:normal}
.lebd-du-all .da-overlay[hidden]{display:none!important}
.lebd-du-all .da-empty{padding:28px}
.lebd-du-all .da-page{display:inline-flex;align-items:center;text-decoration:none}
.lebd-du-all .da-page[aria-disabled="true"]{opacity:.45;pointer-events:none}
@media(max-width:1200px){.lebd-du-all .da-filterbox{grid-template-columns:repeat(3,minmax(0,1fr))}.lebd-du-all .da-filterbox input{grid-column:span 2}}
@media(max-width:700px){.lebd-du-all .da-filterbox{grid-template-columns:1fr 1fr}.lebd-du-all .da-filterbox input{grid-column:span 2}}
@media(max-width:430px){.lebd-du-all .da-filterbox{grid-template-columns:1fr}.lebd-du-all .da-filterbox input{grid-column:span 1}}
</style>
<main class="lebd-du-all" id="lebdDuAllUpdates">
    <header class="da-head">
        <div><h1>▤ All Daily Updates</h1><p class="da-crumb">Admin &nbsp;›&nbsp; Daily Update &nbsp;›&nbsp; All Updates</p></div>
        <div class="da-actions">
            <a class="da-btn light" href="{{ route('admin.daily-update.index') }}">← Dashboard</a>
            <a class="da-btn light" href="{{ route('admin.daily-update.categories') }}">▤ Categories</a>
            <a class="da-btn blue" href="{{ route('admin.daily-update.create') }}">＋ Add New Update</a>
        </div>
    </header>

    <div class="da-banner" role="status"><strong>Live Database Connected:</strong> এই তালিকায় <code>lebd_daily_updates</code> Table-এর আসল প্রশ্ন দেখাচ্ছে। Edit, Publish/Unpublish এবং Delete Database-এ কাজ করবে। Student Daily Update Page এবং Comment Management এখনো Demo।</div>
    @if(session('success'))
        <div class="da-toast show" role="status">✓ {{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="da-toast show" style="background:#fff1f1;border-color:#f5c1c1;color:#a33b3b" role="alert">{{ $errors->first() }}</div>
    @endif

    <section class="da-statgrid" aria-label="Actual database counts">
        <div class="da-stat"><span class="da-icon">▣</span><div><span>Total Updates</span><strong>{{ number_format($totalUpdates) }}</strong></div></div>
        <div class="da-stat"><span class="da-icon green">✓</span><div><span>Published</span><strong>{{ number_format($publishedUpdates) }}</strong></div></div>
        <div class="da-stat"><span class="da-icon orange">◴</span><div><span>Draft</span><strong>{{ number_format($draftUpdates) }}</strong></div></div>
        <div class="da-stat"><span class="da-icon purple">◉</span><div><span>Categories Used</span><strong>{{ number_format($usedCategories) }}</strong></div></div>
    </section>

    <section class="da-panel" aria-labelledby="daPanelTitle">
        <div class="da-paneltop"><div><h2 id="daPanelTitle">Daily Update Question List</h2><p>Question, Category, Date, Status ও Database Actions</p></div><span class="da-demo">LIVE DATA</span></div>
        <form class="da-filterbox" method="GET" action="{{ route('admin.daily-update.all') }}" aria-label="Filter updates">
            <input class="da-field" name="search" value="{{ request('search') }}" type="search" placeholder="প্রশ্ন বা Title দিয়ে খুঁজুন..." aria-label="Search updates">
            <select class="da-field" name="category_id" aria-label="Filter category">
                <option value="">All Categories</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" @selected((string)request('category_id') === (string)$category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
            <select class="da-field" name="year" aria-label="Filter year">
                <option value="">All Years</option>
                @foreach($years as $year)
                    <option value="{{ $year }}" @selected((string)request('year') === (string)$year)>{{ $year }}</option>
                @endforeach
            </select>
            <select class="da-field" name="month" aria-label="Filter month">
                <option value="">All Months</option>
                @foreach([1=>'January',2=>'February',3=>'March',4=>'April',5=>'May',6=>'June',7=>'July',8=>'August',9=>'September',10=>'October',11=>'November',12=>'December'] as $num => $label)
                    <option value="{{ $num }}" @selected((string)request('month') === (string)$num)>{{ $label }}</option>
                @endforeach
            </select>
            <select class="da-field" name="status" aria-label="Filter status">
                <option value="">All Status</option>
                <option value="published" @selected(request('status') === 'published')>Published</option>
                <option value="draft" @selected(request('status') === 'draft')>Draft</option>
            </select>
            <button type="submit" class="da-btn blue">⌕ Search</button>
            <a class="da-btn light" href="{{ route('admin.daily-update.all') }}">Reset</a>
        </form>
        <div class="da-tablebox">
            <table class="da-table"><thead><tr><th>ID</th><th>Question</th><th>Category</th><th>Update Date</th><th>Status</th><th>Views</th><th>Actions</th></tr></thead>
                <tbody>
                    @forelse($updates as $update)
                        <tr>
                            <td class="da-id">#{{ $update->id }}</td>
                            <td class="da-question">{{ \Illuminate\Support\Str::limit($update->question, 90) }}<small class="da-sub">{{ $update->topic ?: ($update->title ?? '') }}</small></td>
                            <td class="da-cat">{{ $update->category?->name ?? 'Category unavailable' }}</td>
                            <td class="da-dt">{{ $update->update_date?->format('d M Y') ?? '—' }}</td>
                            <td><span class="da-badge {{ $update->status === 'published' ? 'published' : 'draft' }}">{{ ucfirst($update->status) }}</span></td>
                            <td class="da-dt">{{ number_format($update->view_count ?? 0) }}</td>
                            <td>
                                <div class="da-rowactions">
                                    <button type="button" class="da-mini da-preview-trigger" data-preview="du-preview-{{ $update->id }}">View</button>
                                    <a class="da-mini" href="{{ route('admin.daily-update.edit', $update->id) }}">Edit</a>
                                    <form action="{{ route('admin.daily-update.toggle', $update->id) }}" method="POST" onsubmit="return confirm('এই প্রশ্নের Publish Status পরিবর্তন করতে চান?');">
                                        @csrf @method('PATCH')
                                        <button class="da-mini {{ $update->status === 'published' ? 'warn' : 'success' }}" type="submit">{{ $update->status === 'published' ? 'Unpublish' : 'Publish' }}</button>
                                    </form>
                                    <form action="{{ route('admin.daily-update.destroy', $update->id) }}" method="POST" onsubmit="return confirm('এই প্রশ্নটি Soft Delete করতে চান? এটি সাধারণ তালিকা থেকে সরবে।');">
                                        @csrf @method('DELETE')
                                        <button class="da-mini delete" type="submit">Delete</button>
                                    </form>
                                </div>
                                <div id="du-preview-{{ $update->id }}" class="da-hidden-preview" hidden>
                                    <div class="data-question">{{ $update->question }}</div>
                                    <div class="data-meta">{{ $update->category?->name ?? 'Uncategorized' }} · {{ $update->update_date?->format('d M Y') ?? '—' }} · {{ ucfirst($update->status) }}</div>
                                    @foreach(['A'=>'option_a','B'=>'option_b','C'=>'option_c','D'=>'option_d'] as $letter=>$field)
                                        <div class="data-option" data-correct="{{ $update->correct_option === $letter ? 'yes' : 'no' }}">{{ $letter }}) {{ $update->{$field} }}</div>
                                    @endforeach
                                    <div class="data-correct">{{ $update->correct_option }}</div>
                                    <div class="data-explanation">{{ $update->explanation ?? 'কোনো ব্যাখ্যা নেই।' }}</div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="da-empty">এই Filter-এ কোনো প্রশ্ন নেই। Add New Update দিয়ে প্রশ্ন যোগ করুন।</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="da-foot">
            <span>Showing {{ $updates->firstItem() ?? 0 }}–{{ $updates->lastItem() ?? 0 }} of {{ $updates->total() }} matching updates</span>
            <div class="da-pages">
                @if($updates->onFirstPage())
                    <span class="da-page" aria-disabled="true">← Previous</span>
                @else
                    <a class="da-page" href="{{ $updates->previousPageUrl() }}">← Previous</a>
                @endif
                <strong>{{ $updates->currentPage() }} / {{ $updates->lastPage() }}</strong>
                @if($updates->hasMorePages())
                    <a class="da-page" href="{{ $updates->nextPageUrl() }}">Next →</a>
                @else
                    <span class="da-page" aria-disabled="true">Next →</span>
                @endif
            </div>
        </div>
    </section>

    <div class="da-overlay" id="daModal" hidden>
        <div class="da-modal" role="dialog" aria-modal="true" aria-labelledby="daModalTitle" tabindex="-1">
            <div class="da-modalhead"><div><h3 id="daModalTitle">Question Preview</h3><p class="da-modalnote" id="daModalNote"></p></div><button class="da-close" type="button" id="daClose" aria-label="Close preview">×</button></div>
            <div class="da-previewq" id="daPreviewTitle"></div>
            <div class="da-options" id="daPreviewOptions"></div>
            <div class="da-answer" id="daPreviewAnswer"></div>
            <p class="da-explanation" id="daPreviewExplanation"></p>
            <div class="da-modalfoot"><button type="button" class="da-btn light" id="daCancel">Close</button></div>
        </div>
    </div>
</main>
<script>
(function(){
    'use strict';
    var root = document.getElementById('lebdDuAllUpdates');
    if (!root) return;
    var modal = root.querySelector('#daModal');
    var closeButton = root.querySelector('#daClose');
    var lastFocus = null;
    function closeModal(){ modal.hidden = true; modal.classList.remove('open'); document.body.style.overflow = ''; if(lastFocus && lastFocus.focus)lastFocus.focus(); }
    root.querySelectorAll('.da-preview-trigger').forEach(function(button){
        button.addEventListener('click',function(){
            var block = document.getElementById(button.dataset.preview);
            if (!block) return;
            lastFocus = document.activeElement;
            root.querySelector('#daPreviewTitle').textContent = 'Q. ' + block.querySelector('.data-question').textContent;
            root.querySelector('#daModalNote').textContent = block.querySelector('.data-meta').textContent;
            var choices = root.querySelector('#daPreviewOptions'); choices.replaceChildren();
            var correct = block.querySelector('.data-correct').textContent.trim();
            var answer = '';
            block.querySelectorAll('.data-option').forEach(function(source){
                var item = document.createElement('div');
                item.className = 'da-option' + (source.dataset.correct==='yes' ? ' right' : '');
                item.textContent = source.textContent;
                if(source.dataset.correct==='yes')answer = source.textContent;
                choices.appendChild(item);
            });
            root.querySelector('#daPreviewAnswer').textContent = '✓ সঠিক উত্তর (' + correct + '): ' + answer;
            root.querySelector('#daPreviewExplanation').textContent = 'ব্যাখ্যা: ' + block.querySelector('.data-explanation').textContent;
            modal.hidden = false; modal.classList.add('open'); document.body.style.overflow='hidden'; closeButton.focus();
        });
    });
    closeButton.addEventListener('click',closeModal);
    root.querySelector('#daCancel').addEventListener('click',closeModal);
    modal.addEventListener('click',function(e){if(e.target===modal)closeModal();});
    document.addEventListener('keydown',function(e){if(e.key==='Escape' && !modal.hidden)closeModal();});
})();
</script>
@endsection
