<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\Models\Course;
use App\Models\Department;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\Chapter;
use App\Models\Question;
use App\Models\Filter;
use App\Models\Order;

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
          'success_orders' => Order::whereIn('status', ['Processing', 'Complete'])->count(),
          'orders' => Order::count(),
          'courses' => Course::count(),
          'departments' => Department::count(),
          'semesters' => Semester::count(),
          'subjects' => Subject::count(),
          'chapters' => Chapter::count(),
          'questions' => Question::count(),
          'filters' => Filter::count(),
        ];

        return view('layouts.index', compact('data'));
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
