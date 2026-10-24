@extends('dashboard')
@section('title','Exam Section — Routine Topic Selection')
@section('content')
<style>
.ex150-landing{padding:20px 14px;min-height:55vh;font-family:'Noto Sans Bengali',Arial,sans-serif}
.ex150-landing h2{font-size:23px;font-weight:700;color:#102d5d;margin:0 0 8px}
.ex150-landing p{color:#53647a;margin-bottom:20px}
.ex150-cards{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:20px;max-width:950px}
.ex150-card{display:flex;min-height:195px;flex-direction:column;justify-content:center;align-items:center;gap:13px;background:#fff;border:2px solid #dce8f8;border-radius:14px;text-decoration:none!important;color:#0b376b!important;box-shadow:0 7px 22px rgba(21,65,125,.08);transition:.2s}
.ex150-card:hover{transform:translateY(-3px);border-color:#1b82d0;box-shadow:0 12px 30px rgba(21,65,125,.14)}
.ex150-card b{font-size:21px}.ex150-card small{font-size:13px;color:#6a7d91;text-align:center}
.ex150-symbol{width:58px;height:58px;background:#edf5ff;border-radius:15px;display:grid;place-items:center;font-size:27px}
@media(max-width:600px){.ex150-cards{grid-template-columns:1fr}.ex150-card{min-height:155px}}
</style>
<section class="ex150-landing">
<h2>Routine Topic Name</h2><p>প্রথমে Department অথবা Non-Department নির্বাচন করুন। দুই অংশের Department → Subject → Chapter আলাদা থাকবে।</p>
<div class="ex150-cards">
<a class="ex150-card" href="{{ route('admin.exam-section.routine-topic.index',['level'=>'department','kind'=>'non-department']) }}"><span class="ex150-symbol">📚</span><b>Non-Department</b><small>Non-Department → Subject → Chapter</small></a>
<a class="ex150-card" href="{{ route('admin.exam-section.routine-topic.index',['level'=>'department','kind'=>'department']) }}"><span class="ex150-symbol">🏛️</span><b>Department</b><small>Department → Subject → Chapter</small></a>
</div>
</section>
@endsection
