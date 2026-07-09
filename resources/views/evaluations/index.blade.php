@extends('layouts.app')

@section('title', 'سجل التقييمات')

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-2">
        <div>
            <h2 class="fw-bold mb-0"><i class="fas fa-star text-warning me-2"></i>سجل التقييمات</h2>
            <p class="text-muted small mb-0">عرض جميع تقييمات المشاريع المنجزة</p>
        </div>
        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary btn-sm shadow-sm">
            <i class="fas fa-arrow-right me-1"></i> العودة للوحة التحكم
        </a>
    </div>

    <!-- إحصائيات سريعة -->
    @php
        $totalEvals = $evaluations->total();
        $avgPercentage = $evaluations->avg('total_percentage');
        $highEvals = $evaluations->where('total_percentage', '>=', 70)->count();
        $lowEvals = $evaluations->where('total_percentage', '<', 50)->count();
    @endphp
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 bg-primary bg-opacity-10 shadow-sm">
                <div class="card-body py-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted">إجمالي التقييمات</small>
                            <h5 class="mb-0">{{ $totalEvals }}</h5>
                        </div>
                        <i class="fas fa-list-check fa-2x text-primary opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 bg-success bg-opacity-10 shadow-sm">
                <div class="card-body py-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted">متوسط النسبة</small>
                            <h5 class="mb-0">{{ number_format($avgPercentage ?? 0, 1) }}%</h5>
                        </div>
                        <i class="fas fa-chart-line fa-2x text-success opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 bg-info bg-opacity-10 shadow-sm">
                <div class="card-body py-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted">متميز (≥70%)</small>
                            <h5 class="mb-0">{{ $highEvals }}</h5>
                        </div>
                        <i class="fas fa-medal fa-2x text-info opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 bg-danger bg-opacity-10 shadow-sm">
                <div class="card-body py-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted">ضعيف (<50%)</small>
                            <h5 class="mb-0">{{ $lowEvals }}</h5>
                        </div>
                        <i class="fas fa-exclamation-triangle fa-2x text-danger opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- فلترة وبحث (اختياري) -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('evaluations.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">المشروع</label>
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="بحث في عنوان المشروع..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">المقيم</label>
                    <select name="supervisor_id" class="form-select form-select-sm">
                        <option value="">الكل</option>
                        @foreach($supervisors ?? [] as $supervisor)
                            <option value="{{ $supervisor->id }}" {{ request('supervisor_id') == $supervisor->id ? 'selected' : '' }}>{{ $supervisor->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">الحد الأدنى</label>
                    <input type="number" name="min_percentage" class="form-control form-control-sm" placeholder="مثلاً 60" value="{{ request('min_percentage') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">الحد الأقصى</label>
                    <input type="number" name="max_percentage" class="form-control form-control-sm" placeholder="مثلاً 90" value="{{ request('max_percentage') }}">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary btn-sm w-100"><i class="fas fa-search me-1"></i> بحث</button>
                </div>
            </form>
        </div>
    </div>

    <!-- جدول التقييمات المحسن -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">#</th>
                            <th width="35%">المشروع</th>
                            <th width="20%">المقيم</th>
                            <th width="15%">نسبة النجاح</th>
                            <th width="15%">تاريخ التقييم</th>
                            <th width="10%">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($evaluations as $evaluation)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                <a href="{{ route('projects.show', $evaluation->project) }}" class="text-decoration-none fw-semibold">
                                    {{ Str::limit($evaluation->project->title_ar, 50) }}
                                </a>
                                @if($evaluation->project->status == 'completed')
                                    <span class="badge bg-success bg-opacity-10 text-success ms-1"><i class="fas fa-check-circle"></i> مكتمل</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <i class="fas fa-user-tie text-secondary small"></i>
                                    <span>{{ $evaluation->supervisor->name }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex flex-column gap-1" style="min-width: 100px;">
                                    <div class="d-flex justify-content-between small">
                                        <span class="text-muted">النسبة</span>
                                        <span class="fw-bold {{ $evaluation->total_percentage >= 60 ? 'text-success' : 'text-danger' }}">
                                            {{ $evaluation->total_percentage }}%
                                        </span>
                                    </div>
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar {{ $evaluation->total_percentage >= 70 ? 'bg-success' : ($evaluation->total_percentage >= 50 ? 'bg-warning' : 'bg-danger') }}"
                                             role="progressbar"
                                             style="width: {{ $evaluation->total_percentage }}%"
                                             aria-valuenow="{{ $evaluation->total_percentage }}"
                                             aria-valuemin="0"
                                             aria-valuemax="100">
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <i class="far fa-calendar-alt text-muted me-1"></i>
                                {{ $evaluation->evaluated_at ? $evaluation->evaluated_at->format('Y-m-d') : $evaluation->created_at->format('Y-m-d') }}
                            </td>
                            <td>
                                <a href="{{ route('evaluations.show', $evaluation) }}" class="btn btn-sm btn-outline-primary" data-bs-toggle="tooltip" title="عرض التفاصيل">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if(auth()->user()->isAdmin() || auth()->user()->id == $evaluation->supervisor_id)
                                    <a href="{{ route('evaluations.edit', $evaluation) }}" class="btn btn-sm btn-outline-secondary" data-bs-toggle="tooltip" title="تعديل">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                @endif
                            </td>
                        </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">لا توجد تقييمات بعد</h5>
                                    <p class="text-muted small">سيتم عرض تقييمات المشاريع هنا عند إتمامها</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center p-3 border-top gap-2">
                <div class="text-muted small">
                    عرض {{ $evaluations->firstItem() ?? 0 }} - {{ $evaluations->lastItem() ?? 0 }} من إجمالي {{ $evaluations->total() }} تقييم
                </div>
                <div>
                    {{ $evaluations->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // تفعيل التلميحات (Tooltips)
    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>
@endpush