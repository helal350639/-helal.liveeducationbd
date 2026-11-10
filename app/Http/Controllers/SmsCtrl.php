<?php

namespace App\Http\Controllers;

use App\Http\Controllers\SourceCtrl;
use Illuminate\Http\Request;
use App\Models\Batch;
use App\Models\Sms;
use App\Models\Role;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Auth;
use Image;
use Toastr;
use File;
use Session;

class SmsCtrl extends Controller
{
     public function __construct()
    {
        $this->middleware('auth');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Show ONLY names explicitly saved from Create Batch Name.
        // Batch records disappear automatically when their validity period ends.
        $today = now('Asia/Dhaka')->toDateString();
        $batches = collect();

        if (\Illuminate\Support\Facades\Schema::hasTable('sms_batch_names')) {
            $savedNames = DB::table('sms_batch_names')->orderByDesc('id')->get();

            foreach ($savedNames as $saved) {
                if ($saved->source_type === 'batch_list' && $saved->batch_id) {
                    $batch = Batch::with('course')
                        ->where('id', $saved->batch_id)
                        ->where('status', 'Active')
                        ->whereNotNull('start_at')->whereNotNull('end_at')
                        ->whereDate('start_at', '<=', $today)
                        ->whereDate('end_at', '>=', $today)
                        ->first(['id', 'name', 'course_id', 'start_at', 'end_at', 'status']);
                    if ($batch) {
                        $batch->sms_source_type = 'batch_list';
                        $batches->push($batch);
                    }
                } elseif ($saved->source_type === 'exam_section' && $saved->category_id) {
                    // Pull live information from the dedicated Exam Section Dashboard
                    // and its Routine + Routine Item tables. Do not use legacy routine tables.
                    $category = DB::table('lebd_exam_section_categories')
                        ->where('id', $saved->category_id)->where('status', 'Active')->first();
                    if (!$category) continue;

                    $subcategoryName = null;
                    if ($saved->subcategory_id) {
                        $subcategory = DB::table('lebd_exam_section_subcategories')
                            ->where('id', $saved->subcategory_id)
                            ->where('category_id', $saved->category_id)
                            ->where('status', 'Active')->first();
                        if (!$subcategory) continue;
                        $subcategoryName = $subcategory->name;
                    }

                    $routineQuery = DB::table('lebd_exam_section_routines as r')
                        ->leftJoin('courses as c', 'c.id', '=', 'r.course_id')
                        ->leftJoin('batches as b', 'b.id', '=', 'r.batch_id')
                        ->where('r.category_id', $saved->category_id)
                        ->where('r.status', 'Published');
                    if ($saved->subcategory_id) {
                        $routineQuery->where('r.subcategory_id', $saved->subcategory_id);
                    } else {
                        $routineQuery->whereNull('r.subcategory_id');
                    }

                    $routineRows = $routineQuery->orderBy('r.name')->get([
                        'r.id', 'r.name', 'r.status', 'r.course_id', 'r.batch_id',
                        'r.created_at', 'c.name as course_name', 'b.name as batch_name'
                    ]);
                    if ($routineRows->isEmpty()) continue;

                    $routineIds = $routineRows->pluck('id')->map(function ($id) { return (int) $id; })->all();
                    $routineItems = DB::table('lebd_exam_section_routine_items')
                        ->whereIn('routine_id', $routineIds)
                        ->where('type', 'exam')
                        ->orderBy('exam_start_at')
                        ->get(['id', 'routine_id', 'exam_name', 'exam_start_at', 'exam_end_at']);

                    $itemGroups = $routineItems->groupBy('routine_id');
                    $allStarts = $routineItems->pluck('exam_start_at')->filter()->map(function ($value) {
                        return \Carbon\Carbon::parse($value, 'Asia/Dhaka');
                    });
                    $allEnds = $routineItems->pluck('exam_end_at')->filter()->map(function ($value) {
                        return \Carbon\Carbon::parse($value, 'Asia/Dhaka');
                    });

                    $routineDetails = $routineRows->map(function ($routine) use ($itemGroups) {
                        $items = $itemGroups->get($routine->id, collect());
                        $routine->exam_items = $items;
                        $routine->exam_count = $items->count();
                        $routine->first_exam_at = $items->pluck('exam_start_at')->filter()->sort()->first();
                        $routine->last_exam_end_at = $items->pluck('exam_end_at')->filter()->sortDesc()->first();
                        return $routine;
                    });

                    $entry = (object) [
                        'id' => null,
                        'name' => $saved->name,
                        'course' => null,
                        'course_id' => null,
                        'start_at' => $allStarts->isNotEmpty() ? $allStarts->min()->format('Y-m-d H:i:s') : null,
                        'end_at' => $allEnds->isNotEmpty() ? $allEnds->max()->format('Y-m-d H:i:s') : null,
                        'status' => 'Active',
                        'sms_source_type' => 'exam_section',
                        'sms_saved_id' => (int) $saved->id,
                        'sms_category_id' => (int) $saved->category_id,
                        'sms_subcategory_id' => $saved->subcategory_id ? (int) $saved->subcategory_id : null,
                        'sms_category_name' => $category->name,
                        'sms_subcategory_name' => $subcategoryName,
                        'sms_routines' => $routineDetails,
                        'sms_routine_count' => $routineRows->count(),
                        'sms_exam_count' => $routineItems->count(),
                        'sms_link_batch_id' => $routineRows->pluck('batch_id')->filter()->first(),
                    ];
                    $batches->push($entry);
                }
            }
        }

        // Existing SMS rows are associated with one or more batches through JSON batch_id.
        // Read them without changing the existing SMS schema or records.
        $smsRows = Sms::where('sms_workflow', 'mcq')
            ->whereIn('sms_type', ['Exam Alert', 'Exam Link'])
            ->orderBy('id', 'DESC')->get();

        foreach ($batches as $batch) {
            if (($batch->sms_source_type ?? 'batch_list') !== 'batch_list') {
                $batch->exam_alert_scheduled = 0;
                $batch->exam_alert_total = 0;
                $batch->exam_link_scheduled = 0;
                $batch->exam_link_total = 0;
                continue;
            }
            $batchSms = $smsRows->filter(function ($sms) use ($batch) {
                $ids = json_decode($sms->batch_id, true);
                if (!is_array($ids)) $ids = [(int) $sms->batch_id];
                return in_array((int) $batch->id, array_map('intval', $ids), true);
            });
            $batch->exam_alert_scheduled = $batchSms->where('sms_type', 'Exam Alert')->where('status', 'Scheduled')->count();
            $batch->exam_alert_total = $batchSms->where('sms_type', 'Exam Alert')->count();
            $batch->exam_link_scheduled = $batchSms->where('sms_type', 'Exam Link')->where('status', 'Scheduled')->count();
            $batch->exam_link_total = $batchSms->where('sms_type', 'Exam Link')->count();
        }

        return view('layouts.sms.index', compact('batches'));
    }

