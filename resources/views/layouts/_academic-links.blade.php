{{--
    روابط الوحدات الأكاديمية (Phase 5–7 والخدمات الإلكترونية والمالية) حسب صلاحيات المستخدم.
    مصدر واحد تستخدمه القائمة الجانبية القديمة (variant=sidebar) ولوحات التحكم (variant=cards)،
    فلا تتباعد القوائم. الشاشات الجديدة صارت بنفس تنسيق النظام القديم (layouts.app / Bootstrap RTL) وتظهر داخل قائمته الجانبية.
--}}
@php
    $u = auth()->user();
    $isAdmin = $u->isAdmin() || $u->hasRole('admin');
    // المدير يملك كل الصلاحيات (الـ middleware والسياسات كلاهما يتجاوزان له).
    $has = fn (string $p) => $isAdmin || $u->hasPermission($p);
    $links = array_values(array_filter([
        // الطالب
        $has('enrollments.viewOwn') ? ['student.enrollments.index', 'fa-clipboard-list', 'تسجيلاتي', 'student.enrollments.*', 'الشؤون الأكاديمية'] : null,
        $has('schedules.viewOwn') && $u->hasPermission('enrollments.viewOwn') ? ['student.schedule.index', 'fa-calendar-week', 'جدولي', 'student.schedule.*', 'الشؤون الأكاديمية'] : null,
        $u->hasPermission('attendance.viewOwn') ? ['student.attendance.index', 'fa-user-check', 'حضوري', 'student.attendance.*', 'الشؤون الأكاديمية'] : null,
        $u->hasPermission('results.viewOwn') ? ['student.results.index', 'fa-poll', 'نتائجي', 'student.results.*', 'الاختبارات والنتائج'] : null,
        $u->hasPermission('results.viewOwn') ? ['student.academic-record', 'fa-book-reader', 'سجلي الأكاديمي', 'student.academic-record', 'الشؤون الأكاديمية'] : null,
        $u->hasPermission('results.viewOwn') ? ['student.transcript', 'fa-file-alt', 'كشف الدرجات', 'student.transcript*', 'الاختبارات والنتائج'] : null,
        $u->hasPermission('fees.viewOwn') ? ['student.fees.index', 'fa-money-bill-wave', 'رسومي', 'student.fees.*', 'الإدارة المالية'] : null,
        $u->hasPermission('service-requests.manageOwn') ? ['student.service-requests.index', 'fa-concierge-bell', 'الخدمات الإلكترونية', 'student.service-requests.*', 'الخدمات الإلكترونية'] : null,
        // عضو هيئة التدريس
        $u->hasPermission('schedules.viewOwn') && ! $u->hasPermission('enrollments.viewOwn') ? ['faculty.schedule.index', 'fa-calendar-week', 'جدولي التدريسي', 'faculty.schedule.*', 'الشؤون الأكاديمية'] : null,
        $u->hasPermission('enrollments.viewOwnSections') ? ['faculty.enrollments.index', 'fa-users', 'طلاب شعبي', 'faculty.enrollments.*', 'الشؤون الأكاديمية'] : null,
        $u->hasPermission('attendance.manageOwn') ? ['faculty.attendance.index', 'fa-user-check', 'حضور شعبي', 'faculty.attendance.*', 'الشؤون الأكاديمية'] : null,
        \Illuminate\Support\Facades\Gate::allows('viewAny', \App\Models\Exam::class) ? ['exams.index', 'fa-pen-alt', 'الاختبارات والدرجات', 'exams.*', 'الاختبارات والنتائج'] : null,
        // الموظفون
        $has('student-profiles.manage') ? ['staff.student-profiles.index', 'fa-id-card', 'ملفات الطلاب', 'staff.student-profiles.*', 'شؤون الطلاب'] : null,
        $has('enrollments.manage') ? ['staff.enrollments.index', 'fa-user-plus', 'التسجيل في الشعب', 'staff.enrollments.index', 'شؤون الطلاب'] : null,
        $has('enrollments.manage') ? ['staff.enrollments.students', 'fa-address-book', 'سجل الطلاب', 'staff.enrollments.students', 'شؤون الطلاب'] : null,
        $has('rooms.manage') ? ['staff.rooms.index', 'fa-door-open', 'القاعات', 'staff.rooms.*', 'الجداول الدراسية'] : null,
        $has('schedules.manage') ? ['staff.schedules.index', 'fa-calendar-alt', 'الجداول الدراسية', 'staff.schedules.*', 'الجداول الدراسية'] : null,
        $has('attendance.viewAll') ? ['staff.attendance.index', 'fa-clipboard-check', 'الحضور', 'staff.attendance.*', 'شؤون الطلاب'] : null,
        $has('service-requests.manageAll') ? ['staff.service-requests.index', 'fa-concierge-bell', 'طلبات الخدمات', 'staff.service-requests.*', 'الخدمات الإلكترونية'] : null,
        $has('results.review') ? ['results.review', 'fa-tasks', 'مراجعة النتائج', 'results.review', 'الاختبارات والنتائج'] : null,
        $has('results.publish') ? ['results.publish', 'fa-bullhorn', 'نشر النتائج', 'results.publish', 'الاختبارات والنتائج'] : null,
        $has('academic-records.viewAll') ? ['academic-stats', 'fa-chart-line', 'إحصاءات أكاديمية', 'academic-stats', 'التقارير والإحصائيات'] : null,
        \Illuminate\Support\Facades\Gate::allows('viewAny', \App\Models\StudentFee::class) ? ['accounting.fees.index', 'fa-cash-register', 'رسوم الطلاب والمدفوعات', 'accounting.fees.*', 'الإدارة المالية'] : null,
        \Illuminate\Support\Facades\Gate::allows('viewAny', \App\Models\StudentFee::class) ? ['accounting.reports.fees', 'fa-chart-pie', 'تقارير الرسوم', 'accounting.reports.*', 'الإدارة المالية'] : null,
        \Illuminate\Support\Facades\Gate::allows('viewAny', \App\Models\FeeSchedule::class) ? ['fee-schedules.index', 'fa-tags', 'أسعار الرسوم', 'fee-schedules.*', 'الإدارة المالية'] : null,
        \Illuminate\Support\Facades\Gate::allows('manage', \App\Models\AcademicSetting::class) ? ['academic-settings.edit', 'fa-sliders-h', 'الإعدادات الأكاديمية', 'academic-settings.*', 'إعدادات النظام'] : null,
    ]));
