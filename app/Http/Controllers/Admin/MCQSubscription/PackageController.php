<?php
namespace App\Http\Controllers\Admin\MCQSubscription;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class PackageController extends Controller
{
    private function baseQuery()
    {
        return DB::table('mcq_subscription_packages');
    }

    private function kinds(): array
    {
        return ['department', 'non-department'];
    }

    private function categories()
    {
        return DB::table('job_prep_categories')
            ->select('id', 'kind', 'name', 'status')
            ->orderBy('kind')->orderBy('sort_order')->orderBy('id')->get();
    }

    public function index()
    {
        $packages = $this->baseQuery()->orderByDesc('id')->paginate(20);
        $links = DB::table('mcq_subscription_package_categories')
            ->join('job_prep_categories', 'job_prep_categories.id', '=', 'mcq_subscription_package_categories.category_id')
            ->select('mcq_subscription_package_categories.package_id', 'job_prep_categories.name', 'job_prep_categories.kind')
            ->get()->groupBy('package_id');
        return view('admin.mcq-subscription.index', compact('packages', 'links'));
    }

    public function create()
    {
        return view('admin.mcq-subscription.form', ['package' => null, 'categories' => $this->categories(), 'selected' => []]);
    }

    public function edit(int $package)
    {
        $record = $this->baseQuery()->where('id', $package)->first();
        abort_unless($record, 404);
        $selected = DB::table('mcq_subscription_package_categories')->where('package_id', $package)->pluck('category_id')->map(fn ($id) => (int)$id)->all();
        return view('admin.mcq-subscription.form', ['package' => $record, 'categories' => $this->categories(), 'selected' => $selected]);
    }

