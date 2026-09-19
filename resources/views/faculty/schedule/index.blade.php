@extends('layouts.app')

@section('title', 'جدولي الدراسي')

@section('content')
<div class="container-fluid">

    {{-- العنوان --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">الجدول الدراسي</h2>
            <p class="text-muted mb-0">
                جدول المحاضرات والمواد المسندة إليك
            </p>
        </div>

        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-right me-1"></i>
            العودة للوحة التحكم
        </a>
    </div>

    {{-- معلومات عضو هيئة التدريس --}}
    @if($facultyProfile)
        <div class="card mb-4">
            <div class="card-body">
                <div class="row align-items-center">

                    <div class="col-md-8">
                        <h5 class="mb-1">
                            {{ $facultyProfile->user->name }}
                        </h5>

                        <div class="text-muted">
                            @if($facultyProfile->academic_rank)
                                <span class="me-3">
                                    <i class="fas fa-user-tie me-1"></i>
                                    {{ $facultyProfile->academic_rank }}
                                </span>
                            @endif

                            @if($facultyProfile->specialization)
                                <span>
                                    <i class="fas fa-graduation-cap me-1"></i>
                                    {{ $facultyProfile->specialization->name }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="col-md-4 text-md-end mt-3 mt-md-0">
                        <span class="badge bg-primary fs-6">
                            {{ $schedules->count() }} محاضرة
                        </span>
                    </div>

                </div>
            </div>
        </div>
    @endif

    {{-- الجدول --}}
    <div class="card">

        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-calendar-week me-2"></i>
                المحاضرات الأسبوعية
            </h5>
        </div>

        <div class="card-body p-0">

            @if($schedules->count())

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">

                        <thead>
                        <tr>
                            <th>اليوم</th>
                            <th>الوقت</th>
                            <th>المقرر</th>
                            <th>الشعبة</th>
                            <th>الفصل الدراسي</th>
                            <th>القاعة</th>
                        </tr>
                        </thead>

                        <tbody>

                        @foreach($schedules as $schedule)

                            <tr>

                                {{-- اليوم --}}
                                <td>
                                    <strong>
                                        {{ \App\Models\Schedule::dayLabel($schedule->day_of_week) }}
                                    </strong>
                                </td>

                                {{-- الوقت --}}
                                <td>
                                    <span class="badge bg-light text-dark border">
                                        {{ \Illuminate\Support\Carbon::parse($schedule->start_time)->format('H:i') }}
                                        -
                                        {{ \Illuminate\Support\Carbon::parse($schedule->end_time)->format('H:i') }}
                                    </span>
                                </td>

                                {{-- المقرر --}}
                                <td>
                                    @if($schedule->section?->course)
                                        <strong>
                                            {{ $schedule->section->course->name }}
                                        </strong>

                                        @if($schedule->section->course->code)
                                            <small class="text-muted d-block">
                                                {{ $schedule->section->course->code }}
                                            </small>
                                        @endif
                                    @else
                                        <span class="text-muted">
                                            غير محدد
                                        </span>
                                    @endif
                                </td>

                                {{-- الشعبة --}}
                                <td>
                                    {{ $schedule->section?->code ?? 'غير محدد' }}
                                </td>

                                {{-- الفصل الدراسي --}}
                                <td>
                                    {{ $schedule->section?->academicTerm?->name ?? 'غير محدد' }}
                                </td>

                                {{-- القاعة --}}
                                <td>
                                    @if($schedule->room)
                                        <span>
                                            <i class="fas fa-door-open me-1"></i>
                                            {{ $schedule->room->name }}
                                        </span>
                                    @else
                                        <span class="text-muted">
                                            غير محددة
                                        </span>
                                    @endif
                                </td>

                            </tr>

                        @endforeach

                        </tbody>

                    </table>
                </div>

            @else

                <div class="text-center py-5">

                    <i class="fas fa-calendar-times fa-4x text-muted mb-3"></i>

                    <h5>لا يوجد جدول دراسي</h5>

                    <p class="text-muted mb-0">
                        لم يتم إسناد أي محاضرات إليك حاليًا.
                    </p>

                </div>

            @endif

        </div>
    </div>

</div>
@endsection