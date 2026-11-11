<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEnrollmentRequest;
use App\Http\Requests\UpdateEnrollmentStatusRequest;
use App\Models\AcademicTerm;
use App\Models\Enrollment;
use App\Models\Section;
use App\Models\StudentProfile;
use App\Services\EnrollmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EnrollmentController extends Controller
{
    public function __construct(private EnrollmentService $service)
    {
    }

    /** الشعب حسب الفصل الأكاديمي. */
    public function index(Request $request): View
    {
        $terms = AcademicTerm::with('academicYear')->orderByDesc('id')->get();
        $termId = $request->integer('term') ?: $terms->first()?->id;

        $sections = Section::with(['course', 'academicTerm.academicYear', 'faculty.user', 'schedules.room'])
            ->withCount(['enrollments as active_count' => fn ($q) => $q->where('status', Enrollment::STATUS_ACTIVE)])
            ->when($termId, fn ($q) => $q->where('academic_term_id', $termId))
            ->orderBy('course_id')
            ->orderBy('code')
            ->paginate(20)
            ->withQueryString();

        return view('staff.enrollments.index', compact('terms', 'termId', 'sections'));
    }

    /** بيانات الشعبة، المسجَّلون، والطلاب المؤهَّلون (مع بحث). */
    public function show(Request $request, Section $section): View
    {
        $section->load(['course.studyPlans.level.program.specialization', 'academicTerm.academicYear', 'faculty.user', 'schedules.room']);

        $levels = $section->course->studyPlans->pluck('level')->filter();

        $enrollments = $section->enrollments()
            ->with(['studentProfile.user', 'studentProfile.currentLevel'])
            ->when($request->filled('q'), fn ($q) => $q->whereHas('studentProfile', function ($sp) use ($request) {
                $this->service->applySearch($sp, $request->input('q'));
            }))
            ->orderByRaw("case status when 'active' then 0 when 'completed' then 1 else 2 end")
            ->get();

        $eligible = $this->service->eligibleStudents($section, $request->input('q'))->limit(50)->get();

        $activeCount = $this->service->activeCount($section);

        return view('staff.enrollments.show', compact('section', 'levels', 'enrollments', 'eligible', 'activeCount'));
    }

    public function store(StoreEnrollmentRequest $request, Section $section): RedirectResponse
    {
        $student = StudentProfile::findOrFail($request->validated()['student_profile_id']);

        $this->service->enroll($student, $section);

        return redirect()->route('staff.enrollments.show', $section)->with('success', 'تم تسجيل الطالب في الشعبة.');
    }

    public function updateStatus(UpdateEnrollmentStatusRequest $request, Enrollment $enrollment): RedirectResponse
    {
        $this->service->changeStatus($enrollment, $request->validated()['status']);

        return back()->with('success', 'تم تحديث حالة التسجيل.');
    }

    /** سجل تسجيلات الطالب + البحث عن طالب. */
    public function students(Request $request): View
    {
        $students = $this->service->applySearch(StudentProfile::query()->with(['user', 'program.specialization', 'currentLevel']), $request->input('q'))
            ->orderBy('number_student')
            ->paginate(20)
            ->withQueryString();

        return view('staff.enrollments.students', compact('students'));
    }

    public function history(StudentProfile $studentProfile): View
    {
        $studentProfile->load(['user', 'program.specialization', 'currentLevel']);

        $enrollments = $studentProfile->enrollments()
            ->with(['section.course', 'section.academicTerm.academicYear', 'section.faculty.user'])
            ->latest()
            ->get();

        return view('staff.enrollments.history', compact('studentProfile', 'enrollments'));
    }
}
