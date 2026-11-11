<?php

namespace App\Http\Requests;

use App\Models\Enrollment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEnrollmentStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->hasPermission('enrollments.manage');
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(Enrollment::STATUSES)],
        ];
    }
}
