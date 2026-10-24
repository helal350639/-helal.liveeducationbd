@extends('dashboard')
@section('title', $group === 'department' ? 'Premium Department List' : 'Premium Non-Department List')
@section('content')
<link rel="stylesheet" href="{{ asset('web/css/lebd-premium-class-admin-fix102.css') }}">
<div class="lebd-pd102">
  <header class="lebd-pd102-header">
    <div>
      <h2>{{ $group === 'department' ? 'Departments' : 'Non-Department Subjects' }}</h2>
      <p>{{ $group === 'department' ? 'প্রথমে Department নির্বাচন করুন। এরপর Subject → Chapter → Video যোগ করুন।' : 'প্রথমে Subject Category নির্বাচন করুন। এরপর প্রয়োজন অনুযায়ী Subject → Chapter → Video যোগ করুন।' }}</p>
    </div>
    <span>{{ count($topics) }} {{ $group === 'department' ? 'Departments' : 'Categories' }}</span>
  </header>
  <section class="lebd-pd102-panel">
    <div class="lebd-pd102-caption">{{ $group === 'department' ? 'Department List' : 'Non-Department List' }}</div>
    <div class="lebd-pd102-tablewrap">
      <table class="lebd-pd102-table">
        <thead><tr><th>SL</th><th>{{ $group === 'department' ? 'Department Name' : 'Category Name' }}</th><th>Subjects</th><th>Actions</th></tr></thead>
        <tbody>
        @foreach($topics as $index => $slug)
          <tr>
            <td>{{ $index+1 }}</td>
            <td><i class="fa {{ $group === 'department' ? 'fa-building' : 'fa-book' }}"></i> <strong>{{ $labels[$slug] ?? $slug }}</strong></td>
            <td>{{ (int)($counts[$slug] ?? 0) }}</td>
            <td>
              <a class="lebd-pd102-btn" href="{{ route('admin.premium-recordings.index', ['premium_group'=>$group,'premium_topic'=>$slug,'screen'=>'subjects']) }}"><i class="fa fa-folder-open"></i> View Subjects / Add Subject</a>
            </td>
          </tr>
        @endforeach
        </tbody>
      </table>
    </div>
  </section>
</div>
@endsection
