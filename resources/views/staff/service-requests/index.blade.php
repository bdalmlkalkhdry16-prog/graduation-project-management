@extends('layouts.app')

@section('title', 'طلبات الخدمة')

@section('content')
<div class="container-fluid">
    <h2 class="mb-4">طلبات الخدمة</h2>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">الحالة</label>
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="">الكل</option>
                        @foreach (['pending', 'in_progress', 'approved', 'rejected', 'completed'] as $status)
                            <option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>
                                {{ \App\Models\StudentServiceRequest::statusLabel($status) }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead>
                    <tr><th>الطالب</th><th>النوع</th><th>الحالة</th><th>التاريخ</th><th></th></tr>
                </thead>
                <tbody>
                @forelse ($requests as $request)
                    <tr>
                        <td>{{ $request->studentProfile->user->name }}</td>
                        <td>{{ $request->type }}</td>
                        <td>{{ \App\Models\StudentServiceRequest::statusLabel($request->status) }}</td>
                        <td>{{ $request->created_at->format('Y-m-d') }}</td>
                        <td><a href="{{ route('staff.service-requests.show', $request) }}" class="btn btn-sm btn-outline-primary">عرض</a></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">لا توجد طلبات.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">{{ $requests->links() }}</div>
</div>
@endsection