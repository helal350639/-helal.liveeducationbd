@extends('dashboard')
@section('title', $package ? 'Edit Exam Package' : 'Create Exam Package')
@section('content')
<style>
.ex183{padding:20px;color:#16396c}.ex183 .panel{background:#fff;border:1px solid #d3e2f1;border-top:3px solid #0ea5e9;border-radius:7px}.ex183 .head{display:flex;justify-content:space-between;align-items:center;padding:15px;border-bottom:1px solid #dbe7f5;color:#ae241b}.ex183 .body{padding:18px}.ex183 label.caption{font-size:13px;font-weight:700;display:block;margin:0 0 7px}.ex183 input.text,.ex183 select.text,.ex183 textarea.text{width:100%;border:1px solid #cbd8e8;border-radius:5px;padding:11px;color:#233b58;background:#fff;box-sizing:border-box}.ex183 .grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:16px}.ex183 .span2{grid-column:span 2}.ex183 .span4{grid-column:span 4}.ex183 .categories{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}.ex183 .group{border:1px solid #cfe0fa;border-radius:9px;padding:12px;background:#f7faff}.ex183 .ghead{display:flex;justify-content:space-between;align-items:center;gap:8px;margin-bottom:10px}.ex183 .group-title{font-size:14px;font-weight:750}.ex183 .checks{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:8px}.ex183 .check{display:flex;gap:8px;align-items:center;border:1px solid #d4e1f2;background:#fff;border-radius:5px;padding:10px;cursor:pointer;font-size:13px}.ex183 .check input{width:16px;height:16px;flex:none}.ex183 button.small{padding:6px 8px;border-radius:4px;border:1px solid #d5e1ef;background:#fff;cursor:pointer;color:#275078}.ex183 .count{font-size:12px;background:#edf6ff;border-radius:20px;padding:5px}.ex183 .help{font-size:12px;color:#6980a2;margin-top:4px}.ex183 .footer{padding:13px 18px;border-top:1px solid #e6ecf4}.ex183 .btn{display:inline-block;padding:11px 15px;border:0;border-radius:5px;text-decoration:none;cursor:pointer;font-weight:700}.ex183 .primary{background:#2388c6;color:white}.ex183 .gray{background:#f0f4f8;color:#41576c}.ex183 .total{background:#ddfbea;font-weight:750}.ex183 .errors{background:#fff1f2;color:#a81c32;padding:14px;margin-bottom:15px;border-radius:6px}.ex183 .section-label{margin:8px 0 14px;font-size:15px;font-weight:750}
@media(max-width:1050px){.ex183 .categories{grid-template-columns:1fr}.ex183 .grid{grid-template-columns:repeat(2,minmax(0,1fr))}.ex183 .span4{grid-column:span 2}}@media(max-width:600px){.ex183 .grid,.ex183 .checks{grid-template-columns:1fr}.ex183 .span2,.ex183 .span4{grid-column:span 1}}
</style>
<div class="ex183">
<div class="panel">
<div class="head"><strong>{{ $package ? 'Edit Package Details' : 'Package Details' }}</strong><a href="{{ route('admin.exam-section.packages') }}" class="btn gray">Back to List</a></div>
<div class="body">
@if($errors->any())<div class="errors"><strong>Please correct:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<form method="POST" id="examPackageForm" action="{{ $package ? route('admin.exam-section.packages.update',$package->id) : route('admin.exam-section.packages.store') }}">
@csrf @if($package)@method('PUT')@endif
<div class="grid">
<div class="span2"><label class="caption">Package Name *</label><input class="text" name="name" maxlength="190" required placeholder="e.g. Job Preparation Monthly" value="{{ old('name',$package->name ?? '') }}"></div>
<div class="span2"><label class="caption">Status *</label><select class="text" name="status"><option value="Active" @selected(old('status',$package->status ?? 'Active')==='Active')>Active</option><option value="Inactive" @selected(old('status',$package->status ?? 'Active')==='Inactive')>Inactive</option></select></div>
<div class="span4"><label class="caption">Description (Student Card & See More)</label><textarea class="text" name="description" rows="3" placeholder="Package details and benefits...">{{ old('description',$package->description ?? '') }}</textarea></div>
<div class="span4"><div class="section-label">Included Exam Categories / Sub-categories * <span class="help">যে Category-তে Sub-category আছে, সেখানে Sub-category বাছুন। Sub-category না থাকলে মূল Category বাছুন।</span></div>
@php $savedScopes=old('scopes',$selected); @endphp
<div class="categories">
@forelse($groups as $category)
<div class="group" data-package-group>
<div class="ghead"><div><div class="group-title">📁 {{ $category->name }}</div><div class="help">{{ $category->children->isNotEmpty() ? $category->children->count().' Active Sub-categories' : 'No Sub-category — choose Category' }}</div></div><div style="display:flex;gap:5px;align-items:center"><span class="count" data-count>0 Selected</span><button type="button" class="small" data-select-all>Select All</button><button type="button" class="small" data-clear>Clear</button></div></div>
<div class="checks">
@if($category->children->isNotEmpty())
@foreach($category->children as $sub)
<label class="check"><input type="checkbox" name="scopes[]" value="s:{{ $sub->id }}" @checked(in_array('s:'.$sub->id,$savedScopes))><span>{{ $sub->name }}</span></label>
@endforeach
@else
<label class="check"><input type="checkbox" name="scopes[]" value="c:{{ $category->id }}" @checked(in_array('c:'.$category->id,$savedScopes))><span>{{ $category->name }} (Category)</span></label>
@endif
</div></div>
@empty<div class="help">No Active Categories found. Create a Category first.</div>@endforelse
</div></div>
<div><label class="caption">Duration *</label><input class="text" name="duration" type="number" min="1" required value="{{ old('duration',$package->duration ?? 1) }}"></div>
<div><label class="caption">Duration Unit *</label><select class="text" name="duration_unit">@foreach(['Days','Weeks','Months','Years'] as $unit)<option value="{{ $unit }}" @selected(old('duration_unit',$package->duration_unit ?? 'Months')===$unit)>{{ $unit }}</option>@endforeach</select></div>
<div><label class="caption">Main Price (BDT) *</label><input class="text" id="mainPrice" type="number" name="main_price" min="0" step="0.01" required value="{{ old('main_price',$package->main_price ?? 0) }}"></div>
<div><label class="caption">Sort Order *</label><input class="text" type="number" name="sort_order" min="0" required value="{{ old('sort_order',$package->sort_order ?? 0) }}"></div>
<div><label class="caption">Discount Amount (BDT) *</label><input class="text" id="discountPrice" type="number" name="discount_amount" min="0" step="0.01" required value="{{ old('discount_amount',$package->discount_amount ?? 0) }}"></div>
<div><label class="caption">Final Package Price (BDT)</label><input class="text total" id="finalPrice" readonly value="0.00"><div class="help">Main Price − Discount</div></div>
<div><label class="caption">Student Saving</label><input class="text" id="savingPrice" readonly value="0.00%"><div class="help">Discount ÷ Main Price × 100</div></div>
</div>
</form>
</div>
<div class="footer"><button type="submit" class="btn primary" form="examPackageForm">{{ $package ? 'Update Package' : 'Create Package' }}</button> <a href="{{ route('admin.exam-section.packages') }}" class="btn gray">Cancel</a><div class="help" style="margin-top:12px">This creates the Exam Section package and its Category/Sub-category selections. Student purchases, SSLCommerz payments, and access enforcement are not activated by this form.</div></div>
</div></div>
<script>
(function(){
function recalc(){const p=Math.max(0,Number(document.getElementById('mainPrice').value)||0),d=Math.max(0,Number(document.getElementById('discountPrice').value)||0);document.getElementById('finalPrice').value='৳ '+Math.max(0,p-d).toFixed(2);document.getElementById('savingPrice').value=(p>0?d/p*100:0).toFixed(2)+'% (৳ '+d.toFixed(2)+')';}
['mainPrice','discountPrice'].forEach(id=>document.getElementById(id).addEventListener('input',recalc));recalc();
document.querySelectorAll('[data-package-group]').forEach(group=>{const boxes=[...group.querySelectorAll('input[type=checkbox]')];function update(){group.querySelector('[data-count]').textContent=boxes.filter(c=>c.checked).length+' Selected';}group.querySelector('[data-select-all]').addEventListener('click',()=>{boxes.forEach(c=>c.checked=true);update();});group.querySelector('[data-clear]').addEventListener('click',()=>{boxes.forEach(c=>c.checked=false);update();});boxes.forEach(c=>c.addEventListener('change',update));update();});
})();
</script>
@endsection
