<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class EmailBatchNameController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $emailAccounts = Schema::hasTable('email_sender_accounts')
            ? DB::table('email_sender_accounts')->where('is_active', 1)->orderBy('account_name')->get(['id','account_name','email'])
            : collect();
        return view('layouts.email.batch-name', compact('emailAccounts'));
    }

    public function courses()
    {
        $courses = Course::query()->orderBy('name')->get(['id', 'name']);
        return response()->json(['data' => $courses]);
    }

    public function batches($courseId)
    {
        $course = Course::findOrFail($courseId);
        $today = now('Asia/Dhaka')->toDateString();

        $batches = Batch::query()
            ->where('course_id', $course->id)
            ->where('status', 'Active')
            ->whereNotNull('start_at')->whereNotNull('end_at')
            ->whereDate('start_at', '<=', $today)
            ->whereDate('end_at', '>=', $today)
            ->orderBy('name')
            ->get(['id', 'name', 'status', 'start_at', 'end_at']);

        return response()->json(['data' => $batches]);
    }

    public function examCategories()
    {
        $categories = DB::table('lebd_exam_section_categories')
            ->where('status', 'Active')
            ->orderBy('sort_order')->orderBy('name')
            ->get(['id', 'name']);
        return response()->json(['data' => $categories]);
    }

    public function examSubcategories($categoryId)
    {
        $category = DB::table('lebd_exam_section_categories')
            ->where('id', $categoryId)->where('status', 'Active')
            ->first(['id', 'name']);
        abort_unless($category, 404);

        $subcategories = DB::table('lebd_exam_section_subcategories')
            ->where('category_id', $category->id)->where('status', 'Active')
            ->orderBy('sort_order')->orderBy('name')
            ->get(['id', 'name']);

        return response()->json([
            'has_subcategories' => $subcategories->isNotEmpty(),
            'data' => $subcategories,
        ]);
    }

    /**
     * Save only the final selected Batch OR the final Exam Section item.
     * Requires the email_batch_names table to be created in phpMyAdmin.
     */
    public function store(Request $request)
    {
        abort_unless(Schema::hasTable('email_batch_names'), 500,
            'email_batch_names table পাওয়া যায়নি। Install SQL চালান।');

        $sourceType = $request->input('source_type');
        $senderAccountIds = array_values(array_unique(array_map('intval', $request->input('sender_account_ids', []))));
        if ($senderAccountIds && Schema::hasTable('email_sender_accounts')) {
            $validIds = DB::table('email_sender_accounts')->whereIn('id', $senderAccountIds)->where('is_active', 1)->pluck('id')->map(function ($id) { return (int)$id; })->all();
            if (count($validIds) !== count($senderAccountIds)) return back()->withInput()->with('error', 'শুধু Active Sender Email নির্বাচন করুন।');
        }

        if ($sourceType === 'batch_list') {
            $request->validate(['course_id' => 'required|integer', 'batch_id' => 'required|integer']);
            $today = now('Asia/Dhaka')->toDateString();

            $batch = Batch::where('id', $request->batch_id)
                ->where('course_id', $request->course_id)
                ->where('status', 'Active')
                ->whereNotNull('start_at')->whereNotNull('end_at')
                ->whereDate('start_at', '<=', $today)
                ->whereDate('end_at', '>=', $today)
                ->firstOrFail();

            $values = [
                'source_key' => 'batch:' . $batch->id,
                'source_type' => 'batch_list',
                'course_id' => $batch->course_id,
                'batch_id' => $batch->id,
                'category_id' => null,
                'subcategory_id' => null,
                'name' => $batch->name,
            ];
        } elseif ($sourceType === 'exam_section') {
            $request->validate([
                'category_id' => 'required|integer',
                'subcategory_id' => 'nullable|integer',
            ]);

            $category = DB::table('lebd_exam_section_categories')
                ->where('id', $request->category_id)->where('status', 'Active')->first();
            abort_unless($category, 404);

            if ($request->filled('subcategory_id')) {
                $sub = DB::table('lebd_exam_section_subcategories')
                    ->where('id', $request->subcategory_id)
                    ->where('category_id', $category->id)
                    ->where('status', 'Active')->first();
                abort_unless($sub, 404);
                $sourceKey = 'exam-subcategory:' . $sub->id;
                $name = $sub->name;
                $subcategoryId = $sub->id;
            } else {
                $sourceKey = 'exam-category:' . $category->id;
                $name = $category->name;
                $subcategoryId = null;
            }

            $values = [
                'source_key' => $sourceKey,
                'source_type' => 'exam_section',
                'course_id' => null,
                'batch_id' => null,
                'category_id' => $category->id,
                'subcategory_id' => $subcategoryId,
                'name' => $name,
            ];
        } else {
            return back()->with('error', 'সঠিক Category নির্বাচন করুন।');
        }

        DB::table('email_batch_names')->updateOrInsert(
            ['source_key' => $values['source_key']],
            $values + [
                'sender_account_ids' => json_encode($senderAccountIds),
                'created_by' => auth()->id(),
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        return redirect()->route('email.index')->with('success', 'নির্বাচিত নাম Email Management তালিকায় যোগ হয়েছে।');
    }
}
