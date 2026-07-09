@extends('layouts.app')

@section('title', 'جدول المناقشات')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>جدول المناقشات</h2>
        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-right me-1"></i> العودة للوحة التحكم
        </a>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>المشروع</th>
                            <th>الطلاب</th>
                            <th>المشرف</th>
                            <th>التاريخ والوقت</th>
                            <th>المكان</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($projects as $project)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                <a href="{{ route('projects.show', $project) }}">
                                    {{ Str::limit($project->title_ar, 50) }}
                                </a>
                            </td>
                            <td>{{ $project->students->pluck('name')->implode(', ') }}</td>
                            <td>{{ $project->supervisor->name ?? '-' }}</td>
                            <td>{{ $project->defense_date ? \Carbon\Carbon::parse($project->defense_date)->format('Y-m-d H:i') : '-' }}</td>
                            <td>{{ $project->defense_location ?? '-' }}</td>
                            <td>
                                <a href="{{ route('projects.show', $project) }}" class="btn btn-sm btn-outline-primary">عرض</a>
                                @if(auth()->user()->isAdmin() || (auth()->user()->isSupervisor() && $project->supervisor_id == auth()->id()))
                                    <a href="{{ route('projects.set_defense', $project) }}" class="btn btn-sm btn-outline-secondary">تعديل الموعد</a>
                                @endif
                            </td>
                        </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">لا توجد مناقشات مجدولة حالياً.导航
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