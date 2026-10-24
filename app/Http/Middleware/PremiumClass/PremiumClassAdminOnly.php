<?php
namespace App\Http\Middleware\PremiumClass;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PremiumClassAdminOnly
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (!$user) {
            abort(403);
        }
        // Existing LEBD admin sidebar uses authRole()->name; deny by default.
        $role = null;
        try {
            $role = $user->authRole()->name ?? null;
        } catch (\Throwable $exception) {
            abort(403);
        }
        if (!in_array($role, ['Admin', 'SuperAdmin'], true)) {
            abort(403);
        }
        return $next($request);
    }
}
