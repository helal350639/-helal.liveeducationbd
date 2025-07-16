<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\Ad;
use Auth;
use Image;
use File;
use Session;
use Validator;

class AdCtrl extends Controller
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
      $ads = Ad::orderBy('id','desc')->paginate(25);
      return view('layouts.ads.index', compact('ads'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
      return view('layouts.ads.create');
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
            $data['image'] = $source->uploadImage($data['image'], 'ads/');
          }
          
          $ad = Ad::create($data);

          return redirect()->route('ad.show', $ad->id);
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
      $ad = Ad::find($id);
      return view('layouts.ads.read', compact('ad'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\subfilter  $subfilter
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
      $ad = Ad::find($id);
      return view('layouts.ads.edit', compact('ad'));
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

      $x_ad = Ad::find($id);

      try {

        if($request->hasFile('image'))
        {
          $data['image'] = $source->uploadImage($data['image'], 'ads/');
        }
        
        $ad = Ad::where('id', $id)->update($data);
        $last_ad = Ad::find($id);

        if($request->hasFile('image') && $x_ad->image)
        {
          $img_path = public_path($x_ad->image);
          if(File::exists($img_path))
          {
            File::delete($img_path);
          }
        }

        if($request->hasFile('pdf') && File::exists($x_ad->pdf))
        {
          File::delete($x_ad->pdf);
        }

        Session::flash('success', 'The ad successfully updated.');
        return redirect()->route('ad.show', $id);
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
      $ad = Ad::find($id);

      if($ad->image)
      {
        $img_path = public_path($ad->image);
        if(File::exists($img_path))
        {
          File::delete($img_path);
        }
      }

      $ad->delete();

      Session::flash('success', 'The ad successfully deleted.');        
      return redirect()->route('ad.index');
    }
    
}
