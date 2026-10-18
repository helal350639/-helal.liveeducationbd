{{--
  LEBD • Admin Daily Update • STEP 14 / Add New Update (LIVE MySQL)
  Existing Admin layout (@extends('dashboard')) — no extra sidebar or header.
  Route admin.daily-update.create is handled by Admin\DailyUpdateController@create.
  Form POST admin.daily-update.store is validated and saved by the Controller.
  Dashboard/List, public Daily Update and Comments are still demo interfaces.
--}}
@extends('dashboard')
@section('title', 'Add New Daily Update | Live Education BD')
@section('content')
<style>
/* Everything below is scoped to this Admin Daily Update form. */
.lebd-du-create{--duc-navy:#183659;--duc-blue:#176cf1;--duc-muted:#7288a2;--duc-line:#dce7f5;--duc-surface:#f7faff;color:var(--duc-navy);font-family:inherit;font-size:13px;max-width:1170px;margin:0 auto;padding:17px 15px 45px}
.lebd-du-create,.lebd-du-create *,.lebd-du-create *::before,.lebd-du-create *::after{box-sizing:border-box}
.lebd-du-create h1,.lebd-du-create h2,.lebd-du-create h3,.lebd-du-create p{margin-top:0}
.lebd-du-create button,.lebd-du-create input,.lebd-du-create select,.lebd-du-create textarea{font:inherit}
.lebd-du-create button{cursor:pointer}
.lebd-du-create .duc-head{display:flex;align-items:center;justify-content:space-between;gap:14px;flex-wrap:wrap;margin-bottom:16px}
.lebd-du-create .duc-title{display:flex;align-items:center;gap:12px}
.lebd-du-create .duc-head-icon{display:grid;place-items:center;width:45px;height:45px;border-radius:12px;background:linear-gradient(135deg,#388ef5,#135ecb);color:#fff;font-size:23px;font-weight:800;box-shadow:0 7px 17px #176cf12b}
.lebd-du-create .duc-title h1{font-size:23px;line-height:1.2;font-weight:800;margin:0 0 5px;color:#19385f}
.lebd-du-create .duc-breadcrumb{font-size:11px;color:#8296ae;margin:0}
.lebd-du-create .duc-ghost{display:inline-flex;align-items:center;justify-content:center;gap:7px;color:#395c84;background:#fff;border:1px solid #dce7f4;border-radius:9px;padding:9px 12px;font-weight:700;font-size:12px;text-decoration:none}
.lebd-du-create .duc-info{display:flex;align-items:flex-start;gap:10px;padding:12px 14px;margin-bottom:14px;border:1px solid #f2d9a1;background:#fffaf0;border-radius:10px;color:#785c2b;line-height:1.6}
.lebd-du-create .duc-info strong{font-weight:800}
.lebd-du-create .duc-wrap{display:grid;grid-template-columns:minmax(0,1.75fr) minmax(280px,.8fr);gap:14px;align-items:start}
.lebd-du-create .duc-panel{min-width:0;background:#fff;border:1px solid #e1eaf5;border-radius:15px;box-shadow:0 8px 25px #194c880c;overflow:hidden}
.lebd-du-create .duc-panel-head{padding:17px 19px;border-bottom:1px solid #eaf0f8;display:flex;align-items:center;gap:11px}
.lebd-du-create .duc-panel-icon{width:42px;height:42px;display:grid;place-items:center;border-radius:11px;background:#eaf3ff;color:#1267df;font-size:21px}
.lebd-du-create .duc-panel-head h2{font-size:16px;font-weight:850;margin:0 0 3px;color:#183d6b}
.lebd-du-create .duc-panel-head p{font-size:11px;color:#8498b0;margin:0}
.lebd-du-create .duc-form{padding:18px 19px 20px}
.lebd-du-create .duc-row{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:13px}
.lebd-du-create .duc-field{min-width:0;margin-bottom:15px}
.lebd-du-create .duc-label{display:block;color:#29496d;font-size:12px;font-weight:800;margin:0 0 7px}
.lebd-du-create .duc-star{color:#e04f54}
.lebd-du-create .duc-control{display:block;width:100%;min-width:0;min-height:42px;border-radius:9px;border:1px solid #d4e1f1;background:#fbfdff;padding:9px 11px;outline:none;color:#203e64;font-size:12px!important;box-shadow:none}
.lebd-du-create .duc-control:focus{border-color:#277cf0;background:#fff;box-shadow:0 0 0 3px #2276e315}
.lebd-du-create textarea.duc-control{min-height:84px;resize:vertical;line-height:1.55}
.lebd-du-create .duc-hint{font-size:10px;color:#899db3;margin:5px 0 0;line-height:1.5}
.lebd-du-create .duc-subhead{font-size:13px;font-weight:800;color:#234970;margin:2px 0 13px;display:flex;align-items:center;gap:7px}
.lebd-du-create .duc-subhead::after{content:'';flex:1;height:1px;background:#ebf0f7}
.lebd-du-create .duc-divider{height:1px;background:#ebf0f7;margin:3px 0 16px}
.lebd-du-create .duc-answers{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:0 13px}
.lebd-du-create .duc-answer-label{display:flex;align-items:center;gap:7px}
.lebd-du-create .duc-answer-key{width:23px;height:23px;border-radius:6px;background:#eaf3ff;color:#1266d9;display:grid;place-items:center;font-weight:850}
.lebd-du-create .duc-status-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:9px}
.lebd-du-create .duc-status-option{position:relative;display:flex;align-items:center;gap:9px;background:#f9fbff;border:1px solid #dce6f3;border-radius:10px;padding:11px;cursor:pointer}
.lebd-du-create .duc-status-option:has(input:checked){background:#eef7ff;border-color:#2c85f0;box-shadow:inset 0 0 0 1px #2c85f0}
.lebd-du-create .duc-status-option input{accent-color:#176cf1;flex:none}
.lebd-du-create .duc-status-option span{font-weight:750;font-size:12px}
.lebd-du-create .duc-actions{display:flex;align-items:center;justify-content:flex-end;flex-wrap:wrap;gap:9px;margin-top:19px;padding-top:15px;border-top:1px solid #eaf0f8}
.lebd-du-create .duc-btn{border:0;border-radius:9px;padding:11px 16px;font-weight:800;font-size:12px;min-height:42px;display:inline-flex;align-items:center;justify-content:center;gap:6px}
.lebd-du-create .duc-btn-secondary{background:#f2f6fc;color:#315274;border:1px solid #d8e3f1}
.lebd-du-create .duc-btn-outline{background:#fff;color:#1268d6;border:1px solid #afcffa}
.lebd-du-create .duc-btn-primary{background:linear-gradient(135deg,#1679f8,#0b53c5);color:#fff;box-shadow:0 6px 15px #1469db24}
.lebd-du-create .duc-btn-primary:hover{filter:brightness(.97)}
.lebd-du-create .duc-side{display:grid;gap:14px;align-content:start}
.lebd-du-create .duc-side-body{padding:16px}
.lebd-du-create .duc-side-title{font-size:13px;font-weight:850;color:#24466c;margin:0 0 11px}
.lebd-du-create .duc-upload{border:1px dashed #accbef;background:#f7fbff;border-radius:11px;display:flex;align-items:center;justify-content:center;flex-direction:column;gap:6px;text-align:center;padding:14px 9px;min-height:150px;cursor:pointer;overflow:hidden}
.lebd-du-create .duc-upload-ico{font-size:28px;color:#3985e9;line-height:1}
.lebd-du-create .duc-upload strong{font-size:12px;color:#274b73}
.lebd-du-create .duc-upload small{font-size:10px;color:#849bb6}
.lebd-du-create #ducImagePreview{width:100%;height:auto;max-height:200px;object-fit:contain;border-radius:8px;display:none}
.lebd-du-create .duc-remove-image{margin-top:9px;display:none;background:#fff1f0;border:1px solid #f1b8b2;color:#af3333;border-radius:8px;padding:7px 11px;font-size:11px;font-weight:800}
.lebd-du-create .duc-checklist{display:grid;gap:10px}
.lebd-du-create .duc-check{display:flex;align-items:flex-start;gap:9px;font-size:11px;color:#59728f;line-height:1.55}
.lebd-du-create .duc-check-bullet{flex:none;width:21px;height:21px;display:grid;place-items:center;border-radius:50%;background:#e8f7ee;color:#13915a;font-size:12px;font-weight:900}
.lebd-du-create .duc-summary{background:#f1f7ff;border:1px solid #dceaff;border-radius:10px;padding:11px 12px;font-size:11px;line-height:1.9;color:#4d6a8c}
.lebd-du-create .duc-summary b{color:#1b4776}
.lebd-du-create .duc-toast{display:none;margin-bottom:14px;padding:11px 14px;border-radius:10px;background:#e9f8ee;color:#11683d;border:1px solid #bce8ca;line-height:1.6;font-size:12px;font-weight:700}
.lebd-du-create .duc-toast.visible{display:block}
.lebd-du-create .duc-preview-shade{position:fixed;inset:0;z-index:99999;background:#102642ac;display:none;justify-content:center;align-items:center;padding:16px}
.lebd-du-create .duc-preview-shade.visible{display:flex}
.lebd-du-create .duc-preview-window{background:#fff;width:min(700px,100%);max-height:92vh;overflow:auto;border-radius:15px;padding:18px;box-shadow:0 18px 55px #0004}
.lebd-du-create .duc-preview-top{display:flex;align-items:center;justify-content:space-between;gap:8px;margin-bottom:10px}
.lebd-du-create .duc-preview-top h3{font-size:18px;font-weight:850;color:#20466f;margin:0}
.lebd-du-create .duc-x{border:1px solid #dbe5f1;background:#f8fbff;padding:6px 10px;border-radius:8px;font-size:18px;color:#365778}
.lebd-du-create .duc-preview-meta{font-size:11px;color:#597b9e;background:#eaf3ff;padding:8px 11px;border-radius:8px;margin-bottom:12px}
.lebd-du-create .duc-preview-question{font-size:16px;font-weight:800;color:#1e406b;margin:0 0 12px;overflow-wrap:anywhere}
.lebd-du-create .duc-preview-options{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:9px;margin:0 0 10px}
.lebd-du-create .duc-preview-choice{padding:10px;border:1px solid #dae6f4;border-radius:8px;background:#f8fbff;overflow-wrap:anywhere}
.lebd-du-create .duc-preview-correct{color:#0e7946;font-weight:800;background:#e4f8ed;padding:10px;border-radius:8px;margin-bottom:10px}
.lebd-du-create .duc-preview-note{background:#f3f7fc;border-radius:8px;padding:11px;color:#4c6886;line-height:1.6;white-space:pre-wrap;overflow-wrap:anywhere}
.lebd-du-create .duc-preview-image{max-width:100%;max-height:250px;border-radius:9px;display:none;margin:8px auto}
@media(max-width:900px){.lebd-du-create .duc-wrap{grid-template-columns:1fr}.lebd-du-create .duc-side{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(max-width:640px){.lebd-du-create{padding:10px 9px 26px}.lebd-du-create .duc-title h1{font-size:19px}.lebd-du-create .duc-wrap,.lebd-du-create .duc-side{grid-template-columns:1fr}.lebd-du-create .duc-row,.lebd-du-create .duc-answers,.lebd-du-create .duc-status-grid,.lebd-du-create .duc-preview-options{grid-template-columns:1fr}.lebd-du-create .duc-panel-head,.lebd-du-create .duc-form{padding:14px}.lebd-du-create .duc-actions .duc-btn{flex:1 1 auto}.lebd-du-create .duc-upload{min-height:120px}}
.lebd-du-create .duc-live-feedback{padding:12px 14px;margin-bottom:12px;border:1px solid;border-radius:10px;font-size:12px;line-height:1.7}
.lebd-du-create .duc-live-success{background:#eaf9f0;border-color:#a5e2be;color:#15704a;font-weight:750}
.lebd-du-create .duc-live-error{background:#fff1f0;border-color:#f1c7c3;color:#a83c35}
.lebd-du-create .duc-live-error ul{margin:6px 0 0;padding-left:19px}
.lebd-du-create button:disabled{cursor:wait;opacity:.68}
</style>
<main class="lebd-du-create" id="lebdDailyCreate">
  <header class="duc-head">
    <div class="duc-title">
      <div class="duc-head-icon" aria-hidden="true">＋</div>
      <div>
        <h1>Add New Daily Update</h1>
        <p class="duc-breadcrumb">Admin &nbsp;›&nbsp; Daily Update &nbsp;›&nbsp; Add New</p>
      </div>
    </div>
    {{-- Do not hardcode an unverified admin route. Browser back is available. --}}
    <button type="button" class="duc-ghost" id="ducGoBack">← Back</button>
  </header>

  <div class="duc-info" role="note"><span aria-hidden="true">✓</span><div><strong>Live Database Form:</strong> প্রশ্ন Save করলে তথ্য <strong>lebd_daily_updates</strong> Table-এ সংরক্ষিত হবে। Published Status দিলে Database-এ প্রকাশের জন্য চিহ্নিত হবে; <strong>Student Daily Update Page এখনো Demo</strong>—সেখানে Live প্রশ্ন দেখানোর সংযোগ পরের ধাপে হবে।</div></div>
  @if(session('success'))
    <div class="duc-live-feedback duc-live-success" role="status">✓ {{ session('success') }}</div>
  @endif
  @if($errors->any())
    <div class="duc-live-feedback duc-live-error" role="alert">
      <strong>তথ্য Save হয়নি — অনুগ্রহ করে ঠিক করুন:</strong>
      <ul>
        @foreach($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif
  <div class="duc-toast" id="ducNotice" role="status" aria-live="polite"></div>

  <div class="duc-wrap">
    <section class="duc-panel" aria-labelledby="ducFormTitle">
      <div class="duc-panel-head">
        <div class="duc-panel-icon" aria-hidden="true">▤</div>
        <div><h2 id="ducFormTitle">Update Details</h2><p>সঠিক তথ্য দিয়ে নতুন প্রশ্নটি প্রস্তুত করুন</p></div>
      </div>
      <form class="duc-form" id="ducForm" action="{{ route('admin.daily-update.store') }}" method="POST" enctype="multipart/form-data" autocomplete="off">
        @csrf
        <div class="duc-row">
          <div class="duc-field">
            <label class="duc-label" for="ducCategory">Category <span class="duc-star">*</span></label>
            <select class="duc-control" id="ducCategory" name="category_id" required>
              <option value="">ক্যাটাগরি নির্বাচন করুন</option>
              @foreach($categories as $category)
                <option value="{{ $category->id }}" @selected((string)old('category_id') === (string)$category->id)>{{ $category->name }}</option>
              @endforeach
            </select>
            @if($categories->isEmpty())
              <p class="duc-hint" style="color:#af3c31;font-weight:700">কোনো Active Category নেই। আগে <a href="{{ route('admin.daily-update.categories') }}">Category Management</a> থেকে Category তৈরি করুন।</p>
            @endif
          </div>
          <div class="duc-field">
            <label class="duc-label" for="ducDate">Update Date <span class="duc-star">*</span></label>
            <input class="duc-control" type="date" id="ducDate" name="update_date" value="{{ old('update_date', now('Asia/Dhaka')->toDateString()) }}" required>
          </div>
        </div>
        <div class="duc-row">
          <div class="duc-field"><label class="duc-label" for="ducYear">Year <span class="duc-star">*</span></label><input class="duc-control" id="ducYear" name="year" type="number" min="2000" max="2100" value="{{ old('year', now('Asia/Dhaka')->year) }}" required><p class="duc-hint">তারিখ নির্বাচন করলে সাল আপডেট হবে।</p></div>
          <div class="duc-field"><label class="duc-label" for="ducMonth">Month <span class="duc-star">*</span></label><select class="duc-control" id="ducMonth" name="month" required>
            @foreach([1=>'January',2=>'February',3=>'March',4=>'April',5=>'May',6=>'June',7=>'July',8=>'August',9=>'September',10=>'October',11=>'November',12=>'December'] as $monthNum => $monthName)
              <option value="{{ $monthNum }}" @selected((int)old('month', now('Asia/Dhaka')->month) === $monthNum)>{{ $monthName }}</option>
            @endforeach
          </select></div>
        </div>
        <div class="duc-divider"></div>
        <p class="duc-subhead">Question Information</p>
        <div class="duc-field"><label class="duc-label" for="ducQuestion">Question Title <span class="duc-star">*</span></label><textarea class="duc-control" id="ducQuestion" name="question" placeholder="উদাহরণ: বাংলাদেশের রাজধানীর নাম কী?" maxlength="20000" required>{{ old('question') }}</textarea></div>
        <div class="duc-answers">
          @foreach(['A'=>'ক','B'=>'খ','C'=>'গ','D'=>'ঘ'] as $letter=>$bnLetter)
            <div class="duc-field"><label class="duc-label duc-answer-label" for="ducOption{{ $letter }}"><span class="duc-answer-key">{{ $letter }}</span> Option {{ $letter }} <span class="duc-star">*</span></label><input class="duc-control" type="text" id="ducOption{{ $letter }}" name="option_{{ strtolower($letter) }}" maxlength="1000" value="{{ old('option_' . strtolower($letter)) }}" placeholder="{{ $bnLetter }}) উত্তর লিখুন" required></div>
          @endforeach
        </div>
        <div class="duc-row">
          <div class="duc-field"><label class="duc-label" for="ducCorrect">Correct Answer <span class="duc-star">*</span></label><select class="duc-control" id="ducCorrect" name="correct_answer" required><option value="">সঠিক উত্তর নির্বাচন করুন</option><option value="A" @selected(old('correct_answer') === 'A')>Option A</option><option value="B" @selected(old('correct_answer') === 'B')>Option B</option><option value="C" @selected(old('correct_answer') === 'C')>Option C</option><option value="D" @selected(old('correct_answer') === 'D')>Option D</option></select></div>
          <div class="duc-field"><label class="duc-label" for="ducTopic">Topic / Tag (Optional)</label><input class="duc-control" id="ducTopic" name="topic" maxlength="150" value="{{ old('topic') }}" placeholder="যেমন: বাংলাদেশ বিষয়াবলি"></div>
        </div>
        <div class="duc-field"><label class="duc-label" for="ducExplanation">Explanation (Optional)</label><textarea class="duc-control" id="ducExplanation" name="explanation" placeholder="সঠিক উত্তরটি কেন সঠিক তার সংক্ষিপ্ত ব্যাখ্যা লিখুন...">{{ old('explanation') }}</textarea></div>
        <div class="duc-divider"></div>
        <p class="duc-subhead">Publication Settings</p>
        <div class="duc-field">
          <span class="duc-label">Update Status <span class="duc-star">*</span></span>
          <div class="duc-status-grid" role="group" aria-label="Publication status">
            <label class="duc-status-option"><input type="radio" name="status" value="draft" @checked(old('status', 'draft') === 'draft')><span>◷ &nbsp; Save as Draft</span></label>
            <label class="duc-status-option"><input type="radio" name="status" value="published" @checked(old('status') === 'published')><span>✓ &nbsp; Published</span></label>
          </div>
          <p class="duc-hint">Draft বা Published Status এখন সত্যিকারভাবে Database-এ Save হবে। Student Page-এর Live Display পরের ধাপে যুক্ত হবে।</p>
        </div>
        <div class="duc-actions">
          <button type="reset" class="duc-btn duc-btn-secondary" id="ducReset">↺ Reset</button>
          <button type="button" class="duc-btn duc-btn-outline" id="ducShowPreview">◉ Preview</button>
          <button type="submit" class="duc-btn duc-btn-primary" id="ducSaveBtn">▣ Save to Database</button>
        </div>
      </form>
    </section>

    <aside class="duc-side" aria-label="Update options">
      <section class="duc-panel"><div class="duc-side-body">
        <h3 class="duc-side-title">▧ Featured Image <small style="color:#8ca0b6;font-weight:500">(Optional)</small></h3>
        <label class="duc-upload" for="ducImage">
          <span class="duc-upload-ico" id="ducImageIcon">▧</span>
          <strong id="ducImageLabel">Click to select image</strong>
          <small id="ducImageHelp">JPG / PNG / WEBP · Max 2 MB · Uploaded on Save</small>
          <img id="ducImagePreview" alt="Chosen image preview">
        </label>
        <input type="file" id="ducImage" name="image" form="ducForm" accept="image/png,image/jpeg,image/webp" style="position:absolute;width:1px;height:1px;overflow:hidden;clip-path:inset(50%)">
        <button type="button" class="duc-remove-image" id="ducRemoveImage">✕ Remove Image</button>
        <p class="duc-hint">Preview আপনার কম্পিউটারে দেখাবে; Save করলে ছবি Server-এর public Disk-এ সংরক্ষিত হবে।</p>
      </div></section>
      <section class="duc-panel"><div class="duc-side-body">
        <h3 class="duc-side-title">☑ Before Publishing</h3>
        <div class="duc-checklist">
          <div class="duc-check"><span class="duc-check-bullet">✓</span><span>প্রশ্ন ও চারটি Option লিখুন</span></div>
          <div class="duc-check"><span class="duc-check-bullet">✓</span><span>সঠিক উত্তর যাচাই করুন</span></div>
          <div class="duc-check"><span class="duc-check-bullet">✓</span><span>ক্যাটাগরি ও তারিখ ঠিক করুন</span></div>
          <div class="duc-check"><span class="duc-check-bullet">✓</span><span>Preview-তে দেখে তারপর Submit করুন</span></div>
        </div>
      </div></section>
      <section class="duc-panel"><div class="duc-side-body">
        <h3 class="duc-side-title">ℹ Form Summary</h3>
        <div class="duc-summary"><div>Category: <b id="ducSummaryCategory">Not selected</b></div><div>Date: <b id="ducSummaryDate">—</b></div><div>Correct Answer: <b id="ducSummaryAnswer">Not selected</b></div><div>Status: <b id="ducSummaryStatus">Draft</b></div></div>
        <p class="duc-hint">ফর্মে পরিবর্তন করলে এই Summary আপডেট হবে।</p>
      </div></section>
    </aside>
  </div>

  <div class="duc-preview-shade" id="ducPreviewShade" role="presentation">
    <div class="duc-preview-window" role="dialog" aria-modal="true" aria-labelledby="ducPreviewTitle" tabindex="-1" id="ducPreviewDialog">
      <div class="duc-preview-top"><h3 id="ducPreviewTitle">Daily Update — Preview</h3><button type="button" class="duc-x" id="ducClosePreview" aria-label="Close preview">×</button></div>
      <div class="duc-preview-meta" id="ducPreviewMeta"></div>
      <div class="duc-preview-question" id="ducPreviewQuestion"></div>
      <div class="duc-preview-options" id="ducPreviewOptions"></div>
      <div class="duc-preview-correct" id="ducPreviewCorrect"></div>
      <img class="duc-preview-image" alt="Preview of selected featured image" id="ducPreviewImage">
      <div class="duc-preview-note" id="ducPreviewExplanation" hidden></div>
      <div style="margin-top:13px;text-align:right"><button class="duc-btn duc-btn-primary" type="button" id="ducPreviewDone">ঠিক আছে</button></div>
    </div>
  </div>
</main>
<script>
(function(){
  'use strict';
  var root=document.getElementById('lebdDailyCreate');
  if(!root)return;
  var form=root.querySelector('#ducForm');
  var byId=function(id){return root.querySelector('#'+id);};
  var read=function(id){return byId(id).value.trim();};
  var notice=byId('ducNotice');
  var preview=byId('ducPreviewShade');
  var dialog=byId('ducPreviewDialog');
  var imageInput=byId('ducImage');
  var selectedImageUrl=null;
  var priorFocus=null;
  function summary(){
    var cat=byId('ducCategory');
    byId('ducSummaryCategory').textContent=cat.value?cat.options[cat.selectedIndex].textContent:'Not selected';
    byId('ducSummaryDate').textContent=read('ducDate')||'—';
    byId('ducSummaryAnswer').textContent=read('ducCorrect')||'Not selected';
    var selected=root.querySelector('input[name="status"]:checked');
    byId('ducSummaryStatus').textContent=selected?selected.value:'draft';
  }
  function syncDate(){
    var value=read('ducDate');
    if(/^\d{4}-\d{2}-\d{2}$/.test(value)){
      byId('ducYear').value=Number(value.slice(0,4));
      byId('ducMonth').value=String(Number(value.slice(5,7)));
    }
    summary();
  }
  function validateFields(){
    if(!form.reportValidity())return false;
    var year=Number(read('ducYear'));
    var date=read('ducDate');
    if(!/^\d{4}-\d{2}-\d{2}$/.test(date)||!Number.isInteger(year)||year!==Number(date.slice(0,4))||Number(read('ducMonth'))!==Number(date.slice(5,7))){
      notice.textContent='তারিখ, সাল ও মাস এক হতে হবে। Date আবার নির্বাচন করুন।';
      notice.classList.add('visible');
      byId('ducDate').focus();
      return false;
    }
    return true;
  }
  function openPreview(){
    if(!validateFields())return;
    notice.classList.remove('visible');
    byId('ducPreviewMeta').textContent=byId('ducSummaryCategory').textContent+' · '+read('ducDate')+' · '+root.querySelector('input[name="status"]:checked').value.toUpperCase();
    byId('ducPreviewQuestion').textContent='Q. '+read('ducQuestion');
    var choices=byId('ducPreviewOptions');
    choices.replaceChildren();
    [['A','ক'],['B','খ'],['C','গ'],['D','ঘ']].forEach(function(entry){
      var choice=document.createElement('div');
      choice.className='duc-preview-choice';
      choice.textContent=entry[1]+') '+read('ducOption'+entry[0]);
      choices.appendChild(choice);
    });
    var key=read('ducCorrect');
    byId('ducPreviewCorrect').textContent='✓ সঠিক উত্তর: '+({A:'ক',B:'খ',C:'গ',D:'ঘ'}[key]||'')+') '+read('ducOption'+key);
    byId('ducPreviewExplanation').textContent=read('ducExplanation');
    byId('ducPreviewExplanation').hidden=!read('ducExplanation');
    if(selectedImageUrl){byId('ducPreviewImage').src=selectedImageUrl;byId('ducPreviewImage').style.display='block';}
    else{byId('ducPreviewImage').removeAttribute('src');byId('ducPreviewImage').style.display='none';}
    priorFocus=document.activeElement;
    preview.classList.add('visible');
    dialog.focus();
  }
  function closePreview(){
    preview.classList.remove('visible');
    if(priorFocus&&typeof priorFocus.focus==='function')priorFocus.focus();
  }
  function removeImage(){
    imageInput.value='';
    if(selectedImageUrl)URL.revokeObjectURL(selectedImageUrl);
    selectedImageUrl=null;
    byId('ducImagePreview').removeAttribute('src');
    byId('ducImagePreview').style.display='none';
    byId('ducImageIcon').style.display='';
    byId('ducImageLabel').textContent='Click to select image';
    byId('ducImageHelp').style.display='';
    byId('ducRemoveImage').style.display='none';
  }
  imageInput.addEventListener('change',function(){
    if(!this.files||!this.files.length){removeImage();return;}
    var file=this.files[0];
    if(!['image/png','image/jpeg','image/webp'].includes(file.type)||file.size>2*1024*1024){
      removeImage();
      notice.textContent='শুধু JPG / PNG / WEBP ছবি নির্বাচন করুন, সর্বোচ্চ 2 MB।';
      notice.classList.add('visible');
      return;
    }
    if(selectedImageUrl)URL.revokeObjectURL(selectedImageUrl);
    selectedImageUrl=URL.createObjectURL(file);
    byId('ducImagePreview').src=selectedImageUrl;
    byId('ducImagePreview').style.display='block';
    byId('ducImageIcon').style.display='none';
    byId('ducImageLabel').textContent=file.name;
    byId('ducImageHelp').style.display='none';
    byId('ducRemoveImage').style.display='inline-flex';
    notice.classList.remove('visible');
  });
  byId('ducRemoveImage').addEventListener('click',removeImage);
  form.addEventListener('input',summary);
  form.addEventListener('change',summary);
  byId('ducDate').addEventListener('change',syncDate);
  byId('ducShowPreview').addEventListener('click',openPreview);
  byId('ducClosePreview').addEventListener('click',closePreview);
  byId('ducPreviewDone').addEventListener('click',closePreview);
  preview.addEventListener('click',function(e){if(e.target===preview)closePreview();});
  document.addEventListener('keydown',function(e){if(e.key==='Escape'&&preview.classList.contains('visible'))closePreview();});
  form.addEventListener('submit',function(e){
    if(!validateFields()){
      e.preventDefault();
      notice.scrollIntoView({behavior:'smooth',block:'center'});
      return;
    }
    var btn=byId('ducSaveBtn');
    btn.disabled=true;
    btn.textContent='Saving...';
  });
  form.addEventListener('reset',function(){
    removeImage();
    notice.classList.remove('visible');
    setTimeout(summary,0);
  });
  byId('ducGoBack').addEventListener('click',function(){
    if(window.history.length>1){window.history.back();}
    else{window.location.href="{{ route('admin.daily-update.index') }}";}
  });
  summary();
})();
</script>
@endsection
