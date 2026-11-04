<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Returns a dashboard-originated Auto Question POST to the same Dashboard, after
 * a confirmed success flash. Never changes question mutation or validation.
 */
final class ExamActivityReturnAfterSave
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        $marker = $request->session()->get('lebd_exam_activity_after_save');
        if (!is_array($marker) || !isset($marker['at'])) return $response;
        if (time() - (int)$marker['at'] > 1800) {
            $request->session()->forget('lebd_exam_activity_after_save');
            return $response;
        }
        if (!$request->isMethod('POST')) return $response;
        $path = strtolower($request->path());
        if (!str_contains($path, 'auto-question')) return $response;
        if (!($response instanceof RedirectResponse)) return $response;

        // Never redirect away from validation failures or a failed Save operation.
        if ($request->session()->has('errors') || !$request->session()->has('success')) {
            return $response;
        }
        $message = $request->session()->get('success');
        $request->session()->forget('lebd_exam_activity_after_save');
        return redirect()->route('admin.exam.activity')
            ->with('success', is_string($message) ? $message : 'Saved successfully.');
    }
}
