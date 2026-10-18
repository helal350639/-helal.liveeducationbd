<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Course;
use App\Models\Department;
use App\Models\Batch;
use App\Models\Routine;
use App\Models\RoutineExam;
use App\Models\RoutineItem;
use App\Models\Role;
use App\Models\Paper;
use App\Models\Subject;
use App\Models\RoutineSubjectColumn;
use App\Models\RoutineDepartmentColumn;
use App\Models\RoutineItemRow;
use App\Models\RoutineSubjectRow;
use App\Models\RoutineDepartmentRow;

use Auth;
use Image;
use Toastr;
use File;
use Session;
use DB;


class RoutineItemCtrl extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }


    /*
    |--------------------------------------------------------------------------
    | ADD ROUTINE ITEM
    |--------------------------------------------------------------------------
    */

    public function addItem($id)
    {

        $routine = Routine::findOrFail($id);

        $papers = Paper::orderBy('id', 'DESC')->get();

        $subjects = $routine->subjects()->get();

        $departments = $routine->departments()->get();


        /*
        |--------------------------------------------------------------------------
        | Get Already Used Exam Names
        |--------------------------------------------------------------------------
        */

        $usedExamNames = RoutineItemRow::where(
            'routine_id',
            $id
        )
        ->pluck('paper_name')
        ->toArray();


        /*
        |--------------------------------------------------------------------------
        | Get Only Unused Exam Names
        |--------------------------------------------------------------------------
        */

        $exams = $routine->exams()
            ->whereNotIn('name', $usedExamNames)
            ->orderBy('sort_order', 'ASC')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | Existing Routine Rows
        |--------------------------------------------------------------------------
        */

        $routine_rows = RoutineItemRow::where(
            'routine_item_rows.routine_id',
            $id
        )
        ->with([
            'subjects',
            'departments'
        ])
        ->select(
            'routine_item_rows.*'
        )
        ->get();


        return view(
            'layouts.routines.add-item',
            compact(
                'routine',
                'papers',
                'subjects',
                'departments',
                'routine_rows',
                'exams'
            )
        );
    }


    /*
|--------------------------------------------------------------------------
| STORE ROUTINE ITEM
|--------------------------------------------------------------------------
*/

