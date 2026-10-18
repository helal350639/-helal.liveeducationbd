<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DailyExam;
use App\Models\DailyExamQuestion;
use Illuminate\Http\Request;


class DailyExamQuestionController extends Controller
{


    public function create($id)
    {

        $dailyExam = DailyExam::findOrFail($id);


        $papers = $dailyExam->papers()->get();



        return view('admin.daily-exam.question-manual-create',
            compact(
                'dailyExam',
                'papers'
            ));

    }





    public function store(Request $request)
    {


        $data = $request->validate([

            'daily_exam_id' => 'required',

            'paper_id' => 'required',

            'question' => 'required',

            'option_a' => 'required',

            'option_b' => 'required',

            'option_c' => 'required',

            'option_d' => 'required',

            'correct_answer' => 'required',

            'mark' => 'required',

        ]);



        DailyExamQuestion::create($data);



        return redirect()

            ->route('admin.daily-exam.show',
            $request->daily_exam_id)

            ->with('success',
            'Question Added Successfully');


    }






    public function edit($id)
    {

        $question = DailyExamQuestion::findOrFail($id);


        $dailyExam = $question->dailyExam;


        $papers = $dailyExam->papers()->get();



        return view('admin.daily-exam.question-edit',
            compact(
                'question',
                'dailyExam',
                'papers'
            ));

    }







    public function update(Request $request, $id)
    {

        $question = DailyExamQuestion::findOrFail($id);



        $data = $request->validate([

            'paper_id' => 'required',

            'question' => 'required',

            'option_a' => 'required',

            'option_b' => 'required',

            'option_c' => 'required',

            'option_d' => 'required',

            'correct_answer' => 'required',

            'mark' => 'required',

        ]);



        $question->update($data);



        return redirect()

            ->route('admin.daily-exam.show',
            $question->daily_exam_id)

            ->with('success',
            'Question Updated Successfully');


    }







    public function destroy($id)
    {

        $question = DailyExamQuestion::findOrFail($id);



        $question->delete();



        return back()

            ->with('success',
            'Question Deleted Successfully');

    }



}