@extends('layouts.app')
@section('title', 'جدولي الدراسي')
@section('content')
<div class="container-fluid">
    <h2 class="mb-4">جدولي الدراسي</h2>
    @if (! $profile)
        <div class="alert alert-warning">لا يوجد ملف أكاديمي مرتبط بحسابك بعد.</div>
    @elseif (! $profile->current_level_id)
        <div class="alert alert-warning">لم يُحدَّد مستواك الدراسي الحالي بعد — راجع شؤون الطلاب.</div>
    @else
        <div class="card">
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead><tr><th>المقرر</th><th>المدرس</th><th>القاعة</th><th>اليوم</th><th>الوقت</th></tr></thead>
                    <tbody>
                    @forelse ($schedules as $schedule)
                        <tr>
                            <td>{{ $schedule->section->course->name_ar }}</td>
                            <td>{{ $schedule->section->faculty->user->name ?? '-' }}</td>
                            <td>{{ $schedule->room->name }}</td>
                            <td>{{ \App\Models\Schedule::dayLabel($schedule->day_of_week) }}</td>
                            <td>{{ $schedule->start_time }} - {{ $schedule->end_time }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">لا يوجد جدول منشور لمستواك حاليًا.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
@endsection