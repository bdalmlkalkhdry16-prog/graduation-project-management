@extends('layouts.tailwind')
@php($editing = $exam->exists)
@section('title', $editing ? 'تعديل اختبار' : 'اختبار جديد')
@section('content')
<h1 class="text-2xl font-bold mb-6">{{ $editing ? 'تعديل اختبار' : 'اختبار جديد' }}</h1>
<form method="POST" action="{{ $editing ? route('exams.update', $exam) : route('exams.store') }}" class="max-w-2xl space-y-4 rounded-xl border border-slate-200 bg-white p-6">
    @csrf
    @if ($editing) @method('PUT') @endif

    <div>
        <label class="block text-sm mb-1">الشعبة (المقرر والفصل)</label>
        @if ($editing)
            <div class="rounded-lg bg-slate-50 px-3 py-2">{{ $exam->section->course->name_ar }} — شعبة {{ $exam->section->code }}</div>
        @else
            <select name="section_id" required class="w-full rounded-lg border border-slate-300 px-3 py-2">
                <option value="">اختر الشعبة</option>
                @foreach ($sections as $section)
                    <option value="{{ $section->id }}" @selected(old('section_id') == $section->id)>{{ $section->course->name_ar }} — {{ $section->code }} ({{ $section->academicTerm->academicYear->name ?? '' }} / {{ \App\Services\AcademicRecordService::semesterLabel($section->academicTerm->semester) }})</option>
                @endforeach
            </select>
        @endif
    </div>
    <div class="grid gap-4 sm:grid-cols-2">
        <div><label class="block text-sm mb-1">اسم الاختبار</label>
            <input name="name" value="{{ old('name', $exam->name) }}" required class="w-full rounded-lg border border-slate-300 px-3 py-2"></div>
        <div><label class="block text-sm mb-1">النوع</label>
            <select name="type" required class="w-full rounded-lg border border-slate-300 px-3 py-2">
                @foreach (\App\Models\Exam::TYPES as $type)
                    <option value="{{ $type }}" @selected(old('type', $exam->type) === $type)>{{ \App\Models\Exam::typeLabel($type) }}</option>
                @endforeach
            </select></div>
        <div><label class="block text-sm mb-1">التاريخ</label>
            <input type="date" name="exam_date" value="{{ old('exam_date', $exam->exam_date?->format('Y-m-d')) }}" required class="w-full rounded-lg border border-slate-300 px-3 py-2"></div>
        <div><label class="block text-sm mb-1">وقت البداية (اختياري)</label>
            <input type="time" name="start_time" value="{{ old('start_time', $exam->start_time ? substr($exam->start_time, 0, 5) : '') }}" class="w-full rounded-lg border border-slate-300 px-3 py-2"></div>
        <div><label class="block text-sm mb-1">المدة (دقائق)</label>
            <input type="number" min="1" max="600" name="duration_minutes" value="{{ old('duration_minutes', $exam->duration_minutes) }}" required class="w-full rounded-lg border border-slate-300 px-3 py-2"></div>
        <div><label class="block text-sm mb-1">الدرجة النهائية للاختبار</label>
            <input type="number" step="0.01" min="0.01" name="max_score" value="{{ old('max_score', $exam->max_score) }}" required class="w-full rounded-lg border border-slate-300 px-3 py-2"></div>
        <div><label class="block text-sm mb-1">الدرجة المخصصة من مجموع المقرر</label>
            <input type="number" step="0.01" min="0" name="weight" value="{{ old('weight', $exam->weight) }}" required class="w-full rounded-lg border border-slate-300 px-3 py-2"></div>
    </div>
    <div class="flex gap-3">
        <button class="rounded-lg bg-indigo-600 px-5 py-2 text-white hover:bg-indigo-700">حفظ</button>
        <a href="{{ $editing ? route('exams.show', $exam) : route('exams.index') }}" class="rounded-lg border border-slate-300 px-5 py-2 hover:bg-slate-100">إلغاء</a>
    </div>
</form>
@endsection
