@extends('layouts.app')
@section('title', 'الحضور')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>الحضور</h2>
        <a href="{{ route('faculty.attendance.create') }}" class="btn btn-primary"><i class="fas fa-plus me-2"></i> فتح جلسة حضور جديدة</a>
    </div>
    @if (! auth()->user()->facultyProfile)
        <div class="alert alert-warning">لا يوجد ملف عضو هيئة تدريس مرتبط بحسابك.</div>
    @else
        <div class="card">
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead><tr><th>المقرر</th><th>التاريخ</th><th></th></tr></thead>
                    <tbody>
                    @forelse ($sessions as $session)
                        <tr>
                            <td>{{ $session->schedule->section->course->name_ar }}</td>
                            <td>{{ $session->session_date->format('Y-m-d') }}</td>
                            <td><a href="{{ route('faculty.attendance.show', $session) }}" class="btn btn-sm btn-outline-primary">فتح</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-center text-muted py-4">لا توجد جلسات بعد.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mt-3">{{ $sessions->links() }}</div>
    @endif
</div>
@endsection