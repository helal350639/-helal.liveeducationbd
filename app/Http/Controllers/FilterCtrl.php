<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Course;
use App\Models\Filter;
use App\Models\Role;
use App\Models\Vendor;
use Auth;
use Image;
use Toastr;
use File;
use Session;

class FilterCtrl extends Controller
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

      $filters = Filter::query()
      ->when($search, function($query, $search)
      {
        $query->where('name', 'like', "%{$search}%");
      })
      ->with(['courses'])
      ->orderBy('filters.id','desc')
      ->paginate(25)
      ->appends(['search' => $search]);
      
      return view('layouts.filters.index', compact('filters'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $courses = Course::where('status', 'Active')->get();
        return view('layouts.filters.create', compact('courses'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'course_id' => 'nullable',
            'name'      => 'required|unique:filters|max:255',
            'details'   => 'nullable|max:1000',
        ]);
        
        $data = $request->all();
        $coures = $data['course_id'];

        if(isset($data['_token']))
        {
            unset($data['_token']);
        }

        $data['status'] = 'Active';

        if(isset($data['course_id']))
        {
            unset($data['course_id']);
        }

        try{
            Filter::insert($data);
        }
        catch(\E $e)
        {
            return $e;
        }

        $filter = Filter::orderBy('id', 'DESC')->first();
        $filter->courses()->sync($coures);
        
        Session::flash('success', 'New filter successfully created!');

        return redirect()->route('filter.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\filter  $filter
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $filter = Filter::find($id);
        return view('layouts.filters.read', compact('filter'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\filter  $filter
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $courses = Course::where('status', 'Active')->get();
        $filter = Filter::with(['courses'])->find($id);
        return view('layouts.filters.edit', compact('filter', 'courses'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\filter  $filter
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'course_id' => 'nullable',
            'name'      => 'required|max:255',
            'details'   => 'nullable|max:1000',
        ]);
        
        $data = $request->all();        
        $coures = $data['course_id'];

        if(isset($data['_token']))
        {
            unset($data['_token']);
        }
        if(isset($data['_method']))
        {
            unset($data['_method']);
        }
        if(isset($data['course_id']))
        {
            unset($data['course_id']);
        }

        if(!isset($data['label']))
        {
            $data['label'] = null;
        }

        try{
            Filter::where('id', $id)->update($data);
        }
        catch(\E $e)
        {
            return $e;
        }

        $filter = Filter::find($id);
        $filter->courses()->sync($coures);
        
        Session::flash('success', 'The filter successfully Update!');

        return redirect()->route('filter.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\filter  $filter
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if(Auth::user()->AuthorizeRoles('SuperAdmin'))
        {
            $filter = Filter::find($id);
            
            if (File::exists($filter->image)) {
                File::delete($filter->image);
            }
            $filter->delete();

            $filter->courses()->detach();

            Session::flash('Success','This filter Successfully delete');
            return redirect()->route('filter.index');
        }

        Session::flash('error', 'Permission restricted!');
        return back();
    }

    /** -------------- custom methods ------------- */
    // Ajax Call
    public function getFilters($course_id)
    {
        $course = Course::find($course_id);
        $filters = $course->filters()->with(['SubFilter'])->orderBy('id', 'ASC')->select('filters.id', 'filters.name')->get();
        return response()->json(['data' => $filters]);
    }
    
}
