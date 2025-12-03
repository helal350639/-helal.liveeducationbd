<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\Department;
use App\Models\BookCategory;
use Auth;
use Image;
use File;
use Session;
use Validator;

class BookCategoryCtrl extends Controller
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
      $departments = Department::where('status', 'Active')
      ->select('id', 'name')
      ->get();

      $bookCategories = BookCategory::orderBy('id','desc')
      ->paginate(25);

      return view('layouts.books.book-category', compact('departments', 'bookCategories'));
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
      $bookCategory = BookCategory::with(['departments:id,name'])
      ->find($id);
      $departments = $bookCategory->departments()->pluck('department_id')->toArray();
      
      return response()->json([
        'bookCategory' => $bookCategory,
        'departments' => $departments
      ], 200);
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
          'department' => 'nullable|string',
          'status' => 'nullable|string',
        ]);

        $departments_ids = explode(',', $request->department);

        try{
            BookCategory::where('id', $id)->update([
              'name' => $request->name,
              'status' => $request->status,
            ]);

            $category = BookCategory::find($id);
            $category->departments()->sync($departments_ids);
            
            return back()->with('success', 'Book Category Updated');
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
      if(Auth::user()->AuthorizeRoles('SuperAdmin'))
      {
        $file = CircularType::find($id);
        $file->delete();

        Session::flash('success', 'Circular Type deleted.');

        return redirect()->route('circular-type.index');
      }
      return back()->with('error', 'Permission Denied');
    }
    
}
