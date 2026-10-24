<?php

namespace App\Http\Controllers;

use App\Models\Routine;
use App\Models\RoutineItemRow;
use App\Services\RoutineDerivedDateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RoutineDerivedExamController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    private function examType(string $type): void
    {
        abort_unless(in_array($type, ['revision', 'model'], true), 404);
    }

    private function isDerivedName(string $name): bool
    {
        return (bool) preg_match('/^(revision|revition|model)/i', trim($name));
    }

    public function create(Request $request, int $id, string $type)
    {
        $this->examType($type);
        $routine = Routine::findOrFail($id);
        $subjects = $routine->subjects()->get();
        $departments = $routine->departments()->get();
        $allNames = $routine->exams()->orderBy('sort_order')->orderBy('id')->get();

        $typeNames = $allNames->filter(function ($exam) use ($type) {
            return $type === 'revision'
                ? (bool) preg_match('/^(revision|revition)/i', trim($exam->name))
                : (bool) preg_match('/^model/i', trim($exam->name));
        })->values();

        $mainNames = $allNames->reject(function ($exam) {
            return $this->isDerivedName($exam->name);
        })->pluck('name')->all();

        $used = DB::table('routine_derived_exam_items')
            ->where('routine_id', $id)->pluck('exam_name')->all();
        $typeNames = $typeNames->reject(function ($exam) use ($used) {
            return in_array($exam->name, $used, true);
        })->values();

        $sources = RoutineItemRow::where('routine_id', $id)
            ->whereIn('paper_name', $mainNames)
            ->orderBy('id')->get(['id', 'paper_name', 'exam_end_at']);

        return view('layouts.routines.derived-add-item', compact(
            'routine', 'type', 'subjects', 'departments', 'typeNames', 'sources'
        ));
    }

    public function store(Request $request, int $id, string $type)
    {
        $this->examType($type);
        $routine = Routine::findOrFail($id);

        // Do not use "distinct" here: the Select2/multi-select form can submit
        // the same Main Exam twice. Deduplication is handled per column below.
        $data = $request->validate([
            'exam_name' => 'required|string|max:255',
            'exam_at' => 'required|date',
            'exam_end_at' => 'nullable|date|after_or_equal:exam_at',
            'source' => 'required|array',
            'source.*' => 'array',
            'source.*.*' => 'required|integer|min:1',
        ]);

        $allowedName = $routine->exams()->where('name', $data['exam_name'])->exists();
        $nameMatches = $type === 'revision'
            ? (bool) preg_match('/^(revision|revition)/i', trim($data['exam_name']))
            : (bool) preg_match('/^model/i', trim($data['exam_name']));

        if (!$allowedName || !$nameMatches) {
            throw ValidationException::withMessages([
                'exam_name' => 'Choose a saved '.ucfirst($type).' exam name.',
            ]);
        }

        $columnMap = [
            'subject' => $routine->subjects()->pluck('subjects.id')->map(fn ($v) => (int) $v)->all(),
            'department' => $routine->departments()->pluck('departments.id')->map(fn ($v) => (int) $v)->all(),
        ];

        $linked = [];
        $uniqueKeys = [];
        foreach ((array) $data['source'] as $columnKey => $ids) {
            if (!preg_match('/^(subject|department)_(\d+)$/', (string) $columnKey, $matches)) {
                throw ValidationException::withMessages(['source' => 'Invalid Routine column.']);
            }

            $kind = $matches[1];
            $columnId = (int) $matches[2];
            if (!in_array($columnId, $columnMap[$kind], true)) {
                throw ValidationException::withMessages(['source' => 'Column not in Routine.']);
            }

            foreach ((array) $ids as $sourceId) {
                $sourceId = (int) $sourceId;
                $key = $kind.':'.$columnId.':'.$sourceId;
                if (isset($uniqueKeys[$key])) {
                    continue;
                }
                $uniqueKeys[$key] = true;
                $linked[] = [
                    'column_kind' => $kind,
                    'column_id' => $columnId,
                    'source_item_id' => $sourceId,
                ];
            }
        }

        if (!$linked) {
            throw ValidationException::withMessages(['source' => 'Select at least one Main Exam.']);
        }

        $ids = array_values(array_unique(array_column($linked, 'source_item_id')));
        $mainNames = $routine->exams()->get()->reject(function ($exam) {
            return $this->isDerivedName($exam->name);
        })->pluck('name')->all();

        $valid = RoutineItemRow::where('routine_id', $id)
            ->whereIn('paper_name', $mainNames)
            ->whereIn('id', $ids)->pluck('id')->map(fn ($v) => (int) $v)->all();

        if (count($valid) !== count($ids)) {
            throw ValidationException::withMessages([
                'source' => 'Select Main Exams from this Routine only.',
            ]);
        }

        DB::transaction(function () use ($id, $type, $data, $linked) {
            // Prevent duplicate derived exams for a given Routine.
            if (DB::table('routine_derived_exam_items')
                ->where('routine_id', $id)->where('exam_name', $data['exam_name'])->exists()) {
                throw ValidationException::withMessages([
                    'exam_name' => 'This Revision/Model Exam has already been added.',
                ]);
            }

            $itemId = DB::table('routine_derived_exam_items')->insertGetId([
                'routine_id' => $id,
                'exam_type' => $type,
                'exam_name' => $data['exam_name'],
                'exam_at' => $data['exam_at'],
                'exam_end_at' => $data['exam_end_at'] ?? null,
                'create_pdf_at' => null,
                'question_create_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            foreach ($linked as $link) {
                DB::table('routine_derived_exam_sources')->insert($link + [
                    'derived_item_id' => $itemId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            // Recalculate this and every later derived exam after links are saved.
            $this->recalculateDerivedDates($id);
        });

        return redirect()->route('routine.item.show', ['id' => $id])
            ->with('success', ucfirst($type).' Exam saved with live source Exam links.');
    }

    public function index(int $id)
    {
        $routine = Routine::findOrFail($id);
        $items = DB::table('routine_derived_exam_items')
            ->where('routine_id', $id)->orderBy('id')->get();
        $links = DB::table('routine_derived_exam_sources as s')
            ->join('routine_item_rows as i', 'i.id', '=', 's.source_item_id')
            ->whereIn('s.derived_item_id', $items->pluck('id')->all())
            ->select('s.derived_item_id', 's.column_kind', 's.column_id', 'i.paper_name', 'i.id as source_item_id')
            ->get()->groupBy('derived_item_id');

        return view('layouts.routines.derived-list', compact('routine', 'items', 'links'));
    }

    private function derivedItem(int $id)
    {
        return DB::table('routine_derived_exam_items')->where('id', $id)->first() ?? abort(404);
    }

    public function edit(int $id)
    {
        $item = $this->derivedItem($id);
        $routine = Routine::findOrFail($item->routine_id);
        $type = $item->exam_type;
        $subjects = $routine->subjects()->get();
        $departments = $routine->departments()->get();
        $allNames = $routine->exams()->orderBy('sort_order')->get();
        $mainNames = $allNames->reject(fn ($e) => preg_match('/^(revision|revition|model)/i', trim($e->name)))->pluck('name')->all();
        $sources = RoutineItemRow::where('routine_id', $routine->id)->whereIn('paper_name', $mainNames)->orderBy('id')->get(['id','paper_name']);
        $selected = DB::table('routine_derived_exam_sources')->where('derived_item_id', $id)->get()
            ->groupBy(fn ($link) => $link->column_kind.'_'.$link->column_id)
            ->map(fn ($links) => $links->pluck('source_item_id')->map(fn ($v) => (int) $v)->all())->all();
        return view('layouts.routines.derived-edit-item', compact('item','routine','type','subjects','departments','sources','selected'));
    }

    private function validateSources(Request $request, int $routineId): array
    {
        $routine = Routine::findOrFail($routineId);
        $columns = [
            'subject' => $routine->subjects()->pluck('subjects.id')->map(fn ($n) => (int)$n)->all(),
            'department' => $routine->departments()->pluck('departments.id')->map(fn ($n) => (int)$n)->all(),
        ];
        $mainNames = $routine->exams()->get()->reject(fn ($e) => preg_match('/^(revision|revition|model)/i', trim($e->name)))->pluck('name')->all();
        $validIds = RoutineItemRow::where('routine_id', $routineId)->whereIn('paper_name', $mainNames)->pluck('id')->map(fn ($n) => (int)$n)->all();
        $links = [];
        foreach ((array)$request->input('source', []) as $key => $ids) {
            if (!preg_match('/^(subject|department)_(\d+)$/', (string)$key, $match)) {
                throw ValidationException::withMessages(['source' => 'Invalid routine column.']);
            }
            $kind=$match[1]; $columnId=(int)$match[2];
            if (!in_array($columnId, $columns[$kind], true)) throw ValidationException::withMessages(['source' => 'Column does not belong to this Routine.']);
            foreach (array_unique(array_map('intval', (array)$ids)) as $sourceId) {
                if (!in_array($sourceId, $validIds, true)) throw ValidationException::withMessages(['source' => 'Invalid Main Exam source.']);
                $links[] = ['column_kind'=>$kind, 'column_id'=>$columnId,'source_item_id'=>$sourceId];
            }
        }
        if (!$links) throw ValidationException::withMessages(['source'=>'Select at least one Main Exam.']);
        return $links;
    }

    public function updateItem(Request $request, int $id)
    {
        $item = $this->derivedItem($id);
        $data = $request->validate([
            'exam_at'=>'required|date',
            'exam_end_at'=>'nullable|date|after_or_equal:exam_at',
            'source'=>'required|array', 'source.*'=>'array', 'source.*.*'=>'integer',
        ]);
        $links = $this->validateSources($request, (int)$item->routine_id);
        DB::transaction(function () use ($id, $data, $links) {
            DB::table('routine_derived_exam_items')->where('id', $id)->update([
                'exam_at'=>$data['exam_at'], 'exam_end_at'=>$data['exam_end_at'] ?? null, 'updated_at'=>now(),
            ]);
            DB::table('routine_derived_exam_sources')->where('derived_item_id', $id)->delete();
            foreach ($links as $link) DB::table('routine_derived_exam_sources')->insert($link + [
                'derived_item_id'=>$id,'created_at'=>now(),'updated_at'=>now(),
            ]);
            $this->recalculateDerivedDates((int)$this->derivedItem($id)->routine_id);
        });
        return redirect()->route('routine.item.show', ['id'=>$item->routine_id])->with('success', 'Revision/Model exam updated.');
    }

    public function destroyItem(int $id)
    {
        $item = $this->derivedItem($id);
        DB::transaction(function () use ($item) {
            DB::table('routine_derived_exam_sources')->where('derived_item_id', $item->id)->delete();
            DB::table('routine_derived_exam_items')->where('id', $item->id)->delete();
            $this->recalculateDerivedDates((int)$item->routine_id);
        });
        return redirect()->route('routine.item.show', ['id'=>$item->routine_id])->with('success','Revision/Model exam deleted.');
    }

    public function updateDate(Request $request, int $id)
    {
        $item = $this->derivedItem($id);
        $data = $request->validate([
            'field'=>'required|in:exam_at,exam_end_at',
            'value'=>'required|date',
        ]);
        $field=$data['field'];
        if ($field === 'exam_end_at' && strtotime($data['value']) < strtotime($item->exam_at)) {
            throw ValidationException::withMessages(['value'=>'Exam End cannot be earlier than Exam Start.']);
        }
        if ($field === 'exam_at' && $item->exam_end_at && strtotime($data['value']) > strtotime($item->exam_end_at)) {
            throw ValidationException::withMessages(['value'=>'Exam Start cannot be later than Exam End.']);
        }
        DB::transaction(function () use ($id, $field, $data, $item) {
            DB::table('routine_derived_exam_items')->where('id', $id)->update([$field=>$data['value'], 'updated_at'=>now()]);
            if ($field === 'exam_end_at') $this->recalculateDerivedDates((int)$item->routine_id);
        });
        $fresh = $this->derivedItem($id);
        return response()->json(['success'=>true,'value'=>$fresh->{$field},'message'=>'Date & Time updated.']);
    }

    private function recalculateDerivedDates(int $routineId): void
    {
        app(RoutineDerivedDateService::class)->recalculate($routineId);
    }
}
