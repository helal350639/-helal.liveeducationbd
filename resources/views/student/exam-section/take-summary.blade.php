@extends('student')
@section('title', 'Subject-wise Exam Summary — Live Education BD')
@section('stylesheets')
<style>
.lebd-sum26{background:#f2f6fe;color:#0d2a55;min-height:100vh;padding:125px 14px 45px;font-family:system-ui,-apple-system,'Segoe UI','Noto Sans Bengali',sans-serif}.lebd-sum26 *{box-sizing:border-box}
.lebd-sum26 .wrap{max-width:1100px;margin:auto;background:white;border:1px solid #d5e5fb;border-radius:18px;padding:20px;box-shadow:0 12px 32px #16345612}
.lebd-sum26 h1{font-size:26px;margin:0}.lebd-sum26 h2{font-size:18px;margin:22px 0 10px}.lebd-sum26 .muted{color:#6983a6;font-size:13px}
.lebd-sum26 .heading{display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap}.lebd-sum26 .back{border:1px solid #c8ddfa;color:#0b67d2;text-decoration:none;padding:9px 14px;border-radius:10px;font-weight:700}
.lebd-sum26 .stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px;margin:18px 0}.lebd-sum26 .stat{background:#eaf3ff;border-radius:12px;padding:15px}.lebd-sum26 .stat:nth-child(2){background:#e9f8ef}.lebd-sum26 .stat:nth-child(3){background:#fff4dc}.lebd-sum26 .stat:nth-child(4){background:#f2edff}.lebd-sum26 .stat span{font-size:12px;display:block}.lebd-sum26 .stat strong{font-size:24px;display:block;margin-top:5px}
.lebd-sum26 .scroll{overflow-x:auto;border:1px solid #d4e5fa;border-radius:12px}.lebd-sum26 table{width:100%;border-collapse:collapse;font-size:13px;min-width:0;table-layout:fixed}.lebd-sum26 th,.lebd-sum26 td{padding:10px 8px;overflow-wrap:anywhere;border-bottom:1px solid #e3edfa;text-align:left}.lebd-sum26 th{background:#e4f0ff}.lebd-sum26 .pill{display:inline-block;padding:5px 9px;border-radius:18px;font-weight:700;background:#ffeded;color:#a62c39}.lebd-sum26 .pill.pass{background:#ddf9e9;color:#057944}
.lebd-sum26 .track{width:100%;height:12px;background:#e6ecf5;border-radius:20px;overflow:hidden;margin:7px 0}.lebd-sum26 .fill{height:100%;background:#1672eb;max-width:100%}.lebd-sum26 .subject{border:1px solid #d7e5f8;padding:13px 16px;border-radius:12px;margin:9px 0}.lebd-sum26 .subject .top{display:flex;justify-content:space-between;gap:12px;font-size:13px;font-weight:700}.lebd-sum26 .note{border-radius:10px;background:#ecf5ff;padding:12px;font-size:12px;color:#42648a;margin-top:18px;line-height:1.7}
@media(max-width:650px){.lebd-sum26 table{font-size:11px}.lebd-sum26 th,.lebd-sum26 td{padding:8px 4px}.lebd-sum26 .pill{font-size:10px;padding:4px 6px;white-space:normal;text-align:center}.lebd-sum26 .scroll{overflow-x:visible} .lebd-sum26{padding:115px 9px 36px}.lebd-sum26 .wrap{padding:12px}.lebd-sum26 h1{font-size:21px}.lebd-sum26 .stats{grid-template-columns:repeat(2,minmax(0,1fr))}.lebd-sum26 .stat{padding:11px}}
@media print{.lebd-sum26{padding:0;background:white}.lebd-sum26 .back{display:none}}

.lebd-sum26 .lebd-sum-history{margin-top:20px}.lebd-sum26 .lebd-sum-bars{height:215px;display:flex;align-items:stretch;gap:8px;overflow-x:auto;padding:12px 4px;border:1px solid #d6e5fb;border-radius:12px;background:#f9fcff}
.lebd-sum26 .lebd-sum-bar-item{flex:1 0 62px;max-width:130px;display:flex;flex-direction:column;align-items:center;gap:6px;min-width:0;text-align:center;font-size:12px}
.lebd-sum26 .lebd-sum-bar-item>strong{font-size:12px;color:#0756bd}.lebd-sum26 .lebd-sum-bar-track{width:75%;min-height:125px;flex:1;background:#e8f0fb;border-radius:8px;display:flex;align-items:flex-end;overflow:hidden}.lebd-sum26 .lebd-sum-bar-fill{background:linear-gradient(180deg,#48a4ff,#0868df);width:100%;border-radius:7px 7px 0 0}.lebd-sum26 .lebd-sum-bar-item>span{font-size:10px;color:#496a93;overflow-wrap:anywhere;max-width:100%}
@media(max-width:650px){.lebd-sum26 .lebd-sum-bars{height:185px;gap:5px}.lebd-sum26 .lebd-sum-bar-item{flex-basis:48px}.lebd-sum26 .lebd-sum-bar-track{min-height:90px}}
</style>
@endsection
@section('content')
<div class="lebd-sum26"><main class="wrap">
    <div class="heading"><div><h1>📊 {{ $row->exam_name }} — Summary</h1><p class="muted">{{ $row->category_name ?? 'Exam' }} › {{ $row->routine_name ?? 'Routine' }}</p></div><a class="back" href="{{ route('student.exam-section.take.result', ['item'=>$row->id]) }}">← ফলাফলে ফিরুন</a></div>
    @if(!$visible || !$summary)
        <div class="note">ফলাফল এখনো প্রকাশিত হয়নি। Admin অনুমতি দিলে অথবা এই পরীক্ষার নির্ধারিত End Time পার হলে নিজের Subject-wise Summary দেখতে পারবেন।</div>
    @else
        <div class="stats">
            <div class="stat"><span>মোট প্রশ্ন</span><strong>{{ $summary['total'] }}</strong></div>
            <div class="stat"><span>সঠিক উত্তর</span><strong>{{ collect($summary['rows'])->sum('correct') }}</strong></div>
            <div class="stat"><span>ভুল উত্তর</span><strong>{{ collect($summary['rows'])->sum('wrong') }}</strong></div>
            <div class="stat"><span>প্রাপ্ত নম্বর</span><strong>{{ number_format($summary['earned'],2) }}</strong></div>
        </div>
        <h2>Subject-wise Performance</h2>
        <div class="scroll"><table><thead><tr><th>Subject</th><th>Total Question</th><th>Right</th><th>Wrong</th><th>Mark</th><th>Result</th></tr></thead><tbody>
        @forelse($summary['rows'] as $s)
            <tr>
                <td>{{ $s['subject'] }}</td>
                <td>{{ $s['questions'] }}</td>
                <td>{{ $s['correct'] }}</td>
                <td>{{ $s['wrong'] }}</td>
                <td>{{ number_format($s['marks'],2) }}</td>
                <td><span class="pill {{ $s['passed'] ? 'pass' : '' }}">{{ $s['passed'] ? 'Pass' : 'Fail' }}</span></td>
            </tr>
        @empty
            <tr><td colspan="6">এই পরীক্ষার Subject অনুযায়ী প্রশ্ন পাওয়া যায়নি।</td></tr>
        @endforelse
        </tbody></table></div>
        <section class="lebd-sum-history" aria-label="সর্বশেষ ১০টি পরীক্ষার নম্বর">
            <h2>সর্বশেষ ১০টি পরীক্ষার নম্বর</h2>
            @if(isset($history) && $history->isNotEmpty())
                @php $highestHistory = max(1, (float)$history->max('score')); @endphp
                <div class="lebd-sum-bars">
                  @foreach($history as $examHistory)
                    <div class="lebd-sum-bar-item">
                      <strong>{{ number_format((float)$examHistory->score, 2) }}</strong>
                      <div class="lebd-sum-bar-track"><div class="lebd-sum-bar-fill" style="height:{{ max(0,min(100,(float)$examHistory->score / $highestHistory * 100)) }}%"></div></div>
                      <span title="{{ $examHistory->exam_name }}">{{ $examHistory->exam_name }}</span>
                    </div>
                  @endforeach
                </div>
                <p class="muted">আপনার এই বিভাগের সর্বশেষ ১০টি প্রকাশযোগ্য পরীক্ষার সংরক্ষিত নম্বর।</p>
            @else
                <p class="muted">এখনো প্রকাশযোগ্য পরীক্ষার History পাওয়া যায়নি।</p>
            @endif
        </section>
        <div class="note"><strong>Pass Mark:</strong> প্রতি Subject {{ number_format($summary['subjectPass'],0) }}%, Overall {{ number_format($summary['overallPass'],0) }}%।
          <strong>Overall Result:</strong> <span class="pill {{ $summary['overallPassed'] && $summary['allSubjectsPassed'] ? 'pass' : '' }}">{{ $summary['overallPassed'] && $summary['allSubjectsPassed'] ? 'Pass' : 'Fail' }}</span>
        </div>
    @endif
</main></div>
@endsection
