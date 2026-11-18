<?php

namespace App\Http\Requests;

use App\Models\Exam;
use App\Models\Section;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * إنشاء/تعديل اختبار (يُميَّز التعديل بوجود {exam} في المسار).
 */
class ExamRequest extends FormRequest
{
    private function exam(): ?Exam
    {
        $exam = $this->route('exam');

        return $exam instanceof Exam ? $exam : null;
    }

    public function authorize(): bool
    {
        return $this->exam()
            ? $this->user()->can('update', $this->exam())
            : $this->user()->can('create', Exam::class);
    }

    public function rules(): array
    {
        $exam = $this->exam();
        $sectionId = $exam?->section_id ?? $this->input('section_id');

        $rules = [
            'name' => [
                'required', 'string', 'max:150',
                Rule::unique('exams', 'name')->where('section_id', $sectionId)->ignore($exam?->id),
            ],
            'type' => ['required', Rule::in(Exam::TYPES)],
            'exam_date' => ['required', 'date'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'duration_minutes' => ['required', 'integer', 'min:1', 'max:600'],
            'max_score' => ['required', 'numeric', 'gt:0', 'max:9999.99', 'decimal:0,2'],
            'weight' => ['required', 'numeric', 'min:0', 'max:9999.99', 'decimal:0,2'],
        ];

        if (! $exam) {
            $rules['section_id'] = ['required', 'integer', 'exists:sections,id'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'name.unique' => 'يوجد اختبار بهذا الاسم في نفس الشعبة.',
            'max_score.gt' => 'الدرجة النهائية يجب أن تكون أكبر من صفر.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            if ($v->errors()->isNotEmpty()) {
                return;
            }

            $exam = $this->exam();
            $section = $exam?->section ?? Section::with('academicTerm')->find($this->input('section_id'));

            if (! $section) {
                return;
            }

            if (! $exam && ! $this->user()->can('createForSection', [Exam::class, $section])) {
                $v->errors()->add('section_id', 'لا تملك صلاحية إنشاء اختبار في هذه الشعبة.');

                return;
            }

            // توزيع الدرجات خاص بالشعبة ويُقفل بتقديم نتائج أي اختبار فيها؛ بعدها التعديل إداري فقط (بسبب وسجل)
            if ($section->distributionLocked()) {
                $v->errors()->add('exam', 'توزيع درجات هذه الشعبة مقفل بعد تقديم النتائج؛ أي تعديل يتم إداريًا بسبب مسجَّل.');

                return;
            }

            // لا يقل الحد الأقصى عن أعلى درجة مرصودة
            if ($exam) {
                $highest = $exam->grades()->max('score');
                if ($highest !== null && (float) $this->input('max_score') < (float) $highest) {
                    $v->errors()->add('max_score', 'الدرجة النهائية أقل من درجة مرصودة فعلًا ('.$highest.').');
                }
            }

            // التاريخ ضمن فترة الفصل إن كانت محددة
            $term = $section->academicTerm;
            $date = Carbon::parse($this->input('exam_date'))->startOfDay();
            if ($term?->start_date && $date->lt($term->start_date->copy()->startOfDay())) {
                $v->errors()->add('exam_date', 'تاريخ الاختبار قبل بداية الفصل الدراسي.');
            }
            if ($term?->end_date && $date->gt($term->end_date->copy()->startOfDay())) {
                $v->errors()->add('exam_date', 'تاريخ الاختبار بعد نهاية الفصل الدراسي.');
            }

            // عدم تداخل اختبارين لنفس الشعبة في الوقت نفسه
            if ($this->filled('start_time')) {
                $start = Carbon::parse($this->input('exam_date').' '.$this->input('start_time'));
                $end = $start->copy()->addMinutes((int) $this->input('duration_minutes'));

                $clash = Exam::where('section_id', $section->id)
                    ->whereDate('exam_date', $this->input('exam_date'))
                    ->whereNotNull('start_time')
                    ->when($exam, fn ($q) => $q->where('id', '!=', $exam->id))
                    ->get()
                    ->contains(function (Exam $other) use ($start, $end) {
                        $oStart = Carbon::parse($other->exam_date->format('Y-m-d').' '.$other->start_time);
                        $oEnd = $oStart->copy()->addMinutes($other->duration_minutes);

                        return $start->lt($oEnd) && $end->gt($oStart);
                    });

                if ($clash) {
                    $v->errors()->add('start_time', 'يتعارض هذا الوقت مع اختبار آخر لنفس الشعبة.');
                }
            }
        });
    }
}
