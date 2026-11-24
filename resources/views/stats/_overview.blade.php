@php
    $cards = [
        ['الطلاب', $overview['students'], 'primary', 'fa-user-graduate', null],
        ['التسجيلات النشطة', $overview['active_enrollments'], 'success', 'fa-clipboard-list', null],
        ['الشعب', $overview['sections'], 'secondary', 'fa-layer-group', null],
        ['نتائج بانتظار المراجعة', $overview['exams_to_review'], 'warning', 'fa-hourglass-half', route('results.review')],
        ['نتائج بانتظار النشر', $overview['exams_to_publish'], 'info', 'fa-bullhorn', route('results.publish')],
        ['اختبارات منشورة', $overview['exams_published'], 'success', 'fa-check-circle', null],
        ['طلبات خدمة مفتوحة', $overview['open_requests'], 'danger', 'fa-inbox', route('staff.service-requests.index')],
        ['رسوم غير مسددة', $overview['unsettled_fees'], 'danger', 'fa-money-bill-wave', route('accounting.reports.fees')],
    ];
@endphp
<div class="row g-3">
    @foreach ($cards as [$label, $value, $color, $icon, $link])
        <div class="col-6 col-lg-3">
            @if ($link)
                <a href="{{ $link }}" class="card h-100 text-decoration-none text-reset border-{{ $color }} border-opacity-50">
                    @include('stats._card', compact('label', 'value', 'color', 'icon'))
                </a>
            @else
                <div class="card h-100 border-{{ $color }} border-opacity-50">
                    @include('stats._card', compact('label', 'value', 'color', 'icon'))
                </div>
            @endif
        </div>
    @endforeach
</div>
