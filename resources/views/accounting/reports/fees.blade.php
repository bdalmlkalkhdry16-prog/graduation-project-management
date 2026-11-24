@extends('layouts.app')
@section('title', 'تقارير الرسوم')
@section('content')
<div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h2 class="mb-0">تقارير الرسوم</h2>
        <div class="d-flex gap-2">
            <a href="{{ route('accounting.reports.fees.export', request()->query()) }}" class="btn btn-success"><i class="fas fa-file-excel me-1"></i> تصدير الملخص</a>
            <a href="{{ route('accounting.reports.fees.export', array_merge(request()->query(), ['view' => 'delinquent'])) }}" class="btn btn-outline-success"><i class="fas fa-file-excel me-1"></i> تصدير المتأخرين</a>
        </div>
    </div>

    <form method="GET" class="row g-2 mb-4">
        <div class="col-md-3"><select name="academic_year_id" class="form-select"><option value="">كل السنوات</option>
            @foreach ($years as $y)<option value="{{ $y->id }}" @selected((string) request('academic_year_id') === (string) $y->id)>{{ $y->name }}</option>@endforeach</select></div>
        <div class="col-md-3"><select name="department_id" class="form-select"><option value="">كل الأقسام</option>
            @foreach ($departments as $d)<option value="{{ $d->id }}" @selected((string) request('department_id') === (string) $d->id)>{{ $d->name_ar }}</option>@endforeach</select></div>
        <div class="col-auto"><button class="btn btn-outline-secondary">تطبيق</button></div>
    </form>

    <p class="small text-muted">تُجمَّع الأرقام لكل عملة على حدة ولا تُحوَّل بين الريال اليمني والدولار.</p>

    <div class="row g-3 mb-4">
        @forelse ($totals as $currency => $t)
            <div class="col-lg-6"><div class="card"><div class="card-header">إجمالي ({{ \App\Models\FeeSchedule::currencyLabel($currency) }})</div>
                <div class="card-body"><div class="row text-center">
                    <div class="col"><div class="text-muted small">الطلاب</div><div class="h5">{{ $t['students'] }}</div></div>
                    <div class="col"><div class="text-muted small">الصافي</div><div class="h5">{{ number_format($t['net'], 2) }}</div></div>
                    <div class="col"><div class="text-muted small">المدفوع</div><div class="h5 text-success">{{ number_format($t['paid'], 2) }}</div></div>
                    <div class="col"><div class="text-muted small">المتبقي</div><div class="h5 text-danger">{{ number_format($t['remaining'], 2) }}</div></div>
                </div></div></div></div>
        @empty
            <div class="col-12"><div class="alert alert-light border">لا توجد رسوم مولَّدة بعد.</div></div>
        @endforelse
    </div>

    <h5>الملخص حسب السنة والقسم</h5>
    <div class="card mb-4"><div class="card-body p-0"><div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead><tr><th>السنة</th><th>القسم</th><th>العملة</th><th>الطلاب</th><th>مسددون</th><th>غير مسددين</th><th>المستحق</th><th>الخصومات</th><th>المدفوع</th><th>المتبقي</th><th>التحصيل</th></tr></thead>
            <tbody>
            @forelse ($summary as $r)
                <tr>
                    <td>{{ $r['year'] }}</td><td>{{ $r['department'] }}</td><td>{{ $r['currency'] }}</td>
                    <td>{{ $r['students'] }}</td><td>{{ $r['settled'] }}</td><td>{{ $r['unsettled'] }}</td>
                    <td>{{ number_format($r['gross'], 2) }}</td><td>{{ number_format($r['discounts'], 2) }}</td>
                    <td>{{ number_format($r['paid'], 2) }}</td><td>{{ number_format($r['remaining'], 2) }}</td>
                    <td>{{ $r['collection_rate'] !== null ? $r['collection_rate'].'%' : '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="11" class="text-center text-muted py-4">لا بيانات.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div></div></div>

    <h5>غير المسددين ({{ $delinquent->count() }})</h5>
    <div class="card"><div class="card-body p-0"><div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead><tr><th>الطالب</th><th>السنة</th><th>القسم</th><th>الصافي</th><th>المدفوع</th><th>المتبقي</th><th>الحجب</th><th></th></tr></thead>
            <tbody>
            @forelse ($delinquent as $r)
                <tr>
                    <td>{{ $r['student'] }} <small class="text-muted">({{ $r['number'] }})</small></td>
                    <td>{{ $r['year'] }}</td><td>{{ $r['department'] }}</td>
                    <td>{{ number_format($r['net'], 2) }} {{ $r['currency'] }}</td><td>{{ number_format($r['paid'], 2) }}</td>
                    <td class="fw-bold text-danger">{{ number_format($r['remaining'], 2) }}</td>
                    <td>@if ($r['released'])<span class="badge bg-info text-dark">مرفوع استثناءً</span>@else<span class="badge bg-warning text-dark">محجوبة</span>@endif</td>
                    <td><a href="{{ route('accounting.fees.show', $r['fee']) }}" class="btn btn-sm btn-outline-primary">فتح</a></td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center text-muted py-4">لا يوجد متأخرون.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div></div></div>
</div>
@endsection
