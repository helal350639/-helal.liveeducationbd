<?php
namespace App\Http\Controllers\MCQJobPreparation;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

/** Universal package storefront. Only the MCQ source is connected in this release. */
class StudentPackageController extends Controller
{
    private function checkSection(string $section): void
    {
        abort_unless($section === 'mcq', 404);
    }

    private function groups(array $ids): array
    {
        if (!$ids) { return []; }
        $records = DB::table('mcq_subscription_package_categories as pc')
            ->join('job_prep_categories as c', 'c.id', '=', 'pc.category_id')
            ->whereIn('pc.package_id', $ids)
            ->where('c.status', 'Active')
            ->select('pc.package_id', 'c.name', 'c.kind')
            ->orderBy('c.sort_order')->orderBy('c.id')->get();
        $result = [];
        foreach ($records as $row) {
            $result[(int)$row->package_id][$row->kind][] = $row->name;
        }
        return $result;
    }

    public function catalog(string $section = 'mcq')
    {
        $this->checkSection($section);
        $packages = DB::table('mcq_subscription_packages')
            ->where('status', 'Active')->orderBy('sort_order')->orderBy('id')->get();
        $ids = $packages->pluck('id')->map(fn ($id) => (int)$id)->all();
        $categories = $this->groups($ids);
        return view('homepage.packages.index', compact('packages', 'categories', 'section'));
    }

    public function detail(string $section, int $package)
    {
        $this->checkSection($section);
        $record = DB::table('mcq_subscription_packages')
            ->where('status','Active')->where('id', $package)->first();
        abort_unless($record, 404);
        $groups = $this->groups([(int)$record->id]);
        return view('homepage.packages.details', [
            'package' => $record, 'categories' => $groups[(int)$record->id] ?? [],
            'section' => $section,
        ]);
    }

    /** Safe order review: recalculates every amount from the active database record. */
    public function checkout(string $section, int $package)
    {
        $this->checkSection($section);
        $record = DB::table('mcq_subscription_packages')
            ->where('status', 'Active')->where('id', $package)->first();
        abort_unless($record, 404);
        $groups = $this->groups([(int) $record->id]);
        return view('homepage.packages.order-review', [
            'package' => $record,
            'categories' => $groups[(int) $record->id] ?? [],
            'section' => $section,
        ]);
    }

    // Keep routes published in the previous MCQ student-package release working.
    public function index() { return $this->catalog('mcq'); }
    public function show(int $package) { return $this->detail('mcq', $package); }
}
