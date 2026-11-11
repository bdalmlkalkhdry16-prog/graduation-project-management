@extends('layouts.tailwind')
@section('title', 'طلاب شعبي')
@section('content')
<h1 class="text-2xl font-bold mb-6">شعبي الدراسية</h1>
<div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
    @forelse ($sections as $section)
        <a href="{{ route('faculty.enrollments.show', $section) }}" class="block rounded-xl border border-slate-200 bg-white p-5 hover:border-indigo-400 transition">
            <h2 class="font-bold text-lg">{{ $section->course->name_ar }}</h2>
            <p class="text-sm text-slate-500">شعبة {{ $section->code }} · @include('enrollments._term-label', ['term' => $section->academicTerm])</p>
            <p class="mt-2 text-sm">الطلاب المسجَّلون: <span class="font-semibold">{{ $section->active_count }}</span></p>
        </a>
    @empty
        <p class="text-slate-500 col-span-full">لا توجد شعب مسندة إليك.</p>
    @endforelse
</div>
@endsection
