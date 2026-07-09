@extends('layouts.app')

@section('title', 'تعديل سنة أكاديمية')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">تعديل السنة الأكاديمية</div>
                <div class="card-body">
                    <form action="{{ route('admin.academic-years.update', $academicYear) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label class="form-label">الاسم</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $academicYear->name) }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">السنة الرقمية</label>
                            <input type="number" name="year" class="form-control" value="{{ old('year', $academicYear->year) }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">تاريخ البداية</label>
                            <input type="date" name="start_date" class="form-control" value="{{ old('start_date', $academicYear->start_date) }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">تاريخ النهاية</label>
                            <input type="date" name="end_date" class="form-control" value="{{ old('end_date', $academicYear->end_date) }}">
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">تحديث</button>
                            <a href="{{ route('admin.academic-years.index') }}" class="btn btn-outline-secondary mt-2">إلغاء</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection