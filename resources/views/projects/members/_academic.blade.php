@php($profile = $student->studentProfile)
@if ($profile)
    <div class="small text-muted">
        {{ $profile->program->specialization->name_ar ?? $profile->specialization->name_ar ?? '' }}
        @if ($profile->currentLevel) · {{ $profile->currentLevel->name }}@endif
        @if (\App\Models\AcademicSetting::passPercentage() !== null) · ساعات مُنجَزة: {{ rtrim(rtrim(number_format(app(\App\Services\AcademicRecordService::class)->completedHours($profile), 2, '.', ''), '0'), '.') }}@endif
    </div>
@endif
