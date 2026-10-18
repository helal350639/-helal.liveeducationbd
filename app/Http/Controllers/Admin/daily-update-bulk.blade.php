{{-- Live Education BD — Bulk Add 1–100 MCQs; uses existing admin layout and MySQL tables. --}}
@extends('dashboard')
@section('title', 'Bulk Add Daily Update | Live Education BD')
@section('content')
<style>
.lebd-du-bulk{max-width:1390px;margin:auto;padding:17px 18px 40px;color:#173a67;font-size:13px;line-height:1.55}
.lebd-du-bulk,.lebd-du-bulk *{box-sizing:border-box}.lebd-du-bulk input,.lebd-du-bulk textarea,.lebd-du-bulk select,.lebd-du-bulk button{font:inherit}
.lebd-du-bulk .db-head{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:13px;margin-bottom:15px}.lebd-du-bulk h1{margin:0;font-weight:850;font-size:23px;color:#153761}
.lebd-du-bulk .db-sub{margin:4px 0 0;color:#7b90ab;font-size:11px}.lebd-du-bulk .db-btn{display:inline-flex;gap:7px;align-items:center;justify-content:center;padding:10px 14px;border:1px solid #d6e4f4;background:#fff;color:#275f9a;border-radius:9px;font-weight:800;text-decoration:none;cursor:pointer;white-space:nowrap}
.lebd-du-bulk .db-btn.blue{background:linear-gradient(120deg,#1281ed,#0957c7);color:#fff;border-color:#176bce;box-shadow:0 4px 13px #1c72e62a}.lebd-du-bulk .db-btn.green{background:#08a070;border-color:#08a070;color:#fff}
.lebd-du-bulk .db-actions{display:flex;gap:9px;flex-wrap:wrap}.lebd-du-bulk .db-alert{padding:12px 15px;border-radius:10px;border:1px solid #bde7d5;background:#eafaf1;color:#0d7656;margin-bottom:13px}.lebd-du-bulk .db-alert.error{color:#9b3434;border-color:#ffc4c4;background:#fff1f1}
.lebd-du-bulk .db-tip{padding:13px 15px;border-radius:11px;background:#f0f6ff;border:1px solid #d9e7fa;margin-bottom:16px;font-size:12px;color:#476a95}
.lebd-du-bulk .db-grid{display:grid;grid-template-columns:minmax(0,1.8fr) minmax(290px,1fr);gap:15px}.lebd-du-bulk .db-card{background:#fff;border:1px solid #dbe8f6;border-radius:15px;box-shadow:0 6px 25px #1b4c8009;overflow:hidden;min-width:0}
.lebd-du-bulk .db-cardhead{padding:17px 19px;border-bottom:1px solid #e7eef8;display:flex;gap:11px;align-items:center}.lebd-du-bulk .db-cardhead .db-symbol{display:grid;place-items:center;background:#e9f2ff;border-radius:11px;width:42px;height:42px;color:#1262ca;font-size:21px}.lebd-du-bulk .db-cardhead h2{font-size:17px;margin:0;color:#153860;font-weight:850}.lebd-du-bulk .db-cardhead p{margin:3px 0 0;font-size:11px;color:#8195ae}
.lebd-du-bulk .db-body{padding:18px 19px 20px}.lebd-du-bulk .db-fields{display:grid;grid-template-columns:1.2fr 1fr 1fr;gap:11px}.lebd-du-bulk .db-label{display:block;font-size:12px;font-weight:800;color:#315a82;margin:0 0 6px}.lebd-du-bulk .db-field{height:43px;padding:9px 10px;border:1px solid #d4e3f4;border-radius:9px;outline:none;color:#294b76;background:#fbfdff;width:100%;min-width:0}.lebd-du-bulk .db-field:focus{border-color:#3185ea;box-shadow:0 0 0 3px #3185ea18}
.lebd-du-bulk .db-divider{height:1px;background:#e5edf7;margin:20px 0}.lebd-du-bulk .db-source-tabs{display:flex;gap:7px;flex-wrap:wrap;margin:0 0 12px}.lebd-du-bulk .db-tab{padding:9px 14px;border-radius:9px;background:#eef5ff;color:#1e629c;border:1px solid #d1e2f7;font-weight:850;cursor:pointer}.lebd-du-bulk .db-tab.is-active{background:#0c65cf;color:white;border-color:#1265c6}
.lebd-du-bulk .db-inputbox{width:100%;padding:14px;border:1px dashed #a9c8ef;border-radius:12px;background:#f8fbff}.lebd-du-bulk textarea.db-field{height:auto;min-height:185px;resize:vertical;line-height:1.6;white-space:pre;overflow:auto}
.lebd-du-bulk .db-help{font-size:11px;color:#6985a6;margin:7px 0 0}.lebd-du-bulk .db-summary{display:flex;gap:8px;align-items:center;flex-wrap:wrap;margin-top:13px;background:#ecf7ff;border:1px solid #d8eafa;border-radius:9px;padding:11px}.lebd-du-bulk .db-summary strong{font-size:16px;color:#0656b9}
.lebd-du-bulk .db-submit{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;margin-top:17px}.lebd-du-bulk .db-sidebox{padding:18px 19px}.lebd-du-bulk .db-sidebox h3{margin:0 0 11px;color:#163860;font-weight:850;font-size:15px}.lebd-du-bulk .db-steps{margin:0;padding-left:20px;color:#46678b;line-height:2}.lebd-du-bulk .db-note{background:#fff8e9;border:1px solid #f2dfb3;border-radius:9px;padding:12px;color:#815e1c;font-size:12px;margin-top:13px}
.lebd-du-bulk .db-example{font-size:11px;line-height:1.6;background:#f5f8fd;padding:10px;border:1px solid #e2ecf7;border-radius:8px;white-space:pre-wrap;overflow-wrap:anywhere;color:#375e86}.lebd-du-bulk [hidden]{display:none!important}
@media(max-width:1000px){.lebd-du-bulk .db-grid{grid-template-columns:1fr}}@media(max-width:690px){.lebd-du-bulk{padding:10px 10px 27px}.lebd-du-bulk h1{font-size:20px}.lebd-du-bulk .db-fields{grid-template-columns:1fr}.lebd-du-bulk .db-cardhead,.lebd-du-bulk .db-body,.lebd-du-bulk .db-sidebox{padding:13px}.lebd-du-bulk .db-btn{font-size:11px}}
</style>
<main class="lebd-du-bulk" id="lebdBulkMain">
    <header class="db-head">
        <div><h1>⬆ Bulk Add — Up to 100 Questions</h1><p class="db-sub">Admin › Daily Update › Bulk Question Upload</p></div>
        <div class="db-actions">
            <a class="db-btn" href="{{ route('admin.daily-update.index') }}">← Dashboard</a>
            <a class="db-btn" href="{{ route('admin.daily-update.all') }}">▤ All Updates</a>
            <a class="db-btn" href="{{ route('admin.daily-update.preview', ['section' => 'current-affairs']) }}" target="_blank" rel="noopener">👁 Student Preview</a>
        </div>
    </header>
    @if(session('success'))<div class="db-alert">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="db-alert error" role="alert"><strong>Import হয়নি; কোনো নতুন প্রশ্ন Save হয়নি।</strong><ul style="margin:7px 0 0;padding-left:18px">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    <div class="db-tip">✓ একবারে <strong>১–১০০টি</strong> প্রশ্ন। Excel-এর ৮টি Column একসঙ্গে Copy করে Paste করুন অথবা <strong>CSV UTF-8</strong> Upload করুন। সব Row যাচাই করে তারপর একসঙ্গে MySQL-এ Save হবে।</div>
    <div class="db-actions" style="margin:-5px 0 14px"><a class="db-btn" href="{{ asset('templates/daily-update-100-questions-template.csv') }}" download>⬇ 100 Questions CSV Template (UTF-8)</a></div>
    <div class="db-grid">
        <section class="db-card">
            <div class="db-cardhead"><span class="db-symbol">🗂</span><div><h2>Question Upload Details</h2><p>Category, Month এবং প্রশ্নগুলো নির্ধারণ করুন</p></div></div>
            <form class="db-body" action="{{ route('admin.daily-update.bulk.store') }}" method="POST" enctype="multipart/form-data" id="bulkQuestionsForm">
                @csrf
                <div class="db-fields">
                    <div><label class="db-label" for="bCategory">Category *</label><select class="db-field" name="category_id" id="bCategory" required><option value="">ক্যাটাগরি নির্বাচন করুন</option>@foreach($categories as $category)<option value="{{ $category->id }}" {{ (string)old('category_id', $category->slug === 'current-affairs' ? $category->id : '') === (string)$category->id ? 'selected' : '' }}>{{ $category->name }}</option>@endforeach</select></div>
                    <div><label class="db-label" for="bDate">Update Date *</label><input class="db-field" type="date" id="bDate" name="update_date" required min="{{ $oldestAllowed }}" max="{{ $today }}" value="{{ old('update_date', $today) }}"></div>
                    <div><label class="db-label" for="bStatus">Save as *</label><select class="db-field" name="status" id="bStatus" required><option value="draft" {{ old('status','draft') === 'draft' ? 'selected' : '' }}>Draft — Preview First</option><option value="published" {{ old('status') === 'published' ? 'selected' : '' }}>Published — Visible to Students</option></select></div>
                </div>
                <div class="db-divider"></div>
                <label class="db-label">Question Input Method</label>
                <div class="db-source-tabs" role="group" aria-label="Import method"><button type="button" class="db-tab is-active" id="bPasteTab">✎ Paste from Excel</button><button type="button" class="db-tab" id="bFileTab">⬆ Upload CSV/TSV</button></div>
                <div id="bPastePane">
                    <label class="db-label" for="bText">Paste Questions (Tab-separated Excel Rows)</label>
                    <textarea class="db-field" name="bulk_text" id="bText" placeholder="question&#9;option_a&#9;option_b&#9;option_c&#9;option_d&#9;correct_answer&#9;explanation&#9;topic&#10;বাংলাদেশের রাজধানী কোনটি?&#9;ঢাকা&#9;সিলেট&#9;খুলনা&#9;বরিশাল&#9;A&#9;বাংলাদেশের রাজধানী ঢাকা।&#9;সাধারণ জ্ঞান">{{ old('bulk_text') }}</textarea>
                    <p class="db-help">Excel-এর প্রথম Row-এ Header দিন। নিচের Row-গুলোতে প্রশ্ন লিখে A:H Column Copy → এখানে Ctrl+V।</p>
                </div>
                <div id="bFilePane" hidden>
                    <label class="db-label" for="bFile">UTF-8 CSV বা TSV File (সর্বোচ্চ 2 MB)</label>
                    <div class="db-inputbox"><input type="file" id="bFile" name="csv_file" accept=".csv,.tsv,.txt,text/csv,text/tab-separated-values" disabled><p class="db-help">Excel → Save As → CSV UTF-8 (Comma delimited)। XLSX সরাসরি নয়।</p></div>
                </div>
                <div class="db-summary"><span>অনুমানিক প্রশ্ন:</span> <strong id="bEstimate">0</strong><span style="color:#7594b0">/ 100 — চূড়ান্ত Validation Submit-এর সময় Server-এ হবে</span></div>
                <div class="db-submit"><span class="db-help">প্রশ্ন ভুল থাকলে পুরো Import বাতিল হবে।</span><button class="db-btn blue" type="submit" id="bSubmit">✓ Save Questions to MySQL →</button></div>
            </form>
        </section>
        <aside>
            <section class="db-card"><div class="db-sidebox"><h3>🧾 Required Columns (এই ক্রমে)</h3><div class="db-example">question | option_a | option_b | option_c | option_d | correct_answer | explanation | topic</div><ol class="db-steps"><li><b>question:</b> পুরো প্রশ্ন লিখুন</li><li><b>option_a–d:</b> চারটি উত্তর</li><li><b>correct_answer:</b> A/B/C/D (বা ক/খ/গ/ঘ)</li><li><b>explanation:</b> ঐচ্ছিক ব্যাখ্যা</li><li><b>topic:</b> ঐচ্ছিক বিষয়</li></ol><p class="db-help">প্রথম ছয়টি Column বাধ্যতামূলক। Quoted CSV-এর ভেতরে Comma থাকলেও Import সমর্থিত।</p></div></section>
            <section class="db-card" style="margin-top:14px"><div class="db-sidebox"><h3>✅ Recommended Process</h3><ol class="db-steps"><li>১০০টি প্রশ্ন Excel-এ প্রস্তুত করুন</li><li><strong>Draft</strong> নির্বাচন করে Save করুন</li><li>Student Preview-তে প্রশ্নগুলো যাচাই করুন</li><li>Preview-এর Publish Button দিয়ে মাসের সব Draft একসঙ্গে প্রকাশ করুন</li><li>Student Page-এ যাচাই করুন</li></ol><div class="db-note">📅 Student Archive-এ চলতি মাসসহ সর্বশেষ ১২ মাস থাকবে। ১২ মাসের বেশি পুরোনো মাস Student Page-এ প্রদর্শিত হবে না; Database Record Delete হবে না।</div></div></section>
        </aside>
    </div>
</main>
<script>
(function(){'use strict';var root=document.getElementById('lebdBulkMain');if(!root)return;var paste=root.querySelector('#bPasteTab'),file=root.querySelector('#bFileTab'),txt=root.querySelector('#bText'),input=root.querySelector('#bFile'),p=root.querySelector('#bPastePane'),f=root.querySelector('#bFilePane'),estimate=root.querySelector('#bEstimate');
function toggle(isFile){p.hidden=isFile;f.hidden=!isFile;paste.classList.toggle('is-active',!isFile);file.classList.toggle('is-active',isFile);txt.disabled=isFile;input.disabled=!isFile;count()}
function count(){var source=!p.hidden?txt.value:'';var rows=source.trim().split(/\r?\n/).filter(function(x){return x.trim()!==''});if(rows.length&&/^question\s*[,\t]/i.test(rows[0].trim()))rows.shift();estimate.textContent=String(Math.max(0,rows.length));estimate.style.color=rows.length>100?'#d33333':'#0656b9'}
paste.addEventListener('click',function(){toggle(false)});file.addEventListener('click',function(){toggle(true)});txt.addEventListener('input',count);
root.querySelector('#bulkQuestionsForm').addEventListener('submit',function(e){if(!p.hidden&&!txt.value.trim()){e.preventDefault();alert('প্রথমে Excel থেকে প্রশ্ন Paste করুন');return}if(!f.hidden&&!input.files.length){e.preventDefault();alert('প্রথমে CSV/TSV File নির্বাচন করুন');return}if(root.querySelector('#bStatus').value==='published'&&!confirm('Published করলে প্রশ্নগুলো সঙ্গে সঙ্গে Student Page-এ দেখা যাবে। নিশ্চিত?'))e.preventDefault()});toggle(false)})();
</script>
@endsection
