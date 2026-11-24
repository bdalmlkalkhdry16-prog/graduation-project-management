<?php

namespace App\Http\Requests;

use App\Models\StudentServiceRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateServiceRequestStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('updateStatus', $this->route('serviceRequest'));
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in([
                StudentServiceRequest::STATUS_PENDING,
                StudentServiceRequest::STATUS_IN_PROGRESS,
                StudentServiceRequest::STATUS_APPROVED,
                StudentServiceRequest::STATUS_REJECTED,
                StudentServiceRequest::STATUS_COMPLETED,
            ])],
            'staff_notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            if ($v->errors()->isNotEmpty()) {
                return;
            }

            $serviceRequest = $this->route('serviceRequest');
            $new = $this->input('status');

            if (! in_array($new, $serviceRequest->nextStatuses(), true)) {
                $v->errors()->add('status', 'لا يمكن نقل الطلب من "'.StudentServiceRequest::statusLabel($serviceRequest->status).'" إلى "'.StudentServiceRequest::statusLabel($new).'".');

                return;
            }

            if ($new === StudentServiceRequest::STATUS_APPROVED && $serviceRequest->type === StudentServiceRequest::TYPE_TRANSCRIPT) {
                $check = app(\App\Services\StudyPlanCompletion::class)->check($serviceRequest->studentProfile);

                if (! $check['eligible']) {
                    $v->errors()->add('status', 'لا يمكن قبول طلب كشف الدرجات: '.$check['message']);

                    return;
                }
            }

            if ($new === StudentServiceRequest::STATUS_APPROVED && $serviceRequest->type === StudentServiceRequest::TYPE_GRADUATION_STATEMENT
                && $serviceRequest->studentProfile
                && ($blocker = app(\App\Services\ProjectEligibility::class)->graduationBlocker($serviceRequest->studentProfile))) {
                $v->errors()->add('status', 'لا يمكن قبول إفادة التخرج: '.$blocker);

                return;
            }

            if ($new === StudentServiceRequest::STATUS_REJECTED && ! filled($this->input('staff_notes'))) {
                $v->errors()->add('staff_notes', 'سبب الرفض إلزامي في ملاحظات الموظف.');
            }
        });
    }
}