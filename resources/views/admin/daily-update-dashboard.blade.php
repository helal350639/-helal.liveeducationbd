{{--
    LEBD | Admin Daily Update Dashboard | STEP 1: DEMO UI
    Save: resources/views/admin/daily-update-dashboard.blade.php
    Uses your existing Admin layout (@extends('dashboard')).
    Does not write/read any Daily Update database tables.
    Controller, authenticated admin route, CRUD and database integration are subsequent steps.
--}}
@extends('dashboard')
@section('title', 'Daily Update Management | Live Education BD')
@section('content')
@php
    // Sample data only. Do NOT treat these numbers as genuine site statistics.
    $duStats = [
        ['icon' => '▣', 'label' => 'Total Updates', 'value' => 125, 'detail' => 'নমুনা আপডেট', 'tone' => 'blue'],
        ['icon' => '✓', 'label' => 'Published', 'value' => 98, 'detail' => 'নমুনা প্রকাশিত', 'tone' => 'green'],
        ['icon' => '◴', 'label' => 'Unpublished', 'value' => 12, 'detail' => 'নমুনা ড্রাফট', 'tone' => 'orange'],
        ['icon' => '▤', 'label' => 'Categories', 'value' => 5, 'detail' => 'নমুনা ক্যাটাগরি', 'tone' => 'violet'],
    ];
    $duCategories = [
        ['title' => 'কারেন্ট অ্যাফেয়ার্স', 'slug' => 'current-affairs', 'total' => 55, 'color' => '#2686f4'],
        ['title' => 'মাসিক আপডেট', 'slug' => 'monthly-update', 'total' => 32, 'color' => '#7653d6'],
        ['title' => 'সাধারণ জ্ঞান', 'slug' => 'general-knowledge', 'total' => 18, 'color' => '#14b88a'],
        ['title' => 'আন্তর্জাতিক', 'slug' => 'international', 'total' => 12, 'color' => '#f1a43b'],
        ['title' => 'বিজ্ঞান', 'slug' => 'science', 'total' => 8, 'color' => '#e86b7e'],
    ];
    $duRecent = [
        ['question' => 'বাংলাদেশের রাজধানীর নাম কী?', 'category' => 'কারেন্ট অ্যাফেয়ার্স', 'date' => '13 Oct 2026', 'state' => 'Published'],
        ['question' => 'জাতিসংঘের সদর দপ্তর কোথায়?', 'category' => 'সাধারণ জ্ঞান', 'date' => '12 Oct 2026', 'state' => 'Published'],
        ['question' => 'বাংলাদেশের জাতীয় ফুল কোনটি?', 'category' => 'কারেন্ট অ্যাফেয়ার্স', 'date' => '11 Oct 2026', 'state' => 'Draft'],
        ['question' => '২০২৬ সালের বাজেট কত?', 'category' => 'মাসিক আপডেট', 'date' => '01 Oct 2026', 'state' => 'Published'],
        ['question' => 'বিশ্ব স্বাস্থ্য সংস্থার সদর দপ্তর কোথায়?', 'category' => 'আন্তর্জাতিক', 'date' => '30 Sep 2026', 'state' => 'Published'],
    ];
    $duSum = array_sum(array_column($duCategories, 'total'));
