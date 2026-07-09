<div class="mb-3">
    <label class="form-label">القسم <span class="text-danger">*</span></label>
    <select name="department_id" class="form-select @error('department_id') is-invalid @enderror" required>
        <option value="">اختر القسم</option>
        @foreach($departments as $dept)
            <option value="{{ $dept->id }}" {{ old('department_id', $specialization->department_id ?? '') == $dept->id ? 'selected' : '' }}>
                {{ $dept->name_ar }}
            </option>
        @endforeach
    </select>
    @error('department_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">اسم التخصص (عربي) <span class="text-danger">*</span></label>
        <input type="text" name="name_ar" class="form-control @error('name_ar') is-invalid @enderror" value="{{ old('name_ar', $specialization->name_ar ?? '') }}" required>
        @error('name_ar')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">اسم التخصص (إنجليزي)</label>
        <input type="text" name="name_en" class="form-control @error('name_en') is-invalid @enderror" value="{{ old('name_en', $specialization->name_en ?? '') }}">
        @error('name_en')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>

<div class="row">
    <div class="col-md-4 mb-3">
        <label class="form-label">الكود</label>
        <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code', $specialization->code ?? '') }}">
        @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">مدة الدراسة (سنوات)</label>
        <input type="number" name="duration_years" class="form-control @error('duration_years') is-invalid @enderror" value="{{ old('duration_years', $specialization->duration_years ?? 4) }}" min="1" max="6">
        @error('duration_years')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4 mb-3">
        <div class="form-check mt-4">
            <input type="checkbox" name="is_active" value="1" class="form-check-input" id="is_active" {{ old('is_active', $specialization->is_active ?? true) ? 'checked' : '' }}>
            <label class="form-check-label" for="is_active">نشط</label>
        </div>
    </div>
</div>

<div class="mb-3">
    <label class="form-label">الوصف</label>
    <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description', $specialization->description ?? '') }}</textarea>
    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>