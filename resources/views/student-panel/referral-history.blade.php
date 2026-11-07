@extends('student')

@section('title','Transaction History')


@section('content')


<div class="referral-page">


    <!-- HEADER -->

    <div class="referral-header">


        <a href="{{ route('students.referral') }}" class="back-btn">

            <i class="fa-solid fa-arrow-left"></i>

        </a>


        <h2>
            Transaction History
        </h2>


    </div>




    {{-- LEBD-05: UI-only tabs; the existing transaction collections remain unchanged. --}}
    <div class="lebd-history-tabs" role="tablist" aria-label="Transaction history type">
        <button type="button" class="lebd-history-tab is-active"
                id="lebd-tab-referral" role="tab" aria-selected="true"
                aria-controls="lebd-panel-referral" tabindex="0"
                data-history-tab="referral">
            <i class="fa-solid fa-hand-holding-dollar" aria-hidden="true"></i>
            Referral Income History
        </button>
        <button type="button" class="lebd-history-tab"
                id="lebd-tab-withdraw" role="tab" aria-selected="false"
                aria-controls="lebd-panel-withdraw" tabindex="-1"
                data-history-tab="withdraw">
            <i class="fa-solid fa-money-bill-transfer" aria-hidden="true"></i>
            Withdraw History
        </button>
    </div>

        <!-- REFERRAL TRANSACTION -->

    <div class="history-card" id="lebd-panel-referral" role="tabpanel" aria-labelledby="lebd-tab-referral">


        <h3>
            Referral Earnings History
        </h3>



        @forelse($transactions as $transaction)


        <div class="history-item">


            <div>

                <b>
                    {{ ucfirst($transaction->type) }}
                </b>


                <p>
                    {{ $transaction->created_at->format('d M Y') }}
                </p>

            </div>



            <div class="amount">


                <strong>
                    ৳ {{ number_format($transaction->amount,2) }}
                </strong>


                <br>


                @if($transaction->status == 'approved')

                    <span class="approved">
                        Approved
                    </span>

                @elseif($transaction->status == 'pending')

                    <span class="pending">
                        Pending
                    </span>

                @else

                    <span class="rejected">
                        Rejected
                    </span>

                @endif


            </div>


        </div>


        @empty


        <p class="empty">
            No earning transaction found
        </p>


        @endforelse


    </div>






    <!-- WITHDRAW HISTORY -->


    <div class="history-card" id="lebd-panel-withdraw" role="tabpanel" aria-labelledby="lebd-tab-withdraw" hidden>


        <h3>
            Withdraw History
        </h3>



        @forelse($withdraws as $withdraw)



        <div class="history-item">


            <div>

                <b>
                    Withdraw
                </b>


                <p>
                    {{ $withdraw->created_at->format('d M Y') }}
                </p>


                <small>
                    {{ ucfirst($withdraw->method) }}
                </small>

            </div>




            <div class="amount">


                <strong>
                    ৳ {{ number_format($withdraw->amount,2) }}
                </strong>


                <br>


                @if($withdraw->status == 'approved')

                    <span class="approved">
                        Approved
                    </span>


                @elseif($withdraw->status == 'pending')

                    <span class="pending">
                        Pending
                    </span>


                @else

                    <span class="rejected">
                        Rejected
                    </span>


                @endif


            </div>


        </div>



        @empty


        <p class="empty">
            No withdraw history found
        </p>


        @endforelse



    </div>



</div>





{{-- LEBD-05: Presentational switching only; does not change financial history queries. --}}
<script>
(function () {
    'use strict';
    function bootHistoryTabs() {
        var buttons = Array.from(document.querySelectorAll('[data-history-tab]'));
        var panels = {
            referral: document.getElementById('lebd-panel-referral'),
            withdraw: document.getElementById('lebd-panel-withdraw')
        };
        if (buttons.length !== 2 || !panels.referral || !panels.withdraw) return;

        function activate(which, updateHash) {
            if (!panels[which]) return;
            buttons.forEach(function (button) {
                var chosen = button.dataset.historyTab === which;
                button.classList.toggle('is-active', chosen);
                button.setAttribute('aria-selected', chosen ? 'true' : 'false');
                button.tabIndex = chosen ? 0 : -1;
            });
            Object.keys(panels).forEach(function (key) {
                panels[key].hidden = (key !== which);
            });
            if (updateHash && window.history && window.history.replaceState) {
                window.history.replaceState(null, '', '#' + (which === 'withdraw' ? 'withdraw-history' : 'referral-income-history'));
            }
        }

        buttons.forEach(function (button, index) {
            button.addEventListener('click', function () {
                activate(button.dataset.historyTab, true);
            });
            button.addEventListener('keydown', function (event) {
                if (event.key !== 'ArrowLeft' && event.key !== 'ArrowRight') return;
                event.preventDefault();
                var next = buttons[(index + (event.key === 'ArrowRight' ? 1 : buttons.length - 1)) % buttons.length];
                next.focus();
                activate(next.dataset.historyTab, true);
            });
        });
        activate(window.location.hash === '#withdraw-history' ? 'withdraw' : 'referral', false);
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bootHistoryTabs);
    } else {
        bootHistoryTabs();
    }
})();
</script>