@endphp
<style>
/* Scoped: preserves all original AdminLTE/Bootstrap/dashboard classes. */
.lebd-du-admin{--dua-navy:#152f56;--dua-muted:#667d9d;--dua-line:#e2ebf5;--dua-blue:#176bf1;--dua-soft:#f2f7fe;font-family:inherit;font-size:13px;color:var(--dua-navy);max-width:1460px;margin:0 auto;padding:15px 15px 30px}
.lebd-du-admin,.lebd-du-admin *,.lebd-du-admin *:before,.lebd-du-admin *:after{box-sizing:border-box}
.lebd-du-admin a{text-decoration:none}.lebd-du-admin button,.lebd-du-admin input{font:inherit}.lebd-du-admin button{cursor:pointer}
.lebd-du-admin .dua-head{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:14px}
.lebd-du-admin .dua-heading h1{font-size:23px;font-weight:850;color:#15345e;margin:0 0 3px}
.lebd-du-admin .dua-crumb{font-size:11px;color:#7890ac;margin:0}.lebd-du-admin .dua-crumb b{color:#1d71d7}
.lebd-du-admin .dua-headright{display:flex;align-items:center;gap:8px;flex-wrap:wrap}
.lebd-du-admin .dua-today{display:inline-flex;align-items:center;gap:7px;background:#fff;border:1px solid #d9e5f3;color:#55749e;padding:9px 12px;border-radius:9px;font-size:11px;font-weight:750}
.lebd-du-admin .dua-tag{display:inline-flex;background:#fff4d7;color:#8c6118;border:1px solid #f5d891;border-radius:8px;padding:8px 11px;font-size:11px;font-weight:800;align-items:center;gap:6px}
.lebd-du-admin .dua-notice{border:1px solid #f4dfb0;border-left:4px solid #e7a62d;background:#fffbf2;border-radius:10px;padding:10px 13px;color:#6e5831;margin-bottom:15px;font-size:12px;line-height:1.5}
.lebd-du-admin .dua-stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;margin-bottom:15px}
.lebd-du-admin .dua-card{background:#fff;border:1px solid var(--dua-line);border-radius:13px;box-shadow:0 5px 15px rgba(21,60,112,.045);min-width:0;overflow:hidden}
.lebd-du-admin .dua-stat{padding:16px 15px;display:flex;align-items:center;gap:12px;min-height:112px}
.lebd-du-admin .dua-sicon{width:48px;height:48px;flex:none;display:grid;place-items:center;border-radius:12px;color:#fff;font-size:22px;font-weight:850;box-shadow:0 6px 12px rgba(25,100,190,.13)}
.lebd-du-admin .dua-sicon.blue{background:linear-gradient(135deg,#52b3ff,#1668ef)}.lebd-du-admin .dua-sicon.green{background:linear-gradient(135deg,#39d49a,#02a56a)}.lebd-du-admin .dua-sicon.orange{background:linear-gradient(135deg,#ffce55,#f69b08)}.lebd-du-admin .dua-sicon.violet{background:linear-gradient(135deg,#b78af6,#7045d3)}
.lebd-du-admin .dua-stat-label{font-size:11px;color:#6d829e;font-weight:750}.lebd-du-admin .dua-stat-num{font-size:26px;font-weight:850;line-height:1.2;color:#203c63;margin:4px 0;font-variant-numeric:tabular-nums}.lebd-du-admin .dua-stat-small{color:#92a1b7;font-size:10px}
.lebd-du-admin .dua-body{display:grid;grid-template-columns:minmax(0,1.6fr) minmax(320px,1fr);gap:13px;align-items:start}
.lebd-du-admin .dua-card-title{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:14px 16px;border-bottom:1px solid var(--dua-line)}.lebd-du-admin .dua-card-title h2{font-size:14px;font-weight:850;color:#15345d;margin:0}.lebd-du-admin .dua-hint{font-size:10px;color:#8a9fb9}
.lebd-du-admin .dua-search{display:flex;gap:9px;align-items:center;padding:12px 15px;border-bottom:1px solid #eef3f9}
.lebd-du-admin .dua-search input{border:1px solid #d9e5f4;border-radius:8px;height:35px;padding:0 11px;flex:1;min-width:0;outline:none;background:#fbfdff;color:#264971;font-size:11px}.lebd-du-admin .dua-search input:focus{border-color:#2d88ed;box-shadow:0 0 0 3px rgba(32,117,227,.09)}
.lebd-du-admin .dua-search select{height:35px;max-width:160px;border:1px solid #d9e5f4;border-radius:8px;background:#fff;color:#416084;font-size:11px;padding:0 8px}
.lebd-du-admin .dua-tablewrap{overflow-x:auto}.lebd-du-admin .dua-table{border-collapse:collapse;width:100%;min-width:530px;font-size:11px}.lebd-du-admin .dua-table th{text-align:left;background:#f5f9ff;color:#587292;font-weight:850}.lebd-du-admin .dua-table td,.lebd-du-admin .dua-table th{padding:11px 10px;border-bottom:1px solid #edf2f9;vertical-align:middle}.lebd-du-admin .dua-table tr:last-child td{border-bottom:0}.lebd-du-admin .dua-question{font-weight:750;color:#1e426d}.lebd-du-admin .dua-category{color:#687e9a}.lebd-du-admin .dua-state{font-size:10px;font-weight:750;padding:5px 8px;border-radius:6px;white-space:nowrap}.lebd-du-admin .dua-state.published{color:#0b9158;background:#dff7eb}.lebd-du-admin .dua-state.draft{color:#cb4c42;background:#fff0ee}
.lebd-du-admin .dua-disabled{border:1px solid #dce7f6;background:#f2f6fc;border-radius:7px;color:#9aadbf!important;font-size:10px;padding:5px 9px;font-weight:700;cursor:not-allowed}
.lebd-du-admin .dua-empty{padding:20px;text-align:center;color:#7e91a9;font-size:12px}
.lebd-du-admin .dua-cat-inner{padding:15px}.lebd-du-admin .dua-chart{width:155px;height:155px;margin:2px auto 15px;border-radius:50%;display:grid;place-items:center;background:conic-gradient(#2686f4 0 44%,#7653d6 44% 69.6%,#14b88a 69.6% 84%,#f1a43b 84% 93.6%,#e86b7e 93.6% 100%)}
.lebd-du-admin .dua-chart-center{display:grid;align-content:center;justify-items:center;width:106px;height:106px;border-radius:50%;background:#fff;box-shadow:inset 0 0 0 1px #edf2f8}.lebd-du-admin .dua-chart-center strong{font-size:26px;line-height:1.1;color:#183b66}.lebd-du-admin .dua-chart-center small{font-size:11px;color:#8295ad}
.lebd-du-admin .dua-cat-row{display:flex;align-items:center;gap:8px;margin-bottom:11px;font-size:11px;color:#465f81}.lebd-du-admin .dua-dot{width:9px;height:9px;border-radius:3px;flex:none}.lebd-du-admin .dua-cat-row b{margin-left:auto;color:#193c68;font-size:11px}
.lebd-du-admin .dua-section{margin-top:14px}.lebd-du-admin .dua-actions-grid{padding:15px;display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px}
.lebd-du-admin .dua-tile{border:1px dashed #cddcef;border-radius:11px;background:#f8fbff;min-height:91px;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:11px;text-align:center;gap:7px;opacity:.9}
.lebd-du-admin .dua-tile .dua-tile-ico{font-size:21px;color:#1b70db}.lebd-du-admin .dua-tile strong{font-size:11px;color:#244d78}.lebd-du-admin .dua-tile small{color:#8ba1b8;font-size:10px}
.lebd-du-admin .dua-milestone{padding:0 15px 15px;color:#5a7396;font-size:11px;line-height:1.75}
.lebd-du-admin .dua-steps{padding-left:20px;margin:8px 0 0}.lebd-du-admin .dua-steps li{margin:5px 0}
.lebd-du-admin .dua-footer{color:#96a4b8;font-size:10px;text-align:center;margin:15px 0 0}
@media(max-width:1080px){.lebd-du-admin .dua-body{grid-template-columns:1fr}.lebd-du-admin .dua-stats{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(max-width:640px){.lebd-du-admin{padding:10px 9px 24px}.lebd-du-admin .dua-heading h1{font-size:20px}.lebd-du-admin .dua-stats{gap:8px}.lebd-du-admin .dua-stat{padding:11px 9px;min-height:90px;gap:8px}.lebd-du-admin .dua-sicon{width:36px;height:36px;font-size:19px}.lebd-du-admin .dua-stat-num{font-size:21px}.lebd-du-admin .dua-stat-label{font-size:10px}.lebd-du-admin .dua-actions-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.lebd-du-admin .dua-search{flex-wrap:wrap}.lebd-du-admin .dua-search input,.lebd-du-admin .dua-search select{max-width:none;flex:1 1 100%}}
@media print{.lebd-du-admin .dua-actions-grid,.lebd-du-admin .dua-headright,.lebd-du-admin .dua-search,.lebd-du-admin .dua-notice{display:none!important}.lebd-du-admin .dua-body{grid-template-columns:1fr}.lebd-du-admin .dua-card{break-inside:avoid;box-shadow:none}.lebd-du-admin{padding:0}}
</style>
<main class="lebd-du-admin" id="lebdDailyAdmin">
    <div class="dua-head">
        <div class="dua-heading">
            <h1>▤ Daily Update Dashboard</h1>
            <p class="dua-crumb">Admin &nbsp;›&nbsp; Daily Update &nbsp;›&nbsp; <b>Dashboard</b></p>
        </div>
        <div class="dua-headright">
            <span class="dua-tag">⚠ Demo Preview</span>
            <span class="dua-today">▦ {{ now()->format('d M Y') }}</span>
        </div>
    </div>
    <div class="dua-notice" role="status">
        <strong>Demo Dashboard:</strong> নিচের সংখ্যা, প্রশ্ন ও ক্যাটাগরি শুধুই ডিজাইনের নমুনা। এগুলো আপনার Database-এর আসল তথ্য নয়। Add/Edit/Delete/Publish/Comment Management পরবর্তী ধাপে নিরাপদ Admin Route, Model ও Controller যুক্ত করার পর চালু হবে।
    </div>

    <div class="dua-stats">
        @foreach($duStats as $stat)
        <article class="dua-card dua-stat">
            <div class="dua-sicon {{ $stat['tone'] }}" aria-hidden="true">{{ $stat['icon'] }}</div>
            <div>
                <span class="dua-stat-label">{{ $stat['label'] }}</span>
                <div class="dua-stat-num">{{ $stat['value'] }}</div>
                <small class="dua-stat-small">{{ $stat['detail'] }}</small>
            </div>
        </article>
        @endforeach
    </div>

    <div class="dua-body">
        <section class="dua-card" aria-label="Recent demo updates">
            <div class="dua-card-title">
                <h2>◫ Recent Updates</h2>
                <span class="dua-hint">Last 5 · Sample Data</span>
            </div>
            <div class="dua-search">
                <input id="duaSearch" type="search" placeholder="প্রশ্নের নাম লিখে খুঁজুন..." aria-label="Search sample updates">
                <select id="duaState" aria-label="Filter sample status">
                    <option value="all">All Status</option>
                    <option value="published">Published</option>
                    <option value="draft">Draft</option>
                </select>
            </div>
            <div class="dua-tablewrap">
                <table class="dua-table">
                    <thead><tr><th>#</th><th>Question Title</th><th>Category</th><th>Date</th><th>Status</th><th>Action</th></tr></thead>
                    <tbody id="duaRows">
                    @foreach($duRecent as $index => $entry)
                        <tr class="dua-data-row" data-status="{{ strtolower($entry['state']) }}">
                            <td>{{ $index + 1 }}</td>
                            <td class="dua-question">{{ $entry['question'] }}</td>
                            <td class="dua-category">{{ $entry['category'] }}</td>
                            <td>{{ $entry['date'] }}</td>
                            <td><span class="dua-state {{ strtolower($entry['state']) }}">{{ $entry['state'] }}</span></td>
                            <td><button type="button" class="dua-disabled" disabled title="Edit Module coming next">Edit</button></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                <div class="dua-empty" id="duaEmpty" hidden>এই Filter-এ কোনো Demo প্রশ্ন পাওয়া যায়নি।</div>
            </div>
        </section>
        <section class="dua-card" aria-label="Category sample chart">
            <div class="dua-card-title"><h2>◉ Category Wise Count</h2><span class="dua-hint">Sample</span></div>
            <div class="dua-cat-inner">
                <div class="dua-chart" role="img" aria-label="Demo category count chart">
                    <div class="dua-chart-center"><strong>{{ $duSum }}</strong><small>Total Demo</small></div>
                </div>
                @foreach($duCategories as $category)
                    <div class="dua-cat-row"><span class="dua-dot" style="background:{{ $category['color'] }}"></span><span>{{ $category['title'] }}</span><b>{{ $category['total'] }}</b></div>
                @endforeach
            </div>
        </section>
    </div>

    <section class="dua-card dua-section">
        <div class="dua-card-title"><h2>⚡ Admin Quick Actions</h2><span class="dua-hint">Add Update, Categories, All Updates & Comments ready</span></div>
        <div class="dua-actions-grid">
            <a class="dua-tile" href="{{ route('admin.daily-update.create') }}" style="text-decoration:none;cursor:pointer;opacity:1;border-style:solid;background:#edf5ff" aria-label="Open Add New Daily Update form"><span class="dua-tile-ico">＋</span><strong>Add New Update</strong><small>Open Demo Form →</small></a>
            <a class="dua-tile" href="{{ route('admin.daily-update.categories') }}" style="text-decoration:none;cursor:pointer;opacity:1;border-style:solid;background:#edf5ff" aria-label="Open Daily Update Category Management"><span class="dua-tile-ico">▤</span><strong>Manage Categories</strong><small>Open Demo Categories →</small></a>
            <a class="dua-tile" href="{{ route('admin.daily-update.all') }}" style="text-decoration:none;cursor:pointer;opacity:1;border-style:solid;background:#edf5ff" aria-label="Open All Daily Updates management"><span class="dua-tile-ico">☷</span><strong>All Updates</strong><small>Open Demo List →</small></a>
            <a class="dua-tile" href="{{ route('admin.daily-update.comments') }}" style="text-decoration:none;cursor:pointer;opacity:1;border-style:solid;background:#edf5ff" aria-label="Open Daily Update Comment Management"><span class="dua-tile-ico">✉</span><strong>Comments</strong><small>Open Demo Comments →</small></a>
        </div>
    </section>
    <section class="dua-card dua-section">
        <div class="dua-card-title"><h2>☑ Implementation Roadmap</h2><span class="dua-hint">Safe incremental development</span></div>
        <div class="dua-milestone"><ol class="dua-steps">
            <li><strong>Step 1:</strong> Admin Dashboard UI (this file; demo only).</li>
            <li><strong>Step 2:</strong> Verify your current <code>routes/web.php</code> and admin access protection.</li>
            <li><strong>Step 3:</strong> Add / edit form, category & question management in separate Blade files.</li>
            <li><strong>Step 4:</strong> Add approved Models, migrations, controllers and publish workflow after schema review.</li>
            <li><strong>Step 5:</strong> Connect published updates to the Student Daily Update page; then enable comments.</li>
        </ol></div>
    </section>
    <p class="dua-footer">Live Education BD · Daily Update Management · Preview / No database writes</p>
</main>
<script>
(function(){
  'use strict';
  var root = document.getElementById('lebdDailyAdmin');
  if (!root) return;
  var search = root.querySelector('#duaSearch');
  var state = root.querySelector('#duaState');
  var rows = root.querySelectorAll('.dua-data-row');
  var empty = root.querySelector('#duaEmpty');
  function filterRows(){
    var keyword = (search.value || '').trim().toLocaleLowerCase();
    var status = state.value;
    var count = 0;
    Array.prototype.forEach.call(rows, function(row){
      var visible = row.textContent.toLocaleLowerCase().indexOf(keyword) !== -1 && (status === 'all' || row.getAttribute('data-status') === status);
      row.hidden = !visible;
      if (visible) count++;
    });
    empty.hidden = count !== 0;
  }
  search.addEventListener('input', filterRows);
  state.addEventListener('change', filterRows);
})();
</script>
@endsection
