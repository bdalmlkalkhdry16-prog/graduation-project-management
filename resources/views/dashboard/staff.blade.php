@extends('layouts.app')

@section('title', 'لوحة تحكم موظف شؤون الطلاب')

@section('content')
<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>لوحة تحكم موظف شؤون الطلاب</h2>
        <div class="text-muted">{{ now()->format('Y-m-d') }}</div>
    </div>

    <div class="row g-4">

        {{-- ملفات الطلاب --}}
        @if(auth()->user()->hasPermission('student-profiles.manage'))
            <div class="col-md-6 col-lg-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5>ملفات الطلاب</h5>
                        <p class="text-muted">
                            إنشاء وإدارة الملفات الأكاديمية الرسمية للطلاب.
                        </p>

                        <a href="{{ route('staff.student-profiles.index') }}"
                           class="btn btn-primary">
                            إدارة ملفات الطلاب
                        </a>
                    </div>
                </div>
            </div>
        @endif

        {{-- طلبات الخدمات --}}
        @if(auth()->user()->hasPermission('service-requests.manageAll'))
            <div class="col-md-6 col-lg-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5>طلبات الخدمات</h5>
                        <p class="text-muted">
                            مراجعة وإدارة طلبات الطلاب.
                        </p>

                        <a href="{{ route('staff.service-requests.index') }}"
                           class="btn btn-primary">
                            إدارة الطلبات
                        </a>
                    </div>
                </div>
            </div>
        @endif

        {{-- الغرف --}}
        @if(auth()->user()->hasPermission('rooms.manage'))
            <div class="col-md-6 col-lg-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5>إدارة الغرف</h5>
                        <p class="text-muted">
                            إدارة القاعات والغرف الأكاديمية.
                        </p>

                        <a href="{{ route('staff.rooms.index') }}"
                           class="btn btn-primary">
                            إدارة الغرف
                        </a>
                    </div>
                </div>
            </div>
        @endif

        {{-- الجداول --}}
        @if(auth()->user()->hasPermission('schedules.manage'))
            <div class="col-md-6 col-lg-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5>إدارة الجداول</h5>
                        <p class="text-muted">
                            إنشاء وإدارة الجداول الدراسية.
                        </p>

                        <a href="{{ route('staff.schedules.index') }}"
                           class="btn btn-primary">
                            إدارة الجداول
                        </a>
                    </div>
                </div>
            </div>
        @endif

        {{-- الحضور --}}
        @if(auth()->user()->hasPermission('attendance.viewAll'))
            <div class="col-md-6 col-lg-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5>الحضور</h5>
                        <p class="text-muted">
                            متابعة سجلات حضور الطلاب.
                        </p>

                        <a href="{{ route('staff.attendance.index') }}"
                           class="btn btn-primary">
                            متابعة الحضور
                        </a>
                    </div>
                </div>
            </div>
        @endif

    </div>

</div>
@endsection