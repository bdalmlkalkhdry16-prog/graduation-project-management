@extends('layouts.app')

@section('title', 'إدارة السنوات الأكاديمية')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>السنوات الأكاديمية</h2>
        <a href="{{ route('admin.academic-years.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> إضافة سنة جديدة
        </a>
    </div>

    @if($activeYear)
        <div class="alert alert-success">
            <strong>السنة النشطة حالياً:</strong> {{ $activeYear->name }} ({{ $activeYear->year }})
        </div>
    @endif

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>الاسم</th>
                            <th>السنة الرقمية</th>
                            <th>تاريخ البداية</th>
                            <th>تاريخ النهاية</th>
                            <th>نشطة؟</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($academicYears as $item)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $item->name }}</td>
                            <td>{{ $item->year }}</td>
                            <td>{{ $item->start_date ?? '-' }}</td>
                            <td>{{ $item->end_date ?? '-' }}</td>
                            <td>
                                @if($item->is_active)
                                    <span class="badge bg-success">نشطة</span>
                                @else
                                    <span class="badge bg-secondary">غير نشطة</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.academic-years.edit', $item) }}" class="btn btn-sm btn-outline-primary">تعديل</a>
                                @if(!$item->is_active)
                                    <form action="{{ route('admin.academic-years.set-active', $item) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button class="btn btn-sm btn-outline-success" onclick="return confirm('تعيين هذه السنة كنشطة؟')">تعيين نشطة</button>
                                    </form>
                                    <form action="{{ route('admin.academic-years.destroy', $item) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" onclick="return confirm('حذف السنة؟')">حذف</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">لا توجد سنوات أكاديمية</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-3">
                {{ $academicYears->links() }}
            </div>
        </div>
    </div>
</div>
@endsection