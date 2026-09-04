@extends('layouts.app')

@section('title', 'طلب خدمة جديد')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header"><h4 class="mb-0">طلب خدمة جديد</h4></div>
                <div class="card-body">

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('student.service-requests.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">نوع الطلب</label>
                            <input type="text" name="type" class="form-control" value="{{ old('type') }}"
                                   placeholder="مثال: كشف درجات، إفادة قيد" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">تفاصيل إضافية</label>
                            <textarea name="description" class="form-control" rows="4">{{ old('description') }}</textarea>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">إرسال الطلب</button>
                            <a href="{{ route('student.service-requests.index') }}" class="btn btn-outline-secondary">إلغاء</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection