@extends('layouts.app')

@section('title', 'تعديل الكلية')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">تعديل الكلية: {{ $college->name_ar }}</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.colleges.update', $college) }}" method="POST">
                        @csrf
                        @method('PUT')
                        @include('admin.colleges._form')
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">تحديث</button>
                            <a href="{{ route('admin.colleges.index') }}" class="btn btn-outline-secondary">إلغاء</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection