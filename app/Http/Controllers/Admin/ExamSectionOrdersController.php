<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** Read-only reporting; never changes payments, wallet or package access. */
class ExamSectionOrdersController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'status' => 'nullable|in:all,paid,pending,failed,cancelled',
            'source' => 'nullable|in:all,ssl,referral',
            'date' => 'nullable|date_format:Y-m-d',
            'range' => 'nullable|in:all,day',
            'search' => 'nullable|string|max:120',
        ]);
        $status = $filters['status'] ?? 'all';
        $source = $filters['source'] ?? 'all';
        $range = $filters['range'] ?? 'all';
        $search = trim($filters['search'] ?? '');
        $notice = null;
        $orders = null;
        $totals = (object)['total'=>0,'paid'=>0,'pending'=>0];
        $required = ['lebd_exam_section_package_orders','lebd_exam_section_packages','students'];
        foreach ($required as $table) {
            if (!Schema::hasTable($table)) {
                $notice = 'Exam Section order reporting table unavailable: '.$table;
                return view('admin.lebd-orders.exam', compact('orders','totals','notice','status','source','range','search') + ['selectedDate'=>now('Asia/Dhaka')->toDateString()]);
            }
        }
        $latest = DB::table('lebd_exam_section_package_orders')->max('created_at');
        $selectedDate = $filters['date'] ?? ($latest ? substr((string)$latest,0,10) : now('Asia/Dhaka')->toDateString());
        $query = DB::table('lebd_exam_section_package_orders as o')
            ->leftJoin('students as s','s.id','=','o.student_id')
            ->leftJoin('lebd_exam_section_packages as p','p.id','=','o.package_id')
            ->select('o.*','s.name as student_name','s.email as student_email',
                's.contact as student_contact','p.name as package_name');
        if ($source === 'ssl') $query->where('o.method','sslcommerz');
        elseif ($source === 'referral') $query->where('o.method','wallet');
        if ($status !== 'all') $query->where('o.status',$status);
        if ($range === 'day') $query->whereBetween('o.created_at',[$selectedDate.' 00:00:00',$selectedDate.' 23:59:59']);
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('o.transaction_id','like','%'.$search.'%')
                  ->orWhere('s.name','like','%'.$search.'%')
                  ->orWhere('s.email','like','%'.$search.'%')
                  ->orWhere('s.contact','like','%'.$search.'%')
                  ->orWhere('p.name','like','%'.$search.'%');
            });
        }
        // Reset existing select before aggregating: avoids MariaDB 1140 and GROUP BY issues.
        $totals = (clone $query)->reorder()->select([])->selectRaw(
            "COUNT(*) as total, COALESCE(SUM(CASE WHEN o.status = 'paid' THEN o.amount ELSE 0 END),0) as paid, COALESCE(SUM(CASE WHEN o.status = 'pending' THEN o.amount ELSE 0 END),0) as pending"
        )->first();
        if (Schema::hasTable('lebd_exam_section_student_package_access')) {
            $query->addSelect(['access_expiry' => DB::table('lebd_exam_section_student_package_access as a')
                ->select('a.ends_at')->whereColumn('a.payment_reference','o.transaction_id')
                ->whereColumn('a.student_id','o.student_id')->orderByDesc('a.ends_at')->limit(1)]);
        }
        $orders = $query->orderByDesc('o.created_at')->orderByDesc('o.id')->paginate(10)->withQueryString();
        return view('admin.lebd-orders.exam',compact('orders','totals','notice','status','source','range','search','selectedDate'));
    }

    public function show(int $order)
    {
        abort_unless(Schema::hasTable('lebd_exam_section_package_orders'),404);
        $row = DB::table('lebd_exam_section_package_orders as o')
            ->leftJoin('students as s','s.id','=','o.student_id')
            ->leftJoin('lebd_exam_section_packages as p','p.id','=','o.package_id')
            ->select('o.*','s.name as student_name','s.email as student_email',
                     's.contact as student_contact','p.name as package_name')
            ->where('o.id',$order)->first();
        abort_unless($row,404);
        $access = Schema::hasTable('lebd_exam_section_student_package_access')
            ? DB::table('lebd_exam_section_student_package_access')
                ->where('student_id',$row->student_id)->where('payment_reference',$row->transaction_id)
                ->orderByDesc('ends_at')->first() : null;
        return view('admin.lebd-orders.exam-show',compact('row','access'));
    }
}
