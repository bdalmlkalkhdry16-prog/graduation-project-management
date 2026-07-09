@extends('layouts.app')

@section('title', 'تعديل مشروع')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0">تعديل المشروع: {{ $project->title_ar }}</h4>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('projects.update', $project) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <label for="title_ar" class="form-label">عنوان المشروع (عربي) <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('title_ar') is-invalid @enderror" id="title_ar" name="title_ar" value="{{ old('title_ar', $project->title_ar) }}" required>
                                @error('title_ar')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="mb-3">
                                <label for="title_en" class="form-label">عنوان المشروع (إنجليزي)</label>
                                <input type="text" class="form-control @error('title_en') is-invalid @enderror" id="title_en" name="title_en" value="{{ old('title_en', $project->title_en) }}">
                                @error('title_en')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="mb-3">
                                <label for="abstract_ar" class="form-label">الملخص (عربي) <span class="text-danger">*</span></label>
                                <textarea class="form-control @error('abstract_ar') is-invalid @enderror" id="abstract_ar" name="abstract_ar" rows="4" required>{{ old('abstract_ar', $project->abstract_ar) }}</textarea>
                                @error('abstract_ar')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="mb-3">
                                <label for="abstract_en" class="form-label">الملخص (إنجليزي)</label>
                                <textarea class="form-control @error('abstract_en') is-invalid @enderror" id="abstract_en" name="abstract_en" rows="4">{{ old('abstract_en', $project->abstract_en) }}</textarea>
                                @error('abstract_en')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="mb-3">
                                <label for="keywords" class="form-label">الكلمات المفتاحية</label>
                                <input type="text" class="form-control @error('keywords') is-invalid @enderror" id="keywords" name="keywords" value="{{ old('keywords', $project->keywords) }}">
                                @error('keywords')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="supervisor_id" class="form-label">المشرف <span class="text-danger">*</span></label>
                                    <select name="supervisor_id" id="supervisor_id" class="form-select @error('supervisor_id') is-invalid @enderror" required>
                                        <option value="">اختر المشرف</option>
                                        @foreach($supervisors as $supervisor)
                                            <option value="{{ $supervisor->id }}" {{ old('supervisor_id', $project->supervisor_id) == $supervisor->id ? 'selected' : '' }}>{{ $supervisor->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('supervisor_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="specialization_id" class="form-label">التخصص <span class="text-danger">*</span></label>
                                    <select name="specialization_id" id="specialization_id" class="form-select @error('specialization_id') is-invalid @enderror" required>
                                        <option value="">اختر التخصص</option>
                                        @foreach($specializations as $spec)
                                            <option value="{{ $spec->id }}" {{ old('specialization_id', $project->specialization_id) == $spec->id ? 'selected' : '' }}>{{ $spec->name_ar }}</option>
                                        @endforeach
                                    </select>
                                    @error('specialization_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="academic_year" class="form-label">السنة الأكاديمية <span class="text-danger">*</span></label>
                                    <select name="academic_year" id="academic_year" class="form-select @error('academic_year') is-invalid @enderror" required>
                                        @for($year = date('Y'); $year >= 2020; $year--)
                                            <option value="{{ $year }}" {{ old('academic_year', $project->academic_year) == $year ? 'selected' : '' }}>{{ $year }}</option>
                                        @endfor
                                    </select>
                                    @error('academic_year')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="semester" class="form-label">الفصل الدراسي <span class="text-danger">*</span></label>
                                    <select name="semester" id="semester" class="form-select @error('semester') is-invalid @enderror" required>
                                        <option value="first" {{ old('semester', $project->semester) == 'first' ? 'selected' : '' }}>الفصل الأول</option>
                                        <option value="second" {{ old('semester', $project->semester) == 'second' ? 'selected' : '' }}>الفصل الثاني</option>
                                        <option value="summer" {{ old('semester', $project->semester) == 'summer' ? 'selected' : '' }}>الفصل الصيفي</option>
                                    </select>
                                    @error('semester')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">تحديث المشروع</button>
                                <a href="{{ route('projects.show', $project) }}" class="btn btn-outline-secondary">إلغاء</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
