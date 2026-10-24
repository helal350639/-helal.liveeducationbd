<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Course;
use App\Models\Department;
use App\Models\Batch;
use App\Models\Group;
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

class RoutineCtrl extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }


    /**
     * Display all routines.
     */
    public function index()
    {
        $routines = Routine::orderBy('id', 'DESC')
            ->paginate(25);

        return view(
            'layouts.routines.index',
            compact('routines')
        );
    }


    /**
     * Show create routine page.
     */
    public function create()
    {
        $courses = Course::orderBy('id', 'DESC')
            ->where('status', 'Active')
            ->get();

        $batches = Batch::where('status', 'Active')
            ->get();

        $subjects = Subject::where('status', 'Active')
            ->get();

        $departments = Department::where('status', 'Active')
            ->get();

        return view(
            'layouts.routines.create',
            compact(
                'courses',
                'batches',
                'departments',
                'subjects'
            )
        );
    }


    /**
     * Store new routine.
     */
    public function store(Request $request)
    {
        $source = new SourceCtrl;

        $this->validate($request, [

            'course_id' => 'required|numeric',

            'name' => 'required|unique:routines|max:255',

            'header' => 'nullable|string',

            'details' => 'nullable|max:1000',

            'pdf' => 'nullable|mimes:pdf|max:10000',

            'subjects' => 'nullable|array',

            'departments' => 'nullable|array',

        ]);


        $data = $request->all();


        /*
        |--------------------------------------------------------------------------
        | Remove token
        |--------------------------------------------------------------------------
        */

        unset($data['_token']);


        /*
        |--------------------------------------------------------------------------
        | Status
        |--------------------------------------------------------------------------
        */

        $data['status'] = isset($data['status'])
            ? 'Published'
            : 'Unpublished';


        /*
        |--------------------------------------------------------------------------
        | Created By
        |--------------------------------------------------------------------------
        */

        $data['created_by'] = Auth::id();


        /*
        |--------------------------------------------------------------------------
        | Subjects & Departments
        |--------------------------------------------------------------------------
        */

        $subjects = $data['subjects'] ?? [];
        $departments = $data['departments'] ?? [];
        $exam_names = $data['exam_names'] ?? [];

        unset($data['subjects']);
        unset($data['departments']);
        unset($data['exam_names']);


        try {

            /*
            |--------------------------------------------------------------------------
            | Upload PDF
            |--------------------------------------------------------------------------
            */

            if ($request->hasFile('pdf')) {

                $data['pdf'] = $source->uploadImage(
                    $data['pdf'],
                    'routine/'
                );

            }


            /*
            |--------------------------------------------------------------------------
            | Create Routine
            |--------------------------------------------------------------------------
            */

            $routine = Routine::create($data);


            /*
            |--------------------------------------------------------------------------
            | Attach Subjects
            |--------------------------------------------------------------------------
            */

            if (!empty($subjects)) {

                $routine->subjects()->attach($subjects);


                foreach ($subjects as $subject) {

                    DB::table('routine_subject_columns')
                        ->insert([

                            'routine_id' => $routine->id,

                            'subject_id' => $subject

                        ]);

                }

            }


            /*
            |--------------------------------------------------------------------------
            | Attach Departments
            |--------------------------------------------------------------------------
            */

            if (!empty($departments)) {

                $routine->departments()->attach($departments);


                foreach ($departments as $department) {

                    DB::table('routine_department_columns')
                        ->insert([

                            'routine_id' => $routine->id,

                            'department_id' => $department

                        ]);

                }

            }


        } catch (\Exception $e) {

            return $e->getMessage();

        }


        Session::flash(
            'success',
            'New routine successfully created!'
        );


        return redirect()->route(
            'routine.index'
        );
    }


    /**
     * Display specified routine.
     */
    public function show($id)
    {
        //
    }


    /**
     * Show edit routine page.
     */
    public function edit($id)
    {
        $courses = Course::where(
            'status',
            'Active'
        )->get();


        $batches = Batch::where(
            'status',
            'Active'
        )->get();


        $subjects = Subject::where(
            'status',
            'Active'
        )->get();


        $departments = Department::where(
            'status',
            'Active'
        )->get();


        $routine = Routine::findOrFail($id);


        $subject_columns = $routine
            ->subjects()
            ->pluck('subject_id')
            ->toArray();


        $department_columns = $routine
            ->departments()
            ->pluck('department_id')
            ->toArray();


        return view(
            'layouts.routines.edit',
            compact(
                'routine',
                'courses',
                'departments',
                'batches',
                'subjects',
                'subject_columns',
                'department_columns'
            )
        );
    }






    /**
 * Update routine.
 */
