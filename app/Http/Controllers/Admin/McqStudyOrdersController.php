<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class McqStudyOrdersController extends Controller
{
    private function ensureAdmin(): void
    {
        $admin = Auth::guard('web')->user();
        abort_unless($admin, 403, 'Admin access only.');
        try {
            $role = $admin->authRole()->name ?? null;
        } catch (\Throwable $e) {
            abort(403, 'Admin access only.');
        }
        abort_unless(in_array($role, ['Admin', 'SuperAdmin'], true), 403, 'Admin access only.');
    }

    private function baseQuery()
    {
        return DB::table('lebd_mcq_payment_orders as o')
            ->leftJoin('students as s', 's.id', '=', 'o.student_id')
            ->select('o.*', 's.name as student_name', 's.email as student_email',
                's.contact as student_phone');
    }

    public function index(Request $request)
    {
        $this->ensureAdmin();
        $validated = $request->validate([
            'status' => 'nullable|in:all,paid,pending',
            'source' => 'nullable|in:all,ssl,referral',
            'range' => 'nullable|in:day,all',
            'date' => 'nullable|date_format:Y-m-d',
            'search' => 'nullable|string|max:150',
        ]);
        $state = $validated['status'] ?? 'all';
        $source = $validated['source'] ?? 'all';
        $range = $validated['range'] ?? 'day';
        $search = trim($validated['search'] ?? '');
        $notice = null;
        $orders = null;
        $reportCount = 0;
        $paidTotal = 0;
        $pendingTotal = 0;
        $date = $validated['date'] ?? now('Asia/Dhaka')->toDateString();

        if (!Schema::hasTable('lebd_mcq_payment_orders') || !Schema::hasTable('students')) {
            $notice = 'MCQ order reporting tables are unavailable.';
            return view('admin.mcq-orders.index', compact('orders', 'state', 'source',
                'range', 'search', 'date', 'notice', 'reportCount', 'paidTotal', 'pendingTotal'));
        }

        $latest = DB::table('lebd_mcq_payment_orders')->max('created_at');
        $date = $validated['date'] ?? ($latest ? substr((string)$latest, 0, 10) : now('Asia/Dhaka')->toDateString());

        $query = $this->baseQuery();
        if ($state === 'paid') $query->where('o.status', 'Complete');
        if ($state === 'pending') $query->where('o.status', 'Pending');
        if ($source === 'ssl') $query->where('o.transaction_id', 'like', 'MCQS%');
        if ($source === 'referral') $query->where('o.transaction_id', 'like', 'MCQR%');
        if ($range === 'day') $query->whereDate('o.created_at', $date);
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $like = '%'.$search.'%';
                $q->where('o.transaction_id', 'like', $like)
                    ->orWhere('o.package_name', 'like', $like)
                    ->orWhere('s.name', 'like', $like)
                    ->orWhere('s.email', 'like', $like)
                    ->orWhere('s.contact', 'like', $like);
            });
        }

        $summary = (clone $query)->reorder()->select([])->selectRaw(
            "COUNT(*) AS order_count, ".
            "COALESCE(SUM(CASE WHEN o.status = 'Complete' THEN o.amount ELSE 0 END),0) AS paid_total, ".
            "COALESCE(SUM(CASE WHEN o.status = 'Pending' THEN o.amount ELSE 0 END),0) AS pending_total"
        )->first();
        $reportCount = (int)($summary->order_count ?? 0);
        $paidTotal = (float)($summary->paid_total ?? 0);
        $pendingTotal = (float)($summary->pending_total ?? 0);
        $orders = $query->orderByDesc('o.created_at')->orderByDesc('o.id')
            ->paginate(10)->withQueryString();

        // The expiry belongs to the current subscription, NOT necessarily this order.
        $subscriptionEnds = [];
        $pairs = $orders->getCollection()->filter(fn($o) => $o->status === 'Complete');
        foreach ($pairs as $item) {
            $key = $item->student_id.':'.$item->package_id;
            if (!array_key_exists($key, $subscriptionEnds)) {
                $subscriptionEnds[$key] = Schema::hasTable('lebd_mcq_subscriptions')
                    ? DB::table('lebd_mcq_subscriptions')
                        ->where('student_id', $item->student_id)
                        ->where('package_id', $item->package_id)->max('ends_at')
                    : null;
            }
            $item->expires_at = $subscriptionEnds[$key];
        }

        return view('admin.mcq-orders.index', compact('orders', 'state', 'source',
            'range', 'search', 'date', 'notice', 'reportCount', 'paidTotal', 'pendingTotal'));
    }

    public function show(int $order)
    {
        $this->ensureAdmin();
        abort_unless(Schema::hasTable('lebd_mcq_payment_orders') && Schema::hasTable('students'), 404);
        $record = $this->baseQuery()->where('o.id', $order)->first();
        abort_unless($record, 404);
        $expiresAt = Schema::hasTable('lebd_mcq_subscriptions')
            ? DB::table('lebd_mcq_subscriptions')
                ->where('student_id', $record->student_id)
                ->where('package_id', $record->package_id)->max('ends_at')
            : null;
        return view('admin.mcq-orders.show', compact('record', 'expiresAt'));
    }
}
