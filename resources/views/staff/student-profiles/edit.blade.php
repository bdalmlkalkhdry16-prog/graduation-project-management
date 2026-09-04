@extends('layouts.app')

@section('title', 'تعديل ملف الطالب')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header"><h4 class="mb-0">تعديل ملف: {{ $studentProfile->user->name }}</h4></div>
                <div class="card-body">

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('staff.student-profiles.update', $studentProfile) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label">رقم القيد الرسمي</label>
                            <input type="text" name="number_student" class="form-control"
                                   value="{{ old('number_student', $studentProfile->number_student) }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">التخصص</label>
                            <select name="specialization_id" class="form-select">
                                <option value="">بلا تحديد</option>
                                @foreach ($specializations as $specialization)
                                    <option value="{{ $specialization->id }}"
                                        {{ old('specialization_id', $studentProfile->specialization_id) == $specialization->id ? 'selected' : '' }}>
                                        {{ $specialization->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">البرنامج</label>
                            <select name="program_id" class="form-select">
                                <option value="">بلا تحديد بعد</option>
                                @foreach ($programs as $program)
                                    <option value="{{ $program->id }}"
                                        {{ old('program_id', $studentProfile->program_id) == $program->id ? 'selected' : '' }}>
                                        {{ $program->level === 'diploma' ? 'دبلوم' : 'بكالوريوس' }} — {{ $program->specialization->name }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">يجب أن يطابق تخصص البرنامج التخصص المختار أعلاه.</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">المستوى الحالي</label>
                            <select name="current_level_id" class="form-select">
                                <option value="">بلا تحديد بعد</option>
                                @foreach ($levels as $level)
                                    <option value="{{ $level->id }}"
                                        {{ old('current_level_id', $studentProfile->current_level_id) == $level->id ? 'selected' : '' }}>
                                        {{ $level->name ?? 'المستوى ' . $level->level_number }} ({{ $level->program->specialization->name }})
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">يجب أن ينتمي المستوى للبرنامج المختار أعلاه.</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">سنة القبول</label>
                            <input type="number" name="admission_year" class="form-control"
                                   value="{{ old('admission_year', $studentProfile->admission_year) }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">الحالة الأكاديمية</label>
                            <select name="academic_status" class="form-select">
                                @foreach (['active' => 'منتظم', 'suspended' => 'موقوف', 'withdrawn' => 'منسحب', 'graduated' => 'متخرج'] as $value => $label)
                                    <option value="{{ $value }}" {{ old('academic_status', $studentProfile->academic_status) === $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">حفظ التعديلات</button>
                            <a href="{{ route('staff.student-profiles.show', $studentProfile) }}" class="btn btn-outline-secondary">إلغاء</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection