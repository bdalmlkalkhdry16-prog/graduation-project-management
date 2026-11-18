@extends('layouts.tailwind')
@section('title', 'نتائجي')
@section('content')
<h1 class="text-2xl font-bold mb-6">نتائجي المنشورة</h1>
@if (! $profile)
    <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-amber-800">لا يوجد ملف أكاديمي مرتبط بحسابك بعد.</div>
@else
    <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50 text-slate-600"><tr>
                <th class="px-4 py-3 text-right">المقرر</th><th class="px-4 py-3 text-right">الاختبار</th>
                <th class="px-4 py-3 text-right">النوع</th><th class="px-4 py-3 text-right">الدرجة</th><th class="px-4 py-3 text-right">تاريخ النشر</th>
            </tr></thead>
            <tbody class="divide-y divide-slate-100">
            @forelse ($grades as $grade)
                <tr>
                    <td class="px-4 py-3">{{ $grade->exam->section->course->name_ar }}</td>
                    <td class="px-4 py-3">{{ $grade->exam->name }}</td>
                    <td class="px-4 py-3">{{ \App\Models\Exam::typeLabel($grade->exam->type) }}</td>
                    <td class="px-4 py-3 font-semibold">{{ $grade->score + 0 }} / {{ $grade->exam->max_score + 0 }}</td>
                    <td class="px-4 py-3">{{ $grade->exam->published_at?->format('Y-m-d') }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-4 py-6 text-center text-slate-500">لا توجد نتائج منشورة بعد.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
@endif
@endsection
