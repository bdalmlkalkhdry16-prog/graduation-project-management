<?php

namespace App\Http\Requests;

use App\Models\Enrollment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class SaveGradesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('enterGrades', $this->route('exam'));
    }

    public function rules(): array
    {
        $max = $this->route('exam')->max_score;

        return [
            'scores' => ['present', 'array'],
            'absent' => ['nullable', 'array'],
            'absent.*' => ['boolean'],
            'scores.*' => ['nullable', 'numeric', 'min:0', 'max:'.$max, 'decimal:0,2'],
        ];
    }

    public function messages(): array
    {
        return [
            'scores.*.max' => 'الدرجة تتجاوز الدرجة النهائية للاختبار ('.$this->route('exam')->max_score.').',
            'scores.*.min' => 'الدرجة لا يمكن أن تكون سالبة.',
            'scores.*.numeric' => 'الدرجة يجب أن تكون رقمًا.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            $exam = $this->route('exam');

            if (! $exam->isEditable()) {
                $v->errors()->add('exam', 'نتائج هذا الاختبار مقفلة (حالتها: '.\App\Models\Exam::statusLabel($exam->result_status).').');

                return;
            }

            // لا درجات ولا غياب إلا لطلاب بتسجيل نشط في شعبة الاختبار
            $allowed = Enrollment::where('section_id', $exam->section_id)->active()->pluck('id')->all();
            $keys = array_merge(array_keys((array) $this->input('scores', [])), array_keys((array) $this->input('absent', [])));
            $invalid = array_diff(array_map('intval', $keys), $allowed);

            if ($invalid !== []) {
                $v->errors()->add('scores', 'لا يمكن رصد درجة لطالب غير مسجَّل (تسجيل نشط) في شعبة هذا الاختبار.');
            }
        });
    }
}
