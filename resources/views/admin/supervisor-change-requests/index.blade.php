@extends('layouts.app')

@section('title', 'طلبات تغيير المشرف')

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
    .status-badge-custom {
        font-size: 0.8rem;
        padding: 0.35rem 0.8rem;
        border-radius: 2rem;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
    }
    .status-badge-custom.pending {
        background: #fff3cd;
        color: #664d03;
    }
    .status-badge-custom.approved {
        background: #d1e7dd;
        color: #0a3622;
    }
    .status-badge-custom.rejected {
        background: #f8d7da;
        color: #842029;
    }
    .table-requests td {
        vertical-align: middle;
    }
    .empty-state-icon {
        font-size: 3rem;
        color: #dee2e6;
        margin-bottom: 0.5rem;
    }
    .btn-request {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        border-radius: 0.75rem;
        padding: 0.4rem 1.2rem;
        font-weight: 500;
        transition: transform 0.2s, box-shadow 0.2s;
        color: white;
    }
    .btn-request:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        color: white;
    }
    .filter-card .form-control,
    .filter-card .form-select {
        border-radius: 0.75rem;
        border: 1px solid #e2e8f0;
        transition: border-color 0.2s, box-shadow 0.2s;
        padding: 0.4rem 0.8rem;
        font-size: 0.9rem;
    }
    .filter-card .form-control:focus,
    .filter-card .form-select:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.12);
    }
    .filter-card .btn-filter {
        border-radius: 0.75rem;
        padding: 0.4rem 1.2rem;
        font-weight: 500;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- رأس الصفحة -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-2">
        <div>
            <h2 class="fw-bold mb-0"><i class="fas fa-user-edit text-primary me-2"></i>طلبات تغيير المشرف</h2>
            <p class="text-muted small mb-0">إدارة طلبات تغيير المشرفين على المشاريع</p>
        </div>
        <div>
            @php
                $activeProjects = auth()->user()->projects()->where('status', '!=', 'completed')->get();
                $canRequest = $activeProjects->count() > 0;
                $firstActiveProject = $activeProjects->first();
            @endphp
            @if($canRequest && $firstActiveProject)
                <a href="{{ route('supervisor-requests.create', ['project' => $firstActiveProject->id]) }}" class="btn btn-request shadow-sm">
                    <i class="fas fa-plus-circle me-2"></i> طلب تغيير مشرف جديد
                </a>
            @else
                <button class="btn btn-secondary" disabled>
                    <i class="fas fa-plus-circle me-2"></i> طلب تغيير مشرف (لا يوجد مشاريع نشطة)
                </button>
            @endif
        </div>
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

    <!-- فلترة وبحث -->
    <div class="card filter-card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('supervisor-requests.index') }}" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">الحالة</label>
                    <select name="status" class="form-select">
                        <option value="">جميع الحالات</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>قيد الانتظار</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>مقبول</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>مرفوض</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">المشروع</label>
                    <input type="text" name="search" class="form-control" placeholder="بحث في عنوان المشروع..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">المشرف المطلوب</label>
                    <select name="proposed_supervisor_id" class="form-select">
                        <option value="">الكل</option>
                        @foreach($supervisors ?? [] as $supervisor)
                            <option value="{{ $supervisor->id }}" {{ request('proposed_supervisor_id') == $supervisor->id ? 'selected' : '' }}>
                                {{ $supervisor->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-filter w-100">
                            <i class="fas fa-search me-1"></i> بحث
                        </button>
                        <a href="{{ route('supervisor-requests.index') }}" class="btn btn-outline-secondary btn-filter">
                            <i class="fas fa-redo-alt"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- الجدول -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-requests align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 18%">المشروع</th>
                            <th style="width: 12%">الطالب</th>
                            <th style="width: 12%">المشرف الحالي</th>
                            <th style="width: 12%">المشرف المطلوب</th>
                            <th style="width: 16%">السبب</th>
                            <th style="width: 10%">الحالة</th>
                            <th style="width: 12%">تاريخ الطلب</th>
                            <th style="width: 8%">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($requests as $req)
                        <tr>
                            <td>
                                <a href="{{ route('projects.show', $req->project) }}" class="text-decoration-none fw-semibold">
                                    {{ Str::limit($req->project->title_ar, 30) }}
                                </a>
                            </td>
                            <td>{{ $req->student->name }}</td>
                            <td>{{ $req->currentSupervisor->name }}</td>
                            <td>{{ $req->proposedSupervisor->name }}</td>
                            <td>
                                <span class="text-muted small" data-bs-toggle="tooltip" title="{{ $req->reason }}">
                                    {{ Str::limit($req->reason, 40) }}
                                </span>
                            </td>
                            <td>
                                @if($req->status == 'pending')
                                    <span class="status-badge-custom pending"><i class="fas fa-hourglass-half"></i>قيد الانتظار</span>
                                @elseif($req->status == 'approved')
                                    <span class="status-badge-custom approved"><i class="fas fa-check-circle"></i>مقبول</span>
                                @else
                                    <span class="status-badge-custom rejected"><i class="fas fa-times-circle"></i>مرفوض</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span><i class="far fa-calendar-alt text-muted me-1"></i> {{ $req->created_at->format('Y-m-d') }}</span>
                                    <small class="text-muted">{{ $req->created_at->diffForHumans() }}</small>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="{{ route('supervisor-requests.show', $req) }}" class="btn btn-sm btn-outline-primary" data-bs-toggle="tooltip" title="عرض التفاصيل">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if($req->status == 'pending' && auth()->user()->isAdmin())
                                        <form action="{{ route('supervisor-requests.approve', $req) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-success" data-bs-toggle="tooltip" title="قبول" onclick="return confirm('هل تريد قبول هذا الطلب؟')">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>
                                        <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $req->id }}" title="رفض">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>

                        <!-- مودال رفض الطلب (للمدير) -->
                        @if($req->status == 'pending' && auth()->user()->isAdmin())
                        <div class="modal fade" id="rejectModal{{ $req->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <form action="{{ route('supervisor-requests.reject', $req) }}" method="POST">
                                        @csrf
                                        <div class="modal-header bg-danger text-white">
                                            <h5 class="modal-title"><i class="fas fa-times-circle me-2"></i>رفض الطلب</h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">سبب الرفض <span class="text-danger">*</span></label>
                                                <textarea name="admin_feedback" class="form-control" rows="3" placeholder="اذكر سبب رفض الطلب..." required></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                                            <button type="submit" class="btn btn-danger">رفض الطلب</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endif

                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <i class="fas fa-inbox empty-state-icon"></i>
                                    <h5 class="text-muted">لا توجد طلبات تغيير مشرف</h5>
                                    <p class="text-muted small">يمكنك تقديم طلب لتغيير المشرف على أحد مشاريعك النشطة</p>
                                    @if($canRequest && $firstActiveProject)
                                        <a href="{{ route('supervisor-requests.create', ['project' => $firstActiveProject->id]) }}" class="btn btn-primary btn-sm mt-2">
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
                        {{ $requests->appends(request()->query())->links() }}
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