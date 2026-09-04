<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStudentProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $studentProfile = $this->route('studentProfile');

        return [
            'number_student' => [
                'required', 'string', 'max:50',
                Rule::unique('student_profiles', 'number_student')->ignore($studentProfile?->id),
            ],
            'specialization_id' => ['nullable', 'exists:specializations,id'],
            'program_id' => ['nullable', 'exists:programs,id'],
            'current_level_id' => ['nullable', 'exists:levels,id'],
            'admission_year' => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'academic_status' => ['nullable', Rule::in(['active', 'suspended', 'withdrawn', 'graduated'])],
        ];
    }
}