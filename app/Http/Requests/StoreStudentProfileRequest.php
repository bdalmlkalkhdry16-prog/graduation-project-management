<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStudentProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => [
                'required',
                Rule::exists('users', 'id')->where('role', 'student'),
                Rule::unique('student_profiles', 'user_id'),
            ],
            'number_student' => ['required', 'string', 'max:50', Rule::unique('student_profiles', 'number_student')],
            'specialization_id' => ['nullable', 'exists:specializations,id'],
            'program_id' => ['nullable', 'exists:programs,id'],
            'current_level_id' => ['nullable', 'exists:levels,id'],
            'admission_year' => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'academic_status' => ['nullable', Rule::in(['active', 'suspended', 'withdrawn', 'graduated'])],
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required' => 'اختر المستخدم (الطالب) المطلوب إنشاء ملفه.',
            'user_id.exists' => 'المستخدم المحدَّد ليس له دور طالب في النظام.',
            'user_id.unique' => 'هذا المستخدم لديه ملف أكاديمي بالفعل.',
            'number_student.required' => 'رقم القيد مطلوب.',
            'number_student.unique' => 'رقم القيد هذا مستخدَم بالفعل.',
        ];
    }
}