@extends('layouts.app')
@section('title', 'سجل تسجيلات '.$studentProfile->user->name)
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="mb-0">{{ $studentProfile->user->name }}</h2>
            <div class="text-muted small">{{ $studentProfile->number_student }} · {{ $studentProfile->currentLevel->name ?? 'بلا مستوى' }}</div>
        </div>
        <a href="{{ route('staff.enrollments.students') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-right me-1"></i> بحث الطلاب</a>
    </div>
    <div class="card"><div class="card-body p-0"><div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead><tr><th>المقرر</th><th>الشعبة</th><th>الفصل</th><th>المدرس</th><th>تاريخ التسجيل</th><th>الحالة</th></tr></thead>
            <tbody>
            @forelse ($enrollments as $enrollment)
                <tr>
                    <td>{{ $enrollment->section->course->name_ar }}</td>
                    <td><a href="{{ route('staff.enrollments.show', $enrollment->section) }}">{{ $enrollment->section->code }}</a></td>
                    <td>@include('enrollments._term-label', ['term' => $enrollment->section->academicTerm])</td>
                    <td>{{ $enrollment->section->faculty->user->name ?? '—' }}</td>
                    <td>{{ $enrollment->enrolled_at?->format('Y-m-d') ?? '—' }}</td>
                    <td>@include('enrollments._status-badge', ['status' => $enrollment->status])</td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-4">لا توجد تسجيلات.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div></div></div>
</div>
@endsection
