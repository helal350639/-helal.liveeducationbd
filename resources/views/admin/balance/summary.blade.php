@extends('dashboard')

@section('title', 'Admin Balance - Summary')

@section('content')
<section class="content-header summary-header">
    <div>
        <h1>আয়-ব্যয়ের সারসংক্ষেপ <small>Admin Balance / Summary</small></h1>
    </div>
    <ol class="breadcrumb">
        <li><a href="{{ route('home') }}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
        <li class="active">Admin Balance / Summary</li>
    </ol>
</section>

<section class="content summary-dashboard">
    <div class="summary-topbar">
        <div>
            <div class="summary-eyebrow">FINANCIAL OVERVIEW</div>
            <h2>{{ $selectedYear }} সালের আর্থিক চিত্র</h2>
            <p>Income ও Cost মডিউলে সংরক্ষিত প্রকৃত এন্ট্রি থেকে স্বয়ংক্রিয় হিসাব</p>
        </div>
        <form method="GET" action="{{ route('admin.balance.summary') }}" class="summary-year-form">
            <label for="summary-year">হিসাবের বছর</label>
            <select id="summary-year" name="year" class="form-control" onchange="this.form.submit()">
                @foreach($years as $y)
                    <option value="{{ $y }}" {{ (int)$selectedYear === (int)$y ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
        </form>
    </div>

    <div class="summary-shortcuts">
        <a href="{{ route('admin.balance.summary.income', ['year'=>$selectedYear]) }}" class="summary-shortcut shortcut-income">
            <span class="shortcut-icon"><i class="fa fa-line-chart"></i></span>
            <span class="shortcut-copy">
                <strong>আয় (Income)</strong>
                <small>১২ মাসের সম্পূর্ণ আয় হিসাব</small>
                <b>আয় হিসাব দেখুন <i class="fa fa-arrow-right"></i></b>
            </span>
            <i class="fa fa-money shortcut-watermark"></i>
        </a>
        <a href="{{ route('admin.balance.summary.cost', ['year'=>$selectedYear]) }}" class="summary-shortcut shortcut-cost">
            <span class="shortcut-icon"><i class="fa fa-credit-card"></i></span>
            <span class="shortcut-copy">
                <strong>ব্যয় (Cost)</strong>
                <small>১২ মাসের সম্পূর্ণ ব্যয় হিসাব</small>
                <b>ব্যয় হিসাব দেখুন <i class="fa fa-arrow-right"></i></b>
            </span>
            <i class="fa fa-calculator shortcut-watermark"></i>
        </a>
        <a href="{{ route('admin.balance.summary.combine', ['year'=>$selectedYear]) }}" class="summary-shortcut shortcut-combine">
            <span class="shortcut-icon"><i class="fa fa-balance-scale"></i></span>
            <span class="shortcut-copy">
                <strong>Combine</strong>
                <small>মাসভিত্তিক আয়, ব্যয় ও লাভ-লস</small>
                <b>সম্মিলিত হিসাব দেখুন <i class="fa fa-arrow-right"></i></b>
            </span>
            <i class="fa fa-file-text-o shortcut-watermark"></i>
        </a>
    </div>

    <div class="summary-kpis">
        <div class="summary-kpi kpi-income">
            <div class="kpi-icon"><i class="fa fa-money"></i></div>
            <div><span>মোট আয় (১২ মাস)</span><strong>৳ {{ number_format($annualIncome, 2) }}</strong><small>Income Register</small></div>
        </div>
        <div class="summary-kpi kpi-cost">
            <div class="kpi-icon"><i class="fa fa-money"></i></div>
            <div><span>মোট ব্যয় (১২ মাস)</span><strong>৳ {{ number_format($annualCost, 2) }}</strong><small>Cost Register</small></div>
        </div>
        <div class="summary-kpi kpi-net">
            <div class="kpi-icon"><i class="fa fa-line-chart"></i></div>
            <div>
                <span>নিট ফলাফল</span>
                <strong>{{ $annualStatus }} ৳ {{ number_format($annualDifference, 2) }}</strong>
                <small>বার্ষিক আয় − বার্ষিক ব্যয়</small>
            </div>
        </div>
        <div class="summary-kpi kpi-months">
            <div class="kpi-icon"><i class="fa fa-calendar"></i></div>
            <div><span>হিসাবের মাস</span><strong>১২ মাস</strong><small>জানুয়ারি – ডিসেম্বর</small></div>
        </div>
    </div>

    <div class="summary-charts-grid">
        <div class="summary-panel chart-panel chart-wide">
            <div class="panel-heading">
                <div><h3>মাসভিত্তিক আয় ও ব্যয় তুলনা</h3><p>প্রতি মাসের প্রকৃত আয় এবং ব্যয়ের পরিমাণ</p></div>
                <span class="panel-icon"><i class="fa fa-bar-chart"></i></span>
            </div>
            <div class="chart-holder"><canvas id="incomeCostChart"></canvas></div>
        </div>

        <div class="summary-panel chart-panel">
            <div class="panel-heading">
                <div><h3>বার্ষিক হিসাবের অনুপাত</h3><p>মোট আয় বনাম মোট ব্যয়</p></div>
                <span class="panel-icon"><i class="fa fa-pie-chart"></i></span>
            </div>
            <div class="chart-holder donut-holder"><canvas id="annualRatioChart"></canvas></div>
            <div class="chart-legend">
                <span><i class="legend-dot dot-income"></i> মোট আয় <b>৳ {{ number_format($annualIncome, 2) }}</b></span>
                <span><i class="legend-dot dot-cost"></i> মোট ব্যয় <b>৳ {{ number_format($annualCost, 2) }}</b></span>
            </div>
        </div>

        <div class="summary-panel chart-panel chart-wide">
            <div class="panel-heading">
                <div><h3>মাসভিত্তিক নিট ফলাফল (লাভ/লস)</h3><p>শূন্যের উপরে লাভ এবং নিচে লস</p></div>
                <span class="panel-icon"><i class="fa fa-line-chart"></i></span>
            </div>
            <div class="chart-holder"><canvas id="netResultChart"></canvas></div>
        </div>

        <div class="summary-panel annual-panel">
            <div class="panel-heading">
                <div><h3>বার্ষিক সারসংক্ষেপ</h3><p>{{ $selectedYear }} সালের মোট হিসাব</p></div>
                <a href="{{ route('admin.balance.summary.combine', ['year'=>$selectedYear]) }}" title="সম্পূর্ণ রিপোর্ট"><i class="fa fa-external-link"></i></a>
            </div>
            <div class="annual-summary-row row-income"><span><i class="fa fa-money"></i> মোট আয়</span><strong>৳ {{ number_format($annualIncome, 2) }}</strong></div>
            <div class="annual-summary-row row-cost"><span><i class="fa fa-credit-card"></i> মোট ব্যয়</span><strong>৳ {{ number_format($annualCost, 2) }}</strong></div>
            <div class="annual-summary-row row-net"><span><i class="fa fa-line-chart"></i> নিট ফলাফল</span><strong>{{ $annualStatus }} ৳ {{ number_format($annualDifference, 2) }}</strong></div>
            <div class="annual-summary-note">এই সারসংক্ষেপ Income ও Cost-এর মূল এন্ট্রি থেকে হিসাব করা হয়েছে।</div>
        </div>
    </div>

    <div class="summary-panel month-table-panel">
        <div class="panel-heading">
            <div><h3>{{ $selectedYear }} সালের ১২ মাসের হিসাব</h3><p>প্রতি মাসের আয়, ব্যয় এবং লাভ-লস</p></div>
            <a class="btn btn-primary btn-sm" href="{{ route('admin.balance.summary.combine', ['year'=>$selectedYear]) }}"><i class="fa fa-print"></i> A4 রিপোর্ট</a>
        </div>
        <div class="table-responsive">
            <table class="table summary-month-table">
                <thead><tr><th>SL</th><th>মাস</th><th class="text-right">আয় (৳)</th><th class="text-right">ব্যয় (৳)</th><th class="text-right">ফলাফল</th></tr></thead>
                <tbody>
                    @foreach($months as $month)
                        <tr>
                            <td>{{ $month['sl'] }}</td>
                            <td>{{ $month['name'] }}</td>
                            <td class="text-right">{{ number_format($month['income'], 2) }}</td>
                            <td class="text-right">{{ number_format($month['cost'], 2) }}</td>
                            <td class="text-right {{ $month['status'] === 'লাভ' ? 'text-profit' : ($month['status'] === 'লস' ? 'text-loss' : '') }}">
                                @if($month['status'] === 'লাভ')
                                    লাভ ৳ {{ number_format($month['difference'], 2) }}
                                @elseif($month['status'] === 'লস')
                                    লস ৳ {{ number_format($month['difference'], 2) }}
                                @else
                                    সমতা
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot><tr><th colspan="2">সর্বমোট</th><th class="text-right">৳ {{ number_format($annualIncome, 2) }}</th><th class="text-right">৳ {{ number_format($annualCost, 2) }}</th><th class="text-right {{ $annualStatus === 'লাভ' ? 'text-profit' : ($annualStatus === 'লস' ? 'text-loss' : '') }}">{{ $annualStatus }} ৳ {{ number_format($annualDifference, 2) }}</th></tr></tfoot>
            </table>
        </div>
    </div>
</section>
@endsection

@section('stylesheets')
<style>
.summary-dashboard{padding-top:8px;background:#eef2f7;min-height:calc(100vh - 100px)}
.summary-header{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap}
.summary-header h1{margin-top:5px}
.summary-topbar{display:flex;justify-content:space-between;align-items:center;gap:18px;margin:0 0 18px;padding:4px 2px}
.summary-eyebrow{font-size:10px;letter-spacing:1.8px;font-weight:800;color:#75849a}
.summary-topbar h2{margin:4px 0;font-size:23px;font-weight:700;color:#182b49}
.summary-topbar p{margin:0;color:#7c8ba0;font-size:12px}
.summary-year-form{min-width:145px}
.summary-year-form label{display:block;font-size:11px;color:#66758b;margin-bottom:5px}
.summary-year-form select{height:36px;border-radius:8px;border-color:#d7dfeb;box-shadow:none}
.summary-shortcuts{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px;margin-bottom:17px}
.summary-shortcut{position:relative;display:flex;align-items:center;gap:18px;min-height:145px;padding:22px 24px;border-radius:13px;overflow:hidden;color:#fff!important;text-decoration:none!important;box-shadow:0 5px 14px rgba(28,48,80,.13);transition:transform .18s,box-shadow .18s}
.summary-shortcut:hover{transform:translateY(-3px);box-shadow:0 9px 20px rgba(28,48,80,.2)}
.shortcut-income{background:linear-gradient(120deg,#22b573,#08765b)}
.shortcut-cost{background:linear-gradient(120deg,#ff765e,#d83232)}
.shortcut-combine{background:linear-gradient(120deg,#2698ed,#123f8b)}
.shortcut-icon{z-index:1;display:flex;align-items:center;justify-content:center;width:62px;height:62px;flex:0 0 62px;border:1px solid rgba(255,255,255,.45);border-radius:16px;background:rgba(255,255,255,.17);font-size:30px}
.shortcut-copy{z-index:1;display:flex;flex-direction:column;align-items:flex-start;gap:5px}
.shortcut-copy strong{font-size:22px;font-weight:800}
.shortcut-copy small{font-size:12px;color:rgba(255,255,255,.9)}
.shortcut-copy b{margin-top:8px;padding:8px 14px;background:#fff;border-radius:30px;color:#174c59;font-size:11px}
.shortcut-watermark{position:absolute;right:18px;bottom:5px;font-size:78px;color:rgba(255,255,255,.12);transform:rotate(-12deg)}
.summary-kpis{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px;margin-bottom:17px}
.summary-kpi{display:flex;align-items:center;gap:14px;min-height:96px;padding:15px 17px;border-radius:12px;background:#fff;border:1px solid #e3e9f1;box-shadow:0 3px 10px rgba(31,48,75,.06)}
.kpi-icon{width:49px;height:49px;display:flex;align-items:center;justify-content:center;border-radius:13px;font-size:22px;flex:0 0 49px}
.summary-kpi span,.summary-kpi small{display:block;color:#78869a;font-size:11px}
.summary-kpi strong{display:block;margin:3px 0;color:#20334d;font-size:19px;font-weight:800;line-height:1.3;overflow-wrap:anywhere}
.kpi-income .kpi-icon{background:#e7f8ef;color:#15945d}.kpi-cost .kpi-icon{background:#fff0ed;color:#e34e43}
.kpi-net .kpi-icon{background:#e9f2ff;color:#2475c5}.kpi-months .kpi-icon{background:#f3eaff;color:#8546c9}
.summary-charts-grid{display:grid;grid-template-columns:1.55fr 1fr;gap:16px;margin-bottom:17px}
.summary-panel{background:#fff;border:1px solid #e3e9f1;border-radius:12px;box-shadow:0 3px 10px rgba(31,48,75,.06);padding:17px 18px;min-width:0}
.chart-panel{min-height:290px}
.panel-heading{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:12px}
.panel-heading h3{margin:0 0 4px;font-size:16px;font-weight:800;color:#20334d}
.panel-heading p{margin:0;font-size:11px;color:#8a97a8}
.panel-icon{color:#a1afc1;font-size:18px}
.panel-heading>a{color:#60758e}
.chart-holder{position:relative;height:205px;width:100%}
.donut-holder{height:175px;max-width:270px;margin:0 auto}
.chart-legend{display:flex;flex-direction:column;gap:7px;margin:7px auto 0;max-width:300px}
.chart-legend span{display:flex;align-items:center;gap:7px;font-size:11px;color:#65758b}
.chart-legend b{margin-left:auto;color:#263a54}
.legend-dot{width:9px;height:9px;border-radius:50%;display:inline-block}
.dot-income{background:#e0b100}.dot-cost{background:#f05b50}
.annual-panel{min-height:290px}
.annual-summary-row{display:flex;justify-content:space-between;align-items:center;gap:10px;margin:11px 0;padding:12px 13px;border-radius:9px;font-size:12px;font-weight:700}
.annual-summary-row span{display:flex;align-items:center;gap:8px}
.annual-summary-row strong{text-align:right}
.row-income{background:#eaf8f0;color:#138351;border:1px solid #c6ebd6}
.row-cost{background:#fff0ee;color:#cf4038;border:1px solid #f8d0cb}
.row-net{background:#edf4ff;color:#1854a4;border:1px solid #cfdef7}
.annual-summary-note{margin-top:17px;padding-top:12px;border-top:1px solid #edf0f4;color:#8996a8;font-size:10px;line-height:1.6}
.month-table-panel{margin-bottom:18px}
.summary-month-table{margin:0;font-size:12px}
.summary-month-table thead th{background:#f3f6fa;color:#51627a;border-bottom:1px solid #e1e7ef}
.summary-month-table td,.summary-month-table th{padding:10px 12px!important;vertical-align:middle!important}
.summary-month-table tfoot th{background:#f5f8fb;border-top:2px solid #d9e2ed}
.text-profit{color:#d9a300!important;font-weight:800}.text-loss{color:#d3443b!important;font-weight:800}
@media(max-width:1100px){.summary-kpis{grid-template-columns:repeat(2,minmax(0,1fr))}.summary-shortcuts{gap:10px}.summary-shortcut{padding:17px;gap:12px}}
@media(max-width:767px){.summary-shortcuts{grid-template-columns:1fr}.summary-shortcut{min-height:120px}.summary-charts-grid{grid-template-columns:1fr}.summary-topbar{align-items:flex-start;flex-direction:column}.summary-kpis{grid-template-columns:1fr 1fr}.summary-kpi{padding:12px;gap:9px}.summary-kpi strong{font-size:15px}.summary-kpi .kpi-icon{width:40px;height:40px;flex-basis:40px}.summary-header{display:block}}
@media(max-width:420px){.summary-kpis{grid-template-columns:1fr}}
</style>
@endsection

@section('scripts')
<script src="{{ asset('js/canvas-chart.js') }}"></script>
<script>
(function () {
    if (typeof Chart === 'undefined') return;

    const labels = @json(array_column($months, 'name'));
    const incomeData = @json(array_column($months, 'income'));
    const costData = @json(array_column($months, 'cost'));
    const netData = incomeData.map((value, index) => value - costData[index]);

    const money = function(value) {
        return '৳ ' + Number(value || 0).toLocaleString('en-US', {maximumFractionDigits: 2});
    };

    const commonOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { position: 'top', labels: { usePointStyle: true, boxWidth: 8, padding: 18 } },
            tooltip: { callbacks: { label: function(context) { return context.dataset.label + ': ' + money(context.raw); } } }
        }
    };

    const barCanvas = document.getElementById('incomeCostChart');
    if (barCanvas) {
        new Chart(barCanvas, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    { label: 'আয় (Income)', data: incomeData, backgroundColor: '#e0b100', borderRadius: 5, maxBarThickness: 22 },
                    { label: 'ব্যয় (Cost)', data: costData, backgroundColor: '#f05b50', borderRadius: 5, maxBarThickness: 22 }
                ]
            },
            options: Object.assign({}, commonOptions, {
                scales: {
                    x: { grid: { display: false }, ticks: { color: '#52647c', maxRotation: 0, minRotation: 0 } },
                    y: { beginAtZero: true, grid: { color: '#edf1f5' }, ticks: { color: '#718096', callback: function(value) { return Number(value).toLocaleString('en-US'); } } }
                }
            })
        });
    }

    const donutCanvas = document.getElementById('annualRatioChart');
    if (donutCanvas) {
        const hasValues = Number(@json($annualIncome)) + Number(@json($annualCost)) > 0;
        new Chart(donutCanvas, {
            type: 'doughnut',
            data: {
                labels: ['মোট আয়', 'মোট ব্যয়'],
                datasets: [{
                    data: hasValues ? [@json($annualIncome), @json($annualCost)] : [1, 1],
                    backgroundColor: hasValues ? ['#e0b100', '#f05b50'] : ['#dce4ed', '#edf1f5'],
                    borderWidth: 0,
                    hoverOffset: 5
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false, cutout: '68%',
                plugins: { legend: { display: false }, tooltip: { enabled: hasValues, callbacks: { label: function(context) { return context.label + ': ' + money(context.raw); } } } }
            }
        });
    }

    const netCanvas = document.getElementById('netResultChart');
    if (netCanvas) {
        new Chart(netCanvas, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'নিট ফলাফল',
                    data: netData,
                    borderColor: '#e0b100',
                    backgroundColor: 'rgba(224,177,0,.16)',
                    pointBackgroundColor: netData.map(value => value >= 0 ? '#e0b100' : '#ed5148'),
                    pointBorderColor: netData.map(value => value >= 0 ? '#e0b100' : '#ed5148'),
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    borderWidth: 3,
                    tension: .32,
                    fill: { target: { value: 0 } }
                }]
            },
            options: Object.assign({}, commonOptions, {
                plugins: { legend: { display: false }, tooltip: { callbacks: { label: function(context) { const value = Number(context.raw || 0); return (value >= 0 ? 'লাভ: ' : 'লস: ') + money(Math.abs(value)); } } } },
                scales: {
                    x: { grid: { display: false }, ticks: { color: '#52647c', maxRotation: 0, minRotation: 0 } },
                    y: { grid: { color: '#edf1f5' }, ticks: { color: '#718096', callback: function(value) { return Number(value).toLocaleString('en-US'); } } }
                }
            })
        });
    }
})();
</script>
@endsection
