<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Controllers\SourceCtrl;
use Illuminate\Foundation\Auth\VerifiesEmails;
use Illuminate\Http\Request;
use Session;
use App\Models\Student;
use Auth;
use Validator;


class VerificationController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Email Verification Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling email verification for any
    | user that recently registered with the application. Emails may also
    | be re-sent if the user didn't receive the original email message.
    |
    */

    use VerifiesEmails;

    /**
     * Where to redirect users after verification.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('signed')->only('verify');
        $this->middleware('throttle:6,1')->only('verify', 'resend');
    }

    public function contactCheck(Request $request)
    {
        $source = new SourceCtrl;

        $validator = Validator::make($request->all(), [
          'contact' => 'required|string|min:11|max:11|unique:students',
      ], [
          'contact.required' => 'The contact number is required.',
          'contact.string'   => 'The contact number must be a valid string.',
          'contact.min'      => 'The contact number must be exactly 11 characters.',
          'contact.max'      => 'The contact number must be exactly 11 characters.',
          'contact.unique'   => 'This contact number is already registered.',
      ]);
      
      if ($validator->fails()) {
          return response()->json([
              'success' => false,
              'message' => 'Validation Error.',
              'errors' => $validator->errors(), // already grouped by field
          ], 422);
      }
      

        $contact = '88'.$request->contact;
        $otp = rand(1111, 9999);
        $sms = 'Contact verification. Your OTP: '.$otp.' Live Education BD';
        Session::put('_otp', ['contact' => $request->contact, 'otp' => $otp]);
        $result = $source->sms_send($contact, $sms);
        
        return response()->json([
            'success' => true,
            'result' => $result
        ], 200);
        
    }

    public function otpCheck(Request $request)
    {
        $this->Validate($request, [
            'otp' => 'required|string|min:4|max:6',
            'study' => 'required|string|max:255'
        ]);

        $otp = Session::get('_otp');
        if($otp['otp'] == $request->otp)
        {
            try {
                Student::where('id', Auth::guard('student')->id())->update([
                    'contact' => $otp['contact'],
                    'studying' => $request->study,
                    'contact_verify' => 'Yes'
                ]);
            }
            catch(\Exception $e){
                return $e->getMessage();

            }
        
            Session::forget('_otp');
    
            return response()->json([
                'success' => true,
                'message' => 'Contact successfully updated' 
            ], 200);
            
        }
        else
        {
            return response()->json([
                'success' => false,
                'message' => 'OTP does not match please check again.'
                ], 200);
        }
    }
}
