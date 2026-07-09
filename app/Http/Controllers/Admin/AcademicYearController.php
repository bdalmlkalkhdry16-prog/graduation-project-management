<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use Illuminate\Http\Request;

class AcademicYearController extends Controller
{
    public function index()
    {
        $academicYears = AcademicYear::orderBy('year', 'desc')->paginate(15);
        $activeYear = AcademicYear::getActive();
        return view('admin.academic_years.index', compact('academicYears', 'activeYear'));
    }

    public function create()
    {
        return view('admin.academic_years.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:academic_years',
            'year' => 'required|integer|unique:academic_years',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
        ]);

        $data = $request->only(['name', 'year', 'start_date', 'end_date']);
        $data['is_active'] = false;

        AcademicYear::create($data);

        return redirect()->route('admin.academic-years.index')
            ->with('success', 'تمت إضافة السنة الأكاديمية بنجاح');
    }

    public function edit(AcademicYear $academicYear)
    {
        return view('admin.academic_years.edit', compact('academicYear'));
    }

    public function update(Request $request, AcademicYear $academicYear)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:academic_years,name,' . $academicYear->id,
            'year' => 'required|integer|unique:academic_years,year,' . $academicYear->id,
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
        ]);

        $academicYear->update($request->only(['name', 'year', 'start_date', 'end_date']));

        return redirect()->route('admin.academic-years.index')
            ->with('success', 'تم تحديث السنة الأكاديمية بنجاح');
    }

    public function setActive(AcademicYear $academicYear)
    {
        // إلغاء تفعيل أي سنة أخرى
        AcademicYear::where('is_active', true)->update(['is_active' => false]);
        $academicYear->update(['is_active' => true]);

        return redirect()->route('admin.academic-years.index')
            ->with('success', 'تم تعيين السنة النشطة بنجاح');
    }

    public function destroy(AcademicYear $academicYear)
    {
        if ($academicYear->is_active) {
            return back()->with('error', 'لا يمكن حذف السنة النشطة.');
        }
        $academicYear->delete();
        return redirect()->route('admin.academic-years.index')
            ->with('success', 'تم حذف السنة الأكاديمية');
    }
}