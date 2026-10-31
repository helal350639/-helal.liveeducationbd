<?php
namespace App\Services\Payments;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/** Wallet debit must run inside a DB transaction, after locking the student row. */
class LebdReferralWallet
{
    public function availableForLockedStudent(object $student): int
    {
        // Amounts are converted to paisa to avoid float comparisons during purchases.
        $pending = DB::table('withdraw_requests')->where('student_id', $student->id)
            ->where('status', 'pending')->sum('amount');
        return max(0, $this->paisa($student->referral_balance) - $this->paisa($pending));
    }

    public function paisa($amount): int
    {
        return (int) round((float) $amount * 100);
    }

    public function debitLockedStudent(object $student, int $amountPaisa): void
    {
        if ($amountPaisa < 100 || $this->availableForLockedStudent($student) < $amountPaisa) {
            throw ValidationException::withMessages(['payment' => 'Insufficient available referral balance.']);
        }
        $newPaisa = $this->paisa($student->referral_balance) - $amountPaisa;
        DB::table('students')->where('id', $student->id)
            ->update(['referral_balance' => number_format($newPaisa / 100, 2, '.', '')]);
    }
}
