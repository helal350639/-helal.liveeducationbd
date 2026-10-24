@extends('dashboard')
@section('title','Student Advice Management')
@section('content')
<link rel="stylesheet" href="{{asset('web/css/lebd-student-advice-admin.css')}}">
<div class="lebd-advice-admin"><h2>সফল শিক্ষার্থীদের পরামর্শ — Admin Management</h2>
@if(session('success'))<div class="alert alert-success">{{session('success')}}</div>@endif
@if($errors->any())<div class="alert alert-danger">{{implode(' | ',$errors->all())}}</div>@endif
<section class="la-panel"><h3>নতুন সফল শিক্ষার্থী যোগ করুন</h3>
<form action="{{route('admin.student-advice.store')}}" method="POST" enctype="multipart/form-data">@csrf
<div class="la-grid"><label>নাম *<input name="name" required maxlength="150" value="{{old('name')}}"></label><label>BB ID<input name="bb_id" maxlength="60"></label><label>বিশ্ববিদ্যালয়<input name="university" maxlength="255"></label><label>চাকরির পদ *<input name="position" maxlength="255" required></label><label>প্রতিষ্ঠান<input name="organization" maxlength="255"></label><label>শিক্ষার্থীর ছবি (JPG/PNG/WebP, max 1MB)<input type="file" name="photo" accept=".jpg,.jpeg,.png,.webp"></label><label>Sort Order<input type="number" name="sort_order" value="0" min="0"></label><label>Show Status<select name="status"><option value="Published">Published</option><option value="Draft">Draft</option></select></label></div>
<label>পরামর্শ *<textarea name="advice" rows="4" maxlength="3000" required></textarea></label><button class="btn btn-success" type="submit"><i class="fa fa-plus"></i> Add Student Advice</button>
</form></section>
<section class="la-panel"><h3>Students ({{ $items->total() }})</h3>
@forelse($items as $item)
<article class="la-item"><div class="la-summary">@if($item->photo_path)<img src="{{asset($item->photo_path)}}" alt="">@endif <strong>{{$item->name}}</strong> · {{$item->position}} · {{$item->status}}</div>
<details><summary>Edit / Update</summary><form action="{{route('admin.student-advice.update',$item)}}" method="POST" enctype="multipart/form-data">@csrf @method('PUT')<div class="la-grid">
<label>নাম<input name="name" value="{{$item->name}}" required></label><label>BB ID<input name="bb_id" value="{{$item->bb_id}}"></label><label>বিশ্ববিদ্যালয়<input name="university" value="{{$item->university}}"></label><label>পদ<input name="position" value="{{$item->position}}" required></label><label>প্রতিষ্ঠান<input name="organization" value="{{$item->organization}}"></label><label>ছবি Replace<input type="file" name="photo" accept=".jpg,.jpeg,.png,.webp"></label><label>Order<input type="number" name="sort_order" value="{{$item->sort_order}}" min="0"></label><label>Status<select name="status"><option value="Published" @selected($item->status==='Published')>Published</option><option value="Draft" @selected($item->status==='Draft')>Draft</option></select></label></div><label>পরামর্শ<textarea name="advice" rows="4" required>{{$item->advice}}</textarea></label><button class="btn btn-success" type="submit">Save Changes</button></form></details>
<form action="{{route('admin.student-advice.destroy',$item)}}" method="POST" onsubmit="return confirm('Delete this student advice?')">@csrf @method('DELETE')<button class="btn btn-danger" type="submit">Delete</button></form>
</article>
@empty<p>এখনো কোনো তথ্য নেই।</p>@endforelse
{{$items->links()}}</section></div>
@endsection
