<?php
namespace App\Http\Controllers\Admin\RoutineAutoQuestion;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use App\Services\RoutineAutoQuestion\SavedRoutineExamService;

class AutoQuestionController extends Controller
{
    private function nowBd(): Carbon
    {
        return Carbon::now('Asia/Dhaka');
    }

    /** Only real saved exam items are visible, regardless of exam end date. */
    private function savedExam(Builder $query): Builder
    {
        return app(SavedRoutineExamService::class)->filter($query);
    }

    private function routineExamQuery(): Builder
    {
        return DB::table('routine_exams as e')
            ->join('routines as r', 'r.id', '=', 'e.routine_id')
            ->join('batches as b', function ($join) {
                $join->on('b.id', '=', 'r.batch_id')
                    ->on('b.course_id', '=', 'r.course_id');
            })
            ->where('r.status', 'Published');
    }

    public function index()
    {
        $now = $this->nowBd();
        $stamp = $now->toDateTimeString();
        $courses = DB::table('courses as c')
            ->leftJoin('batches as b', function ($join) use ($stamp) {
                $join->on('b.course_id', '=', 'c.id')
                    ->where('b.status', 'Active')
                    ->where('b.start_at', '<=', $stamp)
                    ->where('b.end_at', '>=', $stamp);
            })
            ->select('c.id', 'c.name', 'c.status', 'c.created_at', 'c.updated_at')
            ->selectRaw('COUNT(DISTINCT b.id) as active_batch_count')
            ->groupBy('c.id', 'c.name', 'c.status', 'c.created_at', 'c.updated_at')
            ->orderByDesc('c.id')->get();

        $activeRoutines = DB::table('routines as r')
            ->join('batches as b', function ($join) {
                $join->on('b.id', '=', 'r.batch_id')->on('b.course_id', '=', 'r.course_id');
            })
            ->where('r.status', 'Published')
            ->where('b.status', 'Active')
            ->where('b.start_at', '<=', $stamp)
            ->where('b.end_at', '>=', $stamp);
        $routineCounts = $activeRoutines->select('r.course_id')
            ->selectRaw('COUNT(DISTINCT r.id) as total')
            ->groupBy('r.course_id')->pluck('total', 'r.course_id');

        $exams = $this->routineExamQuery()
            ->where('b.status', 'Active')
            ->where('b.start_at', '<=', $stamp)
            ->where('b.end_at', '>=', $stamp);
        $examCounts = $this->savedExam($exams)->select('r.course_id')
            ->selectRaw('COUNT(DISTINCT e.id) as total')
            ->groupBy('r.course_id')->pluck('total', 'r.course_id');

        return view('admin.routine-auto-question.index', compact('courses', 'routineCounts', 'examCounts'));
    }

    public function course(int $courseId)
    {
        $course = DB::table('courses')->where('id', $courseId)->first();
        abort_unless($course, 404);
        $now = $this->nowBd();
        $stamp = $now->toDateTimeString();
        $batches = DB::table('batches')->where('course_id', $courseId)
            ->where('status', 'Active')->where('start_at', '<=', $stamp)
            ->where('end_at', '>=', $stamp)->orderBy('end_at')->get();
        $ids = $batches->pluck('id');
        $routineCounts = DB::table('routines as r')
            ->join('batches as b', function ($join) {
                $join->on('b.id', '=', 'r.batch_id')->on('b.course_id', '=', 'r.course_id');
            })->where('r.course_id', $courseId)->whereIn('r.batch_id', $ids)
            ->where('r.status', 'Published')
            ->select('r.batch_id')->selectRaw('COUNT(DISTINCT r.id) as total')
            ->groupBy('r.batch_id')->pluck('total', 'r.batch_id');
        $exams = $this->routineExamQuery()->where('r.course_id', $courseId)
            ->whereIn('r.batch_id', $ids);
        $examCounts = $this->savedExam($exams)->select('r.batch_id')
            ->selectRaw('COUNT(DISTINCT e.id) as total')
            ->groupBy('r.batch_id')->pluck('total', 'r.batch_id');
        return view('admin.routine-auto-question.course', compact('course', 'batches', 'now', 'routineCounts', 'examCounts'));
    }

    public function batch(int $courseId, int $batchId)
    {
        $course = DB::table('courses')->where('id', $courseId)->first();
        abort_unless($course, 404);
        $now = $this->nowBd();
        $batch = DB::table('batches')->where('course_id', $courseId)
            ->where('id', $batchId)->where('status', 'Active')
            ->where('start_at', '<=', $now->toDateTimeString())
            ->where('end_at', '>=', $now->toDateTimeString())->first();
        abort_unless($batch, 404);
        $query = $this->routineExamQuery()
            ->where('r.course_id', $courseId)->where('r.batch_id', $batchId);
        $exams = $this->savedExam($query)
            ->select('r.id as routine_id', 'r.name as routine_name',
                'e.id as exam_id', 'e.name as exam_name', 'e.sort_order')
            ->orderBy('r.id')->orderBy('e.sort_order')->orderBy('e.id')->get();
        return view('admin.routine-auto-question.batch', compact('course', 'batch', 'exams', 'now'));
    }

    private function examContext(int $courseId, int $batchId, int $examId): array
    {
        $course = DB::table('courses')->where('id', $courseId)->first();
        abort_unless($course, 404);
        $now = $this->nowBd();
        $batch = DB::table('batches')->where('course_id', $courseId)
            ->where('id', $batchId)->where('status', 'Active')
            ->where('start_at', '<=', $now->toDateTimeString())
            ->where('end_at', '>=', $now->toDateTimeString())->first();
        abort_unless($batch, 404);
        $query = $this->routineExamQuery()
            ->where('r.course_id', $courseId)->where('r.batch_id', $batchId)
            ->where('e.id', $examId);
        $exam = $this->savedExam($query)
            ->select('r.id as routine_id', 'r.name as routine_name',
                'e.id as exam_id', 'e.name as exam_name', 'e.sort_order')->first();
        abort_unless($exam, 404);
        return compact('course', 'batch', 'exam');
    }

    public function createQuestion(int $courseId, int $batchId, int $examId)
    {
        return view('admin.routine-auto-question.create-question',
            $this->examContext($courseId, $batchId, $examId));
    }

    public function examDetails(int $courseId, int $batchId, int $examId)
    {
        return view('admin.routine-auto-question.exam-details',
            $this->examContext($courseId, $batchId, $examId));
    }
}
