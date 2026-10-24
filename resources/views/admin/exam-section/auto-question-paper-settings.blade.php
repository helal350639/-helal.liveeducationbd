@extends('dashboard')
@section('title', 'Shared Question Paper Settings')
@section('content')
<style>
.aq177{padding:20px;background:#f3f7fd;min-height:75vh;color:#17345c}.aq177 *{box-sizing:border-box}.aq177 .sheet{background:#fff;border:1px solid #d9e5f2;border-radius:10px;padding:20px}.aq177 h2{margin:0 0 10px;font-size:24px}.aq177 h3{font-size:17px;margin:22px 0 9px;border-bottom:1px solid #d9e5f2;padding-bottom:9px}.aq177 label{display:block;font-size:12px;font-weight:700;margin:12px 0 5px}.aq177 input,.aq177 select{width:100%;border:1px solid #cfd7e4;border-radius:5px;padding:9px;background:#fff;color:#26374c;min-height:39px}.aq177 .cols{display:grid;grid-template-columns:1fr 1fr;gap:0 24px}.aq177 .back{display:inline-block;padding:9px 12px;color:#1b5aa2;text-decoration:none}.aq177 .save{color:#fff;background:#168ac1;border:0;padding:11px 18px;border-radius:5px;cursor:pointer}.aq177 .note{background:#fff4e2;border-left:4px solid #e99b1c;padding:12px;margin-top:15px;font-size:12px}.aq177 .success{padding:12px;background:#e4f9ec;color:#11623b}.aq177 .errors{padding:12px;background:#ffeaeb;color:#ac2d37}.aq177 .tablewrap{overflow-x:auto}.aq177 table{width:100%;border-collapse:collapse;font-size:12px;min-width:690px}.aq177 th,.aq177 td{border:1px solid #e5ebf4;padding:9px;text-align:left}.aq177 th{background:#f1f5fa}.aq177 .muted{color:#66809a;font-size:12px}.aq177 .tag{font-size:11px;color:#fff;background:#208fba;padding:4px 7px;border-radius:3px}.aq177 .readonly{background:#f2f6fb}@media(max-width:750px){.aq177 .cols{grid-template-columns:1fr}.aq177{padding:10px}}
</style>
<div class="aq177">
<a class="back" href="{{ $sub ? route('admin.exam-section.auto-question.subcategories',$cat->id) : route('admin.exam-section.auto-question') }}">← {{ $sub ? 'Sub-category List' : 'Category List' }}</a>
<div class="sheet">
<h2><i class="fa fa-cog"></i> Shared Batch Question Paper Settings</h2>
<p><strong>{{ $cat->name }} @if($sub) → {{ $sub->name }} @endif</strong> — একই Group-এর সব Exam-এর জন্য একটি Setting।</p>
@if(session('success'))<p class="success">{{ session('success') }}</p>@endif
@if($errors->any())<div class="errors">{{ implode(' | ', $errors->all()) }}</div>@endif
<form method="post" action="{{ route('admin.exam-section.auto-question.paper-settings.save', $cat->id) }}">@csrf
@if($sub)<input type="hidden" name="subcategory_id" value="{{ $sub->id }}">@endif
<h3>Question Paper Settings — Admin Input</h3>
<div class="cols">
<div><label>Questions Limit</label><input type="number" name="question_limit" min="1" max="10000" required value="{{ old('question_limit',$settings['question_limit']??100) }}"></div>
<div><label>Exam Running Time (minute)</label><input type="number" name="exam_minutes" min="1" max="1440" required value="{{ old('exam_minutes',$settings['exam_minutes']??40) }}"></div>
<div><label>Mark (Correct Answer)</label><input type="number" name="correct_mark" min="0" step="0.01" required value="{{ old('correct_mark',$settings['correct_mark']??1) }}"></div>
<div><label>Mark (Wrong Answer — deducted)</label><input type="number" name="wrong_deduction" min="0" step="0.01" required value="{{ old('wrong_deduction',$settings['wrong_deduction']??0) }}"></div>
<div><label>How Many Times Can Student Take Exam?</label><input type="number" name="attempt_limit" min="1" max="100" required value="{{ old('attempt_limit',$settings['attempt_limit']??1) }}"></div>
<div><label>Show Random Questions?</label><select name="random_questions"><option value="0" @selected((string)old('random_questions',$settings['random_questions']??0)==='0')>No</option><option value="1" @selected((string)old('random_questions',$settings['random_questions']??0)==='1')>Yes</option></select></div>
<div><label>Display Questions</label><select name="display_questions"><option value="all" @selected(old('display_questions',$settings['display_questions']??'all')==='all')>All in One Paper</option><option value="one" @selected(old('display_questions',$settings['display_questions']??'all')==='one')>One by One</option></select></div>
<div><label>Paid Exam?</label><select name="paid_exam"><option value="free" @selected(old('paid_exam',$settings['paid_exam']??'free')==='free')>Free</option><option value="paid" @selected(old('paid_exam',$settings['paid_exam']??'free')==='paid')>Paid</option></select></div>
<div><label>Send Result to Account Email?</label><select name="email_result"><option value="0" @selected((string)old('email_result',$settings['email_result']??0)==='0')>No</option><option value="1" @selected((string)old('email_result',$settings['email_result']??0)==='1')>Yes — registered student account email</option></select><div class="muted">Student Registration-এ ব্যবহার করা Email-এ Result পাঠানোর পছন্দ Save হবে; Email Delivery এখন চালু নয়।</div></div>
<div><label>Student Can View Result After Exam?</label><select name="student_view_result"><option value="no" @selected(old('student_view_result',$settings['student_view_result']??'no')==='no')>No</option><option value="yes" @selected(old('student_view_result',$settings['student_view_result']??'no')==='yes')>Yes — after Exam End Date &amp; Time</option></select><div class="muted">Yes হলে পরীক্ষার Routine End Time-এর আগে Result প্রকাশ করা যাবে না।</div></div>
</div>
<h3>Auto from Routine — Exam-wise Preview</h3>
<p class="muted">Routine Edit-এর Exam No এবং Routine Item-এর Exam Date ও End Date স্বয়ংক্রিয়ভাবে পড়া হচ্ছে। ফাঁকা Date থাকলে Routine Item-এ সেট করুন।</p>
<div class="tablewrap"><table><thead><tr><th>Routine</th><th>Exam No</th><th>Exam Start</th><th>Exam End</th><th>Status (Auto)</th><th>Result Publish At (Auto)</th></tr></thead><tbody>
@forelse($routineSchedule as $exam)
@php
$start = $exam->start ? \Carbon\Carbon::parse($exam->start) : null;
$end = $exam->end ? \Carbon\Carbon::parse($exam->end) : null;
$now = now();
$status = !$start || !$end ? 'Not Scheduled' : ($now->lt($start) ? 'Upcoming' : ($now->lt($end) ? 'Running' : 'Ended'));
@endphp
<tr><td>{{ $exam->routine }}</td><td><strong>{{ $exam->exam }}</strong></td><td>{{ $start ? $start->format('d M Y, h:i A') : 'Not Scheduled' }}</td><td>{{ $end ? $end->format('d M Y, h:i A') : 'Not Scheduled' }}</td><td><span class="tag">{{ $status }}</span></td><td>{{ $end ? $end->format('d M Y, h:i A') : 'Not Scheduled' }}</td></tr>
@empty<tr><td colspan="6" class="muted">No Exam names found in this Group's Routines.</td></tr>@endforelse
</tbody></table></div>
<p class="note">Routine Date, Exam No, Status ও Result Publish Time এখানে Read-only; এগুলো আলাদা করে Input দিতে হবে না। Result Publish Time = Exam End Time। Save করলে Shared Settings সংরক্ষিত হবে, কিন্তু Question Generate, Student Exam, Email Send, Payment বা Result Publish চালু হবে না।</p>
<div style="text-align:right;margin-top:15px"><button type="submit" class="save"><i class="fa fa-save"></i> Save Shared Settings</button></div>
</form></div></div>
@endsection
