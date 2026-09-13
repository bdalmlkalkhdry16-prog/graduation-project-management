@extends('layouts.app')
@section('title', 'تعديل جدول')
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-7">
            <div class="card">
                <div class="card-header"><h4 class="mb-0">تعديل جدول</h4></div>
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
                    @endif
                    <form action="{{ route('staff.schedules.update', $schedule) }}" method="POST">
                        @csrf @method('PUT')
                        <div class="mb-3">
                            <label class="form-label">الشعبة</label>
                            <select name="section_id" class="form-select" required>
                                @foreach ($sections as $section)
                                    <option value="{{ $section->id }}" {{ old('section_id', $schedule->section_id) == $section->id ? 'selected' : '' }}>
                                        {{ $section->course->name_ar }} ({{ $section->code }}) — {{ $section->faculty->user->name ?? 'بلا مدرس' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">القاعة/المعمل</label>
                            <select name="room_id" class="form-select" required>
                                @foreach ($rooms as $room)
                                    <option value="{{ $room->id }}" {{ old('room_id', $schedule->room_id) == $room->id ? 'selected' : '' }}>{{ $room->name }} (سعة {{ $room->capacity }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">اليوم</label>
                            <select name="day_of_week" class="form-select" required>
                                @foreach (['sat' => 'السبت', 'sun' => 'الأحد', 'mon' => 'الاثنين', 'tue' => 'الثلاثاء', 'wed' => 'الأربعاء', 'thu' => 'الخميس', 'fri' => 'الجمعة'] as $value => $label)
                                    <option value="{{ $value }}" {{ old('day_of_week', $schedule->day_of_week) === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="form-label">وقت البدء</label>
                                <input type="time" name="start_time" class="form-control" value="{{ old('start_time', substr($schedule->start_time, 0, 5)) }}" required>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label">وقت الانتهاء</label>
                                <input type="time" name="end_time" class="form-control" value="{{ old('end_time', substr($schedule->end_time, 0, 5)) }}" required>
                            </div>
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">حفظ التعديلات</button>
                            <a href="{{ route('staff.schedules.index') }}" class="btn btn-outline-secondary">إلغاء</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection