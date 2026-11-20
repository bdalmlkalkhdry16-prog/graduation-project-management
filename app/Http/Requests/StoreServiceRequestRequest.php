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
            'description' => [\Illuminate\Validation\Rule::requiredIf($this->input('type') === \App\Models\StudentServiceRequest::TYPE_GRADE_APPEAL), 'nullable', 'string', \Illuminate\Validation\Rule::when($this->input('type') === \App\Models\StudentServiceRequest::TYPE_GRADE_APPEAL, ['min:10']), 'max:2000'],
            // يُحفظ للتظلم فقط ويُهمل في بقية الأنواع
            'enrollment_id' => ['exclude_unless:type,'.\App\Models\StudentServiceRequest::TYPE_GRADE_APPEAL, 'required', 'integer'],
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

            // التظلم: على مقرر للطالب نفسه له درجة منشورة، وبلا تظلم مفتوح سابق على المقرر ذاته
            $isAppeal = $this->input('type') === \App\Models\StudentServiceRequest::TYPE_GRADE_APPEAL;

            if ($profile && $isAppeal
                && ! \App\Models\StudentServiceRequest::appealableEnrollments($profile)->contains('id', (int) $this->input('enrollment_id'))) {
                $v->errors()->add('enrollment_id', 'يمكن التظلم فقط على مقرر لك له درجة منشورة.');

                return;
            }

            $duplicate = $profile && $profile->serviceRequests()
                ->where('type', $this->input('type'))
                ->when($isAppeal, fn ($q) => $q->where('enrollment_id', $this->input('enrollment_id')))
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
            'description.required' => 'اذكر سبب التظلم.',
            'description.min' => 'اشرح سبب التظلم بوضوح (10 أحرف على الأقل).',
            'enrollment_id.required' => 'اختر المقرر المتظلَّم عليه.',
            'type.in' => 'نوع الطلب غير متاح.',
        ];
    }
}