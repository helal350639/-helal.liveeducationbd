<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DailyUpdate;
use App\Models\DailyUpdateCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * LIVE EDUCATION BD — Admin Daily Update (questions/posts) backend.
 *
 * Uses the existing manually created `lebd_daily_updates` SQL table.
 * Step 15 connects create/list/edit/delete/publish through authenticated routes.
 * Comments and public-page UI connection follow separately.
 */
class DailyUpdateController extends Controller
{
    /** Data for the actual management listing (Blade connection follows). */
    public function index(Request $request): View
    {
        $query = DailyUpdate::query()->with('category')->latestUpdates();

        if ($request->filled('search')) {
            $term = trim((string) $request->query('search'));
            $query->where(function ($q) use ($term) {
                $q->where('question', 'like', '%' . $term . '%')
                    ->orWhere('title', 'like', '%' . $term . '%');
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', (int) $request->query('category_id'));
        }
        if (in_array($request->query('status'), ['draft', 'published'], true)) {
            $query->where('status', $request->query('status'));
        }
        if ($request->filled('year')) {
            $query->where('year', (int) $request->query('year'));
        }
        if ($request->filled('month')) {
            $query->where('month', (int) $request->query('month'));
        }

        return view('admin.daily-update-all', [
            'updates' => $query->paginate(15)->withQueryString(),
            'categories' => DailyUpdateCategory::displayOrder()->get(),
            'years' => DailyUpdate::query()->distinct()->orderByDesc('year')->pluck('year'),
            'totalUpdates' => DailyUpdate::query()->count(),
            'publishedUpdates' => DailyUpdate::query()->where('status', 'published')->count(),
            'draftUpdates' => DailyUpdate::query()->where('status', 'draft')->count(),
            'usedCategories' => DailyUpdate::query()->distinct()->count('category_id'),
        ]);
    }

    /** Show the Create Form with categories loaded from MySQL. */
    public function create(): View
    {
        return view('admin.daily-update-create', [
            'categories' => DailyUpdateCategory::active()->displayOrder()->get(),
        ]);
    }

    /** Create one question with four options; status is draft or published. */
    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);
        $imagePath = null;

        try {
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('daily-updates', 'public');
            }

            $created = DB::transaction(function () use ($data, $imagePath) {
                return DailyUpdate::create($this->attributes($data) + [
                    'featured_image' => $imagePath,
                    'created_by' => auth()->id(),
                    'updated_by' => auth()->id(),
                ]);
            });
        } catch (\Throwable $e) {
            if ($imagePath) {
                Storage::disk('public')->delete($imagePath);
            }
            throw $e;
        }

