<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Only redirects an existing Auto Question successful Save when the editing
 * screen was opened from Exam Activity. No changes to generation or settings.
 * Registered once in the web middleware group from routes/exam_activity.php.
 */
final class ExamActivitySaveReturnV3
{
    public function handle(Request $request, Closure $next): Response
    {
        $path = strtolower(trim($request->path(), '/'));
        $isAuto = str_contains($path, 'auto-question')
            && (str_contains($path, 'exam-section') || str_contains($path, 'routine-auto-question'));

        if ($request->isMethod('GET') && $isAuto
            && (string) $request->query('lebd_exam_activity_return', '') === '1') {
            $request->session()->put('lebd05_return_to_exam_activity', [
                'started_at' => time(),
                'module' => str_contains($path, 'exam-section') ? 'exam-section' : 'batch',
            ]);
        }

        $response = $next($request);

        if (!$isAuto || !$request->isMethod('POST') || !($response instanceof RedirectResponse)) {
            return $response;
        }
        $marker = $request->session()->get('lebd05_return_to_exam_activity');
        if (!is_array($marker)) return $response;
        if (time() - (int) ($marker['started_at'] ?? 0) > 1200) {
            $request->session()->forget('lebd05_return_to_exam_activity');
            return $response;
        }
        if (($marker['module'] ?? '') !== (str_contains($path, 'exam-section') ? 'exam-section' : 'batch')) {
            return $response;
        }
        // Never redirect after Delete/Reset or a failed validation.
        $signature = strtolower($path . ' ' . (string) optional($request->route())->getName());
        if (preg_match('/reset|delete|destroy|remove|detach|cancel/', $signature)) return $response;
        if ($request->session()->has('errors')) return $response;
        $success = $request->session()->get('success');
        if (!is_string($success) || trim($success) === '') return $response;
        $request->session()->forget('lebd05_return_to_exam_activity');
        return redirect()->route('admin.exam.activity')->with('success', $success);
    }
}
