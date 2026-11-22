<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Http\Requests\RecordPaymentRequest;
use App\Models\Payment;
use App\Models\StudentFee;
use App\Services\FeeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function store(RecordPaymentRequest $request, StudentFee $studentFee, FeeService $fees): RedirectResponse
    {
        $payment = $fees->recordPayment($studentFee, $request->validated()['amount'], $request->validated()['paid_at'], $request->validated()['notes'] ?? null, $request->user());

        return redirect()->route('accounting.fees.show', $studentFee)->with('success', 'تم تسجيل الدفعة. رقم السند: '.$payment->receipt_number);
    }

    public function void(\Illuminate\Http\Request $request, Payment $payment, FeeService $fees): RedirectResponse
    {
        $this->authorize('voidPayment', StudentFee::class);
        $data = $request->validate(['reason' => ['required', 'string', 'min:5', 'max:1000']], ['reason.required' => 'سبب الإلغاء مطلوب.', 'reason.min' => 'سبب الإلغاء قصير جدًا.']);
        $fees->voidPayment($payment, $data['reason'], $request->user());

        return redirect()->route('accounting.fees.show', $payment->student_fee_id)->with('success', 'أُلغيت الدفعة '.$payment->receipt_number.' وعاد مبلغها إلى المتبقي.');
    }

    /** سند القبض: للمحاسب أو للطالب صاحب الرسم. */
    public function receipt(Payment $payment): View
    {
        $this->authorize('view', $payment->studentFee);

        return view('accounting.receipt', ['payment' => $payment->load(['studentFee.studentProfile.user', 'studentFee.academicYear', 'recordedBy']), 'institution' => config('academic.institution_name')]);
    }
}
