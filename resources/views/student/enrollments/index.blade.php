@extends('layouts.tailwind')
@section('title', 'تسجيلاتي')
@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold">تسجيلاتي النشطة</h1>
    <a href="{{ route('student.schedule.index') }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm hover:bg-slate-100">جدولي الدراسي</a>
</div>
@if (! $profile)
    <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-amber-800">لا يوجد ملف أكاديمي مرتبط بحسابك بعد.</div>
@else
    <div class="grid gap-4 md:grid-cols-2">
        @forelse ($enrollments as $enrollment)
            <div class="rounded-xl border border-slate-200 bg-white p-5">
                <h2 class="font-bold text-lg">{{ $enrollment->section->course->name_ar }}</h2>
                <p class="text-sm text-slate-500">شعبة {{ $enrollment->section->code }} · @include('enrollments._term-label', ['term' => $enrollment->section->academicTerm])</p>
                <p class="mt-2 text-sm">المدرس: {{ $enrollment->section->faculty->user->name ?? '—' }}</p>
                <p class="text-sm">القاعة: {{ $enrollment->section->schedules->map(fn ($s) => $s->room->name)->unique()->join('، ') ?: '—' }}</p>
            </div>
        @empty
            <p class="text-slate-500">لا توجد تسجيلات نشطة حاليًا.</p>
        @endforelse
    </div>
@endif
@endsection
