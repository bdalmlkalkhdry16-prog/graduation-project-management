<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateAcademicSettingsRequest;
use App\Models\AcademicSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AcademicSettingController extends Controller
{
    public function edit(): View
    {
        $this->authorize('manage', AcademicSetting::class);

        return view('settings.academic', [
            'passPercentage' => AcademicSetting::passPercentage(),
            'appealWindowDays' => AcademicSetting::appealWindowDays(),
            'scale' => AcademicSetting::gradeScale(),
            'project' => [
                'min_hours' => AcademicSetting::number(AcademicSetting::PROJECT_MIN_COMPLETED_HOURS),
                'min_percent' => AcademicSetting::number(AcademicSetting::PROJECT_MIN_COMPLETED_PERCENT),
                'final_level' => AcademicSetting::flag(AcademicSetting::PROJECT_REQUIRE_FINAL_LEVEL),
                'min_level' => AcademicSetting::number(AcademicSetting::PROJECT_MIN_LEVEL),
                'max_members' => AcademicSetting::number(AcademicSetting::PROJECT_MAX_MEMBERS),
                'fees' => AcademicSetting::flag(AcademicSetting::PROJECT_REQUIRE_FEES_SETTLED),
                'single' => AcademicSetting::flag(AcademicSetting::PROJECT_SINGLE_ACTIVE),
                'graduation' => AcademicSetting::flag(AcademicSetting::GRADUATION_REQUIRES_PROJECT),
            ],
        ]);
    }

    public function update(UpdateAcademicSettingsRequest $request): RedirectResponse
    {
        $userId = $request->user()->id;

        AcademicSetting::put(AcademicSetting::PASS_PERCENTAGE, $request->filled('pass_percentage') ? (float) $request->input('pass_percentage') : null, $userId);
        AcademicSetting::put(AcademicSetting::APPEAL_WINDOW_DAYS, $request->filled('appeal_window_days') ? (int) $request->input('appeal_window_days') : null, $userId);
        foreach ([
            AcademicSetting::PROJECT_MIN_COMPLETED_HOURS => 'project_min_completed_hours',
            AcademicSetting::PROJECT_MIN_LEVEL => 'project_min_level',
            AcademicSetting::PROJECT_MIN_COMPLETED_PERCENT => 'project_min_completed_percent',
            AcademicSetting::PROJECT_MAX_MEMBERS => 'project_max_members',
        ] as $key => $field) {
            AcademicSetting::put($key, $request->filled($field) ? (float) $request->input($field) : null, $userId);
        }
        foreach ([
            AcademicSetting::PROJECT_REQUIRE_FEES_SETTLED => 'project_require_fees_settled',
            AcademicSetting::PROJECT_SINGLE_ACTIVE => 'project_single_active',
            AcademicSetting::PROJECT_REQUIRE_FINAL_LEVEL => 'project_require_final_level',
            AcademicSetting::GRADUATION_REQUIRES_PROJECT => 'graduation_requires_project',
        ] as $key => $field) {
            AcademicSetting::put($key, $request->boolean($field), $userId);
        }
        AcademicSetting::put(AcademicSetting::GRADE_SCALE, $request->scale(), $userId);

        return back()->with('success', 'تم حفظ الإعدادات الأكاديمية.');
    }

    /** يعيد القواعد الموصى بها لمشاريع التخرج (يستبدل قيم هذا القسم). */
    public function applyRecommendedProjectRules(\Illuminate\Http\Request $request): RedirectResponse
    {
        $this->authorize('manage', AcademicSetting::class);
        AcademicSetting::applyRecommendedProjectRules($request->user()->id, overwrite: true);
        AcademicSetting::put(AcademicSetting::PROJECT_MIN_COMPLETED_HOURS, null, $request->user()->id);
        AcademicSetting::put(AcademicSetting::PROJECT_MIN_LEVEL, null, $request->user()->id);

        return back()->with('success', 'طُبّقت القواعد الموصى بها لمشاريع التخرج.');
    }
}
