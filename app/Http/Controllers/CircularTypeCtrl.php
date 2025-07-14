<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\CircularType;
use Auth;
use Image;
use File;
use Session;
use Validator;

class CircularTypeCtrl extends Controller
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
        $circular_types = CircularType::orderBy('id','desc')
        ->paginate(25);
        return view('layouts.circular-types.index', compact('circular_types'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
      return view('layouts.circular-types.create');
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
            'name' => 'required|string',
            'details' => 'nullable|string',
            'status' => 'nullable|string',
        ]);
        
        $data = $request->all();

        if(isset($data['_token']))
        {
            unset($data['_token']);
        }

        $data['status'] = isset($data['status']) ? 'Active': 'Deactive';

        try{
            CircularType::insert($data);
        }
        catch(\Exception $e)
        {
            return $e->getMessage();
        }

        Session::flash('success', 'Circular Type created.');
        
        return redirect()->route('circular-type.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\subfilter  $subfilter
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $circular_types = CiruclarType::find($id);
        return view('layouts.circular-types.read', compact('circular_types'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\subfilter  $subfilter
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $circular_type = CircularType::find($id);
        return view('layouts.circular-types.edit', compact('circular_type'));
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
          'name' => 'required|string',
          'details' => 'nullable|string',
          'status' => 'nullable|string',
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

        $data['status'] = isset($data['status']) ? 'Active': 'Deactive';

        try{
            CircularType::where('id', $id)->update($data);
        }
        catch(\Exception $e)
        {
            return $e->getMessage();
        }
        
        Session::flash('success', 'Circular Type updated.');
        return redirect()->route('circular-type.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\subfilter  $subfilter
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $file = CircularType::find($id);
        $file->delete();

        Session::flash('success', 'Circular Type deleted.');

        return redirect()->route('circular-type.index');
    }
    
}
