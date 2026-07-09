@extends('layouts.app')

@section('title', 'الصفحة غير موجودة')

@section('content')
    <div class="container">
        <div class="row justify-content-center text-center py-5">
            <div class="col-md-6">
                <div class="mb-4">
                    <i class="fas fa-search fa-5x text-muted"></i>
                </div>
                <h1 class="display-1 fw-bold text-muted">404</h1>
                <h3 class="mb-3">عذراً! الصفحة غير موجودة</h3>
                <p class="text-muted mb-4">الصفحة التي تبحث عنها غير موجودة أو تم نقلها</p>
                <a href="{{ url('/') }}" class="btn btn-primary">
                    <i class="fas fa-home me-2"></i> العودة للرئيسية
                </a>
            </div>
        </div>
    </div>
@endsection
