@extends('layouts.app')

@section('title', $college->name_ar)

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">{{ $college->name_ar }}</h4>
            <div>
                <a href="{{ route('admin.colleges.edit', $college) }}" class="btn btn-sm btn-primary">تعديل</a>
                <a href="{{ route('admin.colleges.index') }}" class="btn btn-sm btn-secondary">عودة</a>
            </div>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-6">
                    <strong>الاسم بالعربية:</strong> {{ $college->name_ar }}
                </div>
                <div class="col-md-6">
                    <strong>الاسم بالإنجليزية:</strong> {{ $college->name_en ?? '-' }}
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <strong>الكود:</strong> {{ $college->code ?? '-' }}
                </div>
                <div class="col-md-6">
                    <strong>الحالة:</strong>
                    @if($college->is_active)
                        <span class="badge bg-success">نشطة</span>
                    @else
                        <span class="badge bg-secondary">غير نشطة</span>
                    @endif
                </div>
            </div>
            <div class="mb-3">
                <strong>الوصف:</strong><br>
                {{ $college->description ?? '-' }}
            </div>

            <hr>
            <h5>الأقسام التابعة لهذه الكلية</h5>
            @if($college->departments->count())
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            32
                                <th>#</th>
                                <th>اسم القسم (عربي)</th>
                                <th>الكود</th>
                                <th>الحالة</th>
                                <th></th>
                            </thead>
                        <tbody>
                            @foreach($college->departments as $dept)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $dept->name_ar }}</td>
                                    <td>{{ $dept->code ?? '-' }}</td>
                                    <td>
                                        @if($dept->is_active)
                                            <span class="badge bg-success">نشط</span>
                                        @else
                                            <span class="badge bg-secondary">غير نشط</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.departments.show', $dept) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-muted">لا توجد أقسام تابعة لهذه الكلية.</p>
            @endif
        </div>
    </div>
</div>
@endsection