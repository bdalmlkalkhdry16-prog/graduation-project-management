@extends('layouts.app')

@section('title', 'تحديد موعد المناقشة')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-header bg-primary text-white rounded-top-4">
                    <h4 class="mb-0 fw-bold"><i class="fas fa-calendar-alt me-2"></i> تحديد موعد مناقشة المشروع</h4>
                </div>
                <div class="card-body p-4">
                    <div class="alert alert-info mb-4">
                        <i class="fas fa-info-circle me-1"></i>
                        <strong>المشروع:</strong> {{ $project->title_ar }}<br>
                        <strong>المشرف الحالي:</strong> {{ $project->supervisor->name ?? 'غير محدد' }}<br>
                        @if($project->defense_date)
                            <strong>الموعد الحالي:</strong> {{ \Carbon\Carbon::parse($project->defense_date)->format('Y-m-d H:i') }}
                        @else
                            <strong>الموعد الحالي:</strong> <span class="text-muted">غير محدد بعد</span>
                        @endif
                    </div>

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('projects.set_defense', $project) }}" method="POST" id="defenseForm">
                        @csrf
                        @method('PUT')

                        <div class="mb-4">
                            <label class="form-label fw-semibold">📅 تاريخ ووقت المناقشة <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="defense_date" id="defense_date"
                                   class="form-control @error('defense_date') is-invalid @enderror" 
                                   value="{{ old('defense_date', $project->defense_date ? \Carbon\Carbon::parse($project->defense_date)->format('Y-m-d\TH:i') : '') }}" 
                                   required
                                   min="{{ now()->format('Y-m-d\TH:i') }}">
                            {{-- رسالة خطأ الخادم (إن وجدت) --}}
                            @error('defense_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            {{-- رسالة خطأ العميل (ستظهر تلقائياً عند إضافة is-invalid) --}}
                            <div class="invalid-feedback" id="defense-date-client-error">
                                ⚠️ يرجى تحديد موعد صحيح في المستقبل
                            </div>
                            <div class="form-text">يجب أن يكون التاريخ والوقت في المستقبل.</div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">📍 المكان</label>
                            <input type="text" name="defense_location" 
                                   class="form-control @error('defense_location') is-invalid @enderror" 
                                   value="{{ old('defense_location', $project->defense_location) }}" 
                                   placeholder="مثال: قاعة الاجتماعات A">
                            @error('defense_location')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">📝 ملاحظات إضافية</label>
                            <textarea name="defense_notes" rows="3" 
                                      class="form-control @error('defense_notes') is-invalid @enderror" 
                                      placeholder="أي تعليمات أو ملاحظات للطلاب">{{ old('defense_notes', $project->defense_notes) }}</textarea>
                            @error('defense_notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="alert alert-warning">
                            <i class="fas fa-bell me-1"></i>
                            سيتم إرسال إشعار لجميع أعضاء المشروع بعد حفظ الموعد.
                        </div>

                        <div class="d-flex justify-content-between gap-2">
                            <button type="submit" class="btn btn-primary px-4" id="submitBtn">
                                <i class="fas fa-save me-1"></i> حفظ الموعد
                            </button>
                            <a href="{{ route('projects.show', $project) }}" class="btn btn-outline-secondary px-4">
                                <i class="fas fa-times me-1"></i> إلغاء
                            </a>
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
    const form = document.getElementById('defenseForm');
    const dateInput = document.getElementById('defense_date');
    const clientError = document.getElementById('defense-date-client-error');

    // منع إرسال النموذج بالضغط على Enter (عدا textarea)
    form.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && e.target.tagName !== 'TEXTAREA') {
            e.preventDefault();
        }
    });

    // التحقق عند تقديم النموذج
    form.addEventListener('submit', function(e) {
        // إعادة تعيين حالة الحقل (إخفاء أي خطأ سابق)
        dateInput.classList.remove('is-invalid');
        
        const selectedDate = new Date(dateInput.value);
        const now = new Date();

        // إذا كان التاريخ فارغاً أو في الماضي
        if (!dateInput.value || selectedDate <= now) {
            e.preventDefault();             // منع الإرسال
            dateInput.classList.add('is-invalid');  // إظهار الخطأ
            // لا حاجة لإظهار/إخفاء clientError يدوياً، فـ Bootstrap يظهره مع is-invalid
            dateInput.focus();
        }
    });

    // عند تغيير التاريخ، أخفِ أي خطأ ظاهر
    dateInput.addEventListener('change', function() {
        dateInput.classList.remove('is-invalid');
    });
</script>
@endpush