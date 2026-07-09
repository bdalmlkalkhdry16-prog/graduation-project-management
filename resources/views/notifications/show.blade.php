@extends('layouts.app')

@section('title', $notification->title)

@section('content')
    <div class="container">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0">{{ $notification->title }}</h4>
                <small class="text-muted">{{ $notification->created_at->format('Y-m-d H:i') }}</small>
            </div>
            <div class="card-body">
                <p>{{ $notification->message }}</p>
                @if($notification->link)
                    <a href="{{ $notification->link }}" class="btn btn-primary mt-3">عرض التفاصيل</a>
                @endif
            </div>
            <div class="card-footer text-muted">
                نوع الإشعار: {{ $notification->type_name }}
            </div>
        </div>
    </div>
@endsection
