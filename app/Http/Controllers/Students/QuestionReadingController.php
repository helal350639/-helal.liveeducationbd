<?php

namespace App\Http\Controllers\Students;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\QuestionComment;
use App\Models\QuestionReaction;
use App\Models\QuestionRating;
use App\Models\QuestionReading;
use DB;

class QuestionReadingController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth:student');
  }

  public function index(Request $request)
  {
    $data = $request->validate([
      'chapter_id' => 'nullable|numeric',
      'source' => 'nullable|string',
      'sub-source' => 'nullable|string',
      'year' => 'nullable|numeric',
      'reaction' => 'nullable'
    ]);
  }

  public function reactStore(Request $request)
  {
    //validation
    $data = $request->validate([
      'question_id' => 'required|numeric',
      'reaction' => 'required|string'
    ]);

    if(isset($data['_token']))
    {
      unset($data['_token']);
    }

    $data['student_id'] = auth()->guard('student')->id();
    
    try {
      $react = QuestionReaction::updateOrCreate(
        [
          'question_id' => (int) $data['question_id'],
          'student_id' => (int) $data['student_id'],
        ],
        [
          'reaction' => $data['reaction']
        ]
      );

      return response()->json([
        'react' => $react
      ], 200);
    }
    catch(\Exception $e)
    {
      return $e->getMessage();
    }
  }

  public function reactShow($question_id)
  {
    $student_id = auth()->guard('student')->id();
    $react = QuestionReaction::orderBy('created_at', 'DESC')
    ->where('question_id', $question_id)
    ->where('student_id', $student_id)
    ->first();

    return response()->json([
      'react' => $react
    ], 200);
  }

  public function commentStore(Request $request)
  {
    //validation
    $data = $request->validate([
      'question_id' => 'required|numeric',
      'comment' => 'required|string'
    ]);

    if(isset($data['_token']))
    {
      unset($data['_token']);
    }

    $data['student_id'] = auth()->guard('student')->id();

    try {
      $comment = QuestionComment::create($data);

      return response()->json([
        'comment' => $comment
      ], 200);
    }
    catch(\Exception $e)
    {
      return $e->getMessage();
    }
  }

  public function commentShow($question_id)
  {
    $student_id = auth()->guard('student')->id();
    $comment = QuestionComment::orderBy('created_at', 'DESC')
    ->where('question_id', $question_id)
    ->where('student_id', $student_id)
    ->get();

    return response()->json([
      'comment' => $comment
    ], 200);
  }

  public function rateStore(Request $request)
  {
    //validation
    $data = $request->validate([
      'question_id' => 'required|numeric',
      'rating' => 'required|string'
    ]);

    if(isset($data['_token']))
    {
      unset($data['_token']);
    }

    $data['student_id'] = auth()->guard('student')->id();

    try {
      $rate = QuestionRating::updateOrCreate(
        [
          'question_id' => $data['question_id'],
          'student_id' => $data['student_id']
        ],
        [
          'rating' => $data['rating']
        ]);

      return response()->json([
        'rate' => $rate
      ], 200);
    }
    catch(\Exception $e)
    {
      return $e->getMessage();
    }
  }

  public function rateShow($question_id)
  {
    $student_id = auth()->guard('student')->id();
    $rate = QuestionRating::orderBy('created_at', 'DESC')
    ->where('question_id', $question_id)
    ->where('student_id', $student_id)
    ->first();

    return response()->json([
      'rate' => $rate
    ], 200);
  }

  public function readingStore(Request $request)
  {
    //validation
    $data = $request->validate([
      'question_id' => 'required|numeric',
      'reading' => 'required|string'
    ]);

    if(isset($data['_token']))
    {
      unset($data['_token']);
    }

    $data['student_id'] = auth()->guard('student')->id();

    try {
      $rate = QuestionReading::updateOrCreate(
        [
          'question_id' => $data['question_id'],
          'student_id' => $data['student_id']
        ],
        [
          'reading' => $data['reading']
        ]);

      return response()->json([
        'rate' => $rate
      ], 200);
    }
    catch(\Exception $e)
    {
      return $e->getMessage();
    }
  }

  public function readingShow($question_id)
  {
    $student_id = auth()->guard('student')->id();
    $reading = QuestionReading::orderBy('created_at', 'DESC')
    ->where('question_id', $question_id)
    ->where('student_id', $student_id)
    ->first();

    return response()->json([
      'reading' => $reading
    ], 200);
  }
}
