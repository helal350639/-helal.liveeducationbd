<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DailyExam;
use App\Models\DailyExamPaper;
use Illuminate\Http\Request;


class DailyExamPaperController extends Controller
{


    public function create($id)
    {

        $dailyExam = DailyExam::findOrFail($id);


        return view('admin.daily-exam.paper-create',
            compact('dailyExam'));

    }





    public function store(Request $request)
    {


        $data = $request->validate([

            'daily_exam_id' => 'required',

            'paper_name' => 'required',

            'description' => 'nullable',

            'status' => 'nullable',

        ]);



        DailyExamPaper::create($data);



        return redirect()

            ->route('admin.daily-exam.show',
            $request->daily_exam_id)

            ->with('success',
            'Question Paper Created Successfully');


    }





    public function edit($id)
    {

        $paper = DailyExamPaper::findOrFail($id);


        return view('admin.daily-exam.paper-edit',
            compact('paper'));

    }





    public function update(Request $request, $id)
    {

        $paper = DailyExamPaper::findOrFail($id);



        $data = $request->validate([

            'paper_name' => 'required',

            'description' => 'nullable',

            'status' => 'required',

        ]);



        $paper->update($data);



        return redirect()

            ->route('admin.daily-exam.show',
            $paper->daily_exam_id)

            ->with('success',
            'Question Paper Updated Successfully');

    }





    public function destroy($id)
    {

        $paper = DailyExamPaper::findOrFail($id);


        $paper->delete();



        return back()

            ->with('success',
            'Question Paper Deleted Successfully');

    }



}