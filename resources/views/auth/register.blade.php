@extends('layouts.app')

@section('title', 'إنشاء حساب جديد')

@section('content')
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-8 col-lg-6">
                <div class="card shadow-lg border-0">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <h3 class="fw-bold">إنشاء حساب جديد</h3>
                            <p class="text-muted">سجل الآن للانضمام إلى منصة المشاريع</p>
                        </div>

                        <form method="POST" action="{{ route('register') }}">
                            @csrf

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="name" class="form-label">الاسم الكامل</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                                               id="name" name="name" value="{{ old('name') }}" required>
                                    </div>
                                    @error('name')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">البريد الإلكتروني</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                        <input type="email" class="form-control @error('email') is-invalid @enderror"
                                               id="email" name="email" value="{{ old('email') }}" required>
                                    </div>
                                    @error('email')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="password" class="form-label">كلمة المرور</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                        <input type="password" class="form-control @error('password') is-invalid @enderror"
                                               id="password" name="password" required>
                                    </div>
                                    @error('password')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="password_confirmation" class="form-label">تأكيد كلمة المرور</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                        <input type="password" class="form-control"
                                               id="password_confirmation" name="password_confirmation" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="role" class="form-label">نوع الحساب</label>
                                    <select class="form-select @error('role') is-invalid @enderror" id="role" name="role" required>
                                        <option value="student">طالب</option>
                                        <option value="supervisor">مشرف</option>
                                    </select>
                                    @error('role')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="phone" class="form-label">رقم الجوال</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                        <input type="tel" class="form-control @error('phone') is-invalid @enderror"
                                               id="phone" name="phone" value="{{ old('phone') }}">
                                    </div>
                                    @error('phone')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row student-fields" style="display: none;">
                                <div class="col-md-6 mb-3">
                                    <label for="student_id" class="form-label">الرقم الجامعي</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-id-card"></i></span>
                                        <input type="text" class="form-control @error('student_id') is-invalid @enderror"
                                               id="student_id" name="student_id" value="{{ old('student_id') }}">
                                    </div>
                                    @error('student_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="specialization_id" class="form-label">التخصص</label>
                                    <select class="form-select @error('specialization_id') is-invalid @enderror"
                                            id="specialization_id" name="specialization_id">
                                        <option value="">اختر التخصص</option>
                                        @foreach($specializations ?? [] as $spec)
                                            <option value="{{ $spec->id }}">{{ $spec->name_ar }}</option>
                                        @endforeach
                                    </select>
                                    @error('specialization_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row supervisor-fields" style="display: none;">
                                <div class="col-md-6 mb-3">
                                    <label for="employee_id" class="form-label">الرقم الوظيفي</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-id-badge"></i></span>
                                        <input type="text" class="form-control @error('employee_id') is-invalid @enderror"
                                               id="employee_id" name="employee_id" value="{{ old('employee_id') }}">
                                    </div>
                                    @error('employee_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 py-2 mt-3">
                                <i class="fas fa-user-plus me-2"></i> إنشاء حساب
                            </button>
                        </form>

                        <hr class="my-4">

                        <div class="text-center">
                            <p class="mb-0">لديك حساب بالفعل؟ <a href="{{ route('login') }}">تسجيل الدخول</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            const roleSelect = document.getElementById('role');
            const studentFields = document.querySelector('.student-fields');
            const supervisorFields = document.querySelector('.supervisor-fields');

            function toggleFields() {
                const role = roleSelect.value;
                if (role === 'student') {
                    studentFields.style.display = 'flex';
                    supervisorFields.style.display = 'none';
                } else if (role === 'supervisor') {
                    studentFields.style.display = 'none';
                    supervisorFields.style.display = 'flex';
                } else {
                    studentFields.style.display = 'none';
                    supervisorFields.style.display = 'none';
                }
            }

            roleSelect.addEventListener('change', toggleFields);
            toggleFields();
        </script>
    @endpush
@endsection
