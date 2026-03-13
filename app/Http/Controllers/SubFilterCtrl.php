<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Filter;
use App\Models\SubFilter;
use App\Models\Role;
use App\Models\Vendor;
use Auth;
use Image;
use Toastr;
use File;
use Session;

class SubFilterCtrl extends Controller
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

      $subfilters = SubFilter::query()
      ->when($search, function($query, $search)
      {
        $query->where('sub_filters.name', 'like', "%{$search}%");
      })
      ->leftJoin('filters', 'filters.id', 'sub_filters.filter_id')
      ->orderBy('id','desc')
      ->select('sub_filters.*', 'filters.name as filter_name')
      ->paginate(25)
      ->appends(['search' => $search]);

      return view('layouts.subfilters.index',compact('subfilters'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $filters = Filter::where('status', 'Active')->get();
        return view('layouts.subfilters.create', compact('filters'));
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
            'name'      => 'required|unique:sub_filters|max:255',
            'details'   => 'nullable|max:1000',
        ]);
        
        $data = $request->all();

        if(isset($data['_token']))
        {
            unset($data['_token']);
        }

        $data['status'] = 'Active';

        try{
            SubFilter::insert($data);
        }
        catch(\E $e)
        {
            return $e;
        }

        $subfilter_id = SubFilter::orderBy('id', 'DESC')->first()->id;
        
        Session::flash('success', 'New subfilter successfully created!');

        return redirect()->route('sub-filter.index',$subfilter_id);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\subfilter  $subfilter
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $subfilter = SubFilter::find($id);
        return view('layouts.subfilters.read', compact('subfilter'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\subfilter  $subfilter
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $filters = Filter::where('status', 'Active')->get();
        $filter = Filter::where('slug', 'source')->first();
        $parents = $filter->parents()->get();
        $subfilter = SubFilter::find($id);
        return view('layouts.subfilters.edit', compact('filters', 'parents', 'subfilter'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\subfilter  $subfilter
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name'      => 'required|max:255',
            'details'   => 'nullable|max:1000',
        ]);
        
        $data = $request->all();

        if(isset($data['_token']))
        {
            unset($data['_token']);
        }
        if(isset($data['_method']))
        {
            unset($data['_method']);
        }

        try{
            SubFilter::where('id', $id)->update($data);
        }
        catch(\E $e)
        {
            return $e;
        }
        
        Session::flash('success', 'New subfilter successfully Update!');

        return redirect()->route('sub-filter.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\subfilter  $subfilter
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
      if(Auth::user()->AuthorizeRoles('SuperAdmin'))
      {
        $subfilter = SubFilter::find($id);
           
        if (File::exists('img/subfilter/' .$subfilter->image)) {
            File::delete('img/subfilter/' .$subfilter->image);
        }
        $subfilter->delete();

        Session::flash('Success','This subfilter Successfully delete');
        return redirect()->route('sub-filter.index');
      }
      return back()->with('error', 'Permission Denied');
    }

    public function getSubSource($parent_id)
    {
      $subsource = SubFilter::where('parent_id', $parent_id)->get();
      return response()->json([
        'subsource' => $subsource
      ], 200);
    }
    
}
