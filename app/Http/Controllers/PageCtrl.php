<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Filter;
use App\Models\SubFilter;
use App\Models\Role;
use App\Models\Page;
use Auth;
use Image;
use File;
use Session;
use Validator;

class PageCtrl extends Controller
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
    $pages = Page::orderBy('id', 'DESC')->paginate(25);
    return view('layouts.pages.index',compact('pages'));
  }

  public function create()
  {
    return view('layouts.pages.create');
  }

  public function store(Request $request)
  {
    $this->validate($request, [
      'title' => 'required|max:255',
      'details' => 'string'
    ]);

    $data = $request->all();

    if(isset($data['_token']))
    {
      unset($data['_token']);
    }

    try{
      Page::insert($data);
    }
    catch(\Exception $e)
    {
      return $e;
    }

    Session::flash('success', 'The page created successfull.');
    return redirect()->route('page.index');
  }

  public function edit($id)
  {
    $page = Page::find($id);
    return view('layouts.pages.edit', compact('page'));
  }

  public function update(Request $request, $id)
  {
    $this->validate($request, [
      'title' => 'required|max:255',
      'details' => 'string'
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

    try {
      Page::where('id', $id)->update($data);
    }
    catch(\Exception $e)
    {
      return $e;
    }

    Session::flash('success', 'The page successfully updated.');
    return redirect()->route('page.edit', $id);
  }

  public function destroy($id)
  {
    if(Auth::user()->AuthorizeRoles('SuperAdmin'))
    {
      //
    }
    return back()->with('error', 'Permission Denied');
  }
}