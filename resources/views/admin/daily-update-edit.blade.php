{{-- Live Education BD — Database-backed Edit Daily Update. --}}
@extends('dashboard')
@section('title', 'Edit Daily Update | Live Education BD')
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
<main class="lebd-du-create" id="lebdDailyEdit">
    <header class="duc-head">
        <div class="duc-title"><div class="duc-head-icon">✎</div><div><h1>Edit Daily Update #{{ $update->id }}</h1><p class="duc-breadcrumb">Admin &nbsp;›&nbsp; Daily Update &nbsp;›&nbsp; Edit</p></div></div>
        <a href="{{ route('admin.daily-update.all') }}" class="duc-ghost">← All Updates</a>
    </header>
    <div class="duc-info"><span>ⓘ</span><div><strong>Live Edit:</strong> পরিবর্তন করলে সরাসরি MySQL Database-এ Save হবে। Original Question ID অপরিবর্তিত থাকবে।</div></div>
    @if($errors->any())
        <div class="duc-live-feedback duc-live-error" role="alert"><strong>তথ্য Save হয়নি:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif
    <div class="duc-wrap">
        <section class="duc-panel" aria-labelledby="ducFormTitle">
            <div class="duc-panel-head"><div class="duc-panel-icon">▤</div><div><h2 id="ducFormTitle">Edit Question Details</h2><p>সঠিক তথ্য দিয়ে পরিবর্তন Save করুন</p></div></div>
            <form class="duc-form" action="{{ route('admin.daily-update.update', $update->id) }}" method="POST" enctype="multipart/form-data" id="dailyUpdateEditForm" autocomplete="off">
                @csrf @method('PATCH')
                <div class="duc-row">
                    <div class="duc-field"><label class="duc-label" for="category">Category <span class="duc-star">*</span></label>
                        <select class="duc-control" name="category_id" id="category" required>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" @selected((string)old('category_id', $update->category_id)===(string)$category->id)>{{ $category->name }}{{ !$category->is_active ? ' (Inactive)' : '' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="duc-field"><label class="duc-label" for="editDate">Update Date <span class="duc-star">*</span></label><input class="duc-control" type="date" name="update_date" id="editDate" value="{{ old('update_date', $update->update_date?->format('Y-m-d')) }}" required></div>
                </div>
                <div class="duc-row">
                    <div class="duc-field"><label class="duc-label" for="editYear">Year <span class="duc-star">*</span></label><input class="duc-control" type="number" name="year" id="editYear" min="2000" max="2100" value="{{ old('year', $update->year) }}" required></div>
                    <div class="duc-field"><label class="duc-label" for="editMonth">Month <span class="duc-star">*</span></label><select class="duc-control" id="editMonth" name="month" required>
                        @foreach([1=>'January',2=>'February',3=>'March',4=>'April',5=>'May',6=>'June',7=>'July',8=>'August',9=>'September',10=>'October',11=>'November',12=>'December'] as $number=>$name)
                            <option value="{{ $number }}" @selected((int)old('month',$update->month)===$number)>{{ $name }}</option>
                        @endforeach
                    </select></div>
                </div>
                <div class="duc-divider"></div><p class="duc-subhead">Question Information</p>
                <div class="duc-field"><label class="duc-label" for="editQuestion">Question <span class="duc-star">*</span></label><textarea class="duc-control" name="question" id="editQuestion" maxlength="20000" required>{{ old('question',$update->question) }}</textarea></div>
                <div class="duc-answers">
                    @foreach(['A'=>'ক','B'=>'খ','C'=>'গ','D'=>'ঘ'] as $letter=>$bangla)
                        @php $field = 'option_' . strtolower($letter); @endphp
                        <div class="duc-field"><label class="duc-label" for="editOption{{ $letter }}"><span class="duc-answer-key">{{ $letter }}</span> Option {{ $letter }} <span class="duc-star">*</span></label><input class="duc-control" type="text" maxlength="1000" name="{{ $field }}" id="editOption{{ $letter }}" value="{{ old($field, $update->{$field}) }}" required></div>
                    @endforeach
                </div>
                <div class="duc-row">
                    <div class="duc-field"><label class="duc-label" for="editCorrect">Correct Answer <span class="duc-star">*</span></label><select class="duc-control" name="correct_answer" id="editCorrect" required>
                        @foreach(['A','B','C','D'] as $letter)
                            <option value="{{ $letter }}" @selected(old('correct_answer',$update->correct_option)===$letter)>Option {{ $letter }}</option>
                        @endforeach
                    </select></div>
                    <div class="duc-field"><label class="duc-label" for="editTopic">Topic / Tag (Optional)</label><input class="duc-control" id="editTopic" type="text" name="topic" maxlength="150" value="{{ old('topic',$update->topic) }}"></div>
                </div>
                <div class="duc-field"><label class="duc-label" for="editExplanation">Explanation</label><textarea class="duc-control" id="editExplanation" name="explanation" maxlength="40000">{{ old('explanation',$update->explanation) }}</textarea></div>
                <div class="duc-divider"></div><p class="duc-subhead">Publication Settings</p>
                <div class="duc-status-grid">
                    <label class="duc-status-option"><input type="radio" name="status" value="draft" @checked(old('status',$update->status)==='draft')><span>◷ Save as Draft</span></label>
                    <label class="duc-status-option"><input type="radio" name="status" value="published" @checked(old('status',$update->status)==='published')><span>✓ Published</span></label>
                </div>
                <div class="duc-actions">
                    <a class="duc-btn duc-btn-secondary" href="{{ route('admin.daily-update.all') }}">Cancel</a>
                    <button type="submit" class="duc-btn duc-btn-primary" id="editSave">✓ Save Changes to Database</button>
                </div>
            </form>
        </section>
        <aside class="duc-side">
            <section class="duc-panel"><div class="duc-side-body">
                <h3 class="duc-side-title">▧ Featured Image (Optional)</h3>
                @if($update->featured_image)
                    <p class="duc-hint">বর্তমান ছবি:</p>
                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($update->featured_image) }}" alt="Current Daily Update image" style="max-width:100%;max-height:200px;object-fit:contain;border-radius:10px;margin:10px 0">
                @endif
                <label class="duc-label" for="editImage">Replace Image</label>
                <input class="duc-control" type="file" name="image" form="dailyUpdateEditForm" id="editImage" accept="image/jpeg,image/png,image/webp">
                <p class="duc-hint">JPG, PNG, WEBP · Maximum 2 MB. নতুন ছবি না দিলে আগেরটি অপরিবর্তিত থাকবে।</p>
            </div></section>
            <section class="duc-panel"><div class="duc-side-body"><h3 class="duc-side-title">ℹ Current Record</h3>
                <div class="duc-summary">
                    <div>Question ID: <b>#{{ $update->id }}</b></div>
                    <div>Current Status: <b>{{ ucfirst($update->status) }}</b></div>
                    <div>Created: <b>{{ $update->created_at?->format('d M Y, h:i A') ?? '—' }}</b></div>
                    <div>Last Updated: <b>{{ $update->updated_at?->format('d M Y, h:i A') ?? '—' }}</b></div>
                </div>
            </div></section>
        </aside>
    </div>
</main>
<script>
(function(){
    var date=document.getElementById('editDate'),year=document.getElementById('editYear'),month=document.getElementById('editMonth');
    if(date && year && month){date.addEventListener('change',function(){if(!/^\d{4}-\d{2}-\d{2}$/.test(date.value))return;year.value=date.value.slice(0,4);month.value=parseInt(date.value.slice(5,7),10);});}
    var form=document.getElementById('dailyUpdateEditForm'), save=document.getElementById('editSave');
    if(form && save)form.addEventListener('submit',function(){save.disabled=true;save.textContent='Saving...';});
})();
</script>
@endsection
