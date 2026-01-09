<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Department;
use App\Models\BookCategory;
use App\Models\Book;
use App\Models\Chapter;
use App\Models\Role;
use Auth;
use Image;
use File;
use Session;

class BookCtrl extends Controller
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
    public function index(Request $request)
    {
      $search = $request->input('search');

      $books = Book::query()
      ->when($search, function($query, $search)
      {
        $query->where('name', 'like', "%{$search}%");
      })
      ->orderBy('id','DESC')
      ->paginate(25)
      ->appends(['search' => $search]);
      
      return view('layouts.books.index', compact('books'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $categories = BookCategory::where('status', 'Active')
        ->select('id', 'name')
        ->get();
        $departments = Department::where('status', 'Active')
        ->select('id', 'name')
        ->get();
        $chapters = Chapter::where('status', 'Active')
        ->select('id', 'name')
        ->get();

        return view('layouts.books.create', compact('categories', 'departments', 'chapters'));
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
        $this->validate($request, [
          'category_id'   => 'required|numeric',
          'department_id' => 'nullable|numeric',
          'name'          => 'required|unique:books|max:255',
          'chapter_id'    => 'nullable|array',
          'front_cover'   => 'nullable|image|mimes:jpeg,jpg,png|max:5000',
          'back_cover'    => 'nullable|image|mimes:jpeg,jpg,png|max:5000',
          'details'       => 'nullable|max:1000',
        ]);
        
        $data = $request->all();

        if(isset($data['_token']))
        {
          unset($data['_token']);
        }

        $data['status'] = 'Active';
        $data['created_by'] = Auth::id();

        if($request->hasFile('front_cover'))
        {
          $data['front_cover'] = $source->uploadImage($data['front_cover'], 'books/');
        }

        if($request->hasFile('back_cover'))
        {
          $data['back_cover'] = $source->uploadImage($data['back_cover'], 'books/');
        }

        $chapters = $data['chapter_id'];
        if(isset($data['chapter_id']))
        {
          unset($data['chapter_id']);
        }

        try {
          $book = Book::create($data);

          //attach chapter ids
          $book->chapters()->attach($chapters);
        }
        catch(\E $e)
        {
          return $e->getMessage();
        }
        
        Session::flash('success', 'New Book successfully created!');
        return redirect()->route('book.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\department  $book
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $book = Book::find($id);
        return view('layouts.books.read', compact('book'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\department  $book
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
      $categories = BookCategory::where('status', 'Active')
      ->select('id', 'name')
      ->get();
      $departments = Department::where('status', 'Active')
      ->select('id', 'name')
      ->get();
      $chapters = Chapter::where('status', 'Active')
      ->select('id', 'name')
      ->get();

      $book = Book::find($id);
      $book_chapters = $book->chapters->pluck('id')->toArray();
      return view('layouts.books.edit', compact('categories', 'departments', 'book', 'chapters', 'book_chapters'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\department  $book
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
      $source = new SourceCtrl;
        $this->validate($request, [
          'category_id'   => 'required|numeric',
          'department_id' => 'nullable|numeric',
          'name'          => 'required|max:255',
          'chapter_id'    => 'nullable|array',
          'front_cover'   => 'nullable|image|mimes:jpeg,jpg,png|max:5000',
          'back_cover'    => 'nullable|image|mimes:jpeg,jpg,png|max:5000',
          'details'       => 'nullable|max:1000',
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

        $data['updated_by'] = Auth::id();

        if($request->hasFile('front_cover'))
        {
          $data['front_cover'] = $source->uploadImage($data['front_cover'], 'books/');
        }

        if($request->hasFile('back_cover'))
        {
          $data['back_cover'] = $source->uploadImage($data['back_cover'], 'books/');
        }

        $chapters = $data['chapter_id'];
        if(isset($data['chapter_id']))
        {
          unset($data['chapter_id']);
        }

        try {
          $book = Book::find($id);

          $xfront_cover = public_path($book->front_cover);
          $xback_cover = public_path($book->back_cover);

          Book::where('id', $id)->update($data);
          $book->chapters()->sync($chapters);

          //delete image
          if($request->hasFile('front_cover') && File::exists($xfront_cover))
          {
            File::delete($xfront_cover);
          }

          //delete image
          if($request->hasFile('back_cover') && File::exists($xback_cover))
          {
            File::delete($xback_cover);
          }
        }
        catch(\E $e)
        {
          return $e->getMessage();
        }
        
        Session::flash('success', 'The Book successfully Updated!');

        return redirect()->route('book.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\department  $book
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
      if(Auth::user()->AuthorizeRoles('SuperAdmin'))
      {
        $book = Book::find($id);

        $xfront_cover = public_path($book->front_cover);
        $xback_cover = public_path($book->back_cover);

        // remove relationship
        $book->chapters()->detach();
        $book->questions()->detach();

        $book->delete();

        if(File::exists($xfront_cover))
        {
          File::delete($xfront_cover);
        }

        if(File::exists($xback_cover))
        {
          File::delete($xback_cover);
        }

        Session::flash('Success','This book Successfully delete');
        return redirect()->route('book.index');        
      }
      return back()->with('error', 'Permission Denied');
    }

    /** -------------- custom methods ------------- */
    //custom routes
    public function addQuestion($type, $id)
    {
      $paper = Book::find($id);
      $paper['type'] = $type;
      if($paper)
      {
        Session::put('_paper', $paper);
        return redirect()->route('question.view');
      }
      return back();
    }

    public function addToPaper(Request $request)
    {
      $data = $request->all();
      $ids = explode(',', $data['question']);
      if(!is_null(Session::get('_paper')))
      {
        $paper = Session::get('_paper');
        if($data['action'] == 'add')
        {
          $qcount = $paper->questions()->attach($ids);
        }
        else
        {
          $qcount = $paper->questions()->detach($ids);
        }

        $paper = Book::find($paper->id);
        Session::put('_paper', $paper);

        return response()->json([
          'success' => true,
          'message' => 'The question added to the book',
          'qcount' => $qcount
        ]);
      }

      return response()->json([
          'success' => false,
          'message' => 'We are getting error'
      ]);
    }
    
    public function view($id)
    {
        if(!is_null(Session::get('_paper')))
        {
            Session::forget('_paper');
        }
        $book = Book::find($id);
        $questions = $book->questions()->paginate(25);

        return view('layouts.books.view', compact('book', 'questions'));
    }

    // Ajax Call
    // public function getbooks($department_id)
    // {
    //   $arr = explode(',', $department_id);
    //   $items = Book::where('status', 'Active')
    //   ->whereHas('departments', function($query) use($arr) {
    //       $query->whereIn('department_id', $arr);
    //   })->select('id', 'name')->get();
    //   return response()->json(['data' => $items]);
    // }   
}