@endphp

@php
    // روابط موجودة أصلًا في اللوحة المضمَّنة فيها (لا تكرار)
    $links = array_values(array_filter($links, fn ($l) => ! in_array($l[0], $exclude ?? [], true)));
@endphp

@if ($links !== [])
    @if (($variant ?? 'sidebar') === 'sidebar')
        @foreach (collect($links)->groupBy(4) as $group => $items)
            <div class="nav-group-title">{{ $group }}</div>
            @foreach ($items as [$route, $icon, $label, $pattern])
                <a href="{{ route($route) }}" class="nav-link {{ request()->routeIs($pattern) ? 'active' : '' }}">
                    <i class="fas {{ $icon }}"></i> {{ $label }}
                </a>
            @endforeach
        @endforeach
    @else
        <div class="row g-3 mb-4">
            <div class="col-12"><h5 class="mb-0"><i class="fas fa-th-large me-2"></i>الخدمات الأكاديمية</h5></div>
            @foreach ($links as [$route, $icon, $label])
                <div class="col-6 col-md-4 col-lg-3">
                    <a href="{{ route($route) }}" class="card h-100 text-decoration-none text-dark shadow-sm border-0">
                        <div class="card-body text-center py-4">
                            <i class="fas {{ $icon }} fa-2x text-primary mb-2"></i>
                            <div class="fw-semibold">{{ $label }}</div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    @endif
@endif
