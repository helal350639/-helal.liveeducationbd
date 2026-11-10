@extends('dashboard')

@section('title', 'Admin Balance - Cost')

@section('content')
<style>
    .abc-wrap{color:#344054}.abc-head{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:16px}
    .abc-head h1{margin:0;font-size:24px;color:#263238}.abc-sub{color:#8492a6;font-size:12px}
    .abc-card{background:#fff;border:1px solid #e5eaf0;border-top:3px solid #e35b54;border-radius:4px;box-shadow:0 1px 3px rgba(16,24,40,.06);margin-bottom:16px}
    .abc-card-head{padding:12px 16px;border-bottom:1px solid #edf0f4;display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap}
    .abc-card-head h3{margin:0;font-size:17px;color:#263238}.abc-card-body{padding:15px}
    .abc-total{background:#fff5f4;border:1px solid #f4d7d4;border-radius:6px;padding:11px 17px;min-width:205px}
    .abc-total small{display:block;color:#7d8795;margin-bottom:3px}.abc-total strong{color:#d94f48;font-size:23px}
    .abc-filter{display:flex;gap:8px;align-items:end;flex-wrap:wrap}.abc-filter label,.abc-row label{display:block;font-weight:600;color:#475467;font-size:13px;margin-bottom:5px}
    .abc-filter select,.abc-row input,.abc-row select{height:38px;border:1px solid #d0d5dd;border-radius:4px;padding:7px 10px;width:100%;background:#fff;color:#344054}
    .abc-filter .form-group{margin:0;min-width:125px}.abc-btn{border:0;border-radius:4px;padding:9px 13px;font-weight:600;cursor:pointer;display:inline-flex;align-items:center;gap:6px;text-decoration:none!important}
    .abc-btn-primary{background:#1877bd;color:#fff}.abc-btn-add{background:#168b60;color:#fff}.abc-btn-muted{background:#eef2f6;color:#475467}
    .abc-btn-remove{background:#fff0ef;color:#c03932;border:1px solid #f2c8c5;padding:7px 10px}.abc-btn-outline{background:#fff;border:1px solid #cbd5e1;color:#475467}
    .abc-row{display:grid;grid-template-columns:1.1fr 1.5fr 1fr auto;gap:10px;align-items:end;padding:12px;border:1px solid #dce4ed;border-radius:6px;margin-bottom:9px;background:#fbfcfe}
    .abc-row .abc-other{display:none;margin-top:7px}.abc-help{color:#98a2b3;font-size:11px;margin-top:4px}
    .abc-category-tools{display:flex;gap:6px;margin-top:6px}.abc-category-tools input{height:34px;flex:1;min-width:0}.abc-category-tools button{white-space:nowrap;padding:6px 9px}
    .abc-alert{padding:10px 13px;border-radius:4px;margin-bottom:14px}.abc-alert-success{background:#eaf8f0;color:#167347;border:1px solid #ccebd9}.abc-alert-danger{background:#fff0ef;color:#a9322b;border:1px solid #f4cecb}
    .abc-filter .form-group label{white-space:nowrap}
    .abc-paper-wrap{background:#e9edf2;padding:18px;overflow:auto}
    .abc-paper{background:#fff;width:210mm;min-height:297mm;margin:0 auto;padding:15mm 14mm;box-shadow:0 2px 12px rgba(16,24,40,.12);color:#111827}
    .abc-paper-title{text-align:center;border-bottom:2px solid #27364b;padding-bottom:12px;margin-bottom:16px}
    .abc-paper-title h2{font-size:22px;font-weight:700;margin:0 0 5px;color:#172b4d}
    .abc-paper-title p{margin:0;color:#667085;font-size:12px}
    .abc-paper-meta{display:flex;justify-content:space-between;gap:12px;font-size:12px;margin-bottom:14px}
    .abc-sheet{width:100%;border-collapse:collapse;font-size:12px}
    .abc-sheet th,.abc-sheet td{border:1px solid #697586;padding:8px 7px;vertical-align:middle}
    .abc-sheet th{background:#edf2f7;text-align:center;font-weight:700;color:#172b4d}
    .abc-sheet td.center{text-align:center}.abc-sheet td.money{text-align:right;white-space:nowrap}
    .abc-sheet tfoot th{background:#f1f5f9}.abc-sheet-empty{text-align:center!important;padding:24px!important;color:#667085}
    .abc-paper-sign{display:flex;justify-content:space-between;margin-top:55px;font-size:12px}
    .abc-paper-sign span{border-top:1px solid #344054;padding:7px 18px 0;min-width:150px;text-align:center}
    @media(max-width:767px){.abc-row{grid-template-columns:1fr}.abc-total{width:100%}.abc-paper-wrap{padding:8px}.abc-paper{margin:0;width:210mm}}
    @media print{
        @page{size:A4 portrait;margin:10mm}
        body{background:#fff!important}
        body *{visibility:hidden!important}
        #abc-print-area,#abc-print-area *{visibility:visible!important}
        #abc-print-area{position:absolute!important;left:0!important;top:0!important;width:100%!important;background:#fff!important;padding:0!important;margin:0!important}
        #abc-print-area .abc-paper{width:100%!important;min-height:0!important;margin:0!important;padding:0!important;box-shadow:none!important}
        #abc-print-area .abc-paper-title h2{font-size:18pt}
        #abc-print-area .abc-sheet{font-size:9pt}
        #abc-print-area .abc-sheet th,#abc-print-area .abc-sheet td{padding:5px}
        .no-print{display:none!important}
    }
</style>

<section class="content-header">
    <h1>ব্যয় হিসাব <small>Admin Balance / Cost</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('home') }}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
        <li>Admin Balance</li><li class="active">Cost</li>
    </ol>
</section>

<section class="content abc-wrap">
    @if(session('success'))<div class="abc-alert abc-alert-success"><i class="fa fa-check-circle"></i> {{ session('success') }}</div>@endif
    @if($errors->any())
        <div class="abc-alert abc-alert-danger"><strong>এন্ট্রি সংরক্ষণ করা যায়নি:</strong>
            <ul style="margin:6px 0 0 18px;">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif
    <div id="abc-category-message" class="abc-alert" style="display:none;"></div>

    <div class="abc-head">
        <div><h1><i class="fa fa-money text-red"></i> মাসিক ব্যয়ের হিসাব</h1>
            <div class="abc-sub">তারিখ, ব্যয়ের বিবরণ ও টাকার পরিমাণ একই সারিতে যোগ করুন। নতুন খরচের খাত নিজেই তৈরি করতে পারবেন।</div>
        </div>
        <div class="abc-total"><small>{{ $monthLabel }} মাসের মোট ব্যয়</small><strong>৳ {{ number_format($monthlyTotal, 2) }}</strong></div>
    </div>

    @unless($archiveMode)
    <div class="no-print" style="margin-bottom:12px;">
        <button type="button" class="abc-btn abc-btn-add" id="abc-open-entry">
            <i class="fa fa-plus-circle"></i> নতুন ব্যয় এন্ট্রি / Add Option
        </button>
    </div>

    <div class="abc-card no-print" id="abc-entry-card" style="display:none;">
        <div class="abc-card-head"><h3><i class="fa fa-plus-circle text-green"></i> নতুন ব্যয় এন্ট্রি</h3><span class="abc-sub">এন্ট্রি যোগ করে Confirm & Save করুন</span></div>
        <div class="abc-card-body">
            <form method="POST" action="{{ route('admin.balance.cost.store') }}" id="abc-expense-form">
                @csrf
                <div id="abc-entry-rows">
                    <div class="abc-row" data-entry-row>
                        <div>
                            <label>তারিখ ও সময়</label>
                            <input type="datetime-local" name="entries[0][entry_at]" value="{{ $nowDhaka }}" required>
                            <div class="abc-help">Bangladesh local time (Asia/Dhaka)</div>
                        </div>
                        <div>
                            <label>ব্যয়ের বিবরণ</label>
                            <select name="entries[0][expense_head]" class="abc-head-select" required>
                                <option value="">ব্যয়ের খাত নির্বাচন করুন</option>
                                @foreach($expenseHeads as $key => $label)<option value="{{ $key }}">{{ $label }}</option>@endforeach
                            </select>
                            <div class="abc-category-tools">
                                <input type="text" class="abc-new-category-input" placeholder="নিজের নতুন খরচের খাত লিখুন">
                                <button type="button" class="abc-btn abc-btn-outline abc-save-category"><i class="fa fa-plus"></i> খাত যোগ</button>
                            </div>
                        </div>
                        <div>
                            <label>টাকার পরিমাণ (৳)</label>
                            <input type="number" name="entries[0][amount]" min="0.01" step="0.01" placeholder="যেমন: 1500" required>
                        </div>
                        <div><button type="button" class="abc-btn abc-btn-remove" data-remove-row title="সারি বাদ দিন" disabled><i class="fa fa-trash"></i></button></div>
                    </div>
                </div>
                <div style="display:flex;gap:9px;flex-wrap:wrap;margin-top:12px;">
                    <button type="button" class="abc-btn abc-btn-add" id="abc-add-row"><i class="fa fa-plus"></i> আরেকটি Row যোগ</button>
                    <button type="submit" class="abc-btn abc-btn-primary"><i class="fa fa-check"></i> Confirm & Save</button>
                    <button type="button" class="abc-btn abc-btn-muted" id="abc-cancel-entry">বাতিল</button>
                </div>
            </form>
        </div>
    </div>
    @endunless

    @if(!empty($editExpense))
    <div class="abc-card no-print" id="abc-edit-card">
        <div class="abc-card-head">
            <h3><i class="fa fa-pencil text-blue"></i> ব্যয়ের তথ্য সম্পাদনা</h3>
            <a class="abc-btn abc-btn-muted" href="{{ $archiveMode ? route('admin.balance.cost.archive', ['month' => $selectedMonth, 'year' => $selectedYear]) : route('admin.balance.cost') }}">বন্ধ করুন</a>
        </div>
        <div class="abc-card-body">
            <form method="POST" action="{{ route('admin.balance.cost.update', $editExpense->id) }}">
                @csrf
                @method('PUT')
                <div class="abc-row" style="grid-template-columns:1.1fr 1.5fr 1fr;">
                    <div>
                        <label>তারিখ ও সময়</label>
                        <input type="datetime-local" name="entry_at" value="{{ old('entry_at', $editDateTime) }}" required>
                    </div>
                    <div>
                        <label>ব্যয়ের বিবরণ</label>
                        <select name="expense_head" required>
                            <option value="">ব্যয়ের খাত নির্বাচন করুন</option>
                            @foreach($expenseHeads as $key => $label)
                                <option value="{{ $key }}" {{ old('expense_head', $editExpense->expense_head) === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label>টাকার পরিমাণ (৳)</label>
                        <input type="number" name="amount" min="0.01" step="0.01" value="{{ old('amount', $editExpense->amount) }}" required>
                    </div>
                </div>
                <button type="submit" class="abc-btn abc-btn-primary"><i class="fa fa-save"></i> পরিবর্তন সংরক্ষণ</button>
                <a href="{{ $archiveMode ? route('admin.balance.cost.archive', ['month' => $selectedMonth, 'year' => $selectedYear]) : route('admin.balance.cost') }}" class="abc-btn abc-btn-muted">বাতিল</a>
            </form>
        </div>
    </div>
    @endif

    <div class="abc-card no-print">
        <div class="abc-card-head">
            <h3>
                <i class="fa fa-{{ $archiveMode ? 'archive' : 'calendar' }}"></i>
                {{ $archiveMode ? 'সংরক্ষিত পুরোনো মাসের হিসাব' : 'চলতি মাসের হিসাব' }}
            </h3>
            @if($archiveMode)
                <form method="GET" action="{{ route('admin.balance.cost.archive') }}" class="abc-filter">
                    <div class="form-group"><label for="abc-month">মাস</label>
                        <select id="abc-month" name="month">@foreach(range(1,12) as $m)
                            <option value="{{ $m }}" {{ (int)$selectedMonth === $m ? 'selected' : '' }}>{{ \Carbon\Carbon::createFromDate(2020,$m,1)->locale('bn')->translatedFormat('F') }}</option>
                        @endforeach</select>
                    </div>
                    <div class="form-group"><label for="abc-year">বছর</label>
                        <select id="abc-year" name="year">@foreach($years as $y)
                            <option value="{{ $y }}" {{ (int)$selectedYear === $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach</select>
                    </div>
                    <button type="submit" class="abc-btn abc-btn-primary"><i class="fa fa-search"></i> Filter</button>
                    <a class="abc-btn abc-btn-muted" href="{{ route('admin.balance.cost') }}">চলতি মাস</a>
                </form>
            @else
                <a class="abc-btn abc-btn-outline" href="{{ route('admin.balance.cost.archive') }}">
                    <i class="fa fa-archive"></i> পুরোনো মাসের হিসাব
                </a>
            @endif
        </div>
        <div class="abc-card-body" style="display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;">
            <span class="abc-sub">
                {{ $archiveMode ? 'সংরক্ষিত মাসের হিসাব দেখছেন।' : 'শুধু বর্তমান মাসের এন্ট্রি এখানে দেখানো হচ্ছে। আগের মাসের হিসাব Archive-এ সংরক্ষিত।' }}
            </span>
            <button type="button" class="abc-btn abc-btn-primary" onclick="window.print()"><i class="fa fa-print"></i> Print A4 Sheet</button>
        </div>
    </div>

    <div id="abc-print-area">
        <div class="abc-paper-wrap">
            <div class="abc-paper">
                <div class="abc-paper-title">
                    <h2>LiveEducationBD</h2>
                    <p>MONTHLY EXPENSE REGISTER / মাসিক ব্যয়ের হিসাব</p>
                </div>
                <div class="abc-paper-meta">
                    <div><strong>মাস ও বছর:</strong> {{ $monthLabel }}</div>
                    <div><strong>প্রিন্টের তারিখ:</strong> {{ \Carbon\Carbon::now('Asia/Dhaka')->format('d-m-Y h:i A') }}</div>
                </div>
                <table class="abc-sheet">
                    <thead><tr>
                        <th style="width:7%;">SL No.</th>
                        <th style="width:23%;">তারিখ ও সময়</th>
                        <th>ব্যয়ের বিবরণ</th>
                        <th style="width:20%;">টাকার পরিমাণ (৳)</th>
                        <th class="no-print" style="width:15%;">Action</th>
                    </tr></thead>
                    <tbody>
                    @forelse($expenses as $index => $expense)
                        <tr>
                            <td class="center">{{ $index + 1 }}</td>
                            <td>{{ \Carbon\Carbon::parse($expense->entry_at, 'Asia/Dhaka')->timezone('Asia/Dhaka')->format('d-m-Y h:i A') }}</td>
                            <td>
                                {{ $expenseHeads[$expense->expense_head] ?? $expense->expense_head }}
                            </td>
                            <td class="money">{{ number_format((float)$expense->amount, 2) }}</td>
                            <td class="no-print" style="white-space:nowrap;text-align:center;">
                                <a href="{{ route('admin.balance.cost.edit', $expense->id) }}" class="abc-btn abc-btn-outline" style="padding:5px 8px;" title="Edit">
                                    <i class="fa fa-pencil"></i>
                                </a>
                                <form method="POST" action="{{ route('admin.balance.cost.destroy', $expense->id) }}" style="display:inline;" onsubmit="return confirm('এই ব্যয়ের এন্ট্রিটি স্থায়ীভাবে মুছে ফেলতে চান?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="abc-btn abc-btn-remove" style="padding:5px 8px;" title="Delete">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="abc-sheet-empty">এই মাসে এখনো কোনো ব্যয়ের এন্ট্রি নেই।</td></tr>
                    @endforelse
                    </tbody>
                    <tfoot><tr><th colspan="3" style="text-align:right;">সর্বমোট ব্যয়</th><th class="money">৳ {{ number_format($monthlyTotal, 2) }}</th><th class="no-print"></th></tr></tfoot>
                </table>
                <div class="abc-paper-sign"><span>প্রস্তুতকারীর স্বাক্ষর</span><span>অনুমোদনকারীর স্বাক্ষর</span></div>
            </div>
        </div>
    </div>
</section>

<script>
(function(){
    var container=document.getElementById('abc-entry-rows');
    var addButton=document.getElementById('abc-add-row');
    var openButton=document.getElementById('abc-open-entry');
    var cancelButton=document.getElementById('abc-cancel-entry');
    var entryCard=document.getElementById('abc-entry-card');
    var rowIndex=1;
    var options=@json($expenseHeads);
    var categoryUrl=@json(route('admin.balance.cost.categories.store'));
    var csrf=@json(csrf_token());
    var message=document.getElementById('abc-category-message');

    function dhakaNowValue(){
        var parts=new Intl.DateTimeFormat('en-CA',{timeZone:'Asia/Dhaka',year:'numeric',month:'2-digit',day:'2-digit',hour:'2-digit',minute:'2-digit',hourCycle:'h23'}).formatToParts(new Date());
        var v={};parts.forEach(function(p){v[p.type]=p.value;});
        return v.year+'-'+v.month+'-'+v.day+'T'+v.hour+':'+v.minute;
    }
    if(openButton && entryCard){
        openButton.addEventListener('click',function(){
            entryCard.style.display='block';
            var firstDate=container.querySelector('input[type="datetime-local"]');
            if(firstDate && container.querySelectorAll('[data-entry-row]').length===1 && !container.querySelector('[data-entry-row] input[type="number"]').value){
                firstDate.value=dhakaNowValue();
            }
            entryCard.scrollIntoView({behavior:'smooth',block:'start'});
        });
    }
    if(cancelButton && entryCard){
        cancelButton.addEventListener('click',function(){entryCard.style.display='none';});
    }
    function escapeHtml(s){return String(s).replace(/[&<>"']/g,function(c){return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[c];});}
    function buildOptions(selected){
        var html='<option value="">ব্যয়ের খাত নির্বাচন করুন</option>';
        Object.keys(options).forEach(function(key){html+='<option value="'+escapeHtml(key)+'" '+(key===selected?'selected':'')+'>'+escapeHtml(options[key])+'</option>';});
        return html;
    }
    function refreshAllDropdowns(selected){
        container.querySelectorAll('.abc-head-select').forEach(function(select){
            var current=selected || select.value;
            select.innerHTML=buildOptions(current);
            select.value=current;
        });
    }
    function updateRemoveButtons(){
        var rows=container.querySelectorAll('[data-entry-row]');
        rows.forEach(function(row){var b=row.querySelector('[data-remove-row]');if(b)b.disabled=rows.length===1;});
    }
    function bindRow(row){
        row.querySelector('[data-remove-row]').addEventListener('click',function(){row.remove();updateRemoveButtons();});
        row.querySelector('.abc-save-category').addEventListener('click',function(){
            var input=row.querySelector('.abc-new-category-input'),name=input.value.trim();
            if(!name){showMessage('নতুন খরচের খাতের নাম লিখুন।',false);input.focus();return;}
            var button=this;button.disabled=true;
            fetch(categoryUrl,{method:'POST',headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':csrf,'X-Requested-With':'XMLHttpRequest'},body:JSON.stringify({name:name})})
            .then(function(res){return res.json().then(function(data){if(!res.ok)throw new Error(data.message||'খাত যোগ করা যায়নি।');return data;});})
            .then(function(data){options[data.category.name]=data.category.name;refreshAllDropdowns(data.category.name);input.value='';showMessage(data.message,true);})
            .catch(function(err){showMessage(err.message,false);})
            .finally(function(){button.disabled=false;});
        });
    }
    function showMessage(text,ok){message.textContent=text;message.className='abc-alert '+(ok?'abc-alert-success':'abc-alert-danger');message.style.display='block';}
    addButton.addEventListener('click',function(){
        var index=rowIndex++,row=document.createElement('div');row.className='abc-row';row.setAttribute('data-entry-row','');
        row.innerHTML=
        '<div><label>তারিখ ও সময়</label><input type="datetime-local" name="entries['+index+'][entry_at]" value="'+dhakaNowValue()+'" required><div class="abc-help">Bangladesh local time (Asia/Dhaka)</div></div>'+
        '<div><label>ব্যয়ের বিবরণ</label><select name="entries['+index+'][expense_head]" class="abc-head-select" required>'+buildOptions()+'</select><div class="abc-category-tools"><input type="text" class="abc-new-category-input" placeholder="নিজের নতুন খরচের খাত লিখুন"><button type="button" class="abc-btn abc-btn-outline abc-save-category"><i class="fa fa-plus"></i> খাত যোগ</button></div></div>'+
        '<div><label>টাকার পরিমাণ (৳)</label><input type="number" name="entries['+index+'][amount]" min="0.01" step="0.01" placeholder="যেমন: 1500" required></div>'+
        '<div><button type="button" class="abc-btn abc-btn-remove" data-remove-row title="সারি বাদ দিন"><i class="fa fa-trash"></i></button></div>';
        container.appendChild(row);bindRow(row);updateRemoveButtons();
    });
    container.querySelectorAll('[data-entry-row]').forEach(bindRow);updateRemoveButtons();
})();
</script>
@endsection
