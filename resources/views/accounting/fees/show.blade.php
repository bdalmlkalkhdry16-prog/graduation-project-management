@extends('layouts.tailwind')
@section('title', 'رسوم طالب')
@section('content')
<h1 class="mb-1 text-2xl font-bold">{{ $fee->studentProfile->user->name }}</h1>
<p class="mb-4 text-sm text-slate-500">رقم القيد {{ $fee->studentProfile->number_student }} — السنة {{ $fee->academicYear->name }}</p>
<div class="mb-6 grid gap-3 sm:grid-cols-4 text-sm">
    <div class="rounded-xl border border-slate-200 bg-white p-4"><div class="text-slate-500">المستحق</div><div class="text-lg font-bold">{{ number_format($fee->amount, 2) }} {{ $fee->currency }}</div></div>
    <div class="rounded-xl border border-slate-200 bg-white p-4"><div class="text-slate-500">الخصومات</div><div class="text-lg font-bold">{{ number_format($fee->discount(), 2) }}</div></div>
    <div class="rounded-xl border border-slate-200 bg-white p-4"><div class="text-slate-500">المدفوع</div><div class="text-lg font-bold">{{ number_format($fee->paid(), 2) }}</div></div>
    <div class="rounded-xl border border-slate-200 bg-white p-4"><div class="text-slate-500">المتبقي</div><div class="text-lg font-bold">{{ number_format($fee->remaining(), 2) }}</div></div>
</div>

@can('recordPayment', \App\Models\StudentFee::class)
    @unless ($fee->isSettled())
    <form method="POST" action="{{ route('accounting.payments.store', $fee) }}" class="mb-6 flex flex-wrap items-end gap-3 rounded-xl border border-slate-200 bg-white p-4 text-sm">
        @csrf
        <div><label class="mb-1 block">مبلغ الدفعة ({{ $fee->currency }})</label><input type="number" step="0.01" min="0.01" name="amount" required class="w-40 rounded-lg border border-slate-300 px-3 py-2"></div>
        <div><label class="mb-1 block">تاريخ الدفع</label><input type="date" name="paid_at" value="{{ now()->toDateString() }}" max="{{ now()->toDateString() }}" required class="rounded-lg border border-slate-300 px-3 py-2"></div>
        <div class="grow"><label class="mb-1 block">ملاحظات</label><input name="notes" class="w-full rounded-lg border border-slate-300 px-3 py-2"></div>
        <button class="rounded-lg bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700">تسجيل الدفعة</button>
    </form>
    @endunless
@endcan

@if ($fee->discounts->isNotEmpty() || auth()->user()->can('discount', \App\Models\StudentFee::class))
<h2 class="mb-2 font-bold">الخصومات والإعفاءات</h2>
<div class="mb-6 rounded-xl border border-slate-200 bg-white p-4 text-sm">
    <ul class="space-y-2">
        @foreach ($fee->discounts as $d)
            <li @class(['text-slate-400 line-through' => $d->isRevoked()])>
                {{ number_format($d->amount, 2) }} {{ $fee->currency }} ({{ $d->type === 'percent' ? $d->value.'%' : 'مبلغ' }}) — {{ $d->reason }}@if ($d->isRevoked()) <span class="no-underline text-red-600">(ملغى: {{ $d->revoke_reason }})</span>@endif
                @can('discount', \App\Models\StudentFee::class)
                    @unless ($d->isRevoked())
                        <form method="POST" action="{{ route('accounting.discounts.revoke', $d) }}" class="mt-1 inline-flex gap-1">@csrf
                            <input name="reason" required minlength="5" placeholder="سبب الإلغاء" class="w-32 rounded border border-slate-300 px-2 py-1">
                            <button class="rounded bg-red-600 px-2 py-1 text-white">إلغاء الخصم</button>
                        </form>
                    @endunless
                @endcan
            </li>
        @endforeach
    </ul>
    @can('discount', \App\Models\StudentFee::class)
        <form method="POST" action="{{ route('accounting.discounts.store', $fee) }}" class="mt-3 flex flex-wrap items-end gap-3">@csrf
            <div><label class="mb-1 block">النوع</label><select name="type" class="rounded-lg border border-slate-300 px-3 py-2"><option value="amount">مبلغ</option><option value="percent">نسبة %</option></select></div>
            <div><label class="mb-1 block">القيمة</label><input type="number" step="0.01" min="0.01" name="value" required class="w-32 rounded-lg border border-slate-300 px-3 py-2"></div>
            <div class="grow"><label class="mb-1 block">السبب (منحة، إعفاء...)</label><input name="reason" required minlength="5" class="w-full rounded-lg border border-slate-300 px-3 py-2"></div>
            <button class="rounded-lg bg-indigo-600 px-4 py-2 text-white">إضافة خصم</button>
        </form>
    @endcan
