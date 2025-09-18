<?php

namespace App\Http\Controllers\Teachers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\SourceCtrl;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\Referrer;
use App\Models\Batch;
use Auth;
use Session;
use Mail;

class TeacherHomeCtrl extends Controller
{
  public function __construct()
  {
    $this->middleware('auth:teacher');
  }

  public function dashboard()
  {
    $teacher = Auth::guard('teacher')->user();
    $references = Referrer::orderBy('id', 'DESC')
    ->where('referrer_id', $teacher->id)
    ->paginate(25);

    $courses = Batch::orderBy('id', 'DESC')
    ->whereRaw('DATE(reg_end_at) > ?', date('Y-m-d'))
    ->count();

    $data = [
      'courses' => $courses
    ];
    return view('teachers.dashboard', compact('references', 'data'));
  }

  public function courses()
  {
    $courses = Batch::orderBy('id', 'DESC')
    ->whereRaw('DATE(reg_end_at) > ?', date('Y-m-d'))
    ->get();

    return view('teachers.courses', compact('courses'));
  }
}