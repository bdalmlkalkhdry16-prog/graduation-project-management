@extends('layouts.app')

@section('title', 'إضافة عضو للمشروع')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0">إضافة عضو إلى مشروع: {{ $project->title_ar }}</h4>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('projects.members.store', $project) }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">الطالب</label>
                                <select name="student_id" class="form-select @error('student_id') is-invalid @enderror" required>
                                    <option value="">اختر الطالب</option>
                                    @foreach($availableStudents as $student)
                                        <option value="{{ $student->id }}" @selected(old('student_id') == $student->id)>{{ $student->name }} ({{ $student->student_id }}) - {{ $student->specialization->name_ar ?? '' }}@if (isset($ineligible[$student->id])) — غير مؤهل: {{ implode(' ', $ineligible[$student->id]) }}@endif</option>
                                    @endforeach
                                </select>
                                @error('student_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            @if (auth()->user()->isAdmin() || auth()->user()->hasRole('admin'))
                                <div class="mb-3">
                                    <label class="form-label">استثناء إداري (اختياري)</label>
                                    <input type="text" name="override_reason" value="{{ old('override_reason') }}" class="form-control" placeholder="سبب إضافة طالب غير مؤهل — اتركه فارغًا إن كان مؤهلًا">
                                </div>
                            @endif

                            <div class="mb-3">
                                <label class="form-label">الدور</label>
                                <select name="role" class="form-select" required>
                                    <option value="member">عضو</option>
                                    <option value="leader">قائد الفريق</option>
                                </select>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">إضافة</button>
                                <a href="{{ route('projects.show', $project) }}" class="btn btn-outline-secondary">إلغاء</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
