@extends('layouts.app')

@section('title', 'لوحة تحكم عضو هيئة التدريس')

@section('content')
<div class="container-fluid px-0">

    {{-- =========================
         Header
    ========================== --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">

        <div>
            <h2 class="fw-bold mb-1">
                لوحة تحكم عضو هيئة التدريس
            </h2>

            <p class="text-muted mb-0">
                مرحباً، {{ auth()->user()->name }}
            </p>
        </div>

        <div class="text-muted small">
            <i class="fas fa-calendar-day me-1"></i>
            {{ now()->format('Y-m-d') }}
        </div>

    </div>


    {{-- =========================
         Profile Summary
    ========================== --}}
    <div class="card border-0 shadow-sm mb-4">

        <div class="card-body">

            <div class="row align-items-center">

                <div class="col-md-8">

                    <div class="d-flex align-items-center gap-3">

                        <div class="rounded-circle bg-primary text-white d-flex
                                    align-items-center justify-content-center"
                             style="width:60px;height:60px;font-size:24px;">

                            <i class="fas fa-chalkboard-user"></i>

                        </div>

                        <div>

                            <h5 class="fw-bold mb-1">
                                {{ auth()->user()->name }}
                            </h5>

                            <div class="text-muted small">
                                عضو هيئة تدريس
                            </div>

                            @if(auth()->user()->employee_id)
                                <div class="text-muted small mt-1">
                                    الرقم الوظيفي:
                                    <strong>{{ auth()->user()->employee_id }}</strong>
                                </div>
                            @endif

                        </div>

                    </div>

                </div>

                <div class="col-md-4 text-md-end mt-3 mt-md-0">

                    <a href="{{ route('profile.show') }}"
                       class="btn btn-outline-primary rounded-pill px-4">

                        <i class="fas fa-user me-1"></i>
                        الملف الشخصي

                    </a>

                </div>

            </div>

        </div>

    </div>


    {{-- =========================
         Main Actions
    ========================== --}}
    <div class="row g-4 mb-4">

        {{-- Schedule --}}
        @if(auth()->user()->hasPermission('schedules.viewOwn'))

            <div class="col-sm-6 col-lg-4">

                <a href="{{ route('faculty.schedule.index') }}"
                   class="text-decoration-none">

                    <div class="card border-0 shadow-sm h-100 dashboard-action-card">

                        <div class="card-body p-4">

                            <div class="d-flex justify-content-between align-items-start">

                                <div>

                                    <h5 class="fw-bold text-dark mb-2">
                                        الجدول الدراسي
                                    </h5>

                                    <p class="text-muted small mb-0">
                                        عرض الجدول الدراسي والمواعيد والقاعات.
                                    </p>

                                </div>

                                <div class="text-primary fs-2">
                                    <i class="fas fa-calendar-alt"></i>
                                </div>

                            </div>

                            <div class="mt-4 text-primary small fw-semibold">
                                عرض الجدول
                                <i class="fas fa-arrow-left ms-1"></i>
                            </div>

                        </div>

                    </div>

                </a>

            </div>

        @endif


        {{-- Attendance --}}
        @if(auth()->user()->hasPermission('attendance.manageOwn'))

            <div class="col-sm-6 col-lg-4">

                <a href="{{ route('faculty.attendance.index') }}"
                   class="text-decoration-none">

                    <div class="card border-0 shadow-sm h-100 dashboard-action-card">

                        <div class="card-body p-4">

                            <div class="d-flex justify-content-between align-items-start">

                                <div>

                                    <h5 class="fw-bold text-dark mb-2">
                                        إدارة الحضور
                                    </h5>

                                    <p class="text-muted small mb-0">
                                        إنشاء جلسات الحضور وإدارة سجلات الطلاب.
                                    </p>

                                </div>

                                <div class="text-success fs-2">
                                    <i class="fas fa-clipboard-check"></i>
                                </div>

                            </div>

                            <div class="mt-4 text-success small fw-semibold">
                                إدارة الحضور
                                <i class="fas fa-arrow-left ms-1"></i>
                            </div>

                        </div>

                    </div>

                </a>

            </div>

        @endif


        {{-- Profile --}}
        <div class="col-sm-6 col-lg-4">

            <a href="{{ route('profile.edit') }}"
               class="text-decoration-none">

                <div class="card border-0 shadow-sm h-100 dashboard-action-card">

                    <div class="card-body p-4">

                        <div class="d-flex justify-content-between align-items-start">

                            <div>

                                <h5 class="fw-bold text-dark mb-2">
                                    بياناتي الشخصية
                                </h5>

                                <p class="text-muted small mb-0">
                                    مراجعة وتحديث بيانات الحساب الشخصية.
                                </p>

                            </div>

                            <div class="text-info fs-2">
                                <i class="fas fa-user-edit"></i>
                            </div>

                        </div>

                        <div class="mt-4 text-info small fw-semibold">
                            تعديل البيانات
                            <i class="fas fa-arrow-left ms-1"></i>
                        </div>

                    </div>

                </div>

            </a>

        </div>

    </div>


    {{-- =========================
         Academic Services
    ========================== --}}
    <div class="card border-0 shadow-sm mb-4">

        <div class="card-header bg-transparent border-bottom py-3">

            <h5 class="fw-bold mb-0">
                <i class="fas fa-graduation-cap text-primary me-2"></i>
                الخدمات الأكاديمية
            </h5>

        </div>

        <div class="card-body">

            <div class="row g-3">

                @if(auth()->user()->hasPermission('schedules.viewOwn'))

                    <div class="col-md-6 col-lg-4">

                        <a href="{{ route('faculty.schedule.index') }}"
                           class="btn btn-light border w-100 text-start p-3">

                            <i class="fas fa-calendar-week text-primary me-2"></i>
                            الجدول الدراسي

                        </a>

                    </div>

                @endif


                @if(auth()->user()->hasPermission('attendance.manageOwn'))

                    <div class="col-md-6 col-lg-4">

                        <a href="{{ route('faculty.attendance.index') }}"
                           class="btn btn-light border w-100 text-start p-3">

                            <i class="fas fa-user-check text-success me-2"></i>
                            الحضور والغياب

                        </a>

                    </div>

                @endif

            </div>

        </div>

    </div>


    {{-- =========================
         Graduation Projects
         يظهر فقط لمن لديه دور المشرف
    ========================== --}}
    @if(auth()->user()->hasRole('supervisor'))

        <div class="card border-0 shadow-sm mb-4">

            <div class="card-header bg-transparent border-bottom py-3">

                <div class="d-flex justify-content-between align-items-center">

                    <h5 class="fw-bold mb-0">
                        <i class="fas fa-project-diagram text-primary me-2"></i>
                        مشاريع التخرج
                    </h5>

                    <span class="badge bg-primary">
                        مشرف مشاريع
                    </span>

                </div>

            </div>

            <div class="card-body">

                <p class="text-muted mb-3">
                    لديك صلاحيات الإشراف على مشاريع التخرج بالإضافة إلى
                    صلاحيات عضو هيئة التدريس.
                </p>

                <a href="{{ route('projects.index', ['supervisor_id' => auth()->id()]) }}"
                   class="btn btn-primary rounded-pill px-4">

                    <i class="fas fa-folder-open me-1"></i>
                    مشاريعي التي أشرف عليها

                </a>

            </div>

        </div>

    @endif


    {{-- =========================
         Information
    ========================== --}}
    <div class="alert alert-info border-0 shadow-sm">

        <div class="d-flex align-items-start gap-3">

            <i class="fas fa-circle-info fs-4"></i>

            <div>

                <h6 class="fw-bold mb-1">
                    لوحة عضو هيئة التدريس
                </h6>

                <p class="mb-0 small">
                    تعرض هذه اللوحة الخدمات والصلاحيات المتاحة لك حسب
                    الأدوار والصلاحيات المسندة إلى حسابك.
                </p>

            </div>

        </div>

    </div>

</div>


{{-- =========================
     Dashboard Styles
========================== --}}
@push('styles')
<style>

    .dashboard-action-card {
        transition: transform .2s ease, box-shadow .2s ease;
    }

    .dashboard-action-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 .5rem 1rem rgba(0,0,0,.10) !important;
    }

</style>
@endpush

@endsection