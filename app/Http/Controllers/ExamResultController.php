<?php

namespace App\Http\Controllers;

use App\Http\Requests\RejectResultsRequest;
use App\Models\Exam;
use App\Services\ExamResultWorkflow;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ExamResultController extends Controller
{
    public function __construct(private ExamResultWorkflow $workflow)
    {
    }

    /** صفحة مراجعة واعتماد النتائج. */
    public function review(): View
    {
        $this->authorize('reviewResults', Exam::class);

        $exams = Exam::with(['section.course', 'section.academicTerm.academicYear', 'section.faculty.user'])
            ->whereIn('result_status', [Exam::STATUS_SUBMITTED, Exam::STATUS_APPROVED])
            ->orderBy('submitted_at')
            ->get();

        return view('results.review', compact('exams'));
    }

    /** صفحة نشر النتائج المعتمدة. */
    public function publishIndex(): View
    {
        $this->authorize('publishResults', Exam::class);

        $exams = Exam::with(['section.course', 'section.academicTerm.academicYear'])
            ->where('result_status', Exam::STATUS_APPROVED)
            ->orderBy('approved_at')
            ->get();

        return view('results.publish', compact('exams'));
    }

    public function submit(Exam $exam): RedirectResponse
    {
        $this->authorize('submitResults', $exam);
        $this->workflow->submit($exam, auth()->user());

        return back()->with('success', 'تم تقديم النتائج للمراجعة.');
    }

    public function approve(Exam $exam): RedirectResponse
    {
        $this->authorize('reviewResults', $exam);
        $this->workflow->approve($exam, auth()->user());

        return back()->with('success', 'تم اعتماد النتائج.');
    }

    public function reject(RejectResultsRequest $request, Exam $exam): RedirectResponse
    {
        $this->workflow->reject($exam, $request->user(), $request->validated()['reason']);

        return back()->with('success', 'تم رفض النتائج وإعادتها للمدرس.');
    }

    public function publish(Exam $exam): RedirectResponse
    {
        $this->authorize('publishResults', $exam);
        $this->workflow->publish($exam, auth()->user());

        return back()->with('success', 'تم نشر النتائج للطلاب.');
    }
}
