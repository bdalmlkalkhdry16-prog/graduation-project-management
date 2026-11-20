@extends('layouts.tailwind')
@section('title', 'سند قبض '.$payment->receipt_number)
@section('content')
<div class="mx-auto max-w-xl rounded-xl border border-slate-300 bg-white p-8">
    <div class="mb-6 text-center">
        <div class="text-lg font-bold">{{ $institution }}</div>
        <div class="text-xl font-bold mt-1">سند قبض</div>
        <div class="text-sm text-slate-500 mt-1">رقم السند: {{ $payment->receipt_number }}</div>
    </div>
    <dl class="grid grid-cols-3 gap-y-3 text-sm">
        <dt class="text-slate-500">الطالب</dt><dd class="col-span-2">{{ $payment->studentFee->studentProfile->user->name }} ({{ $payment->studentFee->studentProfile->number_student }})</dd>
        <dt class="text-slate-500">السنة الدراسية</dt><dd class="col-span-2">{{ $payment->studentFee->academicYear->name }}</dd>
        <dt class="text-slate-500">المبلغ</dt><dd class="col-span-2 font-bold">{{ number_format($payment->amount, 2) }} {{ \App\Models\FeeSchedule::currencyLabel($payment->studentFee->currency) }}</dd>
        <dt class="text-slate-500">تاريخ الدفع</dt><dd class="col-span-2">{{ $payment->paid_at->format('Y-m-d') }}</dd>
        <dt class="text-slate-500">المتبقي بعد الدفعة</dt><dd class="col-span-2">{{ number_format($payment->studentFee->remaining(), 2) }} (حتى تاريخ الطباعة)</dd>
        <dt class="text-slate-500">استلمها</dt><dd class="col-span-2">{{ $payment->recordedBy?->name }}</dd>
        @if ($payment->notes)<dt class="text-slate-500">ملاحظات</dt><dd class="col-span-2">{{ $payment->notes }}</dd>@endif
    </dl>
    <div class="mt-6 text-center print:hidden"><button onclick="window.print()" class="rounded-lg border border-slate-300 px-4 py-2 text-sm hover:bg-slate-100">طباعة</button></div>
</div>
@endsection
