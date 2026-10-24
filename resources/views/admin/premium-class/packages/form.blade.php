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
<div class="pc108-actions"><button class="pc108-submit" type="submit"><i class="fa fa-save"></i> {{ $package ? 'Update Package':'Create Package' }}</button><a class="pc108-lightbtn" href="{{route('admin.premium-class-packages.index')}}">Cancel</a></div>
</form>
</section>
<p class="pc108-footnote">SSLCommerz Payment এবং Student Premium Playback Permission পরবর্তী ধাপে যুক্ত হবে। এখন এই ফর্মে Package তথ্য সংরক্ষণ করা হবে।</p>
</div>
<script src="{{asset('web/js/lebd-premium-class-packages-fix108.js')}}"></script>
@endsection
