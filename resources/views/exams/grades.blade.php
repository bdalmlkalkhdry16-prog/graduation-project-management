@extends('layouts.tailwind')
@section('title', 'رصد درجات '.$exam->name)
@section('content')
<a href="{{ route('exams.show', $exam) }}" class="text-sm text-indigo-700 hover:underline">&rarr; {{ $exam->name }}</a>
<h1 class="mt-3 text-2xl font-bold">رصد الدرجات — {{ $exam->section->course->name_ar }} (شعبة {{ $exam->section->code }})</h1>
<p class="mb-6 text-sm text-slate-500">{{ $exam->name }} · الدرجة النهائية {{ $exam->max_score + 0 }} · @include('exams._status', ['status' => $exam->result_status])</p>

@php($canEnter = $exam->isEditable() && auth()->user()->can('enterGrades', $exam))
@php($canCorrect = in_array($exam->result_status, ['approved', 'published']) && auth()->user()->can('correctGrades', $exam))

@if (! $exam->isEditable())
    <div class="mb-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-amber-800">قائمة الطلاب مثبتة منذ التقديم ولا تتأثر بتغيّر التسجيل لاحقًا. النتائج مقفلة ({{ \App\Models\Exam::statusLabel($exam->result_status) }}). التعديل يتم عبر التصحيح المعتمد فقط لمن يملك الصلاحية.</div>
@endif

<form method="POST" action="{{ route('exams.grades.update', $exam) }}">
    @csrf @method('PUT')
    <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50 text-slate-600"><tr>
                <th class="px-4 py-3 text-right">الرقم الأكاديمي</th><th class="px-4 py-3 text-right">الطالب</th>
                <th class="px-4 py-3 text-right">الدرجة (من {{ $exam->max_score + 0 }})</th><th class="px-4 py-3 text-right">غياب</th><th class="px-4 py-3"></th>
            </tr></thead>
            <tbody class="divide-y divide-slate-100">
            @forelse ($roster as $enrollment)
                @php($grade = $grades->get($enrollment->id))
                <tr>
                    <td class="px-4 py-3">{{ $enrollment->studentProfile->number_student }}</td>
                    <td class="px-4 py-3">{{ $enrollment->studentProfile->user->name }}</td>
                    <td class="px-4 py-3">
                        @if ($canEnter)
                            <input type="number" step="0.01" min="0" max="{{ $exam->max_score }}" name="scores[{{ $enrollment->id }}]"
                                   value="{{ old('scores.'.$enrollment->id, $grade?->score) }}" class="w-28 rounded-lg border border-slate-300 px-2 py-1">
                        @else
                            {{ $grade?->score !== null ? $grade->score + 0 : '—' }}
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        @if ($canEnter)
                            <label class="flex items-center gap-1 text-xs"><input type="checkbox" name="absent[{{ $enrollment->id }}]" value="1" @checked(old('absent.'.$enrollment->id, $grade?->isAbsentUnresolved()))> غياب (غير محسوم)</label>
                        @elseif ($grade?->isAbsentUnresolved())
                            <span class="rounded-full bg-red-100 px-2 py-0.5 text-xs text-red-700">غياب غير محسوم — يمنع النشر</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-left">
                        @if ($grade && $grade->changes->isNotEmpty())
                            <details class="text-xs">
                                <summary class="cursor-pointer text-indigo-700">سجل التعديلات ({{ $grade->changes->count() }})</summary>
                                <ul class="mt-2 space-y-1 text-slate-600">
                                    @foreach ($grade->changes as $change)
                                        <li>{{ $change->created_at->format('Y-m-d H:i') }} — {{ $change->changedBy->name ?? '—' }}:
                                            {{ $change->old_score !== null ? $change->old_score + 0 : ($change->old_status === 'absent_unresolved' ? 'غياب' : '∅') }} ← {{ $change->new_score !== null ? $change->new_score + 0 : 'غياب' }}
                                            ({{ ['entered' => 'رصد', 'updated' => 'تعديل', 'corrected' => 'تصحيح معتمد'][$change->action] }}){{ $change->reason ? ' — '.$change->reason : '' }}</li>
                                    @endforeach
                                </ul>
                            </details>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-4 py-6 text-center text-slate-500">لا يوجد طلاب في قائمة هذا الاختبار.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if ($canEnter && $roster->isNotEmpty())
        <button class="mt-4 rounded-lg bg-indigo-600 px-5 py-2 text-white hover:bg-indigo-700">حفظ كمسودة</button>
        <span class="mr-2 text-xs text-slate-500">لا تُمسح درجة سابقة بترك الحقل فارغًا. الغياب لا يُحوَّل إلى صفر ولا يمكن نشر نتيجة تحتوي غيابًا غير محسوم.</span>
    @endif
</form>

@if ($canCorrect)
    <section class="mt-8 rounded-xl border border-slate-200 bg-white p-5">
        <h2 class="font-bold mb-3">تصحيح معتمد لدرجة</h2>
        <div class="space-y-3">
            @foreach ($roster as $enrollment)
                @php($grade = $grades->get($enrollment->id))
                @if ($grade)
                    <form method="POST" action="{{ route('exams.grades.correct', [$exam, $grade]) }}" class="flex flex-wrap items-center gap-2 text-sm">
                        @csrf
                        <span class="w-48">{{ $enrollment->studentProfile->user->name }} ({{ $grade->score !== null ? $grade->score + 0 : 'غياب غير محسوم' }})</span>
                        <input type="number" step="0.01" min="0" max="{{ $exam->max_score }}" name="new_score" placeholder="الدرجة الجديدة" required class="w-32 rounded-lg border border-slate-300 px-2 py-1">
                        <input name="reason" placeholder="سبب التصحيح (إلزامي)" required class="flex-1 min-w-48 rounded-lg border border-slate-300 px-2 py-1">
                        <button class="rounded-lg border border-slate-300 px-3 py-1 hover:bg-slate-100">تصحيح</button>
                    </form>
                @endif
            @endforeach
        </div>
    </section>
@endif
@endsection
