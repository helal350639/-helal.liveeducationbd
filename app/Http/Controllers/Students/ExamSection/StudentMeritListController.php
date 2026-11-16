<?php

namespace App\Http\Controllers\Students\ExamSection;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class StudentMeritListController extends Controller
{
    public function index(Request $request, int $category, ?int $item = null)
    {
        $student = Auth::guard('student')->user();
        abort_unless($student, 403);

        foreach ([
            'lebd_exam_section_categories',
            'lebd_exam_section_subcategories',
            'lebd_exam_section_routines',
            'lebd_exam_section_routine_items',
            'lebd_exam_section_student_attempts',
            'lebd_exam_section_generated_questions',
            'students',
        ] as $table) {
            abort_unless(Schema::hasTable($table), 503, 'Merit List data is not available yet.');
        }

        // The Merit List is shown for the category selected in the Exam Section.
        $categoryId = $category;
        $categoryRow = DB::table('lebd_exam_section_categories')
            ->where('id', $categoryId)
            ->whereRaw('LOWER(TRIM(status)) = ?', ['active'])
            ->first(['id', 'name']);
        abort_unless($categoryRow, 404);

        $subcategoryId = $request->filled('subcategory_id') ? (int) $request->query('subcategory_id') : null;
        $subcategoryRow = null;
        if ($subcategoryId) {
            $subcategoryRow = DB::table('lebd_exam_section_subcategories')
                ->where('id', $subcategoryId)
                ->where('category_id', $categoryId)
                ->whereRaw('LOWER(TRIM(status)) = ?', ['active'])
                ->first(['id', 'category_id', 'name']);
            abort_unless($subcategoryRow, 404);
        }

        // First show the published exams available in this exact Category / Sub-category.
        // A selected item then opens the leaderboard for that exam only.
        $examCards = DB::table('lebd_exam_section_routine_items as i')
            ->join('lebd_exam_section_routines as r', 'r.id', '=', 'i.routine_id')
            ->join('lebd_exam_section_categories as c', 'c.id', '=', 'r.category_id')
            ->leftJoin('lebd_exam_section_subcategories as s', 's.id', '=', 'r.subcategory_id')
            ->where('r.category_id', $categoryId)
            ->when($subcategoryId, fn ($q) => $q->where('r.subcategory_id', $subcategoryId))
            ->whereRaw('LOWER(TRIM(i.type)) = ?', ['exam'])
            ->whereRaw('LOWER(TRIM(r.status)) = ?', ['published'])
            ->whereRaw('LOWER(TRIM(c.status)) = ?', ['active'])
            ->where(function ($q) {
                $q->whereNull('r.subcategory_id')
                    ->orWhereRaw('LOWER(TRIM(s.status)) = ?', ['active']);
            })
            ->select([
                'i.id', 'i.exam_name', 'i.exam_end_at', 'i.routine_id',
                'r.name as routine_name', 'r.subcategory_id',
            ])
            ->orderByDesc('i.exam_end_at')->orderByDesc('i.id')
            ->get();

        if (!$item) {
            return view('student.exam-section.merit-list-exams', [
                'category' => $categoryRow,
                'subcategory' => $subcategoryRow,
                'categoryId' => $categoryId,
                'subcategoryId' => $subcategoryId,
                'scopeTitle' => $subcategoryRow ? $subcategoryRow->name : $categoryRow->name,
                'examCards' => $examCards,
            ]);
        }

        $selectedExam = $examCards->firstWhere('id', $item);
        abort_unless($selectedExam, 404);

        // A category-wide link can open an exam belonging to a subcategory.
        // Extra entries must match the SELECTED EXAM's scope, not the optional URL filter.
        $extraSubcategoryId = $selectedExam->subcategory_id !== null
            ? (int) $selectedExam->subcategory_id : null;
        $extraSubcategoryName = $extraSubcategoryId
            ? (string) DB::table('lebd_exam_section_subcategories')->where('id', $extraSubcategoryId)->value('name')
            : '';

        $now = Carbon::now('Asia/Dhaka');

        $query = DB::table('lebd_exam_section_student_attempts as a')
            ->join('lebd_exam_section_routine_items as i', 'i.id', '=', 'a.routine_item_id')
            ->join('lebd_exam_section_routines as r', 'r.id', '=', 'i.routine_id')
            ->join('lebd_exam_section_categories as c', 'c.id', '=', 'r.category_id')
            ->leftJoin('lebd_exam_section_subcategories as s', 's.id', '=', 'r.subcategory_id')
            ->join('students as st', 'st.id', '=', 'a.student_id')
            ->where('a.status', 'submitted')
            ->where('r.category_id', $categoryId)
            ->where('i.id', $item)
            ->when($subcategoryId, fn ($q) => $q->where('r.subcategory_id', $subcategoryId))
            ->whereRaw('LOWER(TRIM(i.type)) = ?', ['exam'])
            ->whereRaw('LOWER(TRIM(r.status)) = ?', ['published'])
            ->whereRaw('LOWER(TRIM(c.status)) = ?', ['active'])
            ->where(function ($q) {
                $q->whereNull('r.subcategory_id')
                    ->orWhereRaw('LOWER(TRIM(s.status)) = ?', ['active']);
            })
            ->select([
                'a.id as attempt_id', 'a.student_id', 'a.started_at', 'a.submitted_at',
                'a.score', 'a.correct_count', 'a.wrong_count',
                'i.id as item_id', 'i.exam_name', 'i.exam_end_at', 'i.routine_id',
                'r.category_id', 'r.subcategory_id', 'r.name as routine_name',
                'c.name as category_name', 's.name as subcategory_name',
                'st.name as student_name',
            ])
            ->orderBy('a.submitted_at')
            ->get();

        $settingsByScope = [];
        $attemptRows = [];
        foreach ($query as $attempt) {
            $scopeKey = $attempt->subcategory_id
                ? 'sub:' . (int) $attempt->subcategory_id
                : 'cat:' . (int) $attempt->category_id;

            if (!array_key_exists($scopeKey, $settingsByScope)) {
                $settingsJson = Schema::hasTable('lebd_exam_section_paper_settings')
                    ? DB::table('lebd_exam_section_paper_settings')->where('scope_key', $scopeKey)->value('settings_json')
                    : null;
                $settingsByScope[$scopeKey] = $settingsJson ? (json_decode($settingsJson, true) ?: []) : [];
            }
            $settings = $settingsByScope[$scopeKey];

            $showImmediately = in_array(
                strtolower((string) ($settings['student_view_result'] ?? 'no')),
                ['yes', '1', 'true'],
                true
            );
            $examEnded = !empty($attempt->exam_end_at)
                && Carbon::parse($attempt->exam_end_at, 'Asia/Dhaka')->lte($now);
            if (!$showImmediately && !$examEnded) {
                continue;
            }

            $questionIds = DB::table('lebd_exam_section_generated_questions')
                ->where('routine_id', $attempt->routine_id)
                ->where('exam_name', $attempt->exam_name)
                ->orderBy('position')->orderBy('id')
                ->pluck('question_id')->map(fn ($id) => (int) $id)->unique()->values()->all();

            $questionLimit = max(1, min(1000, (int) ($settings['question_limit'] ?? 100)));
            $questionCount = count(array_slice($questionIds, 0, $questionLimit));
            $positiveMark = max(0, (float) ($settings['correct_mark'] ?? 1));
            $possibleMark = $questionCount * $positiveMark;
            $score = (float) $attempt->score;
            $percent = $possibleMark > 0 ? ($score / $possibleMark) * 100 : 0;
            $answered = (int) $attempt->correct_count + (int) $attempt->wrong_count;
            $accuracy = $answered > 0 ? ((int) $attempt->correct_count / $answered) * 100 : 0;

            $durationSeconds = 0;
            if ($attempt->started_at && $attempt->submitted_at) {
                $durationSeconds = max(0, Carbon::parse($attempt->started_at, 'Asia/Dhaka')
                    ->diffInSeconds(Carbon::parse($attempt->submitted_at, 'Asia/Dhaka')));
            }

            $attemptRows[] = [
                'student_id' => (int) $attempt->student_id,
                'student_name' => (string) $attempt->student_name,
                'category_name' => (string) $attempt->category_name,
                'subcategory_name' => (string) ($attempt->subcategory_name ?? ''),
                'exam_name' => (string) $attempt->exam_name,
                'routine_name' => (string) $attempt->routine_name,
                'item_id' => (int) $attempt->item_id,
                'score' => $score,
                'possible' => $possibleMark,
                'percent' => max(0, min(100, $percent)),
                'correct' => (int) $attempt->correct_count,
                'wrong' => (int) $attempt->wrong_count,
                'accuracy' => $accuracy,
                'duration' => $durationSeconds,
                'submitted_at' => $attempt->submitted_at,
                'is_extra_student' => false,
                'extra_group_name' => null,
                'marks_entered' => true,
                'mark_source' => 'exam',
            ];
        }

        // Include every marked or unmarked Extra Student assigned to a group in this
        // exact Category/Sub-category in this exam's ORIGINAL merit list. Extra students
        // are not separate group cards and are not required to submit a real exam attempt.
        $extraTablesReady = Schema::hasTable('lebd_exam_extra_student_groups')
            && Schema::hasTable('lebd_exam_extra_student_group_members')
            && Schema::hasTable('lebd_exam_extra_student_exam_marks')
            && Schema::hasTable('lebd_exam_section_extra_student_picks')
            && Schema::hasTable('lebd_extra_students');

        $extraScopeKey = $extraSubcategoryId ? 'sub:' . $extraSubcategoryId : 'cat:' . (int) $categoryId;
        $extraResultSettings = $settingsByScope[$extraScopeKey] ?? null;
        if ($extraResultSettings === null) {
            $extraSettingsJson = Schema::hasTable('lebd_exam_section_paper_settings')
                ? DB::table('lebd_exam_section_paper_settings')->where('scope_key', $extraScopeKey)->value('settings_json')
                : null;
            $extraResultSettings = $extraSettingsJson ? (json_decode($extraSettingsJson, true) ?: []) : [];
        }
        $extraImmediate = in_array(strtolower((string) ($extraResultSettings['student_view_result'] ?? 'no')), ['yes', '1', 'true'], true);
        $extraExamEnded = !empty($selectedExam->exam_end_at)
            && Carbon::parse($selectedExam->exam_end_at, 'Asia/Dhaka')->lte($now);
        $extraResultVisible = $extraImmediate || $extraExamEnded;

        if ($extraTablesReady && $extraResultVisible) {
            $extraQuery = DB::table('lebd_exam_extra_student_group_members as m')
                ->join('lebd_exam_extra_student_groups as g', 'g.id', '=', 'm.group_id')
                ->join('lebd_exam_section_extra_student_picks as p', 'p.id', '=', 'm.pick_id')
                ->join('lebd_extra_students as es', 'es.id', '=', 'p.extra_student_id')
                ->leftJoin('lebd_exam_extra_student_exam_marks as gm', function ($join) use ($selectedExam) {
                    $join->on('gm.group_id', '=', 'g.id')->on('gm.pick_id', '=', 'p.id')
                        ->where('gm.routine_item_id', '=', $selectedExam->id);
                })
                ->where('g.category_id', $categoryId)
                ->where('p.category_id', $categoryId)
                ->when($extraSubcategoryId !== null, function ($q) use ($extraSubcategoryId) {
                    $q->where('g.subcategory_id', $extraSubcategoryId)->where('p.subcategory_id', $extraSubcategoryId);
                }, function ($q) {
                    $q->whereNull('g.subcategory_id')->whereNull('p.subcategory_id');
                })
                ->select([
                    'p.id as pick_id', 'es.id as extra_student_id', 'es.name as student_name',
                    'g.name as extra_group_name', 'g.mark_to', 'gm.marks',
                ])
                ->orderBy('g.sort_order')->orderBy('m.id');

            $extraMembers = $extraQuery->get();

            foreach ($extraMembers as $member) {
                // Group Mark Range (e.g. 80–90) is stored as the Extra Student's
                // own mark. Rank comparison uses its percentage, so it is comparable
                // with regular students even when the exam total differs (e.g. 30 marks).
                $markScale = max(100.0, (float) ($member->mark_to ?? 100));
                $hasMark = $member->marks !== null;
                $rawMark = $hasMark ? max(0.0, (float) $member->marks) : 0.0;
                $percent = max(0.0, min(100.0, ($rawMark / $markScale) * 100.0));

                $attemptRows[] = [
                    // Negative IDs prevent collisions with real student IDs.
                    'student_id' => -1 * (int) $member->extra_student_id,
                    'student_name' => (string) $member->student_name,
                    'category_name' => (string) $categoryRow->name,
                    'subcategory_name' => $extraSubcategoryName,
                    'exam_name' => (string) $selectedExam->exam_name,
                    'routine_name' => (string) $selectedExam->routine_name,
                    'item_id' => (int) $selectedExam->id,
                    'score' => $rawMark,
                    'possible' => $markScale,
                    'percent' => $percent,
                    'correct' => 0,
                    'wrong' => 0,
                    'accuracy' => 0,
                    'duration' => 0,
                    'submitted_at' => null,
                    'is_extra_student' => true,
                    'extra_group_name' => (string) $member->extra_group_name,
                    'marks_entered' => $hasMark,
                    'mark_source' => 'simulation',
                ];
            }
        }

        // With "All Exams", each student is ranked by their average percentage
        // across the published exams they actually submitted in this scope.
        $rankRows = collect($attemptRows)
            ->groupBy('student_id')
            ->map(function ($studentAttempts) {
                $first = $studentAttempts->first();
                $totalPossible = (float) $studentAttempts->sum('possible');
                $totalScore = (float) $studentAttempts->sum('score');
                $averagePercent = (float) $studentAttempts->avg('percent');
                $correct = (int) $studentAttempts->sum('correct');
                $wrong = (int) $studentAttempts->sum('wrong');
                $answered = $correct + $wrong;

                return [
                    'student_id' => $first['student_id'],
                    'student_name' => $first['student_name'],
                    'category_name' => $first['category_name'],
                    'subcategory_name' => $first['subcategory_name'],
                    'exam_name' => $studentAttempts->count() === 1 ? $first['exam_name'] : $studentAttempts->count() . ' Exams',
                    'score' => $totalScore,
                    'possible' => $totalPossible,
                    'percent' => $averagePercent,
                    'accuracy' => $answered > 0 ? ($correct / $answered) * 100 : 0,
                    'duration' => (int) round($studentAttempts->avg('duration')),
                    'exam_count' => $studentAttempts->count(),
                    'latest_at' => $studentAttempts->max('submitted_at'),
                    'is_extra_student' => (bool) ($first['is_extra_student'] ?? false),
                    'extra_group_name' => $first['extra_group_name'] ?? null,
                    'marks_entered' => (bool) ($first['marks_entered'] ?? true),
                    'mark_source' => $first['mark_source'] ?? 'exam',
                ];
            })
            ->sort(function ($a, $b) {
                // Entries without a recorded mark never outrank marked students.
                $markCompare = (int) $b['marks_entered'] <=> (int) $a['marks_entered'];
                if ($markCompare !== 0) return $markCompare;
                $scoreCompare = $b['percent'] <=> $a['percent'];
                if ($scoreCompare !== 0) return $scoreCompare;
                $accuracyCompare = $b['accuracy'] <=> $a['accuracy'];
                if ($accuracyCompare !== 0) return $accuracyCompare;
                $timeCompare = strcmp((string) $a['latest_at'], (string) $b['latest_at']);
                if ($timeCompare !== 0) return $timeCompare;
                return (int) $a['student_id'] <=> (int) $b['student_id'];
            })
            ->values()
            ->map(function ($row, $index) {
                $row['rank'] = $index + 1;
                return $row;
            });

        $totalParticipants = $rankRows->count();
        $currentPosition = $rankRows->firstWhere('student_id', (int) $student->id);
        $yourRank = $currentPosition['rank'] ?? null;
        $topRows = $rankRows->filter(fn ($row) => $row['marks_entered'])->take(3);
        // Do not silently hide positions after 50: the combined list includes everyone.
        $visibleRows = $rankRows;
        $scoredRows = $rankRows->filter(fn ($row) => $row['marks_entered']);
        $highestPercent = $scoredRows->isNotEmpty() ? (float) $scoredRows->max('percent') : 0;
        $averagePercent = $scoredRows->isNotEmpty() ? (float) $scoredRows->avg('percent') : 0;
        $percentile = ($yourRank && $totalParticipants > 1)
            ? round((($totalParticipants - $yourRank) / ($totalParticipants - 1)) * 100, 2)
            : ($yourRank ? 100 : 0);

        $scopeTitle = $subcategoryRow ? $subcategoryRow->name : $categoryRow->name;

        return view('student.exam-section.merit-list', [
            'student' => $student,
            'category' => $categoryRow,
            'subcategory' => $subcategoryRow,
            'categoryId' => $categoryId,
            'subcategoryId' => $subcategoryId,
            'scopeTitle' => $scopeTitle,
            'selectedExam' => $selectedExam,
            'totalParticipants' => $totalParticipants,
            'yourRank' => $yourRank,
            'currentPosition' => $currentPosition,
            'topRows' => $topRows,
            'visibleRows' => $visibleRows,
            'highestPercent' => $highestPercent,
            'averagePercent' => $averagePercent,
            'percentile' => $percentile,
            'isExtraGroup' => false,
        ]);
    }
}
