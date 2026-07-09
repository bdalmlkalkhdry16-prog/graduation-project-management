@extends('layouts.app')

@section('title', 'تعديل القسم')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0">تعديل القسم: {{ $department->name_ar }}</h4>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.departments.update', $department) }}" method="POST">
                            @csrf
                            @method('PUT')
                            @include('admin.departments._form')
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">تحديث</button>
                                <a href="{{ route('admin.departments.index') }}" class="btn btn-outline-secondary">إلغاء</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
