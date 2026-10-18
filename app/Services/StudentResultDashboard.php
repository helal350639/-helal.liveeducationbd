<?php

namespace App\Services;

use App\Models\Exam;
use App\Models\Paper;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Read-only. Does not create or update an exam/choice/paper.
 * Call with Auth::guard('student')->id(), NEVER with a user-provided student_id.
 */
class StudentResultDashboard
{
    public static function build(int $paperId, int $studentId, ?int $examId = null): array
    {
        $paper = Paper::findOrFail($paperId);
        $query = Exam::query()
            ->where('student_id', $studentId)
            ->where('paper_id', $paper->id)
            ->where('status', 'Completed');

        if ($examId !== null) {
            $query->where('id', $examId);
        }

        $exam = $query->orderByDesc('id')->first();
        $base = ['paper' => $paper, 'exam' => $exam, 'published' => false];

        // If no Completed attempt, nothing from the results is queried or displayed.
        if (!$exam) {
            return $base;
        }

        // Same publication rule used by the existing student result controller.
        $releaseAt = $paper->result_at ? Carbon::parse($paper->result_at) : null;
        $published = ($paper->result_view === 'Yes')
            || ($releaseAt !== null && $releaseAt->lessThanOrEqualTo(now()));
        $base['published'] = $published;
        if (!$published) {
            return $base;
        }

        $questionRecords = DB::table('paper_question as pq')
            ->join('questions as q', 'q.id', '=', 'pq.question_id')
            ->where('pq.paper_id', $paper->id)
            ->select('q.id', 'q.title', 'q.image', 'q.explanation', 'q.solution')
            ->distinct()->orderBy('q.id')->get();
        $questionIds = $questionRecords->pluck('id')->map(fn ($n) => (int) $n)->all();
        $total = count($questionIds);
        $perQuestionMark = (float) ($paper->mark ?? 0);
        $minusMark = (float) ($paper->minus ?? 0);
        $maximumMarks = round($total * $perQuestionMark, 2);

        // Future paper pass_mark is nullable; no DB migration is required now.
        // A real question-paper pass mark overrides the temporary 75% policy.
        $configuredPassMark = $paper->pass_mark ?? null;
        $customPassMark = ($configuredPassMark !== null && $configuredPassMark !== ''
                && is_numeric($configuredPassMark))
            ? (float) $configuredPassMark : null;
        $passMark = $maximumMarks > 0
            ? ($customPassMark ?? round($maximumMarks * 0.75, 2)) : null;
        $passPolicy = $customPassMark === null ? '75% (অস্থায়ী নিয়ম)' : 'Question Paper';
        // For subject/department summaries use same pass percentage as the paper.
        // In the future a configured absolute pass_mark becomes a proportional
        // threshold for each category (e.g. 80/100 = 80% for each category).
        $groupPassRatio = $maximumMarks > 0 && $passMark !== null
            ? max(0, $passMark / $maximumMarks)
            : 0.75;
        $groupPassPercent = round($groupPassRatio * 100, 2);

        // Latest valid answer for each question; old duplicate rows are ignored.
        $answers = DB::table('choices as c')
            ->join('mcq_items as mi', 'mi.id', '=', 'c.mcq_id')
            ->where('c.exam_id', $exam->id)
            ->whereIn('c.question_id', $questionIds)
            ->whereColumn('mi.question_id', 'c.question_id')
            ->select('c.id', 'c.question_id', 'c.mcq_id', 'mi.correct_answer')
            ->orderByDesc('c.id')->get()->unique('question_id')->keyBy('question_id');

        $subjects = DB::table('question_subject as qs')
            ->join('subjects as s', 's.id', '=', 'qs.subject_id')
            ->whereIn('qs.question_id', $questionIds)
            ->orderBy('qs.subject_id')
            ->get(['qs.question_id', 's.name'])->groupBy('question_id');
        $departments = DB::table('department_question as dq')
            ->join('departments as d', 'd.id', '=', 'dq.department_id')
            ->whereIn('dq.question_id', $questionIds)
            ->orderBy('dq.department_id')
            ->get(['dq.question_id', 'd.name'])->groupBy('question_id');
        $options = DB::table('mcq_items')
            ->whereIn('question_id', $questionIds)
            ->orderBy('id')
            ->get(['id', 'question_id', 'item', 'image', 'correct_answer'])
            ->groupBy('question_id');

        $subjectStats = [];
        $departmentStats = [];
        $questionRows = [];
        $answered = $correct = $wrong = 0;

        foreach ($questionRecords as $index => $q) {
            $qid = (int) $q->id;
            $answer = $answers->get($qid);
            $status = !$answer ? 'skipped'
                : ($answer->correct_answer !== null ? 'correct' : 'wrong');

            if ($status !== 'skipped') $answered++;
            if ($status === 'correct') $correct++;
            if ($status === 'wrong') $wrong++;

            // One primary subject/department per question avoids double-counting.
            $subject = $subjects->get($qid)?->first()?->name ?? 'সাধারণ / অন্যান্য';
            $department = $departments->get($qid)?->first()?->name ?? 'সাধারণ প্রশ্ন';
            self::tally($subjectStats, $subject, $status);
            self::tally($departmentStats, $department, $status);

            $questionOptions = $options->get($qid, collect());
            $selectedOption = $answer
                ? $questionOptions->first(fn ($o) => (int) $o->id === (int) $answer->mcq_id)
                : null;
            $correctOption = $questionOptions->first(fn ($o) => $o->correct_answer !== null);

            $questionRows[] = [
                'number' => $index + 1,
                'question' => self::plain($q->title),
                'question_image' => $q->image,
                'subject' => $subject,
                'department' => $department,
                'status' => $status,
                'selected' => $selectedOption ? self::optionText($selectedOption) : null,
                'correct_option' => $correctOption ? self::optionText($correctOption) : null,
                'explanation' => self::plain($q->explanation ?: $q->solution),
            ];
        }

        $unanswered = max(0, $total - $answered);
        // Stored final mark is authoritative (including negative marking).
        $score = round((float) ($exam->mark ??
            ($correct * $perQuestionMark - $wrong * $minusMark)), 2);
        $scorePercent = $maximumMarks > 0 ? round($score / $maximumMarks * 100, 1) : null;
        $passed = $passMark !== null ? $score >= $passMark : null;
        $accuracy = $total > 0 ? round($correct / $total * 100, 1) : 0;

        // Merit for the same course + batch + examination name across departments.
        // Only released, completed results are eligible; keep best attempt / student.
        $meritRows = self::batchMeritRows($paper, $studentId);
        $meritDepartments = collect($meritRows)->pluck('department')->unique()->sort()->values()->all();
        $participants = count($meritRows);
        $averageScore = $participants
            ? round(array_sum(array_column($meritRows, 'mark')) / $participants, 2) : 0;
        $averagePercent = $participants
            ? round(array_sum(array_column($meritRows, 'percent')) / $participants, 1) : 0;
        $passedCount = count(array_filter($meritRows, fn ($r) => $r['passed'] === true));
        $failedCount = $participants - $passedCount;
        $passRate = $participants
            ? round($passedCount / $participants * 100, 1) : null;
        $myMerit = collect($meritRows)->first(fn ($r) => $r['is_mine']);
        $ownRank = $myMerit['rank'] ?? null;
        $topFive = array_map(fn ($r) => [
            'position' => $r['rank'],
            'label' => $r['is_mine'] ? 'আপনি · ' . $r['name'] : $r['name'],
            'mark' => $r['mark'],
            'percent' => $r['percent'],
            'is_mine' => $r['is_mine'],
        ], array_slice($meritRows, 0, 5));

        $distribution = [0, 0, 0, 0, 0];
        foreach ($meritRows as $r) {
            $pct = max(0, min(100, $r['percent']));
            $bucket = $pct >= 100 ? 4 : min(4, (int) floor($pct / 20));
            $distribution[$bucket]++;
        }

        $myAttempts = Exam::query()
            ->where('paper_id', $paper->id)
            ->where('student_id', $studentId)
            ->where('status', 'Completed')
            ->orderByDesc('id')
            ->get(['id', 'start_at', 'end_at', 'answer', 'correct', 'wrong', 'no_answer', 'mark'])
            ->map(function ($attempt) use ($passMark) {
                return [
                    'id' => $attempt->id,
                    'start_at' => $attempt->start_at,
                    'end_at' => $attempt->end_at,
                    'score' => (float) $attempt->mark,
                    'answered' => (int) $attempt->answer,
                    'correct' => (int) $attempt->correct,
                    'wrong' => (int) $attempt->wrong,
                    'unanswered' => (int) $attempt->no_answer,
                    'passed' => $passMark !== null
                        ? ((float) $attempt->mark >= $passMark) : null,
                ];
            })->all();

        $courseName = $paper->course_id
            ? DB::table('courses')->where('id', $paper->course_id)->value('name') : null;
        $batchName = $paper->batch_id
            ? DB::table('batches')->where('id', $paper->batch_id)->value('name') : null;
        $departmentName = $paper->department_id
            ? DB::table('departments')->where('id', $paper->department_id)->value('name') : null;

        $durationMinutes = ($exam->start_at && $exam->end_at)
            ? max(0, Carbon::parse($exam->start_at)->diffInMinutes(Carbon::parse($exam->end_at)))
            : null;

        return $base + compact(
            'total', 'answered', 'correct', 'wrong', 'unanswered', 'score',
            'scorePercent', 'accuracy', 'passed', 'passMark', 'customPassMark',
            'passPolicy', 'maximumMarks', 'perQuestionMark', 'minusMark',
            'participants', 'averageScore', 'passedCount', 'failedCount',
            'passRate', 'ownRank', 'topFive', 'distribution', 'myAttempts',
            'meritRows', 'meritDepartments', 'averagePercent',
            'courseName', 'batchName', 'departmentName', 'durationMinutes',
            'groupPassPercent'
        ) + [
            'subjectRows' => self::finishGroups($subjectStats, $perQuestionMark, $minusMark, $groupPassRatio),
            'departmentRows' => self::finishGroups($departmentStats, $perQuestionMark, $minusMark, $groupPassRatio),
            'questionRows' => $questionRows,
        ];
    }

