@extends('layouts.app')

@section('title', 'تفاصيل المستخدم')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">{{ $user->name }}</h4>
            <div>
                <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-primary">تعديل</a>
                <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-secondary">عودة</a>
            </div>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-6">
                    <strong>الاسم الكامل:</strong> {{ $user->name }}
                </div>
                <div class="col-md-6">
                    <strong>البريد الإلكتروني:</strong> {{ $user->email }}
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <strong>الدور:</strong> 
                    <span class="badge bg-{{ $user->role == 'admin' ? 'danger' : ($user->role == 'supervisor' ? 'info' : 'success') }}">
                        {{ $user->role_name }}
                    </span>
                </div>
                <div class="col-md-6">
                    <strong>الحالة:</strong>
                    @if($user->is_active)
                        <span class="badge bg-success">نشط</span>
                    @else
                        <span class="badge bg-secondary">غير نشط</span>
                    @endif
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <strong>رقم الجوال:</strong> {{ $user->phone ?? '-' }}
                </div>
                <div class="col-md-6">
                    <strong>تاريخ التسجيل:</strong> {{ $user->created_at->format('Y-m-d') }}
                </div>
            </div>
            @if($user->role == 'student')
            <div class="row mb-3">
                <div class="col-md-6">
                    <strong>الرقم الجامعي:</strong> {{ $user->student_id ?? '-' }}
                </div>
                <div class="col-md-6">
                    <strong>التخصص:</strong> {{ $user->specialization->name_ar ?? '-' }}
                </div>
            </div>
            @endif
            @if($user->role == 'supervisor')
            <div class="row mb-3">
                <div class="col-md-6">
                    <strong>الرقم الوظيفي:</strong> {{ $user->employee_id ?? '-' }}
                </div>
                <div class="col-md-6">
                    <strong>التخصص:</strong> {{ $user->specialization->name_ar ?? '-' }}
                </div>
            </div>
            @endif
            <hr>
            <h5>المشاريع المرتبطة</h5>
            @if($user->isStudent())
                @if($user->projects->count())
                    <ul>
                        @foreach($user->projects as $project)
                            <li><a href="{{ route('projects.show', $project) }}">{{ $project->title_ar }}</a></li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-muted">لا توجد مشاريع مرتبطة.</p>
                @endif
            @elseif($user->isSupervisor())
                @if($user->supervisedProjects->count())
                    <ul>
                        @foreach($user->supervisedProjects as $project)
                            <li><a href="{{ route('projects.show', $project) }}">{{ $project->title_ar }}</a></li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-muted">لا توجد مشاريع يشرف عليها.</p>
                @endif
            @endif
        </div>
    </div>
</div>
@endsection