    /** Show saved SMS records for one batch and SMS type. */
    public function batchSmsList($batchId, $type)
    {
        $batch = Batch::with('course')->findOrFail($batchId);
        abort_unless(in_array($type, ['alert', 'link'], true), 404);
        $smsType = $type === 'alert' ? 'Exam Alert' : 'Exam Link';
        $smsRows = Sms::where('sms_workflow', 'mcq')->where('sms_type', $smsType)
            ->orderBy('id', 'DESC')->get();
        $smsList = $smsRows->filter(function ($sms) use ($batchId) {
            $ids = json_decode($sms->batch_id, true);
            if (!is_array($ids)) $ids = [(int) $sms->batch_id];
            return in_array((int) $batchId, array_map('intval', $ids), true);
        })->values();
        return view('layouts.sms.batch-sms-list', compact('batch', 'smsList', 'smsType'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // Only batches whose validity period includes today's Bangladesh date.
        $today = now('Asia/Dhaka')->toDateString();

        $batches = Batch::where('status', 'Active')
            ->whereNotNull('start_at')
            ->whereNotNull('end_at')
            ->whereDate('start_at', '<=', $today)
            ->whereDate('end_at', '>=', $today)
            ->orderBy('id', 'DESC')
            ->select('id', 'name', 'start_at', 'end_at')
            ->get();

        $sourceType = request()->query('source_type', 'batch_list');
        $sourceId = (int) request()->query('source_id', 0);
        $smsSource = null;
        if ($sourceType === 'exam_section' && $sourceId && DB::table('sms_batch_names')->where('id', $sourceId)->where('source_type', 'exam_section')->exists()) {
            $smsSource = DB::table('sms_batch_names')->where('id', $sourceId)->first();
            $routineBatchIds = DB::table('lebd_exam_section_routines')
                ->where('category_id', $smsSource->category_id)
                ->when($smsSource->subcategory_id, function ($query) use ($smsSource) {
                    $query->where('subcategory_id', $smsSource->subcategory_id);
                }, function ($query) {
                    $query->whereNull('subcategory_id');
                })
                ->where('status', 'Published')
                ->whereNotNull('batch_id')
                ->pluck('batch_id')->map(function ($id) { return (int) $id; })->unique()->values()->all();

            $batches = $batches->whereIn('id', $routineBatchIds)->values();
        } else {
            $sourceType = 'batch_list';
            $sourceId = 0;
        }

        $requestedBatchId = (int) request()->query('batch_id', 0);
        $selectedBatchId = $batches->contains('id', $requestedBatchId) ? $requestedBatchId : ($sourceType === 'exam_section' ? $batches->first()->id ?? null : null);

        $selectedRoutineIds = session('sms_selected_routine_ids', []);
        $selectedExamIds = session('sms_selected_exam_ids', []);

        if ($sourceType === 'exam_section' && $smsSource) {
            // Exam Section source: use its own Category/Subcategory, Routine and
            // Routine Item tables. Batch data is retained only as hidden recipient
            // mapping for the SMS delivery engine; it is not presented as the source.
            $category = DB::table('lebd_exam_section_categories')
                ->where('id', $smsSource->category_id)->first();
            $subcategory = $smsSource->subcategory_id
                ? DB::table('lebd_exam_section_subcategories')->where('id', $smsSource->subcategory_id)->first()
                : null;

            $routineRows = DB::table('lebd_exam_section_routines as r')
                ->leftJoin('courses as c', 'c.id', '=', 'r.course_id')
                ->where('r.category_id', $smsSource->category_id)
                ->when($smsSource->subcategory_id, function ($q) use ($smsSource) {
                    $q->where('r.subcategory_id', $smsSource->subcategory_id);
                }, function ($q) {
                    $q->whereNull('r.subcategory_id');
                })
                ->where('r.status', 'Published')
                ->orderBy('r.name')
                ->get(['r.id', 'r.name', 'r.status', 'r.course_id', 'r.batch_id', 'c.name as course_name']);

            $sectionRoutineIds = $routineRows->pluck('id')->map(function ($id) { return (int) $id; })->all();
            $sectionItems = $sectionRoutineIds
                ? DB::table('lebd_exam_section_routine_items')
                    ->whereIn('routine_id', $sectionRoutineIds)
                    ->where('type', 'exam')
                    ->orderBy('exam_start_at')
                    ->get(['id', 'routine_id', 'exam_name', 'exam_start_at', 'exam_end_at'])
                : collect();

            $selectedRoutineIds = $sectionRoutineIds;
            $selectedExamIds = $sectionItems->pluck('id')->map(function ($id) { return (int) $id; })->all();

            $routines = $routineRows;
            $selectedExams = $sectionItems->map(function ($item) use ($routineRows, $smsSource) {
                $routine = $routineRows->firstWhere('id', $item->routine_id);
                $exam = (object) [
                    'id' => $item->id,
                    'routine_id' => $item->routine_id,
                    'paper_name' => $item->exam_name,
                    'exam_at' => $item->exam_start_at,
                    'exam_end_at' => $item->exam_end_at,
                    'routine_name' => $routine->name ?? '',
                    'batch_name' => $smsSource->name,
                    'batch_name_en' => $smsSource->name,
                    'source_type' => 'exam_section',
                ];
                return $exam;
            })->values();

            $sectionBatchIds = $routineRows->pluck('batch_id')->filter()->map(function ($id) { return (int) $id; })->unique()->values()->all();
            $batches = $batches->whereIn('id', $sectionBatchIds)->values();
            $selectedBatchId = null;
            $smsSource->category_name = $category->name ?? $smsSource->name;
            $smsSource->subcategory_name = $subcategory->name ?? null;
            $smsSource->routine_count = $routineRows->count();
            $smsSource->exam_count = $sectionItems->count();
            $smsSource->course_names = $routineRows->pluck('course_name')->filter()->unique()->values();
        } else {
            // Batch List source: automatically preload all Published routines and
            // exams belonging to the selected Batch.
            if ($sourceType === 'batch_list' && $selectedBatchId && request()->query('batch_id')) {
                $batchRoutineIds = DB::table('routines')
                    ->where('batch_id', $selectedBatchId)
                    ->where('status', 'Published')
                    ->orderBy('id')
                    ->pluck('id')
                    ->map(function ($id) { return (int) $id; })
                    ->all();

                if (!empty($batchRoutineIds)) {
                    $selectedRoutineIds = $batchRoutineIds;
                    $selectedExamIds = DB::table('routine_item_rows')
                        ->whereIn('routine_id', $batchRoutineIds)
                        ->orderBy('exam_at')
                        ->pluck('id')
                        ->map(function ($id) { return (int) $id; })
                        ->all();
                }
            }

            $routines = DB::table('routines as r')
                ->join('batches as b', 'b.id', '=', 'r.batch_id')
                ->whereIn('r.id', $selectedRoutineIds ?: [0])
                ->where('r.status', 'Published')
                ->orderBy('r.name')
                ->get(['r.id', 'r.batch_id', 'r.name', 'b.name as batch_name']);

            $selectedExams = DB::table('routine_item_rows as ri')
                ->join('routines as r', 'r.id', '=', 'ri.routine_id')
                ->join('batches as b', 'b.id', '=', 'r.batch_id')
                ->whereIn('ri.id', $selectedExamIds ?: [0])
                ->orderBy('ri.exam_at')
                ->get([
                    'ri.id', 'ri.routine_id', 'ri.paper_name', 'ri.exam_at',
                    'ri.exam_end_at', 'r.name as routine_name',
                    'r.batch_id', 'b.name as batch_name'
                ])
                ->map(function ($exam) {
                    $exam->batch_name_en = $this->batchNameInEnglish($exam->batch_name);
                    return $exam;
                });
        }

        return view('layouts.sms.create', compact('batches', 'routines', 'selectedRoutineIds', 'selectedBatchId', 'selectedExamIds', 'selectedExams', 'sourceType', 'sourceId', 'smsSource'));
    }

    /**
     * Open the existing Routine list in SMS selection mode.
     */
    public function selectRoutines(Request $request)
    {
        $today = now('Asia/Dhaka')->toDateString();
        $batchIds = array_values(array_unique(array_map('intval', $request->input('batch_ids', []))));

        $validBatchIds = Batch::whereIn('id', $batchIds)
            ->where('status', 'Active')
            ->whereNotNull('start_at')
            ->whereNotNull('end_at')
            ->whereDate('start_at', '<=', $today)
            ->whereDate('end_at', '>=', $today)
            ->pluck('id')->map(fn($id) => (int) $id)->all();

        if (!$batchIds || count($batchIds) !== count($validBatchIds)) {
            return redirect()->route('sms.create')
                ->with('error', 'Routine নির্বাচন করার আগে বর্তমানে মেয়াদ থাকা Batch নির্বাচন করুন।');
        }

        // Each scheduled exam is stored as a row under its Routine.
        $examRows = DB::table('routine_item_rows as ri')
            ->join('routines as r', 'r.id', '=', 'ri.routine_id')
            ->join('batches as b', 'b.id', '=', 'r.batch_id')
            ->whereIn('r.batch_id', $validBatchIds)
            ->where('r.status', 'Published')
            ->orderBy('r.name')
            ->orderBy('ri.exam_at')
            ->get([
                'ri.id as exam_id', 'ri.routine_id', 'ri.paper_name',
                'ri.exam_at', 'ri.exam_end_at', 'r.name as routine_name',
                'r.batch_id', 'b.name as batch_name'
            ]);

        $selectedExamIds = session('sms_selected_exam_ids', []);
        $examGroups = $examRows->groupBy('routine_id');

        return view('layouts.sms.routine-select', compact('examGroups', 'batchIds', 'selectedExamIds'));
    }

    /**
     * Save checked routines and return to the SMS Details form.
     */
    public function doneSelectingRoutines(Request $request)
    {
        $request->validate([
            'batch_ids' => 'required|array|min:1',
            'batch_ids.*' => 'integer',
            'exam_ids' => 'required|array|min:1',
            'exam_ids.*' => 'integer|distinct',
        ]);

        $today = now('Asia/Dhaka')->toDateString();
        $batchIds = array_values(array_unique(array_map('intval', $request->input('batch_ids', []))));
        $validBatchIds = Batch::whereIn('id', $batchIds)
            ->where('status', 'Active')
            ->whereNotNull('start_at')
            ->whereNotNull('end_at')
            ->whereDate('start_at', '<=', $today)
            ->whereDate('end_at', '>=', $today)
            ->pluck('id')->map(fn($id) => (int) $id)->all();

        if (count($batchIds) !== count($validBatchIds)) {
            return redirect()->route('sms.create')
                ->with('error', 'Batch-এর মেয়াদ শেষ বা শুরু হয়নি। আবার Batch নির্বাচন করুন।');
        }

        $examIds = array_values(array_unique(array_map('intval', $request->input('exam_ids', []))));
        $validExams = DB::table('routine_item_rows as ri')
            ->join('routines as r', 'r.id', '=', 'ri.routine_id')
            ->whereIn('ri.id', $examIds)
            ->whereIn('r.batch_id', $validBatchIds)
            ->where('r.status', 'Published')
            ->get(['ri.id', 'ri.routine_id']);

        if (count($examIds) !== $validExams->count()) {
            return back()->withInput()->withErrors([
                'exam_ids' => 'শুধু নির্বাচিত Batch-এর Routine-এর Exam নির্বাচন করুন।'
            ]);
        }

        $routineIds = $validExams->pluck('routine_id')->map(fn($id) => (int) $id)->unique()->values()->all();
        session([
            'sms_selected_exam_ids' => $examIds,
            'sms_selected_routine_ids' => $routineIds,
        ]);

        return redirect()->route('sms.create', ['resume' => 1])
            ->with('success', count($examIds) . 'টি Exam নির্বাচন করা হয়েছে।');
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'batch_id'  => 'required|array|min:1',
            'batch_id.*' => 'integer|distinct|exists:batches,id',
            'student_id'  => 'nullable|array',
            'details'   => 'nullable|max:150',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'is_active' => 'nullable|boolean',
            'routine_ids' => 'nullable|array',
            'routine_ids.*' => 'integer|distinct|exists:routines,id',
            'exam_ids' => 'nullable|array',
            'exam_ids.*' => 'integer|distinct|exists:routine_item_rows,id',
        ]);

        $batchIds = array_values(array_unique(array_map('intval', $request->input('batch_id', []))));
        $today = now('Asia/Dhaka')->toDateString();
        $validBatchIds = Batch::whereIn('id', $batchIds)
            ->where('status', 'Active')
            ->whereNotNull('start_at')
            ->whereNotNull('end_at')
            ->whereDate('start_at', '<=', $today)
            ->whereDate('end_at', '>=', $today)
            ->pluck('id')->map(fn($id) => (int) $id)->all();

        if (count($batchIds) !== count($validBatchIds)) {
            return back()->withInput()->withErrors(['batch_id' => 'শুধু বর্তমানে মেয়াদ থাকা Active Batch নির্বাচন করুন।']);
        }

        $workflow = $request->input('sms_workflow');
        $smsType = $request->input('sms_type');

        /*
         * EXAM ALERT:
         * One independently scheduled SMS is created per selected routine exam.
         * The send time is calculated server-side as exactly 12 hours before exam_at.
         */
        if ($workflow === 'mcq' && $smsType === 'Exam Alert') {
            $examIds = array_values(array_unique(array_map('intval', $request->input('exam_ids', []))));
            if (!$examIds) {
                return back()->withInput()->withErrors(['exam_ids' => 'Exam Alert তৈরি করতে অন্তত একটি Routine Exam নির্বাচন করুন।']);
            }

            $examRows = DB::table('routine_item_rows as ri')
                ->join('routines as r', 'r.id', '=', 'ri.routine_id')
                ->join('batches as b', 'b.id', '=', 'r.batch_id')
                ->whereIn('ri.id', $examIds)
                ->whereIn('r.batch_id', $batchIds)
                ->where('r.status', 'Published')
                ->orderBy('ri.exam_at')
                ->get([
                    'ri.id as exam_id', 'ri.routine_id', 'ri.paper_name',
                    'ri.exam_at', 'ri.exam_end_at', 'r.name as routine_name',
                    'r.batch_id', 'b.name as batch_name'
                ]);

            if ($examRows->count() !== count($examIds)) {
                return back()->withInput()->withErrors(['exam_ids' => 'নির্বাচিত Exam-এর Routine/Batch তথ্য সঠিক নয়। আবার নির্বাচন করুন।']);
            }

            foreach ($examRows as $exam) {
                if (empty($exam->exam_at)) {
                    return back()->withInput()->withErrors(['exam_ids' => $exam->paper_name . ' Exam-এর তারিখ ও সময় Routine-এ দেওয়া নেই।']);
                }
            }

            $examAction = $request->input('exam_action', 'confirm_schedule');
            if (!in_array($examAction, ['save_draft', 'confirm_schedule'], true)) {
                return back()->withInput()->withErrors(['exam_action' => 'সঠিক Save/Schedule অপশন নির্বাচন করুন।']);
            }

            DB::beginTransaction();
            try {
                foreach ($examRows as $exam) {
                    $examStart = \Carbon\Carbon::parse($exam->exam_at, 'Asia/Dhaka')->timezone('Asia/Dhaka');
                    $sendAt = $examStart->copy()->subHours(12);
                    if ($examAction === 'confirm_schedule' && $sendAt->lte(now('Asia/Dhaka'))) {
                        throw new \RuntimeException($exam->paper_name . ' Exam-এর ১২ ঘণ্টা আগে SMS পাঠানোর সময় ইতোমধ্যে পার হয়েছে। এই Exam-এর জন্য এখন Schedule করা যাবে না।');
                    }
                    if ($examAction === 'save_draft' && $examStart->lte(now('Asia/Dhaka'))) {
                        // Keep past exams editable as drafts; scheduling will be revalidated later.
                    }

                    $studentIds = DB::table('batch_student')
                        ->where('batch_id', $exam->batch_id)
                        ->pluck('student_id')
                        ->map(fn($id) => (int) $id)
                        ->unique()
                        ->values()
                        ->all();

                    if (!$studentIds) {
                        throw new \RuntimeException($exam->batch_name . ' Batch-এ কোনো Student পাওয়া যায়নি।');
                    }

                    $batchEnglish = $this->batchNameInEnglish($exam->batch_name);
                    $batchEnglish = trim(preg_replace('/\\s*\\([^)]*\\)\\s*/u', ' ', $batchEnglish));
                    $examDate = $examStart->format('d-m-y');
                    $examTime = $examStart->format('h:i A');

                    $message = implode("\n", [
                        'Exam Alert',
                        'Live Education BD',
                        $batchEnglish,
                        'Exam No: ' . $exam->paper_name,
                        'Exam Date: ' . $examDate,
                        'Time: ' . $examTime,
                        'Call: 01718778184',
                    ]);

                    if (mb_strlen($message, 'UTF-8') > 150) {
                        throw new \RuntimeException($exam->paper_name . ' Exam Alert SMS 150 characters-এর বেশি হচ্ছে। Batch/Exam নাম সংক্ষিপ্ত করুন।');
                    }

                    $smsRow = [
                        'title' => 'Exam Alert — ' . $exam->paper_name . ' — ' . $exam->batch_name,
                        'batch_id' => json_encode([(int) $exam->batch_id]),
                        'student_id' => json_encode($studentIds),
                        'details' => $message,
                        'sms_send_by' => Auth::id(),
                        'status' => $examAction === 'save_draft' ? 'Draft' : 'Scheduled',
                        'datetime' => $examAction === 'save_draft' ? null : $sendAt->format('Y-m-d H:i:s'),
                        'start_date' => now('Asia/Dhaka')->toDateString(),
                        'end_date' => $examStart->toDateString(),
                        'is_active' => $request->boolean('is_active', true) ? 1 : 0,
                        'sms_workflow' => 'mcq',
                        'sms_type' => 'Exam Alert',
                        'routine_ids' => json_encode([(int) $exam->routine_id]),
                        'exam_ids' => json_encode([(int) $exam->exam_id]),
                        'created_at' => now('Asia/Dhaka')->format('Y-m-d H:i:s'),
                        'updated_at' => now('Asia/Dhaka')->format('Y-m-d H:i:s'),
                    ];

                    DB::table('sms')->insert($smsRow);
                }

                DB::commit();
            } catch (\Throwable $e) {
                DB::rollBack();
                \Log::error('Exam Alert SMS scheduling failed: ' . $e->getMessage());
                return back()->withInput()->withErrors(['sms' => 'Exam Alert schedule তৈরি করা যায়নি: ' . $e->getMessage()]);
            }

            session()->forget(['sms_selected_routine_ids', 'sms_selected_exam_ids']);

            return redirect()->route('sms.index')
                ->with('success', $examRows->count() . ($examAction === 'save_draft'
                    ? 'টি Exam Alert Draft হিসেবে সংরক্ষণ হয়েছে। পরে Edit করে Schedule করতে পারবেন।'
                    : 'টি Exam Alert SMS Schedule হয়েছে। প্রতিটি Exam শুরুর ১২ ঘণ্টা আগে পাঠানো হবে।'));
        }

        // Existing non-Exam-Alert SMS flow remains manual.
        $source = new SourceCtrl;
        $data = $request->all();
        unset($data['_token']);

        $routineIds = array_map('intval', $request->input('routine_ids', []));
        if ($routineIds) {
            $validRoutineIds = DB::table('routines')
                ->whereIn('id', $routineIds)
                ->whereIn('batch_id', $batchIds)
                ->where('status', 'Published')
                ->pluck('id')->map(fn($id) => (int) $id)->all();

            if (count(array_unique($routineIds)) !== count($validRoutineIds)) {
                return back()->withInput()->withErrors(['routine_ids' => 'নির্বাচিত Routine অবশ্যই নির্বাচিত Batch-এর Published Routine হতে হবে।']);
            }
        }

        $examIds = array_map('intval', $request->input('exam_ids', []));
        $students = Student::leftJoin('batch_student', 'batch_student.student_id', 'students.id')
            ->whereIn('batch_student.batch_id', $batchIds);

        $studentContacts = $students->pluck('students.contact')->toArray();
        $studentIds = $students->pluck('students.id')->toArray();

        $smsableContact = '';
        $arrayMapping = function ($numbers) {
            return implode(', ', array_map(function ($num) {
                return '+88' . $num;
            }, $numbers));
        };

        if (isset($data['student_id'])) {
            $selectedStudents = Student::whereIn('id', $data['student_id']);
            $studentContacts = $selectedStudents->pluck('contact')->toArray();
            $studentIds = $selectedStudents->pluck('id')->toArray();
        }
        $smsableContact = $arrayMapping($studentContacts);

        $data['batch_id'] = json_encode($batchIds);
        $data['routine_ids'] = json_encode($routineIds);
        $data['exam_ids'] = json_encode($examIds);
        $data['sms_workflow'] = $workflow;
        $data['sms_type'] = $smsType;
        $data['student_id'] = json_encode($studentIds);
        $data['sms_send_by'] = Auth::id();
        $data['is_active'] = $request->boolean('is_active');

        if (($data['status'] ?? '') === 'Sent') {
            $source->sms_send($smsableContact, $data['details'] ?? '');
        }

        try {
            Sms::insert($data);
        } catch (\Exception $e) {
            return $e->getMessage();
        }

        Session::flash('success', 'SMS Send successfull');
        session()->forget(['sms_selected_routine_ids', 'sms_selected_exam_ids']);

        return redirect()->route('sms.index');
    }

