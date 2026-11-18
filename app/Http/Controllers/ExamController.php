<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExamRequest;
use App\Models\AcademicTerm;
use App\Models\Course;
use App\Models\Exam;
use App\Models\Section;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExamController extends Controller
{
    /** لوحة الاختبارات حسب الفصل والمقرر والشعبة. */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Exam::class);

        $sectionScope = fn ($q) => $this->scopeSections($q);

        $exams = Exam::with(['section.course', 'section.academicTerm.academicYear'])
            ->whereHas('section', $sectionScope)
            ->when($request->filled('term'), fn ($q) => $q->whereHas('section', fn ($s) => $s->where('academic_term_id', $request->integer('term'))))
            ->when($request->filled('course'), fn ($q) => $q->whereHas('section', fn ($s) => $s->where('course_id', $request->integer('course'))))
            ->when($request->filled('section'), fn ($q) => $q->where('section_id', $request->integer('section')))
            ->orderByDesc('exam_date')
            ->paginate(20)
            ->withQueryString();

        $terms = AcademicTerm::with('academicYear')->orderByDesc('id')->get();
        $courses = Course::orderBy('code')->get();
        $sections = Section::with('course')->where($sectionScope)->get();

        return view('exams.index', compact('exams', 'terms', 'courses', 'sections'));
    }

    public function create(): View
    {
        $this->authorize('create', Exam::class);

        return view('exams.form', ['exam' => new Exam(), 'sections' => $this->manageableSections()]);
    }

    public function store(ExamRequest $request): RedirectResponse
    {
        $exam = Exam::create($request->validated() + ['created_by' => $request->user()->id]);

        return redirect()->route('exams.show', $exam)->with('success', 'تم إنشاء الاختبار.');
    }

    public function show(Exam $exam): View
    {
        $this->authorize('view', $exam);

        $exam->load(['section.course', 'section.academicTerm.academicYear', 'section.faculty.user']);
        $gradesCount = $exam->grades()->whereNotNull('score')->count();

        return view('exams.show', compact('exam', 'gradesCount'));
    }

    public function edit(Exam $exam): View
    {
        $this->authorize('update', $exam);

        return view('exams.form', ['exam' => $exam, 'sections' => collect([$exam->section->load('course')])]);
    }

    public function update(ExamRequest $request, Exam $exam): RedirectResponse
    {
        $exam->update($request->validated());

        return redirect()->route('exams.show', $exam)->with('success', 'تم تحديث الاختبار.');
    }

    public function destroy(Exam $exam): RedirectResponse
    {
        $this->authorize('delete', $exam);

        if ($exam->section->distributionLocked() || ! $exam->isEditable() || $exam->grades()->exists()) {
            return back()->withErrors(['exam' => 'لا يمكن حذف اختبار له درجات مرصودة، أو نتائجه خارج حالة المسودة/المرفوضة، أو توزيع شعبته مقفل (التعديل إداري فقط).']);
        }

        $exam->delete();

        return redirect()->route('exams.index')->with('success', 'تم حذف الاختبار.');
    }

    /** شعب يراها المستخدم: الكل لمن يملك صلاحية شاملة، وإلا شعبه فقط. */
    private function scopeSections($query)
    {
        $user = auth()->user();

        if ($user->can('viewAll', Exam::class)) {
            return $query;
        }

        return $query->where('faculty_profile_id', $user->facultyProfile?->id ?? 0);
    }

    private function manageableSections()
    {
        $user = auth()->user();
        $query = Section::with(['course', 'academicTerm.academicYear']);

        if (! $user->hasPermission('exams.manage') && ! $user->hasRole('admin')) {
            $query->where('faculty_profile_id', $user->facultyProfile?->id ?? 0);
        }

        return $query->get();
    }
}
