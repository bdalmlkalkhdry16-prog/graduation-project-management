<?php

namespace App\Http\Requests;

use App\Models\Schedule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAttendanceSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $schedule = Schedule::find($this->input('schedule_id'));

        if (! $schedule) {
            return false;
        }

        $facultyProfile = $this->user()->facultyProfile;

        return $facultyProfile && $schedule->section->faculty_profile_id === $facultyProfile->id;
    }

    public function rules(): array
    {
        return [
            'schedule_id' => ['required', 'exists:schedules,id'],
            'session_date' => [
                'required',
                'date',
                Rule::unique('attendance_sessions', 'session_date')->where('schedule_id', $this->input('schedule_id')),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'session_date.unique' => 'توجد جلسة حضور مسجَّلة بالفعل لهذا الجدول في هذا التاريخ.',
        ];
    }
}