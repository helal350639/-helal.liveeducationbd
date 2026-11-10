<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class SummaryController extends Controller
{
    private function monthNames(): array
    {
        return [
            1 => 'জানুয়ারি', 2 => 'ফেব্রুয়ারি', 3 => 'মার্চ',
            4 => 'এপ্রিল', 5 => 'মে', 6 => 'জুন',
            7 => 'জুলাই', 8 => 'আগস্ট', 9 => 'সেপ্টেম্বর',
            10 => 'অক্টোবর', 11 => 'নভেম্বর', 12 => 'ডিসেম্বর',
        ];
    }

    private function monthlyTotals(string $table, int $year): array
    {
        $totals = array_fill(1, 12, 0.0);

        if (!Schema::hasTable($table)) {
            return $totals;
        }

        $rows = DB::table($table)
            ->selectRaw('MONTH(entry_at) as month_number, SUM(amount) as total_amount')
            ->whereYear('entry_at', $year)
            ->groupByRaw('MONTH(entry_at)')
            ->get();

        foreach ($rows as $row) {
            $month = (int) $row->month_number;
            if ($month >= 1 && $month <= 12) {
                $totals[$month] = (float) $row->total_amount;
            }
        }

        return $totals;
    }

    private function reportData(int $year): array
    {
        $income = $this->monthlyTotals('admin_balance_incomes', $year);
        $cost = $this->monthlyTotals('admin_balance_costs', $year);
        $months = [];

        $annualIncome = 0.0;
        $annualCost = 0.0;

        foreach ($this->monthNames() as $number => $name) {
            $incomeAmount = $income[$number];
            $costAmount = $cost[$number];
            $difference = abs($incomeAmount - $costAmount);

            if ($incomeAmount > $costAmount) {
                $status = 'লাভ';
            } elseif ($costAmount > $incomeAmount) {
                $status = 'লস';
            } else {
                $status = 'সমতা';
            }

            $months[] = [
                'sl' => $number,
                'name' => $name,
                'income' => $incomeAmount,
                'cost' => $costAmount,
                'difference' => $difference,
                'status' => $status,
            ];

            $annualIncome += $incomeAmount;
            $annualCost += $costAmount;
        }

        return [
            'months' => $months,
            'annualIncome' => $annualIncome,
            'annualCost' => $annualCost,
            'annualDifference' => abs($annualIncome - $annualCost),
            'annualStatus' => $annualIncome > $annualCost
                ? 'লাভ' : ($annualCost > $annualIncome ? 'লস' : 'সমতা'),
            'year' => $year,
            'years' => range(max(2020, Carbon::now('Asia/Dhaka')->year - 5), Carbon::now('Asia/Dhaka')->year + 1),
        ];
    }

    public function index(Request $request)
    {
        $currentYear = Carbon::now('Asia/Dhaka')->year;
        $year = (int) $request->input('year', $currentYear);

        if ($year < 2000 || $year > $currentYear + 1) {
            $year = $currentYear;
        }

        return view('admin.balance.summary', $this->reportData($year) + [
            'section' => 'Summary',
            'sectionBn' => 'আয়-ব্যয়ের সারসংক্ষেপ',
            'currentYear' => $currentYear,
            'selectedYear' => $year,
        ]);
    }

    public function income(Request $request)
    {
        $currentYear = Carbon::now('Asia/Dhaka')->year;
        $year = (int) $request->input('year', $currentYear);
        if ($year < 2000 || $year > $currentYear + 1) $year = $currentYear;

        return view('admin.balance.summary-report', $this->reportData($year) + [
            'reportType' => 'income',
            'reportTitle' => 'বার্ষিক আয় হিসাব',
            'reportSubtitle' => 'ANNUAL INCOME SUMMARY',
        ]);
    }

    public function cost(Request $request)
    {
        $currentYear = Carbon::now('Asia/Dhaka')->year;
        $year = (int) $request->input('year', $currentYear);
        if ($year < 2000 || $year > $currentYear + 1) $year = $currentYear;

        return view('admin.balance.summary-report', $this->reportData($year) + [
            'reportType' => 'cost',
            'reportTitle' => 'বার্ষিক ব্যয় হিসাব',
            'reportSubtitle' => 'ANNUAL EXPENSE SUMMARY',
        ]);
    }

    public function combine(Request $request)
    {
        $currentYear = Carbon::now('Asia/Dhaka')->year;
        $year = (int) $request->input('year', $currentYear);
        if ($year < 2000 || $year > $currentYear + 1) $year = $currentYear;

        return view('admin.balance.summary-report', $this->reportData($year) + [
            'reportType' => 'combine',
            'reportTitle' => 'বার্ষিক আয়-ব্যয়ের সম্মিলিত হিসাব',
            'reportSubtitle' => 'ANNUAL COMBINED FINANCIAL SUMMARY',
        ]);
    }
}
