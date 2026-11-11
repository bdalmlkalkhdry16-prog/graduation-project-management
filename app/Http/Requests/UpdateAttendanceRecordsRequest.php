<?php

namespace App\Http\Requests;

use App\Models\Enrollment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAttendanceRecordsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manageAttendance', $this->route('attendanceSession'));
    }

    private function sectionId(): ?int
    {
        return $this->route('attendanceSession')->schedule->section_id;
    }

    public function messages(): array
    {
        return [
            'records.*.student_profile_id.exists' => 'الطالب غير مسجَّل (تسجيل نشط) في شعبة هذه الجلسة.',
        ];
    }

    public function rules(): array
    {
        return [
            'records' => ['required', 'array'],
            'records.*.student_profile_id' => [
                'required',
                'exists:student_profiles,id',
                // يجب أن يكون الطالب مسجَّلًا (active) في شعبة جلسة الحضور
                Rule::exists('enrollments', 'student_profile_id')
                    ->where('section_id', $this->sectionId())
                    ->where('status', Enrollment::STATUS_ACTIVE),
            ],
            'records.*.status' => ['required', Rule::in(['present', 'absent', 'late', 'excused'])],
        ];
    }
}