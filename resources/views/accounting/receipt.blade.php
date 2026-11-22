@extends('layouts.app')
@section('title', 'سند قبض '.$payment->receipt_number)
@section('content')
<div class="container">
    <div class="row justify-content-center"><div class="col-lg-7">
        <div class="card"><div class="card-body p-4">
            <div class="text-center mb-4">
                <h5>{{ $institution }}</h5>
                <h4>سند قبض</h4>
                <div class="small text-muted">رقم السند: {{ $payment->receipt_number }}</div>
            </div>
            @if ($payment->isVoided())
                <div class="alert alert-danger text-center fw-bold">سند ملغى بتاريخ {{ $payment->voided_at->format('Y-m-d') }} — {{ $payment->void_reason }}</div>
            @endif
            <dl class="row mb-0">
                <dt class="col-4 text-muted fw-normal">الطالب</dt><dd class="col-8">{{ $payment->studentFee->studentProfile->user->name }} ({{ $payment->studentFee->studentProfile->number_student }})</dd>
                <dt class="col-4 text-muted fw-normal">السنة الدراسية</dt><dd class="col-8">{{ $payment->studentFee->academicYear->name }}</dd>
                <dt class="col-4 text-muted fw-normal">المبلغ</dt><dd class="col-8 fw-bold">{{ number_format($payment->amount, 2) }} {{ \App\Models\FeeSchedule::currencyLabel($payment->studentFee->currency) }}</dd>
                <dt class="col-4 text-muted fw-normal">تاريخ الدفع</dt><dd class="col-8">{{ $payment->paid_at->format('Y-m-d') }}</dd>
                <dt class="col-4 text-muted fw-normal">المتبقي بعد الدفعة</dt><dd class="col-8">{{ number_format($payment->studentFee->remaining(), 2) }} (حتى تاريخ الطباعة)</dd>
                <dt class="col-4 text-muted fw-normal">استلمها</dt><dd class="col-8">{{ $payment->recordedBy?->name }}</dd>
                @if ($payment->notes)<dt class="col-4 text-muted fw-normal">ملاحظات</dt><dd class="col-8">{{ $payment->notes }}</dd>@endif
            </dl>
            <div class="text-center mt-4 d-print-none"><button onclick="window.print()" class="btn btn-outline-secondary"><i class="fas fa-print me-1"></i> طباعة</button></div>
        </div></div>
    </div></div>
</div>
@endsection
