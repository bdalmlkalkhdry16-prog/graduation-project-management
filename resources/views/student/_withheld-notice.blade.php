@if (isset($withheld) && $withheld->isNotEmpty())
    <div class="alert alert-danger d-print-none">
        <i class="fas fa-lock me-1"></i>
        تم حجب نتائج {{ $withheld->count() > 1 ? 'السنوات الدراسية' : 'السنة الدراسية' }}
        <b>{{ $withheld->pluck('name')->join('، ') }}</b> لعدم سداد الرسوم كاملة.
        <a href="{{ route('student.fees.index') }}" class="alert-link">اطّلع على رسومك</a> وراجع المحاسب لتسوية المستحق.
    </div>
@endif
