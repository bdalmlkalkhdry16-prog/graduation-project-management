<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\College;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DepartmentController extends Controller
{
    /**
     * عرض قائمة الأقسام
     */
    public function index(Request $request)
    {
        $query = Department::with('college');

        // فلترة حسب الكلية
        if ($request->has('college_id') && $request->college_id) {
            $query->where('college_id', $request->college_id);
        }

        // بحث
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name_ar', 'like', "%{$search}%")
                    ->orWhere('name_en', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $departments = $query->latest()->paginate(15);
        $colleges = College::active()->get();

        return view('admin.departments.index', compact('departments', 'colleges'));
    }

    /**
     * عرض نموذج إنشاء قسم جديد
     */
    public function create()
    {
        $colleges = College::active()->get();
        return view('admin.departments.create', compact('colleges'));
    }

    /**
     * تخزين قسم جديد
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name_ar' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'college_id' => 'required|exists:colleges,id',
            'code' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $department = Department::create([
            'name_ar' => $request->name_ar,
            'name_en' => $request->name_en,
            'college_id' => $request->college_id,
            'code' => $request->code,
            'description' => $request->description,
            'is_active' => $request->is_active ?? true,
        ]);

        $this->logActivity('create', 'Department', $department->id);

        return redirect()->route('admin.departments.index')
            ->with('success', 'تم إنشاء القسم بنجاح');
    }

    /**
     * عرض تفاصيل القسم
     */
    public function show($id)
    {
        $department = Department::with(['college', 'specializations'])->findOrFail($id);
        return view('admin.departments.show', compact('department'));
    }

    /**
     * عرض نموذج تعديل القسم
     */
    public function edit($id)
    {
        $department = Department::findOrFail($id);
        $colleges = College::active()->get();
        return view('admin.departments.edit', compact('department', 'colleges'));
    }

    /**
     * تحديث القسم
     */
    public function update(Request $request, $id)
    {
        $department = Department::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name_ar' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'college_id' => 'required|exists:colleges,id',
            'code' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $oldValues = $department->toArray();

        $department->update([
            'name_ar' => $request->name_ar,
            'name_en' => $request->name_en,
            'college_id' => $request->college_id,
            'code' => $request->code,
            'description' => $request->description,
            'is_active' => $request->is_active ?? true,
        ]);

        $this->logActivity('update', 'Department', $department->id, $oldValues, $department->toArray());

        return redirect()->route('admin.departments.index')
            ->with('success', 'تم تحديث القسم بنجاح');
    }

    /**
     * حذف القسم
     */
    public function destroy($id)
    {
        $department = Department::findOrFail($id);

        // التحقق من وجود تخصصات تابعة
        if ($department->specializations()->count() > 0) {
            return back()->with('error', 'لا يمكن حذف القسم لأنه يحتوي على تخصصات تابعة');
        }

        $this->logActivity('delete', 'Department', $department->id);

        $department->delete();

        return redirect()->route('admin.departments.index')
            ->with('success', 'تم حذف القسم بنجاح');
    }

    /**
     * تغيير حالة القسم (تفعيل/تعطيل)
     */
    public function toggleStatus($id)
    {
        $department = Department::findOrFail($id);
        $department->update(['is_active' => !$department->is_active]);

        $status = $department->is_active ? 'مفعل' : 'معطل';

        return back()->with('success', "تم {$status} القسم بنجاح");
    }
}
