@extends('layouts.tailwind')
@section('title', 'طلاب '.$section->course->name_ar)
@section('content')
<a href="{{ route('faculty.enrollments.index') }}" class="text-sm text-indigo-700 hover:underline">&rarr; شعبي</a>
<h1 class="mt-3 text-2xl font-bold">{{ $section->course->name_ar }} <span class="text-slate-400 text-lg">— شعبة {{ $section->code }}</span></h1>
<p class="text-sm text-slate-500 mb-6">الطلاب المسجَّلون فعليًا: {{ $students->count() }}</p>
<div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
    <table class="min-w-full text-sm">
        <thead class="bg-slate-50 text-slate-600"><tr>
            <th class="px-4 py-3 text-right">الرقم الأكاديمي</th><th class="px-4 py-3 text-right">الاسم</th><th class="px-4 py-3 text-right">المستوى</th>
        </tr></thead>
        <tbody class="divide-y divide-slate-100">
        @forelse ($students as $student)
            <tr><td class="px-4 py-3">{{ $student->number_student }}</td><td class="px-4 py-3">{{ $student->user->name }}</td><td class="px-4 py-3">{{ $student->currentLevel->name ?? '—' }}</td></tr>
        @empty
            <tr><td colspan="3" class="px-4 py-6 text-center text-slate-500">لا يوجد طلاب مسجَّلون.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
