@extends('layouts.app')
@section('title', 'كشف الدرجات')
@section('content')
<div class="container-fluid">
    <div class="d-flex flex-wrap gap-2 mb-4 d-print-none">
        <button onclick="window.print()" class="btn btn-primary"><i class="fas fa-print me-1"></i> طباعة</button>
        <a href="{{ route('student.transcript.pdf') }}" class="btn btn-outline-secondary"><i class="fas fa-file-pdf me-1"></i> تصدير PDF</a>
        <a href="{{ route('student.academic-record') }}" class="btn btn-outline-secondary">السجل الأكاديمي الكامل</a>
    </div>

    @include('student._withheld-notice', ['withheld' => $record['withheld_years'] ?? collect()])

    @if (! $record)
        <div class="alert alert-warning">لا يوجد ملف أكاديمي مرتبط بحسابك بعد.</div>
    @else
        <div class="card"><div class="card-body p-4">
            <header class="text-center border-bottom pb-3 mb-4">
                <h3>{{ $institution }}</h3>
                <h5>كشف الدرجات</h5>
                <div class="small text-muted">تاريخ الإصدار: {{ $record['issued_at']->format('Y-m-d') }}</div>
            </header>
            <div class="mb-4">@include('student.records._student-info')</div>

            @forelse ($record['terms'] as $term)
                <h6 class="mt-4 mb-2">{{ $term['label'] }}</h6>
                <div class="table-responsive">
                <table class="table table-bordered table-sm text-center mb-1">
                    <thead class="table-light"><tr>
                        <th class="text-end">رمز المقرر</th><th class="text-end">اسم المقرر</th><th>الساعات</th><th>المجموع</th><th>النسبة</th><th>التقدير</th><th>الحالة</th>
                    </tr></thead>
                    <tbody>
                    @foreach ($term['courses'] as $c)
                        <tr>
                            <td class="text-end">{{ $c['course']->code }}</td>
                            <td class="text-end">{{ $c['course']->name_ar }}</td>
                            <td>{{ $c['credit_hours'] ?? '—' }}</td>
                            <td>{{ $c['complete'] ? $c['total'] + 0 : '—' }}</td>
                            <td>{{ $c['percentage'] !== null ? ($c['percentage'] + 0).'%' : '—' }}</td>
                            <td>{{ $c['letter'] ?? '—' }}</td>
                            <td>{{ $c['status_label'] }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                </div>
                @if ($term['gpa'] !== null)<div class="small">معدل الفصل: <strong>{{ $term['gpa'] }}</strong></div>@endif
            @empty
                <p class="text-muted">لا توجد مقررات.</p>
            @endforelse

            @if ($record['cumulative_gpa'] !== null)<div class="mt-4">المعدل التراكمي: <strong>{{ $record['cumulative_gpa'] }}</strong></div>@endif
            <div class="mt-4 small text-muted">يتضمن هذا الكشف النتائج المنشورة والمعتمدة فقط.</div>
        </div></div>
    @endif
</div>
@endsection
