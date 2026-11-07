@extends('dashboard')
@section('title', $package ? 'Edit Premium Class Package' : 'Add Premium Class Package')
@section('content')
<link rel="stylesheet" href="{{asset('web/css/lebd-premium-class-packages-fix108.css')}}">
<div class="pc108">
<h2>{{ $package ? 'Edit Premium Class Package' : 'Add Premium Class Package' }}</h2>
<section class="pc108-panel">
<div class="pc108-sectionheading"><b>Package Details</b><a class="pc108-lightbtn" href="{{route('admin.premium-class-packages.index')}}">Back to List</a></div>
@if($errors->any())<div class="alert alert-danger"><ul>@foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach</ul></div>@endif
<form method="post" action="{{ $package ? route('admin.premium-class-packages.update',$package) : route('admin.premium-class-packages.store') }}" id="pc108-form">
@csrf @if($package) @method('PUT') @endif
@php
 $value=fn($key,$default='')=>old($key,$package?($package->$key??$default):$default);
 $selectedDep=old('department_categories',$package?($package->department_categories??[]):[]);
 $selectedNon=old('non_department_categories',$package?($package->non_department_categories??[]):[]);
 $type=$value('package_type','combined');
@endphp
<div class="pc108-formgrid">
 <label class="pc108-name">Package Name * <input type="text" class="form-control" name="name" maxlength="150" required value="{{ $value('name') }}" placeholder="Civil + Non-Department Monthly"></label>
 <label>Access Type * <select class="form-control" id="pc108-type" name="package_type" required>
 @foreach(['department'=>'Department','non-department'=>'Non-Department','combined'=>'Department + Non-Department'] as $k=>$label)<option value="{{$k}}" @selected($type===$k)>{{$label}}</option>@endforeach
 </select></label>
 <label class="pc108-wide">Description <small>(Student Card ও See More-এর বিবরণ)</small>
 <textarea class="form-control" name="description" rows="4" maxlength="1500">{{ $value('description') }}</textarea></label>
</div>
<div class="pc108-category-head"><b>Included Recorded Class Categories *</b> <span>(Student Homepage-এর নির্ধারিত তালিকা)</span></div>
<p class="pc108-help">যে Department বা Non-Department Category-তে প্রবেশাধিকার দিতে চান, সেগুলো নির্বাচন করুন।</p>
<div class="pc108-categories">
<section class="pc108-cat pc108-dep" id="pc108-dep">
 <div class="pc108-cathead"><div><b>▣ Department Recorded Classes</b><small>১০টি নির্ধারিত Department</small></div><div><span class="pc108-count" data-count="department">0 Selected</span><button type="button" data-action="all" data-kind="department">Select All</button><button type="button" data-action="clear" data-kind="department">Clear</button></div></div>
 <div class="pc108-checkgrid">@foreach($departments as $key=>$label)<label><input type="checkbox" name="department_categories[]" value="{{$key}}" data-cat="department" @checked(in_array($key,$selectedDep))> {{$label}}</label>@endforeach</div>
</section>
<section class="pc108-cat pc108-nondep" id="pc108-nondep">
 <div class="pc108-cathead"><div><b>▤ Non-Department Recorded Classes</b><small>৮টি নির্ধারিত বিষয়</small></div><div><span class="pc108-count" data-count="non-department">0 Selected</span><button type="button" data-action="all" data-kind="non-department">Select All</button><button type="button" data-action="clear" data-kind="non-department">Clear</button></div></div>
 <div class="pc108-checkgrid">@foreach($nonDepartments as $key=>$label)<label><input type="checkbox" name="non_department_categories[]" value="{{$key}}" data-cat="non-department" @checked(in_array($key,$selectedNon))> {{$label}}</label>@endforeach</div>
</section>
</div>
<div class="pc108-terms">
<label>Duration * <input class="form-control" type="number" min="1" max="3650" name="duration_value" required value="{{ $value('duration_value',1) }}"></label>
<label>Duration Unit * <select class="form-control" name="duration_unit">@foreach(['days'=>'Days','weeks'=>'Weeks','months'=>'Months','years'=>'Years'] as $key=>$label)<option value="{{$key}}" @selected($value('duration_unit','months')===$key)>{{$label}}</option>@endforeach</select></label>
<label>Main Price (BDT) * <input class="form-control" type="number" min="0" max="9999999.99" step="0.01" id="pc108-price" name="price" required value="{{ $value('price',300) }}"></label>
<label>Sort Order * <input class="form-control" type="number" min="0" name="sort_order" value="{{ $value('sort_order',0) }}"></label>
<label>Discount Amount (BDT) * <input class="form-control" type="number" min="0" max="9999999.99" step="0.01" id="pc108-discount" name="discount_amount" required value="{{ $value('discount_amount',0) }}"></label>
<label>Final Package Price (BDT) <output id="pc108-final" class="pc108-output pc108-green">৳ 0.00</output><small>Main Price − Discount</small></label>
<label>Student Saving <output id="pc108-saving" class="pc108-output pc108-blue">0%</output><small>Discount ÷ Main Price × 100</small></label>
<label>Status * <select class="form-control" name="is_active"><option value="1" @selected((string)$value('is_active',1)==='1')>Active</option><option value="0" @selected((string)$value('is_active',1)==='0')>Inactive</option></select></label>
</div>

