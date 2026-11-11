<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class InvestController extends Controller
{
    private function investors()
    {
        return [
            'helal' => 'Helal',
            'pranto' => 'Pranto',
            'juel_mia' => 'Juel Mia',
        ];
    }

    private function ensureTable()
    {
        abort_unless(Schema::hasTable('admin_balance_investments'), 503,
            'Admin Invest-এর database table তৈরি করা হয়নি। INSTALL_ADMIN_INVEST.sql ফাইলটি phpMyAdmin থেকে একবার চালান।');
    }

    private function dashboardData()
    {
        $this->ensureTable();
        $investors = $this->investors();
        $totals = [];
        foreach ($investors as $key => $name) {
            $totals[$key] = (float) DB::table('admin_balance_investments')
                ->where('investor_key', $key)->sum('amount');
        }

        return [
            'investors' => $investors,
            'totals' => $totals,
            'grandTotal' => (float) array_sum($totals),
            'entries' => DB::table('admin_balance_investments')
                ->orderBy('entry_date', 'desc')->orderBy('id', 'desc')->get(),
            'editEntry' => null,
        ];
    }

    public function index()
    {
        return view('admin.balance.invest', $this->dashboardData());
    }

    public function store(Request $request)
    {
        $this->ensureTable();
        $data = $request->validate([
            'investor_key' => 'required|in:helal,pranto,juel_mia',
            'entry_date' => 'required|date',
            'amount' => 'required|numeric|min:0.01|max:999999999999.99',
            'details' => 'required|string|max:5000',
        ], [
            'investor_key.required' => 'বিনিয়োগকারীর নাম নির্বাচন করুন।',
            'entry_date.required' => 'বিনিয়োগের তারিখ নির্বাচন করুন।',
            'amount.required' => 'টাকার পরিমাণ লিখুন।',
            'details.required' => 'বিনিয়োগের বিবরণ লিখুন।',
        ]);

        $names = $this->investors();
        DB::table('admin_balance_investments')->insert([
            'investor_key' => $data['investor_key'],
            'investor_name' => $names[$data['investor_key']],
            'entry_date' => Carbon::parse($data['entry_date'], 'Asia/Dhaka')->format('Y-m-d'),
            'amount' => $data['amount'],
            'details' => trim($data['details']),
            'created_by' => Auth::id(),
            'created_at' => Carbon::now('Asia/Dhaka'),
            'updated_at' => Carbon::now('Asia/Dhaka'),
        ]);

        return redirect()->route('admin.balance.invest')->with('success', 'Investment entry সফলভাবে সংরক্ষণ করা হয়েছে।');
    }

    public function edit($id)
    {
        $this->ensureTable();
        $data = $this->dashboardData();
        $data['editEntry'] = DB::table('admin_balance_investments')->where('id', $id)->first();
        abort_if(!$data['editEntry'], 404);
        return view('admin.balance.invest', $data);
    }

    public function update(Request $request, $id)
    {
        $this->ensureTable();
        $entry = DB::table('admin_balance_investments')->where('id', $id)->first();
        abort_if(!$entry, 404);
        $data = $request->validate([
            'investor_key' => 'required|in:helal,pranto,juel_mia',
            'entry_date' => 'required|date',
            'amount' => 'required|numeric|min:0.01|max:999999999999.99',
            'details' => 'required|string|max:5000',
        ]);
        $names = $this->investors();
        DB::table('admin_balance_investments')->where('id', $id)->update([
            'investor_key' => $data['investor_key'],
            'investor_name' => $names[$data['investor_key']],
            'entry_date' => Carbon::parse($data['entry_date'], 'Asia/Dhaka')->format('Y-m-d'),
            'amount' => $data['amount'],
            'details' => trim($data['details']),
            'updated_at' => Carbon::now('Asia/Dhaka'),
        ]);
        return redirect()->route('admin.balance.invest')->with('success', 'Investment entry আপডেট হয়েছে।');
    }

    public function destroy($id)
    {
        $this->ensureTable();
        DB::table('admin_balance_investments')->where('id', $id)->delete();
        return redirect()->route('admin.balance.invest')->with('success', 'Investment entry মুছে ফেলা হয়েছে।');
    }

    public function report($investor)
    {
        $this->ensureTable();
        $investors = $this->investors();
        abort_unless(isset($investors[$investor]), 404);
        $entries = DB::table('admin_balance_investments')->where('investor_key', $investor)
            ->orderBy('entry_date', 'asc')->orderBy('id', 'asc')->get();
        $total = (float) DB::table('admin_balance_investments')->where('investor_key', $investor)->sum('amount');
        return view('admin.balance.invest-report', [
            'investorKey' => $investor,
            'investorName' => $investors[$investor],
            'entries' => $entries,
            'total' => $total,
        ]);
    }
}
