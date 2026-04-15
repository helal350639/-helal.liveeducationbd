<?php

namespace App\Http\Controllers\Homepage;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Filter;
use App\Models\SubFilter;
use App\Models\Role;
use App\Models\AnswerFile;
use App\Models\Notice;
use Auth;
use Image;
use Toastr;
use File;
use Session;
use Validator;

class HomepageSettingCtrl extends Controller
{
  public function __construct()
  {
    $this->middleware('auth');
  }

  public function aboutUs()
  {
    if(!Storage::exists('about.json'))
    {
      Storage::put('about.json', json_encode([]));
    }

    $json = Storage::get('about.json');
    $array = json_decode($json, true);
    $about = (object) $array;
    
    return view('layouts.settings.about-us', compact('about'));
  }

  public function aboutUsUpdate(Request $request)
  {
    $data = $request->all();
    if(isset($data['_token'])){
      unset($data['_token']);
    }

    $parts = parse_url($data['video']);
    parse_str($parts['query'], $queryParams);
    $data['video'] = $queryParams['v'] ?? null;

    $result = Storage::put('about.json', json_encode($data, JSON_PRETTY_PRINT));

    return redirect()->route('settings.about-us');
  }

  public function noticeEdit()
  {
    $notice = Notice::latest()->first();
    return view('layouts.homepagesettings.notice-edit', compact('notice'));
  }

  public function noticeUpdate(Request $request, $id)
  {
    $data = $request->validate([
      'title' => 'required|string',
      'details' => 'nullable|string',
      'image' => 'nullable|image',
      'start_at' => 'nullable|date',
      'end_at' => 'nullable|date',
      'status' => 'nullable|string',
    ]);

    try {
      Notice::where('id', $id)->update($data);
      Session::flash('success', 'The notice successfully updated.');
    }
    catch(\Exception $e)
    {
      return $e->getMessage();
    }
    return back();
  }
}