@extends('layouts.app')

@section('title', 'طلب خدمة جديد')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header"><h4 class="mb-0">طلب خدمة جديد</h4></div>
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

                    <form action="{{ route('student.service-requests.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">نوع الطلب</label>
                            <select name="type" class="form-select" required>
                                <option value="">— اختر نوع الطلب —</option>
                                @foreach ($types as $key => $label)
                                    <option value="{{ $key }}" @selected(old('type') === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3" id="appeal-course" style="display:none">
                            <label class="form-label">المقرر المتظلَّم عليه</label>
                            <select name="enrollment_id" class="form-select">
                                <option value="">— اختر المقرر —</option>
                                @foreach ($appealable as $enrollment)
                                    <option value="{{ $enrollment->id }}" @selected((string) old('enrollment_id') === (string) $enrollment->id)>
                                        {{ $enrollment->section->course->name_ar }} — {{ $enrollment->section->academicTerm?->label() }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text">تظهر المقررات التي لها درجة منشورة فقط. اذكر سبب التظلم في التفاصيل.</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">تفاصيل إضافية</label>
                            <textarea name="description" class="form-control" rows="4">{{ old('description') }}</textarea>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">إرسال الطلب</button>
                            <a href="{{ route('student.service-requests.index') }}" class="btn btn-outline-secondary">إلغاء</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@push('scripts')
<script>
    (function () {
        var type = document.querySelector('select[name="type"]');
        var box = document.getElementById('appeal-course');
        function toggle() { box.style.display = type.value === 'grade_appeal' ? '' : 'none'; }
        type.addEventListener('change', toggle); toggle();
    })();
</script>
@endpush
@endsection