@extends('layouts.tailwind')
@section('title', 'التسجيل في الشعب')
@section('content')
<div class="flex flex-wrap items-center justify-between gap-3 mb-6">
    <h1 class="text-2xl font-bold">التسجيل في الشعب الدراسية</h1>
    <a href="{{ route('staff.enrollments.students') }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm hover:bg-slate-100">البحث عن طالب وسجله</a>
</div>

<form method="GET" class="mb-6 flex flex-wrap items-end gap-3">
    <div>
        <label for="term" class="block text-sm text-slate-600 mb-1">الفصل الأكاديمي</label>
        <select id="term" name="term" onchange="this.form.submit()" class="rounded-lg border border-slate-300 bg-white px-3 py-2 min-w-56">
            @foreach ($terms as $term)
                <option value="{{ $term->id }}" @selected($term->id == $termId)>@include('enrollments._term-label', ['term' => $term])</option>
            @endforeach
        </select>
    </div>
    <noscript><button class="rounded-lg bg-indigo-600 px-4 py-2 text-white">عرض</button></noscript>
</form>

<div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
    @forelse ($sections as $section)
        <a href="{{ route('staff.enrollments.show', $section) }}" class="block rounded-xl border border-slate-200 bg-white p-5 hover:border-indigo-400 hover:shadow-sm transition">
            <div class="flex items-start justify-between gap-2">
                <h2 class="font-bold text-lg">{{ $section->course->name_ar }}</h2>
                <span class="text-xs rounded bg-indigo-50 text-indigo-700 px-2 py-1">شعبة {{ $section->code }}</span>
            </div>
            <dl class="mt-3 space-y-1 text-sm text-slate-600">
                <div class="flex justify-between"><dt>المدرس</dt><dd>{{ $section->faculty->user->name ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt>القاعة</dt><dd>{{ $section->schedules->map(fn ($s) => $s->room->name)->unique()->join('، ') ?: '—' }}</dd></div>
                <div class="flex justify-between"><dt>المسجَّلون</dt><dd class="font-semibold">{{ $section->active_count }}@if ($section->capacity) <span class="text-slate-400 font-normal">(السعة: {{ $section->capacity }})</span>@endif</dd></div>
            </dl>
        </a>
    @empty
        <p class="text-slate-500 col-span-full">لا توجد شعب في هذا الفصل.</p>
    @endforelse
</div>
<div class="mt-6">{{ $sections->links() }}</div>
@endsection
