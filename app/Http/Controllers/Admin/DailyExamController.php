<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DailyExam;
use Illuminate\Http\Request;


class DailyExamController extends Controller
{


    public function index()
    {
        $dailyExams = DailyExam::all();

        return view('admin.daily-exam.index', compact('dailyExams'));
    }



    public function create()
    {
        return view('admin.daily-exam.create');
    }



    public function store(Request $request)
    {

        DailyExam::create($request->all());


        return redirect()
            ->route('admin.daily-exam.index')
            ->with('success','Daily Exam Created Successfully');

    }





public function show($id)
{

    $dailyExam = DailyExam::findOrFail($id);


    $questions = $dailyExam->questions()->get();


    $papers = $dailyExam->papers()->get();



    return view('admin.daily-exam.show',
        compact(
            'dailyExam',
            'questions',
            'papers'
        ));

}



    public function edit($id)
    {

        $dailyExam = DailyExam::findOrFail($id);


        return view('admin.daily-exam.edit',
            compact('dailyExam'));

    }





    public function update(Request $request, $id)
    {

        $dailyExam = DailyExam::findOrFail($id);


        $dailyExam->update($request->all());


        return redirect()
            ->route('admin.daily-exam.index')
            ->with('success','Daily Exam Updated Successfully');

    }





    // Question Add Option Page

    public function questionCreate($id)
    {

        $dailyExam = DailyExam::findOrFail($id);


        return view('admin.daily-exam.question-option',
            compact('dailyExam'));

    }





    // Manual Question Create Page

    public function manualQuestionCreate($id)
    {

        $dailyExam = DailyExam::findOrFail($id);


        return view('admin.daily-exam.question-manual-create',
            compact('dailyExam'));

    }



}