@extends('layouts.app')
@section('title', 'تفاصيل جلسة الحضور')
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header"><h4 class="mb-0">{{ $attendanceSession->schedule->section->course->name_ar }} — {{ $attendanceSession->session_date->format('Y-m-d') }}</h4></div>
                <div class="card-body p-0">
                    <table class="table mb-0">
                        <thead><tr><th>الطالب</th><th>الحالة</th></tr></thead>
                        <tbody>
                        @forelse ($attendanceSession->records as $record)
                            <tr><td>{{ $record->studentProfile->user->name }}</td><td>{{ \App\Models\AttendanceRecord::statusLabel($record->status) }}</td></tr>
                        @empty
                            <tr><td colspan="2" class="text-center text-muted py-3">لم يُسجَّل حضور بعد.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <a href="{{ route('staff.attendance.index') }}" class="btn btn-outline-secondary mt-3">رجوع</a>
        </div>
    </div>
</div>
@endsection