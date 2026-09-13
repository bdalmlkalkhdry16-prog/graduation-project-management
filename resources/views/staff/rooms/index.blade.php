@extends('layouts.app')
@section('title', 'القاعات والمعامل')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>القاعات والمعامل</h2>
        <a href="{{ route('staff.rooms.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i> إضافة قاعة/معمل
        </a>
    </div>
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    <div class="card">
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead><tr><th>الاسم</th><th>المبنى</th><th>السعة</th><th>النوع</th><th>الحالة</th><th></th></tr></thead>
                <tbody>
                @forelse ($rooms as $room)
                    <tr>
                        <td>{{ $room->name }}</td>
                        <td>{{ $room->building ?? '-' }}</td>
                        <td>{{ $room->capacity }}</td>
                        <td>{{ \App\Models\Room::typeLabel($room->type) }}</td>
                        <td>{{ \App\Models\Room::statusLabel($room->status) }}</td>
                        <td><a href="{{ route('staff.rooms.edit', $room) }}" class="btn btn-sm btn-outline-secondary">تعديل</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">لا توجد قاعات/معامل بعد.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $rooms->links() }}</div>
</div>
@endsection