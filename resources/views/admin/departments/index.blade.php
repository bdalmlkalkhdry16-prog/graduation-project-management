@extends('layouts.app')

@section('title', 'إدارة الأقسام')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>الأقسام الأكاديمية</h2>
            <a href="{{ route('admin.departments.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i> قسم جديد
            </a>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">الكلية</label>
                        <select name="college_id" class="form-select">
                            <option value="">الكل</option>
                            @foreach($colleges as $college)
                                <option value="{{ $college->id }}" {{ request('college_id') == $college->id ? 'selected' : '' }}>
                                    {{ $college->name_ar }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">بحث</label>
                        <input type="text" name="search" class="form-control" placeholder="اسم القسم أو الكود..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
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
                        32
                        <th>#</th>
                        <th>اسم القسم (عربي)</th>
                        <th>اسم القسم (إنجليزي)</th>
                        <th>الكلية</th>
                        <th>الكود</th>
                        <th>الحالة</th>
                        <th>الإجراءات</th>
                        </thead>
                        <tbody>
                        @forelse($departments as $dept)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $dept->name_ar }}</td>
                                <td>{{ $dept->name_en ?? '-' }}</td>
                                <td>{{ $dept->college->name_ar ?? '-' }}</td>
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
                                    <a href="{{ route('admin.departments.edit', $dept) }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-edit"></i></a>
                                    <form action="{{ route('admin.departments.toggle-status', $dept) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-{{ $dept->is_active ? 'warning' : 'success' }}" onclick="return confirm('تغيير حالة القسم؟')">
                                            <i class="fas fa-{{ $dept->is_active ? 'ban' : 'check' }}"></i>
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.departments.destroy', $dept) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('حذف القسم نهائياً؟')"><i class="fas fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">لا توجد أقسام</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-3">
                    {{ $departments->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
