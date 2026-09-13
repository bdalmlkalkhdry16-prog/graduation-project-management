@extends('layouts.app')
@section('title', 'الجداول الدراسية')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>الجداول الدراسية</h2>
        <a href="{{ route('staff.schedules.create') }}" class="btn btn-primary"><i class="fas fa-plus me-2"></i> إضافة جدول</a>
    </div>
    @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    <div class="card">
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead><tr><th>المقرر</th><th>المدرس</th><th>القاعة</th><th>اليوم</th><th>الوقت</th><th></th></tr></thead>
                <tbody>
                @forelse ($schedules as $schedule)
                    <tr>
                        <td>{{ $schedule->section->course->name_ar }}</td>
                        <td>{{ $schedule->section->faculty->user->name ?? '-' }}</td>
                        <td>{{ $schedule->room->name }}</td>
                        <td>{{ \App\Models\Schedule::dayLabel($schedule->day_of_week) }}</td>
                        <td>{{ $schedule->start_time }} - {{ $schedule->end_time }}</td>
                        <td><a href="{{ route('staff.schedules.edit', $schedule) }}" class="btn btn-sm btn-outline-secondary">تعديل</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">لا توجد جداول بعد.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $schedules->links() }}</div>
</div>
@endsection