<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\Service;
use Auth;
use Image;
use File;
use Session;
use Validator;

class ServiceCtrl extends Controller
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
      $services = Service::orderBy('id','desc')->paginate(25);
      return view('layouts.services.index', compact('services'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
      return view('layouts.services.create');
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
          'title' => 'required|string',
          'details' => 'nullable|string',
          'status' => 'nullable|string',
          'image'   => 'nullable|mimes:jpeg,jpg,png|max:1000',
        ]);
        
        $data = $request->all();

        if(isset($data['_token']))
        {
          unset($data['_token']);
        }

        $data['status'] = isset($data['status']) ? 'Active' : 'Deactive';

        try {

          if($request->hasFile('image'))
          {
            $data['image'] = $source->uploadImage($data['image'], 'services/');
          }
          
          $service = Service::create($data);

          return redirect()->route('service.show', $service->id);
        }
        catch(\Exception $e)
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
      $service = Service::find($id);
      return view('layouts.services.read', compact('service'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\subfilter  $subfilter
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
      $service = Service::find($id);
      return view('layouts.services.edit', compact('service'));
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
        'title' => 'required|string',
        'details' => 'nullable|string',
        'status' => 'nullable|string',
        'image'   => 'nullable|mimes:jpeg,jpg,png|max:1000',
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

      $data['status'] = isset($data['status']) ? 'Active' : 'Deactive';

      $x_service = Service::find($id);

      try {

        if($request->hasFile('image'))
        {
          $data['image'] = $source->uploadImage($data['image'], 'services/');
        }
        
        $service = Service::where('id', $id)->update($data);
        $last_service = Service::find($id);

        if($request->hasFile('image') && $x_service->image)
        {
          $img_path = public_path($x_service->image);
          if(File::exists($img_path))
          {
            File::delete($img_path);
          }
        }

        Session::flash('success', 'The service successfully updated.');
        return redirect()->route('service.show', $id);
      }
      catch(\Exception $e)
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
        $service = Service::find($id);

        if($service->image)
        {
          $img_path = public_path($service->image);
          if(File::exists($img_path))
          {
            File::delete($img_path);
          }
        }

        $service->delete();

        Session::flash('success', 'The service successfully deleted.');        
        return redirect()->route('service.index');
    }
    
}
