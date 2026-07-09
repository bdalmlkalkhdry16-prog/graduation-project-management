@extends('layouts.app')

@section('title', 'تعديل مستخدم')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">تعديل المستخدم: {{ $user->name }}</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.users.update', $user) }}" method="POST">
                        @csrf
                        @method('PUT')
                        @include('admin.users._form')
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">تحديث</button>
                            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">إلغاء</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection