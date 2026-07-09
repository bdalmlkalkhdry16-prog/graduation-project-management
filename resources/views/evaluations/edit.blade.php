@extends('layouts.app')

@section('title', 'تعديل التقييم')

@push('styles')
<style>
    .score-input-group {
        position: relative;
    }
    .score-input-group .form-control {
        padding-left: 3.5rem;
    }
    .score-input-group .score-label {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        font-weight: 600;
        color: #6c757d;
        font-size: 0.9rem;
        background: #f8f9fa;
        padding: 0.1rem 0.5rem;
        border-radius: 0.3rem;
        border: 1px solid #e9ecef;
    }
    .score-preview {
        font-size: 0.9rem;
        font-weight: 500;
        padding: 0.25rem 0.75rem;
        border-radius: 2rem;
    }
    .score-preview.weak {
        background: #f8d7da;
        color: #842029;
    }
    .score-preview.moderate {
        background: #fff3cd;
        color: #664d03;
    }
    .score-preview.good {
        background: #d1e7dd;
        color: #0a3622;
    }
    .score-preview.excellent {
        background: #cfe2ff;
        color: #084298;
    }
    .score-slider::-webkit-slider-runnable-track {
        height: 6px;
        background: #e9ecef;
        border-radius: 3px;
    }
    .score-slider::-webkit-slider-thumb {
        width: 16px;
        height: 16px;
        margin-top: -5px;
        background: #0d6efd;
        border-radius: 50%;
        cursor: pointer;
    }
    .score-slider:focus {
        outline: none;
    }
    .weight-badge {
        font-size: 0.75rem;
        background: #e9ecef;
        color: #495057;
        padding: 0.2rem 0.6rem;
        border-radius: 1rem;
        margin-right: 0.5rem;
    }
