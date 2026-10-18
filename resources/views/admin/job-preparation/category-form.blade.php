@extends('dashboard')
@section('title', $category ? 'Edit Category' : 'Add Category')
@section('content')
<style>
.jp-catwrap{max-width:1500px;margin:auto}.jp-catbox{background:#fff;border:1px solid #dde4ed;border-top:3px solid #2196d0;border-radius:4px;margin-bottom:20px}.jp-cathead{padding:15px 18px;border-bottom:1px solid #e5eaf0;display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap}.jp-cathead h3{margin:0;font-size:18px;color:#a00000}.jp-catbody{padding:18px}.jp-catactions{display:flex;align-items:center;gap:6px;flex-wrap:wrap}.jp-cattable th{background:#f5f8fb;white-space:nowrap}.jp-cattable td{vertical-align:middle!important}.jp-catmuted{color:#708096}.jp-count{display:inline-block;border-radius:4px;background:#e5f4fc;color:#075a88;font-weight:700;padding:5px 10px}.jp-catfooter{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap}.jp-catinput{max-width:760px}.jp-formrow{margin-bottom:16px}@media(max-width:750px){.jp-cattable{min-width:780px}.jp-catbody{padding:12px}}

.jp-icon-grid{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:10px;margin:12px 0 18px}.jp-icon-option{position:relative;cursor:pointer;border:1px solid #d9e4ef;border-radius:12px;background:#fff;padding:12px 4px;display:flex;align-items:center;justify-content:center;flex-direction:column;min-height:82px;gap:7px}.jp-icon-option input{position:absolute;opacity:0;pointer-events:none}.jp-icon-option:has(input:checked){border:2px solid #0877d7;background:#eaf4ff}.jp-icon-option .fa{font-size:24px;color:#0877d7}.jp-icon-option span{font-size:11px;color:#394d68}.jp-card-preview{width:180px;min-height:126px;padding:18px;text-align:center;border-radius:14px;border:1px solid #dce4ed;display:flex;align-items:center;justify-content:center;flex-direction:column;gap:9px;font-weight:700;color:#082c58}.jp-card-preview i{font-size:26px}.jp-icon-colors{display:flex;flex-wrap:wrap;align-items:flex-end;gap:18px}.jp-icon-colors label{display:block}.jp-icon-colors input[type=color]{width:75px;height:40px;padding:2px;border:1px solid #d4dfec;background:#fff;border-radius:5px}.jp-custom-icon{max-width:390px}@media(max-width:650px){.jp-icon-grid{grid-template-columns:repeat(3,minmax(0,1fr))}}
</style>
@php
$isEdit = $category !== null;
$kindName = $kind === 'department' ? 'Department' : 'Non-Department';
$action = $isEdit ? route('admin.job-preparation.categories.update',['kind'=>$kind,'category'=>$category->id]) : route('admin.job-preparation.categories.store',['kind'=>$kind]);
@endphp
<section class="content-header"><h1>{{ $isEdit ? 'Edit' : 'Add New' }} Category <small>MCQ Job Preparation / {{ $kindName }}</small></h1></section>
<section class="content jp-catwrap">
@if($errors->any())<div class="alert alert-danger"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<div class="jp-catbox jp-catinput">
  <div class="jp-cathead"><h3>Category Details</h3><a class="btn btn-default btn-sm" href="{{route('admin.job-preparation.categories',['kind'=>$kind])}}"><i class="fa fa-arrow-left"></i> Back to Categories</a></div>
  <form method="POST" action="{{ $action }}">@csrf @if($isEdit) @method('PUT') @endif
    <div class="jp-catbody">
      <div class="form-group jp-formrow"><label>Category Name <span class="text-danger">*</span></label><input name="name" class="form-control" maxlength="180" required autofocus value="{{ old('name', $isEdit ? $category->name : '') }}" placeholder="e.g. Civil, Electrical"></div>
      <div class="form-group jp-formrow"><label>Sort Order</label><input type="number" name="sort_order" min="0" max="999999" class="form-control" value="{{ old('sort_order', $isEdit ? $category->sort_order : 0) }}"></div>
      <div class="form-group jp-formrow"><label>Status</label><select class="form-control" name="status"><option value="Active" {{ old('status',$isEdit ? $category->status : 'Active') === 'Active' ? 'selected' : '' }}>Active</option><option value="Inactive" {{ old('status',$isEdit ? $category->status : 'Active') === 'Inactive' ? 'selected' : '' }}>Inactive</option></select></div>
      @php
        $iconChoices = [
          'hard-hat'=>'Civil','bolt'=>'Electrical','cogs'=>'Mechanical','desktop'=>'Computer','microchip'=>'Electronic',
          'wifi'=>'Power','fan'=>'RAC','building'=>'Architecture','industry'=>'Textile','leaf'=>'Agriculture',
          'book'=>'Bangla','globe'=>'General','language'=>'English','calculator'=>'Math','ellipsis-h'=>'Others',
          'train'=>'Railway','medkit'=>'Medical','university'=>'Banking','flask'=>'Science','briefcase'=>'Jobs',
          'graduation-cap'=>'Education','stethoscope'=>'Health','code'=>'Programming','balance-scale'=>'Law','ship'=>'Marine',
          'plane'=>'Aviation','car'=>'Automotive','pencil'=>'Writing','line-chart'=>'Economics','folder'=>'Other'
        ];
        $savedIcon = old('icon', $isEdit ? ($category->icon ?? 'folder') : 'hard-hat');
        $savedIconColor = old('icon_color', $isEdit ? ($category->icon_color ?? '#1677EE') : '#1677EE');
        $savedCardColor = old('card_color', $isEdit ? ($category->card_color ?? '#EDF4FF') : '#EDF4FF');
      @endphp
      <div class="form-group jp-formrow">
        <label>Choose Category Icon <span class="text-danger">*</span></label>
        <p class="jp-catmuted">Select a ready-made icon, or enter another Font Awesome 4 icon name below. No image upload is necessary.</p>
        <div class="jp-icon-grid" id="jp-icon-gallery">
          @foreach($iconChoices as $iconName=>$displayName)
          <label class="jp-icon-option">
            <input type="radio" name="icon_choice" value="{{ $iconName }}" {{ $savedIcon === $iconName ? 'checked' : '' }}>
            <i class="fa fa-{{ $iconName }}" aria-hidden="true"></i><span>{{ $displayName }}</span>
          </label>
          @endforeach
        </div>
        <label for="jp-icon-input">Custom Icon Name (optional)</label>
        <input type="text" name="icon" id="jp-icon-input" class="form-control jp-custom-icon" pattern="[a-z0-9-]+" required maxlength="80" value="{{ $savedIcon }}" placeholder="e.g. train, heart, anchor">
        <small class="jp-catmuted">Enter only the icon name, without “fa-”. Supports icons available in the current Admin Font Awesome library.</small>
      </div>
      <div class="form-group jp-icon-colors">
        <div><label for="jp-icon-color">Icon Color</label><input type="color" name="icon_color" id="jp-icon-color" value="{{ $savedIconColor }}"></div>
        <div><label for="jp-card-color">Card Background Color</label><input type="color" name="card_color" id="jp-card-color" value="{{ $savedCardColor }}"></div>
        <div><label>Live Category Preview</label><div class="jp-card-preview" id="jp-preview-card"><i id="jp-preview-icon" class="fa fa-{{ preg_match('/^[a-z0-9-]+$/', $savedIcon) ? $savedIcon : 'folder' }}" aria-hidden="true"></i><span id="jp-preview-name">{{ old('name', $isEdit ? $category->name : 'New Category') }}</span></div></div>
      </div>
      <p class="jp-catmuted">The category can be updated later. Existing subjects, chapters and linked questions are preserved when you change its name or status.</p>
    </div>
    <div class="box-footer text-right"><a class="btn btn-default" href="{{route('admin.job-preparation.categories',['kind'=>$kind])}}">Cancel</a> <button class="btn btn-primary" type="submit"><i class="fa fa-save"></i> {{ $isEdit ? 'Save Changes' : 'Add Category' }}</button></div>
  </form>
</div>
</section>
@endsection

@section('scripts')
<script>
(function(){
  var name=document.querySelector('input[name="name"]');
  var icon=document.getElementById('jp-icon-input');
  var iconColor=document.getElementById('jp-icon-color');
  var cardColor=document.getElementById('jp-card-color');
  var preview=document.getElementById('jp-preview-card');
  var glyph=document.getElementById('jp-preview-icon');
  var text=document.getElementById('jp-preview-name');
  function update(){
    var cleaned=(icon.value||'folder').trim().toLowerCase().replace(/[^a-z0-9-]/g,'');
    glyph.className='fa fa-'+(cleaned||'folder');
    glyph.style.color=iconColor.value;
    preview.style.backgroundColor=cardColor.value;
    text.textContent=name.value.trim()||'New Category';
  }
  document.querySelectorAll('input[name="icon_choice"]').forEach(function(radio){
    radio.addEventListener('change',function(){if(this.checked){icon.value=this.value;update();}});
  });
  icon.addEventListener('input',function(){
    var val=this.value.trim().toLowerCase();
    document.querySelectorAll('input[name="icon_choice"]').forEach(function(r){r.checked=r.value===val;});
    update();
  });
  [name,iconColor,cardColor].forEach(function(el){el.addEventListener('input',update);});
  update();
})();
</script>
@endsection
