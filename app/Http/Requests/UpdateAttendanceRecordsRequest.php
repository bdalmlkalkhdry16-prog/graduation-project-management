<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAttendanceRecordsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manageAttendance', $this->route('attendanceSession'));
    }

    public function rules(): array
    {
        return [
            'records' => ['required', 'array'],
            'records.*.student_profile_id' => ['required', 'exists:student_profiles,id'],
            'records.*.status' => ['required', Rule::in(['present', 'absent', 'late', 'excused'])],
        ];
    }
}