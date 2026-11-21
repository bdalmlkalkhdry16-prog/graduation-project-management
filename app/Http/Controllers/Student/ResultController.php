<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Grade;
use Illuminate\View\View;

class ResultController extends Controller
{
    /** نتائج الطالب المنشورة فقط، ولطالب واحد (هو نفسه). */
    public function index(): View
    {
        $profile = auth()->user()->studentProfile;

        $withheld = $profile ? app(\App\Services\FeeStatus::class)->withheldYears($profile) : collect();

        $grades = $profile
            ? Grade::whereNotNull('score')
                // درجة تُعرض فقط لعضو في القائمة المثبتة؛ والمنسحب لاحقًا تبقى نتائجه المنشورة
                ->whereIn('enrollment_id', \App\Models\ExamRosterEntry::select('enrollment_id')->whereColumn('exam_id', 'grades.exam_id'))
                ->whereHas('enrollment', fn ($q) => $q->where('student_profile_id', $profile->id))
                ->whereHas('exam', fn ($q) => $q->published())
                ->with(['exam.section.course', 'exam.section.academicTerm.academicYear'])
                ->get()
                // نتائج السنوات غير المسددة رسومها محجوبة
                ->reject(fn ($g) => $withheld->contains('id', $g->exam->section->academicTerm?->academic_year_id))
                ->sortByDesc(fn ($g) => $g->exam->published_at)
                ->values()
            : collect();

        return view('student.results.index', compact('grades', 'profile', 'withheld'));
    }
}
