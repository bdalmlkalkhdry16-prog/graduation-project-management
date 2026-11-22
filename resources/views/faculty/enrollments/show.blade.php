@extends('layouts.app')
@section('title', 'طلاب '.$section->course->name_ar)
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="mb-0">{{ $section->course->name_ar }} <small class="text-muted">— شعبة {{ $section->code }}</small></h2>
            <div class="text-muted small">الطلاب المسجَّلون فعليًا: {{ $students->count() }}</div>
        </div>
        <a href="{{ route('faculty.enrollments.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-right me-1"></i> شعبي</a>
    </div>
    <div class="card"><div class="card-body p-0"><div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead><tr><th>الرقم الأكاديمي</th><th>الاسم</th><th>المستوى</th></tr></thead>
            <tbody>
            @forelse ($students as $student)
                <tr><td>{{ $student->number_student }}</td><td>{{ $student->user->name }}</td><td>{{ $student->currentLevel->name ?? '—' }}</td></tr>
            @empty
                <tr><td colspan="3" class="text-center text-muted py-4">لا يوجد طلاب مسجَّلون.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div></div></div>
</div>
@endsection
