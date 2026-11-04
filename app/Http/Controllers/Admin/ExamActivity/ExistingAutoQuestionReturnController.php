<?php

namespace App\Http\Controllers\Admin\ExamActivity;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/** Only tracks navigation from Exam Activity to existing administration screens. */
final class ExistingAutoQuestionReturnController extends Controller
{
    public function open(Request $request)
    {
        $target = (string) $request->query('target', '');
        $parts = parse_url($target);
        abort_unless(is_array($parts), 400, 'Invalid target URL.');

        // Deny external redirects and non-HTTP URLs.
        $scheme = strtolower((string) ($parts['scheme'] ?? ''));
        $host = strtolower((string) ($parts['host'] ?? ''));
        abort_if($scheme !== '' && !in_array($scheme, ['http','https'], true), 400);
        abort_if($host !== '' && $host !== strtolower($request->getHost()), 400);
        $path = (string) ($parts['path'] ?? '');
        // Accommodate projects using a subfolder like /liveeducationbd/public/.
        $allowed = str_contains($path, '/admin/exam-section/auto-question/')
            || str_contains($path, '/admin/routine-auto-question/');
        abort_unless($allowed, 400, 'Not an Auto Question management page.');
        abort_if(str_contains($path, '//') || str_contains($path, '..'), 400);
        $query = (string) ($parts['query'] ?? '');
        $relative = $path . ($query === '' ? '' : '?'.$query);
        $request->session()->put('lebd_exam_activity_after_save', [
            'at' => time(),
        ]);
        return redirect()->to($relative);
    }
}
