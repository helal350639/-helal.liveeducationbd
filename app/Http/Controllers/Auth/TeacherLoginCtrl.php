<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use App\Models\Teacher;
use Session;
use Laravel\Socialite\Facades\Socialite;

class TeacherLoginCtrl extends Controller
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
    protected $redirectTo = '/teachers';

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
        if(Auth::guard('teacher')->check())
        {
            return redirect()->intended(route('teachers.dashboard'));
        }

        return view('auth.teacher-login');
    }

    public function loginPost(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required|max:32'
        ]);

        $email    = $request->email;
        $password = $request->password;

        if(Auth::guard('teacher')->attempt([
            'email' => $email,
            'password' => $password,
            // 'status' => 'Active'
        ], $remember = true))
        {
            return redirect()->intended(route('teachers.dashboard'));
        }
        
        return back()->withErrors(['email' => 'Invalid credentials']);
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('teacher')->logout();
    
        $request->session()->invalidate();
    
        $request->session()->regenerateToken();
    
        return redirect()->route('teachers.login');
    }

    /** --------------------- O Auth logins -------------------- */
    public function oAuthGithub()
    {
        $socialUser = Socialite::driver('github')->user();
    
        // Find or create user
        $user = Teacher::updateOrCreate(
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
    
        Auth::guard('teacher')->loginUsingId($user->id);
    
        return redirect()->intended(route('teachers.dashboard'));
    }

    public function oAuthGoogle()
    {
        $socialUser = Socialite::driver('google')->user();
    
        // Find or create user
        $user = Teacher::updateOrCreate(
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
  
      Auth::guard('teacher')->loginUsingId($user->id);
  
      return redirect()->intended(route('teachers.dashboard'));
    }
}