    /**
     * Batch-wide merit for ONE logical examination: same course/batch/name.
     * Across papers of different maximum marks, rank by percentage; display
     * actual marks. A duplicate attempt counts only once (best percentage).
     * Avoid disclosing unreleased scores, or students outside this batch.
     */
    private static function batchMeritRows(Paper $paper, int $studentId): array
    {
        // A student may reach a public paper without belonging to its batch;
        // that must not expose the enrolled students' named merit list.
        if ($paper->batch_id !== null && !DB::table('batch_student')
            ->where('batch_id', $paper->batch_id)
            ->where('student_id', $studentId)
            ->exists()) {
            return [];
        }

        $papersQuery = DB::table('papers')
            ->where('id', $paper->id);

        if ($paper->batch_id !== null && $paper->course_id !== null) {
            $papersQuery = DB::table('papers')
                ->where('course_id', $paper->course_id)
                ->where('batch_id', $paper->batch_id)
                ->where('name', $paper->name);
        }

        // Never include another department's unreleased paper.
        $eligiblePapers = $papersQuery->get()->filter(function ($p) {
            return $p->result_view === 'Yes' ||
                ($p->result_at !== null && Carbon::parse($p->result_at)->lessThanOrEqualTo(now()));
        })->keyBy('id');

        if ($eligiblePapers->isEmpty()) {
            return [];
        }

        $paperIds = $eligiblePapers->keys()->all();
        $questionCounts = DB::table('paper_question')
            ->whereIn('paper_id', $paperIds)
            ->select('paper_id')
            ->selectRaw('COUNT(DISTINCT question_id) as question_count')
            ->groupBy('paper_id')
            ->pluck('question_count', 'paper_id');

        $departmentNames = DB::table('departments')->pluck('name', 'id');
        // On generic papers, select the department belonging to this course.
        $studentDepartments = DB::table('department_student as ds')
            ->join('departments as d', 'd.id', '=', 'ds.department_id')
            ->where(function ($q) use ($paper) {
                $q->where('d.course_id', $paper->course_id);
                if ($paper->course_id === null) {
                    $q->orWhereNull('d.course_id');
                }
            })
            ->select('ds.student_id', 'd.name')
            ->orderBy('ds.department_id')
            ->get()->groupBy('student_id');

        $examQuery = DB::table('exams as e')
            ->join('students as s', 's.id', '=', 'e.student_id')
            ->whereIn('e.paper_id', $paperIds)
            ->where('e.status', 'Completed')
            ->whereNotNull('e.mark')
            ->select('e.id', 'e.student_id', 'e.paper_id', 'e.mark',
                's.name', 's.reg_id');

        if ($paper->batch_id !== null) {
            $examQuery->whereExists(function ($q) use ($paper) {
                $q->selectRaw('1')->from('batch_student as bs')
                    ->whereColumn('bs.student_id', 'e.student_id')
                    ->where('bs.batch_id', $paper->batch_id);
            });
        }

        $best = [];
        foreach ($examQuery->get() as $candidate) {
            $p = $eligiblePapers->get($candidate->paper_id);
            if (!$p) continue;
            $maximum = (float) ($questionCounts[$p->id] ?? 0)
                * (float) ($p->mark ?? 0);
            if ($maximum <= 0) continue;

            $earned = (float) $candidate->mark;
            $percent = round($earned * 100 / $maximum, 3);
            $configured = $p->pass_mark ?? null; // Optional future column.
            $passMark = is_numeric($configured) ? (float) $configured : $maximum * .75;
            $department = $p->department_id
                ? ($departmentNames[$p->department_id] ?? '—')
                : ($studentDepartments->get($candidate->student_id)?->first()?->name ?? 'সাধারণ');
            $row = [
                'student_id' => (int) $candidate->student_id,
                'name' => (string) $candidate->name,
                'roll' => trim((string) ($candidate->reg_id ?: ('#' . $candidate->student_id))),
                'department' => (string) $department,
                'mark' => round($earned, 2),
                'maximum' => round($maximum, 2),
                'percent' => $percent,
                'passed' => $earned >= $passMark,
                'is_mine' => (int) $candidate->student_id === $studentId,
            ];
            $sid = (int) $candidate->student_id;
            if (!isset($best[$sid]) ||
                $row['percent'] > $best[$sid]['percent'] ||
                ($row['percent'] === $best[$sid]['percent'] &&
                    $row['mark'] > $best[$sid]['mark'])) {
                $best[$sid] = $row;
            }
        }

        $rows = array_values($best);
        usort($rows, fn ($a, $b) =>
            ($b['percent'] <=> $a['percent'])
            ?: ($b['mark'] <=> $a['mark'])
            ?: ($a['student_id'] <=> $b['student_id'])
        );

        // Competition rank: tied percentage gives the same rank.
        $previous = null;
        $previousRank = 0;
        foreach ($rows as $i => &$row) {
            if ($previous === null || abs($row['percent'] - $previous) > 0.000001) {
                $previousRank = $i + 1;
            }
            $row['rank'] = $previousRank;
            $previous = $row['percent'];
        }
        unset($row);
        return $rows;
    }

