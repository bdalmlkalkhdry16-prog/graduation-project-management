<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEnrollmentRequest extends FormRequest
{
    /**
     * تحقق مباشر من الصلاحية (وليس عبر Gate/Policy) لأن Gate::before
     * يمنح مدير النظام كل شيء، بينما إدارة التسجيل حصرية لشؤون الطلاب.
     */
    public function authorize(): bool
    {
        return (bool) $this->user()?->hasPermission('enrollments.manage');
    }

    public function rules(): array
    {
        return [
            'student_profile_id' => ['required', 'integer', 'exists:student_profiles,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'student_profile_id.exists' => 'الطالب غير موجود.',
        ];
    }
}
