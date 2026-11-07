<?php
namespace App\Services\Referral;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Read-only credited referral income. Never uses package bonus * referral count,
 * available wallet balance, pending rows or unverified checkout references.
 * A bad/missing table in one module must not disable the other four cards.
 */
final class FiveSectionReferralEarnings
{
    private const LABELS = [
        'batch' => ['Batch / Course', 'fa-graduation-cap'],
        'exam'  => ['Exam Section', 'fa-calendar-check'],
        'mcq'   => ['MCQ Study', 'fa-list-check'],
        'video' => ['Video Class', 'fa-video'],
        'ebook' => ['E-Book', 'fa-book'],
    ];

    public function forStudent(int $studentId): array
    {
        $out = [];
        foreach (self::LABELS as $key => $label) {
            $earned = ['verified' => false, 'earned' => 0.0];
            $count = 0;
            if ($studentId > 0) {
                try {
                    $earned = $this->earnedFor($key, $studentId);
                } catch (\Throwable $e) {
                    Log::warning('LEBD section income calculation unavailable', ['section'=>$key,'error'=>$e->getMessage()]);
                }
                try {
                    $count = $this->activeCountFor($key, $studentId);
                } catch (\Throwable $e) {
                    // Counts are secondary and must NEVER hide valid earned income.
                    Log::warning('LEBD section active count unavailable', ['section'=>$key,'error'=>$e->getMessage()]);
                }
            }
            $out[$key] = [
                'label'=>$label[0], 'icon'=>$label[1], 'count'=>$count,
                'verified'=>$earned['verified'], 'earned'=>$earned['earned'],
            ];
        }
        return $out;
    }

    private function hasColumns(string $table, array $columns): bool
    {
        if (!Schema::hasTable($table)) return false;
        foreach ($columns as $column) {
            if (!Schema::hasColumn($table, $column)) return false;
        }
        return true;
    }

    private function earnedFor(string $key, int $studentId): array
    {
        $ledger = [
            'exam'=>'lebd_exam_section_referral_credits',
            'ebook'=>'lebd_ebook_referral_credits',
        ];
        if (isset($ledger[$key])) {
            $table=$ledger[$key];
            if (!$this->hasColumns($table, ['referrer_id','referrer_bonus','credited_at'])) {
                return ['verified'=>false,'earned'=>0.0];
            }
            $amount = DB::table($table)->where('referrer_id',$studentId)
                ->whereNotNull('credited_at')->sum('referrer_bonus');
            return ['verified'=>true,'earned'=>round((float)$amount,2)];
        }

        // Older Batch/MCQ/Video implementations may record paid bonuses here.
        // A type needs an explicit record before the table is treated as that
        // module's authoritative income source. A 'pending' row is NOT earned.
        $table='referral_transactions';
        if (!$this->hasColumns($table, ['student_id','type','amount','status'])) {
            return ['verified'=>false,'earned'=>0.0];
        }
        $types = [
            'batch'=>['batch','course','batch_referral','course_referral'],
            'mcq'=>['mcq','mcq_referral','mcq_subscription'],
            'video'=>['premium_video','video','video_referral','premium_class'],
        ];
        $typeList=$types[$key] ?? [];
        if (!$typeList) return ['verified'=>false,'earned'=>0.0];
        $source = DB::table($table)->where('student_id',$studentId)
            ->whereIn('type',$typeList);
        if (!(clone $source)->exists()) return ['verified'=>false,'earned'=>0.0];
        $credited = (clone $source)->whereIn('status',[
            'completed','complete','paid','credited','success','successful','approved',
            'Completed','Complete','Paid','Credited','Success','Successful','Approved',
        ]);
        return ['verified'=>true,'earned'=>round((float)$credited->sum('amount'),2)];
    }

    private function activeCountFor(string $key, int $studentId): int
    {
        $now=now('Asia/Dhaka')->toDateTimeString();
        switch ($key) {
            case 'batch':
                if (!$this->hasColumns('batch_student',['student_id','batch_id']) ||
                    !$this->hasColumns('batches',['id'])) return 0;
                $q=DB::table('batch_student as bs')->join('batches as b','b.id','=','bs.batch_id')
                    ->where('bs.student_id',$studentId);
                if (Schema::hasColumn('batches','status')) $q->whereIn('b.status',['Active','active']);
                if (Schema::hasColumn('batches','start_at')) $q->where(fn($a)=>$a->whereNull('b.start_at')->orWhere('b.start_at','<=',$now));
                if (Schema::hasColumn('batches','end_at')) $q->where(fn($a)=>$a->whereNull('b.end_at')->orWhere('b.end_at','>',$now));
                return (int)$q->distinct()->count('b.id');
            case 'exam':
                if (!$this->hasColumns('lebd_exam_section_student_package_access',['student_id','package_id','status','starts_at','ends_at'])) return 0;
                return (int)DB::table('lebd_exam_section_student_package_access')
                    ->where('student_id',$studentId)->whereIn('status',['active','Active'])
                    ->where('starts_at','<=',$now)->where('ends_at','>',$now)->distinct()->count('package_id');
            case 'mcq':
                if (!$this->hasColumns('lebd_mcq_subscriptions',['student_id','package_id','starts_at','ends_at'])) return 0;
                return (int)DB::table('lebd_mcq_subscriptions')->where('student_id',$studentId)
                    ->where('starts_at','<=',$now)->where('ends_at','>',$now)->distinct()->count('package_id');
            case 'video':
                if (!$this->hasColumns('lebd_premium_class_orders',['student_id','package_id','status','expires_at'])) return 0;
                return (int)DB::table('lebd_premium_class_orders')->where('student_id',$studentId)
                    ->where('status','paid')->where('expires_at','>',$now)->distinct()->count('package_id');
            case 'ebook':
                $ids=[];
                if ($this->hasColumns('lebd_ebook_manual_grants',['student_id','package_id','starts_at','ends_at'])) {
                    $ids=DB::table('lebd_ebook_manual_grants')->where('student_id',$studentId)
                        ->where('starts_at','<=',$now)->where('ends_at','>',$now)->pluck('package_id')->all();
                }
                if ($this->hasColumns('lebd_ebook_entitlements',['user_id','package_id','status','expires_at'])) {
                    $paid=DB::table('lebd_ebook_entitlements')->where('user_id',$studentId)
                        ->where('status','Active')->where('expires_at','>',$now)->pluck('package_id')->all();
                    $ids=array_merge($ids,$paid);
                }
                return count(array_unique(array_filter(array_map('intval',$ids))));
        }
        return 0;
    }
}
