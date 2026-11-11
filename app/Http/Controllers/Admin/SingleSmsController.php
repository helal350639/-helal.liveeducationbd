<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\SourceCtrl;
use App\Models\Sms;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SingleSmsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /** Show students from the main students table with search and filters. */
    public function index(Request $request)
    {
        $query = Student::query()->with(['courses:id,name', 'batches:id,name']);

        if ($request->filled('q')) {
            $term = trim($request->input('q'));
            $query->where(function ($builder) use ($term) {
                $builder->where('name', 'like', '%' . $term . '%')
                    ->orWhere('contact', 'like', '%' . $term . '%')
                    ->orWhere('email', 'like', '%' . $term . '%')
                    ->orWhere('reg_id', 'like', '%' . $term . '%');
            });
        }

        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('course_id')) {
            $query->whereHas('courses', function ($builder) use ($request) {
                $builder->where('courses.id', (int) $request->input('course_id'));
            });
        }

        if ($request->filled('batch_id')) {
            $query->whereHas('batches', function ($builder) use ($request) {
                $builder->where('batches.id', (int) $request->input('batch_id'));
            });
        }

        $perPage = min(max((int) $request->input('per_page', 25), 10), 100);
        $students = $query->orderBy('id', 'desc')->paginate($perPage)->withQueryString();
        $courses = DB::table('courses')->orderBy('name')->get(['id', 'name']);
        $batches = DB::table('batches')->orderBy('name')->get(['id', 'name']);
        $history = Sms::where('sms_workflow', 'single')->orderByDesc('id')->limit(15)->get();

        return view('layouts.sms.single', compact('students', 'courses', 'batches', 'history'));
    }

    /** Send one message to only the checked students and save a history record. */
    public function send(Request $request)
    {
        $request->validate([
            'student_ids' => 'required|array|min:1|max:500',
            'student_ids.*' => 'required|integer|distinct|exists:students,id',
            'details' => 'required|string|max:1000',
        ], [
            'student_ids.required' => 'SMS পাঠানোর জন্য অন্তত একজন Student নির্বাচন করুন।',
            'student_ids.min' => 'SMS পাঠানোর জন্য অন্তত একজন Student নির্বাচন করুন।',
            'details.required' => 'SMS-এর Message লিখুন।',
            'details.max' => 'SMS সর্বোচ্চ ১০০০ Character হতে পারবে।',
        ]);

        $message = trim($request->input('details'));
        if ($message === '') {
            return back()->withInput()->withErrors(['details' => 'SMS-এর Message লিখুন।']);
        }

        $selectedIds = array_values(array_unique(array_map('intval', $request->input('student_ids', []))));
        $students = Student::whereIn('id', $selectedIds)->orderBy('id')->get(['id', 'name', 'contact']);

        $recipients = [];
        $recipientIds = [];
        foreach ($students as $student) {
            $digits = preg_replace('/\D+/', '', (string) $student->contact);
            if ($digits === '') {
                continue;
            }
            if (Str::startsWith($digits, '880')) {
                $number = '+' . $digits;
            } elseif (Str::startsWith($digits, '01')) {
                $number = '+88' . $digits;
            } elseif (Str::startsWith($digits, '1')) {
                $number = '+880' . $digits;
            } else {
                continue;
            }
            $recipients[] = $number;
            $recipientIds[] = (int) $student->id;
        }

        $recipients = array_values(array_unique($recipients));
        if (!$recipients) {
            return back()->withInput()->withErrors(['student_ids' => 'নির্বাচিত Student-দের মধ্যে বৈধ Mobile Number পাওয়া যায়নি।']);
        }

        try {
            $gatewayResponse = (new SourceCtrl())->sms_send(implode(',', $recipients), $message);
            if ($gatewayResponse === false || $gatewayResponse === null || $gatewayResponse === '') {
                return back()->withInput()->withErrors(['details' => 'SMS Gateway থেকে কোনো Response পাওয়া যায়নি। Gateway Configuration পরীক্ষা করুন।']);
            }

            $now = now('Asia/Dhaka');
            DB::table('sms')->insert([
                'title' => 'Single SMS',
                'batch_id' => json_encode([]),
                'student_id' => json_encode($recipientIds),
                'details' => $message,
                'created_at' => $now,
                'updated_at' => $now,
                'sms_send_by' => Auth::id(),
                'status' => 'Sent',
                'datetime' => $now,
                'start_date' => null,
                'end_date' => null,
                'is_active' => 1,
                'sms_workflow' => 'single',
                'sms_type' => 'Single SMS',
                'routine_ids' => json_encode([]),
                'exam_ids' => json_encode([]),
            ]);
        } catch (\Throwable $exception) {
            report($exception);
            return back()->withInput()->withErrors(['details' => 'SMS পাঠানো যায়নি। SMS Gateway/API Configuration পরীক্ষা করে আবার চেষ্টা করুন।']);
        }

        $skipped = max(0, $students->count() - count($recipientIds));
        $notice = count($recipientIds) . ' জন Student-এর নম্বরে SMS Gateway-তে পাঠানো হয়েছে।';
        if ($skipped) {
            $notice .= ' বৈধ Mobile Number না থাকায় ' . $skipped . ' জন বাদ গেছে।';
        }

        return redirect()->route('sms.single.index')->with('success', $notice);
    }
}
