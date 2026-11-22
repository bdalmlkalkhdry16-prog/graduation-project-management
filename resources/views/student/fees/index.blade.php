@extends('layouts.app')
@section('title', 'رسومي')
@section('content')
<div class="container-fluid">
    <h2 class="mb-4">رسومي</h2>
    @forelse ($fees as $fee)
        <div class="card mb-3"><div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">السنة الدراسية {{ $fee->academicYear->name }}</h5>
                @if ($fee->isSettled())<span class="badge bg-success">مسددة</span>@else<span class="badge bg-warning text-dark">غير مسددة</span>@endif
            </div>
            <div class="row g-2">
                <div class="col-6 col-md-3">المستحق: <b>{{ number_format($fee->amount, 2) }} {{ $fee->currency }}</b></div>
                <div class="col-6 col-md-3">الخصومات: <b>{{ number_format($fee->discount(), 2) }}</b></div>
                <div class="col-6 col-md-3">المدفوع: <b>{{ number_format($fee->paid(), 2) }}</b></div>
                <div class="col-6 col-md-3">المتبقي: <b>{{ number_format($fee->remaining(), 2) }}</b></div>
            </div>
            @if ($fee->payments->isNotEmpty())
                <ul class="mt-3 mb-0 small text-muted">
                    @foreach ($fee->payments as $p)
                        <li>{{ $p->paid_at->format('Y-m-d') }} — {{ number_format($p->amount, 2) }}@if ($p->isVoided()) <b class="text-danger">(ملغاة)</b>@endif — <a href="{{ route('payments.receipt', $p) }}">سند {{ $p->receipt_number }}</a></li>
                    @endforeach
                </ul>
            @endif
        </div></div>
    @empty
        <div class="alert alert-light border">لا توجد رسوم مسجَّلة عليك بعد.</div>
    @endforelse
</div>
@endsection
