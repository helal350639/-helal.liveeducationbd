<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Filter;
use App\Models\SubFilter;
use App\Models\Role;
use App\Models\Complain;
use Auth;
use Image;
use Toastr;
use File;
use Session;
use Validator;

class ComplainCtrl extends Controller
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
        $complains = Complain::orderBy('id', 'DESC')->paginate(25);
        return view('layouts.complains.index', compact('complains'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
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
            Complain::insert($data);
        }
        catch(\E $e)
        {
            return $e;
        }

        $file = Complain::orderBy('id', 'DESC')->first();

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
        $complain = Complain::find($id);
        // dd($complain);

        if($complain->status == "New")
        {
            Complain::where('id', $id)->update(['status' => 'Read']);
        }

        return view('layouts.complains.read', compact('complain'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\subfilter  $subfilter
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $complain = Complain::find($id);
        return view('layouts.complains.edit', compact('complain'));
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
        $data['web'] = isset($data['web']) ? 'Yes' : 'No';

        try {
            Complain::where('id', $id)->update($data);
        }
        catch(\E $e)
        {
            return $e;
        }

        $file = Complain::find($id);
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
        $file = Complain::find($id);
        $xfile = public_path($file->file);
           
        if(File::exists($xfile))
        {
            File::delete($xfile);
        }

        $file->delete();

        return redirect()->route('complain.index');

        // return response()->json([
        //     'success' => true,
        //     'message' => 'File successfully deleted!',
        //     'file' => $file
        // ]);
      }
      return back()->with('error', 'Permission Denied');
    }

    public function reply(Request $request)
    {
        $request->validate([
            'complain_id' => 'required|integer|exists:complains,id',
            'reply_message' => 'required|string|max:10000',
        ]);

        $source = new SourceCtrl;
        $complain = Complain::findOrFail($request->complain_id);

        // Backfill the student link for older complaints created before v26.
        // Prefer exact contact/email matches. Use the name only when it is unique.
        if (empty($complain->student_id)) {
            $matchedStudent = null;

            if (!empty($complain->contact)) {
                $matchedStudent = \App\Models\Student::where('contact', $complain->contact)->first();
            }

            if (!$matchedStudent && !empty($complain->email)) {
                $matchedStudent = \App\Models\Student::where('email', $complain->email)->first();
            }

            if (!$matchedStudent && !empty($complain->name)) {
                $sameNameCount = \App\Models\Student::where('name', $complain->name)->count();
                if ($sameNameCount === 1) {
                    $matchedStudent = \App\Models\Student::where('name', $complain->name)->first();
                }
            }

            if ($matchedStudent) {
                $complain->student_id = $matchedStudent->id;
            }
        }

        // Save the reply for the student's dashboard and keep the existing email notification.
        $emailData = [
            'email_to' => $complain->email,
            'subject' => 'Reply from Live Education BD',
            'comments' => 'Hello '.$complain->name.',<br> '.$request->reply_message
        ];

        try {
            $complain->reply_message = $request->reply_message;
            $complain->replied_at = now();
            $complain->student_notice_dismissed_at = null;
            $complain->status = 'Replied';
            $complain->save();
        }
        catch(\Exception $e)
        {
            return back()->with('error', 'Reply could not be saved: '.$e->getMessage());
        }

        // Email is an additional channel; failure must not prevent the in-dashboard notice.
        if (!empty($complain->email)) {
            try {
                $source->sendMail($emailData);
            } catch(\Exception $mailError) {
                \Log::warning('Complaint reply email failed; dashboard notice was saved.', [
                    'complain_id' => $complain->id,
                    'error' => $mailError->getMessage(),
                ]);
            }
        }

        Session::flash('success', 'Your reply has been saved and the student will see it on the dashboard.');
        return back();
    }
    
}
