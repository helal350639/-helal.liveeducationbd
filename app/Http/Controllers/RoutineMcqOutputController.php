<?php
namespace App\Http\Controllers;

use App\Models\RoutineItemRow;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RoutineMcqOutputController extends Controller
{
    public function __construct() { $this->middleware('auth'); }

    public function preview(int $id)
    {
        $item = RoutineItemRow::findOrFail($id);
        $mode = request()->query('mode', 'question');
        abort_unless(in_array($mode, ['question', 'pdf'], true), 404);
        $selections = DB::table('routine_mcq_selections')
            ->where('routine_item_row_id', $id)->orderBy('id')->get();
        $questions = collect();
        $seen = [];
        $sources = [];
        foreach ($selections as $s) {
            $level = $s->chapter_id ? 'chapter' : ($s->subject_id ? 'subject' : 'category');
            $entity = $s->chapter_id ?: ($s->subject_id ?: $s->category_id);
            $ids = DB::table('exam_mcq_question_links as l')
                ->join('questions as q', 'q.id', '=', 'l.question_id')
                ->where('l.category_id', $s->category_id)
                ->where('l.level', $level)->where('l.entity_id', $entity)
                ->where('q.type', 'MCQ')
                ->distinct()->orderBy('q.id')->pluck('q.id')->all();
            $from = max(1, (int)($s->range_from ?? 1));
            $to = $s->range_to !== null ? (int)$s->range_to : count($ids);
            $chosen = array_slice($ids, $from - 1, max(0, $to - $from + 1));
            foreach ($chosen as $qid) { if (!isset($seen[$qid])) { $seen[$qid] = true; $sources[] = $qid; } }
        }
        // Stable question ID ascending within each selected source; preserve source order.
        $records = DB::table('questions')->whereIn('id', $sources)
            ->get(['id','title'])->keyBy('id');
        $options = DB::table('mcq_items')->whereIn('question_id', $sources)
            ->orderBy('id')->get(['question_id','item','correct_answer'])->groupBy('question_id');
        foreach ($sources as $qid) {
            if (isset($records[$qid])) {
                $questions->push((object)[
                    'id'=>$qid, 'title'=>$records[$qid]->title,
                    'options'=>$options->get($qid, collect()),
                ]);
            }
        }
        return view('layouts.routines.mcq-output', compact('item','questions','mode'));
    }
}
