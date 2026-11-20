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

    /** سند القبض: للمحاسب أو للطالب صاحب الرسم. */
    public function receipt(Payment $payment): View
    {
        $this->authorize('view', $payment->studentFee);

        return view('accounting.receipt', ['payment' => $payment->load(['studentFee.studentProfile.user', 'studentFee.academicYear', 'recordedBy']), 'institution' => config('academic.institution_name')]);
    }
}
