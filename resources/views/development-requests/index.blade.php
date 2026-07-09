@extends('layouts.app')

@section('title', 'طلبات تطوير المشاريع')

@push('styles')
<style>
    .stat-card-mini {
        border: none;
        border-radius: 0.75rem;
        background: white;
        box-shadow: 0 1px 4px rgba(0,0,0,0.04);
        transition: transform 0.2s;
    }
    .stat-card-mini:hover {
        transform: translateY(-2px);
    }
    .stat-card-mini .card-body {
        padding: 0.75rem 1rem;
    }
    .stat-number {
        font-size: 1.5rem;
        font-weight: 700;
        line-height: 1.2;
    }
    .request-status-badge {
        font-size: 0.8rem;
        padding: 0.35rem 0.8rem;
        border-radius: 2rem;
    }
    .request-status-badge.pending { background: #fff3cd; color: #664d03; }
    .request-status-badge.approved { background: #d1e7dd; color: #0a3622; }
    .request-status-badge.rejected { background: #f8d7da; color: #842029; }
    .table-requests td {
        vertical-align: middle;
    }
    .action-buttons .btn {
        padding: 0.2rem 0.5rem;
        font-size: 0.8rem;
    }
    .empty-state-icon {
        font-size: 3rem;
        color: #dee2e6;
        margin-bottom: 0.5rem;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-2">
        <div>
            <h2 class="fw-bold mb-0"><i class="fas fa-code-branch text-primary me-2"></i>طلبات تطوير المشاريع</h2>
            <p class="text-muted small mb-0">إدارة طلبات تطوير المشاريع المكتملة</p>
        </div>

        @php
            $completedProjects = auth()->user()->projects()->where('status', 'completed')->get();
        @endphp

        @if($completedProjects->count() > 0)
            <a href="{{ route('development-requests.create', ['project' => $completedProjects->first()->id]) }}" class="btn btn-primary shadow-sm">
                <i class="fas fa-plus-circle me-2"></i> طلب تطوير جديد
            </a>
        @else
            <button class="btn btn-secondary" disabled>
                <i class="fas fa-plus-circle me-2"></i> طلب تطوير (لا يوجد مشاريع مكتملة)
            </button>
        @endif
    </div>

    <!-- إحصائيات سريعة -->
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-sm-6">
            <div class="card stat-card-mini">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small">إجمالي الطلبات</span>
                            <div class="stat-number">{{ $requests->total() }}</div>
                        </div>
                        <i class="fas fa-list-check fa-2x text-primary opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card stat-card-mini">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small">قيد الانتظار</span>
                            <div class="stat-number text-warning">{{ $requests->where('status', 'pending')->count() }}</div>
                        </div>
                        <i class="fas fa-clock fa-2x text-warning opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card stat-card-mini">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small">مقبولة</span>
                            <div class="stat-number text-success">{{ $requests->where('status', 'approved')->count() }}</div>
                        </div>
                        <i class="fas fa-check-circle fa-2x text-success opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card stat-card-mini">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small">مرفوضة</span>
                            <div class="stat-number text-danger">{{ $requests->where('status', 'rejected')->count() }}</div>
                        </div>
                        <i class="fas fa-times-circle fa-2x text-danger opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- الجدول -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-requests align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 25%">المشروع</th>
                            <th style="width: 15%">مقدم الطلب</th>
                            <th style="width: 25%">سبب الطلب</th>
                            <th style="width: 12%">الحالة</th>
                            <th style="width: 13%">تاريخ التقديم</th>
                            <th style="width: 10%">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($requests as $req)
                        <tr>
                            <td>
                                <a href="{{ route('projects.show', $req->project) }}" class="text-decoration-none fw-semibold">
                                    {{ Str::limit($req->project->title_ar, 40) }}
                                </a>
                                @if($req->project->status === 'completed')
                                    <span class="badge bg-success bg-opacity-10 text-success ms-1"><i class="fas fa-check-circle"></i> مكتمل</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <i class="fas fa-user text-secondary small"></i>
                                    <span>{{ $req->student->name }}</span>
                                </div>
                            </td>
                            <td>
                                <span class="text-muted small">{{ Str::limit($req->reason, 60) }}</span>
                            </td>
                            <td>
                                @if($req->status == 'pending')
                                    <span class="request-status-badge pending"><i class="fas fa-hourglass-half me-1"></i>قيد الانتظار</span>
                                @elseif($req->status == 'approved')
                                    <span class="request-status-badge approved"><i class="fas fa-check-circle me-1"></i>مقبول</span>
                                @else
                                    <span class="request-status-badge rejected"><i class="fas fa-times-circle me-1"></i>مرفوض</span>
                                @endif
                            </td>
                            <td>
                                <i class="far fa-calendar-alt text-muted me-1"></i>
                                {{ $req->created_at->format('Y-m-d') }}
                                <br>
                                <small class="text-muted">{{ $req->created_at->diffForHumans() }}</small>
                            </td>
                            <td>
                                <div class="d-flex gap-1 action-buttons">
                                    <a href="{{ route('development-requests.show', $req) }}" class="btn btn-outline-primary" data-bs-toggle="tooltip" title="عرض التفاصيل">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if($req->status === 'pending' && (auth()->id() === $req->student_id || auth()->user()->isAdmin()))
                                        <a href="{{ route('development-requests.edit', $req) }}" class="btn btn-outline-warning" data-bs-toggle="tooltip" title="تعديل">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    @endif
                                    @if(auth()->user()->isAdmin() && $req->status === 'pending')
                                        <form action="{{ route('development-requests.approve', $req) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-success" data-bs-toggle="tooltip" title="قبول" onclick="return confirm('هل تريد قبول هذا الطلب؟')">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('development-requests.reject', $req) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-danger" data-bs-toggle="tooltip" title="رفض" onclick="return confirm('هل تريد رفض هذا الطلب؟')">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <i class="fas fa-inbox empty-state-icon"></i>
                                    <h5 class="text-muted">لا توجد طلبات تطوير</h5>
                                    <p class="text-muted small">يمكنك تقديم طلب تطوير عند إكمال مشروعك</p>
                                    @if($completedProjects->count() > 0)
                                        <a href="{{ route('development-requests.create', ['project' => $completedProjects->first()->id]) }}" class="btn btn-primary btn-sm mt-2">
                                            <i class="fas fa-plus-circle me-1"></i> تقديم طلب
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($requests->hasPages())
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-center p-3 border-top gap-2">
                    <div class="text-muted small">
                        عرض {{ $requests->firstItem() ?? 0 }} - {{ $requests->lastItem() ?? 0 }} من إجمالي {{ $requests->total() }} طلب
                    </div>
                    <div>
                        {{ $requests->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // تفعيل التلميحات
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>
@endpush