{{-- LEBD-05 NEW: referral pricing only; all Fix108 controls above remain unchanged. --}}
<section class="pc108-referral" aria-labelledby="pc108-referral-title">
  <h3 id="pc108-referral-title">Package Pricing &amp; Referral Settings</h3>
  <p class="pc108-referral-help">Referral Discount শুধু বৈধ Referral Link ব্যবহারকারী Buyer-এর ক্ষেত্রে প্রযোজ্য। Bonus সফল ও যাচাইকৃত Payment-এর পরে Referrer পাবে (পরবর্তী Payment Integration ধাপে)।</p>
  <div class="pc108-referral-grid">
    <label>Referral Discount (BDT)
      <input class="form-control" type="number" name="referral_discount" id="pc108-ref-discount" value="{{ $value('referral_discount',0) }}" min="0" max="9999999.99" step="0.01">
      <small>Buyer Referral Link ব্যবহার করলে ছাড়</small>
    </label>
    <label>Referrer Bonus (BDT)
      <input class="form-control" type="number" name="referrer_bonus" id="pc108-ref-bonus" value="{{ $value('referrer_bonus',0) }}" min="0" max="9999999.99" step="0.01">
      <small>সফল Payment-এর পরে Link Owner-এর Bonus</small>
    </label>
    <label>Extra Fee (BDT)
      <input class="form-control" type="number" name="extra_fee" id="pc108-extra-fee" value="{{ $value('extra_fee',0) }}" min="0" max="9999999.99" step="0.01">
      <small>অতিরিক্ত ফি (থাকলে)</small>
    </label>
    <label>Net Price (BDT) — Referral Buyer Preview
      <output id="pc108-ref-net" class="pc108-output pc108-green" aria-live="polite">৳ 0.00</output>
      <small>Final Package Price − Referral Discount + Extra Fee</small>
    </label>
    <label class="pc108-referral-wide">Reason for Extra Fee
      <input class="form-control" type="text" name="extra_fee_note" id="pc108-extra-note" maxlength="255" value="{{ $value('extra_fee_note') }}" placeholder="No extra fee">
    </label>
  </div>
  <p class="pc108-referral-preview">Regular Buyer Total: <strong id="pc108-regular-total">৳ 0.00</strong> &nbsp;|&nbsp; Referral Buyer Total: <strong id="pc108-ref-total">৳ 0.00</strong></p>
</section>
<style>
.pc108 .pc108-referral{border:1px solid #d9e5f4;border-radius:12px;background:#f8fbff;padding:18px;margin:20px 0}
.pc108 .pc108-referral h3{font-size:18px;font-weight:700;margin:0 0 8px;color:#123664}
.pc108 .pc108-referral-help{color:#50647c;font-size:13px;margin:0 0 17px;line-height:1.6}
.pc108 .pc108-referral-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:15px 20px}
.pc108 .pc108-referral-grid label{display:block;min-width:0;font-size:13px;font-weight:600;color:#253956}
.pc108 .pc108-referral-grid input{display:block;width:100%;margin:6px 0}
.pc108 .pc108-referral-grid small{display:block;color:#66788e;font-size:11px;font-weight:400}
.pc108 .pc108-referral-wide{grid-column:1 / -1}
.pc108 .pc108-referral-preview{margin:16px 0 0;padding:12px;border-radius:8px;background:#e8f3fc;color:#173c64;line-height:1.8}
@media(max-width:650px){.pc108 .pc108-referral-grid{grid-template-columns:1fr}.pc108 .pc108-referral-wide{grid-column:auto}.pc108 .pc108-referral{padding:12px}}
</style>
<div class="pc108-actions"><button class="pc108-submit" type="submit"><i class="fa fa-save"></i> {{ $package ? 'Update Package':'Create Package' }}</button><a class="pc108-lightbtn" href="{{route('admin.premium-class-packages.index')}}">Cancel</a></div>
</form>
</section>
<p class="pc108-footnote">SSLCommerz Payment এবং Student Premium Playback Permission পরবর্তী ধাপে যুক্ত হবে। এখন এই ফর্মে Package তথ্য সংরক্ষণ করা হবে।</p>
</div>
<script src="{{asset('web/js/lebd-premium-class-packages-fix108.js')}}"></script>
<script>
(function(){
  'use strict';
  var refs={price:document.getElementById('pc108-price'),discount:document.getElementById('pc108-discount'),
    referral:document.getElementById('pc108-ref-discount'),fee:document.getElementById('pc108-extra-fee'),
    net:document.getElementById('pc108-ref-net'),regular:document.getElementById('pc108-regular-total'),total:document.getElementById('pc108-ref-total'),note:document.getElementById('pc108-extra-note')};
  if(!refs.price || !refs.discount || !refs.referral || !refs.fee)return;
  function number(el){var n=Number(el.value);return Number.isFinite(n)?Math.max(0,n):0;}
  function money(v){return '৳ '+v.toFixed(2);}
  function update(){
    var finalPrice=Math.max(0,number(refs.price)-number(refs.discount));
    var extra=number(refs.fee), refPrice=Math.max(0,finalPrice-number(refs.referral)+extra);
    refs.regular.textContent=money(finalPrice+extra);
    refs.net.textContent=money(refPrice);
    refs.total.textContent=money(refPrice);
    refs.note.required=extra>0;
  }
  [refs.price,refs.discount,refs.referral,refs.fee].forEach(function(el){el.addEventListener('input',update);el.addEventListener('change',update);});
  update();
})();
</script>
@endsection
