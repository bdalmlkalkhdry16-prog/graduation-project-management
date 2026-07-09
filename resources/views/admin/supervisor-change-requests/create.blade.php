@extends('layouts.app')

@section('title', 'طلب تغيير المشرف')

@push('styles')
<style>
    .request-card {
        border: none;
        border-radius: 1.25rem;
        box-shadow: 0 2px 12px rgba(0,0,0,0.04);
        overflow: hidden;
    }
    .request-card .card-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        padding: 1.25rem 1.5rem;
    }
    .request-card .card-header h4 {
        font-weight: 700;
        margin: 0;
    }
    .request-card .card-header h4 i {
        margin-left: 0.5rem;
    }
    .form-label-custom {
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 0.4rem;
    }
    .form-label-custom .required-star {
        color: #dc3545;
        margin-right: 0.2rem;
    }
    .form-control, .form-select {
        border-radius: 0.75rem;
        border: 1px solid #e2e8f0;
        transition: border-color 0.2s, box-shadow 0.2s;
        padding: 0.6rem 1rem;
    }
    .form-control:focus, .form-select:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.15);
    }
    .form-control.is-invalid, .form-select.is-invalid {
        border-color: #dc3545;
        box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.1);
    }
    .invalid-feedback {
        font-size: 0.85rem;
        margin-top: 0.3rem;
    }
    .info-box {
        background: #f0f4ff;
        border-radius: 0.75rem;
        padding: 1rem 1.25rem;
        border-right: 4px solid #667eea;
    }
    .info-box i {
        color: #667eea;
        margin-left: 0.5rem;
    }
    .btn-submit {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        border-radius: 0.75rem;
        padding: 0.7rem 1.5rem;
        font-weight: 600;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .btn-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
    }
    .btn-cancel {
        border-radius: 0.75rem;
        padding: 0.7rem 1.5rem;
        font-weight: 500;
    }
    .current-supervisor-display {
        background: #f8fafc;
        border-radius: 0.75rem;
        padding: 0.6rem 1rem;
        color: #1e293b;
        font-weight: 500;
        border: 1px solid #e2e8f0;
    }
    .current-supervisor-display i {
        color: #667eea;
        margin-left: 0.5rem;
    }
</style>
@endpush

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card request-card">
                <div class="card-header">
                    <h4><i class="fas fa-user-edit"></i> طلب تغيير المشرف</h4>
                    <p class="mb-0 small text-white-50">{{ $project->title_ar }}</p>
                </div>
                <div class="card-body">
                    <form action="{{ route('supervisor-requests.store', $project) }}" method="POST">
                        @csrf

                        <!-- المشرف الحالي -->
                        <div class="mb-4">
                            <label class="form-label-custom">المشرف الحالي</label>
                            <div class="current-supervisor-display">
                                <i class="fas fa-user-tie"></i>
                                {{ $project->supervisor->name }}
                            </div>
                        </div>

                        <!-- المشرف المطلوب -->
                        <div class="mb-4">
                            <label class="form-label-custom">
                                <span class="required-star">*</span> المشرف المطلوب
                            </label>
                            <select name="proposed_supervisor_id" class="form-select @error('proposed_supervisor_id') is-invalid @enderror" required>
                                <option value="">اختر المشرف المناسب</option>
                                @foreach($supervisors as $supervisor)
                                    <option value="{{ $supervisor->id }}" 
                                        {{ $supervisor->id == $project->supervisor_id ? 'disabled' : '' }}
                                        {{ old('proposed_supervisor_id') == $supervisor->id ? 'selected' : '' }}>
                                        {{ $supervisor->name }}
                                        @if($supervisor->id == $project->supervisor_id)
                                            (المشرف الحالي)
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('proposed_supervisor_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">يرجى اختيار مشرف آخر غير المشرف الحالي.</small>
                        </div>

                        <!-- سبب الطلب -->
                        <div class="mb-4">
                            <label class="form-label-custom">
                                <span class="required-star">*</span> سبب الطلب
                            </label>
                            <textarea name="reason" rows="4" 
                                      class="form-control @error('reason') is-invalid @enderror" 
                                      placeholder="اذكر سبب رغبتك في تغيير المشرف بشكل واضح ومفصل..."
                                      required>{{ old('reason') }}</textarea>
                            @error('reason')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">يُفضل ذكر أسباب موضوعية تسهل على الإدارة اتخاذ القرار.</small>
                        </div>

                        <!-- صندوق معلومات -->
                        <div class="info-box mb-4">
                            <i class="fas fa-info-circle"></i>
                            <span>سيتم إرسال الطلب إلى الإدارة للمراجعة، وسيتم إعلامك بنتيجة الطلب عبر البريد الإلكتروني والإشعارات داخل النظام.</span>
                        </div>

                        <!-- أزرار الإرسال -->
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="{{ route('projects.show', $project) }}" class="btn btn-outline-secondary btn-cancel px-4">
                                <i class="fas fa-times me-1"></i> إلغاء
                            </a>
                            <button type="submit" class="btn btn-primary btn-submit px-4">
                                <i class="fas fa-paper-plane me-2"></i> إرسال الطلب
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
    document.addEventListener('DOMContentLoaded', function() {
        // إزالة رسائل الخطأ عند التغيير
        const inputs = document.querySelectorAll('.form-control, .form-select');
        inputs.forEach(input => {
            input.addEventListener('input', function() {
                if (this.classList.contains('is-invalid')) {
                    this.classList.remove('is-invalid');
                    const feedback = this.parentElement.querySelector('.invalid-feedback');
                    if (feedback) feedback.style.display = 'none';
                }
            });
        });

        // تأكيد الإرسال (اختياري)
        const form = document.querySelector('form');
        form.addEventListener('submit', function(e) {
            const reason = document.querySelector('textarea[name="reason"]');
            if (reason && reason.value.trim().length < 10) {
                e.preventDefault();
                alert('يرجى كتابة سبب واضح للطلب (على الأقل 10 أحرف).');
                reason.focus();
            }
        });
    });
</script>
@endpush