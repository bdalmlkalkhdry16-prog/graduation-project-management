@extends('layouts.app')

@section('title', $department->name_ar)

@section('content')
    <div class="container">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0">{{ $department->name_ar }}</h4>
                <div>
                    <a href="{{ route('admin.departments.edit', $department) }}" class="btn btn-sm btn-primary">تعديل</a>
                    <a href="{{ route('admin.departments.index') }}" class="btn btn-sm btn-secondary">عودة</a>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>الاسم بالعربية:</strong> {{ $department->name_ar }}
                    </div>
                    <div class="col-md-6">
                        <strong>الاسم بالإنجليزية:</strong> {{ $department->name_en ?? '-' }}
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>الكلية:</strong> {{ $department->college->name_ar ?? '-' }}
                    </div>
                    <div class="col-md-6">
                        <strong>الكود:</strong> {{ $department->code ?? '-' }}
                    </div>
                </div>
                <div class="mb-3">
                    <strong>الوصف:</strong><br>
                    {{ $department->description ?? '-' }}
                </div>
                <div class="mb-3">
                    <strong>الحالة:</strong>
                    @if($department->is_active)
                        <span class="badge bg-success">نشط</span>
                    @else
                        <span class="badge bg-secondary">غير نشط</span>
                    @endif
                </div>

                <hr>
                <h5>التخصصات التابعة</h5>
                @if($department->specializations->count())
                    <ul>
                        @foreach($department->specializations as $spec)
                            <li>{{ $spec->name_ar }} ({{ $spec->code }})</li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-muted">لا توجد تخصصات تابعة لهذا القسم.</p>
                @endif
            </div>
        </div>
    </div>
@endsection