</div>
@endif

@can('releaseResults', \App\Models\StudentFee::class)
    <form method="POST" action="{{ route('accounting.fees.release-results', $fee) }}" class="mb-6 flex flex-wrap items-end gap-3 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm">
        @csrf
        @if ($fee->resultsReleased())
            <p class="grow">رُفع الحجب استثناءً بتاريخ {{ $fee->results_released_at->format('Y-m-d') }} — السبب: {{ $fee->results_release_reason }}</p>
            <input type="hidden" name="release" value="0">
            <button class="rounded-lg bg-slate-700 px-4 py-2 text-white">إعادة الحجب</button>
        @else
            <div class="grow"><label class="mb-1 block">استثناء إداري: رفع حجب النتائج رغم عدم السداد (سبب مطلوب)</label><input name="reason" required minlength="5" class="w-full rounded-lg border border-slate-300 px-3 py-2"></div>
            <input type="hidden" name="release" value="1">
            <button class="rounded-lg bg-amber-600 px-4 py-2 text-white">رفع الحجب</button>
        @endif
    </form>
@endcan

<h2 class="mb-2 font-bold">الدفعات</h2>
<div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-slate-600"><tr><th class="px-4 py-3 text-right">السند</th><th class="px-4 py-3 text-right">التاريخ</th><th class="px-4 py-3 text-right">المبلغ</th><th class="px-4 py-3 text-right">سجّلها</th><th class="px-4 py-3"></th></tr></thead>
        <tbody class="divide-y divide-slate-100">
        @forelse ($fee->payments as $p)
            <tr @class(['bg-slate-50 text-slate-400 line-through' => $p->isVoided()])>
                <td class="px-4 py-3">{{ $p->receipt_number }}@if ($p->isVoided()) <span class="no-underline text-red-600">(ملغاة: {{ $p->void_reason }})</span>@endif</td><td class="px-4 py-3">{{ $p->paid_at->format('Y-m-d') }}</td>
                <td class="px-4 py-3">{{ number_format($p->amount, 2) }} {{ $fee->currency }}</td><td class="px-4 py-3">{{ $p->recordedBy?->name }}</td>
                <td class="px-4 py-3 text-left">
                    <a href="{{ route('payments.receipt', $p) }}" class="text-indigo-600 hover:underline">سند القبض</a>
                    @can('voidPayment', \App\Models\StudentFee::class)
                        @unless ($p->isVoided())
                            <form method="POST" action="{{ route('payments.void', $p) }}" class="mt-1 flex gap-1">@csrf
                                <input name="reason" required minlength="5" placeholder="سبب الإلغاء" class="w-32 rounded border border-slate-300 px-2 py-1">
                                <button class="rounded bg-red-600 px-2 py-1 text-white">إلغاء</button>
                            </form>
                        @endunless
                    @endcan
                </td>
            </tr>
        @empty
            <tr><td colspan="5" class="px-4 py-6 text-center text-slate-500">لا توجد دفعات.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
