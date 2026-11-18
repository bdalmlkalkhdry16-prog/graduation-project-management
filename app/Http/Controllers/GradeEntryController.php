<?php

namespace App\Http\Controllers;

use App\Http\Requests\CorrectGradeRequest;
use App\Http\Requests\SaveGradesRequest;
use App\Models\Exam;
use App\Models\Grade;
use App\Services\GradeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class GradeEntryController extends Controller
{
    public function __construct(private GradeService $service)
    {
    }

    /** صفحة رصد الدرجات: الطلاب بتسجيل نشط + الدرجات الحالية (+ سجل التعديلات لمن يراجع). */
    public function edit(Exam $exam): View
    {
        $this->authorize('view', $exam);

        $exam->load(['section.course', 'section.academicTerm.academicYear']);
        $roster = $this->service->roster($exam);
        $grades = $exam->grades()->with('changes.changedBy')->get()->keyBy('enrollment_id');

        return view('exams.grades', compact('exam', 'roster', 'grades'));
    }

    public function update(SaveGradesRequest $request, Exam $exam): RedirectResponse
    {
        $changed = $this->service->saveScores($exam, $request->validated()['scores'], $request->user(), $request->validated()['absent'] ?? []);

        return redirect()->route('exams.grades.edit', $exam)
            ->with('success', $changed ? "تم حفظ المسودة ($changed تغيير)." : 'لا توجد تغييرات للحفظ.');
    }

    public function correct(CorrectGradeRequest $request, Exam $exam, Grade $grade): RedirectResponse
    {
        abort_unless($grade->exam_id === $exam->id, 404);

        $this->service->correct($grade, (float) $request->validated()['new_score'], $request->validated()['reason'], $request->user());

        return redirect()->route('exams.grades.edit', $exam)->with('success', 'تم تسجيل التصحيح في سجل التغييرات.');
    }
}
