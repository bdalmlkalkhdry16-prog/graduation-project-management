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
            'scale' => AcademicSetting::gradeScale(),
        ]);
    }

    public function update(UpdateAcademicSettingsRequest $request): RedirectResponse
    {
        $userId = $request->user()->id;

        AcademicSetting::put(AcademicSetting::PASS_PERCENTAGE, $request->filled('pass_percentage') ? (float) $request->input('pass_percentage') : null, $userId);
        AcademicSetting::put(AcademicSetting::GRADE_SCALE, $request->scale(), $userId);

        return back()->with('success', 'تم حفظ الإعدادات الأكاديمية.');
    }
}
