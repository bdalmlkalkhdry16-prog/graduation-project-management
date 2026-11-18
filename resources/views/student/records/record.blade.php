@extends('layouts.tailwind')
@section('title', 'السجل الأكاديمي')
@section('content')
<div class="flex flex-wrap items-center justify-between gap-3 mb-6">
    <h1 class="text-2xl font-bold">السجل الأكاديمي</h1>
    @empty($staffView)
        <a href="{{ route('student.transcript') }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm hover:bg-slate-100">كشف الدرجات / طباعة</a>
    @endempty
</div>

@if (! $record)
    <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-amber-800">لا يوجد ملف أكاديمي مرتبط بحسابك بعد.</div>
@else
    <section class="mb-6 rounded-xl border border-slate-200 bg-white p-5">@include('student.records._student-info')</section>
    <p class="mb-4 text-xs text-slate-500">يعرض هذا السجل النتائج المنشورة فقط.</p>

    @forelse ($record['terms'] as $term)
        <section class="mb-8">
            <h2 class="mb-3 text-lg font-bold">{{ $term['label'] }}</h2>
            @foreach ($term['courses'] as $course)
                <div class="mb-3 rounded-xl border border-slate-200 bg-white p-4">
                    <div class="flex flex-wrap items-start justify-between gap-2">
                        <div>
                            <div class="font-semibold">{{ $course['course']->code }} — {{ $course['course']->name_ar }}</div>
                            <div class="text-xs text-slate-500">شعبة {{ $course['section']->code }} · الساعات المعتمدة: {{ $course['credit_hours'] ?? '—' }} · التسجيل: {{ ['active' => 'نشط', 'completed' => 'مكتمل', 'dropped' => 'منسحب (النتائج المنشورة محفوظة)'][$course['enrollment']->status] ?? $course['enrollment']->status }}</div>
                        </div>
                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold">{{ $course['status_label'] }}</span>
                    </div>
                    @if ($course['exams'])
                        <table class="mt-3 min-w-full text-sm">
                            <tbody class="divide-y divide-slate-100">
                            @foreach ($course['exams'] as $row)
                                <tr><td class="py-1.5">{{ $row['exam']->name }} ({{ \App\Models\Exam::typeLabel($row['exam']->type) }})</td>
                                    <td class="py-1.5">{{ $row['score'] + 0 }} / {{ $row['exam']->max_score + 0 }}</td>
                                    <td class="py-1.5 text-slate-500">المحتسب: {{ $row['contribution'] + 0 }}</td></tr>
                            @endforeach
                            </tbody>
                        </table>
                        <div class="mt-2 text-sm">المجموع المحتسب: <strong>{{ $course['total'] + 0 }}</strong>
                            @if ($course['percentage'] !== null) · النسبة: <strong>{{ $course['percentage'] + 0 }}%</strong>@endif
                            @if ($course['letter']) · التقدير: <strong>{{ $course['letter'] }}</strong>@endif
                        </div>
                    @else
                        <p class="mt-2 text-sm text-slate-500">لا توجد نتائج منشورة لهذا المقرر.</p>
                    @endif
                </div>
            @endforeach
            @if ($term['gpa'] !== null)<p class="text-sm">معدل الفصل: <strong>{{ $term['gpa'] }}</strong></p>@endif
        </section>
    @empty
        <p class="text-slate-500">لا توجد مقررات مسجَّلة.</p>
    @endforelse

    @if ($record['cumulative_gpa'] !== null)
        <p class="rounded-lg bg-indigo-50 px-4 py-3 text-indigo-900">المعدل التراكمي: <strong>{{ $record['cumulative_gpa'] }}</strong></p>
    @elseif (! $record['has_scale'])
        <p class="text-xs text-slate-500">لم يُضبط سلم التقديرات في المؤسسة بعد، لذلك لا تُحسب معدلات.</p>
    @endif
@endif
@endsection
