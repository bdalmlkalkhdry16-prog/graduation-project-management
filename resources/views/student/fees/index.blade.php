@extends('layouts.tailwind')
@section('title', 'رسومي')
@section('content')
<h1 class="mb-4 text-2xl font-bold">رسومي</h1>
@forelse ($fees as $fee)
    <section class="mb-4 rounded-xl border border-slate-200 bg-white p-5">
        <div class="mb-2 flex items-center justify-between">
            <h2 class="font-bold">السنة الدراسية {{ $fee->academicYear->name }}</h2>
            @if ($fee->isSettled())<span class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs text-emerald-700">مسددة</span>@else<span class="rounded-full bg-amber-100 px-2 py-0.5 text-xs text-amber-700">غير مسددة</span>@endif
        </div>
        <div class="grid grid-cols-3 gap-2 text-sm">
            <div>المستحق: <b>{{ number_format($fee->amount, 2) }} {{ $fee->currency }}</b></div>
            <div>المدفوع: <b>{{ number_format($fee->paid(), 2) }}</b></div>
            <div>المتبقي: <b>{{ number_format($fee->remaining(), 2) }}</b></div>
        </div>
        @if ($fee->payments->isNotEmpty())
            <ul class="mt-3 space-y-1 text-sm text-slate-600">
                @foreach ($fee->payments as $p)
                    <li>{{ $p->paid_at->format('Y-m-d') }} — {{ number_format($p->amount, 2) }}@if ($p->isVoided()) <b class="text-red-600">(ملغاة)</b>@endif — <a class="text-indigo-600 hover:underline" href="{{ route('payments.receipt', $p) }}">سند {{ $p->receipt_number }}</a></li>
                @endforeach
            </ul>
        @endif
    </section>
@empty
    <p class="text-slate-500">لا توجد رسوم مسجَّلة عليك بعد.</p>
@endforelse
@endsection
