@extends('layouts.app')
@section('title', 'نشر النتائج')
@section('content')
<div class="container-fluid">
    <h2 class="mb-4">نشر النتائج المعتمدة</h2>
    @forelse ($exams as $exam)
        <div class="card mb-2"><div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <div class="fw-bold">{{ $exam->name }} — {{ $exam->section->course->name_ar }} ({{ $exam->section->code }})</div>
                <div class="small text-muted">اعتُمدت: {{ $exam->approved_at?->format('Y-m-d H:i') }}</div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('exams.grades.edit', $exam) }}" class="btn btn-sm btn-outline-primary">عرض الدرجات</a>
                <form method="POST" action="{{ route('exams.publish', $exam) }}" onsubmit="return confirm('نشر النتائج للطلاب؟')">@csrf
                    <button class="btn btn-sm btn-success"><i class="fas fa-bullhorn me-1"></i> نشر</button></form>
            </div>
        </div></div>
    @empty
        <div class="alert alert-light border">لا توجد نتائج معتمدة بانتظار النشر.</div>
    @endforelse
</div>
@endsection
