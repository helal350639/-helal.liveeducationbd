<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Course;
use App\Models\Department;
use App\Models\Batch;
use App\Models\Routine;
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


  /** ================== Add Item Section =================== */

  public function addItem($id)
  {
    $routine = Routine::find($id);
    $papers = Paper::orderBy('id', 'DESC')->get();

    $subjects = $routine->subjects()->get();

    $departments = $routine->departments()->get();

    $routine_rows = RoutineItemRow::where('routine_item_rows.routine_id', $id)
    ->with(['subjects', 'departments'])
    ->select('routine_item_rows.*')
    ->get();

    return view('layouts.routines.add-item', compact('routine', 'papers', 'subjects', 'departments', 'routine_rows'));
  }
  
  public function addItemPost(Request $request)
  {
    $data = $request->all();

    try {

      $routineItem = RoutineItemRow::create([
        'routine_id' => $data['routine_id'],
        'paper_name' => $data['paper_name'],
        'exam_at' => $data['exam_at']
      ]);

      foreach($data['subject_id'] as $key => $value)
      {
        RoutineSubjectRow::insert([
          'item_id' => $routineItem->id,
          'subject_id' => $value,
          'chapter' => $data['subject_item'][$key]
        ]);
      }

      foreach($data['department_id'] as $key => $value)
      {
        RoutineDepartmentRow::insert([
          'item_id' => $routineItem->id,
          'department_id' => $value,
          'details' => $data['department_item'][$key]
        ]);
      }
    }
    catch(\Exception $e)
    {
      return $e->getMessage();
    }
    
    return redirect()->route('routine.item.show', $data['routine_id']);
  }

  public function showItem($id)
  {
    $routine = Routine::find($id);
    $papers = Paper::orderBy('id', 'DESC')->get();

    $subjects = $routine->subjects()->get();

    $departments = $routine->departments()->get();

    $routine_rows = RoutineItemRow::where('routine_item_rows.routine_id', $id)
    ->with(['subjects', 'departments'])
    ->select('routine_item_rows.*')
    ->get();

    return view('layouts.routines.show-item', compact('routine', 'papers', 'subjects', 'departments', 'routine_rows'));
  }

  public function editItem($id)
  {
    $routine_row = RoutineItemRow::find($id);

    $routine = Routine::find($routine_row->routine_id);
    $papers = Paper::orderBy('id', 'DESC')->get();

    $subjects = $routine->subjects()->get();

    $departments = $routine->departments()->get();
    
    $subject_rows = RoutineSubjectRow::leftJoin('subjects', 'subjects.id', 'routine_subject_rows.subject_id')
    ->where('routine_subject_rows.item_id', $id)
    ->select('routine_subject_rows.*', 'subjects.name')
    ->get();

    $department_rows = RoutineDepartmentRow::leftJoin('departments', 'departments.id', 'routine_department_rows.department_id')
    ->where('routine_department_rows.item_id', $id)
    ->select('routine_department_rows.*', 'departments.name')
    ->get();

    $routine_rows = RoutineItemRow::where('routine_id', $routine_row->routine_id)
    ->get();

    return view('layouts.routines.edit-item', compact('routine', 'papers', 'subjects', 'departments', 'routine_rows', 'routine_row', 'subject_rows', 'department_rows'));
  }

  public function update(Request $request, $id)
  {
    $data = $request->all();

    try {

      RoutineItemRow::where('id', $id)
      ->update([
        'routine_id' => $data['routine_id'],
        'paper_name' => $data['paper_name'],
        'exam_at' => $data['exam_at']
      ]);

      foreach($data['subject_id'] as $key => $value)
      {
        RoutineSubjectRow::where('item_id', $id)
        ->where('subject_id', $data['subject_id'][$key])
        ->update([
          'chapter' => $data['subject_item'][$key]
        ]);
      }

      foreach($data['department_id'] as $key => $value)
      {
        RoutineDepartmentRow::where('item_id', $id)
        ->where('department_id', $data['department_id'][$key])
        ->update([
          'details' => $data['department_item'][$key]
        ]);
      }
    }
    catch(\Exception $e)
    {
      return $e->getMessage();
    }

    Session::flash('success', 'Routine item successfully updated.');
    return redirect()->route('routine.item.show', $data['routine_id']);
  }


  //delete routine item
  public function deleteItem($id)
  {
    $item_row = RoutineItemRow::find($id);
    $subject_rows =  RoutineSubjectRow::where('item_id', $id)->get();
    $department_rows =  RoutineDepartmentRow::where('item_id', $id)->get();

    if($subject_rows)
    {
      foreach($subject_rows as $row)
      {
        $row->delete();
      }
    }

    if($department_rows)
    {
      foreach($department_rows as $row)
      {
        $row->delete();
      }
    }

    $item_row->delete();

    Session::flash('success', 'The item deleted');
    return back();
  }
}