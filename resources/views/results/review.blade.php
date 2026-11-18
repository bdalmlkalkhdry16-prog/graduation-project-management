@extends('layouts.tailwind')
@section('title', 'مراجعة واعتماد النتائج')
@section('content')
<h1 class="text-2xl font-bold mb-6">مراجعة واعتماد النتائج</h1>
<div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
    <table class="min-w-full text-sm">
        <thead class="bg-slate-50 text-slate-600"><tr>
            <th class="px-4 py-3 text-right">الاختبار</th><th class="px-4 py-3 text-right">المقرر / الشعبة</th>
            <th class="px-4 py-3 text-right">المدرس</th><th class="px-4 py-3 text-right">الحالة</th><th class="px-4 py-3"></th>
        </tr></thead>
        <tbody class="divide-y divide-slate-100">
        @forelse ($exams as $exam)
            <tr>
                <td class="px-4 py-3 font-semibold">{{ $exam->name }}</td>
                <td class="px-4 py-3">{{ $exam->section->course->name_ar }} — {{ $exam->section->code }}</td>
                <td class="px-4 py-3">{{ $exam->section->faculty->user->name ?? '—' }}</td>
                <td class="px-4 py-3">@include('exams._status', ['status' => $exam->result_status])</td>
                <td class="px-4 py-3 text-left whitespace-nowrap">
                    <a href="{{ route('exams.grades.edit', $exam) }}" class="text-indigo-700 hover:underline ml-3">الدرجات</a>
                    <a href="{{ route('exams.show', $exam) }}" class="text-indigo-700 hover:underline">مراجعة</a>
                </td>
            </tr>
        @empty
            <tr><td colspan="5" class="px-4 py-6 text-center text-slate-500">لا توجد نتائج بانتظار المراجعة.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
