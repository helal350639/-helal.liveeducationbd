<?php
namespace App\Http\Controllers\Students;

use App\Http\Controllers\Controller;
use App\Http\Controllers\SourceCtrl;
use App\Models\Batch;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class BatchReferralCheckoutController extends Controller
{
    private function amount(Batch $batch): int
    {
        // Exact integer paisa calculation on SERVER, matching checkout's active offer/referral logic.
        $price = (int) round(((float)$batch->price + (float)$batch->fee) * 100);
        if ($batch->discount && $batch->offer_end_at && $batch->offer_end_at > now()) {
            $price -= (int) round((float)$batch->discount * 100);
        }
        $ref = session('batch-referrer');
        if ($batch->reference_discount && is_array($ref) && (int)($ref['batch'] ?? 0) === (int)$batch->id) {
            $price -= (int) round((float)$batch->reference_discount * 100);
        }
        return max(0, $price);
    }

    private function available(int $studentId, int $balancePaisa): int
    {
        $pending = DB::table('withdraw_requests')->where('student_id', $studentId)
            ->where('status', 'pending')->sum('amount');
        return max(0, $balancePaisa - (int)round((float)$pending * 100));
    }

    private function selected(Request $request): array
    {
        $data = $request->validate([
            'batch_id' => 'required|integer|exists:batches,id',
            'department_id' => 'required|integer|exists:departments,id',
        ]);
        $batch = Batch::findOrFail($data['batch_id']);
        abort_unless($batch->departments()->where('departments.id',$data['department_id'])->exists(),422,'Invalid department for this batch.');
        return [$batch,(int)$data['department_id']];
    }

    public function preview(Request $request)
    {
        abort_unless(auth('student')->check(),403);
        [$batch,$departmentId] = $this->selected($request);
        $student = Student::findOrFail(auth('student')->id());
        abort_if($student->batches()->where('batches.id',$batch->id)->exists(),409,'You already own this batch.');
        $price = $this->amount($batch);
        abort_if($price <= 0,422,'Free batches use the existing enrolment flow.');
        $balance=(int)round((float)$student->referral_balance*100);
        $available=$this->available($student->id,$balance);
        abort_if($available < $price,422,'Insufficient available referral balance.');
        $token=(string)Str::uuid();
        session(['lebd_batch_referral_preview' => ['batch_id'=>$batch->id,'department_id'=>$departmentId,'token'=>$token,'student_id'=>$student->id]]);
        return view('student-panel.batch-referral-preview',[
            'batch'=>$batch,'department'=> $batch->departments()->findOrFail($departmentId),
            'price'=>$price/100,'available'=>$available/100,'remaining'=>($available-$price)/100,
            'token'=>$token
        ]);
    }

    public function confirm(Request $request)
    {
        abort_unless(auth('student')->check(),403);
        $request->validate(['token'=>'required|uuid']);
        $session=session('lebd_batch_referral_preview');
        abort_unless(is_array($session) && hash_equals((string)($session['token']??''),(string)$request->token)
            && (int)($session['student_id']??0)===auth('student')->id(),419,'Preview expired. Please review the payment again.');
        // Consume session before operation; repeat requests must not start another deduction.
        session()->forget('lebd_batch_referral_preview');
        $studentId=auth('student')->id();
        $batchId=(int)$session['batch_id'];
        $deptId=(int)$session['department_id'];
        $receipt=DB::transaction(function() use($studentId,$batchId,$deptId){
            $student=Student::whereKey($studentId)->lockForUpdate()->firstOrFail();
            $batch=Batch::findOrFail($batchId);
            abort_unless($batch->departments()->where('departments.id',$deptId)->exists(),422);
            abort_if($student->batches()->where('batches.id',$batchId)->exists(),409,'You already own this batch.');
            $price=$this->amount($batch);
            abort_if($price<=0,422);
            $balance=(int)round((float)$student->referral_balance*100);
            abort_if($this->available($studentId,$balance)<$price,422,'Insufficient balance.');
            // Order, wallet change and enrollment all commit or roll back together.
            $tran='LEBDBR'.strtoupper(Str::random(20));
            DB::table('orders')->insert([
                'student_id'=>$studentId,'batch_id'=>$batchId,'department_id'=>$deptId,
                'name'=>$student->name,'email'=>$student->email,'phone'=>$student->contact,
                'amount'=>$price/100,'status'=>'Processing','address'=>'',
                'transaction_id'=>$tran,'currency'=>'BDT',
                'created_at'=>now(),
            ]);
            $student->referral_balance=($balance-$price)/100;
            $student->save();
            $student->batches()->syncWithoutDetaching([$batchId]);
            $student->departments()->syncWithoutDetaching([$deptId]);
            return ['transaction_id'=>$tran,'batch'=>$batch->name,'mobile'=>$student->contact];
        },3);
        // SMS is secondary: a gateway failure cannot undo a completed purchase.
        if (!app()->environment('local') && $receipt['mobile']) {
            try {
                (new SourceCtrl())->sms_send('88'.ltrim((string)$receipt['mobile'],'+88'),
                    'Live Education BD: Your batch purchase was successful. Log in to My Batch to access your course. Thank you.');
            } catch (\Throwable $e) {Log::warning('Batch referral purchase SMS failed',['order'=>$receipt['transaction_id']]);}
        }
        return redirect()->route('students.my-course')->with('success','আপনার ব্যাচ কেনা সফল হয়েছে। My Batch থেকে ক্লাস দেখুন।');
    }
}
