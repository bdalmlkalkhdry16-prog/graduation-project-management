@extends('layouts.tailwind')
@section('title', 'الرسوم الدراسية')
@section('content')
<div class="mb-4 flex items-center justify-between">
    <h1 class="text-2xl font-bold">الرسوم الدراسية</h1>
    @can('manage', \App\Models\FeeSchedule::class)
        <a href="{{ route('fee-schedules.create') }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700">سعر جديد</a>
    @endcan
</div>
<p class="mb-4 text-sm text-slate-500">لكل قسم سعره الخاص عن كل سنة دراسية، بالريال اليمني (القديم) أو بالدولار.</p>
<div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-slate-600"><tr>
            <th class="px-4 py-3 text-right">السنة الدراسية</th><th class="px-4 py-3 text-right">القسم</th>
            <th class="px-4 py-3 text-right">المبلغ</th><th class="px-4 py-3 text-right">العملة</th><th class="px-4 py-3"></th>
        </tr></thead>
        <tbody class="divide-y divide-slate-100">
        @forelse ($schedules as $s)
            <tr>
                <td class="px-4 py-3">{{ $s->academicYear->name }}</td>
                <td class="px-4 py-3">{{ $s->department->name_ar }}</td>
                <td class="px-4 py-3">{{ number_format($s->amount, 2) }}</td>
                <td class="px-4 py-3">{{ \App\Models\FeeSchedule::currencyLabel($s->currency) }}</td>
                <td class="px-4 py-3 text-left">
                    @can('manage', \App\Models\FeeSchedule::class)
                        <a href="{{ route('fee-schedules.edit', $s) }}" class="text-indigo-600 hover:underline">تعديل</a>
                        <form method="POST" action="{{ route('fee-schedules.destroy', $s) }}" class="inline" onsubmit="return confirm('حذف هذا السعر؟')">@csrf @method('DELETE')
                            <button class="mr-2 text-red-600 hover:underline">حذف</button></form>
                    @endcan
                </td>
            </tr>
        @empty
            <tr><td colspan="5" class="px-4 py-6 text-center text-slate-500">لم تُحدَّد أسعار بعد.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
