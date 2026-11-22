@extends('layouts.app')
@php($editing = $exam->exists)
@section('title', $editing ? 'تعديل اختبار' : 'اختبار جديد')
@section('content')
<div class="container">
    <div class="row justify-content-center"><div class="col-lg-9">
        <div class="card">
            <div class="card-header"><h4 class="mb-0">{{ $editing ? 'تعديل اختبار' : 'اختبار جديد' }}</h4></div>
            <div class="card-body">
                @include('layouts._errors')
                <form method="POST" action="{{ $editing ? route('exams.update', $exam) : route('exams.store') }}">
                    @csrf
                    @if ($editing) @method('PUT') @endif

                    <div class="mb-3">
                        <label class="form-label">الشعبة (المقرر والفصل)</label>
                        @if ($editing)
                            <div class="form-control-plaintext border rounded px-3 bg-light">{{ $exam->section->course->name_ar }} — شعبة {{ $exam->section->code }}</div>
                        @else
                            <select name="section_id" required class="form-select">
                                <option value="">اختر الشعبة</option>
                                @foreach ($sections as $section)
                                    <option value="{{ $section->id }}" @selected(old('section_id') == $section->id)>{{ $section->course->name_ar }} — {{ $section->code }} ({{ $section->academicTerm->academicYear->name ?? '' }} / {{ \App\Services\AcademicRecordService::semesterLabel($section->academicTerm->semester) }})</option>
                                @endforeach
                            </select>
                        @endif
                    </div>
                    <div class="row g-3">
                        <div class="col-sm-6"><label class="form-label">اسم الاختبار</label>
                            <input name="name" value="{{ old('name', $exam->name) }}" required class="form-control"></div>
                        <div class="col-sm-6"><label class="form-label">النوع</label>
                            <select name="type" required class="form-select">
                                @foreach (\App\Models\Exam::TYPES as $type)
                                    <option value="{{ $type }}" @selected(old('type', $exam->type) === $type)>{{ \App\Models\Exam::typeLabel($type) }}</option>
                                @endforeach
                            </select></div>
                        <div class="col-sm-6"><label class="form-label">التاريخ</label>
                            <input type="date" name="exam_date" value="{{ old('exam_date', $exam->exam_date?->format('Y-m-d')) }}" required class="form-control"></div>
                        <div class="col-sm-6"><label class="form-label">وقت البداية (اختياري)</label>
                            <input type="time" name="start_time" value="{{ old('start_time', $exam->start_time ? substr($exam->start_time, 0, 5) : '') }}" class="form-control"></div>
                        <div class="col-sm-6"><label class="form-label">المدة (دقائق)</label>
                            <input type="number" min="1" max="600" name="duration_minutes" value="{{ old('duration_minutes', $exam->duration_minutes) }}" required class="form-control"></div>
                        <div class="col-sm-6"><label class="form-label">الدرجة النهائية للاختبار</label>
                            <input type="number" step="0.01" min="0.01" name="max_score" value="{{ old('max_score', $exam->max_score) }}" required class="form-control"></div>
                        <div class="col-sm-6"><label class="form-label">الدرجة المخصصة من مجموع المقرر</label>
                            <input type="number" step="0.01" min="0" name="weight" value="{{ old('weight', $exam->weight) }}" required class="form-control"></div>
                    </div>
                    <div class="mt-4">
                        <button class="btn btn-primary"><i class="fas fa-save me-1"></i> حفظ</button>
                        <a href="{{ $editing ? route('exams.show', $exam) : route('exams.index') }}" class="btn btn-outline-secondary">إلغاء</a>
                    </div>
                </form>
            </div>
        </div>
    </div></div>
</div>
@endsection
