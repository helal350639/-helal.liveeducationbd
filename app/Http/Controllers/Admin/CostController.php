<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class CostController extends Controller
{
    private function categoryOptions(): array
    {
        if (!Schema::hasTable('admin_balance_cost_categories')) {
            return [];
        }

        return DB::table('admin_balance_cost_categories')
            ->orderBy('name')
            ->pluck('name', 'name')
            ->toArray();
    }

    private function pageData(int $month, int $year): array
    {
        $expenses = DB::table('admin_balance_costs')
            ->whereYear('entry_at', $year)
            ->whereMonth('entry_at', $month)
            ->orderBy('entry_at', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $now = Carbon::now('Asia/Dhaka');

        return [
            'section' => 'Cost',
            'sectionBn' => 'ব্যয় হিসাব',
            'expenses' => $expenses,
            'monthlyTotal' => (float) DB::table('admin_balance_costs')
                ->whereYear('entry_at', $year)
                ->whereMonth('entry_at', $month)
                ->sum('amount'),
            'selectedMonth' => $month,
            'selectedYear' => $year,
            'years' => range(max(2020, $now->year - 5), $now->year + 1),
            'nowDhaka' => $now->format('Y-m-d\TH:i'),
            'expenseHeads' => $this->categoryOptions(),
            'monthLabel' => Carbon::createFromDate($year, $month, 1)
                ->locale('bn')->translatedFormat('F Y'),
            'archiveMode' => false,
        ];
    }

    public function index(Request $request)
    {
        // Main Cost page always shows the current Bangladesh month.
        $now = Carbon::now('Asia/Dhaka');

        return view('admin.balance.cost', $this->pageData($now->month, $now->year) + [
            'editExpense' => null,
            'archiveMode' => false,
        ]);
    }

    public function archive(Request $request)
    {
        $now = Carbon::now('Asia/Dhaka');
        $month = (int) $request->input('month', $now->copy()->subMonth()->month);
        $year = (int) $request->input('year', $now->copy()->subMonth()->year);

        if ($month < 1 || $month > 12 || $year < 2000 || $year > $now->year) {
            $previous = $now->copy()->subMonth();
            $month = $previous->month;
            $year = $previous->year;
        }

        $data = $this->pageData($month, $year);
        $data['archiveMode'] = true;
        $data['editExpense'] = null;

        return view('admin.balance.cost', $data);
    }

    public function edit($id)
    {
        $expense = DB::table('admin_balance_costs')->where('id', $id)->first();
        abort_if(!$expense, 404);

        $date = Carbon::parse($expense->entry_at, 'Asia/Dhaka');
        $data = $this->pageData($date->month, $date->year);
        $data['editExpense'] = $expense;
        $data['editDateTime'] = $date->format('Y-m-d\TH:i');
        $data['archiveMode'] = true;

        // Keep a historical category selectable even if it was removed from the category list.
        if (!array_key_exists($expense->expense_head, $data['expenseHeads'])) {
            $data['expenseHeads'][$expense->expense_head] = $expense->expense_head;
        }

        return view('admin.balance.cost', $data);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'entries' => 'required|array|min:1|max:50',
            'entries.*.entry_at' => 'required|date',
            'entries.*.expense_head' => 'required|string|max:120',
            'entries.*.amount' => 'required|numeric|min:0.01|max:9999999999.99',
        ], [
            'entries.required' => 'কমপক্ষে একটি ব্যয়ের এন্ট্রি যোগ করুন।',
            'entries.*.entry_at.required' => 'তারিখ ও সময় আবশ্যক।',
            'entries.*.expense_head.required' => 'ম্যানুয়ালি যোগ করা ব্যয়ের খাত নির্বাচন করুন।',
            'entries.*.amount.required' => 'টাকার পরিমাণ লিখুন।',
        ]);

        $validCategories = array_values($this->categoryOptions());
        foreach ($validated['entries'] as $entry) {
            if (!in_array($entry['expense_head'], $validCategories, true)) {
                return back()->withErrors([
                    'expense_category' => 'এই ব্যয়ের খাতটি তালিকায় নেই। আগে নতুন খরচের খাত যোগ করুন।'
                ])->withInput();
            }
        }

        DB::transaction(function () use ($validated) {
            foreach ($validated['entries'] as $entry) {
                DB::table('admin_balance_costs')->insert([
                    'entry_at' => Carbon::parse($entry['entry_at'], 'Asia/Dhaka')
                        ->timezone('Asia/Dhaka')->format('Y-m-d H:i:s'),
                    'expense_head' => trim($entry['expense_head']),
                    'other_description' => null,
                    'amount' => $entry['amount'],
                    'created_by' => Auth::id(),
                    'created_at' => Carbon::now('Asia/Dhaka'),
                    'updated_at' => Carbon::now('Asia/Dhaka'),
                ]);
            }
        });

        $firstEntry = $validated['entries'][0];
        $savedAt = Carbon::parse($firstEntry['entry_at'], 'Asia/Dhaka');

        $now = Carbon::now('Asia/Dhaka');
        $targetRoute = ($savedAt->month === $now->month && $savedAt->year === $now->year)
            ? 'admin.balance.cost'
            : 'admin.balance.cost.archive';

        return redirect()->route($targetRoute, [
            'month' => $savedAt->month,
            'year' => $savedAt->year,
        ])->with('success', 'ব্যয়ের এন্ট্রি সফলভাবে সংরক্ষণ করা হয়েছে।');
    }

    public function update(Request $request, $id)
    {
        $expense = DB::table('admin_balance_costs')->where('id', $id)->first();
        abort_if(!$expense, 404);

        $validated = $request->validate([
            'entry_at' => 'required|date',
            'expense_head' => 'required|string|max:120',
            'amount' => 'required|numeric|min:0.01|max:9999999999.99',
        ]);

        $validCategories = array_values($this->categoryOptions());
        if (!in_array($validated['expense_head'], $validCategories, true) &&
            $validated['expense_head'] !== $expense->expense_head) {
            return back()->withErrors([
                'expense_category' => 'এই ব্যয়ের খাতটি তালিকায় নেই। আগে নতুন খরচের খাত যোগ করুন।'
            ])->withInput();
        }

        $date = Carbon::parse($validated['entry_at'], 'Asia/Dhaka');

        DB::table('admin_balance_costs')->where('id', $id)->update([
            'entry_at' => $date->timezone('Asia/Dhaka')->format('Y-m-d H:i:s'),
            'expense_head' => trim($validated['expense_head']),
            'other_description' => null,
            'amount' => $validated['amount'],
            'updated_at' => Carbon::now('Asia/Dhaka'),
        ]);

        $now = Carbon::now('Asia/Dhaka');
        $targetRoute = ($date->month === $now->month && $date->year === $now->year)
            ? 'admin.balance.cost'
            : 'admin.balance.cost.archive';

        return redirect()->route($targetRoute, [
            'month' => $date->month,
            'year' => $date->year,
        ])->with('success', 'ব্যয়ের তথ্য সফলভাবে আপডেট হয়েছে।');
    }

    public function destroy($id)
    {
        $expense = DB::table('admin_balance_costs')->where('id', $id)->first();
        abort_if(!$expense, 404);

        $date = Carbon::parse($expense->entry_at, 'Asia/Dhaka');

        DB::table('admin_balance_costs')->where('id', $id)->delete();

        $now = Carbon::now('Asia/Dhaka');
        $targetRoute = ($date->month === $now->month && $date->year === $now->year)
            ? 'admin.balance.cost'
            : 'admin.balance.cost.archive';

        return redirect()->route($targetRoute, [
            'month' => $date->month,
            'year' => $date->year,
        ])->with('success', 'ব্যয়ের এন্ট্রি মুছে ফেলা হয়েছে।');
    }

    public function storeCategory(Request $request)
    {
        if (!Schema::hasTable('admin_balance_cost_categories')) {
            return response()->json([
                'message' => 'Category table তৈরি করা হয়নি। phpMyAdmin SQL চালান।'
            ], 500);
        }

        $data = $request->validate([
            'name' => 'required|string|max:120',
        ]);

        $name = trim($data['name']);
        $exists = DB::table('admin_balance_cost_categories')
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'এই খরচের খাতটি আগে থেকেই রয়েছে।',
                'name' => $name,
            ], 422);
        }

        $id = DB::table('admin_balance_cost_categories')->insertGetId([
            'name' => $name,
            'created_by' => Auth::id(),
            'created_at' => Carbon::now('Asia/Dhaka'),
            'updated_at' => Carbon::now('Asia/Dhaka'),
        ]);

        return response()->json([
            'message' => 'নতুন খরচের খাত যোগ হয়েছে।',
            'category' => ['id' => $id, 'name' => $name],
        ]);
    }
}
