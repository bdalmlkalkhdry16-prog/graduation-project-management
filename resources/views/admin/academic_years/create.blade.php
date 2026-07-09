@extends('layouts.app')

@section('title', 'إضافة سنة أكاديمية')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">إضافة سنة أكاديمية جديدة</div>
                <div class="card-body">
                    <form action="{{ route('admin.academic-years.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">الاسم (مثال: 2024-2025)</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">السنة الرقمية (مثال: 2025)</label>
                            <input type="number" name="year" class="form-control @error('year') is-invalid @enderror" required>
                            @error('year')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">تاريخ البداية</label>
                            <input type="date" name="start_date" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">تاريخ النهاية</label>
                            <input type="date" name="end_date" class="form-control">
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">حفظ</button>
                            <a href="{{ route('admin.academic-years.index') }}" class="btn btn-outline-secondary mt-2">إلغاء</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection