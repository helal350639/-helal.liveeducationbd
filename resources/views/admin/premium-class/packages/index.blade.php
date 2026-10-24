@extends('dashboard')
@section('title','Premium Class Packages')
@section('content')
<link rel="stylesheet" href="{{asset('web/css/lebd-premium-class-packages-fix108.css')}}">
<div class="pc108">
<div class="pc108-listheader"><div><h2>Premium Class Packages</h2><p>Department, Non-Department এবং Combined Recorded Class Packages</p></div><a class="pc108-submit" href="{{route('admin.premium-class-packages.create')}}"><i class="fa fa-plus"></i> Create Package</a></div>
@if(session('success'))<div class="alert alert-success">{{session('success')}}</div>@endif
@if($errors->any())<div class="alert alert-danger">@foreach($errors->all() as $e)<p>{{$e}}</p>@endforeach</div>@endif
<div class="pc108-panel"><div class="pc108-sectionheading"><b>Packages ({{ $packages->count() }})</b></div>
<div class="pc108-tablewrap"><table class="pc108-table"><thead><tr><th>SL</th><th>Package Name</th><th>Access Type</th><th>Categories</th><th>Duration</th><th>Main Price</th><th>Discount</th><th>Final Price</th><th>Status</th><th>Actions</th></tr></thead><tbody>
@forelse($packages as $package)
@php $departmentCount=count($package->department_categories??[]); $nonCount=count($package->non_department_categories??[]); @endphp
<tr><td>{{$loop->iteration}}</td><td><b>{{$package->name}}</b></td><td>{{['department'=>'Department','non-department'=>'Non-Department','combined'=>'Combined'][$package->package_type]??$package->package_type}}</td><td>D: {{$departmentCount}} / N: {{$nonCount}}</td><td>{{$package->duration_value ?: $package->validity_days}} {{ $package->duration_value ? ucfirst($package->duration_unit) : 'Days' }}</td><td>৳{{number_format((float)$package->price,2)}}</td><td>৳{{number_format((float)($package->discount_amount??0),2)}}</td><td><b>৳{{number_format((float)($package->final_price??$package->price),2)}}</b></td><td><span class="pc108-state {{ $package->is_active?'on':'off' }}">{{$package->is_active?'Active':'Inactive'}}</span></td><td class="pc108-rowactions"><a href="{{route('admin.premium-class-packages.edit',$package)}}">Edit</a>@if($package->is_active)<form method="post" action="{{route('admin.premium-class-packages.destroy',$package)}}" onsubmit="return confirm('Deactivate this package?')">@csrf @method('DELETE')<button type="submit">Deactivate</button></form>@endif</td></tr>
@empty<tr><td colspan="10">এখনো কোনো Package নেই। Create Package দিয়ে নতুন Package তৈরি করুন।</td></tr>@endforelse
</tbody></table></div>
</div></div>
@endsection
