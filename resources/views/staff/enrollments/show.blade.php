@extends('layouts.app')
@section('title', 'شعبة '.$section->course->name_ar)
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">{{ $section->course->name_ar }} <small class="text-muted">— شعبة {{ $section->code }}</small></h2>
        <a href="{{ route('staff.enrollments.index', ['term' => $section->academic_term_id]) }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-right me-1"></i> كل الشعب</a>
    </div>

    <div class="card mb-4"><div class="card-body">
        <div class="row g-3 small">
            <div class="col-md-4"><div class="text-muted">الفصل</div>@include('enrollments._term-label', ['term' => $section->academicTerm])</div>
            <div class="col-md-4"><div class="text-muted">البرنامج</div>{{ $levels->map(fn ($l) => $l->program->specialization->name_ar ?? '')->filter()->unique()->join('، ') ?: '—' }}</div>
            <div class="col-md-4"><div class="text-muted">المستوى</div>{{ $levels->pluck('name')->join('، ') ?: '—' }}</div>
            <div class="col-md-4"><div class="text-muted">المدرس</div>{{ $section->faculty->user->name ?? '—' }}</div>
            <div class="col-md-4"><div class="text-muted">القاعة</div>{{ $section->schedules->map(fn ($s) => $s->room->name)->unique()->join('، ') ?: '—' }}</div>
            <div class="col-md-4"><div class="text-muted">عدد الطلاب النشطين</div><strong>{{ $activeCount }}</strong>@if ($section->capacity) <span class="text-muted">(السعة: {{ $section->capacity }})</span>@endif</div>
        </div>
    </div></div>

    <form method="GET" class="input-group mb-4">
        <input type="search" name="q" value="{{ request('q') }}" placeholder="بحث بالاسم أو الرقم الأكاديمي" class="form-control">
        <button class="btn btn-primary"><i class="fas fa-search me-1"></i> بحث</button>
    </form>

    <div class="row g-4">
        <div class="col-lg-6">
            <h5 class="mb-3">الطلاب المسجَّلون ({{ $enrollments->count() }})</h5>
            @forelse ($enrollments as $enrollment)
                <div class="card mb-2"><div class="card-body">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <div>
                            <a href="{{ route('staff.enrollments.history', $enrollment->studentProfile) }}" class="fw-bold text-decoration-none">{{ $enrollment->studentProfile->user->name }}</a>
                            <div class="small text-muted">{{ $enrollment->studentProfile->number_student }}</div>
                        </div>
                        @include('enrollments._status-badge', ['status' => $enrollment->status])
                    </div>
                    <form method="POST" action="{{ route('staff.enrollments.status', $enrollment) }}" class="d-flex gap-2 mt-3">
                        @csrf @method('PATCH')
                        <select name="status" class="form-select form-select-sm w-auto">
                            <option value="active" @selected($enrollment->status === 'active')>نشط</option>
                            <option value="dropped" @selected($enrollment->status === 'dropped')>منسحب</option>
                            <option value="completed" @selected($enrollment->status === 'completed')>مكتمل</option>
                        </select>
                        <button class="btn btn-sm btn-outline-secondary">تحديث</button>
                    </form>
                </div></div>
            @empty
                <p class="text-muted">لا يوجد طلاب مسجَّلون.</p>
            @endforelse
        </div>

        <div class="col-lg-6">
            <h5 class="mb-1">طلاب مؤهَّلون للتسجيل ({{ $eligible->count() }})</h5>
            <p class="small text-muted mb-3">حسب البرنامج والمستوى المرتبطين بمقرر هذه الشعبة في الخطة الدراسية.</p>
            @forelse ($eligible as $student)
                <form method="POST" action="{{ route('staff.enrollments.store', $section) }}" class="card mb-2">
                    @csrf
                    <input type="hidden" name="student_profile_id" value="{{ $student->id }}">
                    <div class="card-body d-flex justify-content-between align-items-center gap-2">
                        <div>
                            <div class="fw-bold">{{ $student->user->name }}</div>
                            <div class="small text-muted">{{ $student->number_student }} · {{ $student->currentLevel->name ?? '' }}</div>
                        </div>
                        <button class="btn btn-sm btn-success">تسجيل</button>
                    </div>
                </form>
            @empty
                <p class="text-muted">لا يوجد طلاب مؤهَّلون غير مسجَّلين.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
