@extends('layouts.app')

@section('title', 'مشروع جديد')

@section('content')
<div class="container">

    <div class="row justify-content-center">
        <div class="col-md-10">

            <div class="card shadow-sm">

                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">📌 إضافة مشروع جديد</h4>
                </div>

                <div class="card-body">

                    <!-- رسالة توضيحية -->
                    <div class="alert alert-info">
                        الرجاء تعبئة جميع البيانات بدقة قبل إرسال المشروع للمراجعة.
                    </div>

                    <form action="{{ route('projects.store') }}" method="POST">
                        @csrf

                        <!-- العنوان -->
                        <div class="mb-3">
                            <label class="form-label">عنوان المشروع (عربي) *</label>
                            <input type="text" name="title_ar"
                                   class="form-control @error('title_ar') is-invalid @enderror"
                                   value="{{ old('title_ar') }}" required>
                            @error('title_ar')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">عنوان المشروع (إنجليزي)</label>
                            <input type="text" name="title_en"
                                   class="form-control @error('title_en') is-invalid @enderror"
                                   value="{{ old('title_en') }}">
                            @error('title_en')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <!-- الملخص -->
                        <div class="mb-3">
                            <label class="form-label">الملخص (عربي) *</label>
                            <textarea name="abstract_ar" rows="4"
                                      class="form-control @error('abstract_ar') is-invalid @enderror"
                                      required>{{ old('abstract_ar') }}</textarea>
                            @error('abstract_ar')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">الملخص (إنجليزي)</label>
                            <textarea name="abstract_en" rows="4"
                                      class="form-control @error('abstract_en') is-invalid @enderror">{{ old('abstract_en') }}</textarea>
                            @error('abstract_en')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <!-- كلمات مفتاحية -->
                        <div class="mb-3">
                            <label class="form-label">الكلمات المفتاحية</label>
                            <input type="text" name="keywords"
                                   class="form-control"
                                   placeholder="مثال: ذكاء اصطناعي, ويب"
                                   value="{{ old('keywords') }}">
                        </div>

                        <!-- المشرف + التخصص -->
                        <div class="row">

                            <div class="col-md-6 mb-3">
                                <label class="form-label">المشرف *</label>
                                <select name="supervisor_id"
                                        class="form-select @error('supervisor_id') is-invalid @enderror" required>

                                    <option value="">اختر المشرف</option>

                                    @foreach($supervisors as $supervisor)
                                        <option value="{{ $supervisor->id }}"
                                            {{ old('supervisor_id') == $supervisor->id ? 'selected' : '' }}>
                                            {{ $supervisor->name }}
                                        </option>
                                    @endforeach

                                </select>
                                @error('supervisor_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">التخصص *</label>
                                <select name="specialization_id"
                                        class="form-select @error('specialization_id') is-invalid @enderror" required>

                                    <option value="">اختر التخصص</option>

                                    @foreach($specializations as $spec)
                                        <option value="{{ $spec->id }}"
                                            {{ old('specialization_id') == $spec->id ? 'selected' : '' }}>
                                            {{ $spec->name_ar }}
                                        </option>
                                    @endforeach

                                </select>
                                @error('specialization_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                        </div>

                        <!-- السنة + الفصل -->
                        <div class="row">

                            <div class="col-md-6 mb-3">
                                <label class="form-label">السنة الأكاديمية *</label>
                                <select name="academic_year"
                                        class="form-select @error('academic_year') is-invalid @enderror" required>

                                    @for($year = date('Y'); $year >= 2020; $year--)
                                        <option value="{{ $year }}"
                                            {{ old('academic_year') == $year ? 'selected' : '' }}>
                                            {{ $year }}
                                        </option>
                                    @endfor

                                </select>
                                @error('academic_year')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">الفصل الدراسي *</label>
                                <select name="semester"
                                        class="form-select @error('semester') is-invalid @enderror" required>

                                    <option value="first">الفصل الأول</option>
                                    <option value="second">الفصل الثاني</option>
                                    <option value="summer">الفصل الصيفي</option>

                                </select>
                                @error('semester')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                        </div>

                        <!-- أزرار -->
                        <div class="d-grid gap-2 mt-4">

                            <button class="btn btn-success">
                                💾 حفظ المشروع
                            </button>

                            <a href="{{ route('projects.index') }}" class="btn btn-outline-secondary">
                                إلغاء
                            </a>

                        </div>

                    </form>

                </div>

            </div>

        </div>
    </div>

</div>
@endsection