@extends('layouts.app')

@section('title', 'لوحة تحكم المدير')

@section('content')
<div class="container-fluid">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">لوحة تحكم المدير</h2>
            <p class="text-muted mb-0">نظرة عامة على النظام والهيكل الأكاديمي</p>
        </div>

        <div class="text-muted">
            {{ now()->format('Y-m-d') }}
        </div>
    </div>

    {{-- الإحصائيات الرئيسية --}}
    <div class="row g-4 mb-4">

        {{-- الطلاب --}}
        <div class="col-xl-3 col-md-6">
            <div class="card stat-card text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-2">الطلاب</h6>
                            <h3 class="mb-0">
                                {{ number_format($stats['total_students']) }}
                            </h3>
                        </div>
                        <i class="fas fa-user-graduate fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- أعضاء هيئة التدريس --}}
        <div class="col-xl-3 col-md-6">
            <div class="card stat-card bg-success text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-2">أعضاء هيئة التدريس</h6>
                            <h3 class="mb-0">
                                {{ number_format($stats['total_faculty']) }}
                            </h3>
                        </div>
                        <i class="fas fa-chalkboard-teacher fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- الموظفون --}}
        <div class="col-xl-3 col-md-6">
            <div class="card stat-card bg-info text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-2">الموظفون</h6>
                            <h3 class="mb-0">
                                {{ number_format($stats['total_staff']) }}
                            </h3>
                        </div>
                        <i class="fas fa-user-tie fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- المشاريع --}}
        <div class="col-xl-3 col-md-6">
            <div class="card stat-card bg-warning text-dark h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-2">مشاريع التخرج</h6>
                            <h3 class="mb-0">
                                {{ number_format($stats['total_projects']) }}
                            </h3>
                        </div>
                        <i class="fas fa-project-diagram fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- الهيكل الأكاديمي --}}
    <div class="row g-4 mb-4">

        <div class="col-xl-3 col-md-6">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="text-muted">الكليات</h6>
                            <h3>{{ number_format($stats['total_colleges']) }}</h3>
                        </div>
                        <i class="fas fa-university fa-2x text-primary"></i>
                    </div>

                    <a href="{{ route('admin.colleges.index') }}"
                       class="btn btn-outline-primary btn-sm mt-3">
                        إدارة الكليات
                    </a>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="text-muted">الأقسام</h6>
                            <h3>{{ number_format($stats['total_departments']) }}</h3>
                        </div>
                        <i class="fas fa-sitemap fa-2x text-primary"></i>
                    </div>

                    <a href="{{ route('admin.departments.index') }}"
                       class="btn btn-outline-primary btn-sm mt-3">
                        إدارة الأقسام
                    </a>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="text-muted">التخصصات</h6>
                            <h3>{{ number_format($stats['total_specializations']) }}</h3>
                        </div>
                        <i class="fas fa-graduation-cap fa-2x text-primary"></i>
                    </div>

                    <a href="{{ route('admin.specializations.index') }}"
                       class="btn btn-outline-primary btn-sm mt-3">
                        إدارة التخصصات
                    </a>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="text-muted">السنوات الأكاديمية</h6>
                            <h3>{{ number_format($stats['total_academic_years']) }}</h3>
                        </div>
                        <i class="fas fa-calendar-alt fa-2x text-primary"></i>
                    </div>

                    @if(Route::has('admin.academic-years.index'))
                        <a href="{{ route('admin.academic-years.index') }}"
                           class="btn btn-outline-primary btn-sm mt-3">
                            إدارة السنوات
                        </a>
                    @endif
                </div>
            </div>
        </div>

    </div>

    {{-- حالة المستخدمين --}}
    <div class="row g-4 mb-4">

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">حالة المستخدمين</h5>
                </div>

                <div class="card-body">

                    <div class="d-flex justify-content-between mb-3">
                        <span>
                            <i class="fas fa-circle text-success me-2"></i>
                            المستخدمون النشطون
                        </span>

                        <strong>
                            {{ number_format($stats['active_users']) }}
                        </strong>
                    </div>

                    <div class="d-flex justify-content-between">
                        <span>
                            <i class="fas fa-circle text-secondary me-2"></i>
                            المستخدمون المعطلون
                        </span>

                        <strong>
                            {{ number_format($stats['inactive_users']) }}
                        </strong>
                    </div>

                </div>
            </div>
        </div>

        {{-- أفكار معلقة --}}
        <div class="col-md-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">أفكار مشاريع التخرج المعلقة</h5>

                    <span class="badge bg-warning text-dark">
                        {{ $stats['pending_ideas']->count() }}
                    </span>
                </div>

                <div class="card-body">

                    @forelse($stats['pending_ideas'] as $idea)
                        <div class="border-bottom pb-2 mb-2">
                            <strong>{{ $idea->title ?? 'فكرة بدون عنوان' }}</strong>

                            @if($idea->student)
                                <small class="text-muted d-block">
                                    الطالب: {{ $idea->student->name ?? $idea->student->user->name ?? 'غير محدد' }}
                                </small>
                            @endif
                        </div>
                    @empty
                        <div class="text-center text-muted py-3">
                            لا توجد أفكار معلقة حاليًا
                        </div>
                    @endforelse

                </div>
            </div>
        </div>

    </div>

    {{-- آخر المستخدمين --}}
    <div class="card mb-4">

        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">آخر المستخدمين</h5>

            <a href="{{ route('admin.users.index') }}"
               class="btn btn-sm btn-outline-primary">
                عرض جميع المستخدمين
            </a>
        </div>

        <div class="card-body p-0">

            <div class="table-responsive">
                <table class="table table-hover mb-0">

                    <thead>
                    <tr>
                        <th>الاسم</th>
                        <th>البريد الإلكتروني</th>
                        <th>الدور</th>
                        <th>الحالة</th>
                        <th>الإجراءات</th>
                    </tr>
                    </thead>

                    <tbody>

                    @forelse($stats['recent_users'] as $user)

                        <tr>

                            <td>
                                {{ $user->name }}
                            </td>

                            <td>
                                {{ $user->email }}
                            </td>

                            <td>

                                @php
                                    $roleClass = match($user->role) {
                                        'admin' => 'danger',
                                        'faculty' => 'primary',
                                        'supervisor' => 'info',
                                        'staff' => 'warning',
                                        'student' => 'success',
                                        default => 'secondary',
                                    };
                                @endphp

                                <span class="badge bg-{{ $roleClass }}">
                                    {{ $user->role_name }}
                                </span>

                            </td>

                            <td>

                                @if($user->is_active)
                                    <span class="badge bg-success">
                                        نشط
                                    </span>
                                @else
                                    <span class="badge bg-secondary">
                                        معطل
                                    </span>
                                @endif

                            </td>

                            <td>

                                <a href="{{ route('admin.users.show', $user) }}"
                                   class="btn btn-sm btn-outline-primary"
                                   title="عرض">
                                    <i class="fas fa-eye"></i>
                                </a>

                                <a href="{{ route('admin.users.edit', $user) }}"
                                   class="btn btn-sm btn-outline-secondary"
                                   title="تعديل">
                                    <i class="fas fa-edit"></i>
                                </a>

                                @if($user->id != auth()->id())

                                    <form action="{{ route('admin.users.destroy', $user) }}"
                                          method="POST"
                                          class="d-inline">

                                        @csrf
                                        @method('DELETE')

                                        <button type="submit"
                                                class="btn btn-sm btn-outline-danger"
                                                onclick="return confirm('هل أنت متأكد من حذف المستخدم؟')"
                                                title="حذف">
                                            <i class="fas fa-trash"></i>
                                        </button>

                                    </form>

                                @endif

                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                لا يوجد مستخدمون
                            </td>
                        </tr>

                    @endforelse

                    </tbody>

                </table>
            </div>

        </div>

    </div>

    {{-- آخر مشاريع التخرج --}}
    <div class="card mb-4">

        <div class="card-header">
            <h5 class="mb-0">آخر مشاريع التخرج</h5>
        </div>

        <div class="card-body p-0">

            <div class="table-responsive">

                <table class="table table-hover mb-0">

                    <thead>
                    <tr>
                        <th>المشروع</th>
                        <th>المشرف</th>
                        <th>التخصص</th>
                        <th>الحالة</th>
                    </tr>
                    </thead>

                    <tbody>

                    @forelse($stats['recent_projects'] as $project)

                        <tr>

                            <td>
                                {{ $project->title ?? 'بدون عنوان' }}
                            </td>

                            <td>
                                {{ $project->supervisor->name ?? 'غير محدد' }}
                            </td>

                            <td>
                                {{ $project->specialization->name ?? 'غير محدد' }}
                            </td>

                            <td>
                                <span class="badge bg-secondary">
                                    {{ $project->status ?? 'غير محدد' }}
                                </span>
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">
                                لا توجد مشاريع حاليًا
                            </td>
                        </tr>

                    @endforelse

                    </tbody>

                </table>

            </div>

        </div>

    </div>

    {{-- روابط الإدارة --}}
    <div class="row g-4">

        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5>
                        <i class="fas fa-users-cog text-primary me-2"></i>
                        إدارة المستخدمين
                    </h5>

                    <p class="text-muted">
                        إدارة حسابات الطلاب وأعضاء هيئة التدريس والموظفين.
                    </p>

                    <a href="{{ route('admin.users.index') }}"
                       class="btn btn-primary">
                        إدارة المستخدمين
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5>
                        <i class="fas fa-university text-primary me-2"></i>
                        الهيكل الأكاديمي
                    </h5>

                    <p class="text-muted">
                        إدارة الكليات والأقسام والتخصصات والسنوات الأكاديمية.
                    </p>

                    <a href="{{ route('admin.colleges.index') }}"
                       class="btn btn-primary">
                        إدارة الهيكل
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5>
                        <i class="fas fa-chart-bar text-primary me-2"></i>
                        التقارير
                    </h5>

                    <p class="text-muted">
                        عرض التقارير والإحصائيات المتعلقة بالنظام.
                    </p>

                    <a href="{{ route('admin.reports.index') }}"
                       class="btn btn-primary">
                        التقارير والإحصائيات
                    </a>
                </div>
            </div>
        </div>

    </div>

</div>
@endsection