    private function validated(Request $request): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:2000'],
            'duration_value' => ['required', 'integer', 'min:1', 'max:6000'],
            'duration_unit' => ['required', Rule::in(['days', 'months', 'years'])],
            'main_price' => ['required', 'numeric', 'min:0', 'max:9999999.99'],
            'discount_amount' => ['required', 'numeric', 'min:0', 'max:9999999.99'],
            'access_type' => ['required', Rule::in(['department', 'non-department', 'both'])],
            'category_ids' => ['required', 'array', 'min:1'],
            'category_ids.*' => ['required', 'integer', 'distinct', Rule::exists('job_prep_categories', 'id')->where('status', 'Active')],
            'reading_limit' => ['nullable', 'integer', 'min:1', 'max:1000000'],
            'quiz_limit' => ['nullable', 'integer', 'min:1', 'max:1000000'],
            'status' => ['required', Rule::in(['Active','Inactive'])],
            'sort_order' => ['required', 'integer', 'min:0', 'max:999999'],
            'referral_discount' => ['nullable', 'numeric', 'min:0', 'max:9999999.99'],
            'referrer_bonus' => ['nullable', 'numeric', 'min:0', 'max:9999999.99'],
            'extra_fee' => ['nullable', 'numeric', 'min:0', 'max:9999999.99'],
            'extra_fee_note' => ['nullable', 'string', 'max:500'],
        ], [
            'category_ids.required' => 'কমপক্ষে একটি Category নির্বাচন করো।',
            'category_ids.min' => 'কমপক্ষে একটি Category নির্বাচন করো।',
        ]);
        $mainCents = (int) round((float)$validated['main_price'] * 100);
        $discountCents = (int) round((float)$validated['discount_amount'] * 100);
        if ($discountCents > $mainCents) {
            throw \Illuminate\Validation\ValidationException::withMessages(['discount_amount' => 'Discount কখনো Main Price-এর বেশি হতে পারে না।']);
        }
        $validated['price'] = number_format(($mainCents - $discountCents) / 100, 2, '.', '');
        $referralCents = (int) round((float)($validated['referral_discount'] ?? 0) * 100);
        if ($referralCents > $mainCents - $discountCents) {
            throw \Illuminate\Validation\ValidationException::withMessages(['referral_discount' => 'Referral Discount কখনো Final Package Price-এর বেশি হতে পারে না।']);
        }
        $ids = array_map('intval', $validated['category_ids']);
        $categories = DB::table('job_prep_categories')->whereIn('id', $ids)->pluck('kind')->unique()->values()->all();
        if ($validated['access_type'] === 'department' && $categories !== ['department']) {
            throw \Illuminate\Validation\ValidationException::withMessages(['category_ids' => 'Department Only Package-এ শুধু Department Category নির্বাচন করতে হবে।']);
        }
        if ($validated['access_type'] === 'non-department' && $categories !== ['non-department']) {
            throw \Illuminate\Validation\ValidationException::withMessages(['category_ids' => 'Non-Department Only Package-এ শুধু Non-Department Category নির্বাচন করতে হবে।']);
        }
        // For 'both' the administrator can choose any mixture of departments.
        return $validated;
    }

    /** Guard against false "Success" when the additive pricing columns are not installed. */
    private function assertPricingColumns(): void
    {
        $required = ['referral_discount', 'referrer_bonus', 'extra_fee', 'extra_fee_note'];
        $missing = array_values(array_filter($required, fn ($column) => !Schema::hasColumn('mcq_subscription_packages', $column)));
        if ($missing) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'referral_discount' => 'MCQ Package Referral settings save হয়নি: Database-এ প্রয়োজনীয় Column নেই (' . implode(', ', $missing) . '). প্রথমে সংযুক্ত ADDITIVE SQL প্রয়োগ করুন।',
            ]);
        }
    }

    private function payload(array $v): array
    {
        $payload = [
            'name' => $v['name'],
            'description' => $v['description'] ?? null,
            'duration_value' => $v['duration_value'],
            'duration_unit' => $v['duration_unit'],
            'main_price' => $v['main_price'],
            'discount_amount' => $v['discount_amount'],
            'price' => $v['price'],
            'access_type' => $v['access_type'],
            'reading_limit' => $v['reading_limit'] ?? null,
            'quiz_limit' => $v['quiz_limit'] ?? null,
            'status' => $v['status'],
            'sort_order' => $v['sort_order'],
            'updated_at' => now(),
        ];
        // All referral fields must persist together. Never silently ignore missing columns.
        foreach (['referral_discount', 'referrer_bonus', 'extra_fee'] as $field) {
            $payload[$field] = number_format((float)($v[$field] ?? 0), 2, '.', '');
        }
        $payload['extra_fee_note'] = $v['extra_fee_note'] ?? null;
        return $payload;
    }

    private function attach(int $id, array $ids): void
    {
        DB::table('mcq_subscription_package_categories')->where('package_id', $id)->delete();
        $rows = array_map(fn ($category) => ['package_id' => $id, 'category_id' => (int)$category], $ids);
        DB::table('mcq_subscription_package_categories')->insert($rows);
    }

    public function store(Request $request)
    {
        $v = $this->validated($request);
        $this->assertPricingColumns();
        DB::transaction(function () use ($v) {
            $id = DB::table('mcq_subscription_packages')->insertGetId($this->payload($v) + ['created_at' => now()]);
            $this->attach($id, $v['category_ids']);
        });
        return redirect()->route('admin.mcq-subscription.index')->with('success', 'MCQ Subscription Package তৈরি হয়েছে।');
    }

    public function update(Request $request, int $package)
    {
        abort_unless($this->baseQuery()->where('id', $package)->exists(), 404);
        $v = $this->validated($request);
        $this->assertPricingColumns();
        DB::transaction(function () use ($package, $v) {
            $this->baseQuery()->where('id', $package)->update($this->payload($v));
            $this->attach($package, $v['category_ids']);
        });
        return redirect()->route('admin.mcq-subscription.index')->with('success', 'Package Update হয়েছে। আগে কেনা Subscription এই পরিবর্তনে প্রভাবিত হবে না।');
    }

    public function toggle(int $package)
    {
        $record = $this->baseQuery()->where('id', $package)->first();
        abort_unless($record, 404);
        $this->baseQuery()->where('id', $package)->update(['status' => $record->status === 'Active' ? 'Inactive' : 'Active', 'updated_at' => now()]);
        return back()->with('success', 'Package Status পরিবর্তন হয়েছে।');
    }
}
