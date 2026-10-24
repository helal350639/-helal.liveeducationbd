<?php
namespace App\Http\Controllers\Admin\RoutineAutoQuestion;

use App\Http\Controllers\Controller;
use App\Services\RoutineAutoQuestion\RoutineQuestionPoolService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class RoutineQuestionBuilderController extends Controller
{
    private function context(int $courseId,int $batchId,int $examId): array
    {
        $course=DB::table('courses')->where('id',$courseId)->first(); abort_unless($course,404);
        $batch=DB::table('batches')->where('id',$batchId)->where('course_id',$courseId)->first(); abort_unless($batch,404);
        $exam=DB::table('routine_exams as e')->join('routines as r','r.id','=','e.routine_id')
            ->where('r.course_id',$courseId)->where('r.batch_id',$batchId)->where('e.id',$examId)
            ->select('e.id as exam_id','e.name as exam_name','r.id as routine_id','r.name as routine_name')->first(); abort_unless($exam,404);
        $departments=DB::table('batch_department as bd')->join('departments as d','d.id','=','bd.department_id')
            ->where('bd.batch_id',$batchId)->distinct()->select('d.id','d.name')->orderBy('d.name')->get();
        $settings=DB::table('routine_batch_paper_settings')->where('batch_id',$batchId)->first();
        return compact('course','batch','exam','departments','settings');
    }
    public function show(int $courseId,int $batchId,int $examId,RoutineQuestionPoolService $pool)
    {
        $ctx=$this->context($courseId,$batchId,$examId);
        $rows=$pool->rows($ctx['exam']);
        $saved=DB::table('routine_auto_question_plans')->where('exam_id',$examId)->get()->keyBy('row_key');
        $count=DB::table('routine_auto_question_plan_questions')->where('exam_id',$examId)->count();
        return view('admin.routine-auto-question.create-question',array_merge($ctx, compact('rows','saved','count')));
    }
    public function save(Request $request,int $courseId,int $batchId,int $examId,RoutineQuestionPoolService $pool)
    {
        $ctx=$this->context($courseId,$batchId,$examId);$rows=$pool->rows($ctx['exam']);
        $counts=$request->validate(['qty'=>'required|array','qty.*'=>'required|integer|min:0']);
        $wanted=$counts['qty'];$mapped=[];$common=[];$dept=[];
        foreach ($rows as $i=>$row) {
            $qty=(int)($wanted[$i]??0);
            if($qty>$row['available']) throw ValidationException::withMessages(["qty.$i"=>'Selected quantity exceeds available questions.']);
            $mapped[]=['key'=>$row['key'],'qty'=>$qty];
            // Keep the Routine selection's available pool/range, but choose its MCQs
            // in a fresh random order each time Save/Create is submitted.
            // Do not randomize the source range before it is applied by the pool service.
            $availableIds = array_values(array_unique(array_map('intval', $row['question_ids'])));
            if (count($availableIds) > 1) {
                shuffle($availableIds);
            }
            $ids = array_slice($availableIds, 0, $qty);
            if($row['scope']==='common') $common=array_merge($common,$ids);
            else { $d=$row['department_id'];$dept[$d]=array_merge($dept[$d]??[],$ids); }
        }
        $common=array_values(array_unique($common));
        $max=(int)($ctx['settings']->question_limit??100);
        foreach($ctx['departments'] as $d) {
            $total=count(array_unique(array_merge($common,$dept[$d->id]??[])));
            if($total>$max) throw ValidationException::withMessages(['qty'=>'Department '.$d->name.' would receive '.$total.' unique questions, exceeding batch limit '.$max.'.']);
        }
        // Persist the common selection once; department selections are separate.
        DB::transaction(function()use($examId,$rows,$mapped,$common,$dept){
            DB::table('routine_auto_question_plans')->where('exam_id',$examId)->delete();
            DB::table('routine_auto_question_plan_questions')->where('exam_id',$examId)->delete();
            foreach($mapped as $i=>$entry) DB::table('routine_auto_question_plans')->insert([
                'exam_id'=>$examId,'row_key'=>$entry['key'],'requested'=>$entry['qty'],
                'created_at'=>now(),'updated_at'=>now()]);
            foreach($common as $id) DB::table('routine_auto_question_plan_questions')->insert([
                'exam_id'=>$examId,'scope'=>'common','department_id'=>0,'question_id'=>$id]);
            foreach($dept as $d=>$ids) foreach(array_unique($ids) as $id) DB::table('routine_auto_question_plan_questions')->insert([
                'exam_id'=>$examId,'scope'=>'department','department_id'=>$d,'question_id'=>$id]);
        });
        return back()->with('success','Questions saved. Common questions apply to all departments.');
    }

    /** Reset only this exam's saved auto-question selections, not the question bank. */
    public function reset(Request $request, int $courseId, int $batchId, int $examId)
    {
        $this->context($courseId, $batchId, $examId); // prevents resetting another batch's exam
        DB::transaction(function () use ($examId) {
            DB::table('routine_auto_question_plan_questions')->where('exam_id', $examId)->delete();
            DB::table('routine_auto_question_plans')->where('exam_id', $examId)->delete();
        });
        return redirect()->route('admin.routine-auto-question.batch', [
            'courseId' => $courseId, 'batchId' => $batchId,
        ])->with('success', 'Saved auto questions reset for this exam. You can create them again.');
    }

    public function preview(int $courseId, int $batchId, int $examId, int $departmentId, RoutineQuestionPoolService $pool)
    {
        $ctx = $this->context($courseId, $batchId, $examId);
        $department = $ctx['departments']->firstWhere('id', $departmentId);
        abort_unless($department, 404);

        $common = DB::table('routine_auto_question_plan_questions')
            ->where('exam_id', $examId)->where('scope', 'common')->pluck('question_id')->map(fn($v) => (int)$v)->all();
        $own = DB::table('routine_auto_question_plan_questions')
            ->where('exam_id', $examId)->where('scope', 'department')
            ->where('department_id', $departmentId)->pluck('question_id')->map(fn($v) => (int)$v)->all();
        $ids = array_values(array_unique(array_merge($common, $own)));
        $questions = DB::table('questions')->whereIn('id', $ids)->get()->keyBy('id');

        // Group by the Routine's original category and subject filter, not by an unrelated bank label.
        // A question that belongs to multiple filters appears only once in this department's paper.
        $poolRows = $pool->rows($ctx['exam']);
        usort($poolRows, fn($a, $b) => (($a['scope'] ?? '') === 'common' ? 0 : 1) <=> (($b['scope'] ?? '') === 'common' ? 0 : 1));
        $groups = []; $assigned = [];
        $selected = array_fill_keys($ids, true);
        foreach ($poolRows as $row) {
            $isCommon = ($row['scope'] ?? '') === 'common';
            if (!$isCommon && (int)($row['department_id'] ?? 0) !== $departmentId) continue;
            $subject = trim((string)($row['subject'] ?? ''));
            $category = trim((string)($row['category'] ?? ''));
            $chapter = trim((string)($row['chapter'] ?? ''));
            $title = $category.($subject !== '' ? ' — '.$subject : '');
            if ($title === '') $title = $isCommon ? 'Non-Department Subject' : 'Department Subject';
            $key = ($isCommon ? 'common|' : 'department|').$title;
            if (!isset($groups[$key])) {
                $groups[$key] = ['title' => $title, 'part' => $isCommon ? 'Common' : 'Department', 'ids' => [], 'chapters' => []];
            }
            foreach ($ids as $id) {
                if (!in_array($id, array_map('intval', $row['question_ids'] ?? []), true)) continue;
                $id = (int)$id;
                if (!isset($selected[$id]) || isset($assigned[$id])) continue;
                // For overlapping Common and Department IDs: prioritise Common.
                if (!$isCommon && in_array($id, $common, true)) continue;
                $groups[$key]['ids'][] = $id;
                if ($chapter !== '') $groups[$key]['chapters'][$id] = $chapter;
                $assigned[$id] = true;
            }
        }
        // Older plans may reference an older routine range: keep saved questions visible.
        $unmatchedCommon = []; $unmatchedOwn = [];
        foreach ($ids as $id) {
            if (isset($assigned[$id])) continue;
            if (in_array($id, $common, true)) $unmatchedCommon[] = $id;
            else $unmatchedOwn[] = $id;
        }
        if ($unmatchedCommon) $groups['fallback_common'] = ['title'=>'Previously Saved Common MCQs', 'part'=>'Common', 'ids'=>$unmatchedCommon, 'chapters'=>[]];
        if ($unmatchedOwn) $groups['fallback_dept'] = ['title'=>'Previously Saved Department MCQs', 'part'=>'Department', 'ids'=>$unmatchedOwn, 'chapters'=>[]];
        $groups = array_values(array_filter($groups, fn($g) => count($g['ids']) > 0));

        // Support both question-table columns and common option-table layouts without changing DB schema.
        $optionSets = []; $answers = []; $imagePaths = [];
        $optionCandidates = [
            ['option_a','option_b','option_c','option_d'], ['option_1','option_2','option_3','option_4'],
            ['option1','option2','option3','option4'], ['a','b','c','d'],
            ['answer_a','answer_b','answer_c','answer_d'], ['choice_a','choice_b','choice_c','choice_d'],
            ['optionA','optionB','optionC','optionD'],
        ];
        $answerCandidates = ['correct_answer','correct_option','right_answer','answer','correct','correct_ans','ans'];
        $imageCandidates = ['image','question_image','image_path','photo'];
        foreach ($questions as $q) {
            $a = (array)$q; $id = (int)$q->id;
            foreach ($optionCandidates as $cols) {
                if (!array_key_exists($cols[0], $a)) continue;
                $options = [];
                foreach ($cols as $i => $col) {
                    if (isset($a[$col]) && trim((string)$a[$col]) !== '') $options[chr(65+$i)] = (string)$a[$col];
                }
                if ($options) { $optionSets[$id] = $options; break; }
            }
            foreach ($answerCandidates as $col) {
                if (isset($a[$col]) && trim((string)$a[$col]) !== '') { $answers[$id] = (string)$a[$col]; break; }
            }
            foreach ($imageCandidates as $col) {
                if (isset($a[$col]) && trim((string)$a[$col]) !== '') { $imagePaths[$id] = (string)$a[$col]; break; }
            }
        }
        foreach (['question_options', 'options', 'question_answers'] as $table) {
            if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'question_id') || !$ids) continue;
            $cols = Schema::getColumnListing($table);
            $valueCol = collect(['option','option_text','text','title','name','value','answer'])->first(fn($c)=>in_array($c,$cols,true));
            if (!$valueCol) continue;
            $labelCol = collect(['option_key','option_label','label','key','serial','number','sort_order','position'])->first(fn($c)=>in_array($c,$cols,true));
            $correctCol = collect(['is_correct','correct','is_right'])->first(fn($c)=>in_array($c,$cols,true));
            $query = DB::table($table)->whereIn('question_id', $ids);
            if (in_array('id', $cols, true)) $query->orderBy('id');
            foreach ($query->get() as $opt) {
                $id = (int)$opt->question_id;
                if (isset($optionSets[$id]) && count($optionSets[$id]) >= 4) continue;
                $existing = $optionSets[$id] ?? [];
                $label = $labelCol ? strtoupper(trim((string)$opt->{$labelCol})) : '';
                if (ctype_digit($label)) $label = chr(65 + max(0, min(3, (int)$label - 1)));
                if (!in_array($label,['A','B','C','D'],true)) $label = chr(65 + count($existing));
                if (!in_array($label,['A','B','C','D'],true)) continue;
                $existing[$label] = (string)$opt->{$valueCol};
                $optionSets[$id] = $existing;
                if ($correctCol && $opt->{$correctCol}) $answers[$id] = $label;
            }
            // Prefer first matching option table to avoid mixing unrelated structures.
            if ($optionSets) break;
        }
        // LEBD-04 original question bank stores MCQ choices in mcq_items.
        // Fill missing options/answers using that real table, without altering records.
        if ($ids && Schema::hasTable('mcq_items')) {
            $choiceQuery = DB::table('mcq_items')->whereIn('question_id', $ids)->orderBy('id');
            foreach ($choiceQuery->get() as $choice) {
                $questionId = (int)$choice->question_id;
                $position = count($optionSets[$questionId] ?? []);
                if ($position >= 5) continue;
                $letter = chr(65 + $position);
                $optionSets[$questionId][$letter] = (string)($choice->item ?? '');
                if (!empty($choice->correct_answer)) $answers[$questionId] = $letter;
            }
        }
        $duplicateCount = count(array_intersect(array_unique($common), array_unique($own)));
        return view('admin.routine-auto-question.generated-department-preview',
            array_merge($ctx, compact('department','common','own','ids','questions','groups','optionSets','answers','imagePaths','duplicateCount')));
    }

    public function extra(int $courseId,int $batchId,int $examId,string $source)
    {
        abort_unless(in_array($source,['syllabus','database'],true),404);
        $ctx=$this->context($courseId,$batchId,$examId);
        return view('admin.routine-auto-question.extra-source',array_merge($ctx,compact('source')));
    }
}
