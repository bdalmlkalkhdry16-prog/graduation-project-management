@extends('layouts.app')
@section('title', 'تسجيل الحضور')
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header"><h4 class="mb-0">{{ $attendanceSession->schedule->section->course->name_ar }} — {{ $attendanceSession->session_date->format('Y-m-d') }}</h4></div>
                <div class="card-body">
                    @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
                    @if ($roster->isEmpty())
                        <div class="alert alert-warning">لا يوجد طلاب مسجَّلون في هذا المستوى/المقرر حاليًا.</div>
                    @else
                        <form action="{{ route('faculty.attendance.update', $attendanceSession) }}" method="POST">
                            @csrf @method('PUT')
                            <table class="table">
                                <thead><tr><th>الطالب</th><th>الحالة</th></tr></thead>
                                <tbody>
                                @foreach ($roster as $index => $student)
                                    <tr>
                                        <td>
                                            {{ $student->user->name }}
                                            <input type="hidden" name="records[{{ $index }}][student_profile_id]" value="{{ $student->id }}">
                                        </td>
                                        <td>
                                            <select name="records[{{ $index }}][status]" class="form-select form-select-sm">
                                                @php $current = $existingRecords[$student->id]->status ?? 'present'; @endphp
                                                <option value="present" {{ $current === 'present' ? 'selected' : '' }}>حاضر</option>
                                                <option value="absent" {{ $current === 'absent' ? 'selected' : '' }}>غائب</option>
                                                <option value="late" {{ $current === 'late' ? 'selected' : '' }}>متأخر</option>
                                                <option value="excused" {{ $current === 'excused' ? 'selected' : '' }}>معذور</option>
                                            </select>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                            <button type="submit" class="btn btn-primary">حفظ الحضور</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection