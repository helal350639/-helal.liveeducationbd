@extends('student')
@section('title', 'Merit List')
@section('stylesheets')
<style>
.lebd-merit-page{background:#f1f6fd;min-height:0;padding:18px 12px 28px;color:#142b50;font-family:inherit}
/* This page has no footer; prevent the shared legacy black footer strip from appearing. */
#Footer{display:none !important;}
.lebd-merit-wrap{max-width:1400px;margin:0 auto}
.lebd-merit-crumb{display:flex;align-items:center;gap:9px;margin:0 0 15px;color:#55708f;font-size:12px}
.lebd-merit-crumb a{color:#0871dc;text-decoration:none;font-weight:700}
.lebd-merit-back{display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:8px 14px;border:1px solid #c9def8;border-radius:10px;background:#fff;color:#075fbd !important;box-shadow:0 3px 9px rgba(22,88,157,.07);font-size:12px;font-weight:850 !important;white-space:nowrap}
.lebd-merit-back:hover{background:#edf6ff;text-decoration:none}
.lebd-merit-banner{display:flex;align-items:center;justify-content:space-between;gap:20px;position:relative;overflow:hidden;margin-bottom:14px;padding:20px 26px;border:1px solid #d5e5fa;border-radius:17px;background:linear-gradient(105deg,#fff 0%,#f4f9ff 65%,#d9ebff 100%);box-shadow:0 6px 18px rgba(29,79,139,.08)}
.lebd-merit-banner:after{content:"";position:absolute;right:-25px;bottom:-95px;width:390px;height:180px;border-radius:50%;background:linear-gradient(130deg,#c9e2ff,#e8f3ff);opacity:.8}
.lebd-merit-banner-main{display:flex;align-items:center;gap:20px;position:relative;z-index:1}
.lebd-merit-banner-icon{width:64px;height:64px;display:grid;place-items:center;flex:0 0 64px;border-radius:18px;background:linear-gradient(145deg,#35a6ff,#0865d8);color:#fff;font-size:29px;box-shadow:0 7px 14px #0873d52b}
.lebd-merit-banner h1{margin:0 0 5px;color:#102e5c;font-size:clamp(24px,3vw,32px);font-weight:900;line-height:1.15}
.lebd-merit-banner p{margin:0;color:#526985;font-size:14px}
.lebd-merit-trophy{position:relative;z-index:1;color:#3789d8;font-size:62px;padding:0 25px}
.lebd-merit-stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:13px;margin-bottom:15px}
.lebd-merit-stat{display:flex;align-items:center;gap:14px;min-width:0;padding:15px 17px;border:1px solid #dce8f7;border-radius:15px;background:linear-gradient(145deg,#fff,#f0f6ff);box-shadow:0 4px 13px rgba(27,67,117,.07)}
.lebd-merit-stat-icon{width:52px;height:52px;display:grid;place-items:center;flex:0 0 52px;border-radius:15px;background:#e2f0ff;color:#1682e7;font-size:22px}
.lebd-merit-stat:nth-child(2) .lebd-merit-stat-icon{background:#fff2d9;color:#e9a000}
.lebd-merit-stat:nth-child(3) .lebd-merit-stat-icon{background:#e4f0ff;color:#167be0}
.lebd-merit-stat:nth-child(4) .lebd-merit-stat-icon{background:#e5f4ff;color:#1485d5}
.lebd-merit-stat small{display:block;color:#657a95;font-size:11px;margin-bottom:3px}
.lebd-merit-stat strong{display:block;color:#102e5b;font-size:22px;line-height:1.2;font-weight:900;overflow-wrap:anywhere}
.lebd-merit-panels{display:grid;grid-template-columns:minmax(300px,.9fr) minmax(0,1.4fr);gap:14px;margin-bottom:14px}
.lebd-merit-panel{min-width:0;border:1px solid #dce7f5;border-radius:16px;background:#fff;padding:15px;box-shadow:0 4px 14px rgba(26,65,112,.06)}
.lebd-merit-panel-title{display:flex;align-items:center;gap:10px;margin:0 0 12px;padding-bottom:10px;border-bottom:1px solid #e7eef8;color:#173b6d;font-size:17px;font-weight:900}
.lebd-merit-panel-title i{color:#0876df}
.lebd-merit-you{display:flex;align-items:center;gap:16px;min-height:128px;padding:17px;border:1px solid #b8ddff;border-radius:14px;background:linear-gradient(110deg,#e8f5ff,#d8ecff)}
.lebd-merit-rank{flex:0 0 112px;text-align:center;color:#dca323}
.lebd-merit-rank strong{display:block;color:#133d78;font-size:35px;line-height:1.1;font-weight:950}
.lebd-merit-rank span{font-size:12px;color:#446387;font-weight:750}
.lebd-merit-you-info{min-width:0;flex:1}
.lebd-merit-you-info strong{display:block;color:#102c56;font-size:16px;font-weight:900;overflow-wrap:anywhere}
.lebd-merit-you-info p{margin:6px 0 0;color:#405a79;font-size:13px}
.lebd-merit-you-tag{display:inline-block;margin-left:6px;padding:4px 11px;border-radius:20px;background:#8265d5;color:#fff;font-size:10px;font-weight:850}
.lebd-merit-top3{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:11px;align-items:end}
.lebd-merit-podium{position:relative;min-width:0;min-height:116px;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:5px;padding:16px 9px 12px;border:1px solid #cbdcf0;border-radius:14px;background:linear-gradient(145deg,#f8fbff,#e9f3ff);text-align:center}
.lebd-merit-podium.first{min-height:135px;border-color:#f2c34f;background:linear-gradient(145deg,#fff9e7,#ffefc2)}
.lebd-merit-podium.third{border-color:#f0c5ae;background:linear-gradient(145deg,#fff5ef,#fce6dc)}
.lebd-merit-podium .medal{font-size:24px;line-height:1}
.lebd-merit-podium strong{font-size:12px;color:#18365e;overflow-wrap:anywhere}
.lebd-merit-podium small{font-size:11px;color:#506783}
.lebd-merit-podium em{font-style:normal;color:#0b4c98;font-weight:900}
.lebd-merit-bottom{display:grid;grid-template-columns:minmax(0,1fr);gap:14px}
.lebd-merit-table-wrap{overflow-x:auto}
.lebd-merit-table{width:100%;min-width:760px;border-collapse:separate;border-spacing:0;font-size:11px}
.lebd-merit-table th{padding:10px 9px;background:#e7f1ff;color:#193f70;text-align:left;font-weight:900;white-space:nowrap}
.lebd-merit-table th:first-child{border-radius:8px 0 0 8px}.lebd-merit-table th:last-child{border-radius:0 8px 8px 0}
.lebd-merit-table td{padding:9px;border-bottom:1px solid #eaf0f7;color:#2a4262;white-space:nowrap}
.lebd-merit-table tr:last-child td{border-bottom:0}
.lebd-merit-table tr.is-you td{background:#e8f3ff;color:#103e78;font-weight:850}
.lebd-merit-table .rank-pill{display:inline-grid;place-items:center;width:24px;height:24px;border-radius:50%;background:#edf3fb;color:#244d7e;font-weight:900}
.lebd-merit-table tr:nth-child(1) .rank-pill{background:#ffdf83;color:#805000}
.lebd-merit-table tr:nth-child(2) .rank-pill{background:#dce7f3;color:#40556e}
.lebd-merit-table tr:nth-child(3) .rank-pill{background:#f5c69f;color:#7d4217}
.lebd-merit-status{display:inline-block;padding:4px 8px;border-radius:20px;background:#dcf7e6;color:#087844;font-size:10px;font-weight:850}
.lebd-merit-empty{padding:28px 12px;text-align:center;border:1px dashed #cbdcf0;border-radius:12px;background:#f9fbff;color:#70849e;font-size:12px}
@media(max-width:1050px){.lebd-merit-stats{grid-template-columns:repeat(2,minmax(0,1fr))}.lebd-merit-bottom{grid-template-columns:1fr}}
@media(max-width:720px){.lebd-merit-page{padding:7px 7px 16px}.lebd-merit-crumb{gap:6px;margin-bottom:8px;font-size:11px}.lebd-merit-back{padding:6px 10px;font-size:11px;border-radius:8px}.lebd-merit-banner{padding:11px 12px;border-radius:11px;margin-bottom:9px}.lebd-merit-banner-main{gap:9px}.lebd-merit-banner-icon{width:39px;height:39px;flex-basis:39px;border-radius:11px;font-size:18px}.lebd-merit-banner h1{font-size:19px;margin-bottom:3px}.lebd-merit-banner p{font-size:10px;line-height:1.35}.lebd-merit-trophy{font-size:28px;padding:0 2px}.lebd-merit-stats{gap:6px;margin-bottom:9px}.lebd-merit-stat{padding:8px;gap:7px;border-radius:11px}.lebd-merit-stat-icon{width:30px;height:30px;flex-basis:30px;border-radius:9px;font-size:14px}.lebd-merit-stat small{font-size:8px;margin-bottom:2px}.lebd-merit-stat strong{font-size:15px}.lebd-merit-panels{grid-template-columns:1fr;gap:8px;margin-bottom:9px}.lebd-merit-panel{padding:9px;border-radius:12px}.lebd-merit-panel-title{font-size:13px;margin-bottom:8px;padding-bottom:7px}.lebd-merit-you{gap:8px;padding:9px;min-height:82px;border-radius:10px}.lebd-merit-rank{flex-basis:62px}.lebd-merit-rank strong{font-size:24px}.lebd-merit-you-info strong{font-size:12px}.lebd-merit-you-info p{font-size:10px;margin-top:4px}.lebd-merit-top3{gap:5px;grid-template-columns:repeat(3,minmax(0,1fr))}.lebd-merit-podium{padding:8px 4px;min-height:84px;border-radius:10px}.lebd-merit-podium.first{min-height:94px}.lebd-merit-podium strong{font-size:9px}.lebd-merit-podium small{font-size:8px}.lebd-merit-bottom{gap:8px}}
@media(max-width:380px){.lebd-merit-stats{grid-template-columns:1fr 1fr}.lebd-merit-top3{grid-template-columns:repeat(3,minmax(0,1fr))}.lebd-merit-podium,.lebd-merit-podium.first{min-height:76px;flex-direction:column}.lebd-merit-podium .medal{font-size:18px}.lebd-merit-you-tag{margin-left:3px;padding:3px 7px}}
</style>
@endsection

@section('content')
@php
    $formatDuration = function ($seconds) {
        $seconds = max(0, (int) $seconds);
        return sprintf('%02d:%02d', intdiv($seconds, 60), $seconds % 60);
    };
    $firstPlace = $topRows->firstWhere('rank', 1);
    $secondPlace = $topRows->firstWhere('rank', 2);
    $thirdPlace = $topRows->firstWhere('rank', 3);
    $rankSuffix = function ($rank) { $rank = (int) $rank; $mod100 = $rank % 100; if ($mod100 >= 11 && $mod100 <= 13) return 'th'; return match ($rank % 10) { 1 => 'st', 2 => 'nd', 3 => 'rd', default => 'th' }; };
@endphp
<div class="lebd-merit-page">
  <div class="lebd-merit-wrap">
    <div class="lebd-merit-crumb">
      <a class="lebd-merit-back" href="{{ route('student.exam-section.merit-list', ['category' => $categoryId, 'subcategory_id' => $subcategoryId]) }}"><i class="fa fa-arrow-left"></i> ফিরে যান</a>
      <span>›</span><strong>Merit List</strong>
    </div>

    <header class="lebd-merit-banner">
      <div class="lebd-merit-banner-main">
        <span class="lebd-merit-banner-icon"><i class="fa fa-star"></i></span>
        <div>
          <h1>Merit List</h1>
          <p>{{ $selectedExam->exam_name }} — {{ $selectedExam->routine_name }} | {{ $scopeTitle }}-এর পরীক্ষাভিত্তিক মেধা তালিকা</p>
        </div>
      </div>
      <div class="lebd-merit-trophy" aria-hidden="true"><i class="fa fa-trophy"></i></div>
    </header>

    <section class="lebd-merit-stats">
      <article class="lebd-merit-stat"><span class="lebd-merit-stat-icon"><i class="fa fa-users"></i></span><div><small>{{ !empty($isExtraGroup) ? 'Marked Students' : 'Total Participants' }}</small><strong>{{ number_format($totalParticipants) }} জন</strong></div></article>
      <article class="lebd-merit-stat"><span class="lebd-merit-stat-icon"><i class="fa fa-trophy"></i></span><div><small>Your Rank</small><strong>{{ $yourRank ? '#'.number_format($yourRank) : '—' }}</strong></div></article>
      <article class="lebd-merit-stat"><span class="lebd-merit-stat-icon"><i class="fa fa-star"></i></span><div><small>{{ !empty($isExtraGroup) ? 'Highest Mark' : 'Highest Mark' }}</small><strong>{{ number_format($highestPercent, 2) }}{{ !empty($isExtraGroup) ? ' marks' : '%' }}</strong></div></article>
      <article class="lebd-merit-stat"><span class="lebd-merit-stat-icon"><i class="fa fa-chart-column"></i></span><div><small>Average {{ !empty($isExtraGroup) ? 'Mark' : 'Mark' }}</small><strong>{{ number_format($averagePercent, 2) }}{{ !empty($isExtraGroup) ? ' marks' : '%' }}</strong></div></article>
    </section>

    <div class="lebd-merit-panels">
      <section class="lebd-merit-panel">
        <h2 class="lebd-merit-panel-title"><i class="fa fa-user"></i> Your Position</h2>
        @if($currentPosition)
          <div class="lebd-merit-you">
            <div class="lebd-merit-rank"><strong>{{ $yourRank }}{{ $rankSuffix($yourRank) }}</strong><span>Rank</span></div>
            <div class="lebd-merit-you-info">
              <strong>{{ $currentPosition['student_name'] }} <span class="lebd-merit-you-tag">You</span></strong>
              <p>Score: <b>{{ number_format($currentPosition['score'], 2) }} / {{ number_format($currentPosition['possible'], 2) }}</b></p>
              <p>{{ $category->name }} @if($subcategory) | {{ $subcategory->name }} @endif</p>
            </div>
          </div>
        @else
          <div class="lebd-merit-empty">{{ !empty($isExtraGroup) ? 'এই গ্রুপের তালিকায় আপনার কোনো Extra Student record নেই।' : 'এই পরীক্ষার কোনো প্রকাশিত ফলাফল পাওয়া যায়নি।' }}</div>
        @endif
      </section>

      <section class="lebd-merit-panel">
        <h2 class="lebd-merit-panel-title"><i class="fa fa-trophy"></i> Top 3 Performers</h2>
        @if($topRows->isNotEmpty())
          <div class="lebd-merit-top3">
            @foreach([['row'=>$secondPlace,'class'=>'second','medal'=>'🥈'],['row'=>$firstPlace,'class'=>'first','medal'=>'🥇'],['row'=>$thirdPlace,'class'=>'third','medal'=>'🥉']] as $podium)
              @if($podium['row'])
                <div class="lebd-merit-podium {{ $podium['class'] }}">
                  <span class="medal">{{ $podium['medal'] }}</span>
                  <strong>{{ $podium['row']['student_name'] }}</strong>
                  @if(!empty($podium['row']['is_extra_student']))<small style="color:#0b8b73;font-weight:800">Extra Student · {{ $podium['row']['extra_group_name'] }}</small>@endif
                  <small>Score: <em>{{ number_format($podium['row']['percent'], 2) }}%</em></small>
                </div>
              @else
                <div class="lebd-merit-podium {{ $podium['class'] }}"><span class="medal">🏅</span><strong>—</strong><small>অংশগ্রহণকারী নেই</small></div>
              @endif
            @endforeach
          </div>
        @else
          <div class="lebd-merit-empty">এখনো কোনো প্রকাশিত ফলাফল পাওয়া যায়নি।</div>
        @endif
      </section>
    </div>

    <div class="lebd-merit-bottom">
      <section class="lebd-merit-panel">
        <h2 class="lebd-merit-panel-title"><i class="fa fa-list"></i> Merit List <span style="font-size:11px;font-weight:600;color:#6b7f98">({{ $selectedExam->exam_name }} · {{ $scopeTitle }})</span><span style="margin-left:auto;font-size:11px;color:#657a95">Total: {{ number_format($totalParticipants) }} Students</span></h2>
        <p style="font-size:11px;color:#526985;margin:0 0 10px">Official exam submissions and separately labeled Extra Student simulation scores are shown together for comparison. Simulation scores are not official exam submissions and do not alter submitted exam results. Their presence does not change any submitted exam score.</p>
        @if($visibleRows->isNotEmpty())
          <div class="lebd-merit-table-wrap">
            <table class="lebd-merit-table">
              <thead><tr><th>Rank</th><th>Student Name</th><th>Category / Sub-Category</th><th>{{ !empty($isExtraGroup) ? 'Group' : 'Exam Name' }}</th><th>{{ !empty($isExtraGroup) ? 'Marks' : 'Score' }}</th><th>{{ !empty($isExtraGroup) ? 'Mark (%)' : 'Accuracy' }}</th><th>Time</th><th>Status</th></tr></thead>
              <tbody>
              @foreach($visibleRows as $row)
                <tr class="{{ (int)$row['student_id'] === (int)$student->id ? 'is-you' : '' }}">
                  <td><span class="rank-pill">{{ $row['rank'] }}</span></td>
                  <td>{{ $row['student_name'] }} @if(!empty($row['is_extra_student']))<span class="lebd-merit-you-tag" style="background:#0b8b73">Extra Student · {{ $row['extra_group_name'] }}</span>@elseif((int)$row['student_id'] === (int)$student->id)<span class="lebd-merit-you-tag">You</span>@endif</td>
                  <td>{{ $row['category_name'] }}@if($row['subcategory_name']) / {{ $row['subcategory_name'] }}@endif</td>
                  <td>{{ $row['exam_name'] }}</td>
                  <td><b>{{ !empty($row['marks_entered']) ? number_format($row['score'], 2).' / '.number_format($row['possible'], 2) : '—' }}</b></td>
                  <td>{{ !empty($row['is_extra_student']) ? '—' : number_format($row['accuracy'], 1).'%' }}</td>
                  <td>{{ !empty($isExtraGroup) ? '—' : $formatDuration($row['duration']) }}</td>
                  <td>@if(!empty($row['is_extra_student']) && empty($row['marks_entered']))<span class="lebd-merit-status" style="background:#fff1d6;color:#8a5a00">Marks Pending</span>@elseif(!empty($row['is_extra_student']))<span class="lebd-merit-status" style="background:#e5f3ff;color:#075fbd">Simulation score</span>@else<span class="lebd-merit-status">Qualified</span>@endif</td>
                </tr>
              @endforeach
              </tbody>
            </table>
          </div>

        @else
          <div class="lebd-merit-empty">এই পরীক্ষার কোনো প্রকাশিত ফলাফল বা Group-এ যুক্ত Extra Student পাওয়া যায়নি।</div>
        @endif
      </section>


    </div>
  </div>
</div>

@endsection
