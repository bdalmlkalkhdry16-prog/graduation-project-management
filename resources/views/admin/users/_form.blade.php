<div class="mb-3">
    <label class="form-label">الاسم الكامل <span class="text-danger">*</span></label>
    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name ?? '') }}" required>
    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="mb-3">
    <label class="form-label">البريد الإلكتروني <span class="text-danger">*</span></label>
    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email ?? '') }}" required>
    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">كلمة المرور {{ isset($user) ? '(اتركها فارغة إذا لم ترغب في التغيير)' : '*' }}</label>
        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" {{ isset($user) ? '' : 'required' }}>
        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">تأكيد كلمة المرور</label>
        <input type="password" name="password_confirmation" class="form-control">
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">الدور <span class="text-danger">*</span></label>
        <select name="role" class="form-select @error('role') is-invalid @enderror" required>
            <option value="student" {{ old('role', ($user->role ?? '')) == 'student' ? 'selected' : '' }}>طالب</option>
            <option value="supervisor" {{ old('role', ($user->role ?? '')) == 'supervisor' ? 'selected' : '' }}>مشرف</option>
            <option value="admin" {{ old('role', ($user->role ?? '')) == 'admin' ? 'selected' : '' }}>مدير</option>
        </select>
        @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">رقم الجوال</label>
        <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $user->phone ?? '') }}">
        @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>

<div class="row student-fields" style="display: {{ old('role', ($user->role ?? '')) == 'student' ? 'flex' : 'none' }}">
    <div class="col-md-6 mb-3">
        <label class="form-label">الرقم الجامعي</label>
        <input type="text" name="student_id" class="form-control @error('student_id') is-invalid @enderror" value="{{ old('student_id', $user->student_id ?? '') }}">
        @error('student_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">التخصص</label>
        <select name="specialization_id" class="form-select @error('specialization_id') is-invalid @enderror">
            <option value="">اختر التخصص</option>
            @foreach($specializations as $spec)
                <option value="{{ $spec->id }}" {{ old('specialization_id', $user->specialization_id ?? '') == $spec->id ? 'selected' : '' }}>{{ $spec->name_ar }}</option>
            @endforeach
        </select>
        @error('specialization_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>

<div class="row supervisor-fields" style="display: {{ old('role', ($user->role ?? '')) == 'supervisor' ? 'flex' : 'none' }}">
    <div class="col-md-6 mb-3">
        <label class="form-label">الرقم الوظيفي</label>
        <input type="text" name="employee_id" class="form-control @error('employee_id') is-invalid @enderror" value="{{ old('employee_id', $user->employee_id ?? '') }}">
        @error('employee_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">التخصص</label>
        <select name="specialization_id" class="form-select">
            <option value="">اختر التخصص</option>
            @foreach($specializations as $spec)
                <option value="{{ $spec->id }}" {{ old('specialization_id', $user->specialization_id ?? '') == $spec->id ? 'selected' : '' }}>{{ $spec->name_ar }}</option>
            @endforeach
        </select>
    </div>
</div>

<div class="mb-3 form-check">
    <input type="checkbox" name="is_active" value="1" class="form-check-input" id="is_active" {{ old('is_active', ($user->is_active ?? true)) ? 'checked' : '' }}>
    <label class="form-check-label" for="is_active">مفعل</label>
</div>

@push('scripts')
<script>
    const roleSelect = document.querySelector('select[name="role"]');
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