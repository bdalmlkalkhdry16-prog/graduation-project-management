<?php

namespace App\Http\Requests;

use App\Models\StudentFee;
use Illuminate\Foundation\Http\FormRequest;

class RecordPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('recordPayment', StudentFee::class);
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'gt:0', 'max:9999999999.99', 'decimal:0,2'],
            'paid_at' => ['required', 'date', 'before_or_equal:today'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'amount.gt' => 'مبلغ الدفعة يجب أن يكون أكبر من صفر.',
            'paid_at.before_or_equal' => 'تاريخ الدفع لا يكون في المستقبل.',
        ];
    }
}
