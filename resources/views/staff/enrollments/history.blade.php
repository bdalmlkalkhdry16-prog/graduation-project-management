@extends('layouts.tailwind')
@section('title', 'سجل تسجيلات '.$studentProfile->user->name)
@section('content')
<a href="{{ route('staff.enrollments.students') }}" class="text-sm text-indigo-700 hover:underline">&rarr; بحث الطلاب</a>
<h1 class="mt-3 text-2xl font-bold">{{ $studentProfile->user->name }}</h1>
<p class="text-sm text-slate-500 mb-6">{{ $studentProfile->number_student }} · {{ $studentProfile->currentLevel->name ?? 'بلا مستوى' }}</p>
<div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
    <table class="min-w-full text-sm">
        <thead class="bg-slate-50 text-slate-600"><tr>
            <th class="px-4 py-3 text-right">المقرر</th><th class="px-4 py-3 text-right">الشعبة</th>
            <th class="px-4 py-3 text-right">الفصل</th><th class="px-4 py-3 text-right">المدرس</th>
            <th class="px-4 py-3 text-right">تاريخ التسجيل</th><th class="px-4 py-3 text-right">الحالة</th>
        </tr></thead>
        <tbody class="divide-y divide-slate-100">
        @forelse ($enrollments as $enrollment)
            <tr>
                <td class="px-4 py-3">{{ $enrollment->section->course->name_ar }}</td>
                <td class="px-4 py-3"><a class="text-indigo-700 hover:underline" href="{{ route('staff.enrollments.show', $enrollment->section) }}">{{ $enrollment->section->code }}</a></td>
                <td class="px-4 py-3">@include('enrollments._term-label', ['term' => $enrollment->section->academicTerm])</td>
                <td class="px-4 py-3">{{ $enrollment->section->faculty->user->name ?? '—' }}</td>
                <td class="px-4 py-3">{{ $enrollment->enrolled_at?->format('Y-m-d') ?? '—' }}</td>
                <td class="px-4 py-3">@include('enrollments._status-badge', ['status' => $enrollment->status])</td>
            </tr>
        @empty
            <tr><td colspan="6" class="px-4 py-6 text-center text-slate-500">لا توجد تسجيلات.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
