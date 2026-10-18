<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Course;
use App\Models\Department;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\Chapter;
use App\Models\Question;
use App\Models\Filter;
use App\Models\Order;
use App\Models\Visitor;
use App\Models\Student;
use Carbon\Carbon;
use App\Models\WithdrawRequest;
use Auth;
use DB;

use Spatie\Analytics\Facades\Analytics;
use Spatie\Analytics\Period;


class HomeCtrl extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    // public function __construct()
    // {
    //     $this->middleware('auth');
    // }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
      Auth::user()->authorizeRoles(['SuperAdmin', 'Admin', 'Editor', 'Sales']);

$data = [

    'success_orders' => Order::whereIn(
        'status',
        ['Processing', 'Complete']
    )->count(),
    'orders' => Order::count(),
    'courses' => Course::count(),
    'departments' => Department::count(),
    'semesters' => Semester::count(),
    'subjects' => Subject::count(),
    'chapters' => Chapter::count(),
    'questions' => Question::count(),
    'filters' => Filter::count(),

    // Pending Withdraw Request Count

    'withdraws' => WithdrawRequest::where(
        'status',
        'pending'
    )->count(),

];

      //visitors
      $visitors = \DB::table('visitors')
      ->where('visited_at', '>=', now()->subDays(30))
      ->selectRaw('DATE(visited_at) as date, COUNT(*) as total')
      ->groupByRaw('DATE(visited_at)')
      ->orderBy('date', 'ASC')
      ->get();

      $visitors_dates = $visitors->pluck('date');
      $visitors_totals = $visitors->pluck('total');

      //orders/sales
      $orders = Order::where('created_at', '>=', now()->subDays(30))
      ->selectRaw('DATE(created_at) as date, COUNT(*) as total')
      ->groupByRaw('DATE(created_at)')
      ->orderBy('date', 'ASC');

      $orders_dates = $orders->pluck('date');
      $orders_totals = $orders->pluck('total');

      // $orders_pending_totals = $orders
      // ->where('status', 'Pending')
      // ->pluck('total');

      // $orders_success_totals = $orders
      // ->where('status', 'Processing')
      // ->pluck('total');

      //active/inactive students
      $active_students = Student::where('last_login_at', '>=', now()->subDays(3))->count();
      $inactive_students = Student::where('last_login_at', '<', now()->subDays(3))->count();

      return view('layouts.index', compact('data', 'visitors_dates', 'visitors_totals', 'orders_dates', 'orders_totals', 'active_students', 'inactive_students'));
    }

    public function visitors()
    {
      $visitors = \DB::table('visitors')
        ->selectRaw('DATE(visited_at) as date, COUNT(*) as total')
        ->groupByRaw('DATE(visited_at)')
        ->orderBy('date', 'ASC')
        ->get();

      $dates = $visitors->pluck('date');
      $totals = $visitors->pluck('total');

      $all_visitors = Visitor::orderBy('visited_at', 'DESC')->paginate(25);

      return view('layouts.visitors', compact('dates', 'totals', 'all_visitors'));
    }

    public function googleAnalytics()
    {
      $analyticsData = Analytics::fetchVisitorsAndPageViews(Period::days(14));

      $labels = $analyticsData->pluck('date')->map(fn($date) => $date?->format('Y-m-d'))->toArray();

      $visits = $analyticsData->pluck('visitors')->toArray();

      // $labels = [0];
      // $visits = [50];

      // dd($labels, $visits, $analyticsData);

      return view('layouts.google-analytics', compact('labels', 'visits'));
    }


    /*
      public function someAdminStuff(Request $request)
      {
        $request->user()->authorizeRoles('manager');
        return view(‘some.view’);
      }
      */
}