    /**
     * Convert common Bengali batch labels into a readable English SMS label.
     * Existing Latin text and numbers are preserved.
     */
    private function batchNameInEnglish($name)
    {
        $replacements = [
            'অনলাইন' => 'Online',
            'ব্যাচ' => 'Batch',
            'বাংলা' => 'Bangla',
            'ইংরেজি' => 'English',
            'তথ্য ও যোগাযোগ প্রযুক্তি' => 'Information and Communication Technology',
            'তথ্য' => 'Information',
            'যোগাযোগ' => 'Communication',
            'প্রযুক্তি' => 'Technology',
            'প্রস্তুতি' => 'Preparation',
            'বিজ্ঞান' => 'Science',
            'গণিত' => 'Mathematics',
            'পদার্থবিজ্ঞান' => 'Physics',
            'রসায়ন' => 'Chemistry',
            'জীববিজ্ঞান' => 'Biology',
            'সাধারণ' => 'General',
            'বিশেষ' => 'Special',
        ];

        $english = (string) $name;
        uksort($replacements, function ($a, $b) {
            return strlen($b) <=> strlen($a);
        });
        $english = str_replace(array_keys($replacements), array_values($replacements), $english);
        $english = strtr($english, [
            '০' => '0', '১' => '1', '২' => '2', '৩' => '3', '৪' => '4',
            '৫' => '5', '৬' => '6', '৭' => '7', '৮' => '8', '৯' => '9',
        ]);
        $english = preg_replace('/[অ-ঔক-হড়-য়ঃঁংঃ]+/u', ' ', $english);
        $english = preg_replace('/\s+/', ' ', $english);

        return trim($english, " \t\n\r\0\x0B,") ?: 'Live Education BD Batch';
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\course  $course
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
      $sms = Sms::find($id);
      return view('layouts.sms.read', compact('sms'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\course  $course
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
      $batches = Batch::where('status', 'Active')
      ->orderBy('id', 'DESC')
      ->select('id', 'name')
      ->get();

        $sms = Sms::find($id);
        $student_str = json_decode($sms->student_id);
        $student_ids = array_map('intval', $student_str);

        $students = Student::whereIn('id', $student_ids)->get();
        $examInfo = null;
        if ($sms->sms_workflow === 'mcq' && $sms->sms_type === 'Exam Alert') {
            $examIds = json_decode($sms->exam_ids, true);
            $examId = is_array($examIds) ? (int) reset($examIds) : 0;
            $examInfo = DB::table('routine_item_rows as ri')
                ->join('routines as r', 'r.id', '=', 'ri.routine_id')
                ->where('ri.id', $examId)
                ->first(['ri.paper_name', 'ri.exam_at', 'r.name as routine_name']);
        }
        return view('layouts.sms.edit', compact('batches', 'students', 'sms', 'examInfo'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\course  $course
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
      $sms = Sms::findOrFail($id);

      // Exam Alert drafts are edited and scheduled using the Routine's latest exam time.
      if ($sms->sms_workflow === 'mcq' && $sms->sms_type === 'Exam Alert') {
          $request->validate([
              'title' => 'required|string|max:255',
              'details' => 'required|string|max:150',
              'is_active' => 'nullable|boolean',
              'exam_action' => 'required|in:save_draft,confirm_schedule',
          ]);

          $examIds = json_decode($sms->exam_ids, true);
          $examId = is_array($examIds) ? (int) reset($examIds) : 0;
          $exam = DB::table('routine_item_rows as ri')
              ->join('routines as r', 'r.id', '=', 'ri.routine_id')
              ->where('ri.id', $examId)
              ->where('r.status', 'Published')
              ->first(['ri.id', 'ri.exam_at', 'ri.paper_name', 'ri.routine_id']);

          if (!$exam) {
              return back()->withInput()->withErrors(['exam' => 'এই SMS-এর Routine Exam পাওয়া যায়নি।']);
          }

          $updates = [
              'title' => $request->input('title'),
              'details' => $request->input('details'),
              'is_active' => $request->boolean('is_active') ? 1 : 0,
              'updated_at' => now('Asia/Dhaka')->format('Y-m-d H:i:s'),
          ];

          if ($request->input('exam_action') === 'save_draft') {
              $updates['status'] = 'Draft';
              $updates['datetime'] = null;
              Sms::where('id', $id)->update($updates);
              return redirect()->route('sms.index')->with('success', 'Exam Alert Draft সংরক্ষণ হয়েছে। পরে আবার Edit করতে পারবেন।');
          }

          if (empty($exam->exam_at)) {
              return back()->withInput()->withErrors(['exam' => 'Routine-এ Exam-এর তারিখ ও সময় দেওয়া নেই।']);
          }
          $examStart = \Carbon\Carbon::parse($exam->exam_at, 'Asia/Dhaka')->timezone('Asia/Dhaka');
          $sendAt = $examStart->copy()->subHours(12);
          if ($sendAt->lte(now('Asia/Dhaka'))) {
              return back()->withInput()->withErrors(['exam' => 'Exam শুরুর ১২ ঘণ্টা আগে SMS পাঠানোর সময় ইতোমধ্যে পার হয়েছে।']);
          }

          $updates['status'] = 'Scheduled';
          $updates['datetime'] = $sendAt->format('Y-m-d H:i:s');
          Sms::where('id', $id)->update($updates);
          return redirect()->route('sms.index')->with('success', 'Exam Alert আপডেট ও Schedule হয়েছে। SMS নির্ধারিত সময়ে পাঠানো হবে।');
      }

      $source = new SourceCtrl;
      $this->validate($request, [
          'batch_id'  => 'required|array',
          'student_id'  => 'nullable|array',
          'details'   => 'nullable|max:150',
          'start_date' => 'nullable|date',
          'end_date' => 'nullable|date|after_or_equal:start_date',
          'is_active' => 'nullable|boolean',
      ]);
        
        $data = $request->all();

        if(isset($data['_token']))
        {
            unset($data['_token']);
        }
        if(isset($data['_method']))
        {
            unset($data['_method']);
        }

        $data['is_active'] = $request->boolean('is_active');

        try {
          Sms::where('id', $id)->update($data);
        }
        catch(\Exception $e)
        {
            return $e->getMessage();
        }
        
        Session::flash('success', 'The sms successfully Updated!');

        return redirect()->route('sms.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\course  $course
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
      if(Auth::user()->AuthorizeRoles('SuperAdmin'))
      {
        $sms = Sms::find($id);

        // delete all relationship
        // $sms->papers()->detach();

        //delete group table
        $sms->delete();

        Session::flash('Success','The sms successfully deleted');
        return redirect()->route('sms.index');
      }
      return back()->with('error', 'Permission Denied');
    }

    /**
     * Toggle whether an SMS schedule is active.
     */
    public function toggleActive($id)
    {
        $sms = Sms::findOrFail($id);
        $sms->is_active = !$sms->is_active;
        $sms->save();

        return back()->with('success', $sms->is_active ? 'SMS schedule activated.' : 'SMS schedule deactivated.');
    }

    /** get students according to the batch */
    public function getBatchStudents($batch_ids_str)
    {
        $batch_ids = explode(',', $batch_ids_str);
        $students = Student::leftJoin('batch_student', 'batch_student.student_id', 'students.id')
        ->leftJoin('batches', 'batches.id', 'batch_student.batch_id')
        ->whereIn('batch_student.batch_id', $batch_ids)
        ->select(
            'students.id as student_id',
            'students.name as student_name',
            'students.contact',
            'batches.name as batch_name'
            )
        ->get();

        return response()->json([
            'students' => $students
        ], 200);

        // dd($students);        
    }
    
}
