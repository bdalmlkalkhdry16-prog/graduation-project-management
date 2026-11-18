<?php

namespace App\Policies;

use App\Models\Exam;
use App\Models\Section;
use App\Models\User;

/**
 * Phase 7 — من يحق له ماذا. ملاحظة: Gate::before في المشروع يمنح مدير النظام كل شيء،
 * لذلك قواعد "حالة" الاختبار (قابل للتعديل/مقفل) تُفرض في الخدمات والـ FormRequests
 * وليس هنا، كي لا يتجاوزها المدير.
 */
class ExamPolicy
{
    /** يرى كل الاختبارات (وليس اختبارات شعبه فقط). */
    public function viewAll(User $user): bool
    {
        return $user->hasPermission('exams.manage')
            || $user->hasPermission('results.review')
            || $user->hasPermission('results.publish');
    }

    public function viewAny(User $user): bool
    {
        return $this->viewAll($user) || $user->hasPermission('exams.manageOwn') || $user->hasPermission('grades.enterOwn');
    }

    public function view(User $user, Exam $exam): bool
    {
        return $this->viewAll($user)
            || (($user->hasPermission('exams.manageOwn') || $user->hasPermission('grades.enterOwn')) && $this->owns($user, $exam->section));
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('exams.manage') || $user->hasPermission('exams.manageOwn');
    }

    /** إنشاء اختبار داخل شعبة محددة. */
    public function createForSection(User $user, Section $section): bool
    {
        return $user->hasPermission('exams.manage')
            || ($user->hasPermission('exams.manageOwn') && $this->owns($user, $section));
    }

    public function update(User $user, Exam $exam): bool
    {
        return $this->createForSection($user, $exam->section);
    }

    public function delete(User $user, Exam $exam): bool
    {
        return $this->update($user, $exam);
    }

    public function enterGrades(User $user, Exam $exam): bool
    {
        return $user->hasPermission('grades.enterOwn') && $this->owns($user, $exam->section);
    }

    public function submitResults(User $user, Exam $exam): bool
    {
        return $this->enterGrades($user, $exam);
    }

    public function reviewResults(User $user, ?Exam $exam = null): bool
    {
        return $user->hasPermission('results.review');
    }

    public function publishResults(User $user, ?Exam $exam = null): bool
    {
        return $user->hasPermission('results.publish');
    }

    /** تعديل إداري لتوزيع الدرجات بعد التقديم (بسبب إلزامي). */
    public function adjustDistribution(User $user, ?Exam $exam = null): bool
    {
        return $user->hasPermission('exams.adjustDistribution');
    }

    public function correctGrades(User $user, Exam $exam): bool
    {
        return $user->hasPermission('grades.correct');
    }

    private function owns(User $user, Section $section): bool
    {
        $profile = $user->facultyProfile;

        return $profile && $section->faculty_profile_id === $profile->id;
    }
}
