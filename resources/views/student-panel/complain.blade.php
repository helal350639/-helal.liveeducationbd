@php
    $user = Auth::guard('student')->user();
    $student = $user ? \App\Models\Student::with(['batches','departments'])->find($user->id) : null;
    $myBatches = $student ? $student->batches->unique('id') : collect();
    $myDepartments = $student ? $student->departments->unique('id') : collect();

    // Batch and Department must come from the exam dashboard URL, never from a hardcoded ID.
    $requestedBatchId = (int) old('batch_id', request()->query('batch_id', 0));
    $requestedDepartmentId = (int) old('department_id', request()->query('department_id', 0));
    $batch = $requestedBatchId ? $myBatches->firstWhere('id', $requestedBatchId) : ($myBatches->count() === 1 ? $myBatches->first() : null);
    $department = $requestedDepartmentId ? $myDepartments->firstWhere('id', $requestedDepartmentId) : ($myDepartments->count() === 1 ? $myDepartments->first() : null);

    // Never accept a submitted or URL-supplied batch / department that is not owned by the student.
    if ($requestedBatchId && !$batch) abort(403);
    if ($requestedDepartmentId && !$department) abort(403);

    $canSubmit = (bool) ($user && $batch && $department);
@endphp
@extends('student')
@section('title', 'অভিযোগ ও পরামর্শ')
@section('content')
<style>
.lebd-complaint-page{background:linear-gradient(145deg,#eef6ff,#f7fbff);padding:24px 12px 42px;min-height:65vh;font-family:Arial,'Noto Sans Bengali',sans-serif;color:#102852}
.lebd-complaint-shell{max-width:690px;margin:auto;border:1px solid #d8e9ff;border-radius:21px;padding:20px;background:#fff;box-shadow:0 12px 35px rgba(16,75,148,.08)}
.lebd-complaint-head{display:flex;align-items:center;gap:14px;padding:9px 3px 22px;border-bottom:1px solid #ecf2fa;margin-bottom:22px}
.lebd-complaint-icon{display:grid;place-items:center;flex-shrink:0;width:60px;height:60px;border-radius:18px;background:#e8f3ff;color:#075fda;font-size:26px}
.lebd-complaint-head h1{margin:0 0 5px;font-size:24px;font-weight:800;line-height:1.4;color:#102852}
.lebd-complaint-head p{margin:0;color:#61748e;font-size:13px;line-height:1.6}
.lebd-complaint-group{margin:0 0 19px}
.lebd-complaint-group label{display:block;margin:0 0 8px;font-size:15px;font-weight:700;color:#122b50}
.lebd-complaint-group textarea{box-sizing:border-box;display:block;width:100%;min-height:145px;padding:15px 16px;border:1px solid #cbdcf1;border-radius:13px;background:#fff;color:#182b49;line-height:1.75;font-size:15px;resize:vertical;outline:none}
.lebd-complaint-group textarea:focus{border-color:#1875e8;box-shadow:0 0 0 3px rgba(24,117,232,.10)}
.lebd-complaint-submit{display:flex;justify-content:center;align-items:center;gap:8px;width:100%;min-height:50px;background:linear-gradient(95deg,#0861e9,#16b5f3);border:0;border-radius:12px;color:#fff;font-size:16px;font-weight:800;cursor:pointer}
.lebd-complaint-submit:disabled{cursor:not-allowed;opacity:.55}
.lebd-complaint-error{padding:12px 14px;border-radius:10px;background:#fff1ee;border:1px solid #fed4cb;color:#983b2f;margin:0 0 18px;font-size:13px;line-height:1.7}
.lebd-complaint-back{display:inline-flex;margin-bottom:15px;text-decoration:none;color:#126bd7;font-size:13px;font-weight:700}
@media(max-width:600px){.lebd-complaint-page{padding:14px 8px 30px}.lebd-complaint-shell{padding:14px;border-radius:15px}.lebd-complaint-head{gap:10px;padding-bottom:14px;margin-bottom:18px}.lebd-complaint-icon{width:47px;height:47px;font-size:22px;border-radius:13px}.lebd-complaint-head h1{font-size:19px}.lebd-complaint-head p{font-size:11px}.lebd-complaint-group label{font-size:14px}.lebd-complaint-group textarea{font-size:14px;min-height:130px;padding:12px}}
</style>
<section class="lebd-complaint-page">
 <div class="lebd-complaint-shell">
  <a class="lebd-complaint-back" href="{{ $batch && \Illuminate\Support\Facades\Route::has('students.batch.purchased-exams') ? route('students.batch.purchased-exams', ['batch'=>$batch->id, 'department_id'=>$department?->id]) : route('students.dashboard') }}">&#8592; ফিরে যান</a>
  <div class="lebd-complaint-head">
   <div class="lebd-complaint-icon"><i class="fa fa-comments" aria-hidden="true"></i></div>
   <div><h1>অভিযোগ ও পরামর্শ</h1><p>আপনার সমস্যা ও সমাধানের মতামত আমাদের জানান।</p></div>
  </div>
  @if(session('error'))<div class="lebd-complaint-error" role="alert">{{ session('error') }}</div>@endif
  @if($errors->any())<div class="lebd-complaint-error" role="alert">{{ $errors->first() }}</div>@endif
  @unless($canSubmit)
   <div class="lebd-complaint-error" role="alert">এই অভিযোগটি কোন ব্যাচ ও ডিপার্টমেন্টের, তা নিশ্চিত করা যায়নি। অনুগ্রহ করে আপনার কেনা ব্যাচের Exam Dashboard থেকে Complain/Suggestion খুলুন।</div>
  @endunless
  <form action="{{ route('students.complain.store') }}" method="POST">
   @csrf
   {{-- Keep the exact input names required by the existing complaint controller; identity is hidden in UI. --}}
   <input type="hidden" name="name" value="{{ $user?->name ?? '' }}">
   <input type="hidden" name="email" value="{{ $user?->email ?? '' }}">
   <input type="hidden" name="contact" value="{{ $user?->contact ?? '' }}">
   @if($canSubmit)
    <input type="hidden" name="batch_id" value="{{ $batch->id }}">
    <input type="hidden" name="department_id" value="{{ $department->id }}">
   @endif
   <div class="lebd-complaint-group">
    <label for="lebdComplaintDetails">আপনার অভিযোগ লিখুন <span style="color:#d42a2a">*</span></label>
    <textarea id="lebdComplaintDetails" name="details" placeholder="আপনার সমস্যাটি বিস্তারিত লিখুন..." required>{{ old('details') }}</textarea>
   </div>
   <div class="lebd-complaint-group">
    <label for="lebdComplaintSolution">সমাধানে আপনার মতামত দিন</label>
    <textarea id="lebdComplaintSolution" name="solution" placeholder="সমাধানের বিষয়ে আপনার পরামর্শ লিখুন...">{{ old('solution') }}</textarea>
   </div>
   <button class="lebd-complaint-submit" type="submit" @unless($canSubmit) disabled @endunless><i class="fa fa-paper-plane" aria-hidden="true"></i> সাবমিট করুন</button>
  </form>
 </div>
</section>
@endsection
