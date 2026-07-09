@extends('layouts.app')

@section('title', 'طرح فكرة مشروع')

@push('styles')
<style>
    .idea-card {
        border: none;
        border-radius: 1.25rem;
        box-shadow: 0 2px 12px rgba(0,0,0,0.04);
        overflow: hidden;
    }
    .idea-card .card-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        padding: 1.25rem 1.5rem;
    }
    .idea-card .card-header h4 {
        font-weight: 700;
        margin: 0;
    }
    .idea-card .card-header h4 i {
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
        padding: 0.7rem;
        font-weight: 600;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .btn-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(102, 126, 234, 0.3);
    }
    .btn-cancel {
        border-radius: 0.75rem;
        padding: 0.7rem;
        font-weight: 500;
    }
</style>
@endpush

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card idea-card">
                    <div class="card-header">
                        <h4><i class="fas fa-lightbulb"></i> تقديم فكرة مشروع تخرج</h4>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('projects.submit_idea') }}" method="POST" id="ideaForm">
                            @csrf

                            <!-- عنوان الفكرة -->
                            <div class="mb-4">
                                <label class="form-label-custom">
                                    <span class="required-star">*</span> عنوان الفكرة
                                </label>
                                <input type="text" name="title_ar" 
                                       class="form-control @error('title_ar') is-invalid @enderror" 
                                       placeholder="اكتب عنواناً مختصراً وواضحاً للفكرة"
                                       value="{{ old('title_ar') }}"
                                       required>
                                @error('title_ar')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">يفضل أن يكون العنوان جذاباً ومعبراً عن محتوى الفكرة.</small>
                            </div>

                            <!-- الملخص -->
                            <div class="mb-4">
                                <label class="form-label-custom">الملخص</label>
                                <textarea name="abstract_ar" rows="4" 
                                          class="form-control @error('abstract_ar') is-invalid @enderror"
                                          placeholder="صف فكرتك بشكل موجز (من 2 إلى 4 فقرات)">{{ old('abstract_ar') }}</textarea>
                                @error('abstract_ar')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">اذكر أهداف الفكرة، المشكلة التي تحلها، والحل المقترح.</small>
                            </div>

                            <!-- الكلمات المفتاحية -->
                            <div class="mb-4">
                                <label class="form-label-custom">الكلمات المفتاحية</label>
                                <input type="text" name="keywords" 
                                       class="form-control @error('keywords') is-invalid @enderror" 
                                       placeholder="مثال: ذكاء اصطناعي, ويب, تطبيقات, أمن سيبراني"
                                       value="{{ old('keywords') }}">
                                @error('keywords')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">افصل بين الكلمات بفواصل (،) أو (,) لتسهيل البحث والتصنيف.</small>
                            </div>

                            <!-- التخصص -->
                            <div class="mb-4">
                                <label class="form-label-custom">التخصص</label>
                                <select name="specialization_id" class="form-select @error('specialization_id') is-invalid @enderror">
                                    <option value="">اختر التخصص المناسب للفكرة</option>
                                    @foreach($specializations as $spec)
                                        <option value="{{ $spec->id }}" {{ old('specialization_id') == $spec->id ? 'selected' : '' }}>
                                            {{ $spec->name_ar }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('specialization_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- معلومات إضافية (اختيارية) -->
                            <div class="mb-4">
                                <label class="form-label-custom">ملاحظات إضافية (اختياري)</label>
                                <textarea name="notes" rows="2" 
                                          class="form-control @error('notes') is-invalid @enderror"
                                          placeholder="أي معلومات إضافية ترغب في إضافتها عن الفكرة">{{ old('notes') }}</textarea>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- صندوق معلومات -->
                            <div class="info-box mb-4">
                                <i class="fas fa-info-circle"></i>
                                <span>سيتم مراجعة الفكرة من قبل المختصين، وسيتم إعلامك بنتيجة المراجعة عبر البريد الإلكتروني والإشعارات داخل النظام.</span>
                            </div>

                            <!-- أزرار الإرسال -->
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary btn-cancel px-4">
                                    <i class="fas fa-times me-1"></i> إلغاء
                                </a>
                                <button type="submit" class="btn btn-primary btn-submit px-4">
                                    <i class="fas fa-paper-plane me-2"></i> تقديم الفكرة
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
        // تحسين تفاعل الحقول: إزالة رسالة الخطأ عند الكتابة
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

        // عدد الحروف المتبقية للملخص (اختياري)
        const abstractField = document.querySelector('textarea[name="abstract_ar"]');
        if (abstractField) {
            const counter = document.createElement('small');
            counter.className = 'text-muted float-end';
            counter.id = 'abstractCounter';
            abstractField.parentElement.appendChild(counter);
            
            function updateCounter() {
                const max = 500;
                const current = abstractField.value.length;
                const remaining = max - current;
                counter.textContent = remaining >= 0 ? `${remaining} حرف متبقي` : `تجاوزت الحد الأقصى (${max})`;
                counter.style.color = remaining < 0 ? '#dc3545' : '#6c757d';
            }
            abstractField.addEventListener('input', updateCounter);
            updateCounter();
        }

        // تأكيد قبل الإرسال (اختياري)
        const form = document.getElementById('ideaForm');
        form.addEventListener('submit', function(e) {
            const title = document.querySelector('input[name="title_ar"]');
            if (title && title.value.trim().length < 3) {
                e.preventDefault();
                alert('يرجى كتابة عنوان واضح للفكرة (أقل شيء 3 أحرف).');
                title.focus();
            }
        });
    });
</script>
@endpush