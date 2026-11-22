@extends('layouts.app')
@section('title', 'تسجيلاتي')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">تسجيلاتي النشطة</h2>
        <a href="{{ route('student.schedule.index') }}" class="btn btn-outline-primary"><i class="fas fa-calendar-week me-1"></i> جدولي الدراسي</a>
    </div>
    @if (! $profile)
        <div class="alert alert-warning">لا يوجد ملف أكاديمي مرتبط بحسابك بعد.</div>
    @else
        <div class="row g-3">
            @forelse ($enrollments as $enrollment)
                <div class="col-md-6">
                    <div class="card h-100 shadow-sm"><div class="card-body">
                        <h5 class="card-title">{{ $enrollment->section->course->name_ar }}</h5>
                        <p class="text-muted small">شعبة {{ $enrollment->section->code }} · @include('enrollments._term-label', ['term' => $enrollment->section->academicTerm])</p>
                        <p class="mb-1">المدرس: {{ $enrollment->section->faculty->user->name ?? '—' }}</p>
                        <p class="mb-0">القاعة: {{ $enrollment->section->schedules->map(fn ($s) => $s->room->name)->unique()->join('، ') ?: '—' }}</p>
                    </div></div>
                </div>
            @empty
                <div class="col-12 text-muted">لا توجد تسجيلات نشطة حاليًا.</div>
            @endforelse
        </div>
    @endif
</div>
@endsection
