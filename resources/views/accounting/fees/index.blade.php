@extends('layouts.tailwind')
@section('title', 'رسوم الطلاب')
@section('content')
<h1 class="mb-4 text-2xl font-bold">رسوم الطلاب</h1>

@can('generate', \App\Models\StudentFee::class)
<form method="POST" action="{{ route('accounting.fees.generate') }}" class="mb-6 flex flex-wrap items-end gap-3 rounded-xl border border-slate-200 bg-white p-4 text-sm">
    @csrf
    <div><label class="mb-1 block">السنة الدراسية</label>
        <select name="academic_year_id" required class="rounded-lg border border-slate-300 px-3 py-2">
            <option value="">— اختر —</option>@foreach ($years as $y)<option value="{{ $y->id }}">{{ $y->name }}</option>@endforeach
        </select></div>
    <div><label class="mb-1 block">القسم (اختياري)</label>
        <select name="department_id" class="rounded-lg border border-slate-300 px-3 py-2">
            <option value="">كل الأقسام</option>@foreach ($departments as $d)<option value="{{ $d->id }}">{{ $d->name_ar }}</option>@endforeach
        </select></div>
    <button class="rounded-lg bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700">توليد رسوم الطلاب النشطين</button>
    <span class="text-xs text-slate-500">لا يكرر المولَّد سابقًا، ولا يُنشئ رسمًا لقسم بلا سعر.</span>
</form>
@endcan

<form method="GET" class="mb-4 flex flex-wrap gap-2 text-sm">
    <select name="academic_year_id" class="rounded-lg border border-slate-300 px-3 py-2"><option value="">كل السنوات</option>
        @foreach ($years as $y)<option value="{{ $y->id }}" @selected((string) request('academic_year_id') === (string) $y->id)>{{ $y->name }}</option>@endforeach</select>
    <select name="status" class="rounded-lg border border-slate-300 px-3 py-2">
        <option value="">كل الحالات</option>
        <option value="unsettled" @selected(request('status') === 'unsettled')>غير مسددة</option>
        <option value="settled" @selected(request('status') === 'settled')>مسددة</option></select>
    <input name="q" value="{{ request('q') }}" placeholder="رقم قيد الطالب" class="rounded-lg border border-slate-300 px-3 py-2">
    <button class="rounded-lg border border-slate-300 px-4 py-2 hover:bg-slate-100">تصفية</button>
</form>

<div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-slate-600"><tr>
            <th class="px-4 py-3 text-right">الطالب</th><th class="px-4 py-3 text-right">السنة</th><th class="px-4 py-3 text-right">المستحق</th>
            <th class="px-4 py-3 text-right">المدفوع</th><th class="px-4 py-3 text-right">المتبقي</th><th class="px-4 py-3 text-right">الحالة</th><th class="px-4 py-3"></th>
        </tr></thead>
        <tbody class="divide-y divide-slate-100">
        @forelse ($fees as $f)
            <tr>
                <td class="px-4 py-3">{{ $f->studentProfile->user->name }} <span class="text-xs text-slate-500">({{ $f->studentProfile->number_student }})</span></td>
                <td class="px-4 py-3">{{ $f->academicYear->name }}</td>
                <td class="px-4 py-3">{{ number_format($f->amount, 2) }} {{ $f->currency }}</td>
                <td class="px-4 py-3">{{ number_format($f->paid(), 2) }}</td>
                <td class="px-4 py-3">{{ number_format($f->remaining(), 2) }}</td>
                <td class="px-4 py-3">@if ($f->isSettled())<span class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs text-emerald-700">مسددة</span>@else<span class="rounded-full bg-amber-100 px-2 py-0.5 text-xs text-amber-700">غير مسددة</span>@endif</td>
                <td class="px-4 py-3 text-left"><a href="{{ route('accounting.fees.show', $f) }}" class="text-indigo-600 hover:underline">فتح</a></td>
            </tr>
        @empty
            <tr><td colspan="7" class="px-4 py-6 text-center text-slate-500">لا توجد رسوم.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
