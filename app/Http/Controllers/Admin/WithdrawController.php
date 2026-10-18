<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\WithdrawRequest;
use App\Models\Student;


class WithdrawController extends Controller
{

    public function index()
    {

        $withdraws = WithdrawRequest::with('student')
        ->latest()
        ->get();


        return view(
            'admin.withdraw.index',
            compact('withdraws')
        );

    }



    // Approve Withdraw

    public function approve($id)
    {

        $withdraw = WithdrawRequest::findOrFail($id);


        if($withdraw->status != 'pending')
        {
            return back();
        }


        $student = Student::find($withdraw->student_id);



        // Balance Check

        if($student->referral_balance < $withdraw->amount)
        {
            return back()->with(
                'error',
                'Insufficient student balance'
            );
        }



        // Deduct Balance

        $student->referral_balance -= $withdraw->amount;

        $student->save();



        // Update Withdraw Status

        $withdraw->status = 'approved';

        $withdraw->save();



        return back()->with(
            'success',
            'Withdraw approved successfully'
        );

    }




    // Reject Withdraw

    public function reject($id)
    {

        $withdraw = WithdrawRequest::findOrFail($id);


        $withdraw->status = 'rejected';

        $withdraw->save();



        return back()->with(
            'success',
            'Withdraw rejected'
        );

    }

}