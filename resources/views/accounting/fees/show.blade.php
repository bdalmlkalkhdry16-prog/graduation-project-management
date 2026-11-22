@extends('layouts.app')
@section('title', 'رسوم طالب')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="mb-0">{{ $fee->studentProfile->user->name }}</h2>
            <div class="text-muted small">رقم القيد {{ $fee->studentProfile->number_student }} — السنة {{ $fee->academicYear->name }}</div>
        </div>
        <a href="{{ route('accounting.fees.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-right me-1"></i> رسوم الطلاب</a>
    </div>

    @include('layouts._errors')

    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3"><div class="card"><div class="card-body"><div class="text-muted small">المستحق</div><div class="h5 mb-0">{{ number_format($fee->amount, 2) }} {{ $fee->currency }}</div></div></div></div>
        <div class="col-6 col-lg-3"><div class="card"><div class="card-body"><div class="text-muted small">الخصومات</div><div class="h5 mb-0">{{ number_format($fee->discount(), 2) }}</div></div></div></div>
        <div class="col-6 col-lg-3"><div class="card"><div class="card-body"><div class="text-muted small">المدفوع</div><div class="h5 mb-0">{{ number_format($fee->paid(), 2) }}</div></div></div></div>
        <div class="col-6 col-lg-3"><div class="card"><div class="card-body"><div class="text-muted small">المتبقي</div><div class="h5 mb-0">{{ number_format($fee->remaining(), 2) }}</div></div></div></div>
    </div>

    @can('recordPayment', \App\Models\StudentFee::class)
        @unless ($fee->isSettled())
        <form method="POST" action="{{ route('accounting.payments.store', $fee) }}" class="card card-body mb-4">
            @csrf
            <div class="row g-2 align-items-end">
                <div class="col-md-3"><label class="form-label">مبلغ الدفعة ({{ $fee->currency }})</label><input type="number" step="0.01" min="0.01" name="amount" required class="form-control"></div>
                <div class="col-md-3"><label class="form-label">تاريخ الدفع</label><input type="date" name="paid_at" value="{{ now()->toDateString() }}" max="{{ now()->toDateString() }}" required class="form-control"></div>
                <div class="col-md"><label class="form-label">ملاحظات</label><input name="notes" class="form-control"></div>
                <div class="col-auto"><button class="btn btn-primary">تسجيل الدفعة</button></div>
            </div>
        </form>
        @endunless
    @endcan

    @if ($fee->discounts->isNotEmpty() || auth()->user()->can('discount', \App\Models\StudentFee::class))
    <div class="card mb-4">
        <div class="card-header">الخصومات والإعفاءات</div>
        <div class="card-body">
            <ul class="list-unstyled mb-0">
                @foreach ($fee->discounts as $d)
                    <li class="mb-2 @if ($d->isRevoked()) text-muted text-decoration-line-through @endif">
                        {{ number_format($d->amount, 2) }} {{ $fee->currency }} ({{ $d->type === 'percent' ? $d->value.'%' : 'مبلغ' }}) — {{ $d->reason }}@if ($d->isRevoked()) <span class="text-danger d-inline-block text-decoration-none">(ملغى: {{ $d->revoke_reason }})</span>@endif
                        @can('discount', \App\Models\StudentFee::class)
                            @unless ($d->isRevoked())
                                <form method="POST" action="{{ route('accounting.discounts.revoke', $d) }}" class="d-inline-flex gap-1 ms-2">@csrf
                                    <input name="reason" required minlength="5" placeholder="سبب الإلغاء" class="form-control form-control-sm">
                                    <button class="btn btn-sm btn-danger text-nowrap">إلغاء الخصم</button>
                                </form>
                            @endunless
                        @endcan
                    </li>
                @endforeach
            </ul>
            @can('discount', \App\Models\StudentFee::class)
                <form method="POST" action="{{ route('accounting.discounts.store', $fee) }}" class="row g-2 align-items-end mt-2">@csrf
                    <div class="col-md-2"><label class="form-label">النوع</label><select name="type" class="form-select"><option value="amount">مبلغ</option><option value="percent">نسبة %</option></select></div>
                    <div class="col-md-2"><label class="form-label">القيمة</label><input type="number" step="0.01" min="0.01" name="value" required class="form-control"></div>
                    <div class="col-md"><label class="form-label">السبب (منحة، إعفاء...)</label><input name="reason" required minlength="5" class="form-control"></div>
                    <div class="col-auto"><button class="btn btn-primary">إضافة خصم</button></div>
                </form>
            @endcan
        </div>
    </div>
    @endif

    @can('releaseResults', \App\Models\StudentFee::class)
        <form method="POST" action="{{ route('accounting.fees.release-results', $fee) }}" class="alert alert-warning">
            @csrf
            @if ($fee->resultsReleased())
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <span>رُفع الحجب استثناءً بتاريخ {{ $fee->results_released_at->format('Y-m-d') }} — السبب: {{ $fee->results_release_reason }}</span>
                    <input type="hidden" name="release" value="0">
                    <button class="btn btn-secondary">إعادة الحجب</button>
                </div>
            @else
                <div class="row g-2 align-items-end">
                    <div class="col-md"><label class="form-label">استثناء إداري: رفع حجب النتائج رغم عدم السداد (سبب مطلوب)</label><input name="reason" required minlength="5" class="form-control"></div>
                    <input type="hidden" name="release" value="1">
                    <div class="col-auto"><button class="btn btn-warning">رفع الحجب</button></div>
                </div>
            @endif
        </form>
    @endcan

    <h5 class="mb-2">الدفعات</h5>
    <div class="card"><div class="card-body p-0"><div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead><tr><th>السند</th><th>التاريخ</th><th>المبلغ</th><th>سجّلها</th><th></th></tr></thead>
            <tbody>
            @forelse ($fee->payments as $p)
                <tr @class(['table-secondary text-decoration-line-through' => $p->isVoided()])>
                    <td>{{ $p->receipt_number }}@if ($p->isVoided()) <span class="text-danger d-inline-block text-decoration-none">(ملغاة: {{ $p->void_reason }})</span>@endif</td>
                    <td>{{ $p->paid_at->format('Y-m-d') }}</td>
                    <td>{{ number_format($p->amount, 2) }} {{ $fee->currency }}</td>
                    <td>{{ $p->recordedBy?->name }}</td>
                    <td class="text-nowrap">
                        <a href="{{ route('payments.receipt', $p) }}" class="btn btn-sm btn-outline-primary">سند القبض</a>
                        @can('voidPayment', \App\Models\StudentFee::class)
                            @unless ($p->isVoided())
                                <form method="POST" action="{{ route('payments.void', $p) }}" class="d-inline-flex gap-1 ms-1">@csrf
                                    <input name="reason" required minlength="5" placeholder="سبب الإلغاء" class="form-control form-control-sm">
                                    <button class="btn btn-sm btn-danger">إلغاء</button>
                                </form>
                            @endunless
                        @endcan
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted py-4">لا توجد دفعات.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div></div></div>
</div>
@endsection
