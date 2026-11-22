@extends('layouts.app')
@section('title', 'الرسوم الدراسية')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">الرسوم الدراسية</h2>
        @can('manage', \App\Models\FeeSchedule::class)
            <a href="{{ route('fee-schedules.create') }}" class="btn btn-primary"><i class="fas fa-plus me-1"></i> سعر جديد</a>
        @endcan
    </div>
    <p class="text-muted">لكل قسم سعره الخاص عن كل سنة دراسية، بالريال اليمني (القديم) أو بالدولار.</p>
    <div class="card"><div class="card-body p-0"><div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead><tr><th>السنة الدراسية</th><th>القسم</th><th>المبلغ</th><th>العملة</th><th></th></tr></thead>
            <tbody>
            @forelse ($schedules as $s)
                <tr>
                    <td>{{ $s->academicYear->name }}</td>
                    <td>{{ $s->department->name_ar }}</td>
                    <td>{{ number_format($s->amount, 2) }}</td>
                    <td>{{ \App\Models\FeeSchedule::currencyLabel($s->currency) }}</td>
                    <td class="text-nowrap">
                        @can('manage', \App\Models\FeeSchedule::class)
                            <a href="{{ route('fee-schedules.edit', $s) }}" class="btn btn-sm btn-outline-secondary">تعديل</a>
                            <form method="POST" action="{{ route('fee-schedules.destroy', $s) }}" class="d-inline" onsubmit="return confirm('حذف هذا السعر؟')">@csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">حذف</button></form>
                        @endcan
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted py-4">لم تُحدَّد أسعار بعد.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div></div></div>
</div>
@endsection
