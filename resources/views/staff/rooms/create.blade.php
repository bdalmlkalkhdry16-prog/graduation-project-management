@extends('layouts.app')
@section('title', 'إضافة قاعة/معمل')
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header"><h4 class="mb-0">إضافة قاعة/معمل</h4></div>
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                        </div>
                    @endif
                    <form action="{{ route('staff.rooms.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">الاسم</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">المبنى</label>
                            <input type="text" name="building" class="form-control" value="{{ old('building') }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">السعة</label>
                            <input type="number" name="capacity" class="form-control" value="{{ old('capacity') }}" min="1" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">النوع</label>
                            <select name="type" class="form-select" required>
                                <option value="classroom" {{ old('type') === 'classroom' ? 'selected' : '' }}>قاعة</option>
                                <option value="lab" {{ old('type') === 'lab' ? 'selected' : '' }}>معمل</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">الحالة</label>
                            <select name="status" class="form-select" required>
                                <option value="available" {{ old('status') === 'available' ? 'selected' : '' }}>متاحة</option>
                                <option value="maintenance" {{ old('status') === 'maintenance' ? 'selected' : '' }}>تحت الصيانة</option>
                                <option value="closed" {{ old('status') === 'closed' ? 'selected' : '' }}>مغلقة</option>
                            </select>
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">حفظ</button>
                            <a href="{{ route('staff.rooms.index') }}" class="btn btn-outline-secondary">إلغاء</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection