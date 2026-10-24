<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Filter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class FilterCtrl extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /** Serial is assigned manually. No other filter is renumbered. */
    private function ensureUnusedSerial(int $serial, ?int $exceptId = null): void
    {
        $query = Filter::query()->where('order_number', $serial);
        if ($exceptId !== null) {
            $query->where('id', '!=', $exceptId);
        }
        $occupied = $query->orderBy('id')->first(['id', 'name']);
        if ($occupied) {
            throw ValidationException::withMessages([
                'order_number' => 'Serial ' . $serial . ' is currently used by "'
                    . $occupied->name . '" (Filter ID ' . $occupied->id
                    . '). Edit that filter and assign it another unused number first.',
            ]);
        }
    }

    public function index(Request $request)
    {
        // Fixed management chain: keep legacy filters and question assignments untouched.
        $stageLabels = [
            'প্রশ্ন টাইপ', 'কোর্সের নাম', 'প্রশ্ন টপিক', 'ডিপার্টমেন্ট নাম',
            'বুক প্রকাশনির নাম', 'সাবজেক্ট নাম', 'চ্যাপ্টার নাম',
            'প্রশ্ন সোর্স', 'সাব-সোর্স', 'পরীক্ষার সন', 'Question Quality filter',
        ];
        $filters = Filter::query()->where('status', 'Active')
            ->orderBy('order_number')->orderBy('id')->get();
        $stages = collect($stageLabels)->map(function ($label, $index) use ($filters) {
            // The existing manually ordered Filter IDs remain authoritative.
            return [
                'label' => $label, 'filter' => $filters->get($index),
                'position' => $index + 1,
            ];
        });
        $stageIds = $stages->pluck('filter')->filter()->pluck('id')->all();
        $legacyCutoff = Schema::hasTable('lebd_filter_visibility_baselines')
            ? (int) DB::table('lebd_filter_visibility_baselines')->where('id', 1)->value('legacy_max_sub_filter_id')
            : (int) DB::table('sub_filters')->max('id');
        $visibilityConfigured = Schema::hasTable('lebd_filter_visibility_baselines');
        $options = \App\Models\SubFilter::query()
            ->where('id', '>', $legacyCutoff)
            ->whereIn('filter_id', $stageIds)->where('status', 'Active')
            ->orderBy('id')->get(['id', 'filter_id', 'name', 'parent_id']);
        $links = \Illuminate\Support\Facades\Schema::hasTable('sub_filter_connections')
            ? DB::table('sub_filter_connections')
                ->whereIn('child_sub_filter_id', $options->pluck('id')->all())
                ->get(['parent_sub_filter_id', 'child_sub_filter_id'])
            : collect();
        $parentsByChild = [];
        foreach ($links as $link) {
            $parentsByChild[(int) $link->child_sub_filter_id][] = (int) $link->parent_sub_filter_id;
        }
        foreach ($options as $option) {
            if ($option->parent_id && !in_array((int) $option->parent_id, $parentsByChild[$option->id] ?? [], true)) {
                $parentsByChild[$option->id][] = (int) $option->parent_id;
            }
        }
        $optionPayload = $options->map(fn ($item) => [
            'id' => (int) $item->id, 'filter_id' => (int) $item->filter_id, 'name' => $item->name,
        ])->values();
        return view('layouts.filters.index', compact('stages', 'options', 'parentsByChild', 'optionPayload', 'visibilityConfigured'));
    }

    public function create()
    {
        // Main filters are fixed; existing edit and sub-filter creation remain available.
        return redirect()->route('filter.index');
        $courses = Course::where('status', 'Active')->get();
        return view('layouts.filters.create', compact('courses'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'course_id' => 'nullable|array',
            'course_id.*' => 'integer|exists:courses,id',
            'name' => 'required|string|max:255|unique:filters,name',
            'details' => 'nullable|string|max:1000',
            'order_number' => 'required|integer|min:1|max:999',
            'label' => 'nullable|in:Yes',
        ]);

        $this->ensureUnusedSerial((int) $data['order_number']);

        DB::transaction(function () use ($data) {
            $courses = $data['course_id'] ?? [];
            unset($data['course_id']);
            $data['status'] = 'Active';
            $filter = new Filter();
            foreach ($data as $field => $value) {
                $filter->{$field} = $value;
            }
            $filter->save();
            $filter->courses()->sync($courses);
        });

        Session::flash('success', 'New filter successfully created!');
        return redirect()->route('filter.index');
    }

    public function show($id)
    {
        $filter = Filter::findOrFail($id);
        return view('layouts.filters.read', compact('filter'));
    }

    public function edit($id)
    {
        $courses = Course::where('status', 'Active')->get();
        $filter = Filter::with('courses')->findOrFail($id);
        return view('layouts.filters.edit', compact('filter', 'courses'));
    }

    public function update(Request $request, $id)
    {
        $filter = Filter::findOrFail($id);
        $data = $request->validate([
            'course_id' => 'nullable|array',
            'course_id.*' => 'integer|exists:courses,id',
            'name' => 'required|string|max:255',
            'details' => 'nullable|string|max:1000',
            'slug' => 'nullable|string|max:255',
            'order_number' => 'required|integer|min:1|max:999',
            'label' => 'nullable|in:Yes',
        ]);

        $this->ensureUnusedSerial((int) $data['order_number'], (int) $filter->id);

        DB::transaction(function () use ($filter, $data) {
            $courses = $data['course_id'] ?? [];
            unset($data['course_id']);
            $data['label'] = $data['label'] ?? null;
            // An existing slug must not change from the edit screen.
            if ($filter->slug) {
                unset($data['slug']);
            }
            foreach ($data as $field => $value) {
                $filter->{$field} = $value;
            }
            $filter->save();
            $filter->courses()->sync($courses);
        });

        Session::flash('success', 'The filter successfully updated!');
        return redirect()->route('filter.index');
    }

    public function destroy($id)
    {
        if (Auth::user()->AuthorizeRoles('SuperAdmin')) {
            $filter = Filter::findOrFail($id);
            if ($filter->image && File::exists($filter->image)) {
                File::delete($filter->image);
            }
            $filter->courses()->detach();
            $filter->delete();
            Session::flash('Success', 'This filter successfully deleted');
            return redirect()->route('filter.index');
        }
        return back()->with('error', 'Permission Denied');
    }

    public function getFilters($course_id)
    {
        $course = Course::findOrFail($course_id);
        $filters = $course->filters()->with('subfilter')
            ->orderBy('order_number')->orderBy('id')
            ->select('filters.id', 'filters.name', 'filters.order_number')->get();
        return response()->json(['data' => $filters]);
    }
}
