<?php
namespace App\Http\Controllers\Homepage;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View as ViewContract;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;

/** Keep running-batch list aligned with the data visible on homepage. */
class RunningBatchesUnifiedController extends Controller
{
    public function index(HomePageCtrl $homepage)
    {
        // No second, potentially contradictory status/date filter.
        $response = $homepage->index();
        if ($response instanceof RedirectResponse) {
            return $response;
        }
        if (!$response instanceof ViewContract || !method_exists($response, 'getData')) {
            abort(500, 'Homepage did not return a view containing the running batches.');
        }
        $data = $response->getData();
        $batches = $data['batches'] ?? collect();
        if (!($batches instanceof Collection) && !($batches instanceof \Illuminate\Pagination\AbstractPaginator)) {
            $batches = collect($batches);
        }
        if ($batches instanceof \Illuminate\Pagination\AbstractPaginator) {
            $batches->getCollection()->load('course');
        } elseif (method_exists($batches, 'load')) {
            $batches->load('course');
        }
        return view('homepage.running-batches-unified', compact('batches'));
    }
}
