<?php

namespace App\Http\Controllers\Admin\ExtraStudent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class LebdExtraStudentController extends Controller
{
    private function authorizeAdmin(): void
    {
        $user = Auth::guard('web')->user();
        $role = $user && method_exists($user, 'authRole') ? optional($user->authRole())->name : null;
        abort_unless($user && in_array($role, ['Admin', 'SuperAdmin'], true), 403);
    }

    private function ensureTable(): void
    {
        if (!Schema::hasTable('lebd_extra_students')) {
            abort(503, 'Extra Student table not installed. Import the supplied create-only SQL first.');
        }
    }

    private function departments(): array
    {
        return [
            'civil' => 'Civil', 'electrical' => 'Electrical',
            'mechanical' => 'Mechanical', 'computer' => 'Computer',
            'electronic' => 'Electronic', 'power' => 'Power',
            'rac' => 'RAC', 'architecture' => 'Architecture',
            'textile' => 'Textile', 'agriculture' => 'Agriculture',
            'general' => 'General',
        ];
    }

    private function ensureDepartmentColumn(): void
    {
        if (!Schema::hasColumn('lebd_extra_students', 'department')) {
            abort(503, 'Extra Student department column is not installed. Import ADD_DEPARTMENT_TO_LEBD_EXTRA_STUDENTS.sql first.');
        }
    }

    public function index(Request $request)
    {
        $this->authorizeAdmin();
        $this->ensureTable();
        $this->ensureDepartmentColumn();
        $departments = $this->departments();
        $selectedDepartment = strtolower(trim((string) $request->query('department', '')));
        abort_if($selectedDepartment !== '' && !array_key_exists($selectedDepartment, $departments), 404);

        $registryReady = Schema::hasTable('lebd_student_public_ids');
        $query = DB::table('lebd_extra_students as es');
        if ($registryReady) {
            $query->leftJoin('lebd_student_public_ids as sp', function ($join) {
                $join->on('sp.source_id', '=', 'es.id')->where('sp.source', '=', 'extra');
            })->select('es.*', 'sp.public_id as shared_student_id');
        } else {
            $query->select('es.*');
        }
        $query->orderByDesc('es.id');
        if ($selectedDepartment !== '') {
            $query->where('es.department', $selectedDepartment);
        }
        $search = trim((string) $request->query('search', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('es.name', 'like', '%' . $search . '%')
                  ->orWhere('es.email', 'like', '%' . $search . '%')
                  ->orWhere('es.contact', 'like', '%' . $search . '%');
            });
        }
        // Server-side maximum 25 per page; retain existing department and search filters.
        $students = $query->paginate(25)->withQueryString();
        $departmentCounts = DB::table('lebd_extra_students')->select('department', DB::raw('COUNT(*) as total'))->groupBy('department')->pluck('total','department')->all();
        return view('admin.extra-students.index', compact('students', 'departments', 'selectedDepartment', 'departmentCounts'));
    }

    public function create()
    {
        $this->authorizeAdmin();
        $departments = $this->departments();
        return view('admin.extra-students.create', compact('departments'));
    }

    public function store(Request $request)
    {
        $this->authorizeAdmin();
        $this->ensureTable();
        $this->ensureDepartmentColumn();
        $data = $request->validate([
            'department' => ['required', Rule::in(array_keys($this->departments()))],
            'name' => ['required', 'string', 'max:150'],
            'email' => ['nullable', 'email', 'max:190', Rule::unique('lebd_extra_students', 'email')],
            'contact' => ['required', 'string', 'max:30', Rule::unique('lebd_extra_students', 'contact')],
            'password' => ['required', 'string', 'min:8', 'max:200'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,jpg,png', 'max:500'],
        ]);
        $photo = null;
        if ($request->hasFile('photo')) {
            $photo = $request->file('photo')->store('extra-students', 'public');
        }
        try {
            DB::table('lebd_extra_students')->insert([
                'name' => $data['name'],
                'department' => $data['department'],
                'email' => $data['email'] ?? null,
                'contact' => $data['contact'],
                'password' => Hash::make($data['password']),
                'photo' => $photo,
                'status' => 'Active',
                'created_by' => Auth::guard('web')->id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable $e) {
            if ($photo) Storage::disk('public')->delete($photo);
            throw $e;
        }
        return redirect()->route('admin.extra-students.index', ['department' => $data['department']])->with('success', 'Extra Student added successfully.');
    }

    // Added: status-only admin edit. Does not change credentials, public IDs or exam selections.
    public function edit(int $student)
    {
        $this->authorizeAdmin();
        $this->ensureTable();
        $this->ensureDepartmentColumn();
        $extraStudent = DB::table('lebd_extra_students')->where('id', $student)->first();
        abort_unless($extraStudent, 404);
        $departments = $this->departments();
        return view('admin.extra-students.edit', compact('extraStudent', 'departments'));
    }

    public function update(Request $request, int $student)
    {
        $this->authorizeAdmin();
        $this->ensureTable();
        $this->ensureDepartmentColumn();
        $data = $request->validate(['status' => ['required', Rule::in(['Active', 'Inactive'])]]);
        $existing = DB::table('lebd_extra_students')->where('id', $student)->first(['id','department']);
        abort_unless($existing, 404);
        DB::table('lebd_extra_students')->where('id', $student)->update([
            'status' => $data['status'],
            'updated_at' => now(),
        ]);
        return redirect()->route('admin.extra-students.index', ['department' => $existing->department])
            ->with('success', 'Extra Student status updated successfully.');
    }
}
