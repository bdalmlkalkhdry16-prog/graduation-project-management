<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RejectResultsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('reviewResults', $this->route('exam'));
    }

    public function rules(): array
    {
        return ['reason' => ['required', 'string', 'min:5', 'max:1000']];
    }

    public function messages(): array
    {
        return ['reason.required' => 'سبب الرفض إلزامي.', 'reason.min' => 'اكتب سببًا واضحًا للرفض (5 أحرف على الأقل).'];
    }
}
