<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\College;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CollegeController extends Controller
{
    /**
     * عرض قائمة الكليات
     */
    public function index(Request $request)
    {
        $query = College::query();

        // بحث
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name_ar', 'like', "%{$search}%")
                    ->orWhere('name_en', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        // فلترة حسب الحالة
        if ($request->has('is_active') && $request->is_active !== '') {
            $query->where('is_active', $request->is_active);
        }

        $colleges = $query->latest()->paginate(15);

        return view('admin.colleges.index', compact('colleges'));
    }

    /**
     * عرض نموذج إنشاء كلية جديدة
     */
    public function create()
    {
        return view('admin.colleges.create');
    }

    /**
     * تخزين كلية جديدة
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name_ar' => 'required|string|max:255|unique:colleges',
            'name_en' => 'nullable|string|max:255',
            'code' => 'nullable|string|max:50|unique:colleges',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $college = College::create([
            'name_ar' => $request->name_ar,
            'name_en' => $request->name_en,
            'code' => $request->code,
            'description' => $request->description,
            'is_active' => $request->is_active ?? true,
        ]);

        $this->logActivity('create', 'College', $college->id);

        return redirect()->route('admin.colleges.index')
            ->with('success', 'تم إنشاء الكلية بنجاح');
    }

    /**
     * عرض تفاصيل الكلية
     */
    public function show($id)
    {
        $college = College::with('departments.specializations')->findOrFail($id);
        return view('admin.colleges.show', compact('college'));
    }

    /**
     * عرض نموذج تعديل الكلية
     */
    public function edit($id)
    {
        $college = College::findOrFail($id);
        return view('admin.colleges.edit', compact('college'));
    }

    /**
     * تحديث الكلية
     */
    public function update(Request $request, $id)
    {
        $college = College::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name_ar' => 'required|string|max:255|unique:colleges,name_ar,' . $id,
            'name_en' => 'nullable|string|max:255',
            'code' => 'nullable|string|max:50|unique:colleges,code,' . $id,
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $oldValues = $college->toArray();

        $college->update([
            'name_ar' => $request->name_ar,
            'name_en' => $request->name_en,
            'code' => $request->code,
            'description' => $request->description,
            'is_active' => $request->is_active ?? true,
        ]);

        $this->logActivity('update', 'College', $college->id, $oldValues, $college->toArray());

        return redirect()->route('admin.colleges.index')
            ->with('success', 'تم تحديث الكلية بنجاح');
    }

    /**
     * حذف الكلية
     */
    public function destroy($id)
    {
        $college = College::findOrFail($id);

        // التحقق من وجود أقسام تابعة
        if ($college->departments()->count() > 0) {
            return back()->with('error', 'لا يمكن حذف الكلية لأنها تحتوي على أقسام تابعة');
        }

        $this->logActivity('delete', 'College', $college->id);

        $college->delete();

        return redirect()->route('admin.colleges.index')
            ->with('success', 'تم حذف الكلية بنجاح');
    }

    /**
     * تغيير حالة الكلية (تفعيل/تعطيل)
     */
    public function toggleStatus($id)
    {
        $college = College::findOrFail($id);
        $college->update(['is_active' => !$college->is_active]);

        $status = $college->is_active ? 'مفعلة' : 'معطلة';

        return back()->with('success', "تم {$status} الكلية بنجاح");
    }
}
