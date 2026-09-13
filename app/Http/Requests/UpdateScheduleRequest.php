<?php

namespace App\Http\Requests;

use App\Services\ScheduleConflictChecker;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'section_id' => ['required', 'exists:sections,id'],
            'room_id' => ['required', 'exists:rooms,id'],
            'day_of_week' => ['required', Rule::in(['sat', 'sun', 'mon', 'tue', 'wed', 'thu', 'fri'])],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $schedule = $this->route('schedule');

            $conflicts = app(ScheduleConflictChecker::class)->check(
                sectionId: (int) $this->input('section_id'),
                roomId: (int) $this->input('room_id'),
                dayOfWeek: $this->input('day_of_week'),
                startTime: $this->input('start_time'),
                endTime: $this->input('end_time'),
                ignoreScheduleId: $schedule?->id,
            );

            foreach ($conflicts as $message) {
                $validator->errors()->add('conflict', $message);
            }
        });
    }
}