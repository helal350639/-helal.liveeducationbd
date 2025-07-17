<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\Video;
use Auth;
use Image;
use File;
use Session;
use Validator;

class VideoCtrl extends Controller
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
      $videos = Video::orderBy('id','desc')->paginate(25);
      return view('layouts.videos.index', compact('videos'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
      return view('layouts.videos.create');
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
            'status' => 'nullable|string',
            'url' => 'required|string',
            'image'   => 'required|mimes:jpeg,jpg,png|max:1000',
        ]);
        
        $data = $request->all();

        if(isset($data['_token']))
        {
          unset($data['_token']);
        }

        $data['status'] = isset($data['status']) ? 'Active' : 'Deactive';
        
        $parts = parse_url($data['url']);
        parse_str($parts['query'], $queryParams);
        $data['url'] = $queryParams['v'] ?? null;

        try {

          if($request->hasFile('image'))
          {
            $data['image'] = $source->uploadImage($data['image'], 'videos/');
          }
          
          $video = Video::create($data);

          return redirect()->route('video.show', $video->id);
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
      $video = Video::find($id);
      return view('layouts.videos.read', compact('video'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\subfilter  $subfilter
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
      $video = Video::find($id);
      return view('layouts.videos.edit', compact('video'));
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
        'url' => 'required|url',
        'status' => 'nullable|string',
        'image'   => 'required|mimes:jpeg,jpg,png|max:1000',
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
      $data['main'] = isset($data['main']) ? 'Yes' : '';

      $x_video = Video::find($id);
      
      if(isset($data['url']))
      {
        $parts = parse_url($data['url']);
        parse_str($parts['query'], $queryParams);
        $data['url'] = $queryParams['v'] ?? null;
      }
      else
      {
        $data['url'] = $x_video->url;
      }

      try {

        if($request->hasFile('image'))
        {
          $data['image'] = $source->uploadImage($data['image'], 'videos/');
        }
        
        $video = Video::where('id', $id)->update($data);
        $last_video = Video::find($id);

        if($request->hasFile('image') && $x_video->image)
        {
          $img_path = public_path($x_video->image);
          if(File::exists($img_path))
          {
            File::delete($img_path);
          }
        }

        Session::flash('success', 'The video successfully updated.');
        return redirect()->route('video.show', $id);
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
        $video = Video::find($id);

        if($video->image)
        {
          $img_path = public_path($video->image);
          if(File::exists($img_path))
          {
            File::delete($img_path);
          }
        }
        
        $video->delete();

        Session::flash('success', 'The video successfully deleted.');        
        return redirect()->route('video.index');
    }
    
}
