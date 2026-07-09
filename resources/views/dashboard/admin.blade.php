@extends('layouts.app')

@section('title', 'لوحة تحكم المدير')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>لوحة تحكم المدير</h2>
            <div class="text-muted">{{ now()->format('Y-m-d') }}</div>
        </div>

        <!-- Stats Cards -->
        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="card stat-card text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-2">الطلاب</h6>
                                <h3 class="mb-0">{{ number_format($stats['total_students']) }}</h3>
                            </div>
                            <i class="fas fa-users fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card bg-success">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-2">المشرفين</h6>
                                <h3 class="mb-0">{{ number_format($stats['total_supervisors']) }}</h3>
                            </div>
                            <i class="fas fa-chalkboard-user fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card bg-info">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-2">المشاريع</h6>
                                <h3 class="mb-0">{{ number_format($stats['total_projects']) }}</h3>
                            </div>
                            <i class="fas fa-project-diagram fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card bg-warning">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-2">التخصصات</h6>
                                <h3 class="mb-0">{{ number_format($stats['total_specializations']) }}</h3>
                            </div>
                            <i class="fas fa-graduation-cap fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- إدارة المستخدمين -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">إدارة المستخدمين</h5>
                <a href="{{ route('admin.users.create') }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus me-1"></i> مستخدم جديد
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                        32
                        <th>الاسم</th>
                        <th>البريد الإلكتروني</th>
                        <th>الدور</th>
                        <th>الحالة</th>
                        <th>الإجراءات</th>
                        </thead>
                        <tbody>
                        @foreach($stats['recent_users'] as $user)
                            <tr>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    <span class="badge bg-{{ $user->role == 'admin' ? 'danger' : ($user->role == 'supervisor' ? 'info' : 'success') }}">
                                        {{ $user->role_name }}
                                    </span>
                                </td>
                                <td>
                                    @if($user->is_active)
                                        <span class="badge bg-success">نشط</span>
                                    @else
                                        <span class="badge bg-secondary">معطل</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.users.show', $user) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a>
                                    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-edit"></i></a>
                                    @if($user->id != auth()->id())
                                        <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('حذف المستخدم؟')"><i class="fas fa-trash"></i></button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="p-3 text-center">
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-primary btn-sm">عرض جميع المستخدمين</a>
                </div>
            </div>
        </div>

        <!-- روابط الإدارة السريعة -->
        <div class="row g-4">
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="mb-0">إدارة الكليات</h5>
                    </div>
                    <div class="card-body">
                        <p>إضافة وتعديل وحذف الكليات الأكاديمية</p>
                        <a href="{{ route('admin.colleges.index') }}" class="btn btn-outline-primary">إدارة الكليات</a>
                        <a href="{{ route('admin.colleges.create') }}" class="btn btn-primary">إضافة كلية</a>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="mb-0">إدارة الأقسام</h5>
                    </div>
                    <div class="card-body">
                        <p>إضافة وتعديل وحذف الأقسام الأكاديمية</p>
                        <a href="{{ route('admin.departments.index') }}" class="btn btn-outline-primary">إدارة الأقسام</a>
                        <a href="{{ route('admin.departments.create') }}" class="btn btn-primary">إضافة قسم</a>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="mb-0">إدارة التخصصات</h5>
                    </div>
                    <div class="card-body">
                        <p>إضافة وتعديل وحذف التخصصات</p>
                        <a href="{{ route('admin.specializations.index') }}" class="btn btn-outline-primary">إدارة التخصصات</a>
                        <a href="{{ route('admin.specializations.create') }}" class="btn btn-primary">إضافة تخصص</a>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="mb-0">التقارير والإحصائيات</h5>
                    </div>
                    <div class="card-body">
                        <p>إنشاء تقارير متنوعة عن المشاريع والطلاب والمشرفين</p>
                        <a href="{{ route('admin.reports.index') }}" class="btn btn-outline-primary">التقارير</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
