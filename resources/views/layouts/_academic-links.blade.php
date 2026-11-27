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
    $links = \App\Support\NavLinks::academic($u);
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
