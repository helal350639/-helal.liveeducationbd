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

    public function circulars($key = null)
    {
      $circulars = [
        '৯ম & অন্যান্য জব সার্কুলার',
        'ডিপ্লোমা জব সার্কুলার',
        '১১ - ২০ তম গ্রেডের জব সার্কুলার',
        'বে-সরকারি জব সার্কুলার'
      ];

      if($key === null)
      {
        return $circulars;
      }
      else
      {
        return $circulars[$key];
      }
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
      $circulars = Circular::orderBy('id','desc')->paginate(25);
      return view('layouts.circulars.index',compact('circulars'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
      $circular_types = CircularType::where('status', 'Active')->get();
      return view('layouts.circulars.create', compact('circular_types'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
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
            'details' => 'nullable|string',
            'status' => 'nullable|string',
            'image'   => 'nullable|mimes:jpeg,jpg,png|max:10000',
            'pdf'   => 'nullable|mimes:pdf|max:10000',
        ]);
        
        $data = $request->all();

        if(isset($data['_token']))
        {
          unset($data['_token']);
        }

        $types = $data['type'];

        if(isset($data['type']))
        {
          unset($data['type']);
        }

        try {

          if($request->hasFile('image'))
          {
            $images = [];
            foreach($data['image'] as $image)
            {
              $img = $source->uploadImage($image, 'circulars/');
              array_push($images, $img);
              $data['image'] = json_encode($images);
            }
          }

          if($request->hasFile('pdf'))
          {
            $data['pdf'] = $source->uploadImage($data['pdf'], 'circulars/');
          }
          
          $circular = Circular::create($data);
          $circular->circularType()->attach($types);

          if(isset($data['notify']))
          {
            Notification::create([
              'title' => $data['title'],
              'route' => 'home.circular.show',
              'type_id' => $id,
              'type' => 'circular',
            ]);
          }

          return redirect()->route('circular.show', $circular->id);
        }
        catch(\Eexception $e)
        {
          return $e->getMessage();
        }
        return back();
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\subfilter  $subfilter
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
      $circular = Circular::find($id);
      return view('layouts.circulars.read', compact('circular'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\subfilter  $subfilter
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
      $circular_types = CircularType::where('status', 'Active')->get();
      $circular = Circular::find($id);
      return view('layouts.circulars.edit', compact('circular_types', 'circular'));
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
          'details' => 'nullable|string',
          'status' => 'nullable|string',
          'image'   => 'nullable|mimes:jpeg,jpg,png|max:10000',
          'pdf'   => 'nullable|mimes:pdf|max:10000',
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

      $types = $data['type'];

      if(isset($data['type']))
      {
        unset($data['type']);
      }

      $x_circular = Circular::find($id);

      try {

        if($request->hasFile('image'))
        {
          $images = [];
          foreach($data['image'] as $image)
          {
            $img = $source->uploadImage($image, 'circulars/');
            array_push($images, $img);
            $data['image'] = $images;
          }
        }

        if($request->hasFile('pdf'))
        {
          $data['pdf'] = $source->uploadImage($data['pdf'], 'circulars/');
        }
        
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
          foreach($img_array as $image)
          {
            $img_path = public_path($image);
            if(File::exists($img_path))
            {
              File::delete($img_path);
            }
          }
        }

        if($request->hasFile('pdf') && File::exists($x_circular->pdf))
        {
          File::delete($x_circular->pdf);
        }

        Session::flash('success', 'The circular successfully updated.');
        return redirect()->route('circular.show', $id);
      }
      catch(\Eexception $e)
      {
        return $e->getMessage();
      }
      return back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\subfilter  $subfilter
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $file = Circular::find($id);
        $xfile = public_path($file->pdf);

        if($file->image)
        {
          $img_array = json_decode($file->image);
          foreach($img_array as $image)
          {
            $img_path = public_path($image);
            if(File::exists($img_path))
            {
              File::delete($img_path);
            }
          }
        }

        if(File::exists($xfile))
        {
          File::delete($xfile);
        }
        $file->delete();

        Session::flash('success', 'The circular successfully deleted.');        
        return redirect()->route('circular.index');
    }
    
}
