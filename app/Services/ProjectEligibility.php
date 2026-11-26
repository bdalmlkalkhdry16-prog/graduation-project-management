<?php

namespace App\Services;

use App\Models\AcademicSetting;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\StudentProfile;
use App\Models\User;

/**
 * أهلية الطالب للانضمام لمشروع تخرج وفق الإعدادات الأكاديمية. كل شرط اختياري: غير المضبوط لا يُفرض،
 * فلا تتغير معاملة المشاريع القائمة ما لم يضبط المدير شيئًا. لا تُخترع سياسة.
 */
class ProjectEligibility
{
    public function __construct(private AcademicRecordService $records, private FeeStatus $fees)
    {
    }

    /** @return list<string> أسباب عدم الأهلية؛ فارغة = مؤهَّل. */
    public function reasons(User $student, ?Project $project = null): array
    {
        $reasons = [];

        $maxMembers = AcademicSetting::number(AcademicSetting::PROJECT_MAX_MEMBERS);
        if ($maxMembers !== null && $project && ! $project->members()->where('student_id', $student->id)->exists()
            && $project->members()->count() >= (int) $maxMembers) {
            $reasons[] = 'اكتمل الحد الأقصى لأعضاء المشروع ('.(int) $maxMembers.').';
        }

        if (AcademicSetting::flag(AcademicSetting::PROJECT_SINGLE_ACTIVE)) {
            $other = ProjectMember::where('student_id', $student->id)
                ->when($project, fn ($q) => $q->where('project_id', '!=', $project->id))
                ->whereHas('project', fn ($q) => $q->whereNotIn('status', [Project::STATUS_REJECTED, Project::STATUS_COMPLETED]))
                ->exists();
            if ($other) {
                $reasons[] = 'الطالب عضو في مشروع قائم آخر (غير مرفوض أو مكتمل).';
            }
        }

        $minHours = AcademicSetting::number(AcademicSetting::PROJECT_MIN_COMPLETED_HOURS);
        $minLevel = AcademicSetting::number(AcademicSetting::PROJECT_MIN_LEVEL);
        $minPercent = AcademicSetting::number(AcademicSetting::PROJECT_MIN_COMPLETED_PERCENT);
        $finalLevel = AcademicSetting::flag(AcademicSetting::PROJECT_REQUIRE_FINAL_LEVEL);
        $needFees = AcademicSetting::flag(AcademicSetting::PROJECT_REQUIRE_FEES_SETTLED);

        if ($minHours === null && $minLevel === null && $minPercent === null && ! $finalLevel && ! $needFees) {
            return $reasons;
        }

        $profile = $student->studentProfile;
        if (! $profile) {
            return array_merge($reasons, ['لا يوجد ملف أكاديمي للطالب، ولا يمكن التحقق من شروط الانضمام للمشروع.']);
        }

        if ($minHours !== null) {
            if (AcademicSetting::passPercentage() === null) {
                $reasons[] = 'لم تُحدَّد نسبة النجاح في الإعدادات؛ لا يمكن احتساب الساعات المُنجَزة.';
            } else {
                $done = $this->records->completedHours($profile);
                if ($done < $minHours) {
                    $reasons[] = 'الساعات المُنجَزة ('.$this->fmt($done).') أقل من المطلوب ('.$this->fmt($minHours).').';
                }
            }
        }

        if ($minPercent !== null) {
            $planHours = $this->records->planHours($profile);
            if (AcademicSetting::passPercentage() === null) {
                $reasons[] = 'لم تُحدَّد نسبة النجاح في الإعدادات؛ لا يمكن احتساب نسبة الإنجاز.';
            } elseif ($planHours > 0) {
                $done = $this->records->completedHours($profile);
                $pct = $done / $planHours * 100;
                if ($pct + 1e-9 < $minPercent) {
                    $reasons[] = 'نسبة الإنجاز من ساعات الخطة ('.$this->fmt(round($pct, 1)).'%) أقل من المطلوب ('.$this->fmt($minPercent).'%).';
                }
            } else {
                $reasons[] = 'لا توجد خطة دراسية بساعات معتمدة لبرنامج الطالب؛ لا يمكن احتساب نسبة الإنجاز.';
            }
        }

        if ($finalLevel) {
            $current = $profile->currentLevel?->level_number;
            $last = $profile->program_id ? \App\Models\Level::where('program_id', $profile->program_id)->max('level_number') : null;
            if ($current === null || $last === null || $current < $last) {
                $reasons[] = 'الانضمام للمشروع من المستوى الأخير في البرنامج (مستواك الحالي: '.($current ?? 'غير محدد').' من '.($last ?? '—').').';
            }
        }

        if ($minLevel !== null) {
            $level = $profile->currentLevel?->level_number;
            if ($level === null || $level < $minLevel) {
                $reasons[] = 'المستوى الحالي ('.($level ?? 'غير محدد').') أقل من المطلوب ('.$this->fmt($minLevel).').';
            }
        }

        if ($needFees) {
            $years = $this->fees->withheldYears($profile);
            if ($years->isNotEmpty()) {
                $reasons[] = 'رسوم غير مسددة عن: '.$years->pluck('name')->join('، ').'.';
            }
        }

        return $reasons;
    }

    /** مشروع تخرج مكتمل للطالب (شرط إفادة التخرج عند تفعيله). */
    public function hasCompletedProject(User $student): bool
    {
        return ProjectMember::where('student_id', $student->id)
            ->whereHas('project', fn ($q) => $q->where('status', Project::STATUS_COMPLETED))->exists();
    }

    /** شرط إفادة التخرج من جهة المشروع: null = مستوفى أو غير مشروط، وإلا رسالة السبب. */
    public function graduationBlocker(StudentProfile $profile): ?string
    {
        if (! AcademicSetting::flag(AcademicSetting::GRADUATION_REQUIRES_PROJECT)) {
            return null;
        }

        return $this->hasCompletedProject($profile->user) ? null : 'تشترط الكلية مشروع تخرج مكتملًا ولا يوجد لك مشروع مكتمل.';
    }

    private function fmt(float $n): string
    {
        return rtrim(rtrim(number_format($n, 2, '.', ''), '0'), '.');
    }
}
