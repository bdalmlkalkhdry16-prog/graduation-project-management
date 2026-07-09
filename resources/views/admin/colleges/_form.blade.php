<div class="mb-3">
    <label class="form-label">الاسم بالعربية <span class="text-danger">*</span></label>
    <input type="text" name="name_ar" class="form-control @error('name_ar') is-invalid @enderror" value="{{ old('name_ar', $college->name_ar ?? '') }}" required>
    @error('name_ar')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="mb-3">
    <label class="form-label">الاسم بالإنجليزية</label>
    <input type="text" name="name_en" class="form-control @error('name_en') is-invalid @enderror" value="{{ old('name_en', $college->name_en ?? '') }}">
    @error('name_en')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">الكود</label>
        <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code', $college->code ?? '') }}">
        @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6 mb-3">
        <div class="form-check mt-4">
            <input type="checkbox" name="is_active" value="1" class="form-check-input" id="is_active" {{ old('is_active', $college->is_active ?? true) ? 'checked' : '' }}>
            <label class="form-check-label" for="is_active">نشطة</label>
        </div>
    </div>
</div>

<div class="mb-3">
    <label class="form-label">الوصف</label>
    <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description', $college->description ?? '') }}</textarea>
    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>