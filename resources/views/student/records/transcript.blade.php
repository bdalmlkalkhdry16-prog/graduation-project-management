@extends('layouts.tailwind')
@section('title', 'كشف الدرجات')
@section('content')
<div class="mb-6 flex flex-wrap gap-2 print:hidden">
    <button onclick="window.print()" class="rounded-lg bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700">طباعة</button>
    <a href="{{ route('student.transcript.pdf') }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2 hover:bg-slate-100">تصدير PDF</a>
    <a href="{{ route('student.academic-record') }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2 hover:bg-slate-100">السجل الأكاديمي الكامل</a>
</div>

@include('student._withheld-notice', ['withheld' => $record['withheld_years'] ?? collect()])

@if (! $record)
    <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-amber-800">لا يوجد ملف أكاديمي مرتبط بحسابك بعد.</div>
@else
    <article class="rounded-xl border border-slate-300 bg-white p-8 print:border-0 print:p-0">
        <header class="mb-6 border-b border-slate-300 pb-4 text-center">
            <h1 class="text-2xl font-bold">{{ $institution }}</h1>
            <h2 class="mt-1 text-lg">كشف الدرجات</h2>
            <p class="text-xs text-slate-500">تاريخ الإصدار: {{ $record['issued_at']->format('Y-m-d') }}</p>
        </header>
        <div class="mb-6">@include('student.records._student-info')</div>

        @forelse ($record['terms'] as $term)
            <h3 class="mt-6 mb-2 font-bold">{{ $term['label'] }}</h3>
            <table class="w-full text-sm border border-slate-300">
                <thead class="bg-slate-50"><tr>
                    <th class="border border-slate-300 px-2 py-1.5 text-right">رمز المقرر</th>
                    <th class="border border-slate-300 px-2 py-1.5 text-right">اسم المقرر</th>
                    <th class="border border-slate-300 px-2 py-1.5">الساعات</th>
                    <th class="border border-slate-300 px-2 py-1.5">المجموع</th>
                    <th class="border border-slate-300 px-2 py-1.5">النسبة</th>
                    <th class="border border-slate-300 px-2 py-1.5">التقدير</th>
                    <th class="border border-slate-300 px-2 py-1.5">الحالة</th>
                </tr></thead>
                <tbody>
                @foreach ($term['courses'] as $c)
                    <tr class="text-center">
                        <td class="border border-slate-300 px-2 py-1.5 text-right">{{ $c['course']->code }}</td>
                        <td class="border border-slate-300 px-2 py-1.5 text-right">{{ $c['course']->name_ar }}</td>
                        <td class="border border-slate-300 px-2 py-1.5">{{ $c['credit_hours'] ?? '—' }}</td>
                        <td class="border border-slate-300 px-2 py-1.5">{{ $c['complete'] ? $c['total'] + 0 : '—' }}</td>
                        <td class="border border-slate-300 px-2 py-1.5">{{ $c['percentage'] !== null ? ($c['percentage'] + 0).'%' : '—' }}</td>
                        <td class="border border-slate-300 px-2 py-1.5">{{ $c['letter'] ?? '—' }}</td>
                        <td class="border border-slate-300 px-2 py-1.5">{{ $c['status_label'] }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            @if ($term['gpa'] !== null)<p class="mt-1 text-sm">معدل الفصل: <strong>{{ $term['gpa'] }}</strong></p>@endif
        @empty
            <p class="text-slate-500">لا توجد مقررات.</p>
        @endforelse

        @if ($record['cumulative_gpa'] !== null)<p class="mt-6 text-sm">المعدل التراكمي: <strong>{{ $record['cumulative_gpa'] }}</strong></p>@endif
        <p class="mt-8 text-xs text-slate-500">يتضمن هذا الكشف النتائج المنشورة والمعتمدة فقط.</p>
    </article>
@endif
@endsection
