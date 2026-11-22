@extends('layouts.app')
@section('title', 'طلاب شعبي')
@section('content')
<div class="container-fluid">
    <h2 class="mb-4">شعبي الدراسية</h2>
    <div class="row g-3">
        @forelse ($sections as $section)
            <div class="col-md-6 col-lg-4">
                <a href="{{ route('faculty.enrollments.show', $section) }}" class="card h-100 text-decoration-none text-reset shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">{{ $section->course->name_ar }}</h5>
                        <p class="text-muted small mb-2">شعبة {{ $section->code }} · @include('enrollments._term-label', ['term' => $section->academicTerm])</p>
                        <p class="mb-0">الطلاب المسجَّلون: <span class="fw-bold">{{ $section->active_count }}</span></p>
                    </div>
                </a>
            </div>
        @empty
            <div class="col-12 text-muted">لا توجد شعب مسندة إليك.</div>
        @endforelse
    </div>
</div>
@endsection
