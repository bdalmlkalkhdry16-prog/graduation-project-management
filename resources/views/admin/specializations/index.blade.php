@extends('layouts.app')

@section('title', 'إدارة التخصصات')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>التخصصات الأكاديمية</h2>
            <a href="{{ route('admin.specializations.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i> تخصص جديد
            </a>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">القسم</label>
                        <select name="department_id" class="form-select">
                            <option value="">الكل</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                                    {{ $dept->name_ar }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">بحث</label>
                        <input type="text" name="search" class="form-control" placeholder="اسم التخصص أو الكود..." value="{{ request('search') }}">
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
                        <th>اسم التخصص (عربي)</th>
                        <th>القسم</th>
                        <th>الكود</th>
                        <th>مدة الدراسة</th>
                        <th>الحالة</th>
                        <th>الإجراءات</th>
                        </thead>
                        <tbody>
                        @forelse($specializations as $spec)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $spec->name_ar }}</td>
                                <td>{{ $spec->department->name_ar ?? '-' }}</td>
                                <td>{{ $spec->code ?? '-' }}</td>
                                <td>{{ $spec->duration_years }} سنوات</td>
                                <td>
                                    @if($spec->is_active)
                                        <span class="badge bg-success">نشط</span>
                                    @else
                                        <span class="badge bg-secondary">غير نشط</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.specializations.show', $spec) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a>
                                    <a href="{{ route('admin.specializations.edit', $spec) }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-edit"></i></a>
                                    <form action="{{ route('admin.specializations.toggle-status', $spec) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-{{ $spec->is_active ? 'warning' : 'success' }}" onclick="return confirm('تغيير حالة التخصص؟')">
                                            <i class="fas fa-{{ $spec->is_active ? 'ban' : 'check' }}"></i>
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.specializations.destroy', $spec) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('حذف التخصص نهائياً؟')"><i class="fas fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">لا توجد تخصصات</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-3">
                    {{ $specializations->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