    private static function plain(?string $html): string
    {
        return trim(preg_replace('/\s+/u', ' ', html_entity_decode(
            strip_tags((string) $html), ENT_QUOTES | ENT_HTML5, 'UTF-8'
        )));
    }

    private static function optionText(object $option): string
    {
        $label = self::plain($option->item);
        if ($label !== '') return $label;
        return $option->image ? '[চিত্রভিত্তিক অপশন]' : '[অপশন]';
    }

    private static function tally(array &$stats, string $name, string $status): void
    {
        if (!isset($stats[$name])) {
            $stats[$name] = ['name' => $name, 'total' => 0,
                'answered' => 0, 'correct' => 0, 'wrong' => 0, 'unanswered' => 0];
        }
        $stats[$name]['total']++;
        if ($status === 'skipped') {
            $stats[$name]['unanswered']++;
        } else {
            $stats[$name]['answered']++;
            $stats[$name][$status]++;
        }
    }

    private static function finishGroups(array $groups, float $mark, float $minus, float $passRatio): array
    {
        $rows = array_values($groups);
        foreach ($rows as &$row) {
            $row['score'] = round($row['correct'] * $mark - $row['wrong'] * $minus, 2);
            $row['accuracy'] = $row['total'] > 0
                ? round($row['correct'] / $row['total'] * 100, 1) : 0;
            $row['max_marks'] = round($row['total'] * $mark, 2);
            $row['pass_mark'] = round($row['max_marks'] * $passRatio, 2);
            $row['score_percent'] = $row['max_marks'] > 0
                ? round($row['score'] / $row['max_marks'] * 100, 1)
                : null;
            // Do not label categories with zero possible marks as Pass or Fail.
            $row['passed'] = $row['max_marks'] > 0
                ? ($row['score'] + 0.000001 >= $row['pass_mark'])
                : null;
        }
        unset($row);
        return $rows;
    }
}
