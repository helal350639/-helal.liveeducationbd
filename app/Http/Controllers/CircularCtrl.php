<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CircularType;
use App\Models\Role;
use App\Models\Circular;
use App\Models\Notification;
use Auth;
use Image;
use Toastr;
use File;
use Session;
use Validator;

class CircularCtrl extends Controller
{
    public function __construct()
    {
      $this->middleware('auth');
    }

    /** Display a listing of the resource. */
    public function index()
    {
      $circulars = Circular::orderBy('id','desc')->paginate(25);
      return view('layouts.circulars.index',compact('circulars'));
    }

    /** Show the form for creating a new resource. */
    public function create()
    {
      $circular_types = CircularType::where('status', 'Active')->get();
      return view('layouts.circulars.create', compact('circular_types'));
    }

    /** Store a newly created resource in storage. */
    public function store(Request $request)
    {
        $source = new SourceCtrl;
        $validator = Validator::make($request->all(), [
            'type' => 'required|array',
            'title' => 'required|string',
            'published_at' => 'nullable|string',
            'start_at' => 'nullable|string',
            'end_at' => 'nullable|string',
            'admit_to' => 'nullable|string',
            'position' => 'required|string',
            'fee' => 'nullable|string',
            'url' => 'nullable|url',
            'pdf_link' => 'nullable|url|max:2048',
            'details' => 'nullable|string',
            'status' => 'nullable|string',
            'image' => 'nullable|mimes:jpeg,jpg,png|max:10000',
            'pdf' => 'nullable|mimes:pdf|max:10000',
        ]);
        if ($validator->fails()) {
          return back()->withErrors($validator)->withInput();
        }
        $data = $request->all();
        if(isset($data['_token'])) { unset($data['_token']); }
        $types = $data['type'];
        if(isset($data['type'])) { unset($data['type']); }
        try {
          if($request->hasFile('image'))
          {
            $images = [];
            foreach((array) $request->file('image') as $image)
            {
              $img = $source->uploadImage($image, 'circulars/');
              array_push($images, $img);
            }
            $data['image'] = json_encode($images);
          }
          if($request->hasFile('pdf'))
          {
            $data['pdf'] = $source->uploadImage($request->file('pdf'), 'circulars/');
          }
          $data['pdf_link'] = trim($request->input('pdf_link', '')) ?: null;
          $circular = Circular::create($data);
          $circular->circularType()->attach($types);
          if(isset($data['notify']) && $circular)
          {
            Notification::create([
              'title' => $data['title'],
              'route' => 'home.circular.show',
              'type_id' => $circular->id,
              'type' => 'circular',
            ]);
          }
          return redirect()->route('circular.show', $circular->id);
        }
        catch(\Exception $e)
        {
          return $e->getMessage();
        }
        return back();
    }

    /** Display the specified resource. */
    public function show($id)
    {
      $circular = Circular::find($id);
      return view('layouts.circulars.read', compact('circular'));
    }

    /** Show the form for editing the specified resource. */
    public function edit($id)
    {
      $circular_types = CircularType::where('status', 'Active')->get();
      $circular = Circular::find($id);
      return view('layouts.circulars.edit', compact('circular_types', 'circular'));
    }

    /** Update the specified resource in storage. */
    public function update(Request $request, $id)
    {
      $source = new SourceCtrl;
      $validator = Validator::make($request->all(), [
          'type' => 'required|array',
          'title' => 'required|string',
          'published_at' => 'nullable|string',
          'start_at' => 'nullable|string',
          'end_at' => 'nullable|string',
          'admit_to' => 'nullable|string',
          'position' => 'required|string',
          'fee' => 'nullable|string',
          'url' => 'nullable|url',
          'pdf_link' => 'nullable|url|max:2048',
          'details' => 'nullable|string',
          'status' => 'nullable|string',
          'image' => 'nullable|mimes:jpeg,jpg,png|max:10000',
          'pdf' => 'nullable|mimes:pdf|max:10000',
      ]);
      if ($validator->fails()) {
        return back()->withErrors($validator)->withInput();
      }
      $data = $request->all();
      if(isset($data['_token'])) { unset($data['_token']); }
      if(isset($data['_method'])) { unset($data['_method']); }
      $types = $data['type'];
      if(isset($data['type'])) { unset($data['type']); }
      $x_circular = Circular::find($id);
      try {
        if($request->hasFile('image'))
        {
          $images = [];
          foreach((array) $request->file('image') as $image)
          {
            $img = $source->uploadImage($image, 'circulars/');
            array_push($images, $img);
          }
          $data['image'] = json_encode($images);
        }
        if($request->hasFile('pdf'))
        {
          $data['pdf'] = $source->uploadImage($request->file('pdf'), 'circulars/');
        }
        $data['pdf_link'] = trim($request->input('pdf_link', '')) ?: null;
        $circular = Circular::where('id', $id)->update($data);
        $last_circular = Circular::find($id);
        $last_circular->circularType()->sync($types);
        if(isset($data['notify']))
        {
          Notification::create([
            'title' => $data['title'],
            'route' => 'home.circular.show',
            'type_id' => $id,
            'type' => 'circular',
          ]);
        }
        if($request->hasFile('image') && $x_circular->image)
        {
          $img_array = json_decode($x_circular->image);
          if (is_array($img_array)) {
            foreach($img_array as $image)
            {
              $img_path = public_path($image);
              if(File::exists($img_path)) { File::delete($img_path); }
            }
          }
        }
        if($request->hasFile('pdf') && $x_circular->pdf && File::exists(public_path($x_circular->pdf)))
        {
          File::delete(public_path($x_circular->pdf));
        }
        Session::flash('success', 'The circular successfully updated.');
        return redirect()->route('circular.show', $id);
      }
      catch(\Exception $e)
      {
        return $e->getMessage();
      }
      return back();
    }

    /** Remove the specified resource from storage. */
    public function destroy($id)
    {
      if(Auth::user()->AuthorizeRoles('SuperAdmin'))
      {
        $file = Circular::find($id);
        $xfile = public_path($file->pdf);
        if($file->image)
        {
          $img_array = json_decode($file->image);
          if (is_array($img_array)) {
            foreach($img_array as $image)
            {
              $img_path = public_path($image);
              if(File::exists($img_path)) { File::delete($img_path); }
            }
          }
        }
        if(File::exists($xfile)) { File::delete($xfile); }
        $file->delete();
        Session::flash('success', 'The circular successfully deleted.');
        return redirect()->route('circular.index');
      }
      return back()->with('error', 'Permission Denied');
    }
}
