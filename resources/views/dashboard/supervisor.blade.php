@extends('layouts.app')

@section('title', 'لوحة تحكم المشرف')

@section('content')
<div class="container-fluid px-0">
    <!-- الترحيب -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <h2 class="fw-bold">مرحباً، {{ auth()->user()->name }}</h2>
        <div class="text-muted small">{{ now()->format('Y-m-d') }}</div>
    </div>

    <!-- بطاقات الإحصائيات -->
    <div class="row g-4 mb-5">
        <div class="col-sm-6 col-md-3">
            <div class="card stat-card text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-2 opacity-75">المشاريع المشرف عليها</h6>
                            <h3 class="mb-0 fw-bold">{{ number_format($stats['total_supervised_projects']) }}</h3>
                        </div>
                        <i class="fas fa-project-diagram fa-3x opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-3">
            <a href="{{ route('projects.pending_review') }}" class="text-decoration-none">
                <div class="card stat-card bg-warning text-white h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-2 opacity-75">قيد المراجعة (مشاريع)</h6>
                                <h3 class="mb-0 fw-bold">{{ number_format($stats['pending_review']) }}</h3>
                            </div>
                            <i class="fas fa-clock fa-3x opacity-25"></i>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-sm-6 col-md-3">
            <div class="card stat-card bg-success text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-2 opacity-75">مقبولة</h6>
                            <h3 class="mb-0 fw-bold">{{ number_format($stats['approved_projects']) }}</h3>
                        </div>
                        <i class="fas fa-check-circle fa-3x opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-3">
            <div class="card stat-card bg-info text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-2 opacity-75">مكتملة</h6>
                            <h3 class="mb-0 fw-bold">{{ number_format($stats['completed_projects']) }}</h3>
                        </div>
                        <i class="fas fa-trophy fa-3x opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- جدول المناقشات (بطاقة منفصلة) -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="card bg-dark text-white border-0 shadow-sm overflow-hidden">
                <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div>
                        <h5 class="mb-1 fw-semibold"><i class="fas fa-calendar-alt me-2"></i> جدول المناقشات</h5>
                        <p class="mb-0 text-white-50 small">مواعيد المناقشات المجدولة للمشاريع المقبولة</p>
                    </div>
                    <a href="{{ route('defense.schedule') }}" class="btn btn-light rounded-pill px-4">
                        <i class="fas fa-arrow-left me-1"></i> عرض الجدول
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- الأفكار الجديدة قيد المراجعة -->
    <div class="card border-0 shadow-sm mb-5">
        <div class="card-header bg-transparent border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="mb-0 fw-semibold"><i class="fas fa-lightbulb text-primary me-2"></i>أفكار جديدة قيد المراجعة</h5>
        </div>
        <div class="card-body p-0">
            @if(isset($stats['pending_ideas']) && $stats['pending_ideas']->count())
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>العنوان</th>
                                <th>الطالب</th>
                                <th>التخصص</th>
                                <th>تاريخ التقديم</th>
                                <th class="text-nowrap">الإجراء</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($stats['pending_ideas'] as $idea)
                                <tr>
                                    <td>
                                        <a href="{{ route('projects.idea.show', $idea) }}" class="text-decoration-none fw-medium">
                                            {{ Str::limit($idea->title_ar, 60) }}
                                        </a>
                                    </td>
                                    <td>{{ $idea->student->name }}</td>
                                    <td>{{ $idea->specialization->name_ar ?? '-' }}</td>
                                    <td>{{ $idea->submitted_at->format('Y-m-d') }}</td>
                                    <td>
                                        <a href="{{ route('projects.idea.show', $idea) }}" class="btn btn-sm btn-outline-primary rounded-pill">
                                            <i class="fas fa-eye me-1"></i> مراجعة
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-inbox fa-3x mb-2 d-block"></i>
                    <p>لا توجد أفكار جديدة قيد المراجعة حالياً</p>
                </div>
            @endif
        </div>
    </div>

    <!-- المشاريع التي يشرف عليها -->
    <div class="card border-0 shadow-sm mb-5">
        <div class="card-header bg-transparent border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="mb-0 fw-semibold"><i class="fas fa-list-check text-primary me-2"></i>المشاريع التي أشرف عليها</h5>
            <a href="{{ route('projects.index', ['supervisor_id' => auth()->id()]) }}" class="btn btn-sm btn-outline-primary rounded-pill">
                <i class="fas fa-external-link-alt me-1"></i> عرض جميع المشاريع
            </a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>المشروع</th>
                            <th>الطلاب</th>
                            <th>الحالة</th>
                            <th>نسبة النجاح</th>
                            <th>تاريخ التسليم</th>
                            <th class="text-nowrap">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($stats['recent_projects'] as $project)
                            <tr>
                                <td>
                                    <a href="{{ route('projects.show', $project) }}" class="text-decoration-none fw-medium">
                                        {{ Str::limit($project->title_ar, 50) }}
                                    </a>
                                </td>
                                <td>{{ $project->students->pluck('name')->implode(', ') ?? '-' }}</td>
                                <td>
                                    <span class="badge bg-{{ 
                                        $project->status == 'submitted' ? 'warning' : 
                                        ($project->status == 'approved' ? 'info' : 
                                        ($project->status == 'completed' ? 'success' : 
                                        ($project->status == 'rejected' ? 'danger' : 'secondary'))) 
                                    }}">
                                        {{ $project->status_name }}
                                    </span>
                                </td>
                                <td>{{ $project->success_percentage ?? '-' }}%</td>
                                <td>{{ $project->submission_date ?? '-' }}</td>
                                <td>
                                    @if($project->status == 'submitted')
                                        <a href="{{ route('projects.show', $project) }}" class="btn btn-sm btn-primary rounded-pill">
                                            <i class="fas fa-check-double me-1"></i> مراجعة
                                        </a>
                                    @else
                                        <a href="{{ route('projects.show', $project) }}" class="btn btn-sm btn-outline-secondary rounded-pill">
                                            <i class="fas fa-eye me-1"></i> عرض
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="fas fa-folder-open fa-3x mb-2 d-block"></i>
                                    لا توجد مشاريع بعد
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- التقييمات المعلقة -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-bottom">
                    <h5 class="mb-0 fw-semibold"><i class="fas fa-chart-simple text-primary me-2"></i>التقييمات المعلقة</h5>
                </div>
                <div class="card-body">
                    @if($stats['pending_evaluations'] > 0)
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                            <div>
                                <i class="fas fa-exclamation-triangle text-warning fs-4 me-2"></i>
                                <span class="fw-medium">لديك {{ $stats['pending_evaluations'] }} تقييم غير مكتمل.</span>
                            </div>
                            <a href="{{ route('projects.index', ['status' => 'submitted']) }}" class="btn btn-primary rounded-pill px-4">
                                <i class="fas fa-star me-1"></i> الانتقال للتقييم
                            </a>
                        </div>
                    @else
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-check-circle fa-2x mb-2 d-block text-success"></i>
                            <p>لا توجد تقييمات معلقة</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection