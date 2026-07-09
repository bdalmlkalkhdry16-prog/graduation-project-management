@extends('layouts.app')

@section('title', 'تعديل طلب تطوير')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">تعديل طلب تطوير المشروع: {{ $developmentRequest->project->title_ar }}</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('development-requests.update', $developmentRequest) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label">سبب طلب التطوير <span class="text-danger">*</span></label>
                            <textarea name="reason" class="form-control @error('reason') is-invalid @enderror" rows="4" required>{{ old('reason', $developmentRequest->reason) }}</textarea>
                            @error('reason')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">التحسينات المقترحة</label>
                            <textarea name="proposed_improvements" class="form-control @error('proposed_improvements') is-invalid @enderror" rows="4">{{ old('proposed_improvements', $developmentRequest->proposed_improvements) }}</textarea>
                            @error('proposed_improvements')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> سيتم إعادة إرسال الطلب للمراجعة بعد التعديل.
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">تحديث الطلب</button>
                            <a href="{{ route('development-requests.show', $developmentRequest) }}" class="btn btn-outline-secondary">إلغاء</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection