@extends('layouts.app')

@section('title', 'طلب خدمة')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header"><h4 class="mb-0">طلب خدمة — {{ $serviceRequest->studentProfile->user->name }}</h4></div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-3">النوع</dt>
                        <dd class="col-sm-9">{{ $serviceRequest->type }}</dd>

                        <dt class="col-sm-3">الوصف</dt>
                        <dd class="col-sm-9">{{ $serviceRequest->description ?? '-' }}</dd>

                        <dt class="col-sm-3">الحالة الحالية</dt>
                        <dd class="col-sm-9">{{ \App\Models\StudentServiceRequest::statusLabel($serviceRequest->status) }}</dd>

                        @if ($serviceRequest->handledBy)
                            <dt class="col-sm-3">عولج بواسطة</dt>
                            <dd class="col-sm-9">{{ $serviceRequest->handledBy->name }}</dd>
                        @endif
                    </dl>
                </div>
            </div>

            @can('updateStatus', $serviceRequest)
                <div class="card">
                    <div class="card-header"><h5 class="mb-0">تحديث الحالة</h5></div>
                    <div class="card-body">
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form action="{{ route('staff.service-requests.update-status', $serviceRequest) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <label class="form-label">الحالة الجديدة</label>
                                <select name="status" class="form-select" required>
                                    @foreach (['pending', 'in_progress', 'approved', 'rejected', 'completed'] as $status)
                                        <option value="{{ $status }}" {{ $serviceRequest->status === $status ? 'selected' : '' }}>
                                            {{ \App\Models\StudentServiceRequest::statusLabel($status) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">ملاحظات الموظف</label>
                                <textarea name="staff_notes" class="form-control" rows="3">{{ old('staff_notes', $serviceRequest->staff_notes) }}</textarea>
                            </div>

                            <button type="submit" class="btn btn-primary">تحديث</button>
                        </form>
                    </div>
                </div>
            @endcan
        </div>
    </div>
</div>
@endsection