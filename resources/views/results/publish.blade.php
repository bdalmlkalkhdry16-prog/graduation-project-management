@extends('layouts.tailwind')
@section('title', 'نشر النتائج')
@section('content')
<h1 class="text-2xl font-bold mb-6">نشر النتائج المعتمدة</h1>
<div class="space-y-3">
    @forelse ($exams as $exam)
        <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-slate-200 bg-white p-4">
            <div>
                <div class="font-semibold">{{ $exam->name }} — {{ $exam->section->course->name_ar }} ({{ $exam->section->code }})</div>
                <div class="text-xs text-slate-500">اعتُمدت: {{ $exam->approved_at?->format('Y-m-d H:i') }}</div>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('exams.grades.edit', $exam) }}" class="text-sm text-indigo-700 hover:underline">عرض الدرجات</a>
                <form method="POST" action="{{ route('exams.publish', $exam) }}" onsubmit="return confirm('نشر النتائج للطلاب؟')">@csrf
                    <button class="rounded-lg bg-emerald-600 px-4 py-2 text-sm text-white hover:bg-emerald-700">نشر</button></form>
            </div>
        </div>
    @empty
        <p class="text-slate-500">لا توجد نتائج معتمدة بانتظار النشر.</p>
    @endforelse
</div>
@endsection
