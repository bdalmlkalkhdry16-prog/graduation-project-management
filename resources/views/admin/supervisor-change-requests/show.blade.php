@extends('layouts.app')

@section('title', 'تفاصيل طلب تغيير المشرف')

@push('styles')
<style>
    .detail-card {
        border: none;
        border-radius: 1.25rem;
        box-shadow: 0 2px 12px rgba(0,0,0,0.04);
        overflow: hidden;
    }
    .detail-card .card-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        padding: 1.25rem 1.5rem;
    }
    .detail-card .card-header h4 {
        font-weight: 700;
        margin: 0;
    }
    .detail-card .card-header h4 i {
        margin-left: 0.5rem;
    }
    .detail-item {
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        padding: 0.6rem 0;
        border-bottom: 1px solid #f1f5f9;
    }
    .detail-item:last-child {
        border-bottom: none;
    }
    .detail-item .detail-icon {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f1f5f9;
        color: #667eea;
        flex-shrink: 0;
    }
    .detail-item .detail-label {
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 0.1rem;
    }
    .detail-item .detail-value {
        color: #334155;
    }
    .status-badge-custom {
        font-size: 0.9rem;
        padding: 0.4rem 1rem;
        border-radius: 2rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
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
    .feedback-box {
        background: #f8fafc;
        border-radius: 0.75rem;
        padding: 1rem 1.25rem;
        border-right: 4px solid #667eea;
        margin-top: 0.5rem;
    }
    .feedback-box .feedback-label {
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 0.3rem;
    }
    .feedback-box .feedback-text {
        color: #334155;
        margin: 0;
    }
    .admin-actions .form-control {
        border-radius: 0.75rem;
        border: 1px solid #e2e8f0;
        transition: border-color 0.2s, box-shadow 0.2s;
        padding: 0.5rem 1rem;
    }
    .admin-actions .form-control:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.15);
    }
    .btn-approve {
        background: linear-gradient(135deg, #198754, #157347);
        border: none;
        border-radius: 0.75rem;
        padding: 0.6rem;
        font-weight: 600;
        transition: transform 0.2s, box-shadow 0.2s;
        color: white;
    }
    .btn-approve:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(25, 135, 84, 0.3);
        color: white;
    }
    .btn-reject {
        background: linear-gradient(135deg, #dc3545, #b02a37);
        border: none;
        border-radius: 0.75rem;
        padding: 0.6rem;
        font-weight: 600;
        transition: transform 0.2s, box-shadow 0.2s;
        color: white;
    }
    .btn-reject:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);
        color: white;
    }
    .back-btn {
        border-radius: 0.75rem;
        padding: 0.4rem 1rem;
        font-weight: 500;
        transition: all 0.2s;
    }
    .back-btn:hover {
        transform: translateY(-1px);
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
    .review-info {
        background: #f0f4ff;
        border-radius: 0.75rem;
        padding: 0.75rem 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: #4a5568;
        font-size: 0.9rem;
    }
    .review-info i {
        color: #667eea;
    }
</style>
@endpush

@section('content')
<div class="container">
    <div class="card detail-card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h4><i class="fas fa-user-edit"></i> تفاصيل طلب تغيير المشرف</h4>
            <div class="d-flex gap-2">
                @php
                    $activeProjects = auth()->user()->projects()->where('status', '!=', 'completed')->get();
                    $canRequest = $activeProjects->count() > 0;
                    $firstActiveProject = $activeProjects->first();
                @endphp
                @if($canRequest && $firstActiveProject)
                    <a href="{{ route('supervisor-requests.create', ['project' => $firstActiveProject->id]) }}" class="btn btn-request btn-sm shadow-sm">
                        <i class="fas fa-plus-circle me-1"></i> طلب جديد
                    </a>
                @endif
                <a href="{{ route('supervisor-requests.index') }}" class="btn btn-light btn-sm back-btn text-dark shadow-sm">
                    <i class="fas fa-arrow-right me-1"></i> عودة للقائمة
                </a>
            </div>
        </div>
        <div class="card-body">
            <!-- المعلومات الأساسية -->
            <div class="row g-0">
                <div class="col-md-6">
                    <div class="detail-item">
                        <span class="detail-icon"><i class="fas fa-project-diagram"></i></span>
                        <div>
                            <div class="detail-label">المشروع</div>
                            <div class="detail-value">
                                <a href="{{ route('projects.show', $supervisorChangeRequest->project) }}" class="text-decoration-none fw-semibold text-primary">
                                    {{ $supervisorChangeRequest->project->title_ar }}
                                </a>
                                @if($supervisorChangeRequest->project->status == 'completed')
                                    <span class="badge bg-success bg-opacity-10 text-success ms-1"><i class="fas fa-check-circle"></i> مكتمل</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="detail-item">
                        <span class="detail-icon"><i class="fas fa-user-graduate"></i></span>
                        <div>
                            <div class="detail-label">الطالب</div>
                            <div class="detail-value">{{ $supervisorChangeRequest->student->name }}</div>
                        </div>
                    </div>
                    <div class="detail-item">
                        <span class="detail-icon"><i class="fas fa-user-tie"></i></span>
                        <div>
                            <div class="detail-label">المشرف الحالي</div>
                            <div class="detail-value">
                                <span class="badge bg-secondary bg-opacity-10 text-secondary">
                                    <i class="fas fa-user me-1"></i> {{ $supervisorChangeRequest->currentSupervisor->name }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="detail-item">
                        <span class="detail-icon"><i class="fas fa-user-check"></i></span>
                        <div>
                            <div class="detail-label">المشرف المطلوب</div>
                            <div class="detail-value">
                                <span class="badge bg-primary bg-opacity-10 text-primary">
                                    <i class="fas fa-user-plus me-1"></i> {{ $supervisorChangeRequest->proposedSupervisor->name }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="detail-item">
                        <span class="detail-icon"><i class="fas fa-clock"></i></span>
                        <div>
                            <div class="detail-label">تاريخ التقديم</div>
                            <div class="detail-value">
                                {{ $supervisorChangeRequest->created_at->format('Y-m-d') }}
                                <br>
                                <small class="text-muted">{{ $supervisorChangeRequest->created_at->diffForHumans() }}</small>
                            </div>
                        </div>
                    </div>
                    <div class="detail-item">
                        <span class="detail-icon"><i class="fas fa-tag"></i></span>
                        <div>
                            <div class="detail-label">الحالة</div>
                            <div class="detail-value">
                                @if($supervisorChangeRequest->status == 'pending')
                                    <span class="status-badge-custom pending"><i class="fas fa-hourglass-half"></i> قيد الانتظار</span>
                                @elseif($supervisorChangeRequest->status == 'approved')
                                    <span class="status-badge-custom approved"><i class="fas fa-check-circle"></i> مقبول</span>
                                @else
                                    <span class="status-badge-custom rejected"><i class="fas fa-times-circle"></i> مرفوض</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- سبب الطلب -->
            <div class="detail-item" style="border-bottom: none; padding-top: 0.8rem;">
                <span class="detail-icon"><i class="fas fa-question-circle"></i></span>
                <div style="flex: 1;">
                    <div class="detail-label">سبب الطلب</div>
                    <div class="detail-value" style="background: #f8fafc; padding: 0.75rem 1rem; border-radius: 0.75rem; margin-top: 0.2rem;">
                        {{ $supervisorChangeRequest->reason }}
                    </div>
                </div>
            </div>

            <!-- معلومات المراجعة (إن وجدت) -->
            @if($supervisorChangeRequest->reviewed_at)
                <div class="review-info mt-3">
                    <i class="fas fa-check-circle"></i>
                    <span>
                        تمت المراجعة بواسطة 
                        <strong>{{ optional($supervisorChangeRequest->reviewer)->name ?? 'الإدارة' }}</strong>
                        في تاريخ {{ $supervisorChangeRequest->reviewed_at->format('Y-m-d H:i') }}
                    </span>
                </div>
            @endif

            <!-- ملاحظات الإدارة (إن وجدت) -->
            @if($supervisorChangeRequest->admin_feedback)
                <div class="feedback-box mt-3">
                    <div class="feedback-label"><i class="fas fa-comment-dots me-2"></i>ملاحظات الإدارة</div>
                    <p class="feedback-text">{{ $supervisorChangeRequest->admin_feedback }}</p>
                </div>
            @endif

            <!-- إجراءات المدير -->
            @if(auth()->user()->isAdmin() && $supervisorChangeRequest->status == 'pending')
                <div class="admin-actions mt-4 pt-3 border-top">
                    <h6 class="fw-bold mb-3"><i class="fas fa-gavel me-2"></i>اتخاذ القرار</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <form action="{{ route('supervisor-requests.approve', $supervisorChangeRequest) }}" method="POST">
                                @csrf
                                <button class="btn btn-success w-100 btn-approve" onclick="return confirm('هل أنت متأكد من قبول هذا الطلب؟')">
                                    <i class="fas fa-check-circle me-2"></i> قبول الطلب
                                </button>
                            </form>
                        </div>
                        <div class="col-md-6">
                            <form action="{{ route('supervisor-requests.reject', $supervisorChangeRequest) }}" method="POST">
                                @csrf
                                <div class="mb-2">
                                    <label class="form-label small fw-semibold">سبب الرفض <span class="text-danger">*</span></label>
                                    <textarea name="admin_feedback" class="form-control" rows="2" placeholder="اذكر سبب رفض الطلب..." required></textarea>
                                </div>
                                <button class="btn btn-danger w-100 btn-reject" onclick="return confirm('هل أنت متأكد من رفض هذا الطلب؟')">
                                    <i class="fas fa-times-circle me-2"></i> رفض الطلب
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endif

            <!-- روابط إضافية -->
            <div class="mt-4 text-center">
                <a href="{{ route('projects.show', $supervisorChangeRequest->project) }}" class="btn btn-outline-primary px-4">
                    <i class="fas fa-eye me-2"></i> عرض المشروع
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // تفعيل التلميحات (Tooltips)
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>
@endpush