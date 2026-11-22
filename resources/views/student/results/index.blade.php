@extends('layouts.app')
@section('title', 'نتائجي')
@section('content')
<div class="container-fluid">
    <h2 class="mb-4">نتائجي المنشورة</h2>
    @include('student._withheld-notice')
    @if (! $profile)
        <div class="alert alert-warning">لا يوجد ملف أكاديمي مرتبط بحسابك بعد.</div>
    @else
        <div class="card"><div class="card-body p-0"><div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th>المقرر</th><th>الاختبار</th><th>النوع</th><th>الدرجة</th><th>تاريخ النشر</th></tr></thead>
                <tbody>
                @forelse ($grades as $grade)
                    <tr>
                        <td>{{ $grade->exam->section->course->name_ar }}</td>
                        <td>{{ $grade->exam->name }}</td>
                        <td>{{ \App\Models\Exam::typeLabel($grade->exam->type) }}</td>
                        <td class="fw-bold">{{ $grade->score + 0 }} / {{ $grade->exam->max_score + 0 }}</td>
                        <td>{{ $grade->exam->published_at?->format('Y-m-d') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">لا توجد نتائج منشورة بعد.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div></div></div>
    @endif
</div>
@endsection
