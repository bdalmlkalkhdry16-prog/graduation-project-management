@extends('layouts.app')

@section('title', 'ملف الطالب: ' . $studentProfile->user->name)

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>{{ $studentProfile->user->name }}</h2>
        <a href="{{ route('staff.student-profiles.edit', $studentProfile) }}" class="btn btn-outline-secondary">تعديل</a>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card mb-4">
        <div class="card-body">
            <dl class="row mb-0">
                <dt class="col-sm-3">رقم القيد</dt>
                <dd class="col-sm-9">{{ $studentProfile->number_student }}</dd>

                <dt class="col-sm-3">البريد الإلكتروني</dt>
                <dd class="col-sm-9">{{ $studentProfile->user->email }}</dd>

                <dt class="col-sm-3">التخصص</dt>
                <dd class="col-sm-9">{{ $studentProfile->specialization->name ?? '-' }}</dd>

                <dt class="col-sm-3">البرنامج</dt>
                <dd class="col-sm-9">
                    @if ($studentProfile->program)
                        {{ $studentProfile->program->level === 'diploma' ? 'دبلوم' : 'بكالوريوس' }}
                    @else
                        -
                    @endif
                </dd>

                <dt class="col-sm-3">المستوى الحالي</dt>
                <dd class="col-sm-9">{{ $studentProfile->currentLevel->name ?? '-' }}</dd>

                <dt class="col-sm-3">سنة القبول</dt>
                <dd class="col-sm-9">{{ $studentProfile->admission_year ?? '-' }}</dd>

                <dt class="col-sm-3">الحالة الأكاديمية</dt>
                <dd class="col-sm-9">{{ $studentProfile->academic_status }}</dd>
            </dl>
        </div>
    </div>

    <h4>طلبات الخدمة</h4>
    <div class="card">
        <div class="card-body p-0">
            <table class="table mb-0">
                <thead><tr><th>النوع</th><th>الحالة</th><th>التاريخ</th></tr></thead>
                <tbody>
                @forelse ($studentProfile->serviceRequests as $request)
                    <tr>
                        <td>{{ $request->type }}</td>
                        <td>{{ \App\Models\StudentServiceRequest::statusLabel($request->status) }}</td>
                        <td>{{ $request->created_at->format('Y-m-d') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="text-center text-muted py-3">لا توجد طلبات خدمة.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection