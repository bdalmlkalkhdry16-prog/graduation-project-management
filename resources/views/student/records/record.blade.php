@extends('layouts.app')
@section('title', 'السجل الأكاديمي')
@section('content')
<div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="mb-0">السجل الأكاديمي</h2>
        @empty($staffView)
            <a href="{{ route('student.transcript') }}" class="btn btn-outline-primary"><i class="fas fa-file-alt me-1"></i> كشف الدرجات / طباعة</a>
        @endempty
    </div>

    @include('student._withheld-notice', ['withheld' => $record['withheld_years'] ?? collect()])

    @if (! $record)
        <div class="alert alert-warning">لا يوجد ملف أكاديمي مرتبط بحسابك بعد.</div>
    @else
        <div class="card mb-3"><div class="card-body">@include('student.records._student-info')</div></div>
        <p class="small text-muted">يعرض هذا السجل النتائج المنشورة فقط.</p>

        @forelse ($record['terms'] as $term)
            <h5 class="mt-4 mb-3">{{ $term['label'] }}</h5>
            @foreach ($term['courses'] as $course)
                <div class="card mb-2"><div class="card-body">
                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
                        <div>
                            <div class="fw-bold">{{ $course['course']->code }} — {{ $course['course']->name_ar }}</div>
                            <div class="small text-muted">شعبة {{ $course['section']->code }} · الساعات المعتمدة: {{ $course['credit_hours'] ?? '—' }} · التسجيل: {{ ['active' => 'نشط', 'completed' => 'مكتمل', 'dropped' => 'منسحب (النتائج المنشورة محفوظة)'][$course['enrollment']->status] ?? $course['enrollment']->status }}</div>
                        </div>
                        <span class="badge bg-secondary">{{ $course['status_label'] }}</span>
                    </div>
                    @if ($course['exams'])
                        <table class="table table-sm mt-3 mb-2">
                            <tbody>
                            @foreach ($course['exams'] as $row)
                                <tr><td>{{ $row['exam']->name }} ({{ \App\Models\Exam::typeLabel($row['exam']->type) }})</td>
                                    <td>{{ $row['score'] + 0 }} / {{ $row['exam']->max_score + 0 }}</td>
                                    <td class="text-muted">المحتسب: {{ $row['contribution'] + 0 }}</td></tr>
                            @endforeach
                            </tbody>
                        </table>
                        <div>المجموع المحتسب: <strong>{{ $course['total'] + 0 }}</strong>
                            @if ($course['percentage'] !== null) · النسبة: <strong>{{ $course['percentage'] + 0 }}%</strong>@endif
                            @if ($course['letter']) · التقدير: <strong>{{ $course['letter'] }}</strong>@endif
                        </div>
                    @else
                        <p class="mt-2 mb-0 small text-muted">لا توجد نتائج منشورة لهذا المقرر.</p>
                    @endif
                </div></div>
            @endforeach
            @if ($term['gpa'] !== null)<div class="small">معدل الفصل: <strong>{{ $term['gpa'] }}</strong></div>@endif
        @empty
            <p class="text-muted">لا توجد مقررات مسجَّلة.</p>
        @endforelse

        @if ($record['cumulative_gpa'] !== null)
            <div class="alert alert-primary mt-4">المعدل التراكمي: <strong>{{ $record['cumulative_gpa'] }}</strong></div>
        @elseif (! $record['has_scale'])
            <p class="small text-muted mt-3">لم يُضبط سلم التقديرات في المؤسسة بعد، لذلك لا تُحسب معدلات.</p>
        @endif
    @endif
</div>
@endsection
