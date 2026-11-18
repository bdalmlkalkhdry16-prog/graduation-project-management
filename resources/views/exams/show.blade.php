@extends('layouts.tailwind')
@section('title', $exam->name)
@section('content')
<a href="{{ route('exams.index') }}" class="text-sm text-indigo-700 hover:underline">&rarr; الاختبارات</a>
<div class="mt-3 mb-6 flex flex-wrap items-center justify-between gap-3">
    <h1 class="text-2xl font-bold">{{ $exam->name }} @include('exams._status', ['status' => $exam->result_status])</h1>
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('exams.grades.edit', $exam) }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm hover:bg-slate-100">الدرجات</a>
        @if (! $exam->section->distributionLocked())
            @can('update', $exam)<a href="{{ route('exams.edit', $exam) }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm hover:bg-slate-100">تعديل</a>@endcan
            @can('delete', $exam)
                <form method="POST" action="{{ route('exams.destroy', $exam) }}" onsubmit="return confirm('حذف الاختبار؟')">@csrf @method('DELETE')
                    <button class="rounded-lg border border-red-300 px-4 py-2 text-sm text-red-700 hover:bg-red-50">حذف</button></form>
            @endcan
        @endif
    </div>
</div>

@if ($exam->result_status === 'rejected' && $exam->rejection_reason)
    <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-800"><strong>سبب الرفض:</strong> {{ $exam->rejection_reason }}</div>
@endif

<section class="mb-6 rounded-xl border border-slate-200 bg-white p-5">
    <dl class="grid gap-3 text-sm sm:grid-cols-2 lg:grid-cols-3">
        <div><dt class="text-slate-500">المقرر</dt><dd>{{ $exam->section->course->name_ar }}</dd></div>
        <div><dt class="text-slate-500">الشعبة</dt><dd>{{ $exam->section->code }}</dd></div>
        <div><dt class="text-slate-500">الفصل</dt><dd>{{ $exam->section->academicTerm->academicYear->name ?? '' }} — {{ \App\Services\AcademicRecordService::semesterLabel($exam->section->academicTerm->semester) }}</dd></div>
        <div><dt class="text-slate-500">المدرس</dt><dd>{{ $exam->section->faculty->user->name ?? '—' }}</dd></div>
        <div><dt class="text-slate-500">النوع</dt><dd>{{ \App\Models\Exam::typeLabel($exam->type) }}</dd></div>
        <div><dt class="text-slate-500">التاريخ والوقت</dt><dd>{{ $exam->exam_date->format('Y-m-d') }} {{ $exam->start_time ? substr($exam->start_time, 0, 5) : '' }}</dd></div>
        <div><dt class="text-slate-500">المدة</dt><dd>{{ $exam->duration_minutes }} دقيقة</dd></div>
        <div><dt class="text-slate-500">الدرجة النهائية / المخصصة</dt><dd>{{ $exam->max_score + 0 }} / {{ $exam->weight + 0 }}</dd></div>
        <div><dt class="text-slate-500">درجات مرصودة</dt><dd>{{ $gradesCount }}</dd></div>
    </dl>
</section>

@if ($exam->section->distributionLocked())
    <div class="mb-6 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">توزيع درجات هذه الشعبة مقفل بعد تقديم النتائج. أي تعديل يتم إداريًا بسبب مسجَّل وقيم سابقة وجديدة.</div>
    @can('adjustDistribution', \App\Models\Exam::class)
        <section class="mb-6 rounded-xl border border-slate-200 bg-white p-5">
            <h2 class="font-bold mb-3">تعديل إداري للتوزيع</h2>
            <form method="POST" action="{{ route('exams.distribution.update', $exam) }}" class="grid gap-2 sm:grid-cols-5 text-sm">@csrf @method('PUT')
                <input name="name" value="{{ $exam->name }}" required class="rounded-lg border border-slate-300 px-2 py-1">
                <select name="type" class="rounded-lg border border-slate-300 px-2 py-1">@foreach (\App\Models\Exam::TYPES as $t)<option value="{{ $t }}" @selected($exam->type === $t)>{{ \App\Models\Exam::typeLabel($t) }}</option>@endforeach</select>
                <input name="max_score" type="number" step="0.01" value="{{ $exam->max_score + 0 }}" required class="rounded-lg border border-slate-300 px-2 py-1">
                <input name="weight" type="number" step="0.01" value="{{ $exam->weight + 0 }}" required class="rounded-lg border border-slate-300 px-2 py-1">
                <input name="reason" placeholder="السبب (إلزامي)" required class="rounded-lg border border-slate-300 px-2 py-1">
                <button class="rounded-lg border border-slate-300 px-3 py-1 hover:bg-slate-100 sm:col-span-5">تسجيل التعديل الإداري</button>
            </form>
        </section>
    @endcan
@endif

<section class="rounded-xl border border-slate-200 bg-white p-5">
    <h2 class="font-bold mb-3">دورة النتائج</h2>
    <div class="flex flex-wrap items-start gap-3">
        @if ($exam->isEditable())
            @can('submitResults', $exam)
                <form method="POST" action="{{ route('exams.submit', $exam) }}">@csrf
                    <button class="rounded-lg bg-amber-600 px-4 py-2 text-white hover:bg-amber-700">تقديم للمراجعة</button></form>
            @endcan
        @endif
        @if ($exam->result_status === 'submitted')
            @can('reviewResults', $exam)
                <form method="POST" action="{{ route('exams.approve', $exam) }}">@csrf
                    <button class="rounded-lg bg-sky-600 px-4 py-2 text-white hover:bg-sky-700">اعتماد</button></form>
            @endcan
        @endif
        @if (in_array($exam->result_status, ['submitted', 'approved']))
            @can('reviewResults', $exam)
                <form method="POST" action="{{ route('exams.reject', $exam) }}" class="flex flex-wrap gap-2">@csrf
                    <input name="reason" placeholder="سبب الرفض" required class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <button class="rounded-lg border border-red-300 px-4 py-2 text-red-700 hover:bg-red-50">رفض</button></form>
            @endcan
        @endif
        @if ($exam->result_status === 'approved')
            @can('publishResults', $exam)
                <form method="POST" action="{{ route('exams.publish', $exam) }}">@csrf
                    <button class="rounded-lg bg-emerald-600 px-4 py-2 text-white hover:bg-emerald-700">نشر للطلاب</button></form>
            @endcan
        @endif
    </div>
    <ul class="mt-4 text-xs text-slate-500 space-y-1">
        @if ($exam->submitted_at)<li>قُدِّمت: {{ $exam->submitted_at->format('Y-m-d H:i') }}</li>@endif
        @if ($exam->approved_at)<li>اعتُمدت: {{ $exam->approved_at->format('Y-m-d H:i') }}</li>@endif
        @if ($exam->published_at)<li>نُشرت: {{ $exam->published_at->format('Y-m-d H:i') }}</li>@endif
    </ul>
</section>
@endsection
