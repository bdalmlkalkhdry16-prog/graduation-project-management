@extends('layouts.app')

@section('title', 'إنشاء ملف طالب')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header"><h4 class="mb-0">إنشاء ملف طالب جديد</h4></div>
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

                    <form action="{{ route('staff.student-profiles.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">المستخدم (طالب بلا ملف حاليًا)</label>
                            <select name="user_id" class="form-select" required>
                                <option value="">اختر...</option>
                                @foreach ($eligibleUsers as $user)
                                    <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }} ({{ $user->email }})
                                    </option>
                                @endforeach
                            </select>
                            @if ($eligibleUsers->isEmpty())
                                <small class="text-muted">لا يوجد مستخدمون بدور طالب بلا ملف حاليًا.</small>
                            @endif
                        </div>

                        <div class="mb-3">
                            <label class="form-label">رقم القيد الرسمي</label>
                            <input type="text" name="number_student" class="form-control" value="{{ old('number_student') }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">التخصص</label>
                            <select name="specialization_id" class="form-select">
                                <option value="">بلا تحديد</option>
                                @foreach ($specializations as $specialization)
                                    <option value="{{ $specialization->id }}" {{ old('specialization_id') == $specialization->id ? 'selected' : '' }}>
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
                                    <option value="{{ $program->id }}" {{ old('program_id') == $program->id ? 'selected' : '' }}>
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
                                    <option value="{{ $level->id }}" {{ old('current_level_id') == $level->id ? 'selected' : '' }}>
                                        {{ $level->name ?? 'المستوى ' . $level->level_number }} ({{ $level->program->specialization->name }})
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">يجب أن ينتمي المستوى للبرنامج المختار أعلاه.</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">سنة القبول</label>
                            <input type="number" name="admission_year" class="form-control" value="{{ old('admission_year', date('Y')) }}">
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">حفظ</button>
                            <a href="{{ route('staff.student-profiles.index') }}" class="btn btn-outline-secondary">إلغاء</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection