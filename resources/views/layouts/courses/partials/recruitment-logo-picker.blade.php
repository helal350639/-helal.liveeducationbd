<div class="col-md-12">
 <div class="form-group lebd-logo-picker">
  <label for="recruitment_logo_search">Government Recruitment Logo (Optional)</label>
  <p class="text-muted">শুধু যাচাই করা Official Logo নির্বাচন করুন। Logo ছাড়া Course-এর পুরোনো Banner দেখাবে।</p>
  <input type="search" id="recruitment_logo_search" class="form-control" placeholder="Search BPSC, BR, DPE, NBR..." autocomplete="off">
  <input type="hidden" name="recruitment_logo_id" id="recruitment_logo_id" value="{{ old('recruitment_logo_id', isset($course) ? $course->recruitment_logo_id : '') }}">
  <div class="lebd-logo-picker-grid" id="lebd_logo_picker_grid">
   <button type="button" class="lebd-logo-option" data-logo-id="" data-search="no logo default banner" aria-pressed="false">
    <span class="lebd-logo-placeholder"><i class="fa fa-image"></i></span><strong>Default Banner</strong>
   </button>
   @foreach($logos as $logo)
    <button type="button" class="lebd-logo-option" data-logo-id="{{ $logo->id }}" data-search="{{ strtolower($logo->name.' '.$logo->short_name) }}" aria-pressed="false" title="{{ $logo->name }}">
     @if($logo->logo_path)<img src="{{ asset(ltrim($logo->logo_path,'/')) }}" alt="{{ $logo->short_name }}" loading="lazy">@else<span class="lebd-logo-placeholder"><i class="fa fa-building-o"></i></span>@endif
     <strong>{{ $logo->short_name }}</strong>
    </button>
   @endforeach
  </div>
  <p class="help-block">Official image না থাকলে শুধু Short Name দেখাবে; মিথ্যা/অনুমানভিত্তিক সরকারি প্রতীক ব্যবহার করা হবে না।</p>
  @error('recruitment_logo_id')<div class="text-danger">{{ $message }}</div>@enderror
 </div>
</div>
<style>
.lebd-logo-picker-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(104px,1fr));gap:10px;max-height:315px;overflow:auto;padding:10px;border:1px solid #dce7f5;background:#f7faff;border-radius:12px;margin-top:10px}
.lebd-logo-option{min-height:104px;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:7px;border:2px solid #e2e8f0;background:white;border-radius:12px;padding:8px;cursor:pointer;overflow:hidden}
.lebd-logo-option:hover{border-color:#88b6ff}.lebd-logo-option.is-selected{border-color:#0976ed;background:#ebf5ff;box-shadow:0 0 0 2px #dceaff}
.lebd-logo-option img{height:59px;width:70px;object-fit:contain}.lebd-logo-option strong{font-size:12px;color:#18365f;text-align:center;overflow-wrap:anywhere}
.lebd-logo-placeholder{width:55px;height:55px;display:flex;align-items:center;justify-content:center;border-radius:12px;background:#ebeff6;color:#526d91;font-size:25px}
</style>
<script>
document.addEventListener('DOMContentLoaded',function(){
 var input=document.getElementById('recruitment_logo_id'), search=document.getElementById('recruitment_logo_search'), buttons=[...document.querySelectorAll('.lebd-logo-option')];
 if(!input)return;
 function sync(){buttons.forEach(function(btn){var chosen=btn.dataset.logoId===input.value;btn.classList.toggle('is-selected',chosen);btn.setAttribute('aria-pressed',chosen?'true':'false');});}
 buttons.forEach(function(btn){btn.addEventListener('click',function(){input.value=btn.dataset.logoId;sync();});});
 if(search)search.addEventListener('input',function(){var q=search.value.toLowerCase().trim();buttons.forEach(function(btn){btn.style.display=(btn.dataset.search||'').includes(q)?'':'none';});});sync();
});
</script>
