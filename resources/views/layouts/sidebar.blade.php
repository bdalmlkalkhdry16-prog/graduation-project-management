@auth
    @php
        $user = auth()->user();
        $r = fn (string $route, string $icon, string $label, string $pattern) => [$route, $icon, $label, $pattern];

        $groups = [];
        $add = function (string $title, string $icon, array $items) use (&$groups) {
            if ($items === []) return;
            $groups[$title]['icon'] = $icon;
            $groups[$title]['items'] = array_merge($groups[$title]['items'] ?? [], $items);
        };

        // 1) الهيكل الأكاديمي (المدير)
        if ($user->isAdmin()) {
            $add('إدارة الهيكل الأكاديمي', 'fa-layer-group', [
                $r('admin.colleges.index', 'fa-university', 'الكليات', 'admin.colleges.*'),
                $r('admin.departments.index', 'fa-building', 'الأقسام', 'admin.departments.*'),
                $r('admin.specializations.index', 'fa-graduation-cap', 'التخصصات', 'admin.specializations.*'),
                $r('admin.academic-years.index', 'fa-calendar-alt', 'السنوات الأكاديمية', 'admin.academic-years.*'),
            ]);
        }

        // 2) وحدات النظام الأكاديمي حسب الصلاحيات
        foreach (\App\Support\NavLinks::academic($user) as [$route, $icon, $label, $pattern, $grp]) {
            $groupIcons = [
                'الشؤون الأكاديمية' => 'fa-book-reader', 'شؤون الطلاب' => 'fa-users', 'الجداول الدراسية' => 'fa-calendar-week',
                'الاختبارات والنتائج' => 'fa-clipboard-list', 'الخدمات الإلكترونية' => 'fa-th-large', 'الإدارة المالية' => 'fa-coins',
                'التقارير والإحصائيات' => 'fa-chart-bar', 'إعدادات النظام' => 'fa-cog',
            ];
            $add($grp, $groupIcons[$grp] ?? 'fa-folder', [[$route, $icon, $label, $pattern]]);
        }

        // 3) مشاريع التخرج (حسب الدور)
        if ($user->isAdmin()) {
            $add('مشاريع التخرج', 'fa-project-diagram', [
                $r('projects.index', 'fa-project-diagram', 'المشاريع', 'projects.*'),
                $r('projects.archive', 'fa-archive', 'أرشيف المشاريع', 'projects.archive'),
                $r('evaluations.index', 'fa-star', 'سجل التقييمات', 'evaluations.index'),
                $r('supervisor-requests.index', 'fa-exchange-alt', 'طلبات تغيير المشرف', 'supervisor-requests.*'),
            ]);
            $add('التقارير والإحصائيات', 'fa-chart-bar', [$r('admin.reports.index', 'fa-chart-bar', 'التقارير', 'admin.reports.*')]);
            $add('إعدادات النظام', 'fa-cog', [$r('admin.users.index', 'fa-users', 'المستخدمين', 'admin.users.*')]);
        } elseif ($user->isSupervisor()) {
            $add('مشاريع التخرج', 'fa-project-diagram', [
                $r('projects.index', 'fa-project-diagram', 'المشاريع', 'projects.*'),
                $r('projects.pending_review', 'fa-clock', 'قيد المراجعة', 'projects.pending_review'),
                $r('defense.schedule', 'fa-calendar-alt', 'جدول المناقشات', 'defense.schedule'),
                $r('projects.archive', 'fa-archive', 'أرشيف المشاريع', 'projects.archive'),
                $r('evaluations.index', 'fa-star', 'سجل التقييمات', 'evaluations.index'),
            ]);
        } elseif ($user->isStudent()) {
            $add('مشاريع التخرج', 'fa-project-diagram', [
                $r('projects.index', 'fa-project-diagram', 'مشاريعي', 'projects.*'),
                $r('projects.create', 'fa-plus-circle', 'مشروع جديد', 'projects.create'),
                $r('development-requests.index', 'fa-code-branch', 'طلبات التطوير', 'development-requests.*'),
                $r('supervisor-requests.index', 'fa-exchange-alt', 'طلبات تغيير المشرف', 'supervisor-requests.*'),
                $r('projects.archive', 'fa-archive', 'أرشيف المشاريع', 'projects.archive'),
                $r('evaluations.index', 'fa-star', 'سجل تقييماتي', 'evaluations.index'),
            ]);
        }

        $order = ['إدارة الهيكل الأكاديمي', 'شؤون الطلاب', 'الشؤون الأكاديمية', 'الجداول الدراسية', 'الاختبارات والنتائج',
            'الخدمات الإلكترونية', 'الإدارة المالية', 'مشاريع التخرج', 'التقارير والإحصائيات', 'إعدادات النظام'];
        $ordered = [];
        foreach ($order as $t) { if (isset($groups[$t])) $ordered[$t] = $groups[$t]; }
        foreach ($groups as $t => $g) { $ordered[$t] ??= $g; }
    @endphp

    <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
        <i class="fas fa-home"></i> لوحة التحكم
    </a>

    @foreach ($ordered as $title => $g)
        @php
            $open = collect($g['items'])->contains(fn ($i) => request()->routeIs($i[3]));
            $gid = 'nav-g-' . $loop->index;
        @endphp
        <a href="#{{ $gid }}" class="nav-link nav-group-toggle {{ $open ? 'open' : '' }}" data-bs-toggle="collapse"
           aria-expanded="{{ $open ? 'true' : 'false' }}">
            <i class="fas {{ $g['icon'] }}"></i> {{ $title }}
            <i class="fas fa-chevron-down chev"></i>
        </a>
        <div class="collapse nav-sub {{ $open ? 'show' : '' }}" id="{{ $gid }}">
            @foreach ($g['items'] as [$route, $icon, $label, $pattern])
                <a href="{{ route($route) }}" class="nav-link {{ request()->routeIs($pattern) ? 'active' : '' }}">
                    <i class="fas {{ $icon }}"></i> {{ $label }}
                </a>
            @endforeach
        </div>
    @endforeach

    <a href="{{ route('help') }}" class="nav-link mt-2">
        <i class="fas fa-question-circle"></i> دليل الاستخدام
    </a>
@endauth
