@extends('layouts.app')
@section('title', 'الاختبارات')
@section('content')
<div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="mb-0">الاختبارات</h2>
        @can('create', \App\Models\Exam::class)
            <a href="{{ route('exams.create') }}" class="btn btn-primary"><i class="fas fa-plus me-1"></i> اختبار جديد</a>
        @endcan
    </div>

    <form method="GET" class="row g-2 mb-4">
        <div class="col-md-3"><select name="term" class="form-select">
            <option value="">كل الفصول</option>
            @foreach ($terms as $term)
                <option value="{{ $term->id }}" @selected(request('term') == $term->id)>{{ $term->academicYear->name ?? '' }} — {{ \App\Services\AcademicRecordService::semesterLabel($term->semester) }}</option>
            @endforeach
        </select></div>
        <div class="col-md-3"><select name="course" class="form-select">
            <option value="">كل المقررات</option>
            @foreach ($courses as $course)
                <option value="{{ $course->id }}" @selected(request('course') == $course->id)>{{ $course->code }} — {{ $course->name_ar }}</option>
            @endforeach
        </select></div>
        <div class="col-md-3"><select name="section" class="form-select">
            <option value="">كل الشعب</option>
            @foreach ($sections as $section)
                <option value="{{ $section->id }}" @selected(request('section') == $section->id)>{{ $section->course->name_ar }} — {{ $section->code }}</option>
            @endforeach
        </select></div>
        <div class="col-auto"><button class="btn btn-outline-secondary">تصفية</button></div>
    </form>

    <div class="card"><div class="card-body p-0"><div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead><tr><th>الاختبار</th><th>المقرر / الشعبة</th><th>النوع</th><th>التاريخ</th><th>الدرجة</th><th>الحالة</th><th></th></tr></thead>
            <tbody>
            @forelse ($exams as $exam)
                <tr>
                    <td class="fw-bold">{{ $exam->name }}</td>
                    <td>{{ $exam->section->course->name_ar }} — {{ $exam->section->code }}</td>
                    <td>{{ \App\Models\Exam::typeLabel($exam->type) }}</td>
                    <td>{{ $exam->exam_date->format('Y-m-d') }}</td>
                    <td>{{ $exam->max_score + 0 }} (مخصصة: {{ $exam->weight + 0 }})</td>
                    <td>@include('exams._status', ['status' => $exam->result_status])</td>
                    <td><a href="{{ route('exams.show', $exam) }}" class="btn btn-sm btn-outline-primary">فتح</a></td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted py-4">لا توجد اختبارات.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div></div></div>
    <div class="mt-3">{{ $exams->links() }}</div>
</div>
@endsection
