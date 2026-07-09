@extends('layouts.app')

@section('title', 'إضافة كلية جديدة')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">إضافة كلية جديدة</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.colleges.store') }}" method="POST">
                        @csrf
                        @include('admin.colleges._form')
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">حفظ</button>
                            <a href="{{ route('admin.colleges.index') }}" class="btn btn-outline-secondary">إلغاء</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection