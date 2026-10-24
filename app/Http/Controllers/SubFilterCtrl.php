<?php

namespace App\Http\Controllers;

use App\Models\Filter;
use App\Models\SubFilter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class SubFilterCtrl extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    private function legacyCutoff(): int
    {
        if (!Schema::hasTable('lebd_filter_visibility_baselines')) {
            // Fail closed: never accidentally show legacy items before setup SQL is run.
            return (int) DB::table('sub_filters')->max('id');
        }
        return (int) DB::table('lebd_filter_visibility_baselines')
            ->where('id', 1)->value('legacy_max_sub_filter_id');
    }

    private function mainFilterIds(): array
    {
        // Mirror the eleven existing main filters used by FilterCtrl@index.
        return Filter::where('status', 'Active')->orderBy('order_number')
            ->orderBy('id')->limit(11)->pluck('id')->map(fn ($id) => (int) $id)->all();
    }

    public function index(Request $request)
    {
        if ($request->query('view') !== 'manage') {
            // Both /filter and /sub-filter display the SAME fixed main-filter screen.
            return app(FilterCtrl::class)->index($request);
        }
        $filterId = (int) $request->query('filter_id', 0);
        abort_unless(in_array($filterId, $this->mainFilterIds(), true), 404);
        $managedFilter = Filter::findOrFail($filterId);
        $search = trim((string) $request->query('search', ''));
        $perPage = (int) $request->query('per_page', 25);
        if (!in_array($perPage, [25, 50, 100], true)) $perPage = 25;
        $legacyCutoff = $this->legacyCutoff();
        $query = SubFilter::query()->where('filter_id', $filterId)
            ->where('id', '>', $legacyCutoff);
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%');
                if (ctype_digit($search)) $q->orWhere('id', (int) $search);
            });
        }
        $subfilters = $query->orderByDesc('id')->paginate($perPage)->withQueryString();
        $visibilityConfigured = Schema::hasTable('lebd_filter_visibility_baselines');
        return view('layouts.subfilters.index', compact('subfilters', 'managedFilter',
            'search', 'perPage', 'visibilityConfigured'));
    }

    private function connectionOptions(): array
    {
        $filters = Filter::where('status', 'Active')
            ->orderBy('order_number')->orderBy('id')->limit(11)->get();
        $subfilters = SubFilter::query()->where('id', '>', $this->legacyCutoff())
            ->whereIn('filter_id', $filters->pluck('id')->all())
            ->orderBy('id')->get(['id', 'filter_id', 'name']);
        return compact('filters', 'subfilters');
    }

    public function create()
    {
        $options = $this->connectionOptions();
        $requestedFilterId = (int) request()->query('filter_id', 0);
        $selectedFilterId = $options['filters']->contains('id', $requestedFilterId) ? $requestedFilterId : null;
        return view('layouts.subfilters.create', array_merge($options, compact('selectedFilterId')));
    }

    private function validateConnection(Request $request, ?SubFilter $current = null): array
    {
        $data = $request->validate([
            'filter_id' => 'required|integer|exists:filters,id',
            'name' => 'required|string|max:255',
            'details' => 'nullable|string|max:1000',
            'parent_connections' => 'nullable|array',
            'parent_connections.*' => 'integer|distinct|exists:sub_filters,id',
        ]);
        $filter = Filter::findOrFail((int)$data['filter_id']);
        $all = $this->mainFilterIds();
        if (!in_array((int) $filter->id, $all, true)) {
            throw ValidationException::withMessages(['filter_id' => 'Select one of the eleven main filters.']);
        }
        $position = array_search((int) $filter->id, $all, true);
        $previousFilterIds = $position !== false ? array_slice($all, 0, $position) : [];
        $isQualityFilter = $position === 10; // Stage 11 stays independent of all parent connections.
        $immediatePreviousId = ($position > 0 && !$isQualityFilter) ? $all[$position - 1] : null;
        $parentIds = array_values(array_unique(array_map('intval', $data['parent_connections'] ?? [])));

        if ((!$previousFilterIds || $isQualityFilter) && $parentIds) {
            throw ValidationException::withMessages(['parent_connections' => 'Question Type and Question Quality do not accept parent connections.']);
        }
        // Immediate previous stage remains mandatory so the cascading chain can work.
        // Earlier stages may also be linked and edited independently.
        if ($immediatePreviousId !== null && !$parentIds) {
            throw ValidationException::withMessages(['parent_connections' => 'Connect at least one option from the immediately previous filter.']);
        }
        if ($parentIds) {
            $candidates = SubFilter::query()->where('id', '>', $this->legacyCutoff())
                ->whereIn('id', $parentIds)->get(['id', 'filter_id']);
            if ($candidates->count() !== count($parentIds)
                || $candidates->contains(fn ($option) => !in_array((int)$option->filter_id, $previousFilterIds, true))
                || ($current && in_array((int)$current->id, $parentIds, true))) {
                throw ValidationException::withMessages([
                    'parent_connections' => 'Connections must belong to earlier main filters only.',
                ]);
            }
            if ($immediatePreviousId !== null && !$candidates->contains('filter_id', $immediatePreviousId)) {
                throw ValidationException::withMessages([
                    'parent_connections' => 'Choose at least one option from the immediately previous filter.',
                ]);
            }
        }
        return [$data, $parentIds];
    }

    private function syncConnections(SubFilter $subfilter, array $parentIds, int $filterId): void
    {
        // Mirror the first parent into legacy parent_id for existing source/sub-source features.
        // Additional parents live in the new connection table.
        $ids = $this->mainFilterIds();
        $position = array_search($filterId, $ids, true);
        $immediateId = ($position > 0 && $position !== 10) ? $ids[$position - 1] : null;
        $legacyParent = $immediateId !== null
            ? SubFilter::where('filter_id', $immediateId)->whereIn('id', $parentIds)->value('id')
            : null;
        DB::table('sub_filters')->where('id', $subfilter->id)
            ->update(['parent_id' => $legacyParent]);
        DB::table('sub_filter_connections')->where('child_sub_filter_id', $subfilter->id)->delete();
        if ($parentIds) {
            DB::table('sub_filter_connections')->insert(array_map(static function ($id) use ($subfilter) {
                return [
                    'parent_sub_filter_id' => $id,
                    'child_sub_filter_id' => $subfilter->id,
                ];
            }, $parentIds));
        }
    }

    public function store(Request $request)
    {
        if (!Schema::hasTable('lebd_filter_visibility_baselines')) {
            throw ValidationException::withMessages(['filter_id' => 'Run the Fix 12 visibility checkpoint SQL before creating new options.']);
        }
        [$data, $parentIds] = $this->validateConnection($request);
        // Ignore hidden legacy names when allowing a fresh management catalog.
        $duplicate = SubFilter::where('id', '>', $this->legacyCutoff())
            ->where('filter_id', $data['filter_id'])->where('name', $data['name'])->exists();
        if ($duplicate) {
            throw ValidationException::withMessages(['name' => 'This sub-filter name is already in use.']);
        }
        DB::transaction(function () use ($data, $parentIds) {
            $subfilter = new SubFilter();
            $subfilter->filter_id = $data['filter_id'];
            $subfilter->name = $data['name'];
            $subfilter->details = $data['details'] ?? null;
            $subfilter->status = 'Active';
            $subfilter->save();
            $this->syncConnections($subfilter, $parentIds, (int)$data['filter_id']);
        });
        Session::flash('success', 'New subfilter successfully created!');
        return redirect()->route('sub-filter.index');
    }

    public function show($id)
    {
        $subfilter = SubFilter::findOrFail($id);
        return view('layouts.subfilters.read', compact('subfilter'));
    }

    public function edit($id)
    {
        $subfilter = SubFilter::where('id', '>', $this->legacyCutoff())->findOrFail($id);
        $options = $this->connectionOptions();
        $connectionIds = DB::table('sub_filter_connections')
            ->where('child_sub_filter_id', $subfilter->id)
            ->pluck('parent_sub_filter_id')->map(fn($id) => (int)$id)->all();
        if ($subfilter->parent_id && !in_array((int)$subfilter->parent_id, $connectionIds, true)) {
            $connectionIds[] = (int)$subfilter->parent_id;
        }
        return view('layouts.subfilters.edit', array_merge($options, compact('subfilter', 'connectionIds')));
    }

    public function update(Request $request, $id)
    {
        $subfilter = SubFilter::where('id', '>', $this->legacyCutoff())->findOrFail($id);
        [$data, $parentIds] = $this->validateConnection($request, $subfilter);
        $duplicate = SubFilter::where('id', '>', $this->legacyCutoff())
            ->where('id', '!=', $subfilter->id)
            ->where('filter_id', $data['filter_id'])->where('name', $data['name'])->exists();
        if ($duplicate) {
            throw ValidationException::withMessages(['name' => 'This sub-filter name is already used in this main filter.']);
        }
        DB::transaction(function () use ($subfilter, $data, $parentIds) {
            $subfilter->filter_id = $data['filter_id'];
            $subfilter->name = $data['name'];
            $subfilter->details = $data['details'] ?? null;
            $subfilter->save();
            $this->syncConnections($subfilter, $parentIds, (int)$data['filter_id']);
        });
        Session::flash('success', 'The subfilter successfully updated!');
        return redirect()->route('sub-filter.index');
    }

    public function destroy($id)
    {
        if (!Auth::user()->AuthorizeRoles('SuperAdmin')) {
            return back()->with('error', 'Permission Denied');
        }
        $subfilter = SubFilter::where('id', '>', $this->legacyCutoff())->findOrFail($id);
        DB::transaction(function () use ($subfilter) {
            DB::table('sub_filter_connections')->where('child_sub_filter_id', $subfilter->id)
                ->orWhere('parent_sub_filter_id', $subfilter->id)->delete();
            if ($subfilter->image && File::exists('img/subfilter/' . $subfilter->image)) {
                File::delete('img/subfilter/' . $subfilter->image);
            }
            $subfilter->delete();
        });
        Session::flash('Success', 'This subfilter successfully deleted');
        return redirect()->route('sub-filter.index');
    }

    // Preserve the existing endpoint and response shape for the current question page.
    public function getSubSource($parent_id)
    {
        $subsource = SubFilter::where('parent_id', $parent_id)->get();
        return response()->json(['subsource' => $subsource], 200);
    }
}
