@extends('dashboard')
@section('title','E-Book Categories')
@section('content')
<link rel="stylesheet" href="{{ asset('web/css/lebd-ebook.css') }}">
<style>
.ebi-form{max-width:980px}.ebi-three{display:grid;grid-template-columns:minmax(0,1fr) 200px 200px;gap:12px}.ebi-form label{font-weight:650;display:block;margin-bottom:6px}.ebi-control{margin-bottom:15px}.ebi-hint{font-size:12px;color:#738099;margin:5px 0 12px}.ebi-icons{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:10px;margin:12px 0 18px}.ebi-option{cursor:pointer;background:#fff;border:1px solid #d7e2f0;border-radius:12px;min-height:82px;padding:10px 4px;display:flex!important;align-items:center;justify-content:center;flex-direction:column;gap:8px;text-align:center;font-weight:500!important}.ebi-option input{position:absolute;opacity:0;pointer-events:none}.ebi-option.is-selected{background:#e9f3ff;border:2px solid #0877db}.ebi-option i{font-size:23px;color:#0672d7}.ebi-option small{font-size:11px;color:#263e5f}.ebi-preview{min-height:105px;border:1px solid #dbe5ee;border-radius:12px;padding:15px;display:flex;align-items:center;justify-content:center;flex-direction:column;gap:7px;text-align:center;font-weight:700}.ebi-preview i{font-size:28px}.ebi-color-row{display:flex;align-items:flex-start;flex-wrap:wrap;gap:24px}.ebi-color-row input[type=color]{height:42px;width:86px;padding:3px;border:1px solid #cbd6df;border-radius:6px;background:#fff}.ebi-custom{max-width:390px}.ebi-editor{margin-top:15px}.ebi-editor summary{cursor:pointer;font-weight:650;color:#066ca8}.ebi-details{margin-top:14px}.ebi-saved-icon{font-size:25px!important}.ebi-card-title{font-size:17px;margin:0}@media(max-width:780px){.ebi-icons{grid-template-columns:repeat(3,minmax(0,1fr))}.ebi-three{grid-template-columns:1fr}}@media(max-width:390px){.ebi-icons{grid-template-columns:repeat(2,minmax(0,1fr))}}
</style>
<style>
.ect-wrap{max-width:1600px;margin:12px auto;padding:0 12px}.ect-panel{background:white;border:1px solid #dfebf4;border-radius:8px;margin-bottom:15px;padding:16px}.ect-heading{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;margin-bottom:13px}.ect-heading h2{font-size:19px;margin:0;color:#ba2525}.ect-btn{border:0;border-radius:5px;background:#09aee0;color:white!important;padding:9px 12px;font-size:12px;font-weight:bold;cursor:pointer;display:inline-block;text-decoration:none}.ect-btn.blue{background:#1483bb}.ect-btn.green{background:#05a85e}.ect-btn.orange{background:#f39c12}.ect-btn.red{background:#db4636}.ect-btn.grey{background:#eaf2fa;color:#234f81!important}.ect-controls{display:grid;grid-template-columns:2fr 1fr 1fr auto auto;gap:10px;align-items:end;background:#f5f9fd;padding:13px;border:1px solid #d9e8f8;border-radius:7px;margin-bottom:15px}.ect-controls label{font-size:11px;color:#475b76;font-weight:bold;display:block;margin-bottom:6px}.ect-controls input,.ect-controls select{height:36px;width:100%;border:1px solid #ccd9e7;border-radius:4px;padding:7px}.ect-scroll{overflow-x:auto}.ect-table{border-collapse:collapse;width:100%;font-size:12px;min-width:970px}.ect-table th{background:#f1f6fb;color:#183657;white-space:nowrap;text-align:left;padding:12px;border:1px solid #e7edf5}.ect-table td{padding:10px;border:1px solid #edf1f6;vertical-align:middle}.ect-table tr:hover td{background:#fafcff}.ect-table .actions{display:flex;gap:6px;align-items:center;flex-wrap:wrap}.ect-icon{width:37px;height:37px;border-radius:11px;display:inline-flex;align-items:center;justify-content:center;font-size:18px;margin-right:9px;vertical-align:middle}.ect-chip{background:#e9f4fc;color:#11669b;padding:5px 9px;border-radius:4px;font-weight:bold}.ect-status{background:#0caf64;color:white;padding:4px 8px;border-radius:4px;font-size:11px;font-weight:bold}.ect-status.inactive{background:#8290a3}.ect-pager{display:flex;justify-content:space-between;align-items:center;margin-top:13px;gap:8px;font-size:12px;color:#506781}.ect-pager button{border:1px solid #cbdceb;background:white;padding:6px 10px;border-radius:4px;cursor:pointer}.ect-pager button:disabled{opacity:.45}.ect-form{display:none}.ect-form.is-open{display:block}.ect-modal{position:fixed;inset:0;background:#11223c99;z-index:12000;display:none;align-items:flex-start;justify-content:center;padding:25px 12px;overflow-y:auto}.ect-modal.is-open{display:flex}.ect-dialog{width:min(980px,100%);background:white;border-radius:12px;padding:20px;position:relative}.ect-modal-close{position:absolute;right:15px;top:10px;font-size:26px;border:0;background:none;cursor:pointer}.ect-form-grid{display:grid;grid-template-columns:2fr 1fr 1fr;gap:12px}.ect-form-grid label{font-weight:bold;display:block;margin-bottom:5px}.ect-form-grid .ebook-field{width:100%}.ect-modal-footer{margin-top:16px;display:flex;gap:10px}.ebi-icons{grid-template-columns:repeat(5,minmax(0,1fr))}@media(max-width:850px){.ect-controls{grid-template-columns:1fr 1fr}.ect-form-grid{grid-template-columns:1fr}.ect-heading h2{font-size:16px}}@media(max-width:500px){.ect-controls{grid-template-columns:1fr}.ebi-icons{grid-template-columns:repeat(3,minmax(0,1fr))}}
</style>

<style>
/* v21: Scoped alignment for E-Book Category action buttons only. */
.ect-table td .actions { display:flex; flex-wrap:wrap; align-items:center; gap:7px; }
.ect-table td .actions > a.ect-btn,
.ect-table td .actions > button.ect-btn,
.ect-table td .actions > form > button.ect-btn {
    box-sizing:border-box; display:inline-flex; align-items:center; justify-content:center;
    vertical-align:middle; min-height:34px; line-height:1.25; padding:8px 11px;
    margin:0; border:0; border-radius:5px; font-size:12px; font-weight:700;
    white-space:nowrap; text-decoration:none; box-shadow:none;
}
.ect-table td .actions > form {
    display:inline-flex; align-items:center; align-self:center; justify-content:center;
    padding:0; margin:0 !important; border:0; height:auto; min-height:0;
    background:transparent; float:none;
}
.ect-table td .actions > form > button.ect-btn.red {
    background:#db4636 !important; color:#fff !important; cursor:pointer;
}
.ect-table td .actions .ect-btn i { margin-right:4px; font-size:12px; line-height:1; }
.ect-table td .actions .ect-btn:focus-visible { outline:2px solid #185b9e; outline-offset:2px; }
@media(max-width:650px) { .ect-table td .actions { gap:6px; } }
</style>

@php
$iconChoices = [
    'hard-hat' => 'Civil',
    'bolt' => 'Electrical',
    'cogs' => 'Mechanical',
    'desktop' => 'Computer',
    'microchip' => 'Electronic',
    'wifi' => 'Power',
    'fan' => 'RAC',
    'building' => 'Architecture',
    'industry' => 'Textile',
    'leaf' => 'Agriculture',
    'book' => 'Bangla',
    'globe' => 'General',
    'language' => 'English',
    'calculator' => 'Math',
    'ellipsis-h' => 'Others',
    'train' => 'Railway',
    'medkit' => 'Medical',
    'university' => 'Banking',
    'flask' => 'Science',
    'briefcase' => 'Jobs',
    'graduation-cap' => 'Education',
    'stethoscope' => 'Health',
    'code' => 'Programming',
    'balance-scale' => 'Law',
    'ship' => 'Marine',
    'plane' => 'Aviation',
    'car' => 'Automotive',
    'pencil' => 'Writing',
    'line-chart' => 'Economics',
    'folder' => 'Other',
 ];
@endphp
<div class="ect-wrap">
 <div class="ect-panel">
  <div class="ect-heading"><h2>List of {{ $kind === 'department' ? 'Department' : 'Non-Department' }} Categories</h2>
   <div class="ebook-actions"><a class="ect-btn grey" href="{{route('admin.ebook.categories',['kind'=>'department'])}}">Department</a><a class="ect-btn grey" href="{{route('admin.ebook.categories',['kind'=>'non-department'])}}">Non-Department</a><button type="button" class="ect-btn" id="ect-show-create"><i class="fa fa-plus"></i> Add Category</button></div>
  </div>
  @if(session('success'))<div class="ebook-alert">{{session('success')}}</div>@endif
  @if($errors->any())<div class="ebook-error">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif
  <div id="ect-create" class="ect-form {{ $errors->any() ? 'is-open' : '' }}">
   <h3>Add E-Book Category</h3>
   <form data-ebi-form method="POST" enctype="multipart/form-data" action="{{route('admin.ebook.categories.store',['kind'=>$kind])}}">@csrf
    <div class="ect-form-grid"><div><label>Category Name *</label><input class="ebook-field" name="name" maxlength="200" required value="{{old('name')}}" placeholder="Civil / Bangla"></div><div><label>Sort Order</label><input class="ebook-field" name="sort_order" type="number" min="0" value="{{old('sort_order',0)}}"></div><div><label>Status</label><select class="ebook-field" name="status"><option>Active</option><option {{old('status')==='Inactive'?'selected':''}}>Inactive</option></select></div></div>

 <div class="lebd-cover-upload-row">
  <label>Front Page — A4 JPG <small>(recommended 1240 × 1754 px)</small>
   <input type="file" name="front_cover" accept="image/jpeg,.jpg,.jpeg" class="ebook-field lebd-cover-file" data-cover-kind="front">
   <img class="lebd-cover-live-image" data-cover-live="front" alt="Front cover preview" hidden>
  </label>
  <label>Book Details Page — A4 JPG <small>(recommended 1240 × 1754 px; shown after Front Cover)</small>
   <input type="file" name="details_page" accept="image/jpeg,.jpg,.jpeg" class="ebook-field lebd-cover-file" data-cover-kind="details">
   <img class="lebd-cover-live-image" data-cover-live="details" alt="Book details page preview" hidden>
  </label>
  <label>Back Page — A4 JPG <small>(recommended 1240 × 1754 px)</small>
   <input type="file" name="back_cover" accept="image/jpeg,.jpg,.jpeg" class="ebook-field lebd-cover-file" data-cover-kind="back">
   <img class="lebd-cover-live-image" data-cover-live="back" alt="Back cover preview" hidden>
  </label>
 </div>
    @include('admin.ebook.partials.category-icons',['savedIcon'=>old('icon','fa-book'),'savedIconColor'=>old('icon_color','#1677ee'),'savedCardColor'=>old('card_color','#edf4ff'),'previewName'=>old('name','New Category')])
    <button class="ect-btn blue" type="submit"><i class="fa fa-save"></i> Save Category</button><button type="button" class="ect-btn grey" id="ect-hide-create">Cancel</button>
   </form>
  </div>
  <div class="ect-controls"><div><label>Search by name</label><input type="search" id="ect-search" placeholder="Search category name..."></div><div><label>Status</label><select id="ect-status"><option value="">All Status</option><option value="Active">Active</option><option value="Inactive">Inactive</option></select></div><div><label>Sort By</label><select id="ect-sort"><option value="order">Display Order</option><option value="name">Category Name</option><option value="newest">Newest First</option><option value="oldest">Oldest First</option></select></div><button type="button" class="ect-btn" id="ect-filter"><i class="fa fa-filter"></i> Filter</button><button type="button" class="ect-btn grey" id="ect-reset">Reset</button></div>
  <div class="ect-scroll"><table class="ect-table"><thead><tr><th>SL</th><th>ID</th><th>Category Name</th><th>সূচিপত্র</th><th>Status</th><th>Created At</th><th>Updated At</th><th>Actions</th></tr></thead><tbody id="ect-body">
   @foreach($categories as $category)
   <tr data-name="{{mb_strtolower($category->name)}}" data-status="{{$category->status}}" data-order="{{$category->sort_order}}" data-id="{{$category->id}}">
    <td data-sl></td><td>{{$category->id}}</td><td><span class="ect-icon" style="background:{{$category->card_color}};color:{{$category->icon_color}}"><i class="fa {{$category->icon}}"></i></span><strong>{{$category->name}}</strong>
     @if($category->front_cover)<img class="lebd-cover-list-thumb" src="{{asset($category->front_cover)}}" alt="Front cover">@endif
     @if($category->details_page)<span class="lebd-cover-back-indicator">Details page ✓</span>@endif
     @if($category->back_cover)<span class="lebd-cover-back-indicator">Back cover ✓</span>@endif</td>
    <td><span class="ect-chip">{{(int)($tocCounts[$category->id] ?? 0)}}</span></td><td><span class="ect-status {{ $category->status==='Inactive'?'inactive':'' }}">{{$category->status}}</span></td><td>{{\Carbon\Carbon::parse($category->created_at)->format('d M Y h:i A')}}</td><td>{{\Carbon\Carbon::parse($category->updated_at)->format('d M Y h:i A')}}</td>
    <td><div class="actions"><a class="ect-btn" href="{{route('admin.ebook.chapters',['kind'=>$kind,'category'=>$category->id])}}"><i class="fa fa-file-text"></i> Details / সূচিপত্র</a>
    <a class="ect-btn" style="background:#155e9b" href="{{route('admin.ebook.full-book',['kind'=>$kind,'category'=>$category->id])}}"><i class="fa fa-book"></i> সম্পূর্ণ বই দেখুন</a>
    <button type="button" class="ect-btn orange ect-edit" data-action="{{route('admin.ebook.categories.update',['kind'=>$kind,'category'=>$category->id])}}" data-name="{{$category->name}}" data-icon="{{$category->icon}}" data-icon-color="{{$category->icon_color}}" data-card-color="{{$category->card_color}}" data-order="{{$category->sort_order}}" data-status="{{$category->status}}" data-front-cover="{{ $category->front_cover ? asset($category->front_cover) : '' }}" data-back-cover="{{ $category->back_cover ? asset($category->back_cover) : '' }}" data-details-page="{{ $category->details_page ? asset($category->details_page) : '' }}"><i class="fa fa-pencil"></i> Edit</button>
    <form method="POST" action="{{route('admin.ebook.categories.delete',['kind'=>$kind,'category'=>$category->id])}}" onsubmit="return confirm('Delete this category and all its chapters?')">@csrf @method('DELETE')<button type="submit" class="ect-btn red"><i class="fa fa-trash"></i> Delete</button></form></div></td>
   </tr>
   @endforeach
   </tbody></table></div><p id="ect-empty" class="ebook-muted" style="display:none;padding:20px">কোনো Category পাওয়া যায়নি।</p>
   <div class="ect-pager"><span id="ect-info"></span><div><button id="ect-prev" type="button">Previous</button> <span id="ect-page">1</span> <button id="ect-next" type="button">Next</button></div></div>
 </div>
</div>
<div id="ect-edit-modal" class="ect-modal" role="dialog" aria-modal="true" aria-label="Edit E-Book Category"><div class="ect-dialog"><button class="ect-modal-close" type="button" id="ect-modal-close" aria-label="Close">×</button><h3>Edit Category / Icon</h3><form method="POST" id="ect-edit-form" data-ebi-form enctype="multipart/form-data">@csrf @method('PUT')
 <div class="ect-form-grid"><div><label>Category Name *</label><input class="ebook-field" name="name" maxlength="200" required></div><div><label>Sort Order</label><input class="ebook-field" name="sort_order" type="number" min="0" value="0"></div><div><label>Status</label><select class="ebook-field" name="status"><option>Active</option><option>Inactive</option></select></div></div>

 <div class="lebd-cover-upload-row">
  <label>Front Page — A4 JPG <small>(recommended 1240 × 1754 px)</small>
   <input type="file" name="front_cover" accept="image/jpeg,.jpg,.jpeg" class="ebook-field lebd-cover-file" data-cover-kind="front">
   <img class="lebd-cover-live-image" data-cover-live="front" alt="Front cover preview" hidden>
  </label>
  <label>Book Details Page — A4 JPG <small>(recommended 1240 × 1754 px; shown after Front Cover)</small>
   <input type="file" name="details_page" accept="image/jpeg,.jpg,.jpeg" class="ebook-field lebd-cover-file" data-cover-kind="details">
   <img class="lebd-cover-live-image" data-cover-live="details" alt="Book details page preview" hidden>
  </label>
  <label>Back Page — A4 JPG <small>(recommended 1240 × 1754 px)</small>
   <input type="file" name="back_cover" accept="image/jpeg,.jpg,.jpeg" class="ebook-field lebd-cover-file" data-cover-kind="back">
   <img class="lebd-cover-live-image" data-cover-live="back" alt="Back cover preview" hidden>
  </label>
 </div>
 @include('admin.ebook.partials.category-icons',['savedIcon'=>'fa-book','savedIconColor'=>'#1677ee','savedCardColor'=>'#edf4ff','previewName'=>'Category'])
 <div class="ect-modal-footer"><button class="ect-btn blue" type="submit">Save Changes</button><button class="ect-btn grey" type="button" id="ect-cancel-edit">Cancel</button></div></form></div></div>
<script>
(function(){
 document.querySelectorAll('[data-ebi-form]').forEach(function(form){
  var icon=form.querySelector('[data-ebi-icon]'), name=form.querySelector('[name="name"]'), color=form.querySelector('[name="icon_color"]'), bg=form.querySelector('[name="card_color"]');
  var preview=form.querySelector('[data-ebi-preview]'), glyph=form.querySelector('[data-ebi-preview-icon]'), text=form.querySelector('[data-ebi-preview-name]');
  var radios=form.querySelectorAll('input[data-ebi-choice]');
  function refresh(){
   var clean=(icon.value||'').trim().toLowerCase().replace(/[^a-z0-9-]/g,'').replace(/^fa-/,'')||'book';
   glyph.className='fa fa-'+clean;glyph.style.color=color.value;preview.style.backgroundColor=bg.value;text.textContent=name.value.trim()||'New Category';
   radios.forEach(function(r){r.checked=r.value===clean;r.closest('.ebi-option').classList.toggle('is-selected',r.checked);});
  }
  radios.forEach(function(r){r.addEventListener('change',function(){if(r.checked){icon.value='fa-'+r.value;refresh();}});});
  [icon,name,color,bg].forEach(function(el){el.addEventListener('input',refresh);el.addEventListener('change',refresh);});
  refresh();
 });
})();
</script>
<script>
(function(){
 const create=document.getElementById('ect-create'),modal=document.getElementById('ect-edit-modal');
 document.getElementById('ect-show-create').onclick=()=>{create.classList.add('is-open');create.scrollIntoView({behavior:'smooth',block:'start'});};
 document.getElementById('ect-hide-create').onclick=()=>create.classList.remove('is-open');
 function close(){modal.classList.remove('is-open');document.body.style.overflow='';}
 document.getElementById('ect-modal-close').onclick=close;document.getElementById('ect-cancel-edit').onclick=close;
 modal.addEventListener('click',e=>{if(e.target===modal)close();});document.addEventListener('keydown',e=>{if(e.key==='Escape')close();});
 document.querySelectorAll('.ect-edit').forEach(btn=>btn.onclick=()=>{
  const form=document.getElementById('ect-edit-form');form.action=btn.dataset.action;
  ['name','sort_order','status'].forEach(n=>{form.elements[n].value=n==='sort_order'?btn.dataset.order:btn.dataset[n];});
  form.elements.icon.value=btn.dataset.icon;form.elements.icon_color.value=btn.dataset.iconColor;form.elements.card_color.value=btn.dataset.cardColor;
  form.querySelectorAll('[data-cover-live]').forEach(function(img){var url=img.dataset.coverLive==='front'?btn.dataset.frontCover:(img.dataset.coverLive==='details'?btn.dataset.detailsPage:btn.dataset.backCover);img.hidden=!url;if(url)img.src=url;else img.removeAttribute('src');});
  form.querySelectorAll('.lebd-cover-file').forEach(function(input){input.value='';});
  form.elements.icon.dispatchEvent(new Event('input',{bubbles:true}));modal.classList.add('is-open');document.body.style.overflow='hidden';
 });
 const rows=[...document.querySelectorAll('#ect-body tr')],q=document.getElementById('ect-search'),status=document.getElementById('ect-status'),sort=document.getElementById('ect-sort');let page=1,visible=[];
 function update(){let term=q.value.toLocaleLowerCase().trim();visible=rows.filter(r=>r.dataset.name.includes(term)&&(!status.value||r.dataset.status===status.value));visible.sort((a,b)=>sort.value==='name'?a.dataset.name.localeCompare(b.dataset.name):sort.value==='newest'?Number(b.dataset.id)-Number(a.dataset.id):sort.value==='oldest'?Number(a.dataset.id)-Number(b.dataset.id):(Number(a.dataset.order)-Number(b.dataset.order)||Number(a.dataset.id)-Number(b.dataset.id)));const pages=Math.max(1,Math.ceil(visible.length/10));page=Math.max(1,Math.min(page,pages));rows.forEach(r=>r.style.display='none');visible.slice((page-1)*10,page*10).forEach((r,i)=>{r.style.display='';r.querySelector('[data-sl]').textContent=(page-1)*10+i+1;document.getElementById('ect-body').appendChild(r);});document.getElementById('ect-empty').style.display=visible.length?'none':'block';document.getElementById('ect-info').textContent=visible.length?`Showing ${(page-1)*10+1}–${Math.min(page*10,visible.length)} of ${visible.length} categories`:'Showing 0 categories';document.getElementById('ect-page').textContent=`${page} / ${pages}`;document.getElementById('ect-prev').disabled=page<=1;document.getElementById('ect-next').disabled=page>=pages;}
 document.getElementById('ect-filter').onclick=()=>{page=1;update();};document.getElementById('ect-reset').onclick=()=>{q.value='';status.value='';sort.value='order';page=1;update();};q.addEventListener('input',()=>{page=1;update();});status.addEventListener('change',()=>{page=1;update();});sort.addEventListener('change',()=>{page=1;update();});document.getElementById('ect-prev').onclick=()=>{page--;update();};document.getElementById('ect-next').onclick=()=>{page++;update();};update();
})();
</script>
@endsection

<script>
(function(){document.querySelectorAll('.lebd-cover-file').forEach(function(input){input.addEventListener('change',function(){var img=input.closest('label').querySelector('[data-cover-live]');if(!img)return;if(input.files[0]){if(img.dataset.objectUrl)URL.revokeObjectURL(img.dataset.objectUrl);img.dataset.objectUrl=URL.createObjectURL(input.files[0]);img.src=img.dataset.objectUrl;img.hidden=false;}});});})();
</script>
<link rel="stylesheet" href="{{asset('web/css/lebd-ebook-cover-v19.css')}}">
