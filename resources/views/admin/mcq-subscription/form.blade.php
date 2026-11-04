@extends('dashboard')

@section('title', $package ? 'Edit MCQ Package' : 'Add MCQ Package')

@section('content')

@php

  $edit = $package !== null;

  $access = old('access_type', $edit ? $package->access_type : 'both');

  $selectedIds = array_map('intval', old('category_ids', $selected));

@endphp

<style>

.mcqsub-section-wrap{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px;margin-top:10px}

.mcqsub-section{border:1px solid #dce7f4;border-radius:10px;padding:13px;background:#fff;min-width:0}

.mcqsub-section-header{display:flex;align-items:center;justify-content:space-between;gap:8px;margin-bottom:12px;flex-wrap:wrap}

.mcqsub-section-header h4{font-size:16px;font-weight:700;margin:0}

.mcqsub-section-header small{display:block;color:#6c7888;margin-top:3px}

.mcqsub-section.department{background:#fff9f4;border-color:#ffdfc8}

.mcqsub-section.non-department{background:#f5f8ff;border-color:#d4e1ff}

.mcqsub-actions{display:flex;gap:5px;align-items:center;flex-wrap:wrap}

.mcqsub-options{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:8px}

.mcqsub-options label{display:flex;align-items:center;gap:8px;cursor:pointer;border:1px solid #dce7f4;background:#fff;border-radius:7px;padding:10px;min-height:43px;font-weight:500;margin:0;overflow-wrap:anywhere}

.mcqsub-options label:hover{border-color:#4794dc;background:#f9fcff}

.mcqsub-options label input{margin:0;flex:0 0 auto}

.mcqsub-options label.mcqsub-disabled{opacity:.6;cursor:not-allowed}

.mcqsub-pill{display:inline-block;background:#fff;border:1px solid #dce7f4;border-radius:30px;padding:3px 9px;font-size:12px;white-space:nowrap}

.mcqsub-section[hidden]{display:none!important}

@media(max-width:1100px){.mcqsub-section-wrap{grid-template-columns:1fr}}

@media(max-width:550px){.mcqsub-options{grid-template-columns:1fr}.mcqsub-section{padding:10px}}


/* LEBD-05 Admin-only Pricing & Referral Settings — no changes to category controls */
.mcqsub-pricing-card{border:1px solid #d4e1f1;border-radius:8px;margin:17px 0 12px;overflow:hidden;background:#fff}
.mcqsub-pricing-heading{padding:10px 14px;background:#eff5fc;border-bottom:1px solid #d4e1f1;color:#123c76;font-size:15px;font-weight:700}
.mcqsub-pricing-body{padding:14px 14px 2px}
.mcqsub-pricing-note{display:block;color:#65758d;font-size:12px;margin-top:5px}
.mcqsub-pricing-preview{border:1px solid #bed6f2;background:#f4f8fe;border-radius:8px;padding:12px 15px;margin-bottom:14px}
.mcqsub-pricing-preview strong{font-size:15px;color:#11623d}
.mcqsub-pricing-preview .mcqsub-preview-line{padding:4px 0;line-height:1.6}
.mcqsub-price-readonly{font-weight:700!important;background:#ecfff1!important;color:#087a3e!important}
@media(max-width:767px){.mcqsub-pricing-body{padding:12px 10px 0}}
</style>

<section class="content-header"><h1>{{ $edit ? 'Edit' : 'Add' }} MCQ Subscription Package</h1></section>

<section class="content">

 @if($errors->any())<div class="alert alert-danger"><strong>Save হয়নি:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

 <div class="box box-primary"><div class="box-header with-border"><h3 class="box-title">Package Details</h3><a class="btn btn-default pull-right" href="{{ route('admin.mcq-subscription.index') }}">Back to List</a></div>

 <form method="POST" action="{{ $edit ? route('admin.mcq-subscription.update', $package->id) : route('admin.mcq-subscription.store') }}">@csrf @if($edit)@method('PUT')@endif

 <div class="box-body">

 <div class="row"><div class="col-md-8 form-group"><label>Package Name *</label><input class="form-control" name="name" required maxlength="180" value="{{ old('name', $edit ? $package->name : '') }}" placeholder="Civil + Non-Department Monthly"></div>

 <div class="col-md-4 form-group"><label>Access Type *</label><select class="form-control" id="mcqsub-kind" name="access_type" required>

 @foreach(['department'=>'Department Only','non-department'=>'Non-Department Only','both'=>'Department + Non-Department'] as $key=>$label)<option value="{{ $key }}" @selected($access === $key)>{{ $label }}</option>@endforeach

 </select></div></div>

 <div class="form-group"><label>Description <small class="text-muted">(Student Card-এ প্রথম ৪ লাইন, See More-এ সম্পূর্ণ বিবরণ)</small></label><textarea name="description" rows="5" maxlength="2000" class="form-control">{{ old('description', $edit ? $package->description : '') }}</textarea></div>

 <div class="form-group">

   <label>Included MCQ Categories * <span class="text-muted">(Auto-loaded from existing active MCQ categories)</span></label>

   <p class="help-block" style="margin:3px 0 5px">Student Homepage-এর মতো Department এবং Non-Department আলাদা। দুই Section-এর Category বেছে এক Package করা যাবে।</p>

   <div class="mcqsub-section-wrap" id="mcqsub-category-sections">

     <div class="mcqsub-section department" data-section="department">

       <div class="mcqsub-section-header">

         <div><h4><i class="fa fa-briefcase"></i> Department MCQ Study</h4><small>Existing Department categories</small></div>

         <div class="mcqsub-actions"><span class="mcqsub-pill"><span data-count="department">0</span> Selected</span><button type="button" class="btn btn-default btn-xs" data-select="department">Select All</button><button type="button" class="btn btn-default btn-xs" data-clear="department">Clear</button></div>

       </div>

       <div class="mcqsub-options">

         @php $deptCount = 0; @endphp

         @foreach($categories as $cat)

           @if($cat->kind === 'department' && ($cat->status === 'Active' || in_array((int)$cat->id, $selectedIds, true)))

             @php $deptCount++; @endphp

             <label class="{{ $cat->status !== 'Active' ? 'mcqsub-disabled' : '' }}">

               <input type="checkbox" name="category_ids[]" value="{{ $cat->id }}" data-kind="department" @checked(in_array((int)$cat->id, $selectedIds, true)) @disabled($cat->status !== 'Active')>

               <span>{{ $cat->name }} @if($cat->status !== 'Active')<small class="text-muted">(Inactive)</small>@endif</span>

             </label>

           @endif

         @endforeach

         @if($deptCount === 0)<p class="text-muted">কোনো Active Department Category পাওয়া যায়নি।</p>@endif

       </div>

     </div>

     <div class="mcqsub-section non-department" data-section="non-department">

       <div class="mcqsub-section-header">

         <div><h4><i class="fa fa-book"></i> Non-Department MCQ Study</h4><small>Existing Non-Department categories</small></div>

         <div class="mcqsub-actions"><span class="mcqsub-pill"><span data-count="non-department">0</span> Selected</span><button type="button" class="btn btn-default btn-xs" data-select="non-department">Select All</button><button type="button" class="btn btn-default btn-xs" data-clear="non-department">Clear</button></div>

       </div>

       <div class="mcqsub-options">

         @php $nonDeptCount = 0; @endphp

         @foreach($categories as $cat)

           @if($cat->kind === 'non-department' && ($cat->status === 'Active' || in_array((int)$cat->id, $selectedIds, true)))

             @php $nonDeptCount++; @endphp

             <label class="{{ $cat->status !== 'Active' ? 'mcqsub-disabled' : '' }}">

               <input type="checkbox" name="category_ids[]" value="{{ $cat->id }}" data-kind="non-department" @checked(in_array((int)$cat->id, $selectedIds, true)) @disabled($cat->status !== 'Active')>

               <span>{{ $cat->name }} @if($cat->status !== 'Active')<small class="text-muted">(Inactive)</small>@endif</span>

             </label>

           @endif

         @endforeach

         @if($nonDeptCount === 0)<p class="text-muted">কোনো Active Non-Department Category পাওয়া যায়নি।</p>@endif

       </div>

     </div>

   </div>

   <p class="help-block">নতুন Category Admin-এর Job Preparation Module-এ তৈরি করলে এখানে স্বয়ংক্রিয়ভাবে দেখাবে। Inactive Category নতুন করে নির্বাচন করা যাবে না।</p>

 </div>

 <div class="row"><div class="col-md-3 form-group"><label>Duration *</label><input class="form-control" name="duration_value" type="number" min="1" max="6000" required value="{{ old('duration_value', $edit ? $package->duration_value : 1) }}"></div>

 <div class="col-md-3 form-group"><label>Duration Unit *</label><select class="form-control" name="duration_unit">@foreach(['days'=>'Days','months'=>'Months','years'=>'Years'] as $key=>$label)<option value="{{ $key }}" @selected(old('duration_unit', $edit ? $package->duration_unit : 'months') === $key)>{{ $label }}</option>@endforeach</select></div>

 <div class="col-md-3 form-group"><label>Main Price (BDT) *</label><input id="lebd-main" class="form-control" name="main_price" type="number" step="0.01" min="0" required value="{{ old('main_price', $edit ? $package->main_price : 300) }}"></div>

 <div class="col-md-3 form-group"><label>Sort Order *</label><input class="form-control" name="sort_order" type="number" min="0" required value="{{ old('sort_order', $edit ? $package->sort_order : 0) }}"></div></div>

 <div class="row">

   <div class="col-md-4 form-group"><label>Discount Amount (BDT) *</label><input id="lebd-discount" name="discount_amount" class="form-control" type="number" step="0.01" min="0" required value="{{ old('discount_amount', $edit ? $package->discount_amount : 100) }}"></div>

   <div class="col-md-4 form-group"><label>Final Package Price (BDT)</label><input id="lebd-final" class="form-control" type="text" readonly style="font-weight:700;background:#ecfff1;color:#087a3e"><small class="text-muted">Save হওয়া `price` = Main Price − Discount</small></div>

   <div class="col-md-4 form-group"><label>Student Saving</label><input id="lebd-saving" class="form-control" type="text" readonly style="font-weight:700;background:#eff6ff;color:#2057b6"><small class="text-muted">Discount ÷ Main Price × 100</small></div>

 </div>


 <div class="mcqsub-pricing-card" id="mcqsub-referral-pricing">
   <div class="mcqsub-pricing-heading"><i class="fa fa-tags"></i> Package Pricing &amp; Referral Settings</div>
   <div class="mcqsub-pricing-body">
     <div class="row">
       <div class="col-md-3 form-group">
         <label>Referral Discount (BDT)</label>
         <input class="form-control" id="lebd-referral-discount" name="referral_discount" type="number" min="0" max="9999999.99" step="0.01" value="{{ old('referral_discount', $edit ? ($package->referral_discount ?? 0) : 0) }}">
         <small class="mcqsub-pricing-note">Eligible referred student discount; checkout not modified in this phase.</small>
       </div>
       <div class="col-md-3 form-group">
         <label>Referrer Bonus (BDT)</label>
         <input class="form-control" id="lebd-referrer-bonus" name="referrer_bonus" type="number" min="0" max="9999999.99" step="0.01" value="{{ old('referrer_bonus', $edit ? ($package->referrer_bonus ?? 0) : 0) }}">
         <small class="mcqsub-pricing-note">Bonus configuration only; no wallet credits in this phase.</small>
       </div>
       <div class="col-md-3 form-group">
         <label>Extra Fee (BDT)</label>
         <input class="form-control" id="lebd-extra-fee" name="extra_fee" type="number" min="0" max="9999999.99" step="0.01" value="{{ old('extra_fee', $edit ? ($package->extra_fee ?? 0) : 0) }}">
         <small class="mcqsub-pricing-note">Additional fee for display/calculation only in Admin.</small>
       </div>
       <div class="col-md-3 form-group">
         <label>Net Price (BDT)</label>
         <input class="form-control mcqsub-price-readonly" id="lebd-net-price" type="text" readonly aria-label="Referral net price preview">
         <small class="mcqsub-pricing-note">Final Price − Referral Discount + Extra Fee.</small>
       </div>
     </div>
     <div class="row">
       <div class="col-md-6 form-group">
         <label>Reason for Extra Fee</label>
         <input class="form-control" name="extra_fee_note" maxlength="500" value="{{ old('extra_fee_note', $edit ? ($package->extra_fee_note ?? '') : '') }}" placeholder="e.g., service charge (optional)">
       </div>
       <div class="col-md-6">
         <div class="mcqsub-pricing-preview" aria-live="polite">
           <b>হিসাবের পূর্ববীক্ষণ</b>
           <div class="mcqsub-preview-line" id="lebd-pricing-preview-base"></div>
           <div class="mcqsub-preview-line" id="lebd-pricing-preview-net"></div>
           <div class="mcqsub-preview-line" id="lebd-pricing-preview-bonus"></div>
           <small class="mcqsub-pricing-note">Admin preview only. Stored `price` and existing student payment amount remain unchanged.</small>
         </div>
       </div>
     </div>
   </div>
 </div>

 <div class="row"><div class="col-md-4 form-group"><label>Paid Reading Limit</label><input class="form-control" name="reading_limit" type="number" min="1" placeholder="Empty = Unlimited" value="{{ old('reading_limit', $edit ? $package->reading_limit : '') }}"><p class="help-block">Blank means unlimited (per day; applied in next step).</p></div>

 <div class="col-md-4 form-group"><label>Paid Quiz Limit</label><input class="form-control" name="quiz_limit" type="number" min="1" placeholder="Empty = Unlimited" value="{{ old('quiz_limit', $edit ? $package->quiz_limit : '') }}"><p class="help-block">Blank means unlimited (per month; applied in next step).</p></div>

 <div class="col-md-4 form-group"><label>Status *</label><select name="status" class="form-control">@foreach(['Active','Inactive'] as $s)<option value="{{ $s }}" @selected(old('status', $edit ? $package->status : 'Active') === $s)>{{ $s }}</option>@endforeach</select></div></div>

 </div><div class="box-footer"><button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> {{ $edit ? 'Save Changes' : 'Create Package' }}</button> <a href="{{ route('admin.mcq-subscription.index') }}" class="btn btn-default">Cancel</a></div></form></div>

</section>

<script>

(function () {

  'use strict';

  const kind = document.getElementById('mcqsub-kind');

  const sections = document.querySelectorAll('#mcqsub-category-sections [data-section]');

  if (!kind || !sections.length) return;

  const getBoxes = section => Array.from(section.querySelectorAll('input[type="checkbox"][name="category_ids[]"]'));

  function counts() {

    sections.forEach(section => {

      const text = section.querySelector('[data-count]');

      if (text) text.textContent = String(getBoxes(section).filter(box => box.checked && !box.disabled).length);

    });

  }

  function visibility() {

    sections.forEach(section => {

      const show = kind.value === 'both' || section.dataset.section === kind.value;

      section.hidden = !show;

      // Unselected category kinds should not be submitted. Remember the choices if switching back.

      getBoxes(section).forEach(box => {

        if (box.dataset.originalDisabled === undefined) box.dataset.originalDisabled = box.disabled ? '1' : '0';

        box.disabled = !show || box.dataset.originalDisabled === '1';

      });

    });

    counts();

  }

  sections.forEach(section => {

    section.addEventListener('change', counts);

    section.querySelector('[data-select]')?.addEventListener('click', () => {

      getBoxes(section).forEach(box => { if (!box.disabled) box.checked = true; });

      counts();

    });

    section.querySelector('[data-clear]')?.addEventListener('click', () => {

      getBoxes(section).forEach(box => { if (!box.disabled) box.checked = false; });

      counts();

    });

  });

  kind.addEventListener('change', visibility);

  visibility();

  const main = document.getElementById('lebd-main');

  const discount = document.getElementById('lebd-discount');

  const final = document.getElementById('lebd-final');

  const saving = document.getElementById('lebd-saving');

  function showPrices() {

    const m = Number(main.value || 0), d = Number(discount.value || 0);

    const valid = Number.isFinite(m) && Number.isFinite(d) && m >= 0 && d >= 0 && d <= m;

    final.value = valid ? '৳ ' + (m - d).toFixed(2) : 'Invalid discount';

    saving.value = valid && m > 0 ? ((d / m) * 100).toFixed(2) + '% (৳ ' + d.toFixed(2) + ')' : (valid ? '0%' : 'Discount বেশি');

  }

  main.addEventListener('input', showPrices);

  discount.addEventListener('input', showPrices);

  showPrices();

  // New fields affect Admin display only; existing price & payment calculations stay untouched.
  const referralDiscount = document.getElementById('lebd-referral-discount');
  const referrerBonus = document.getElementById('lebd-referrer-bonus');
  const extraFee = document.getElementById('lebd-extra-fee');
  const netPrice = document.getElementById('lebd-net-price');
  const previewBase = document.getElementById('lebd-pricing-preview-base');
  const previewNet = document.getElementById('lebd-pricing-preview-net');
  const previewBonus = document.getElementById('lebd-pricing-preview-bonus');
  function updateReferralPreview() {
    const m = Number(main.value || 0);
    const d = Number(discount.value || 0);
    const r = Number(referralDiscount.value || 0);
    const b = Number(referrerBonus.value || 0);
    const f = Number(extraFee.value || 0);
    const valid = [m,d,r,b,f].every(x => Number.isFinite(x) && x >= 0) && d <= m && r <= (m-d) && (m-d-r+f) >= 0;
    if (!valid) {
      netPrice.value = 'Invalid amount';
      previewBase.textContent = 'মূল্য / ছাড় সঠিকভাবে দিন।';
      previewNet.textContent = 'Referral Discount, Final Price-এর বেশি হতে পারবে না।';
      previewBonus.textContent = '';
      return;
    }
    const base = m - d;
    const net = base - r + f;
    netPrice.value = '৳ ' + net.toFixed(2);
    previewBase.textContent = 'Main ৳' + m.toFixed(2) + ' − Discount ৳' + d.toFixed(2) + ' = Final ৳' + base.toFixed(2);
    previewNet.textContent = 'Final ৳' + base.toFixed(2) + ' − Referral ৳' + r.toFixed(2) + ' + Extra Fee ৳' + f.toFixed(2) + ' = Net ৳' + net.toFixed(2);
    previewBonus.textContent = 'Referrer Bonus (separately configured): ৳' + b.toFixed(2);
  }
  [main, discount, referralDiscount, referrerBonus, extraFee].forEach(input => input?.addEventListener('input', updateReferralPreview));
  updateReferralPreview();

})();

</script>

@endsection
