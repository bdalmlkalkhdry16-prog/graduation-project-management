@extends('layouts.app')

@section('title', 'المشاريع قيد المراجعة')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>المشاريع قيد المراجعة</h2>
        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-right me-1"></i> العودة للوحة التحكم
        </a>
    </div>

    <!-- فلترة وبحث -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('projects.pending_review') }}" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">التخصص</label>
                    <select name="specialization_id" class="form-select">
                        <option value="">الكل</option>
                        @foreach($specializations as $spec)
                            <option value="{{ $spec->id }}" {{ request('specialization_id') == $spec->id ? 'selected' : '' }}>
                                {{ $spec->name_ar }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-5">
                    <label class="form-label">بحث</label>
                    <input type="text" name="search" class="form-control" placeholder="عنوان المشروع..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search me-2"></i> بحث</button>
                </div>
            </form>
        </div>
    </div>

    <!-- جدول المشاريع -->
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>عنوان المشروع</th>
                            <th>الطلاب</th>
                            <th>التخصص</th>
                            <th>تاريخ التقديم</th>
                            <th>الحالة</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($projects as $project)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td><a href="{{ route('projects.show', $project) }}">{{ Str::limit($project->title_ar, 50) }}</a></td>
                            <td>{{ $project->students->pluck('name')->implode(', ') }}</td>
                            <td>{{ $project->specialization->name_ar ?? '-' }}</td>
                            <td>{{ $project->submission_date ? $project->submission_date->format('Y-m-d') : '-' }}</td>
                            <td><span class="badge bg-warning">{{ $project->status_name }}</span></td>
                            <td>
                                <a href="{{ route('projects.show', $project) }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-check-double"></i> مراجعة
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">لا توجد مشاريع قيد المراجعة حالياً</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-3">
                {{ $projects->links() }}
            </div>
        </div>
    </div>
</div>
@endsection