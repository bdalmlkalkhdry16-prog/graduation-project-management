<?php

namespace App\Http\Controllers\Faculty;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\Section;
use Illuminate\View\View;

class EnrollmentController extends Controller
{
    /** شعب المدرس مع أعداد المسجَّلين فعليًا. */
    public function index(): View
    {
        $facultyProfile = auth()->user()->facultyProfile;

        $sections = $facultyProfile
            ? $facultyProfile->sections()
                ->with(['course', 'academicTerm.academicYear'])
                ->withCount(['enrollments as active_count' => fn ($q) => $q->where('status', Enrollment::STATUS_ACTIVE)])
                ->get()
            : collect();

        return view('faculty.enrollments.index', compact('sections'));
    }

    /** الطلاب المسجَّلون فعليًا (active) في شعبة يدرّسها المدرس نفسه فقط. */
    public function show(Section $section): View
    {
        $facultyProfile = auth()->user()->facultyProfile;

        abort_unless($facultyProfile && $section->faculty_profile_id === $facultyProfile->id, 403, 'هذه الشعبة ليست من شعبك.');

        $section->load(['course', 'academicTerm.academicYear']);

        $students = $section->activeStudentProfiles()->with(['user', 'currentLevel'])->orderBy('number_student')->get();

        return view('faculty.enrollments.show', compact('section', 'students'));
    }
}
