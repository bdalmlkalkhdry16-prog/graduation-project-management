@extends('layouts.app')
@section('title', 'إشراف الحضور')
@section('content')
<div class="container-fluid">
    <h2 class="mb-4">إشراف الحضور</h2>
    <div class="card">
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead><tr><th>المقرر</th><th>المدرس</th><th>التاريخ</th><th></th></tr></thead>
                <tbody>
                @forelse ($sessions as $session)
                    <tr>
                        <td>{{ $session->schedule->section->course->name_ar }}</td>
                        <td>{{ $session->schedule->section->faculty->user->name ?? '-' }}</td>
                        <td>{{ $session->session_date->format('Y-m-d') }}</td>
                        <td><a href="{{ route('staff.attendance.show', $session) }}" class="btn btn-sm btn-outline-primary">عرض</a></td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted py-4">لا توجد جلسات حضور بعد.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $sessions->links() }}</div>
</div>
@endsection