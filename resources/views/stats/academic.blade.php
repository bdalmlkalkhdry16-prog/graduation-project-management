@extends('layouts.app')
@section('title', 'إحصاءات أكاديمية')
@section('content')
<div class="container-fluid">
    <h2 class="mb-4">إحصاءات أكاديمية</h2>
    @include('stats._overview', ['overview' => $overview])

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-4 mb-2">
        <h5 class="mb-0">نتائج المقررات</h5>
        <form method="GET" class="d-flex gap-2">
            <select name="term" onchange="this.form.submit()" class="form-select">
                @foreach ($terms as $t)
                    <option value="{{ $t->id }}" @selected($t->id == $termId)>@include('enrollments._term-label', ['term' => $t])</option>
                @endforeach
            </select>
        </form>
    </div>
    @if ($overview['pass_percentage'] === null)
        <div class="alert alert-warning small">لم تُضبط نسبة النجاح في الإعدادات الأكاديمية، لذلك لا تُحسب نسب النجاح والرسوب.
            <a href="{{ route('academic-settings.edit') }}" class="alert-link">ضبط الإعدادات</a></div>
    @endif
    <p class="small text-muted">تُحسب النسبة من النتائج المكتملة فقط (كل اختبارات الشعبة منشورة). المنسحبون مستبعدون.</p>
    <div class="card"><div class="card-body p-0"><div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead><tr><th>المقرر</th><th>الشعب</th><th>المسجَّلون</th><th>مكتملة</th><th>غير مكتملة</th><th>ناجحون</th><th>راسبون</th><th>نسبة النجاح</th></tr></thead>
            <tbody>
            @forelse ($rates as $r)
                <tr>
                    <td>{{ $r['course']->code }} — {{ $r['course']->name_ar }}</td>
                    <td>{{ $r['sections'] }}</td><td>{{ $r['enrolled'] }}</td><td>{{ $r['complete'] }}</td><td>{{ $r['incomplete'] }}</td>
                    <td>{{ $r['passed'] }}</td><td>{{ $r['failed'] }}</td>
                    <td class="fw-bold">{{ $r['pass_rate'] !== null ? $r['pass_rate'].'%' : 'غير محددة' }}</td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center text-muted py-4">لا توجد مقررات في هذا الفصل.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div></div></div>
</div>
@endsection