</style>
@endpush

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <h4 class="mb-0 fw-bold">
                            <i class="fas fa-star text-warning me-2"></i>تعديل تقييم المشروع
                        </h4>
                        <span class="badge bg-secondary">{{ $evaluation->project->title_ar }}</span>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('evaluations.update', $evaluation) }}" method="POST" id="evaluationForm">
                            @csrf
                            @method('PUT')

                            <!-- عرض النسبة الإجمالية الحية -->
                            <div class="alert alert-info bg-opacity-10 border-0 d-flex justify-content-between align-items-center mb-4">
                                <span class="fw-semibold"><i class="fas fa-calculator me-2"></i>النسبة الإجمالية المحسوبة:</span>
                                <span id="totalPercentageDisplay" class="badge bg-primary fs-6 px-3 py-2">0%</span>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <label class="form-label fw-semibold">
                                        الإبداع <span class="weight-badge">الوزن 40%</span>
                                    </label>
                                    <div class="score-input-group">
                                        <input type="number" name="creativity_score" id="creativity_score"
                                               class="form-control score-input"
                                               min="0" max="100"
                                               value="{{ old('creativity_score', $evaluation->creativity_score) }}"
                                               required>
                                        <span class="score-label">/100</span>
                                    </div>
                                    <input type="range" class="form-range score-slider mt-1" min="0" max="100" step="1"
                                           value="{{ old('creativity_score', $evaluation->creativity_score) }}"
                                           oninput="document.getElementById('creativity_score').value = this.value; updateTotal();">
                                </div>
                                <div class="col-md-6 mb-4">
                                    <label class="form-label fw-semibold">
                                        التنفيذ <span class="weight-badge">الوزن 30%</span>
                                    </label>
                                    <div class="score-input-group">
                                        <input type="number" name="implementation_score" id="implementation_score"
                                               class="form-control score-input"
                                               min="0" max="100"
                                               value="{{ old('implementation_score', $evaluation->implementation_score) }}"
                                               required>
                                        <span class="score-label">/100</span>
                                    </div>
                                    <input type="range" class="form-range score-slider mt-1" min="0" max="100" step="1"
                                           value="{{ old('implementation_score', $evaluation->implementation_score) }}"
                                           oninput="document.getElementById('implementation_score').value = this.value; updateTotal();">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <label class="form-label fw-semibold">
                                        التوثيق <span class="weight-badge">الوزن 20%</span>
                                    </label>
                                    <div class="score-input-group">
                                        <input type="number" name="documentation_score" id="documentation_score"
                                               class="form-control score-input"
                                               min="0" max="100"
                                               value="{{ old('documentation_score', $evaluation->documentation_score) }}"
                                               required>
                                        <span class="score-label">/100</span>
                                    </div>
                                    <input type="range" class="form-range score-slider mt-1" min="0" max="100" step="1"
                                           value="{{ old('documentation_score', $evaluation->documentation_score) }}"
                                           oninput="document.getElementById('documentation_score').value = this.value; updateTotal();">
                                </div>
                                <div class="col-md-6 mb-4">
                                    <label class="form-label fw-semibold">
                                        العرض <span class="weight-badge">الوزن 10%</span>
                                    </label>
                                    <div class="score-input-group">
                                        <input type="number" name="presentation_score" id="presentation_score"
                                               class="form-control score-input"
                                               min="0" max="100"
                                               value="{{ old('presentation_score', $evaluation->presentation_score) }}"
                                               required>
                                        <span class="score-label">/100</span>
                                    </div>
                                    <input type="range" class="form-range score-slider mt-1" min="0" max="100" step="1"
                                           value="{{ old('presentation_score', $evaluation->presentation_score) }}"
                                           oninput="document.getElementById('presentation_score').value = this.value; updateTotal();">
                                </div>
                            </div>

                            <hr>

                            <div class="mb-4">
                                <label class="form-label fw-semibold"><i class="fas fa-thumbs-up me-1"></i>نقاط القوة</label>
                                <textarea name="strengths" class="form-control" rows="3" placeholder="اذكر نقاط القوة في المشروع...">{{ old('strengths', $evaluation->strengths) }}</textarea>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-semibold"><i class="fas fa-thumbs-down me-1"></i>نقاط الضعف</label>
                                <textarea name="weaknesses" class="form-control" rows="3" placeholder="اذكر نقاط الضعف التي تحتاج إلى تحسين...">{{ old('weaknesses', $evaluation->weaknesses) }}</textarea>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-semibold"><i class="fas fa-lightbulb me-1"></i>التوصيات</label>
                                <textarea name="recommendations" class="form-control" rows="3" placeholder="قدم توصيات لتحسين المشروع...">{{ old('recommendations', $evaluation->recommendations) }}</textarea>
                            </div>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="{{ route('projects.show', $evaluation->project) }}" class="btn btn-outline-secondary px-4">
                                    <i class="fas fa-times me-1"></i> إلغاء
                                </a>
                                <button type="submit" class="btn btn-primary px-4">
                                    <i class="fas fa-save me-1"></i> تحديث التقييم
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    // دالة حساب النسبة الإجمالية
    function updateTotal() {
        const creativity = parseFloat(document.getElementById('creativity_score').value) || 0;
        const implementation = parseFloat(document.getElementById('implementation_score').value) || 0;
        const documentation = parseFloat(document.getElementById('documentation_score').value) || 0;
        const presentation = parseFloat(document.getElementById('presentation_score').value) || 0;

        const total = (creativity * 0.40) + (implementation * 0.30) + (documentation * 0.20) + (presentation * 0.10);
        const roundedTotal = Math.round(total * 100) / 100;

        const display = document.getElementById('totalPercentageDisplay');
        display.textContent = roundedTotal + '%';

        // تغيير لون البادج حسب النسبة
        display.className = 'badge fs-6 px-3 py-2';
        if (roundedTotal >= 80) {
            display.classList.add('bg-success');
        } else if (roundedTotal >= 60) {
            display.classList.add('bg-info');
        } else if (roundedTotal >= 40) {
            display.classList.add('bg-warning');
        } else {
            display.classList.add('bg-danger');
        }
    }

    // مزامنة السلايدر مع الحقل الرقمي عند التغيير اليدوي
    document.querySelectorAll('.score-input').forEach(input => {
        input.addEventListener('input', function() {
            const slider = this.parentElement.nextElementSibling;
            if (slider && slider.classList.contains('score-slider')) {
                slider.value = this.value;
            }
            updateTotal();
        });
    });

    // تهيئة العرض عند تحميل الصفحة
    document.addEventListener('DOMContentLoaded', function() {
        updateTotal();
    });
</script>
@endpush