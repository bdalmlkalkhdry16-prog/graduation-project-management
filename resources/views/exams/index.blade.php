@extends('layouts.tailwind')
@section('title', 'الاختبارات')
@section('content')
<div class="flex flex-wrap items-center justify-between gap-3 mb-6">
    <h1 class="text-2xl font-bold">الاختبارات</h1>
    @can('create', \App\Models\Exam::class)
        <a href="{{ route('exams.create') }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700">+ اختبار جديد</a>
    @endcan
</div>

<form method="GET" class="mb-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
    <select name="term" class="rounded-lg border border-slate-300 bg-white px-3 py-2">
        <option value="">كل الفصول</option>
        @foreach ($terms as $term)
            <option value="{{ $term->id }}" @selected(request('term') == $term->id)>{{ $term->academicYear->name ?? '' }} — {{ \App\Services\AcademicRecordService::semesterLabel($term->semester) }}</option>
        @endforeach
    </select>
    <select name="course" class="rounded-lg border border-slate-300 bg-white px-3 py-2">
        <option value="">كل المقررات</option>
        @foreach ($courses as $course)
            <option value="{{ $course->id }}" @selected(request('course') == $course->id)>{{ $course->code }} — {{ $course->name_ar }}</option>
        @endforeach
    </select>
    <select name="section" class="rounded-lg border border-slate-300 bg-white px-3 py-2">
        <option value="">كل الشعب</option>
        @foreach ($sections as $section)
            <option value="{{ $section->id }}" @selected(request('section') == $section->id)>{{ $section->course->name_ar }} — {{ $section->code }}</option>
        @endforeach
    </select>
    <button class="rounded-lg border border-slate-300 bg-white px-4 py-2 hover:bg-slate-100">تصفية</button>
</form>

<div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
    <table class="min-w-full text-sm">
        <thead class="bg-slate-50 text-slate-600"><tr>
            <th class="px-4 py-3 text-right">الاختبار</th><th class="px-4 py-3 text-right">المقرر / الشعبة</th>
            <th class="px-4 py-3 text-right">النوع</th><th class="px-4 py-3 text-right">التاريخ</th>
            <th class="px-4 py-3 text-right">الدرجة</th><th class="px-4 py-3 text-right">الحالة</th><th class="px-4 py-3"></th>
        </tr></thead>
        <tbody class="divide-y divide-slate-100">
        @forelse ($exams as $exam)
            <tr>
                <td class="px-4 py-3 font-semibold">{{ $exam->name }}</td>
                <td class="px-4 py-3">{{ $exam->section->course->name_ar }} — {{ $exam->section->code }}</td>
                <td class="px-4 py-3">{{ \App\Models\Exam::typeLabel($exam->type) }}</td>
                <td class="px-4 py-3">{{ $exam->exam_date->format('Y-m-d') }}</td>
                <td class="px-4 py-3">{{ $exam->max_score + 0 }} (مخصصة: {{ $exam->weight + 0 }})</td>
                <td class="px-4 py-3">@include('exams._status', ['status' => $exam->result_status])</td>
                <td class="px-4 py-3 text-left"><a href="{{ route('exams.show', $exam) }}" class="text-indigo-700 hover:underline">فتح</a></td>
            </tr>
        @empty
            <tr><td colspan="7" class="px-4 py-6 text-center text-slate-500">لا توجد اختبارات.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $exams->links() }}</div>
@endsection
