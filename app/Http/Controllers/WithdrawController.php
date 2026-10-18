<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WithdrawRequest;

class WithdrawController extends Controller
{

    public function create()
    {
        $student = auth('student')->user();

        return view('student-panel.withdraw', compact('student'));
    }


    public function store(Request $request)
    {

        $student = auth('student')->user();


        $request->validate([
            'amount' => 'required|numeric|min:1',
            'method' => 'required',
            'account_number' => 'required'
        ]);


        // Balance Check
        if($request->amount > $student->referral_balance)
        {
            return back()->with(
                'error',
                'Insufficient balance'
            );
        }


        WithdrawRequest::create([

            'student_id' => $student->id,
            'amount' => $request->amount,
            'method' => $request->method,
            'account_name' => $request->account_name,
            'account_number' => $request->account_number,
            'status' => 'pending'

        ]);

    

        return back()->with(
            'success',
            'Withdraw request submitted successfully'
        );

    }

}