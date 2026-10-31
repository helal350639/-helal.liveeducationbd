<?php
namespace App\Http\Controllers\Students;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MyBatchExamIndexController extends Controller
{
    public function index(Request $request)
    {
        $student = Auth::guard('student')->user();
        abort_unless($student, 403);
        // Only the authenticated student's attached/enrolled batches; never the public catalog.
        // Existing LEBD purchase flows grant access through batch_student.
        $batches = $student->batches()->orderByDesc('batches.id')->get()->unique('id')->values();
        return view('student-panel.my-batch-exam-index', compact('batches'));
    }
}
