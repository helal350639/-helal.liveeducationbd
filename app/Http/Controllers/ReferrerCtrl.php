<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Referrer;
use Session;

class ReferrerCtrl extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
      $referrer = Referrer::find($id);
      return view('layouts.referrers.edit', compact('referrer'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
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
        Referrer::where('id', $id)->update($data);
        Session::flash('success', 'Updated');
        return back();
      }
      catch(\Exception $e)
      {
        return $e->getMessage();
      }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
      if(Auth::user()->AuthorizeRoles('SuperAdmin'))
      {
        //
      }
      return back()->with('error', 'Permission Denied');
    }
}
