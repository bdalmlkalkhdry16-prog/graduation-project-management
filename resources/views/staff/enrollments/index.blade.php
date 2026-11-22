@extends('layouts.app')
@section('title', 'التسجيل في الشعب')
@section('content')
<div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="mb-0">التسجيل في الشعب الدراسية</h2>
        <a href="{{ route('staff.enrollments.students') }}" class="btn btn-outline-primary"><i class="fas fa-search me-1"></i> البحث عن طالب وسجله</a>
    </div>

    <form method="GET" class="row g-2 align-items-end mb-4">
        <div class="col-auto">
            <label for="term" class="form-label">الفصل الأكاديمي</label>
            <select id="term" name="term" onchange="this.form.submit()" class="form-select">
                @foreach ($terms as $term)
                    <option value="{{ $term->id }}" @selected($term->id == $termId)>@include('enrollments._term-label', ['term' => $term])</option>
                @endforeach
            </select>
        </div>
        <noscript><div class="col-auto"><button class="btn btn-primary">عرض</button></div></noscript>
    </form>

    <div class="row g-3">
        @forelse ($sections as $section)
            <div class="col-md-6 col-lg-4">
                <a href="{{ route('staff.enrollments.show', $section) }}" class="card h-100 text-decoration-none text-reset shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start gap-2">
                            <h5 class="card-title">{{ $section->course->name_ar }}</h5>
                            <span class="badge bg-primary">شعبة {{ $section->code }}</span>
                        </div>
                        <dl class="row small mb-0 mt-2">
                            <dt class="col-5 text-muted fw-normal">المدرس</dt><dd class="col-7 mb-1">{{ $section->faculty->user->name ?? '—' }}</dd>
                            <dt class="col-5 text-muted fw-normal">القاعة</dt><dd class="col-7 mb-1">{{ $section->schedules->map(fn ($s) => $s->room->name)->unique()->join('، ') ?: '—' }}</dd>
                            <dt class="col-5 text-muted fw-normal">المسجَّلون</dt><dd class="col-7 mb-0 fw-bold">{{ $section->active_count }}@if ($section->capacity) <span class="text-muted fw-normal">(السعة: {{ $section->capacity }})</span>@endif</dd>
                        </dl>
                    </div>
                </a>
            </div>
        @empty
            <div class="col-12 text-muted">لا توجد شعب في هذا الفصل.</div>
        @endforelse
    </div>
    <div class="mt-4">{{ $sections->links() }}</div>
</div>
@endsection
