<div class="mb-3">
    <label class="form-label">الكلية <span class="text-danger">*</span></label>
    <select name="college_id" class="form-select @error('college_id') is-invalid @enderror" required>
        <option value="">اختر الكلية</option>
        @foreach($colleges as $college)
            <option value="{{ $college->id }}" {{ old('college_id', $department->college_id ?? '') == $college->id ? 'selected' : '' }}>
                {{ $college->name_ar }}
            </option>
        @endforeach
    </select>
    @error('college_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">اسم القسم (عربي) <span class="text-danger">*</span></label>
        <input type="text" name="name_ar" class="form-control @error('name_ar') is-invalid @enderror" value="{{ old('name_ar', $department->name_ar ?? '') }}" required>
        @error('name_ar')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">اسم القسم (إنجليزي)</label>
        <input type="text" name="name_en" class="form-control @error('name_en') is-invalid @enderror" value="{{ old('name_en', $department->name_en ?? '') }}">
        @error('name_en')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">الكود</label>
        <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code', $department->code ?? '') }}">
        @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6 mb-3">
        <div class="form-check mt-4">
            <input type="checkbox" name="is_active" value="1" class="form-check-input" id="is_active" {{ old('is_active', $department->is_active ?? true) ? 'checked' : '' }}>
            <label class="form-check-label" for="is_active">نشط</label>
        </div>
    </div>
</div>

<div class="mb-3">
    <label class="form-label">الوصف</label>
    <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description', $department->description ?? '') }}</textarea>
    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
