<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreServiceRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\StudentServiceRequest::class);
    }

    public function rules(): array
    {
        return [
            'type' => ['required', 'string', \Illuminate\Validation\Rule::in(array_keys(\App\Models\StudentServiceRequest::availableTypes()))],
            'description' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            if ($v->errors()->isNotEmpty()) {
                return;
            }

            $profile = $this->user()->studentProfile;

            // كشف الدرجات الرسمي: بعد إتمام الخطة الدراسية فقط
            if ($profile && $this->input('type') === \App\Models\StudentServiceRequest::TYPE_TRANSCRIPT) {
                $check = app(\App\Services\StudyPlanCompletion::class)->check($profile);

                if (! $check['eligible']) {
                    $v->errors()->add('type', 'كشف الدرجات يُطلب بعد إتمام الخطة الدراسية. '.$check['message']);

                    return;
                }
            }

            $duplicate = $profile && $profile->serviceRequests()
                ->where('type', $this->input('type'))
                ->whereIn('status', \App\Models\StudentServiceRequest::OPEN_STATUSES)
                ->exists();

            if ($duplicate) {
                $v->errors()->add('type', 'لديك طلب مفتوح من هذا النوع بالفعل؛ انتظر معالجته.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'type.required' => 'نوع الطلب مطلوب.',
            'type.in' => 'نوع الطلب غير متاح.',
        ];
    }
}