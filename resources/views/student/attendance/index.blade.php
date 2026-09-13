@extends('layouts.app')
@section('title', 'حضوري')
@section('content')
<div class="container-fluid">
    <h2 class="mb-4">حضوري</h2>
    @if (! $records)
        <div class="alert alert-warning">لا يوجد ملف أكاديمي مرتبط بحسابك بعد.</div>
    @else
        <div class="card">
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead><tr><th>المقرر</th><th>التاريخ</th><th>الحالة</th></tr></thead>
                    <tbody>
                    @forelse ($records as $record)
                        <tr>
                            <td>{{ $record->session->schedule->section->course->name_ar }}</td>
                            <td>{{ $record->session->session_date->format('Y-m-d') }}</td>
                            <td>{{ \App\Models\AttendanceRecord::statusLabel($record->status) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-center text-muted py-4">لا يوجد سجل حضور بعد.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mt-3">{{ $records->links() }}</div>
    @endif
</div>
@endsection