public function update(Request $request, $id)
{
    $source = new SourceCtrl;

    $this->validate($request, [

        'course_id' => 'required|numeric',

        'name' => 'required|max:255',

        'header' => 'nullable|string',

        'details' => 'nullable|max:1000',

        'pdf' => 'nullable|mimes:pdf|max:10000',

        'subjects' => 'nullable|array',

        'departments' => 'nullable|array',

    ]);


    DB::beginTransaction();


    try {


        /*
        |--------------------------------------------------------------------------
        | Get Routine
        |--------------------------------------------------------------------------
        */

        $routine = Routine::findOrFail($id);


        /*
        |--------------------------------------------------------------------------
        | Main Routine Data
        |--------------------------------------------------------------------------
        */

        $data = $request->all();


        unset($data['_token']);
        unset($data['_method']);


        /*
        |--------------------------------------------------------------------------
        | Status
        |--------------------------------------------------------------------------
        */

        $data['status'] = isset($data['status'])
            ? 'Published'
            : 'Unpublished';


        /*
        |--------------------------------------------------------------------------
        | Subjects / Departments / Exams
        |--------------------------------------------------------------------------
        */

        $subjects = $data['subjects'] ?? [];

        $departments = $data['departments'] ?? [];

        $exam_names = $data['exam_names'] ?? [];


        unset($data['subjects']);

        unset($data['departments']);

        unset($data['exam_names']);


        /*
        |--------------------------------------------------------------------------
        | Created By Must Not Change
        |--------------------------------------------------------------------------
        */

        unset($data['created_by']);


        /*
        |--------------------------------------------------------------------------
        | PDF
        |--------------------------------------------------------------------------
        */

        if ($request->hasFile('pdf')) {

            $data['pdf'] = $source->uploadImage(
                $request->file('pdf'),
                'routine/'
            );

        }
        else {

            unset($data['pdf']);

        }


        /*
        |--------------------------------------------------------------------------
        | Update Main Routine
        |--------------------------------------------------------------------------
        */

       $routine->course_id = $request->course_id;
        $routine->batch_id = $request->batch_id;
$routine->name = $request->name;
$routine->header = $request->header;
$routine->details = $request->details;

$routine->status = isset($request->status)
    ? 'Published'
    : 'Unpublished';

$routine->save();


        /*
        |--------------------------------------------------------------------------
        | Update Subjects
        |--------------------------------------------------------------------------
        */

        $routine->subjects()->sync($subjects);


        DB::table(
            'routine_subject_columns'
        )
        ->where(
            'routine_id',
            $routine->id
        )
        ->delete();


        foreach ($subjects as $subject) {

            DB::table(
                'routine_subject_columns'
            )
            ->insert([

                'routine_id' => $routine->id,

                'subject_id' => $subject

            ]);

        }


        /*
        |--------------------------------------------------------------------------
        | Update Departments
        |--------------------------------------------------------------------------
        */

        $routine->departments()->sync($departments);


        DB::table(
            'routine_department_columns'
        )
        ->where(
            'routine_id',
            $routine->id
        )
        ->delete();


        foreach ($departments as $department) {

            DB::table(
                'routine_department_columns'
            )
            ->insert([

                'routine_id' => $routine->id,

                'department_id' => $department

            ]);

        }


        /*
        |--------------------------------------------------------------------------
        | Update Exam Names
        |--------------------------------------------------------------------------
        */

        RoutineExam::where(
            'routine_id',
            $routine->id
        )->delete();


        foreach ($exam_names as $key => $exam_name) {

            if (
                !empty($exam_name)
            ) {

                RoutineExam::create([

                    'routine_id' => $routine->id,

                    'name' => $exam_name,

                    'sort_order' => $key + 1,

                ]);

            }

        }


        /*
        |--------------------------------------------------------------------------
        | Commit
        |--------------------------------------------------------------------------
        */

        DB::commit();


        Session::flash(
            'success',
            'Routine successfully updated!'
        );


        /*
        |--------------------------------------------------------------------------
        | Correct Redirect
        |--------------------------------------------------------------------------
        */

        return redirect()->route(
            'routine.index'
        );


    }
    catch (\Exception $e)
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







    public function copy($id)
{

    DB::beginTransaction();

    try
    {

        $routine = Routine::findOrFail($id);



        /*
        |--------------------------------------------------------------------------
        | Copy Main Routine
        |--------------------------------------------------------------------------
        */

        $newRoutine = $routine->replicate();

        $newRoutine->name =
            $routine->name . ' - Copy';

        $newRoutine->save();





        /*
        |--------------------------------------------------------------------------
        | Copy Subjects
        |--------------------------------------------------------------------------
        */

        foreach($routine->subjects as $subject)
        {

            $newRoutine->subjects()->attach(
                $subject->id
            );


            DB::table('routine_subject_columns')
            ->insert([

                'routine_id' => $newRoutine->id,

                'subject_id' => $subject->id,

            ]);

        }






        /*
        |--------------------------------------------------------------------------
        | Copy Departments
        |--------------------------------------------------------------------------
        */

        foreach($routine->departments as $department)
        {

            $newRoutine->departments()->attach(
                $department->id
            );


            DB::table('routine_department_columns')
            ->insert([

                'routine_id' => $newRoutine->id,

                'department_id' => $department->id,

            ]);

        }






        /*
        |--------------------------------------------------------------------------
        | Copy Exam Names
        |--------------------------------------------------------------------------
        */

        foreach($routine->exams as $exam)
        {

            RoutineExam::create([

                'routine_id' => $newRoutine->id,

                'name' => $exam->name,

                'sort_order' => $exam->sort_order,

            ]);

        }







        /*
        |--------------------------------------------------------------------------
        | Copy Routine Items
        |--------------------------------------------------------------------------
        */


        $items = RoutineItemRow::where(

            'routine_id',

            $routine->id

        )->get();




        foreach($items as $item)
        {


            $newItem = RoutineItemRow::create([

                'routine_id' => $newRoutine->id,

                'paper_name' => $item->paper_name,

                'exam_at' => $item->exam_at,

            ]);







            /*
            |--------------------------------------------------------------------------
            | Copy Subject Rows
            |--------------------------------------------------------------------------
            */


            $subjectRows = RoutineSubjectRow::where(

                'item_id',

                $item->id

            )->get();



            foreach($subjectRows as $subjectRow)
            {


                RoutineSubjectRow::create([

    'item_id' => $newItem->id,

    'subject_id' => $subjectRow->subject_id,

    'chapter' => $subjectRow->chapter,

]);

            }








            /*
            |--------------------------------------------------------------------------
            | Copy Department Rows
            |--------------------------------------------------------------------------
            */


            $departmentRows = RoutineDepartmentRow::where(

                'item_id',

                $item->id

            )->get();



            foreach($departmentRows as $departmentRow)
            {


                RoutineDepartmentRow::create([

                    'item_id' => $newItem->id,

                    'department_id' => $departmentRow->department_id,

                    'details' => $departmentRow->details,

                ]);

            }


        }






        DB::commit();



        return redirect()

            ->route(

                'routine.item.show',

                $newRoutine->id

            )

            ->with(

                'success',

                'Routine copied successfully'

            );


    }
    catch(\Exception $e)
    {

        DB::rollBack();


        return $e->getMessage();

    }

}   


    /**
     * Delete routine.
     */
    public function destroy($id)
    {
        if (
            Auth::user()->AuthorizeRoles(
                'SuperAdmin'
            )
        ) {

            $routine = Routine::findOrFail($id);


            /*
            |--------------------------------------------------------------------------
            | Delete PDF
            |--------------------------------------------------------------------------
            */

            if ($routine->pdf) {

                $xpdf = public_path(
                    $routine->pdf
                );


                if (File::exists($xpdf)) {

                    File::delete($xpdf);

                }

            }


            /*
            |--------------------------------------------------------------------------
            | Delete Routine
            |--------------------------------------------------------------------------
            */

            $routine->delete();


            Session::flash(
                'success',
                'The routine successfully deleted'
            );


            return redirect()->route(
                'routine.index'
            );
        }


        return back()->with(
            'error',
            'Permission Denied'
        );
    }
}