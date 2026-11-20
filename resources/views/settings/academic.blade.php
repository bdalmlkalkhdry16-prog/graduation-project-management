@extends('layouts.tailwind')
@section('title', 'الإعدادات الأكاديمية')
@section('content')
<h1 class="text-2xl font-bold mb-2">الإعدادات الأكاديمية</h1>
<p class="mb-6 text-sm text-slate-500">لا توجد قيم افتراضية: ما لم يُضبط السلم أو نسبة النجاح لا تُحسب التقديرات أو المعدلات أو النجاح والرسوب.</p>
<form method="POST" action="{{ route('academic-settings.update') }}" class="max-w-2xl space-y-6 rounded-xl border border-slate-200 bg-white p-6">
    @csrf @method('PUT')
    <div>
        <label class="block text-sm mb-1">نسبة النجاح في المقرر (%) — اتركها فارغة إن لم تُحدَّد</label>
        <input type="number" step="0.01" min="0" max="100" name="pass_percentage" value="{{ old('pass_percentage', $passPercentage) }}" class="w-40 rounded-lg border border-slate-300 px-3 py-2">
    </div>
    <div>
        <label class="block text-sm mb-1">مهلة التظلم على الدرجة (بالأيام من نشر النتيجة) — فارغة = بلا مهلة</label>
        <input type="number" min="1" max="365" name="appeal_window_days" value="{{ old('appeal_window_days', $appealWindowDays) }}" class="w-40 rounded-lg border border-slate-300 px-3 py-2">
    </div>
    <div>
        <h2 class="font-bold mb-2">سلم التقديرات</h2>
        <p class="text-xs text-slate-500 mb-2">الصفوف الفارغة تُتجاهل. يجب أن يتضمن السلم تقديرًا حده الأدنى 0.</p>
        @php($rows = old('scale', $scale))
        @for ($i = 0; $i < 10; $i++)
            <div class="mb-2 grid grid-cols-3 gap-2">
                <input name="scale[{{ $i }}][letter]" value="{{ $rows[$i]['letter'] ?? '' }}" placeholder="الرمز" class="rounded-lg border border-slate-300 px-2 py-1.5">
                <input type="number" step="0.01" name="scale[{{ $i }}][min]" value="{{ $rows[$i]['min'] ?? '' }}" placeholder="الحد الأدنى %" class="rounded-lg border border-slate-300 px-2 py-1.5">
                <input type="number" step="0.01" name="scale[{{ $i }}][points]" value="{{ $rows[$i]['points'] ?? '' }}" placeholder="النقاط (اختياري)" class="rounded-lg border border-slate-300 px-2 py-1.5">
            </div>
        @endfor
    </div>
    <button class="rounded-lg bg-indigo-600 px-5 py-2 text-white hover:bg-indigo-700">حفظ</button>
</form>
@endsection
