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



    <!-- REFERRAL TRANSACTION -->

    <div class="history-card">


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


    <div class="history-card">


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
</style>


@endsection