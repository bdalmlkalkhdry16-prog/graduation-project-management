@extends('layouts.tailwind')
@section('title', 'سجل تسجيلات الطلاب')
@section('content')
<h1 class="text-2xl font-bold mb-6">سجل تسجيلات الطلاب</h1>
<form method="GET" class="mb-6 flex gap-2">
    <input type="search" name="q" value="{{ request('q') }}" placeholder="بحث بالاسم أو الرقم الأكاديمي"
           class="flex-1 rounded-lg border border-slate-300 bg-white px-3 py-2">
    <button class="rounded-lg bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700">بحث</button>
</form>
<div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
    <table class="min-w-full text-sm">
        <thead class="bg-slate-50 text-slate-600"><tr>
            <th class="px-4 py-3 text-right">الرقم الأكاديمي</th><th class="px-4 py-3 text-right">الاسم</th>
            <th class="px-4 py-3 text-right">المستوى</th><th class="px-4 py-3"></th>
        </tr></thead>
        <tbody class="divide-y divide-slate-100">
        @forelse ($students as $student)
            <tr>
                <td class="px-4 py-3">{{ $student->number_student }}</td>
                <td class="px-4 py-3">{{ $student->user->name }}</td>
                <td class="px-4 py-3">{{ $student->currentLevel->name ?? '—' }}</td>
                <td class="px-4 py-3 text-left"><a href="{{ route('staff.enrollments.history', $student) }}" class="text-indigo-700 hover:underline">سجل التسجيلات</a></td>
            </tr>
        @empty
            <tr><td colspan="4" class="px-4 py-6 text-center text-slate-500">لا نتائج.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $students->links() }}</div>
@endsection
