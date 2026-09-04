@extends('layouts.app')

@section('title', 'ملفات الطلاب')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>ملفات الطلاب</h2>
        <a href="{{ route('staff.student-profiles.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i> إنشاء ملف طالب جديد
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>الاسم</th>
                            <th>رقم القيد</th>
                            <th>التخصص</th>
                            <th>الحالة</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse ($profiles as $profile)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $profile->user->name }}</td>
                            <td>{{ $profile->number_student }}</td>
                            <td>{{ $profile->specialization->name ?? '-' }}</td>
                            <td>{{ $profile->academic_status }}</td>
                            <td>
                                <a href="{{ route('staff.student-profiles.show', $profile) }}" class="btn btn-sm btn-outline-primary">عرض</a>
                                <a href="{{ route('staff.student-profiles.edit', $profile) }}" class="btn btn-sm btn-outline-secondary">تعديل</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">لا توجد ملفات طلاب بعد.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-3">{{ $profiles->links() }}</div>
</div>
@endsection