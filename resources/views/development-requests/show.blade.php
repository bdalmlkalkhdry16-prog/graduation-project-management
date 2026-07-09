@extends('layouts.app')

@section('title', 'تفاصيل طلب التطوير')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">طلب تطوير المشروع: {{ $developmentRequest->project->title_ar }}</h4>
            <a href="{{ route('development-requests.index') }}" class="btn btn-secondary btn-sm">عودة</a>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-6">
                    <strong>مقدم الطلب:</strong> {{ $developmentRequest->student->name }}
                </div>
                <div class="col-md-6">
                    <strong>تاريخ التقديم:</strong> {{ $developmentRequest->created_at->format('Y-m-d H:i') }}
                </div>
            </div>
            <div class="mb-3">
                <strong>سبب الطلب:</strong>
                <p>{{ $developmentRequest->reason }}</p>
            </div>
            @if($developmentRequest->proposed_improvements)
            <div class="mb-3">
                <strong>التحسينات المقترحة:</strong>
                <p>{{ $developmentRequest->proposed_improvements }}</p>
            </div>
            @endif
            <div class="mb-3">
                <strong>الحالة:</strong>
                @if($developmentRequest->status == 'pending')
                    <span class="badge bg-warning">قيد الانتظار</span>
                @elseif($developmentRequest->status == 'approved')
                    <span class="badge bg-success">مقبول</span>
                @else
                    <span class="badge bg-danger">مرفوض</span>
                @endif
            </div>
            @if($developmentRequest->admin_feedback)
            <div class="alert alert-info">
                <strong>ملاحظات الإدارة:</strong><br>
                {{ $developmentRequest->admin_feedback }}
            </div>
            @endif

            @if(auth()->user()->isAdmin() && $developmentRequest->status == 'pending')
                <div class="row mt-4">
                    <div class="col-md-6">
                        <form action="{{ route('development-requests.approve', $developmentRequest) }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">ملاحظات القبول (اختياري)</label>
                                <textarea name="admin_feedback" class="form-control" rows="2"></textarea>
                            </div>
                            <button type="submit" class="btn btn-success">قبول الطلب</button>
                        </form>
                    </div>
                    <div class="col-md-6">
                        <form action="{{ route('development-requests.reject', $developmentRequest) }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">سبب الرفض <span class="text-danger">*</span></label>
                                <textarea name="admin_feedback" class="form-control" rows="2" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-danger">رفض الطلب</button>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection