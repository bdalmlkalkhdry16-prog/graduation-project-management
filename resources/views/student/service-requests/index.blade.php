@extends('layouts.app')

@section('title', 'طلباتي')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>طلباتي</h2>
        <a href="{{ route('student.service-requests.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i> طلب جديد
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if (! $requests)
        <div class="alert alert-warning">لا يوجد ملف أكاديمي مرتبط بحسابك بعد.</div>
    @else
        <div class="card">
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead><tr><th>النوع</th><th>الحالة</th><th>التاريخ</th><th></th></tr></thead>
                    <tbody>
                    @forelse ($requests as $request)
                        <tr>
                            <td>{{ $request->type }}</td>
                            <td>{{ \App\Models\StudentServiceRequest::statusLabel($request->status) }}</td>
                            <td>{{ $request->created_at->format('Y-m-d') }}</td>
                            <td><a href="{{ route('student.service-requests.show', $request) }}" class="btn btn-sm btn-outline-primary">عرض</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted py-4">لا توجد طلبات بعد.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mt-3">{{ $requests->links() }}</div>
    @endif
</div>
@endsection