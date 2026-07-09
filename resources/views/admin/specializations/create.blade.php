@extends('layouts.app')

@section('title', 'إضافة تخصص جديد')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0">إضافة تخصص جديد</h4>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.specializations.store') }}" method="POST">
                            @csrf
                            @include('admin.specializations._form')
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">حفظ</button>
                                <a href="{{ route('admin.specializations.index') }}" class="btn btn-outline-secondary">إلغاء</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
