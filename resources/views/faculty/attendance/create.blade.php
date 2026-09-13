@extends('layouts.app')
@section('title', 'فتح جلسة حضور')
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header"><h4 class="mb-0">فتح جلسة حضور جديدة</h4></div>
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
                    @endif
                    <form action="{{ route('faculty.attendance.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">الجدول (المقرر/القاعة/اليوم)</label>
                            <select name="schedule_id" class="form-select" required>
                                <option value="">اختر...</option>
                                @foreach ($schedules as $schedule)
                                    <option value="{{ $schedule->id }}" {{ old('schedule_id') == $schedule->id ? 'selected' : '' }}>
                                        {{ $schedule->section->course->name_ar }} — {{ \App\Models\Schedule::dayLabel($schedule->day_of_week) }} ({{ $schedule->room->name }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">تاريخ الجلسة</label>
                            <input type="date" name="session_date" class="form-control" value="{{ old('session_date') }}" required>
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">فتح الجلسة</button>
                            <a href="{{ route('faculty.attendance.index') }}" class="btn btn-outline-secondary">إلغاء</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection