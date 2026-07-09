@extends('layouts.app')

@section('title', 'إضافة قسم جديد')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0">إضافة قسم جديد</h4>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.departments.store') }}" method="POST">
                            @csrf
                            @include('admin.departments._form')
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">حفظ</button>
                                <a href="{{ route('admin.departments.index') }}" class="btn btn-outline-secondary">إلغاء</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
