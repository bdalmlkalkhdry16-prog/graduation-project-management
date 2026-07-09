@extends('layouts.app')

@section('title', 'الإشعارات')

@section('content')
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>الإشعارات</h2>
            <form action="{{ route('notifications.readAll') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-outline-primary">تحديد الكل كمقروء</button>
            </form>
        </div>

        <div class="card">
            <div class="card-body p-0">
                @forelse($notifications as $notification)
                    <div class="notification-item p-3 border-bottom {{ !$notification->is_read ? 'bg-light' : '' }}">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    @if($notification->type == 'success')
                                        <i class="fas fa-check-circle text-success"></i>
                                    @elseif($notification->type == 'error')
                                        <i class="fas fa-times-circle text-danger"></i>
                                    @elseif($notification->type == 'warning')
                                        <i class="fas fa-exclamation-triangle text-warning"></i>
                                    @else
                                        <i class="fas fa-info-circle text-info"></i>
                                    @endif
                                    <strong>{{ $notification->title }}</strong>
                                </div>
                                <p class="mb-1">{{ $notification->message }}</p>
                                <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                            </div>
                            <div>
                                <a href="{{ $notification->link ?? '#' }}" class="btn btn-sm btn-outline-primary me-1">عرض</a>
                                <form action="{{ route('notifications.destroy', $notification) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('حذف الإشعار؟')"><i class="fas fa-trash"></i></button>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-5">
                        <i class="fas fa-bell-slash fa-3x text-muted mb-3"></i>
                        <p class="text-muted">لا توجد إشعارات</p>
                    </div>
                @endforelse
            </div>
        </div>

        <div class="mt-3">
            {{ $notifications->links() }}
        </div>
    </div>
@endsection
