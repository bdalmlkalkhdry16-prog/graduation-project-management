<?php

namespace App\Http\Controllers;

use App\Http\Requests\FeeScheduleRequest;
use App\Models\AcademicYear;
use App\Models\Department;
use App\Models\FeeSchedule;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class FeeScheduleController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', FeeSchedule::class);

        return view('fees.index', [
            'schedules' => FeeSchedule::with(['department', 'academicYear'])->orderByDesc('academic_year_id')->orderBy('department_id')->get(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('manage', FeeSchedule::class);

        return view('fees.form', ['schedule' => new FeeSchedule(), 'departments' => Department::orderBy('name_ar')->get(), 'years' => AcademicYear::orderByDesc('year')->get()]);
    }

    public function store(FeeScheduleRequest $request): RedirectResponse
    {
        FeeSchedule::create($request->validated() + ['updated_by' => $request->user()->id]);

        return redirect()->route('fee-schedules.index')->with('success', 'تم حفظ سعر الرسوم.');
    }

    public function edit(FeeSchedule $feeSchedule): View
    {
        $this->authorize('manage', FeeSchedule::class);

        return view('fees.form', ['schedule' => $feeSchedule, 'departments' => Department::orderBy('name_ar')->get(), 'years' => AcademicYear::orderByDesc('year')->get()]);
    }

    public function update(FeeScheduleRequest $request, FeeSchedule $feeSchedule): RedirectResponse
    {
        $feeSchedule->update($request->validated() + ['updated_by' => $request->user()->id]);

        return redirect()->route('fee-schedules.index')->with('success', 'تم تحديث سعر الرسوم.');
    }

    public function destroy(FeeSchedule $feeSchedule): RedirectResponse
    {
        $this->authorize('manage', FeeSchedule::class);

        if ($feeSchedule->studentFees()->exists()) {
            return back()->withErrors(['fee' => 'لا يمكن حذف سعر ولّد رسومًا للطلاب.']);
        }

        $feeSchedule->delete();

        return redirect()->route('fee-schedules.index')->with('success', 'تم حذف سعر الرسوم.');
    }
}
