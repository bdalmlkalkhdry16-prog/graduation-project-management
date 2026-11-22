@extends('layouts.app')
@section('title', 'رصد درجات '.$exam->name)
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h2 class="mb-0">رصد الدرجات — {{ $exam->section->course->name_ar }} (شعبة {{ $exam->section->code }})</h2>
        <a href="{{ route('exams.show', $exam) }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-right me-1"></i> {{ $exam->name }}</a>
    </div>
    <p class="text-muted">{{ $exam->name }} · الدرجة النهائية {{ $exam->max_score + 0 }} · @include('exams._status', ['status' => $exam->result_status])</p>

    @include('layouts._errors')

    @php($canEnter = $exam->isEditable() && auth()->user()->can('enterGrades', $exam))
    @php($canCorrect = in_array($exam->result_status, ['approved', 'published']) && auth()->user()->can('correctGrades', $exam))

    @if (! $exam->isEditable())
        <div class="alert alert-warning">قائمة الطلاب مثبتة منذ التقديم ولا تتأثر بتغيّر التسجيل لاحقًا. النتائج مقفلة ({{ \App\Models\Exam::statusLabel($exam->result_status) }}). التعديل يتم عبر التصحيح المعتمد فقط لمن يملك الصلاحية.</div>
    @endif

    <form method="POST" action="{{ route('exams.grades.update', $exam) }}">
        @csrf @method('PUT')
        <div class="card"><div class="card-body p-0"><div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead><tr><th>الرقم الأكاديمي</th><th>الطالب</th><th>الدرجة (من {{ $exam->max_score + 0 }})</th><th>غياب</th><th></th></tr></thead>
                <tbody>
                @forelse ($roster as $enrollment)
                    @php($grade = $grades->get($enrollment->id))
                    <tr>
                        <td>{{ $enrollment->studentProfile->number_student }}</td>
                        <td>{{ $enrollment->studentProfile->user->name }}</td>
                        <td>
                            @if ($canEnter)
                                <input type="number" step="0.01" min="0" max="{{ $exam->max_score }}" name="scores[{{ $enrollment->id }}]"
                                       value="{{ old('scores.'.$enrollment->id, $grade?->score) }}" class="form-control form-control-sm w-auto">
                            @else
                                {{ $grade?->score !== null ? $grade->score + 0 : '—' }}
                            @endif
                        </td>
                        <td>
                            @if ($canEnter)
                                <label class="small"><input type="checkbox" class="form-check-input" name="absent[{{ $enrollment->id }}]" value="1" @checked(old('absent.'.$enrollment->id, $grade?->isAbsentUnresolved()))> غياب (غير محسوم)</label>
                            @elseif ($grade?->isAbsentUnresolved())
                                <span class="badge bg-danger">غياب غير محسوم — يمنع النشر</span>
                            @endif
                        </td>
                        <td>
                            @if ($grade && $grade->changes->isNotEmpty())
                                <details class="small">
                                    <summary class="text-primary" style="cursor:pointer">سجل التعديلات ({{ $grade->changes->count() }})</summary>
                                    <ul class="mt-2 text-muted ps-3">
                                        @foreach ($grade->changes as $change)
                                            <li>{{ $change->created_at->format('Y-m-d H:i') }} — {{ $change->changedBy->name ?? '—' }}:
                                                {{ $change->old_score !== null ? $change->old_score + 0 : ($change->old_status === 'absent_unresolved' ? 'غياب' : '∅') }} ← {{ $change->new_score !== null ? $change->new_score + 0 : 'غياب' }}
                                                ({{ ['entered' => 'رصد', 'updated' => 'تعديل', 'corrected' => 'تصحيح معتمد'][$change->action] }}){{ $change->reason ? ' — '.$change->reason : '' }}</li>
                                        @endforeach
                                    </ul>
                                </details>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">لا يوجد طلاب في قائمة هذا الاختبار.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div></div></div>
        @if ($canEnter && $roster->isNotEmpty())
            <button class="btn btn-primary mt-3"><i class="fas fa-save me-1"></i> حفظ كمسودة</button>
            <span class="small text-muted ms-2">لا تُمسح درجة سابقة بترك الحقل فارغًا. الغياب لا يُحوَّل إلى صفر ولا يمكن نشر نتيجة تحتوي غيابًا غير محسوم.</span>
        @endif
    </form>

    @if ($canCorrect)
        <div class="card mt-4">
            <div class="card-header">تصحيح معتمد لدرجة</div>
            <div class="card-body">
                @foreach ($roster as $enrollment)
                    @php($grade = $grades->get($enrollment->id))
                    @if ($grade)
                        <form method="POST" action="{{ route('exams.grades.correct', [$exam, $grade]) }}" class="row g-2 align-items-center mb-2">
                            @csrf
                            <div class="col-md-3">{{ $enrollment->studentProfile->user->name }} ({{ $grade->score !== null ? $grade->score + 0 : 'غياب غير محسوم' }})</div>
                            <div class="col-md-2"><input type="number" step="0.01" min="0" max="{{ $exam->max_score }}" name="new_score" placeholder="الدرجة الجديدة" required class="form-control form-control-sm"></div>
                            <div class="col-md"><input name="reason" placeholder="سبب التصحيح (إلزامي)" required class="form-control form-control-sm"></div>
                            <div class="col-auto"><button class="btn btn-sm btn-outline-secondary">تصحيح</button></div>
                        </form>
                    @endif
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection
