<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdjustDistributionRequest;
use App\Models\Exam;
use App\Models\Section;
use App\Services\ExamDistributionService;
use Illuminate\Http\RedirectResponse;

/** تعديلات إدارية على توزيع درجات شعبة بعد تقديم النتائج (بسبب وسجل قيم). */
class ExamDistributionController extends Controller
{
    public function __construct(private ExamDistributionService $service)
    {
    }

    public function store(AdjustDistributionRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $exam = $this->service->create(Section::findOrFail($data['section_id']), $data, $data['reason'], $request->user());

        return redirect()->route('exams.show', $exam)->with('success', 'تم تسجيل التعديل الإداري على التوزيع.');
    }

    public function update(AdjustDistributionRequest $request, Exam $exam): RedirectResponse
    {
        $this->service->update($exam, $request->validated(), $request->validated()['reason'], $request->user());

        return redirect()->route('exams.show', $exam)->with('success', 'تم تسجيل التعديل الإداري على التوزيع.');
    }

    public function destroy(AdjustDistributionRequest $request, Exam $exam): RedirectResponse
    {
        $this->service->delete($exam, $request->validated()['reason'], $request->user());

        return redirect()->route('exams.index')->with('success', 'تم حذف الاختبار إداريًا وتسجيل السبب.');
    }
}
