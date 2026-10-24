{{-- Shared Routine Exam card: reusable in a future student section. Expects $e, $le194Start, $le194End, $le194Live, $le194Ended. --}}
<div class="le194-card le194-exam-card"><strong><span class="le194-card-icon" aria-hidden="true">▣</span>{{ $e->exam_name }}</strong>
@if($le194Live)<span class="le194-dot" aria-label="Live Exam" title="Live Exam"></span>@endif
<small>শুরু: {{ $le194Start?->format('d M Y h:i A') ?? 'Not scheduled' }}</small>
<small>শেষ: {{ $le194End?->format('d M Y h:i A') ?? 'Not scheduled' }}</small>
<span class="le194-state {{ $le194Live ? 'live' : ($le194Ended ? 'ended' : '') }}">{{ $le194Live ? 'Live' : ($le194Ended ? 'Completed' : 'Upcoming') }}</span>
@if($le194Live)
<a href="{{ route('student.exam-section.take', ['item'=>$e->id]) }}" style="display:inline-block;margin-top:9px;background:#0a9575;color:#fff;padding:7px 12px;border-radius:7px;font-weight:700;font-size:12px">পরীক্ষা দিন →</a>
@endif
</div>
