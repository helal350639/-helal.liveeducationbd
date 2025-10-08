<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string
     */
    protected function redirectTo($request)
    {
      if (! $request->expectsJson()) {
        if($request->is('teachers') || $request->is('teachers/*'))
        {
          return route('teachers.login');
        }

        if($request->is('students') || $request->is('students/*'))
        {
          return route('students.login');
        }
        return route('login');
      }
    }
}
