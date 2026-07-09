@extends('layouts.app')

@section('title', 'إدارة الكليات')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>الكليات</h2>
        <a href="{{ route('admin.colleges.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i> كلية جديدة
        </a>
    </div>

    <!-- شريط البحث والفلترة -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-5">
                    <label class="form-label">بحث</label>
                    <input type="text" name="search" class="form-control" placeholder="اسم الكلية أو الكود..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">الحالة</label>
                    <select name="is_active" class="form-select">
                        <option value="">الكل</option>
                        <option value="1" {{ request('is_active') == '1' ? 'selected' : '' }}>نشطة</option>
                        <option value="0" {{ request('is_active') == '0' ? 'selected' : '' }}>غير نشطة</option>
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">بحث</button>
                </div>
            </form>
        </div>
    </div>

    <!-- جدول الكليات -->
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        32
                            <th>#</th>
                            <th>الاسم (عربي)</th>
                            <th>الاسم (إنجليزي)</th>
                            <th>الكود</th>
                            <th>الحالة</th>
                            <th>تاريخ الإضافة</th>
                            <th>الإجراءات</th>
                        </thead>
                    <tbody>
                        @forelse($colleges as $college)
                        32
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $college->name_ar }}</td>
                            <td>{{ $college->name_en ?? '-' }}</td>
                            <td>{{ $college->code ?? '-' }}</td>
                            <td>
                                @if($college->is_active)
                                    <span class="badge bg-success">نشطة</span>
                                @else
                                    <span class="badge bg-secondary">غير نشطة</span>
                                @endif
                            </td>
                            <td>{{ $college->created_at->format('Y-m-d') }}</td>
                            <td>
                                <a href="{{ route('admin.colleges.show', $college) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a>
                                <a href="{{ route('admin.colleges.edit', $college) }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-edit"></i></a>
                                <form action="{{ route('admin.colleges.toggle-status', $college) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-{{ $college->is_active ? 'warning' : 'success' }}" onclick="return confirm('تغيير حالة الكلية؟')">
                                        <i class="fas fa-{{ $college->is_active ? 'ban' : 'check' }}"></i>
                                    </button>
                                </form>
                                <form action="{{ route('admin.colleges.destroy', $college) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('هل أنت متأكد من حذف الكلية؟ لا يمكن التراجع عن الحذف.')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">لا توجد كليات</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-3">
                {{ $colleges->links() }}
            </div>
        </div>
    </div>
</div>
@endsection