public function addItemPost(Request $request)
{
    $data = $request->validate([
        'routine_id' => 'required|integer',
        'paper_name' => 'required|string|max:255',
        'exam_at' => 'required|date',
        'exam_end_at' => 'nullable|date|after_or_equal:exam_at',

        'subject_id' => 'required|array',
        'subject_item' => 'required|array',

        'department_id' => 'required|array',
        'department_item' => 'required|array',
    ]);

    try {

        /*
        |--------------------------------------------------------------------------
        | Check Exam Is Already Used
        |--------------------------------------------------------------------------
        */

        $alreadyUsed = RoutineItemRow::where(
            'routine_id',
            $data['routine_id']
        )
        ->where(
            'paper_name',
            $data['paper_name']
        )
        ->exists();

        if ($alreadyUsed) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    'This Exam has already been used in this Routine.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Create Main Routine Item
        |--------------------------------------------------------------------------
        */

        $routineItem = new RoutineItemRow();

        $routineItem->routine_id = $data['routine_id'];
        $routineItem->paper_name = $data['paper_name'];
        $routineItem->exam_at = $data['exam_at'];
        $routineItem->exam_end_at = $data['exam_end_at'] ?? null;

        $routineItem->save();

        /*
        |--------------------------------------------------------------------------
        | Save Subjects
        |--------------------------------------------------------------------------
        */

        foreach ($data['subject_id'] as $key => $value) {

            RoutineSubjectRow::insert([
                'item_id' => $routineItem->id,
                'subject_id' => $value,
                'chapter' => $data['subject_item'][$key] ?? '',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Save Departments
        |--------------------------------------------------------------------------
        */

        foreach ($data['department_id'] as $key => $value) {

            RoutineDepartmentRow::insert([
                'item_id' => $routineItem->id,
                'department_id' => $value,
                'details' => $data['department_item'][$key] ?? '',
            ]);
        }

    } catch (\Exception $e) {

        return back()
            ->withInput()
            ->with('error', $e->getMessage());
    }

    Session::flash(
        'success',
        'Routine item successfully added.'
    );

    return redirect()->route(
        'routine.item.show',
        $data['routine_id']
    );
}


    /*
    |--------------------------------------------------------------------------
    | SHOW ROUTINE ITEMS
    |--------------------------------------------------------------------------
    */

    public function showItem($id)
    {

        $routine =
            Routine::findOrFail($id);


        $papers =
            Paper::orderBy(
                'id',
                'DESC'
            )->get();


        $subjects =
            $routine->subjects()->get();


        $departments =
            $routine->departments()->get();


        /*
        |--------------------------------------------------------------------------
        | Exam Names
        |--------------------------------------------------------------------------
        */

        $exams =
            $routine->exams()
                ->orderBy(
                    'sort_order',
                    'ASC'
                )
                ->get();


        /*
        |--------------------------------------------------------------------------
        | Routine Rows
        |--------------------------------------------------------------------------
        */

        $routine_rows =
            RoutineItemRow::where(
                'routine_item_rows.routine_id',
                $id
            )
            ->with([
                'subjects',
                'departments'
            ])
            ->select(
                'routine_item_rows.*'
            )
            ->get();


        return view(
            'layouts.routines.show-item',
            compact(
                'routine',
                'papers',
                'subjects',
                'departments',
                'routine_rows',
                'exams'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | EDIT ROUTINE ITEM
    |--------------------------------------------------------------------------
    */

    public function editItem($id)
    {

        $routine_row =
            RoutineItemRow::findOrFail($id);


        $routine =
            Routine::findOrFail(
                $routine_row->routine_id
            );


        $papers =
            Paper::orderBy(
                'id',
                'DESC'
            )->get();


        $subjects =
            $routine->subjects()->get();


        $departments =
            $routine->departments()->get();


        /*
        |--------------------------------------------------------------------------
        | Get Exams Already Used By OTHER Items
        |--------------------------------------------------------------------------
        */

        $usedExamNames =
            RoutineItemRow::where(
                'routine_id',
                $routine_row->routine_id
            )
            ->where(
                'id',
                '!=',
                $routine_row->id
            )
            ->pluck(
                'paper_name'
            )
            ->toArray();


        /*
        |--------------------------------------------------------------------------
        | Current Exam + Unused Exams
        |--------------------------------------------------------------------------
        */

        $exams =
            $routine->exams()
                ->whereNotIn(
                    'name',
                    $usedExamNames
                )
                ->orderBy(
                    'sort_order',
                    'ASC'
                )
                ->get();


        /*
        |--------------------------------------------------------------------------
        | Subject Rows
        |--------------------------------------------------------------------------
        */

        $subject_rows =
            RoutineSubjectRow::leftJoin(
                'subjects',
                'subjects.id',
                '=',
                'routine_subject_rows.subject_id'
            )
            ->where(
                'routine_subject_rows.item_id',
                $id
            )
            ->select(
                'routine_subject_rows.*',
                'subjects.name'
            )
            ->get();


        /*
        |--------------------------------------------------------------------------
        | Department Rows
        |--------------------------------------------------------------------------
        */

        $department_rows =
            RoutineDepartmentRow::leftJoin(
                'departments',
                'departments.id',
                '=',
                'routine_department_rows.department_id'
            )
            ->where(
                'routine_department_rows.item_id',
                $id
            )
            ->select(
                'routine_department_rows.*',
                'departments.name'
            )
            ->get();


        /*
        |--------------------------------------------------------------------------
        | All Routine Rows
        |--------------------------------------------------------------------------
        */

        $routine_rows =
            RoutineItemRow::where(
                'routine_id',
                $routine_row->routine_id
            )
            ->get();


        return view(
            'layouts.routines.edit-item',
            compact(
                'routine',
                'papers',
                'subjects',
                'departments',
                'routine_rows',
                'routine_row',
                'subject_rows',
                'department_rows',
                'exams'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE ROUTINE ITEM
    |--------------------------------------------------------------------------
    */

    public function update(Request $request, $id)
{

    $request->validate([

        'routine_id' => 'required|integer',

        'paper_name' => 'required|string|max:255',

        'exam_at' => 'required|date',
        'exam_end_at' => 'nullable|date|after_or_equal:exam_at',

        'subject_id' => 'required|array',

        'subject_item' => 'required|array',

        'department_id' => 'required|array',

        'department_item' => 'required|array',

    ]);



    DB::beginTransaction();


    try {


        /*
        |--------------------------------------------------------------------------
        | Update Main Item
        |--------------------------------------------------------------------------
        */

        RoutineItemRow::where(
            'id',
            $id
        )
        ->update([

            'routine_id' => $request->routine_id,
            'paper_name' => $request->paper_name,
            'exam_at' => $request->exam_at,
            'exam_end_at' => $request->exam_end_at,

        ]);





        /*
        |--------------------------------------------------------------------------
        | Replace Subject Data
        |--------------------------------------------------------------------------
        */

        RoutineSubjectRow::where(
            'item_id',
            $id
        )->delete();



        foreach($request->subject_id as $key=>$subject_id)
        {

            RoutineSubjectRow::create([

                'item_id'=>$id,

                'subject_id'=>$subject_id,

                'chapter'=>$request->subject_item[$key] ?? '',

            ]);

        }





        /*
        |--------------------------------------------------------------------------
        | Replace Department Data
        |--------------------------------------------------------------------------
        */


        RoutineDepartmentRow::where(
            'item_id',
            $id
        )->delete();



        foreach($request->department_id as $key=>$department_id)
        {

            RoutineDepartmentRow::create([

                'item_id'=>$id,

                'department_id'=>$department_id,

                'details'=>$request->department_item[$key] ?? '',

            ]);

        }




        DB::commit();



        Session::flash(
            'success',
            'Routine item successfully updated.'
        );



        return redirect()->route(

            'routine.item.show',

            $request->routine_id

        );



    }
    catch(\Exception $e)
    {

        DB::rollBack();


        return back()

        ->withInput()

        ->with(

            'error',

            $e->getMessage()

        );

    }

}


    /*
    |--------------------------------------------------------------------------
    | UPDATE ONLY DATE & TIME
    |--------------------------------------------------------------------------
    */

    public function updateExamAt(
        Request $request,
        $id
    ) {

        try {

            $request->validate([

                'exam_at' =>
                    'required|date',

            ]);


            $item =
                RoutineItemRow::findOrFail(
                    $id
                );


            $item->exam_at =
                $request->exam_at;


            $item->save();


            return response()->json([

                'success' =>
                    true,

                'message' =>
                    'Date & Time successfully updated.',

                'exam_at' =>
                    $item->exam_at,

            ]);


        } catch (\Exception $e) {

            return response()->json([

                'success' =>
                    false,

                'message' =>
                    $e->getMessage(),

            ], 500);

        }
    }

    /*
|--------------------------------------------------------------------------
| UPDATE ONLY EXAM END DATE & TIME
|--------------------------------------------------------------------------
*/

public function updateExamEnd(Request $request, $id)
{

    try {

        $request->validate([

            'exam_end_at' => 'required|date'

        ]);


        $item = RoutineItemRow::findOrFail($id);


        $item->exam_end_at = $request->exam_end_at;


        $item->save();


        return response()->json([

            'success' => true,

            'message' => 'Exam End Date successfully updated.',

            'exam_end_at' => $item->exam_end_at

        ]);


    } catch (\Exception $e) {


        return response()->json([

            'success' => false,

            'message' => $e->getMessage()

        ],500);

    }

}

    /*
    |--------------------------------------------------------------------------
    | DELETE ROUTINE ITEM
    |--------------------------------------------------------------------------
    */

    public function deleteItem($id)
    {

        if (
            Auth::user()->AuthorizeRoles(
                'SuperAdmin'
            )
        ) {


            $item_row =
                RoutineItemRow::findOrFail(
                    $id
                );


            /*
            |--------------------------------------------------------------------------
            | Delete Subject Rows
            |--------------------------------------------------------------------------
            */

            $subject_rows =
                RoutineSubjectRow::where(
                    'item_id',
                    $id
                )->get();


            foreach (
                $subject_rows
                as $row
            ) {

                $row->delete();

            }


            /*
            |--------------------------------------------------------------------------
            | Delete Department Rows
            |--------------------------------------------------------------------------
            */

            $department_rows =
                RoutineDepartmentRow::where(
                    'item_id',
                    $id
                )->get();


            foreach (
                $department_rows
                as $row
            ) {

                $row->delete();

            }


            /*
            |--------------------------------------------------------------------------
            | Delete Main Row
            |--------------------------------------------------------------------------
            */

            $item_row->delete();


            Session::flash(
                'success',
                'The item deleted'
            );


            return back();
        }


        return back()->with(
            'error',
            'Permission Denied'
        );
    }

}