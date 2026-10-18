<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ReferralTransaction;
use App\Models\Student;
use App\Models\WithdrawRequest;


class StudentReferralController extends Controller
{

    public function index()
    {

        $student = auth('student')->user();


        // Registration Earn
        $registrationEarn = ReferralTransaction::where(
            'student_id',
            $student->id
        )
        ->where('type','registration')
        ->sum('amount');



        // Website Share Earn
        $websiteEarn = ReferralTransaction::where(
            'student_id',
            $student->id
        )
        ->where('type','website_share')
        ->sum('amount');



        // MCQ Earn
        $mcqEarn = ReferralTransaction::where(
            'student_id',
            $student->id
        )
        ->where('type','mcq')
        ->sum('amount');



        // Lifetime Total Earn
        $lifetimeEarn = ReferralTransaction::where(
            'student_id',
            $student->id
        )
        ->sum('amount');



        // Total Referral Student
        $totalReferral = Student::where(
            'referred_by',
            $student->id
        )
        ->count();



        // Pending Referral Transaction Balance
        $pendingBalance = ReferralTransaction::where(
            'student_id',
            $student->id
        )
        ->where('status','pending')
        ->sum('amount');



        // Current Balance
        $currentBalance = $student->referral_balance;



        // Pending Withdraw Amount
        $withdrawPending = WithdrawRequest::where(
            'student_id',
            $student->id
        )
        ->where('status','pending')
        ->sum('amount');



        // Available Balance
        $availableBalance = $currentBalance - $withdrawPending;



        // Referral Transaction History
        $transactions = ReferralTransaction::where(
            'student_id',
            $student->id
        )
        ->latest()
        ->get();



        // Withdraw History
        $withdrawRequests = WithdrawRequest::where(
            'student_id',
            $student->id
        )
        ->latest()
        ->get();



        return view('student-panel.referral',
        compact(

            'student',

            'registrationEarn',

            'websiteEarn',

            'mcqEarn',

            'lifetimeEarn',

            'totalReferral',

            'pendingBalance',

            'currentBalance',

            'availableBalance',

            'transactions',

            'withdrawRequests',

            'withdrawPending'

        ));

    }


// Transaction History

    public function history()
    {

        $student = auth('student')->user();


        $transactions = ReferralTransaction::where(
            'student_id',
            $student->id
        )
        ->latest()
        ->get();



        $withdraws = WithdrawRequest::where(
            'student_id',
            $student->id
        )
        ->latest()
        ->get();



        return view(
            'student-panel.referral-history',
            compact(
                'transactions',
                'withdraws'
            )
        );

    }


}