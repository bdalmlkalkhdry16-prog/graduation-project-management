@extends('layouts.app')

@section('title', 'تفاصيل التقييم')

@push('styles')
<style>
    .score-circle {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.8rem;
        font-weight: 700;
        margin: 0 auto 0.5rem;
        position: relative;
    }
    .score-circle.creativity { background: rgba(13, 110, 253, 0.15); color: #0d6efd; }
    .score-circle.implementation { background: rgba(25, 135, 84, 0.15); color: #198754; }
    .score-circle.documentation { background: rgba(255, 193, 7, 0.15); color: #d39e00; }
    .score-circle.presentation { background: rgba(220, 53, 69, 0.15); color: #dc3545; }
    .score-circle.total { background: rgba(13, 202, 240, 0.15); color: #0dcaf0; width: 100px; height: 100px; font-size: 2.2rem; }

    .score-card {
        transition: transform 0.2s;
        border: none;
        border-radius: 1rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    }
    .score-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.08);
    }
    .score-card .card-body {
        padding: 1.25rem 0.75rem;
    }
    .progress-thin {
        height: 6px;
        border-radius: 3px;
        background: #e9ecef;
    }
    .badge-total {
        font-size: 1.5rem;
        padding: 0.5rem 1.5rem;
        border-radius: 2rem;
    }
    .detail-icon {
        width: 32px;
        text-align: center;
        color: #6c757d;
    }
</style>
@endpush

@section('content')
<div class="container">
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
            <h4 class="mb-0 fw-bold">
                <i class="fas fa-clipboard-list text-primary me-2"></i>تفاصيل تقييم المشروع
            </h4>
            <div>
                <a href="{{ route('evaluations.index') }}" class="btn btn-outline-secondary btn-sm shadow-sm">
                    <i class="fas fa-arrow-right me-1"></i> عودة للقائمة
                </a>
                @if(auth()->user()->isAdmin() || auth()->user()->id == $evaluation->supervisor_id)
                    <a href="{{ route('evaluations.edit', $evaluation) }}" class="btn btn-outline-primary btn-sm shadow-sm">
                        <i class="fas fa-edit me-1"></i> تعديل
                    </a>
                @endif
            </div>
        </div>
        <div class="card-body">
            <!-- معلومات أساسية -->
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fas fa-project-diagram text-primary detail-icon"></i>
                        <div>
                            <small class="text-muted d-block">المشروع</small>
                            <span class="fw-semibold">{{ $evaluation->project->title_ar }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fas fa-user-tie text-success detail-icon"></i>
                        <div>
                            <small class="text-muted d-block">المقيم</small>
                            <span class="fw-semibold">{{ $evaluation->supervisor->name }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fas fa-calendar-check text-warning detail-icon"></i>
                        <div>
                            <small class="text-muted d-block">تاريخ التقييم</small>
                            <span class="fw-semibold">{{ $evaluation->evaluated_at ? $evaluation->evaluated_at->format('Y-m-d H:i') : $evaluation->created_at->format('Y-m-d H:i') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- بطاقات المعايير -->
            <div class="row g-3 mb-4">
                <div class="col-md-3 col-sm-6">
                    <div class="card score-card text-center h-100">
                        <div class="card-body">
                            <div class="score-circle creativity">{{ $evaluation->creativity_score }}%</div>
                            <h6 class="fw-bold mb-2">الإبداع</h6>
                            <div class="progress progress-thin">
                                <div class="progress-bar bg-primary" style="width: {{ $evaluation->creativity_score }}%"></div>
                            </div>
                            <small class="text-muted">الوزن 40%</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="card score-card text-center h-100">
                        <div class="card-body">
                            <div class="score-circle implementation">{{ $evaluation->implementation_score }}%</div>
                            <h6 class="fw-bold mb-2">التنفيذ</h6>
                            <div class="progress progress-thin">
                                <div class="progress-bar bg-success" style="width: {{ $evaluation->implementation_score }}%"></div>
                            </div>
                            <small class="text-muted">الوزن 30%</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="card score-card text-center h-100">
                        <div class="card-body">
                            <div class="score-circle documentation">{{ $evaluation->documentation_score }}%</div>
                            <h6 class="fw-bold mb-2">التوثيق</h6>
                            <div class="progress progress-thin">
                                <div class="progress-bar bg-warning" style="width: {{ $evaluation->documentation_score }}%"></div>
                            </div>
                            <small class="text-muted">الوزن 20%</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="card score-card text-center h-100">
                        <div class="card-body">
                            <div class="score-circle presentation">{{ $evaluation->presentation_score }}%</div>
                            <h6 class="fw-bold mb-2">العرض</h6>
                            <div class="progress progress-thin">
                                <div class="progress-bar bg-danger" style="width: {{ $evaluation->presentation_score }}%"></div>
                            </div>
                            <small class="text-muted">الوزن 10%</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- النسبة الإجمالية -->
            <div class="row mb-4">
                <div class="col-12 text-center">
                    <div class="card bg-light border-0 py-3">
                        <div class="card-body">
                            <h5 class="text-muted mb-2">النسبة الإجمالية</h5>
                            <div class="score-circle total mx-auto">{{ $evaluation->total_percentage }}%</div>
                            <span class="badge bg-{{ $evaluation->total_percentage >= 70 ? 'success' : ($evaluation->total_percentage >= 50 ? 'warning' : 'danger') }} badge-total mt-2">
                                {{ $evaluation->total_percentage >= 70 ? 'متميز' : ($evaluation->total_percentage >= 50 ? 'مقبول' : 'ضعيف') }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- النصوص التفصيلية -->
            <div class="row g-3">
                @if($evaluation->strengths)
                    <div class="col-md-6">
                        <div class="card border-0 bg-success bg-opacity-10 h-100">
                            <div class="card-body">
                                <h5 class="fw-bold text-success"><i class="fas fa-thumbs-up me-2"></i>نقاط القوة</h5>
                                <p class="mb-0">{{ $evaluation->strengths }}</p>
                            </div>
                        </div>
                    </div>
                @endif

                @if($evaluation->weaknesses)
                    <div class="col-md-6">
                        <div class="card border-0 bg-danger bg-opacity-10 h-100">
                            <div class="card-body">
                                <h5 class="fw-bold text-danger"><i class="fas fa-thumbs-down me-2"></i>نقاط الضعف</h5>
                                <p class="mb-0">{{ $evaluation->weaknesses }}</p>
                            </div>
                        </div>
                    </div>
                @endif

                @if($evaluation->recommendations)
                    <div class="col-12">
                        <div class="card border-0 bg-info bg-opacity-10">
                            <div class="card-body">
                                <h5 class="fw-bold text-info"><i class="fas fa-lightbulb me-2"></i>التوصيات</h5>
                                <p class="mb-0">{{ $evaluation->recommendations }}</p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- رابط المشروع -->
            <div class="mt-4 text-center">
                <a href="{{ route('projects.show', $evaluation->project) }}" class="btn btn-outline-primary px-4">
                    <i class="fas fa-eye me-2"></i> عرض المشروع
                </a>
            </div>
        </div>
    </div>
</div>
@endsection