<?php
namespace App\Http\Controllers;

use App\Models\WithdrawRequest;
use App\Services\Payments\LebdReferralWallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WithdrawController extends Controller
{
    public function create() {
        $student = auth('student')->user();
        abort_unless($student, 401);
        return view('student-panel.withdraw', compact('student'));
    }

    public function store(Request $request, LebdReferralWallet $wallet) {
        $data = $request->validate([
            'amount' => 'required|numeric|min:1',
            'method' => 'required',
            'account_name' => 'nullable|string|max:255',
            'account_number' => 'required|string|max:255',
        ]);
        $id = auth('student')->id();
        abort_unless($id, 401);
        DB::transaction(function () use ($data, $id, $wallet) {
            $student = DB::table('students')->where('id', $id)->lockForUpdate()->first();
            abort_unless($student, 401);
            if ($wallet->paisa($data['amount']) > $wallet->availableForLockedStudent($student)) {
                throw ValidationException::withMessages(['amount' => 'Insufficient available balance.']);
            }
            WithdrawRequest::create([
                'student_id' => $id, 'amount' => $data['amount'],
                'method' => $data['method'], 'account_name' => $data['account_name'] ?? null,
                'account_number' => $data['account_number'], 'status' => 'pending',
            ]);
        }, 3);
        return back()->with('success', 'Withdraw request submitted successfully');
    }
}
