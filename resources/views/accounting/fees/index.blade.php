@extends('layouts.app')
@section('title', 'رسوم الطلاب')
@section('content')
<div class="container-fluid">
    <h2 class="mb-4">رسوم الطلاب</h2>
    @include('layouts._errors')

    @can('generate', \App\Models\StudentFee::class)
    <form method="POST" action="{{ route('accounting.fees.generate') }}" class="card card-body mb-4">
        @csrf
        <div class="row g-2 align-items-end">
            <div class="col-md-3"><label class="form-label">السنة الدراسية</label>
                <select name="academic_year_id" required class="form-select">
                    <option value="">— اختر —</option>@foreach ($years as $y)<option value="{{ $y->id }}">{{ $y->name }}</option>@endforeach
                </select></div>
            <div class="col-md-3"><label class="form-label">القسم (اختياري)</label>
                <select name="department_id" class="form-select">
                    <option value="">كل الأقسام</option>@foreach ($departments as $d)<option value="{{ $d->id }}">{{ $d->name_ar }}</option>@endforeach
                </select></div>
            <div class="col-auto"><button class="btn btn-primary">توليد رسوم الطلاب النشطين</button></div>
            <div class="col-12 small text-muted">لا يكرر المولَّد سابقًا، ولا يُنشئ رسمًا لقسم بلا سعر.</div>
        </div>
    </form>
    @endcan

    <form method="GET" class="row g-2 mb-3">
        <div class="col-md-3"><select name="academic_year_id" class="form-select"><option value="">كل السنوات</option>
            @foreach ($years as $y)<option value="{{ $y->id }}" @selected((string) request('academic_year_id') === (string) $y->id)>{{ $y->name }}</option>@endforeach</select></div>
        <div class="col-md-3"><select name="status" class="form-select">
            <option value="">كل الحالات</option>
            <option value="unsettled" @selected(request('status') === 'unsettled')>غير مسددة</option>
            <option value="settled" @selected(request('status') === 'settled')>مسددة</option></select></div>
        <div class="col-md-3"><input name="q" value="{{ request('q') }}" placeholder="رقم قيد الطالب" class="form-control"></div>
        <div class="col-auto"><button class="btn btn-outline-secondary">تصفية</button></div>
    </form>

    <div class="card"><div class="card-body p-0"><div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead><tr><th>الطالب</th><th>السنة</th><th>المستحق</th><th>المدفوع</th><th>المتبقي</th><th>الحالة</th><th></th></tr></thead>
            <tbody>
            @forelse ($fees as $f)
                <tr>
                    <td>{{ $f->studentProfile->user->name }} <small class="text-muted">({{ $f->studentProfile->number_student }})</small></td>
                    <td>{{ $f->academicYear->name }}</td>
                    <td>{{ number_format($f->amount, 2) }} {{ $f->currency }}</td>
                    <td>{{ number_format($f->paid(), 2) }}</td>
                    <td>{{ number_format($f->remaining(), 2) }}</td>
                    <td>@if ($f->isSettled())<span class="badge bg-success">مسددة</span>@else<span class="badge bg-warning text-dark">غير مسددة</span>@endif</td>
                    <td><a href="{{ route('accounting.fees.show', $f) }}" class="btn btn-sm btn-outline-primary">فتح</a></td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted py-4">لا توجد رسوم.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div></div></div>
</div>
@endsection
