<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Filter;
use App\Models\SubFilter;
use App\Models\Role;
use App\Models\AnswerFile;
use Auth;
use Image;
use Toastr;
use File;
use Session;
use Validator;

class AnswerFileCtrl extends Controller
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
        $answerfiles = SubFilter::leftJoin('filters', 'filters.id', 'sub_filters.filter_id')
        ->orderBy('id','desc')
        ->select('sub_filters.*', 'filters.name as filter_name')
        ->get();
        return view('layouts.answerfiles.index',compact('answerfiles'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $filters = Filter::where('status', 'Active')->get();
        return view('layouts.answerfiles.create', compact('filters'));
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
            'question_id' => 'required|numeric',
            'file'   => 'required|mimes:jpeg,jpg,png,pdf|max:1000',
        ]);

        if($validator->fails()){
            return response()->json(
                [
                    'success' => false,
                    'message' => 'File upload failed',
                    'errors' => $validator->getMessageBag()->toArray()
                ],
                201
            );
        }
        
        $data = $request->all();

        if(isset($data['_token']))
        {
            unset($data['_token']);
        }

        if(isset($data['file']))
        {
            $data['file'] = $source->uploadImage($data['file'], 'questions/written/files/');
        }

        try{
            AnswerFile::insert($data);
        }
        catch(\E $e)
        {
            return $e;
        }

        $file = AnswerFile::orderBy('id', 'DESC')->first();

        return response()->json(['success' => 'true', 'message' => 'File successfully uploaded!', 'file' => $file], 200);
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
        return view('layouts.answerfiles.read', compact('subfilter'));
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
        $subfilter = SubFilter::find($id);
        return view('layouts.answerfiles.edit', compact('filters', 'subfilter'));
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
            'file'   => 'required|mimes:jpeg,jpg,png,pdf|max:1000',
        ]);

        if($validator->fails()){
            return response()->json(
                [
                    'success' => false,
                    'message' => 'File upload failed',
                    'errors' => $validator->getMessageBag()->toArray()
                ],
                201
            );
        }
        
        $data = $request->all();

        if(isset($data['_token']))
        {
            unset($data['_token']);
        }
        if(isset($data['_method']))
        {
            unset($data['_method']);
        }

        $file = AnswerFile::find($id);
        $xfile = public_path($file->file);

        if(isset($data['file']))
        {
            $data['file'] = $source->uploadImage($data['file'], 'questions/written/files/');
        }

        try{
            AnswerFile::where('id', $id)->update($data);
            if(File::exists($xfile))
            {
                File::delete($xfile);
            }
        }
        catch(\E $e)
        {
            return $e;
        }

        $file = AnswerFile::find($id);
        
        return response()->json(['success' => 'true', 'message' => 'File successfully updated!', 'file' => $file], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\subfilter  $subfilter
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
      if(Auth::user()->authorizeRoles(['SuperAdmin']))
      {
        $file = AnswerFile::find($id);
        $xfile = public_path($file->file);
           
        if (File::exists($xfile)) {
            File::delete($xfile);
        }
        $file->delete();

        return response()->json([
            'success' => true,
            'message' => 'File successfully deleted!',
            'file' => $file
        ]);
      }
      return back()->with('error', 'Permission Denied');
    }
    
}
