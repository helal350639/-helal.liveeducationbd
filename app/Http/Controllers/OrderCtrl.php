<?php

namespace App\Http\Controllers;

use App\Http\Controllers\SourceCtrl;
use Illuminate\Http\Request;
use App\Models\Filter;
use App\Models\SubFilter;
use App\Models\Role;
use App\Models\Order;
use App\Models\Student;
use App\Models\Referrer;
use App\Models\Batch;
use App\Models\Department;
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

  public function index(Request $request)
  {
    $search = $request->input('search');
    $batch = $request->input('batch_id');
    $department = $request->input('department_id');
    $student = $request->input('student_id');
    $status = $request->input('status');
    $start_date = $request->input('start_date');
    $end_date = $request->input('end_date');

    $orders = Order::query()
    ->when($search, function ($query, $search)
    {
      $query->where('orders.name', 'like', "%{$search}%")
      ->orWhere('orders.email', 'like', "%{$search}%")
      ->orWhere('orders.phone', 'like', "%{$search}%");
    })
    ->when($batch, function($query, $batch)
    {
      $query->where('orders.batch_id', $batch);
    })
    ->when($department, function($query, $department)
    {
      $query->where('orders.department_id', $department);
    })
    ->when($student, function($query, $student)
    {
      $query->where('orders.student_id', $student);
    })
    ->when($status, function($query, $status)
    {
      $query->where('orders.status', $status);
    })
    ->when($start_date, function($query, $start_date) use ($end_date)
    {
      $query->whereRaw('DATE(orders.created_at) >= ?', [$start_date])
      ->whereRaw('DATE(orders.created_at) <= ?', [$end_date]);
    })
    ->leftJoin('batches', 'batches.id', 'orders.batch_id')
    ->leftJoin('departments', 'departments.id', 'orders.department_id')
    ->leftJoin('students', 'students.id', 'orders.student_id')
    ->leftJoin('batch_student', function($join)
    {
      $join->on('batch_student.student_id', 'students.id');
    })
    ->orderBy('id','desc')
    ->select('orders.*',
    'batches.name as batch_name',
    'departments.name as department_name',
    'students.name as student_name',
    'batch_student.student_id as batch_has_student'
    )
    ->groupBy('orders.id', 'orders.student_id', 'orders.batch_id', 'orders.department_id', 'orders.name', 'orders.email', 'orders.phone', 'orders.amount', 'orders.address', 'orders.status', 'orders.transaction_id', 'orders.currency', 'orders.created_at', 'batches.name', 'departments.name', 'students.name', 'batch_student.student_id');

    if($search || $batch || $department || $student || $status || $start_date ||$end_date)
    {
      $orders = $orders->paginate(25)
      ->appends(['search' => $search])
      ->withQueryString();
    }
    else
    {
      $orders = $orders->whereRaw('DATE(orders.created_at) >= ?', [date('Y-m-d', strtotime('-7 days'))])
      ->paginate(25)->withQueryString();
    }

    $batches = Batch::where('status', 'Active')
    ->select('id', 'name')
    ->get();

    $departments = Department::where('status', 'Active')
    ->select('id', 'name')
    ->get();

    $students = Student::where('Status', 'Active')
    ->select('id', 'name')
    ->get();

    return view('layouts.orders.index', compact('orders', 'batches', 'departments', 'students'));
  }

  public function show($id)
  {
    return 'Show page';
  }

  public function destroy($id)
  {
    $file = Order::find($id);
    $file->delete();

    Session::flash('success', 'The order item successfully deleted!');
    return redirect()->route('order.index');
  }

  public function addToBatch($orderId)
  {
    $source = new SourceCtrl;
    $order = Order::find($orderId);
    if($order->status == 'Processing' || $order->status == 'Success')
    {
      $student = Student::find($order->student_id);
      $student->batches()->sync($order->batch_id);
      $student->departments()->sync($order->department_id);

      //send sms
      if($student->contact)
      {
          $source->sms_send('88'.$student->contact, 'Your course has been successfully purchased. For more information, please visit liveeducationbd.com');
      }

      //send email
      if($student->email)
      {
          $data = [
              'email_to' => $student->email,
              'subject' => 'New Course Purchase | Live Education BD',
              'comments' => 'Hello '.$student->name.'<br> Your course has been successfully purchased. For more information, visit <a href="'.route('home.course.show', $order->batch_id).'">liveeducationbd.com</a>'
          ];
          $source->sendMail($data);
      }

      Session::flash('success', 'The student added to the batch and departmnet');
    }
    return back();
  }

  public function referrerOrder()
  {
    $referrers = Referrer::orderBy('id', 'DESC')->paginate(25);
    return view('layouts.referrers.index', compact('referrers'));
  }
}