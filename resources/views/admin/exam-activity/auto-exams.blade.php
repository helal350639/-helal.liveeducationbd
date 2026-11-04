@extends('dashboard')

@section('content')
<style>
.lebd-ea-wrap{max-width:1260px;margin:20px auto;padding:18px}.lebd-ea-head{font-size:22px;font-weight:700;margin:26px 0 14px;color:#18345a}.lebd-ea-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(265px,1fr));gap:16px}.lebd-ea-card{background:#fff;border:1px solid #e3eaf2;border-radius:15px;overflow:hidden;box-shadow:0 4px 15px rgba(0,0,0,.05)}.lebd-ea-card header{padding:15px 17px;background:#edf5ff}.lebd-ea-card h3{margin:5px 0;font-size:17px;font-weight:700;color:#1b3455}.lebd-ea-meta{padding:15px 17px;font-size:14px;color:#405169;line-height:1.8}.lebd-ea-tag{display:inline-block;padding:2px 9px;background:#d7e9ff;border-radius:12px;font-size:12px;color:#28558b}.lebd-ea-status{font-weight:700}.lebd-ea-empty{padding:18px;border:1px dashed #b8c7d8;border-radius:12px;color:#63758c}
</style>
<div class="lebd-ea-wrap">
  <h1>Exam Activity — Automatic Routine Exams</h1>
  @php($now = now())
  <section aria-labelledby="lebd-ea-batch">
    <h2 class="lebd-ea-head" id="lebd-ea-batch">Our Batch Exams (ব্যাচের পরীক্ষা) — {{ $batchExams->count() }}</h2>
    @if($batchExams->isEmpty())
      <div class="lebd-ea-empty">কোনো Published Batch Routine Exam পাওয়া যায়নি।</div>
    @else
      <div class="lebd-ea-grid">
      @foreach($batchExams as $exam)
        @php($start = $exam->starts_at ? \Carbon\Carbon::parse($exam->starts_at) : null)
        @php($end = $exam->ends_at ? \Carbon\Carbon::parse($exam->ends_at) : null)
        <article class="lebd-ea-card">
          <header><span class="lebd-ea-tag">Batch Exam</span><h3>{{ $exam->exam_name }}</h3></header>
          <div class="lebd-ea-meta">
            <div><b>Batch:</b> {{ $exam->batch_name ?? '—' }}</div>
            <div><b>Routine:</b> {{ $exam->routine_name ?? '—' }}</div>
            <div><b>Type:</b> {{ $exam->exam_type }}</div>
            <div><b>Start:</b> {{ $start ? $start->format('d M Y, h:i A') : 'Not set' }}</div>
            <div><b>End:</b> {{ $end ? $end->format('d M Y, h:i A') : 'Not set' }}</div>
            <div class="lebd-ea-status">{{ !$start ? 'Unscheduled' : ($now->lt($start) ? 'Upcoming' : ($end && $now->gt($end) ? 'Ended' : 'Ongoing / Scheduled')) }}</div>
          </div>
        </article>
      @endforeach
      </div>
    @endif
  </section>
  <section aria-labelledby="lebd-ea-section">
    <h2 class="lebd-ea-head" id="lebd-ea-section">Exam Section Exams (এক্সাম সেকশনের পরীক্ষা) — {{ $examSectionExams->count() }}</h2>
    @if($examSectionExams->isEmpty())
      <div class="lebd-ea-empty">কোনো Published Exam Section Routine Exam পাওয়া যায়নি।</div>
    @else
      <div class="lebd-ea-grid">
      @foreach($examSectionExams as $exam)
        @php($start = $exam->starts_at ? \Carbon\Carbon::parse($exam->starts_at) : null)
        @php($end = $exam->ends_at ? \Carbon\Carbon::parse($exam->ends_at) : null)
        <article class="lebd-ea-card">
          <header><span class="lebd-ea-tag">Exam Section</span><h3>{{ $exam->exam_name }}</h3></header>
          <div class="lebd-ea-meta">
            <div><b>Batch:</b> {{ $exam->batch_name ?? '—' }}</div>
            <div><b>Routine:</b> {{ $exam->routine_name }}</div>
            <div><b>Category ID:</b> {{ $exam->category_id }}</div>
            <div><b>Subcategory ID:</b> {{ $exam->subcategory_id ?? '—' }}</div>
            <div><b>Start:</b> {{ $start ? $start->format('d M Y, h:i A') : 'Not set' }}</div>
            <div><b>End:</b> {{ $end ? $end->format('d M Y, h:i A') : 'Not set' }}</div>
            <div class="lebd-ea-status">{{ !$start ? 'Unscheduled' : ($now->lt($start) ? 'Upcoming' : ($end && $now->gt($end) ? 'Ended' : 'Ongoing / Scheduled')) }}</div>
          </div>
        </article>
      @endforeach
      </div>
    @endif
  </section>
</div>
@endsection
