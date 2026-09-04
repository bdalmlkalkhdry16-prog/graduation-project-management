@extends('layouts.app')

@section('title', 'تفاصيل الطلب')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header"><h4 class="mb-0">{{ $serviceRequest->type }}</h4></div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-3">الحالة</dt>
                        <dd class="col-sm-9">{{ \App\Models\StudentServiceRequest::statusLabel($serviceRequest->status) }}</dd>

                        <dt class="col-sm-3">التفاصيل</dt>
                        <dd class="col-sm-9">{{ $serviceRequest->description ?? '-' }}</dd>

                        @if ($serviceRequest->staff_notes)
                            <dt class="col-sm-3">ملاحظات الموظف</dt>
                            <dd class="col-sm-9">{{ $serviceRequest->staff_notes }}</dd>
                        @endif

                        <dt class="col-sm-3">تاريخ التقديم</dt>
                        <dd class="col-sm-9">{{ $serviceRequest->created_at->format('Y-m-d H:i') }}</dd>
                    </dl>
                    <a href="{{ route('student.service-requests.index') }}" class="btn btn-outline-secondary">رجوع</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection