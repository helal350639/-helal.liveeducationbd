<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DailyUpdate;
use App\Models\DailyUpdateCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * LIVE EDUCATION BD — Daily Update Category Management.
 *
 * Requires the existing lebd_daily_update_categories / lebd_daily_updates
 * tables and DailyUpdateCategory / DailyUpdate models.
 *
 * IMPORTANT: All routes calling this controller MUST be inside the existing
 * admin web auth middleware group. Do not expose write endpoints publicly.
 * Routes + the currently DEMO-only Category Blade will be connected next.
 */
class DailyUpdateCategoryController extends Controller
{
    /** Render the existing Admin Category screen, with real records available. */
    public function index(): View
    {
        $categories = DailyUpdateCategory::query()
            ->withCount('updates')
            ->displayOrder()
            ->get();

        return view('admin.daily-update-categories', [
            'categories' => $categories,
        ]);
    }

    /** Provide real category records to the upcoming AJAX-enabled Blade. */
    public function listing(): JsonResponse
    {
        $categories = DailyUpdateCategory::query()
            ->withCount('updates')
            ->displayOrder()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $categories->map(fn (DailyUpdateCategory $category) => $this->formatCategory($category)),
        ]);
    }

    /** Create a new category (not a Daily Update question). */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $data = $request->validate($this->rules());

        $category = DailyUpdateCategory::create([
            'name' => $data['name'],
            'slug' => $data['slug'],
            'icon' => $data['icon'] ?? null,
            'description' => $data['description'] ?? null,
            'is_active' => $data['status'] === 'active',
            'sort_order' => (int) ($data['sort_order'] ?? 0),
        ]);

        return $this->respond($request, 'Category successfully created.', $category, 201);
    }

    /** Save edits to an existing category. */
    public function update(Request $request, DailyUpdateCategory $category): JsonResponse|RedirectResponse
    {
        $data = $request->validate($this->rules($category->id));

        $category->update([
            'name' => $data['name'],
            'slug' => $data['slug'],
            'icon' => $data['icon'] ?? null,
            'description' => $data['description'] ?? null,
            'is_active' => $data['status'] === 'active',
            'sort_order' => (int) ($data['sort_order'] ?? $category->sort_order),
        ]);

        return $this->respond($request, 'Category successfully updated.', $category);
    }

    /** Explicit POST/PATCH request only; do not change status through GET. */
    public function toggle(Request $request, DailyUpdateCategory $category): JsonResponse|RedirectResponse
    {
        $category->update(['is_active' => ! $category->is_active]);

        return $this->respond(
            $request,
            $category->is_active ? 'Category activated.' : 'Category deactivated.',
            $category
        );
    }

    /**
     * Delete an empty category only. Also count soft-deleted updates to
     * respect the DB foreign key and avoid losing historical associations.
     */
    public function destroy(Request $request, DailyUpdateCategory $category): JsonResponse|RedirectResponse
    {
        $blocked = DB::transaction(function () use ($category): bool {
            $locked = DailyUpdateCategory::query()->lockForUpdate()->findOrFail($category->id);

            if (DailyUpdate::withTrashed()->where('category_id', $locked->id)->exists()) {
                return true;
            }

            $locked->delete();
            return false;
        });

        if ($blocked) {
            $message = 'This category contains updates and cannot be deleted. Move or delete its updates first.';

            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $message], 409);
            }

            return redirect()->route('admin.daily-update.categories')->with('error', $message);
        }

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Category deleted.']);
        }

        return redirect()->route('admin.daily-update.categories')->with('success', 'Category deleted.');
    }

    /** Match the existing Demo Form's status values (active / inactive). */
    private function rules(?int $exceptId = null): array
    {
        $uniqueSlug = Rule::unique('lebd_daily_update_categories', 'slug');
        if ($exceptId !== null) {
            $uniqueSlug->ignore($exceptId);
        }

        return [
            'name' => ['required', 'string', 'max:150'],
            'slug' => [
                'required', 'string', 'max:160',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                $uniqueSlug,
            ],
            'icon' => ['nullable', 'string', 'max:80'],
            'description' => ['nullable', 'string', 'max:5000'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'sort_order' => ['nullable', 'integer', 'between:0,65535'],
        ];
    }

    private function respond(
        Request $request,
        string $message,
        DailyUpdateCategory $category,
        int $httpStatus = 200
    ): JsonResponse|RedirectResponse {
        if ($request->expectsJson()) {
            $category->loadCount('updates');

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $this->formatCategory($category),
            ], $httpStatus);
        }

        return redirect()->route('admin.daily-update.categories')->with('success', $message);
    }

    private function formatCategory(DailyUpdateCategory $category): array
    {
        return [
            'id' => $category->id,
            'name' => $category->name,
            'slug' => $category->slug,
            'icon' => $category->icon,
            'description' => $category->description,
            'status' => $category->is_active ? 'active' : 'inactive',
            'posts' => (int) ($category->updates_count ?? 0),
            'sort_order' => (int) $category->sort_order,
        ];
    }
}
