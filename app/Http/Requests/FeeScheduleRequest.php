<?php

namespace App\Http\Requests;

use App\Models\FeeSchedule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FeeScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage', FeeSchedule::class);
    }

    public function rules(): array
    {
        $current = $this->route('fee_schedule');

        return [
            'department_id' => ['required', 'integer', 'exists:departments,id'],
            'academic_year_id' => [
                'required', 'integer', 'exists:academic_years,id',
                Rule::unique('fee_schedules')->where('department_id', $this->input('department_id'))->ignore($current?->id),
            ],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:9999999999.99', 'decimal:0,2'],
            'currency' => ['required', Rule::in(array_keys(FeeSchedule::CURRENCIES))],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            $current = $this->route('fee_schedule');

            // سعر ولّد رسومًا للطلاب: لا يُنقل لقسم/سنة أخرى (المبالغ المثبتة عند الطلاب لا تتأثر بتغيير المبلغ)
            if ($current && $current->studentFees()->exists()
                && ((int) $this->input('department_id') !== $current->department_id || (int) $this->input('academic_year_id') !== $current->academic_year_id)) {
                $v->errors()->add('department_id', 'لا يمكن تغيير القسم أو السنة لسعر ولّد رسومًا للطلاب.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'academic_year_id.unique' => 'يوجد سعر لهذا القسم عن هذه السنة الدراسية بالفعل؛ عدِّله بدل إنشاء سعر جديد.',
            'amount.min' => 'المبلغ يجب أن يكون أكبر من صفر.',
            'currency.in' => 'العملة المسموحة: الريال اليمني (القديم) أو الدولار.',
        ];
    }
}
