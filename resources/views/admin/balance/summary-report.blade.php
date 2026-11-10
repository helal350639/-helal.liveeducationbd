@extends('dashboard')

@section('title', 'Admin Balance - ' . $reportTitle)

@section('content')
<section class="content-header no-print">
    <h1>{{ $reportTitle }} <small>Admin Balance / Summary</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('home') }}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
        <li><a href="{{ route('admin.balance.summary') }}">Summary</a></li>
        <li class="active">{{ $reportTitle }}</li>
    </ol>
</section>

<section class="content no-print">
    <div class="box box-default summary-report-toolbar">
        <div class="box-body">
            <div class="summary-report-left">
                <a href="{{ route('admin.balance.summary', ['year'=>$year]) }}" class="summary-back-btn">
                    <i class="fa fa-arrow-left"></i> Back
                </a>
                <div class="summary-report-nav" role="group" aria-label="আয় ব্যয় রিপোর্ট নির্বাচন">
                <a href="{{ route('admin.balance.summary.income', ['year'=>$year]) }}"
                   class="summary-report-tab tab-income {{ $reportType === 'income' ? 'active' : '' }}">
                    <i class="fa fa-line-chart"></i> আয়
                </a>
                <a href="{{ route('admin.balance.summary.cost', ['year'=>$year]) }}"
                   class="summary-report-tab tab-cost {{ $reportType === 'cost' ? 'active' : '' }}">
                    <i class="fa fa-credit-card"></i> ব্যয়
                </a>
                <a href="{{ route('admin.balance.summary.combine', ['year'=>$year]) }}"
                   class="summary-report-tab tab-combine {{ $reportType === 'combine' ? 'active' : '' }}">
                    <i class="fa fa-balance-scale"></i> Combine
                </a>
                </div>
            </div>

            <div class="summary-report-actions">
                <form method="GET" action="{{ $reportType === 'income' ? route('admin.balance.summary.income') : ($reportType === 'cost' ? route('admin.balance.summary.cost') : route('admin.balance.summary.combine')) }}" class="summary-year-filter">
                    <div class="summary-year-field">
                        <label for="summary-year">হিসাবের বছর</label>
                        <select id="summary-year" name="year" class="form-control">
                            @foreach($years as $y)
                                <option value="{{ $y }}" {{ (int)$year === (int)$y ? 'selected' : '' }}>{{ $y }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button class="btn btn-primary summary-filter-btn" type="submit">
                        <i class="fa fa-filter"></i> Filter
                    </button>
                </form>
                <button class="btn btn-info summary-print-btn" type="button" onclick="window.print()">
                    <i class="fa fa-print"></i> Print A4
                </button>
            </div>
        </div>
    </div>
</section>

<section class="summary-paper-wrap">
    <div class="summary-paper">
        <header class="summary-paper-header">
            <h2>LiveEducationBD</h2>
            <div>{{ $reportSubtitle }}</div>
            <div class="summary-paper-bn">{{ $reportTitle }}</div>
            <hr>
            <div class="summary-year">হিসাবের বছর: {{ $year }}</div>
        </header>

        <table class="summary-table">
            <thead>
                @if($reportType === 'combine')
                    <tr>
                        <th style="width:7%">SL</th>
                        <th style="width:23%">মাসের নাম</th>
                        <th style="width:22%">আয় (৳)</th>
                        <th style="width:22%">ব্যয় (৳)</th>
                        <th style="width:26%">ফলাফল</th>
                    </tr>
                @elseif($reportType === 'income')
                    <tr>
                        <th style="width:10%">SL</th>
                        <th>মাসের নাম</th>
                        <th style="width:35%">মোট আয় (৳)</th>
                    </tr>
                @else
                    <tr>
                        <th style="width:10%">SL</th>
                        <th>মাসের নাম</th>
                        <th style="width:35%">মোট ব্যয় (৳)</th>
                    </tr>
                @endif
            </thead>
            <tbody>
                @foreach($months as $month)
                    <tr>
                        <td class="center">{{ $month['sl'] }}</td>
                        <td>{{ $month['name'] }}</td>
                        @if($reportType === 'combine')
                            <td class="amount">{{ number_format($month['income'], 2) }}</td>
                            <td class="amount">{{ number_format($month['cost'], 2) }}</td>
                            <td class="result {{ $month['status'] === 'লাভ' ? 'profit' : ($month['status'] === 'লস' ? 'loss' : 'even') }}">
                                @if($month['status'] === 'লাভ')
                                    লাভ ৳ {{ number_format($month['difference'], 2) }}
                                @elseif($month['status'] === 'লস')
                                    লস ৳ {{ number_format($month['difference'], 2) }}
                                @else
                                    সমতা ৳ 0.00
                                @endif
                            </td>
                        @elseif($reportType === 'income')
                            <td class="amount">{{ number_format($month['income'], 2) }}</td>
                        @else
                            <td class="amount">{{ number_format($month['cost'], 2) }}</td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                @if($reportType === 'combine')
                    <tr>
                        <th colspan="2" class="total-label">বার্ষিক সর্বমোট</th>
                        <th class="amount">{{ number_format($annualIncome, 2) }}</th>
                        <th class="amount">{{ number_format($annualCost, 2) }}</th>
                        <th class="result {{ $annualStatus === 'লাভ' ? 'profit' : ($annualStatus === 'লস' ? 'loss' : 'even') }}">
                            @if($annualStatus === 'লাভ')
                                লাভ ৳ {{ number_format($annualDifference, 2) }}
                            @elseif($annualStatus === 'লস')
                                লস ৳ {{ number_format($annualDifference, 2) }}
                            @else
                                সমতা ৳ 0.00
                            @endif
                        </th>
                    </tr>
                @elseif($reportType === 'income')
                    <tr>
                        <th colspan="2" class="total-label">বার্ষিক মোট আয়</th>
                        <th class="amount">{{ number_format($annualIncome, 2) }}</th>
                    </tr>
                @else
                    <tr>
                        <th colspan="2" class="total-label">বার্ষিক মোট ব্যয়</th>
                        <th class="amount">{{ number_format($annualCost, 2) }}</th>
                    </tr>
                @endif
            </tfoot>
        </table>

        <div class="summary-signatures">
            <div class="summary-sign-block"><span></span><strong>প্রস্তুতকারীর স্বাক্ষর</strong></div>
            <div class="summary-sign-block"><span></span><strong>অনুমোদনকারীর স্বাক্ষর</strong></div>
        </div>
        <footer class="summary-paper-footer">
            <span>LiveEducationBD — {{ $reportTitle }}</span>
            <span>বছর: {{ $year }}</span>
        </footer>
    </div>
</section>

<style>
.summary-paper-wrap{padding:10px 0 25px;background:#edf1f5}
.summary-paper{box-sizing:border-box;width:210mm;min-height:297mm;margin:0 auto;padding:18mm 17mm;background:#fff;color:#172033;box-shadow:0 1px 8px rgba(0,0,0,.15);font-family:inherit;font-size:12px}
.summary-paper-header{text-align:center}
.summary-paper-header h2{font-size:22px;font-weight:700;margin:0 0 4px;color:#172b4d}
.summary-paper-header div{font-size:10px}
.summary-paper-header .summary-paper-bn{font-size:14px;margin-top:5px;font-weight:600}
.summary-paper-header hr{border:0;border-top:1px solid #263b5a;margin:13px 0 9px}
.summary-year{text-align:left;font-weight:600;margin-bottom:12px}
.summary-table{width:100%;border-collapse:collapse;table-layout:fixed;font-size:11px}
.summary-table th,.summary-table td{border:1px solid #68788e;padding:10px 8px;vertical-align:middle}
.summary-table thead th{background:#eef2f7;text-align:center;font-weight:700}
.summary-table .center{text-align:center}
.summary-table .amount{text-align:right;white-space:nowrap;font-variant-numeric:tabular-nums}
.summary-table .result{text-align:center;font-weight:700;white-space:nowrap}
.summary-table .profit{color:#16804b}.summary-table .loss{color:#c0392b}.summary-table .even{color:#344054}
.summary-table tfoot th{background:#f0f3f7;font-weight:700}
.summary-table .total-label{text-align:right}
.summary-signatures{display:flex;justify-content:space-between;margin-top:58px;gap:35px;page-break-inside:avoid;break-inside:avoid}
.summary-sign-block{width:185px;text-align:center;display:flex;flex-direction:column;align-items:stretch}
.summary-sign-block span{height:1px;background:#344054;margin-bottom:9px}
.summary-sign-block strong{font-weight:400;white-space:nowrap;font-size:11px}
.summary-paper-footer{display:flex;justify-content:space-between;border-top:1px solid #d7dde5;margin-top:35px;padding-top:7px;color:#68788e;font-size:9px}
@page{size:A4 portrait;margin:0}
@media print{
    html,body{background:#fff!important}
    .no-print,.main-header,.main-sidebar,.content-header,.main-footer,.control-sidebar{display:none!important}
    .content-wrapper,.wrapper{margin:0!important;padding:0!important;background:#fff!important;min-height:0!important}
    .summary-paper-wrap{padding:0!important;margin:0!important;background:#fff!important}
    .summary-paper{width:210mm;min-height:297mm;margin:0;padding:15mm 15mm 14mm;box-shadow:none!important}
    .summary-table tr{page-break-inside:avoid;break-inside:avoid}
    .summary-signatures{page-break-inside:avoid;break-inside:avoid}
    *{-webkit-print-color-adjust:exact;print-color-adjust:exact}
}
@media screen and (max-width:850px){.summary-paper{width:100%;min-height:0;padding:20px;overflow-x:auto}.summary-table{min-width:560px}}

.summary-report-toolbar{border:1px solid #dce4ef;border-radius:10px;box-shadow:0 3px 12px rgba(25,45,75,.07)}
.summary-report-toolbar>.box-body{display:flex;align-items:center;justify-content:space-between;gap:20px;flex-wrap:wrap;padding:16px 20px}
.summary-report-nav{display:flex;align-items:center;gap:9px;flex-wrap:wrap}
.summary-report-tab{display:inline-flex;align-items:center;justify-content:center;gap:9px;min-width:116px;padding:13px 24px;border:1px solid #d6dfeb;border-radius:9px;background:#f7f9fc;color:#33445d!important;font-size:16px;font-weight:700;text-decoration:none!important;transition:all .18s ease}
.summary-report-tab:hover{background:#edf3fb;border-color:#9eb5d3;transform:translateY(-1px)}
.summary-report-tab.tab-income.active{background:linear-gradient(135deg,#20ad70,#08794f);border-color:#12885b;color:#fff!important;box-shadow:0 4px 10px rgba(15,139,87,.2)}
.summary-report-tab.tab-cost.active{background:linear-gradient(135deg,#f16c5d,#d93632);border-color:#d93632;color:#fff!important;box-shadow:0 4px 10px rgba(217,54,50,.2)}
.summary-report-tab.tab-combine.active{background:linear-gradient(135deg,#328fdb,#1855a2);border-color:#246ebc;color:#fff!important;box-shadow:0 4px 10px rgba(36,110,188,.2)}
.summary-report-actions{display:flex;align-items:flex-end;justify-content:flex-end;gap:12px;margin-left:auto}
.summary-year-filter{display:flex;align-items:flex-end;gap:9px;margin:0}
.summary-year-field label{display:block;margin:0 0 5px;color:#56677e;font-size:11px;font-weight:700}
.summary-year-field select{height:42px;min-width:105px;border:1px solid #d2dce9;border-radius:8px;box-shadow:none;font-size:14px}
.summary-filter-btn,.summary-print-btn{height:42px;padding:0 20px;border-radius:8px;font-size:13px;font-weight:700}
.summary-print-btn{min-width:125px}
@media(max-width:767px){
    .summary-report-toolbar>.box-body{align-items:stretch;padding:13px}
    .summary-report-left{width:100%;align-items:stretch;flex-direction:column}
    .summary-back-btn{width:100%}
    .summary-report-nav{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));width:100%;gap:6px}
    .summary-report-tab{min-width:0;padding:12px 7px;font-size:14px;gap:5px}
    .summary-report-actions{width:100%;margin-left:0;justify-content:flex-end;flex-wrap:wrap}
    .summary-year-filter{flex:1;justify-content:flex-end}
    .summary-year-field{flex:1;max-width:130px}
    .summary-year-field select{width:100%;min-width:0}
}
@media(max-width:420px){
    .summary-report-actions{display:grid;grid-template-columns:1fr 1fr}
    .summary-year-filter{justify-content:stretch}
    .summary-filter-btn,.summary-print-btn{padding:0 10px;min-width:0}
}


.summary-report-left{display:flex;align-items:center;gap:12px;flex-wrap:wrap}
.summary-back-btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;min-width:94px;height:44px;padding:0 15px;border:1px solid #cbd6e4;border-radius:9px;background:#f1f5f9;color:#43536a!important;font-size:13px;font-weight:700;text-decoration:none!important;transition:all .18s ease}
.summary-back-btn:hover{background:#e5edf6;border-color:#9fb2c9;color:#1c3e67!important;transform:translateY(-1px)}

</style>
@endsection
