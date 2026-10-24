@extends('dashboard')
@section('content')
<section class="content-header"><h1>MCQ for Exam — Edit / Delete</h1></section>
<section class="content">
 <div class="box box-primary"><div class="box-body">
 <a href="{{ route('admin.exam-mcq.index',['kind'=>$kind]) }}" class="btn btn-default">&larr; Back to {{ $kind === 'department' ? 'Department' : 'Non-Department' }} Categories</a>
 <a href="{{ route('admin.exam-mcq.manage.index',['kind'=>$kind==='department'?'non-department':'department']) }}" class="btn btn-info">Switch to {{ $kind==='department'?'Non-Department':'Department' }}</a>
 <p class="text-muted" style="margin-top:12px">Edit name, sort order or status. Deletion is blocked when a category contains subjects, a subject contains chapters, or any item has linked questions / Routine selections.</p>
 @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
 @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
 @if($errors->any())<div class="alert alert-danger">{{ implode(' | ', $errors->all()) }}</div>@endif
 <style>.lebd62-unit{border:1px solid #dae4ef;padding:12px;margin:12px 0;border-radius:7px;background:#fff}.lebd62-sub{margin:9px 0 9px 22px;background:#f5f9ff}.lebd62-ch{margin-left:42px;background:#fafafa}.lebd62-line{display:flex;align-items:center;gap:8px;flex-wrap:wrap}.lebd62-line input[name=name]{min-width:170px;flex:1}.lebd62-line input[name=sort_order]{width:76px}.lebd62-line select{width:115px}.lebd62-label{min-width:100px;font-weight:700}.lebd62-form{display:flex;align-items:center;gap:6px;flex-wrap:wrap;flex:1}.lebd62-note{font-size:12px;color:#666}</style>
 @forelse($categories as $category)
 <div class="lebd62-unit">
 @include('admin.exam-mcq.manage-row', ['entry'=>$category,'level'=>'category','kind'=>$kind])
 @foreach($subjects->get($category->id,collect()) as $subject)
 <div class="lebd62-unit lebd62-sub">
 @include('admin.exam-mcq.manage-row', ['entry'=>$subject,'level'=>'subject','kind'=>$kind])
 @foreach($chapters->get($subject->id,collect()) as $chapter)
 <div class="lebd62-unit lebd62-ch">
 @include('admin.exam-mcq.manage-row', ['entry'=>$chapter,'level'=>'chapter','kind'=>$kind])
 </div>
 @endforeach
 </div>
 @endforeach
 </div>
 @empty <p>No categories found.</p> @endforelse
 </div></div>
</section>
@endsection
