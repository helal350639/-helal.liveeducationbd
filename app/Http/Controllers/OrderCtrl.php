<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Filter;
use App\Models\SubFilter;
use App\Models\Role;
use App\Models\Order;
use Auth;
use Image;
use File;
use Session;
use Validator;

class OrderCtrl extends Controller
{
  public function __construct()
  {
    $this->middleware('auth');
  }

  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */

  public function index()
  {
    $orders = Order::leftJoin('batches', 'batches.id', 'orders.batch_id')
    ->leftJoin('departments', 'departments.id', 'orders.department_id')
    ->leftJoin('students', 'students.id', 'orders.student_id')
    ->leftJoin('batch_student', function($join){
      $join->on('batch_student.student_id', 'students.id');
    })
    ->orderBy('id','desc')
    ->select('orders.*',
    'batches.name as batch_name',
    'departments.name as department_name',
    'students.name as student_name',
    'batch_student.student_id as batch_has_student'
    )
    ->paginate(25);
    return view('layouts.orders.index', compact('orders'));
  }

  public function destroy($id)
  {
      $file = Order::find($id);
      $file->delete();

      Session::flash('success', 'The order item successfully deleted!');
      return redirect()->route('order.index');
  }
}