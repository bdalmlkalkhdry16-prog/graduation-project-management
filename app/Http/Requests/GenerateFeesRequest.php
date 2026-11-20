<?php

namespace App\Http\Requests;

use App\Models\StudentFee;
use Illuminate\Foundation\Http\FormRequest;

class GenerateFeesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('generate', StudentFee::class);
    }

    public function rules(): array
    {
        return [
            'academic_year_id' => ['required', 'integer', 'exists:academic_years,id'],
            'department_id' => ['nullable', 'integer', 'exists:departments,id'],
        ];
    }
}