@endsection




@section('stylesheets')


<style>


.referral-page{

    background:#f4f7fb;
    min-height:100vh;
    padding:15px;

}



.referral-header{

    display:flex;
    align-items:center;
    justify-content:center;
    position:relative;
    height:50px;

}



.referral-header h2{

    font-size:22px;
    color:#12345b;

}



.back-btn{

    position:absolute;
    left:5px;
    width:35px;
    height:35px;
    border-radius:50%;
    background:white;
    display:flex;
    align-items:center;
    justify-content:center;
    text-decoration:none;

}



.history-card{

    background:white;
    border-radius:20px;
    padding:15px;
    margin-top:15px;

}



.history-card h3{

    font-size:18px;
    color:#12345b;
    margin-bottom:15px;

}



.history-item{

    display:flex;
    justify-content:space-between;
    align-items:center;

    background:#f4f7ff;
    border-radius:15px;

    padding:12px;

    margin-bottom:10px;

}



.history-item p{

    margin:3px 0;
    font-size:12px;
    color:#777;

}



.amount{

    text-align:right;

}



.approved,
.pending,
.rejected{

    padding:4px 10px;

    border-radius:10px;

    font-size:11px;

}



.approved{

    background:#d4edda;
    color:green;

}



.pending{

    background:#fff3cd;
    color:#856404;

}



.rejected{

    background:#f8d7da;
    color:red;

}



.empty{

    text-align:center;
    color:#777;

}

/* ================= MOBILE FIX ================= */

html,
body{
    width:100%;
    overflow-x:hidden;
}


.referral-page{

    width:100%;
    max-width:100%;
    padding:15px;
    box-sizing:border-box;

}



.referral-header{

    width:100%;
    overflow:hidden;

}


.referral-header h2{

    font-size:20px;
    white-space:nowrap;

}



.history-card{

    width:100%;
    box-sizing:border-box;
    overflow:hidden;

}



.history-item{

    width:100%;
    box-sizing:border-box;
    display:flex;
    flex-wrap:wrap;
    gap:10px;

}



.history-item b{

    font-size:14px;

}



.history-item p{

    font-size:12px;

}



.amount{

    margin-left:auto;
    text-align:right;

}



@media(max-width:768px){


.referral-page{

    padding:10px !important;

}



.history-card h3{

    font-size:16px;

}



.history-item{

    padding:10px;

}



.amount strong{

    font-size:14px;

}


}

/* LEBD-05 — Transaction History tabs only */
.lebd-history-tabs {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 10px;
    margin-top: 16px;
}
.lebd-history-tabs .lebd-history-tab {
    min-width: 0;
    padding: 13px 14px;
    border-radius: 12px;
    border: 1px solid #bfd6f6;
    background: #ffffff;
    color: #174575;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: background-color .2s, box-shadow .2s, color .2s;
}
.lebd-history-tabs .lebd-history-tab.is-active {
    background: #0863d8;
    border-color: #0863d8;
    color: #ffffff;
    box-shadow: 0 4px 12px rgba(8,99,216,.18);
}
.lebd-history-tabs .lebd-history-tab:focus-visible {
    outline: 3px solid #f59e0b;
    outline-offset: 2px;
}
.referral-page .history-card[hidden] { display: none !important; }
@media (max-width: 520px) {
    .lebd-history-tabs { gap: 7px; }
    .lebd-history-tabs .lebd-history-tab { padding: 11px 7px; font-size: 12px; }
}

</style>


@endsection