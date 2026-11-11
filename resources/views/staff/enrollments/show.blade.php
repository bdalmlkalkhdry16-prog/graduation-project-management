@extends('layouts.tailwind')
@section('title', 'شعبة '.$section->course->name_ar)
@section('content')
<a href="{{ route('staff.enrollments.index', ['term' => $section->academic_term_id]) }}" class="text-sm text-indigo-700 hover:underline">&rarr; كل الشعب</a>

<section class="mt-3 mb-8 rounded-xl border border-slate-200 bg-white p-5">
    <h1 class="text-2xl font-bold">{{ $section->course->name_ar }} <span class="text-slate-400 text-lg">— شعبة {{ $section->code }}</span></h1>
    <dl class="mt-4 grid gap-3 text-sm sm:grid-cols-2 lg:grid-cols-3">
        <div><dt class="text-slate-500">الفصل</dt><dd>@include('enrollments._term-label', ['term' => $section->academicTerm])</dd></div>
        <div><dt class="text-slate-500">البرنامج</dt><dd>{{ $levels->map(fn ($l) => $l->program->specialization->name_ar ?? '')->filter()->unique()->join('، ') ?: '—' }}</dd></div>
        <div><dt class="text-slate-500">المستوى</dt><dd>{{ $levels->pluck('name')->join('، ') ?: '—' }}</dd></div>
        <div><dt class="text-slate-500">المدرس</dt><dd>{{ $section->faculty->user->name ?? '—' }}</dd></div>
        <div><dt class="text-slate-500">القاعة</dt><dd>{{ $section->schedules->map(fn ($s) => $s->room->name)->unique()->join('، ') ?: '—' }}</dd></div>
        <div><dt class="text-slate-500">عدد الطلاب النشطين</dt><dd class="font-semibold">{{ $activeCount }}@if ($section->capacity) <span class="text-slate-400 font-normal">(السعة: {{ $section->capacity }})</span>@endif</dd></div>
    </dl>
</section>

<form method="GET" class="mb-6 flex gap-2">
    <input type="search" name="q" value="{{ request('q') }}" placeholder="بحث بالاسم أو الرقم الأكاديمي"
           class="flex-1 rounded-lg border border-slate-300 bg-white px-3 py-2">
    <button class="rounded-lg bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700">بحث</button>
</form>

<div class="grid gap-8 lg:grid-cols-2">
    <section>
        <h2 class="text-lg font-bold mb-3">الطلاب المسجَّلون ({{ $enrollments->count() }})</h2>
        <div class="space-y-3">
            @forelse ($enrollments as $enrollment)
                <div class="rounded-xl border border-slate-200 bg-white p-4">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <div>
                            <a href="{{ route('staff.enrollments.history', $enrollment->studentProfile) }}" class="font-semibold hover:text-indigo-700">{{ $enrollment->studentProfile->user->name }}</a>
                            <div class="text-xs text-slate-500">{{ $enrollment->studentProfile->number_student }}</div>
                        </div>
                        @include('enrollments._status-badge', ['status' => $enrollment->status])
                    </div>
                    <form method="POST" action="{{ route('staff.enrollments.status', $enrollment) }}" class="mt-3 flex items-center gap-2">
                        @csrf @method('PATCH')
                        <select name="status" class="rounded-lg border border-slate-300 bg-white px-2 py-1 text-sm">
                            <option value="active" @selected($enrollment->status === 'active')>نشط</option>
                            <option value="dropped" @selected($enrollment->status === 'dropped')>منسحب</option>
                            <option value="completed" @selected($enrollment->status === 'completed')>مكتمل</option>
                        </select>
                        <button class="rounded-lg border border-slate-300 px-3 py-1 text-sm hover:bg-slate-100">تحديث</button>
                    </form>
                </div>
            @empty
                <p class="text-slate-500">لا يوجد طلاب مسجَّلون.</p>
            @endforelse
        </div>
    </section>

    <section>
        <h2 class="text-lg font-bold mb-3">طلاب مؤهَّلون للتسجيل ({{ $eligible->count() }})</h2>
        <p class="text-xs text-slate-500 mb-3">حسب البرنامج والمستوى المرتبطين بمقرر هذه الشعبة في الخطة الدراسية.</p>
        <div class="space-y-3">
            @forelse ($eligible as $student)
                <form method="POST" action="{{ route('staff.enrollments.store', $section) }}" class="flex items-center justify-between gap-2 rounded-xl border border-slate-200 bg-white p-4">
                    @csrf
                    <input type="hidden" name="student_profile_id" value="{{ $student->id }}">
                    <div>
                        <div class="font-semibold">{{ $student->user->name }}</div>
                        <div class="text-xs text-slate-500">{{ $student->number_student }} · {{ $student->currentLevel->name ?? '' }}</div>
                    </div>
                    <button class="rounded-lg bg-emerald-600 px-3 py-1.5 text-sm text-white hover:bg-emerald-700">تسجيل</button>
                </form>
            @empty
                <p class="text-slate-500">لا يوجد طلاب مؤهَّلون غير مسجَّلين.</p>
            @endforelse
        </div>
    </section>
</div>
@endsection
