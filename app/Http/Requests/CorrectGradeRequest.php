<?php

namespace App\Http\Requests;

use App\Models\Exam;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class CorrectGradeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('correctGrades', $this->route('exam'));
    }

    public function rules(): array
    {
        return [
            'new_score' => ['required', 'numeric', 'min:0', 'max:'.$this->route('exam')->max_score, 'decimal:0,2'],
            'reason' => ['required', 'string', 'min:5', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'new_score.max' => 'الدرجة تتجاوز الدرجة النهائية للاختبار.',
            'reason.required' => 'سبب التصحيح إلزامي.',
            'reason.min' => 'اكتب سببًا واضحًا للتصحيح (5 أحرف على الأقل).',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            if (! in_array($this->route('exam')->result_status, [Exam::STATUS_APPROVED, Exam::STATUS_PUBLISHED], true)) {
                $v->errors()->add('exam', 'التصحيح المعتمد متاح للنتائج المعتمدة أو المنشورة فقط.');
            }
        });
    }
}
