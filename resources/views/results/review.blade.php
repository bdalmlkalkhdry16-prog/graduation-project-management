@extends('layouts.app')
@section('title', 'مراجعة واعتماد النتائج')
@section('content')
<div class="container-fluid">
    <h2 class="mb-4">مراجعة واعتماد النتائج</h2>
    <div class="card"><div class="card-body p-0"><div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead><tr><th>الاختبار</th><th>المقرر / الشعبة</th><th>المدرس</th><th>الحالة</th><th></th></tr></thead>
            <tbody>
            @forelse ($exams as $exam)
                <tr>
                    <td class="fw-bold">{{ $exam->name }}</td>
                    <td>{{ $exam->section->course->name_ar }} — {{ $exam->section->code }}</td>
                    <td>{{ $exam->section->faculty->user->name ?? '—' }}</td>
                    <td>@include('exams._status', ['status' => $exam->result_status])</td>
                    <td class="text-nowrap">
                        <a href="{{ route('exams.grades.edit', $exam) }}" class="btn btn-sm btn-outline-secondary">الدرجات</a>
                        <a href="{{ route('exams.show', $exam) }}" class="btn btn-sm btn-outline-primary">مراجعة</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted py-4">لا توجد نتائج بانتظار المراجعة.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div></div></div>
</div>
@endsection
