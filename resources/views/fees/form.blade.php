@extends('layouts.tailwind')
@section('title', $schedule->exists ? 'تعديل سعر الرسوم' : 'سعر رسوم جديد')
@section('content')
<h1 class="mb-4 text-2xl font-bold">{{ $schedule->exists ? 'تعديل سعر الرسوم' : 'سعر رسوم جديد' }}</h1>
<form method="POST" action="{{ $schedule->exists ? route('fee-schedules.update', $schedule) : route('fee-schedules.store') }}" class="max-w-xl space-y-4 rounded-xl border border-slate-200 bg-white p-6">
    @csrf @if ($schedule->exists) @method('PUT') @endif
    <div>
        <label class="mb-1 block text-sm">القسم</label>
        <select name="department_id" required class="w-full rounded-lg border border-slate-300 px-3 py-2">
            <option value="">— اختر —</option>
            @foreach ($departments as $d)<option value="{{ $d->id }}" @selected((string) old('department_id', $schedule->department_id) === (string) $d->id)>{{ $d->name_ar }}</option>@endforeach
        </select>
    </div>
    <div>
        <label class="mb-1 block text-sm">السنة الدراسية</label>
        <select name="academic_year_id" required class="w-full rounded-lg border border-slate-300 px-3 py-2">
            <option value="">— اختر —</option>
            @foreach ($years as $y)<option value="{{ $y->id }}" @selected((string) old('academic_year_id', $schedule->academic_year_id) === (string) $y->id)>{{ $y->name }}</option>@endforeach
        </select>
    </div>
    <div class="grid grid-cols-2 gap-3">
        <div>
            <label class="mb-1 block text-sm">المبلغ</label>
            <input type="number" step="0.01" min="0.01" name="amount" value="{{ old('amount', $schedule->amount) }}" required class="w-full rounded-lg border border-slate-300 px-3 py-2">
        </div>
        <div>
            <label class="mb-1 block text-sm">العملة</label>
            <select name="currency" required class="w-full rounded-lg border border-slate-300 px-3 py-2">
                @foreach (\App\Models\FeeSchedule::CURRENCIES as $code => $label)<option value="{{ $code }}" @selected(old('currency', $schedule->currency) === $code)>{{ $label }}</option>@endforeach
            </select>
        </div>
    </div>
    <div>
        <label class="mb-1 block text-sm">ملاحظات</label>
        <textarea name="notes" rows="2" class="w-full rounded-lg border border-slate-300 px-3 py-2">{{ old('notes', $schedule->notes) }}</textarea>
    </div>
    <div class="flex gap-2">
        <button class="rounded-lg bg-indigo-600 px-5 py-2 text-white hover:bg-indigo-700">حفظ</button>
        <a href="{{ route('fee-schedules.index') }}" class="rounded-lg border border-slate-300 px-5 py-2 hover:bg-slate-100">إلغاء</a>
    </div>
</form>
@endsection
