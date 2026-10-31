<?php
namespace App\Http\Controllers\Students;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BatchRoutineViewController extends Controller
{
    public function show(Request $request, int $batchId)
    {
        $student = Auth::guard('student')->user();
        abort_unless($student && $student->batches()->where('batches.id', $batchId)->exists(), 403);
        $batch = DB::table('batches')->where('id', $batchId)->first();
        abort_unless($batch, 404);
        $verified = [];
        if (Schema::hasTable('orders') && Schema::hasColumn('orders', 'department_id')
            && Schema::hasColumn('orders', 'status')) {
            $orderIds = DB::table('orders')->where('student_id', $student->id)
                ->where('batch_id', $batchId)->whereIn('status', ['Processing','Complete'])
                ->whereNotNull('department_id')->pluck('department_id')->map(fn ($v) => (int)$v)->all();
            $memberIds = $student->departments()->pluck('departments.id')->map(fn($v) => (int)$v)->all();
            $verified = array_values(array_unique(array_intersect($orderIds, $memberIds)));
        }
        $departmentId = $request->query('department_id');
        if ($departmentId !== null && $departmentId !== '') {
            abort_unless(ctype_digit((string)$departmentId) && in_array((int)$departmentId, $verified, true), 403);
            $departmentId = (int)$departmentId;
        } elseif (count($verified) === 1) {
            $departmentId = $verified[0];
        } else {
            $departmentId = null;
        }
        $departmentName = $departmentId ? DB::table('departments')->where('id', $departmentId)->value('name') : null;

        $routine = $departmentId
            ? \App\Services\BatchPublishedRoutineSelector::find((int)$batch->course_id, $batchId, $departmentId)
            : null;
        $exams = [];
        $pdfUrl = null;
        if ($routine) {
            // Only public local URLs or https links are allowed; never expose a private server file path.
            $pdf = trim((string)($routine->pdf ?? ''));
            if ($pdf !== '' && preg_match('~^https?://~i',$pdf)) $pdfUrl = $pdf;
            elseif ($pdf !== '' && str_starts_with($pdf,'/') && !str_starts_with($pdf,'//')) $pdfUrl = url($pdf);
            elseif ($pdf !== '' && !str_contains($pdf,'..') && !str_contains($pdf,'\\') && !preg_match('~^[a-z]+:~i',$pdf)) $pdfUrl = asset($pdf);

            $main = DB::table('routine_item_rows')->where('routine_id',$routine->id)
                ->get(['id','paper_name','exam_at','exam_end_at']);
            $derived = Schema::hasTable('routine_derived_exam_items')
                ? DB::table('routine_derived_exam_items')->where('routine_id',$routine->id)
                    ->get(['id','exam_name','exam_at','exam_end_at']) : collect();
            $now = Carbon::now('Asia/Dhaka');
            foreach ($main as $item) {
                $exams[] = $this->makeExam($item->paper_name,$item->exam_at,$item->exam_end_at,
                    $this->topics((int)$item->id,$departmentId), $now, 'main');
            }
            foreach ($derived as $item) {
                $exams[] = $this->makeExam($item->exam_name,$item->exam_at,$item->exam_end_at,
                    [['name'=>'Revision / Model','syllabus'=>'Admin Routine-এ সংযুক্ত মূল পরীক্ষার বিষয়বস্তু অনুসরণ করুন।']], $now, 'derived');
            }
            usort($exams,function($a,$b){
                if ($a['sort_stamp'] !== $b['sort_stamp']) return $a['sort_stamp'] <=> $b['sort_stamp'];
                return strnatcasecmp($a['name'],$b['name']);
            });
        }
        return view('student-exams.routine', [
            'examTitle' => $batch->name . ($departmentName ? ' — '.$departmentName : ''),
            'routineData' => $exams,
            'routineName' => $routine->name ?? null,
            'routinePdfUrl' => $pdfUrl,
            'routineIsConnected' => true,
        ]);
    }

    private function topics(int $itemId, ?int $departmentId): array
    {
        $topics = [];
        if ($departmentId) {
            $d = DB::table('routine_department_rows as rd')
                ->join('departments as d','d.id','=','rd.department_id')
                ->where('rd.item_id',$itemId)->where('rd.department_id',$departmentId)
                ->first(['d.name','rd.details']);
            if ($d) $topics[] = ['name'=>$d->name,'syllabus'=>$this->clean($d->details)];
        }
        foreach(DB::table('routine_subject_rows as rs')
            ->leftJoin('subjects as s','s.id','=','rs.subject_id')
            ->where('rs.item_id',$itemId)->orderBy('rs.id')
            ->get(['s.name','rs.chapter']) as $s) {
            $topics[] = ['name'=>$s->name ?: 'Subject','syllabus'=>$this->clean($s->chapter)];
        }
        return $topics ?: [['name'=>'Syllabus','syllabus'=>'এখনো বিষয়বস্তু যোগ করা হয়নি।']];
    }
    private function clean($html): string
    {
        $txt = preg_replace('~<br\s*/?>|</p>|</li>|</div>~i','; ',(string)$html);
        return trim(preg_replace('/\s+/u',' ',html_entity_decode(strip_tags($txt),ENT_QUOTES|ENT_HTML5,'UTF-8'))) ?: '—';
    }
    private function makeExam($name,$start,$end,$topics,Carbon $now,string $kind): array
    {
        $begin = $start ? Carbon::parse($start,'Asia/Dhaka') : null;
        $finish = $end ? Carbon::parse($end,'Asia/Dhaka') : null;
        $status = !$begin ? 'upcoming' : ($now->lt($begin) ? 'upcoming' : ($finish && $now->lt($finish) ? 'running' : 'completed'));
        $time = $begin ? $begin->format('h:i A') : 'নির্ধারিত হয়নি';
        if ($finish) $time .= ' - '.$finish->format('h:i A');
        return [
            'number'=>(string)$name,'name'=>(string)$name,
            'status'=>$status,'status_text'=>['upcoming'=>'Upcoming','running'=>'Live Now','completed'=>'Completed'][$status],
            'date'=>$begin ? $begin->format('d M Y') : 'নির্ধারিত হয়নি',
            'time'=>$time,'subjects'=>$topics,
            'details'=>'পরীক্ষার নির্ধারিত সময় ও সিলেবাস Admin Routine অনুযায়ী প্রদর্শিত হচ্ছে।',
            'sort_stamp'=>$begin ? $begin->timestamp : PHP_INT_MAX,
        ];
    }
}
