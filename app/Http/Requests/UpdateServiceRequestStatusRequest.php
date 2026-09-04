<?php

namespace App\Http\Requests;

use App\Models\StudentServiceRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateServiceRequestStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('updateStatus', $this->route('serviceRequest'));
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in([
                StudentServiceRequest::STATUS_PENDING,
                StudentServiceRequest::STATUS_IN_PROGRESS,
                StudentServiceRequest::STATUS_APPROVED,
                StudentServiceRequest::STATUS_REJECTED,
                StudentServiceRequest::STATUS_COMPLETED,
            ])],
            'staff_notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}