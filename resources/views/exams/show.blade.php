@extends('layouts.app')
@section('title', $exam->name)
@section('content')
<div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h2 class="mb-0">{{ $exam->name }} @include('exams._status', ['status' => $exam->result_status])</h2>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('exams.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-right me-1"></i> الاختبارات</a>
            <a href="{{ route('exams.grades.edit', $exam) }}" class="btn btn-outline-primary">الدرجات</a>
            @if (! $exam->section->distributionLocked())
                @can('update', $exam)<a href="{{ route('exams.edit', $exam) }}" class="btn btn-outline-secondary">تعديل</a>@endcan
                @can('delete', $exam)
                    <form method="POST" action="{{ route('exams.destroy', $exam) }}" onsubmit="return confirm('حذف الاختبار؟')">@csrf @method('DELETE')
                        <button class="btn btn-outline-danger">حذف</button></form>
                @endcan
            @endif
        </div>
    </div>

    @include('layouts._errors')

    @if ($exam->result_status === 'rejected' && $exam->rejection_reason)
        <div class="alert alert-danger"><strong>سبب الرفض:</strong> {{ $exam->rejection_reason }}</div>
    @endif

    <div class="card mb-4"><div class="card-body">
        <div class="row g-3 small">
            <div class="col-md-4"><div class="text-muted">المقرر</div>{{ $exam->section->course->name_ar }}</div>
            <div class="col-md-4"><div class="text-muted">الشعبة</div>{{ $exam->section->code }}</div>
            <div class="col-md-4"><div class="text-muted">الفصل</div>{{ $exam->section->academicTerm->academicYear->name ?? '' }} — {{ \App\Services\AcademicRecordService::semesterLabel($exam->section->academicTerm->semester) }}</div>
            <div class="col-md-4"><div class="text-muted">المدرس</div>{{ $exam->section->faculty->user->name ?? '—' }}</div>
            <div class="col-md-4"><div class="text-muted">النوع</div>{{ \App\Models\Exam::typeLabel($exam->type) }}</div>
            <div class="col-md-4"><div class="text-muted">التاريخ والوقت</div>{{ $exam->exam_date->format('Y-m-d') }} {{ $exam->start_time ? substr($exam->start_time, 0, 5) : '' }}</div>
            <div class="col-md-4"><div class="text-muted">المدة</div>{{ $exam->duration_minutes }} دقيقة</div>
            <div class="col-md-4"><div class="text-muted">الدرجة النهائية / المخصصة</div>{{ $exam->max_score + 0 }} / {{ $exam->weight + 0 }}</div>
            <div class="col-md-4"><div class="text-muted">درجات مرصودة</div>{{ $gradesCount }}</div>
        </div>
    </div></div>

    @if ($exam->section->distributionLocked())
        <div class="alert alert-warning small">توزيع درجات هذه الشعبة مقفل بعد تقديم النتائج. أي تعديل يتم إداريًا بسبب مسجَّل وقيم سابقة وجديدة.</div>
        @can('adjustDistribution', \App\Models\Exam::class)
            <div class="card mb-4">
                <div class="card-header">تعديل إداري للتوزيع</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('exams.distribution.update', $exam) }}" class="row g-2">@csrf @method('PUT')
                        <div class="col-md"><input name="name" value="{{ $exam->name }}" required class="form-control form-control-sm"></div>
                        <div class="col-md"><select name="type" class="form-select form-select-sm">@foreach (\App\Models\Exam::TYPES as $t)<option value="{{ $t }}" @selected($exam->type === $t)>{{ \App\Models\Exam::typeLabel($t) }}</option>@endforeach</select></div>
                        <div class="col-md"><input name="max_score" type="number" step="0.01" value="{{ $exam->max_score + 0 }}" required class="form-control form-control-sm"></div>
                        <div class="col-md"><input name="weight" type="number" step="0.01" value="{{ $exam->weight + 0 }}" required class="form-control form-control-sm"></div>
                        <div class="col-md"><input name="reason" placeholder="السبب (إلزامي)" required class="form-control form-control-sm"></div>
                        <div class="col-12"><button class="btn btn-sm btn-outline-secondary">تسجيل التعديل الإداري</button></div>
                    </form>
                </div>
            </div>
        @endcan
    @endif

    <div class="card">
        <div class="card-header">دورة النتائج</div>
        <div class="card-body">
            <div class="d-flex flex-wrap align-items-start gap-2">
                @if ($exam->isEditable())
                    @can('submitResults', $exam)
                        <form method="POST" action="{{ route('exams.submit', $exam) }}">@csrf
                            <button class="btn btn-warning">تقديم للمراجعة</button></form>
                    @endcan
                @endif
                @if ($exam->result_status === 'submitted')
                    @can('reviewResults', $exam)
                        <form method="POST" action="{{ route('exams.approve', $exam) }}">@csrf
                            <button class="btn btn-info text-white">اعتماد</button></form>
                    @endcan
                @endif
                @if (in_array($exam->result_status, ['submitted', 'approved']))
                    @can('reviewResults', $exam)
                        <form method="POST" action="{{ route('exams.reject', $exam) }}" class="d-flex gap-2">@csrf
                            <input name="reason" placeholder="سبب الرفض" required class="form-control">
                            <button class="btn btn-outline-danger text-nowrap">رفض</button></form>
                    @endcan
                @endif
                @if ($exam->result_status === 'approved')
                    @can('publishResults', $exam)
                        <form method="POST" action="{{ route('exams.publish', $exam) }}">@csrf
                            <button class="btn btn-success"><i class="fas fa-bullhorn me-1"></i> نشر للطلاب</button></form>
                    @endcan
                @endif
            </div>
            <ul class="small text-muted mt-3 mb-0 list-unstyled">
                @if ($exam->submitted_at)<li>قُدِّمت: {{ $exam->submitted_at->format('Y-m-d H:i') }}</li>@endif
                @if ($exam->approved_at)<li>اعتُمدت: {{ $exam->approved_at->format('Y-m-d H:i') }}</li>@endif
                @if ($exam->published_at)<li>نُشرت: {{ $exam->published_at->format('Y-m-d H:i') }}</li>@endif
            </ul>
        </div>
    </div>
</div>
@endsection
