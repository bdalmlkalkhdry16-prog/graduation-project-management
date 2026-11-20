<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Http\Requests\GenerateFeesRequest;
use App\Models\AcademicYear;
use App\Models\Department;
use App\Models\StudentFee;
use App\Services\FeeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentFeeController extends Controller
{
    public function __construct(private FeeService $fees)
    {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', StudentFee::class);

        $fees = StudentFee::with(['studentProfile.user', 'academicYear'])->withSum('payments', 'amount')
            ->when($request->filled('academic_year_id'), fn ($q) => $q->where('academic_year_id', $request->integer('academic_year_id')))
            ->when($request->filled('q'), fn ($q) => $q->whereHas('studentProfile', fn ($p) => $p->where('number_student', 'like', '%'.$request->input('q').'%')))
            ->latest()->get()
            ->when($request->input('status') === 'unsettled', fn ($c) => $c->reject->isSettled())
            ->when($request->input('status') === 'settled', fn ($c) => $c->filter->isSettled());

        return view('accounting.fees.index', [
            'fees' => $fees,
            'years' => AcademicYear::orderByDesc('year')->get(),
            'departments' => Department::orderBy('name_ar')->get(),
        ]);
    }

    public function generate(GenerateFeesRequest $request): RedirectResponse
    {
        $result = $this->fees->generateForYear(
            AcademicYear::findOrFail($request->integer('academic_year_id')),
            $request->filled('department_id') ? Department::findOrFail($request->integer('department_id')) : null,
            $request->user()
        );

        return back()->with('success', "تم توليد {$result['created']} رسم، وتخطي {$result['skipped_existing']} مولَّد سابقًا، و{$result['no_price']} بلا سعر لقسمهم.");
    }

    public function show(StudentFee $studentFee): View
    {
        $this->authorize('view', $studentFee);

        return view('accounting.fees.show', ['fee' => $studentFee->load(['studentProfile.user', 'academicYear', 'payments.recordedBy'])]);
    }
}
