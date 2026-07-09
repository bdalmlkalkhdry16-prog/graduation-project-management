<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Specialization;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SpecializationController extends Controller
{
    /**
     * عرض قائمة التخصصات
     */
    public function index(Request $request)
    {
        $query = Specialization::with('department.college');

        // فلترة حسب القسم
        if ($request->has('department_id') && $request->department_id) {
            $query->where('department_id', $request->department_id);
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

        $specializations = $query->latest()->paginate(15);
        $departments = Department::with('college')->active()->get();

        return view('admin.specializations.index', compact('specializations', 'departments'));
    }

    /**
     * عرض نموذج إنشاء تخصص جديد
     */
    public function create()
    {
        $departments = Department::with('college')->active()->get();
        return view('admin.specializations.create', compact('departments'));
    }

    /**
     * تخزين تخصص جديد
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name_ar' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'department_id' => 'required|exists:departments,id',
            'code' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'duration_years' => 'nullable|integer|min:1|max:6',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $specialization = Specialization::create([
            'name_ar' => $request->name_ar,
            'name_en' => $request->name_en,
            'department_id' => $request->department_id,
            'code' => $request->code,
            'description' => $request->description,
            'duration_years' => $request->duration_years ?? 2,
            'is_active' => $request->is_active ?? true,
        ]);

        $this->logActivity('create', 'Specialization', $specialization->id);

        return redirect()->route('admin.specializations.index')
            ->with('success', 'تم إنشاء التخصص بنجاح');
    }

    /**
     * عرض تفاصيل التخصص
     */
    public function show($id)
    {
        $specialization = Specialization::with(['department.college', 'projects', 'students'])
            ->findOrFail($id);
        return view('admin.specializations.show', compact('specialization'));
    }

    /**
     * عرض نموذج تعديل التخصص
     */
    public function edit($id)
    {
        $specialization = Specialization::findOrFail($id);
        $departments = Department::with('college')->active()->get();
        return view('admin.specializations.edit', compact('specialization', 'departments'));
    }

    /**
     * تحديث التخصص
     */
    public function update(Request $request, $id)
    {
        $specialization = Specialization::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name_ar' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'department_id' => 'required|exists:departments,id',
            'code' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'duration_years' => 'nullable|integer|min:1|max:6',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $oldValues = $specialization->toArray();

        $specialization->update([
            'name_ar' => $request->name_ar,
            'name_en' => $request->name_en,
            'department_id' => $request->department_id,
            'code' => $request->code,
            'description' => $request->description,
            'duration_years' => $request->duration_years ?? 2,
            'is_active' => $request->is_active ?? true,
        ]);

        $this->logActivity('update', 'Specialization', $specialization->id, $oldValues, $specialization->toArray());

        return redirect()->route('admin.specializations.index')
            ->with('success', 'تم تحديث التخصص بنجاح');
    }

    /**
     * حذف التخصص
     */
    public function destroy($id)
    {
        $specialization = Specialization::findOrFail($id);

        // التحقق من وجود مشاريع أو طلاب تابعة
        if ($specialization->projects()->count() > 0) {
            return back()->with('error', 'لا يمكن حذف التخصص لأنه يحتوي على مشاريع تابعة');
        }

        if ($specialization->students()->count() > 0) {
            return back()->with('error', 'لا يمكن حذف التخصص لأنه يحتوي على طلاب مسجلين');
        }

        $this->logActivity('delete', 'Specialization', $specialization->id);

        $specialization->delete();

        return redirect()->route('admin.specializations.index')
            ->with('success', 'تم حذف التخصص بنجاح');
    }

    /**
     * تغيير حالة التخصص (تفعيل/تعطيل)
     */
    public function toggleStatus($id)
    {
        $specialization = Specialization::findOrFail($id);
        $specialization->update(['is_active' => !$specialization->is_active]);

        $status = $specialization->is_active ? 'مفعل' : 'معطل';

        return back()->with('success', "تم {$status} التخصص بنجاح");
    }
}
