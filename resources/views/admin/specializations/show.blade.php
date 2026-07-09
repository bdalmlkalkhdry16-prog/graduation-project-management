@extends('layouts.app')

@section('title', $specialization->name_ar)

@section('content')
    <div class="container">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0">{{ $specialization->name_ar }}</h4>
                <div>
                    <a href="{{ route('admin.specializations.edit', $specialization) }}" class="btn btn-sm btn-primary">تعديل</a>
                    <a href="{{ route('admin.specializations.index') }}" class="btn btn-sm btn-secondary">عودة</a>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>الاسم بالعربية:</strong> {{ $specialization->name_ar }}
                    </div>
                    <div class="col-md-6">
                        <strong>الاسم بالإنجليزية:</strong> {{ $specialization->name_en ?? '-' }}
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>القسم:</strong> {{ $specialization->department->name_ar ?? '-' }}
                    </div>
                    <div class="col-md-6">
                        <strong>الكود:</strong> {{ $specialization->code ?? '-' }}
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>مدة الدراسة:</strong> {{ $specialization->duration_years }} سنوات
                    </div>
                    <div class="col-md-6">
                        <strong>الحالة:</strong>
                        @if($specialization->is_active)
                            <span class="badge bg-success">نشط</span>
                        @else
                            <span class="badge bg-secondary">غير نشط</span>
                        @endif
                    </div>
                </div>
                <div class="mb-3">
                    <strong>الوصف:</strong><br>
                    {{ $specialization->description ?? '-' }}
                </div>

                <hr>
                <h5>المشاريع المرتبطة</h5>
                @if($specialization->projects->count())
                    <ul>
                        @foreach($specialization->projects as $project)
                            <li><a href="{{ route('projects.show', $project) }}">{{ $project->title_ar }}</a></li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-muted">لا توجد مشاريع مسجلة لهذا التخصص.</p>
                @endif
            </div>
        </div>
    </div>
@endsection