        // After saving, keep the admin on Create with the new record ID confirmation.
        return redirect()
            ->route('admin.daily-update.create')
            ->with('success', 'Daily Update #' . $created->id . ' saved in MySQL as ' . strtoupper($created->status) . '.');
    }

    /** Load an existing question into the upcoming live Edit form. */
    public function edit(DailyUpdate $update): View
    {
        return view('admin.daily-update-edit', [
            'update' => $update,
            'categories' => DailyUpdateCategory::displayOrder()->get(),
        ]);
    }

    /** Update only this question. Old image remains unless a new one is saved. */
    public function update(Request $request, DailyUpdate $update): RedirectResponse
    {
        $data = $this->validatedData($request, $update);
        $newImagePath = null;
        $oldImagePath = $update->featured_image;

        try {
            if ($request->hasFile('image')) {
                $newImagePath = $request->file('image')->store('daily-updates', 'public');
            }

            DB::transaction(function () use ($update, $data, $newImagePath) {
                $values = $this->attributes($data, $update);
                if ($newImagePath !== null) {
                    $values['featured_image'] = $newImagePath;
                }
                $values['updated_by'] = auth()->id();
                $update->update($values);
            });
        } catch (\Throwable $e) {
            if ($newImagePath) {
                Storage::disk('public')->delete($newImagePath);
            }
            throw $e;
        }

        if ($newImagePath && $oldImagePath && $oldImagePath !== $newImagePath) {
            Storage::disk('public')->delete($oldImagePath);
        }

        return redirect()
            ->route('admin.daily-update.all')
            ->with('success', 'Daily Update successfully updated.');
    }

    /** No GET request may change publication status. */
    public function togglePublication(DailyUpdate $update): RedirectResponse
    {
        $publish = $update->status !== 'published';

        $update->update([
            'status' => $publish ? 'published' : 'draft',
            'published_at' => $publish ? now() : null,
            'updated_by' => auth()->id(),
        ]);

        return redirect()->route('admin.daily-update.all')
            ->with('success', $publish ? 'Update published.' : 'Update unpublished.');
    }

    /** Soft delete, preserving related comments and historical data. */
    public function destroy(DailyUpdate $update): RedirectResponse
    {
        $update->delete();

        return redirect()->route('admin.daily-update.all')
            ->with('success', 'Daily Update moved to deleted records.');
    }

    /**
     * Accept a date and the fields from the existing Demo Create Form.
     * The upcoming live Blade will use category_id, not demo category name.
     */

    /** Show the bulk import form. Nothing is saved until POST and all rows pass validation. */
    public function bulkCreate(): View
    {
        return view('admin.daily-update-bulk', [
            'categories' => DailyUpdateCategory::active()->displayOrder()->get(),
            'oldestAllowed' => now()->startOfMonth()->subMonths(11)->format('Y-m-d'),
            'today' => now()->format('Y-m-d'),
        ]);
    }

    /** Import 1-100 UTF-8 CSV or Excel-copied TSV questions in a single transaction. */
    public function bulkStore(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'category_id' => [
                'required', 'integer',
                Rule::exists('lebd_daily_update_categories', 'id')->where('is_active', 1),
            ],
            'update_date' => ['required', 'date_format:Y-m-d'],
            'status' => ['required', Rule::in(['draft', 'published'])],
            'csv_file' => ['nullable', 'file', 'max:2048'],
            'bulk_text' => ['nullable', 'string', 'max:1500000'],
        ]);

        $date = Carbon::createFromFormat('!Y-m-d', $data['update_date']);
        if ($date->lt(now()->startOfMonth()->subMonths(11)) || $date->gt(today())) {
            throw ValidationException::withMessages([
                'update_date' => 'Only dates from the current month and previous 11 months are allowed.',
            ]);
        }
        $hasFile = $request->hasFile('csv_file');
        $hasText = trim((string) ($data['bulk_text'] ?? '')) !== '';
        if ($hasFile === $hasText) {
            throw ValidationException::withMessages([
                'bulk_text' => 'Supply either a CSV/TSV file OR pasted questions (one source only).',
            ]);
        }

        if ($hasFile) {
            $extension = strtolower($request->file('csv_file')->getClientOriginalExtension());
            if (!in_array($extension, ['csv', 'tsv', 'txt'], true)) {
                throw ValidationException::withMessages([
                    'csv_file' => 'Please export your Excel questions as CSV UTF-8 or TSV, not XLSX.',
                ]);
            }
            $stream = fopen($request->file('csv_file')->getRealPath(), 'rb');
        } else {
            $stream = fopen('php://temp', 'w+b');
            fwrite($stream, (string) $data['bulk_text']);
            rewind($stream);
        }
        if ($stream === false) {
            throw ValidationException::withMessages(['csv_file' => 'Could not read this question file.']);
        }

        try {
            $rows = $this->parseBulkRows($stream, $hasFile ? $extension : null);
        } finally {
            fclose($stream);
        }
        $category = DailyUpdateCategory::query()->findOrFail((int) $data['category_id']);
        $status = $data['status'];
        DB::transaction(function () use ($rows, $date, $category, $status) {
            foreach ($rows as $row) {
                DailyUpdate::create([
                    'category_id' => $category->id,
                    'title' => mb_substr($row['question'], 0, 255),
                    'question' => $row['question'],
                    'option_a' => $row['option_a'],
                    'option_b' => $row['option_b'],
                    'option_c' => $row['option_c'],
                    'option_d' => $row['option_d'],
                    'correct_option' => $row['correct_answer'],
                    'explanation' => $row['explanation'],
                    'topic' => $row['topic'],
                    'update_date' => $date->toDateString(),
                    'year' => (int) $date->format('Y'),
                    'month' => (int) $date->format('m'),
                    'status' => $status,
                    'published_at' => $status === 'published' ? now() : null,
                    'created_by' => auth()->id(),
                    'updated_by' => auth()->id(),
                ]);
            }
        });

        return redirect()->route('admin.daily-update.preview', [
            'section' => $category->slug,
            'archive' => $date->format('Y-m'),
        ])->with('success', count($rows) . ' questions saved as ' . strtoupper($status) . '. ' . ($status === 'draft' ? 'Review below before publishing.' : 'Already visible to students.'));
    }

    /** Admin-only preview uses the SAME student Blade, including otherwise hidden drafts. */
    public function studentPreview(): View
    {
        return view('daily-update', ['duAdminPreview' => true]);
    }

    /** Explicit admin confirmation to publish drafts in one category + calendar month. */
    public function publishMonth(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'category_id' => ['required', 'integer', Rule::exists('lebd_daily_update_categories', 'id')],
            'archive' => ['required', 'date_format:Y-m'],
        ]);
        $month = Carbon::createFromFormat('!Y-m', $data['archive']);
        if ($month->lt(now()->startOfMonth()->subMonths(11)) || $month->gt(now()->startOfMonth())) {
            throw ValidationException::withMessages(['archive' => 'Month is outside the most recent 12 calendar months.']);
        }
        $changed = DailyUpdate::query()
            ->where('category_id', (int) $data['category_id'])
            ->whereBetween('update_date', [$month->copy()->startOfMonth()->toDateString(), $month->copy()->endOfMonth()->toDateString()])
            ->where('update_date', '<=', today()->toDateString())
            ->where('status', 'draft')
            ->update(['status' => 'published', 'published_at' => now(), 'updated_by' => auth()->id(), 'updated_at' => now()]);
        $category = DailyUpdateCategory::query()->findOrFail((int) $data['category_id']);
        return redirect()->route('admin.daily-update.preview', [
            'section' => $category->slug,
            'archive' => $month->format('Y-m'),
        ])->with('success', $changed . ' draft questions published.');
    }

    /** Read headers and rows from Excel/Google Sheets paste or UTF-8 CSV file. */
    private function parseBulkRows($stream, ?string $extension): array
    {
        $first = fgets($stream);
        if ($first === false) {
            throw ValidationException::withMessages(['bulk_text' => 'The import is empty.']);
        }
        $first = preg_replace('/^\xEF\xBB\xBF/', '', $first);
        $delimiter = ($extension === 'tsv' || ($extension === null && substr_count($first, "\t") > 0)) ? "\t" : ',';
        // The first row is read again via fgetcsv so quoted CSV fields work correctly.
        rewind($stream);
        // A UTF-8 BOM is harmless: strip it from the first parsed column below.
        $expected = ['question', 'option_a', 'option_b', 'option_c', 'option_d', 'correct_answer', 'explanation', 'topic'];
        $headers = null;
        $items = [];
        $seen = [];
        $line = 0;
        while (($cells = fgetcsv($stream, 0, $delimiter, '"', '')) !== false) {
            $line++;
            $cells = array_map(fn ($value) => trim((string) $value), $cells);
            if ($line === 1 && isset($cells[0])) {
                $cells[0] = preg_replace('/^\xEF\xBB\xBF/', '', $cells[0]);
            }
            if (count(array_filter($cells, fn ($v) => $v !== '')) === 0) {
                continue;
            }
            if ($headers === null && strtolower((string) $cells[0]) === 'question') {
                $headers = array_map('strtolower', $cells);
                foreach (array_slice($expected, 0, 6) as $required) {
                    if (!in_array($required, $headers, true)) {
                        throw ValidationException::withMessages(['bulk_text' => 'Missing CSV/TSV header: ' . $required]);
                    }
                }
                continue;
            }
            if (count($items) >= 100) {
                throw ValidationException::withMessages(['bulk_text' => 'Maximum 100 questions per upload. Split larger sets into separate uploads.']);
            }
            $row = [];
            foreach ($expected as $i => $key) {
                $column = $headers === null ? $i : array_search($key, $headers, true);
                $row[$key] = $column === false ? '' : ($cells[$column] ?? '');
            }
            foreach (array_slice($expected, 0, 6) as $required) {
                if ($row[$required] === '') {
                    throw ValidationException::withMessages(['bulk_text' => 'Row ' . $line . ': ' . $required . ' is required. Nothing was saved.']);
                }
            }
            foreach (['question' => 20000, 'option_a' => 1000, 'option_b' => 1000, 'option_c' => 1000, 'option_d' => 1000, 'explanation' => 40000, 'topic' => 150] as $field => $limit) {
                if (mb_strlen($row[$field]) > $limit) {
                    throw ValidationException::withMessages(['bulk_text' => 'Row ' . $line . ': ' . $field . ' is too long.']);
                }
            }
            $answer = mb_strtoupper(rtrim(trim($row['correct_answer']), ').।'));
            $answer = ['ক' => 'A', 'খ' => 'B', 'গ' => 'C', 'ঘ' => 'D'][$answer] ?? $answer;
            if (!in_array($answer, ['A', 'B', 'C', 'D'], true)) {
                throw ValidationException::withMessages(['bulk_text' => 'Row ' . $line . ': correct_answer must be A, B, C or D (or ক, খ, গ, ঘ).']);
            }
            $row['correct_answer'] = $answer;
            $fingerprint = mb_strtolower($row['question']);
            if (isset($seen[$fingerprint])) {
                throw ValidationException::withMessages(['bulk_text' => 'Row ' . $line . ': duplicate question in this upload. Nothing was saved.']);
            }
            $seen[$fingerprint] = true;
            $items[] = $row;
        }
        if ($items === []) {
            throw ValidationException::withMessages(['bulk_text' => 'No valid question rows were found.']);
        }
        return $items;
    }

    private function validatedData(Request $request, ?DailyUpdate $update = null): array
    {
        $rules = [
            'category_id' => [
                'required', 'integer',
                Rule::exists('lebd_daily_update_categories', 'id'),
            ],
            'question' => ['required', 'string', 'max:20000'],
            'title' => ['nullable', 'string', 'max:255'],
            'option_a' => ['required', 'string', 'max:1000'],
            'option_b' => ['required', 'string', 'max:1000'],
            'option_c' => ['required', 'string', 'max:1000'],
            'option_d' => ['required', 'string', 'max:1000'],
            'correct_answer' => ['required', Rule::in(['A', 'B', 'C', 'D'])],
            'explanation' => ['nullable', 'string', 'max:40000'],
            'topic' => ['nullable', 'string', 'max:150'],
            'update_date' => ['required', 'date_format:Y-m-d'],
            'year' => ['nullable', 'integer', 'between:2000,2100'],
            'month' => ['nullable', 'integer', 'between:1,12'],
            'status' => ['required', Rule::in(['draft', 'published'])],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];

        $data = $request->validate($rules);
        $date = Carbon::createFromFormat('!Y-m-d', $data['update_date']);

        if ((int) ($data['year'] ?? $date->year) !== (int) $date->year
            || (int) ($data['month'] ?? $date->month) !== (int) $date->month) {
            throw ValidationException::withMessages([
                'update_date' => 'The date, year and month must match.',
            ]);
        }

        $category = DailyUpdateCategory::query()->findOrFail((int) $data['category_id']);
        if (!$category->is_active && (!$update || $update->category_id !== $category->id)) {
            throw ValidationException::withMessages([
                'category_id' => 'Please choose an active category.',
            ]);
        }

        return $data;
    }

    /** Map visible form names to the exact phpMyAdmin column names. */
    private function attributes(array $data, ?DailyUpdate $existing = null): array
    {
        $date = Carbon::createFromFormat('!Y-m-d', $data['update_date']);
        $status = $data['status'];

        return [
            'category_id' => (int) $data['category_id'],
            'title' => ($data['title'] ?? null) ?: mb_substr(trim($data['question']), 0, 255),
            'question' => trim($data['question']),
            'option_a' => trim($data['option_a']),
            'option_b' => trim($data['option_b']),
            'option_c' => trim($data['option_c']),
            'option_d' => trim($data['option_d']),
            'correct_option' => $data['correct_answer'],
            'explanation' => $data['explanation'] ?? null,
            'topic' => $data['topic'] ?? null,
            'update_date' => $data['update_date'],
            'year' => (int) $date->year,
            'month' => (int) $date->month,
            'status' => $status,
            'published_at' => $status === 'published'
                ? ($existing?->published_at ?? now())
                : null,
        ];
    }
}
