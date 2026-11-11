<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class EnrollmentController extends Controller
{
    /** تسجيلات الطالب النشطة فقط. */
    public function index(): View
    {
        $profile = auth()->user()->studentProfile;

        $enrollments = $profile
            ? $profile->enrollments()->active()
                ->with(['section.course', 'section.academicTerm.academicYear', 'section.faculty.user', 'section.schedules.room'])
                ->get()
            : collect();

        return view('student.enrollments.index', compact('enrollments', 'profile'));
    }
}
