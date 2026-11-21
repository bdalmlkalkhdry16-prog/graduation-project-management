@if (isset($withheld) && $withheld->isNotEmpty())
    <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-800 print:hidden">
        تم حجب نتائج {{ $withheld->count() > 1 ? 'السنوات الدراسية' : 'السنة الدراسية' }}
        <b>{{ $withheld->pluck('name')->join('، ') }}</b> لعدم سداد الرسوم كاملة.
        <a href="{{ route('student.fees.index') }}" class="underline">اطّلع على رسومك</a> وراجع المحاسب لتسوية المستحق.
    </div>
@endif
