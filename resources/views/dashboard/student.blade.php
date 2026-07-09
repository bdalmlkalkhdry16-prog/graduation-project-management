@extends('layouts.app')

@section('title', 'لوحة تحكم الطالب')

@section('content')
<div class="container-fluid px-0">
    <!-- الترحيب -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <h2 class="fw-bold">مرحباً، {{ auth()->user()->name }}</h2>
        <div class="text-muted small">{{ now()->format('Y-m-d') }}</div>
    </div>

    <!-- بطاقات الإحصائيات -->
    <div class="row g-4 mb-5">
        <div class="col-sm-6 col-md-4">
            <div class="card stat-card text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-2 opacity-75">إجمالي المشاريع</h6>
                            <h3 class="mb-0 fw-bold">{{ number_format($stats['my_projects']) }}</h3>
                        </div>
                        <i class="fas fa-project-diagram fa-3x opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-4">
            <div class="card stat-card bg-warning text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-2 opacity-75">مشاريع نشطة</h6>
                            <h3 class="mb-0 fw-bold">{{ number_format($stats['active_projects']) }}</h3>
                        </div>
                        <i class="fas fa-spinner fa-3x opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-4">
            <div class="card stat-card bg-success text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-2 opacity-75">مشاريع مكتملة</h6>
                            <h3 class="mb-0 fw-bold">{{ number_format($stats['completed_projects']) }}</h3>
                        </div>
                        <i class="fas fa-check-circle fa-3x opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- أفكاري المقدمة -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-transparent border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="mb-0 fw-semibold"><i class="fas fa-lightbulb text-primary me-2"></i>أفكاري المقدمة</h5>
            <a href="{{ route('projects.create_idea') }}" class="btn btn-sm btn-primary">
                <i class="fas fa-plus me-1"></i> تقديم فكرة جديدة
            </a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>عنوان الفكرة</th>
                            <th>تاريخ التقديم</th>
                            <th>الحالة</th>
                            <th>ملاحظات المختص</th>
                            <th class="text-nowrap">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $ideas = auth()->user()->ideas()->latest('submitted_at')->get();
                        @endphp
                        @forelse($ideas as $idea)
                        <tr>
                            <td>
                                <a href="{{ route('projects.idea.show', $idea) }}" class="text-decoration-none fw-medium" title="{{ $idea->title_ar }}">
                                    {{ Str::limit($idea->title_ar, 55) }}
                                </a>
                            </td>
                            <td>{{ $idea->submitted_at->format('Y-m-d') }}</td>
                            <td>
                                @if($idea->status === 'pending')
                                    <span class="badge bg-warning">قيد المراجعة</span>
                                @elseif($idea->status === 'approved')
                                    <span class="badge bg-success">معتمدة</span>
                                @else
                                    <span class="badge bg-danger">مرفوضة</span>
                                @endif
                            </td>
                            <td>{{ $idea->review_notes ?? '-' }}</td>
                            <td>
                                @if($idea->status === 'approved')
                                    <a href="{{ route('projects.show', $idea->project_id ?? 0) }}" class="btn btn-sm btn-outline-success">
                                        <i class="fas fa-eye"></i> المشروع
                                    </a>
                                @elseif($idea->status === 'rejected')
                                    <a href="{{ route('projects.create_idea') }}" class="btn btn-sm btn-outline-warning">
                                        <i class="fas fa-redo-alt"></i> إعادة تقديم
                                    </a>
                                @else
                                    <a href="{{ route('projects.idea.show', $idea) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i> عرض
                                    </a>
                                @endif
                            </td>
                        </tr>
                        @empty
                            <td><td colspan="5" class="text-center py-4 text-muted">لم تقم بتقديم أي فكرة بعد</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- روابط سريعة (تقييماتي + الأرشيف) -->
    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <i class="fas fa-star fa-2x text-warning mb-2 d-block"></i>
                        <h5 class="card-title fw-semibold">تقييمات مشاريعي</h5>
                        <p class="card-text text-muted small">عرض تقييمات مشاريعك النهائية المفصلة</p>
                    </div>
                    <a href="{{ route('evaluations.index') }}" class="btn btn-outline-primary rounded-pill px-4">
                        <i class="fas fa-chart-line me-1"></i> عرض التقييمات
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <i class="fas fa-archive fa-2x text-secondary mb-2 d-block"></i>
                        <h5 class="card-title fw-semibold">أرشيف المشاريع المكتملة</h5>
                        <p class="card-text text-muted small">استعراض المشاريع المنجزة للاطلاع فقط</p>
                    </div>
                    <a href="{{ route('projects.archive') }}" class="btn btn-outline-secondary rounded-pill px-4">
                        <i class="fas fa-folder-open me-1"></i> الأرشيف
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- مشاريعي الحالية -->
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="mb-0 fw-semibold"><i class="fas fa-list-check text-primary me-2"></i>مشاريعي</h5>
                    <a href="{{ route('projects.create') }}" class="btn btn-sm btn-primary">
                        <i class="fas fa-plus me-1"></i> مشروع جديد
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>المشروع</th>
                                    <th>المشرف</th>
                                    <th>الحالة</th>
                                    <th>نسبة النجاح</th>
                                    <th class="text-nowrap">الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                            @forelse($stats['my_projects_list'] as $project)
                                <tr>
                                    <td>
                                        <a href="{{ route('projects.show', $project) }}" class="text-decoration-none fw-medium" title="{{ $project->title_ar }}">
                                            {{ Str::limit($project->title_ar, 50) }}
                                        </a>
                                    </td>
                                    <td>{{ $project->supervisor->name ?? 'غير محدد' }}</td>
                                    <td>
                                        <span class="badge bg-{{ $project->status == 'completed' ? 'success' : ($project->status == 'approved' ? 'info' : 'secondary') }}">
                                            {{ $project->status_name }}
                                        </span>
                                    </td>
                                    <td>{{ $project->success_percentage ?? '-' }}%</td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="{{ route('projects.show', $project) }}" class="btn btn-outline-primary" title="عرض التفاصيل">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if($project->status == 'draft')
                                                <form action="{{ route('projects.submit', $project) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-outline-success" onclick="return confirm('هل أنت متأكد من تقديم المشروع للمراجعة؟')" title="تقديم للمراجعة">
                                                        <i class="fas fa-paper-plane"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center py-4 text-muted">لا توجد مشاريع حالياً</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-bottom">
                    <h5 class="mb-0 fw-semibold"><i class="fas fa-handshake text-primary me-2"></i>مشاريع متاحة للانضمام</h5>
                </div>
                <div class="card-body">
                    @if($stats['available_projects']->count())
                        <ul class="list-group list-group-flush">
                            @foreach($stats['available_projects'] as $project)
                                <li class="list-group-item d-flex justify-content-between align-items-center ps-0">
                                    <span class="text-truncate" style="max-width: 70%;" title="{{ $project->title_ar }}">{{ Str::limit($project->title_ar, 30) }}</span>
                                    <a href="{{ route('projects.show', $project) }}" class="btn btn-sm btn-outline-primary rounded-pill">عرض</a>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-ban fa-2x mb-2 d-block"></i>
                            <p>لا توجد مشاريع متاحة حالياً</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection