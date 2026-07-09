<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Specialization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * عرض قائمة المستخدمين
     */
    public function index(Request $request)
    {
        $query = User::query();

        // فلترة حسب الدور
        if ($request->has('role') && $request->role) {
            $query->where('role', $request->role);
        }

        // فلترة حسب حالة النشاط
        if ($request->has('is_active') && $request->is_active !== '') {
            $query->where('is_active', $request->is_active);
        }

        // بحث
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('student_id', 'like', "%{$search}%")
                    ->orWhere('employee_id', 'like', "%{$search}%");
            });
        }

        $users = $query->with('specialization')->latest()->paginate(15);

        return view('admin.users.index', compact('users'));
    }

    /**
     * عرض نموذج إنشاء مستخدم جديد
     */
    public function create()
    {
        $specializations = Specialization::active()->get();
        return view('admin.users.create', compact('specializations'));
    }

    /**
     * تخزين مستخدم جديد
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|in:student,supervisor,admin',
            'student_id' => 'required_if:role,student|nullable|unique:users',
            'employee_id' => 'required_if:role,supervisor|nullable|unique:users',
            'phone' => 'nullable|string|max:20',
            'specialization_id' => 'nullable|exists:specializations,id',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'student_id' => $request->student_id,
            'employee_id' => $request->employee_id,
            'phone' => $request->phone,
            'specialization_id' => $request->specialization_id,
            'is_active' => $request->is_active ?? true,
        ]);

        $this->logActivity('create', 'User', $user->id);

        return redirect()->route('admin.users.index')
            ->with('success', 'تم إنشاء المستخدم بنجاح');
    }

    /**
     * عرض تفاصيل المستخدم
     */
    public function show($id)
    {
        $user = User::with(['specialization', 'projects', 'supervisedProjects'])->findOrFail($id);
        return view('admin.users.show', compact('user'));
    }

    /**
     * عرض نموذج تعديل المستخدم
     */
    public function edit($id)
    {
        $user = User::findOrFail($id);
        $specializations = Specialization::active()->get();
        return view('admin.users.edit', compact('user', 'specializations'));
    }

    /**
     * تحديث المستخدم
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            'role' => 'required|in:student,supervisor,admin',
            'student_id' => 'required_if:role,student|nullable|unique:users,student_id,' . $id,
            'employee_id' => 'required_if:role,supervisor|nullable|unique:users,employee_id,' . $id,
            'phone' => 'nullable|string|max:20',
            'specialization_id' => 'nullable|exists:specializations,id',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $oldValues = $user->toArray();

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'student_id' => $request->student_id,
            'employee_id' => $request->employee_id,
            'phone' => $request->phone,
            'specialization_id' => $request->specialization_id,
            'is_active' => $request->is_active ?? true,
        ]);

        // تحديث كلمة المرور إذا تم إدخالها
        if ($request->filled('password')) {
            $request->validate(['password' => 'min:6|confirmed']);
            $user->update(['password' => Hash::make($request->password)]);
        }

        $this->logActivity('update', 'User', $user->id, $oldValues, $user->toArray());

        return redirect()->route('admin.users.index')
            ->with('success', 'تم تحديث المستخدم بنجاح');
    }

    /**
     * حذف المستخدم
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);

        // لا يمكن حذف المستخدم الحالي
        if ($user->id === auth()->id()) {
            return back()->with('error', 'لا يمكنك حذف حسابك الحالي');
        }

        $this->logActivity('delete', 'User', $user->id);

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'تم حذف المستخدم بنجاح');
    }

    /**
     * تغيير حالة المستخدم (تفعيل/تعطيل)
     */
    public function toggleStatus($id)
    {
        $user = User::findOrFail($id);

        if ($user->id === auth()->id()) {
            return back()->with('error', 'لا يمكنك تغيير حالة حسابك الحالي');
        }

        $user->update(['is_active' => !$user->is_active]);

        $status = $user->is_active ? 'مفعل' : 'معطل';

        return back()->with('success', "تم {$status} المستخدم بنجاح");
    }
}
