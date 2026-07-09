@extends('layouts.app')

@section('title', 'إدارة المستخدمين')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>المستخدمين</h2>
            <a href="{{ route('admin.users.create') }}" class="btn btn-primary"><i class="fas fa-plus me-2"></i> مستخدم جديد</a>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">الدور</label>
                        <select name="role" class="form-select">
                            <option value="">الكل</option>
                            <option value="student" {{ request('role') == 'student' ? 'selected' : '' }}>طالب</option>
                            <option value="supervisor" {{ request('role') == 'supervisor' ? 'selected' : '' }}>مشرف</option>
                            <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>مدير</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">الحالة</label>
                        <select name="is_active" class="form-select">
                            <option value="">الكل</option>
                            <option value="1" {{ request('is_active') == '1' ? 'selected' : '' }}>نشط</option>
                            <option value="0" {{ request('is_active') == '0' ? 'selected' : '' }}>غير نشط</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">بحث</label>
                        <input type="text" name="search" class="form-control" placeholder="اسم أو بريد..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">بحث</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>الاسم</th>
                            <th>البريد</th>
                            <th>الدور</th>
                            <th>الحالة</th>
                            <th>تاريخ التسجيل</th>
                            <th>الإجراءات</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($users as $user)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td><span class="badge bg-{{ $user->role == 'admin' ? 'danger' : ($user->role == 'supervisor' ? 'info' : 'success') }}">{{ $user->role_name }}</span></td>
                                <td>
                                    @if($user->is_active)
                                        <span class="badge bg-success">نشط</span>
                                    @else
                                        <span class="badge bg-secondary">معطل</span>
                                    @endif
                                </td>
                                <td>{{ $user->created_at->format('Y-m-d') }}</td>
                                <td>
                                    <a href="{{ route('admin.users.show', $user) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a>
                                    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-edit"></i></a>
                                    <form action="{{ route('admin.users.toggle-status', $user) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-{{ $user->is_active ? 'warning' : 'success' }}" onclick="return confirm('تغيير حالة المستخدم؟')">
                                            <i class="fas fa-{{ $user->is_active ? 'ban' : 'check' }}"></i>
                                        </button>
                                    </form>
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
                <div class="p-3">
                    {{ $users->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
