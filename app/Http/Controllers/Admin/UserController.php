<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Specialization;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->has('role') && $request->role) {
            if (in_array($request->role, ['student', 'supervisor', 'admin'])) {
                $query->where('role', $request->role);
            } else {
                $query->whereHas('newRoles', function ($q) use ($request) {
                    $q->where('slug', $request->role);
                });
            }
        }

        if ($request->has('is_active') && $request->is_active !== '') {
            $query->where('is_active', $request->is_active);
        }

        if ($request->has('search') && $request->search) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('student_id', 'like', "%{$search}%")
                    ->orWhere('employee_id', 'like', "%{$search}%");
            });
        }

        $users = $query
            ->with(['specialization', 'newRoles'])
            ->latest()
            ->paginate(15);

        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        $specializations = Specialization::active()->get();

        return view('admin.users.create', compact('specializations'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:6|confirmed',

            // يشمل الأدوار القديمة والجديدة
            'role' => 'required|in:student,supervisor,admin,faculty,staff',

            'student_id' => 'required_if:role,student|nullable|unique:users,student_id',
            'employee_id' => 'required_if:role,supervisor,faculty,staff|nullable|unique:users,employee_id',

            'phone' => 'nullable|string|max:20',
            'specialization_id' => 'nullable|exists:specializations,id',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        DB::transaction(function () use ($request) {

            /*
             * users.role نظام قديم.
             *
             * بالنسبة للأدوار الجديدة faculty/staff لا نعتمد عليه
             * في الصلاحيات. نخزن قيمة متوافقة فقط حتى لا نكسر
             * أجزاء المشروع القديمة.
             */
            $legacyRole = match ($request->role) {
                'faculty' => 'faculty',
                'staff' => 'admin',
                default => $request->role,
            };

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $legacyRole,
                'student_id' => $request->role === 'student'
                    ? $request->student_id
                    : null,
                'employee_id' => in_array($request->role, ['supervisor', 'faculty', 'staff'])
                    ? $request->employee_id
                    : null,
                'phone' => $request->phone,
                'specialization_id' => in_array($request->role, ['student', 'supervisor', 'faculty'])
                    ? $request->specialization_id
                    : null,
                'is_active' => $request->boolean('is_active'),
            ]);

            $role = Role::where('slug', $request->role)->firstOrFail();

            UserRole::create([
                'user_id' => $user->id,
                'role_id' => $role->id,
                'department_id' => null,
                'assigned_at' => now(),
                'assigned_by' => auth()->id(),
            ]);

            /*
             * supervisor في النظام القديم = عضو هيئة تدريس أيضًا.
             */
            if ($request->role === 'supervisor') {
                $facultyRole = Role::where('slug', 'faculty')->first();

                if ($facultyRole) {
                    UserRole::firstOrCreate(
                        [
                            'user_id' => $user->id,
                            'role_id' => $facultyRole->id,
                            'department_id' => null,
                        ],
                        [
                            'assigned_at' => now(),
                            'assigned_by' => auth()->id(),
                        ]
                    );
                }
            }

            $this->logActivity('create', 'User', $user->id);
        });

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'تم إنشاء المستخدم وربط صلاحياته بنجاح');
    }

    public function show($id)
    {
        $user = User::with([
            'specialization',
            'projects',
            'supervisedProjects',
            'newRoles',
        ])->findOrFail($id);

        return view('admin.users.show', compact('user'));
    }

    public function edit($id)
    {
        $user = User::with('newRoles')->findOrFail($id);
        $specializations = Specialization::active()->get();

        return view('admin.users.edit', compact('user', 'specializations'));
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,

            'role' => 'required|in:student,supervisor,admin,faculty,staff',

            'student_id' => 'required_if:role,student|nullable|unique:users,student_id,' . $id,
            'employee_id' => 'required_if:role,supervisor,faculty,staff|nullable|unique:users,employee_id,' . $id,

            'phone' => 'nullable|string|max:20',
            'specialization_id' => 'nullable|exists:specializations,id',
            'is_active' => 'boolean',
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        DB::transaction(function () use ($request, $user) {

            $oldValues = $user->toArray();

            $legacyRole = match ($request->role) {
                'faculty' => 'faculty',
                'staff' => 'staff',
                default => $request->role,
            };

            $user->update([
                'name' => $request->name,
                'email' => $request->email,
                'role' => $legacyRole,
                'student_id' => $request->role === 'student'
                    ? $request->student_id
                    : null,
                'employee_id' => in_array($request->role, ['supervisor', 'faculty', 'staff'])
                    ? $request->employee_id
                    : null,
                'phone' => $request->phone,
                'specialization_id' => in_array($request->role, ['student', 'supervisor', 'faculty'])
                    ? $request->specialization_id
                    : null,
                'is_active' => $request->boolean('is_active'),
            ]);

            if ($request->filled('password')) {
                $user->update([
                    'password' => Hash::make($request->password),
                ]);
            }

            /*
             * هذه الشاشة تدير الدور الرئيسي للمستخدم.
             * نحافظ على إمكانية تعدد الأدوار الخاصة بالمشرف
             * بإضافة faculty له تلقائيًا.
             */
            $selectedRole = Role::where('slug', $request->role)->firstOrFail();

            $user->userRoles()->delete();

            UserRole::create([
                'user_id' => $user->id,
                'role_id' => $selectedRole->id,
                'department_id' => null,
                'assigned_at' => now(),
                'assigned_by' => auth()->id(),
            ]);

            if ($request->role === 'supervisor') {
                $facultyRole = Role::where('slug', 'faculty')->first();

                if ($facultyRole) {
                    UserRole::create([
                        'user_id' => $user->id,
                        'role_id' => $facultyRole->id,
                        'department_id' => null,
                        'assigned_at' => now(),
                        'assigned_by' => auth()->id(),
                    ]);
                }
            }

            $this->logActivity(
                'update',
                'User',
                $user->id,
                $oldValues,
                $user->fresh()->toArray()
            );
        });

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'تم تحديث المستخدم وصلاحياته بنجاح');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);

        if ($user->id === auth()->id()) {
            return back()->with('error', 'لا يمكنك حذف حسابك الحالي');
        }

        $this->logActivity('delete', 'User', $user->id);

        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'تم حذف المستخدم بنجاح');
    }

    public function toggleStatus($id)
    {
        $user = User::findOrFail($id);

        if ($user->id === auth()->id()) {
            return back()->with('error', 'لا يمكنك تغيير حالة حسابك الحالي');
        }

        $user->update([
            'is_active' => ! $user->is_active,
        ]);

        $status = $user->is_active ? 'مفعل' : 'معطل';

        return back()->with('success', "تم {$status} المستخدم بنجاح");
    }
}