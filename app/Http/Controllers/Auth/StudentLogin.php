<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use App\Models\Student;
use Session;
use Laravel\Socialite\Facades\Socialite;

class StudentLogin extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/students';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function login()
    {
        if(Auth::guard('student')->check())
        {
            return redirect()->intended(route('students.dashboard'));
        }

        return view('auth.student-login');
    }

    public function loginPost(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required|max:32'
        ]);

        $email    = $request->email;
        $password = $request->password;

        if(Auth::guard('student')->attempt([
            'email' => $email,
            'password' => $password,
            // 'status' => 'Active'
        ], $remember = true))
        {
            return redirect()->intended(route('students.dashboard'));
        }
        
        return back()->withErrors(['email' => 'Invalid credentials']);
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('student')->logout();
    
        $request->session()->invalidate();
    
        $request->session()->regenerateToken();
    
        return redirect()->route('students.login');
    }

    /** --------------------- O Auth logins -------------------- */
    public function oAuthGithub()
    {
        $socialUser = Socialite::driver('github')->user();
    
        // Find or create user
        $user = Student::updateOrCreate(
            [
                'email' => $socialUser->getEmail(),
            ],
            [
                'name' => $socialUser->getName(),
                'password' => bcrypt(str()->random(16)), // Random password
                'image' => $socialUser->getAvatar(),
                'status' => 'Active',
                'github_id' => $socialUser->getId(),
            ]);
    
        Auth::guard('student')->loginUsingId($user->id);
    
        return redirect()->intended(route('students.my-course'));
    }

    public function oAuthGoogle()
    {
        $socialUser = Socialite::driver('google')->user();
    
        // Find or create user
        $user = Student::updateOrCreate(
            [
                'email' => $socialUser->getEmail(),
            ],
            [
                'name' => $socialUser->getName(),
                'password' => bcrypt(str()->random(16)), // Random password
                'image' => $socialUser->getAvatar(),
                'status' => 'Active',
                'google_id' => $socialUser->getId(),
            ]);
    
        Auth::guard('student')->loginUsingId($user->id);
    
        return redirect()->intended(route('students.my-course'));
    }
}
