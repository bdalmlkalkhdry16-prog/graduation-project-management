@auth
    @php
        $user = auth()->user();
    @endphp

    @if($user->isAdmin())
        <!-- مسارات المدير -->
        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="fas fa-tachometer-alt"></i> لوحة التحكم
        </a>
        <a href="{{ route('admin.users.index') }}" class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
            <i class="fas fa-users"></i> المستخدمين
        </a>
      
        <a href="{{ route('admin.colleges.index') }}" class="nav-link {{ request()->routeIs('admin.colleges.*') ? 'active' : '' }}">
            <i class="fas fa-university"></i> الكليات
        </a>
        <a href="{{ route('admin.departments.index') }}" class="nav-link {{ request()->routeIs('admin.departments.*') ? 'active' : '' }}">
            <i class="fas fa-building"></i> الأقسام
        </a>
        <a href="{{ route('admin.specializations.index') }}" class="nav-link {{ request()->routeIs('admin.specializations.*') ? 'active' : '' }}">
            <i class="fas fa-graduation-cap"></i> التخصصات
        </a>
        <a href="{{ route('projects.index') }}" class="nav-link {{ request()->routeIs('projects.*') ? 'active' : '' }}">
            <i class="fas fa-project-diagram"></i> المشاريع
        </a>
        <a href="{{ route('projects.archive') }}" class="nav-link {{ request()->routeIs('projects.archive') ? 'active' : '' }}">
            <i class="fas fa-archive"></i> أرشيف المشاريع
        </a>
      
        <a href="{{ route('evaluations.index') }}" class="nav-link {{ request()->routeIs('evaluations.index') ? 'active' : '' }}">
            <i class="fas fa-star"></i> سجل التقييمات
        </a>
          <a href="{{ route('admin.academic-years.index') }}" class="nav-link">
    <i class="fas fa-calendar-alt"></i> السنوات الأكاديمية
</a>
          <a href="{{ route('supervisor-requests.index') }}" class="nav-link {{ request()->routeIs('supervisor-requests.*') ? 'active' : '' }}">
    <i class="fas fa-exchange-alt"></i> طلبات تغيير المشرف
</a>

        <a href="{{ route('admin.reports.index') }}" class="nav-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
            <i class="fas fa-chart-bar"></i> التقارير
        </a>
        <a href="{{ route('help') }}" class="nav-link">
            <i class="fas fa-question-circle"></i> دليل الاستخدام
        </a>

    @elseif($user->isSupervisor())
        <!-- مسارات المشرف -->
        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="fas fa-tachometer-alt"></i> لوحة التحكم
        </a>
        <a href="{{ route('projects.index') }}" class="nav-link {{ request()->routeIs('projects.*') ? 'active' : '' }}">
            <i class="fas fa-project-diagram"></i> المشاريع
        </a>
        <a href="{{ route('projects.pending_review') }}" class="nav-link">
            <i class="fas fa-clock"></i> قيد المراجعة
        </a>
        <a href="{{ route('defense.schedule') }}" class="nav-link">
            <i class="fas fa-calendar-alt"></i> جدول المناقشات
        </a>
        <a href="{{ route('projects.archive') }}" class="nav-link {{ request()->routeIs('projects.archive') ? 'active' : '' }}">
            <i class="fas fa-archive"></i> أرشيف المشاريع
        </a>
        <a href="{{ route('evaluations.index') }}" class="nav-link {{ request()->routeIs('evaluations.index') ? 'active' : '' }}">
            <i class="fas fa-star"></i> سجل التقييمات
        </a>
        <a href="{{ route('help') }}" class="nav-link">
            <i class="fas fa-question-circle"></i> دليل الاستخدام
        </a>

    @elseif($user->isStudent())
        <!-- مسارات الطالب -->
        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="fas fa-tachometer-alt"></i> لوحة التحكم
        </a>
        <a href="{{ route('projects.index') }}" class="nav-link {{ request()->routeIs('projects.*') ? 'active' : '' }}">
            <i class="fas fa-project-diagram"></i> مشاريعي
        </a>
        <a href="{{ route('projects.create') }}" class="nav-link">
            <i class="fas fa-plus-circle"></i> مشروع جديد
        </a>
        <a href="{{ route('development-requests.index') }}" class="nav-link {{ request()->routeIs('development-requests.*') ? 'active' : '' }}">
            <i class="fas fa-code-branch"></i> طلبات التطوير
        </a>
        <a href="{{ route('supervisor-requests.index') }}" class="nav-link {{ request()->routeIs('supervisor-requests.*') ? 'active' : '' }}">
    <i class="fas fa-exchange-alt"></i> طلبات تغيير المشرف
</a>
        <a href="{{ route('projects.archive') }}" class="nav-link {{ request()->routeIs('projects.archive') ? 'active' : '' }}">
            <i class="fas fa-archive"></i> أرشيف المشاريع
        </a>
        <a href="{{ route('evaluations.index') }}" class="nav-link {{ request()->routeIs('evaluations.index') ? 'active' : '' }}">
            <i class="fas fa-star"></i> سجل تقييماتي
        </a>
        <a href="{{ route('help') }}" class="nav-link">
            <i class="fas fa-question-circle"></i> دليل الاستخدام
        </a>
    @endif
@endauth