<?php

namespace App\Http\Requests;

use App\Models\Exam;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * تعديل إداري لتوزيع درجات شعبة (إنشاء/تعديل/حذف اختبار) مع سبب إلزامي.
 */
class AdjustDistributionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('adjustDistribution', Exam::class);
    }

    private function exam(): ?Exam
    {
        $exam = $this->route('exam');

        return $exam instanceof Exam ? $exam : null;
    }

    public function rules(): array
    {
        $reason = ['required', 'string', 'min:5', 'max:1000'];

        if ($this->isMethod('DELETE')) {
            return ['reason' => $reason];
        }

        $exam = $this->exam();
        $sectionId = $exam?->section_id ?? $this->input('section_id');

        $rules = [
            'reason' => $reason,
            'name' => ['required', 'string', 'max:150', Rule::unique('exams', 'name')->where('section_id', $sectionId)->ignore($exam?->id)],
            'type' => ['required', Rule::in(Exam::TYPES)],
            'max_score' => ['required', 'numeric', 'gt:0', 'max:9999.99', 'decimal:0,2'],
            'weight' => ['required', 'numeric', 'min:0', 'max:9999.99', 'decimal:0,2'],
        ];

        if (! $exam) {
            $rules['section_id'] = ['required', 'integer', 'exists:sections,id'];
            $rules['exam_date'] = ['required', 'date'];
            $rules['duration_minutes'] = ['required', 'integer', 'min:1', 'max:600'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'reason.required' => 'سبب التعديل الإداري إلزامي.',
            'reason.min' => 'اكتب سببًا واضحًا (5 أحرف على الأقل).',
            'name.unique' => 'يوجد اختبار بهذا الاسم في نفس الشعبة.',
        ];
    }
}
