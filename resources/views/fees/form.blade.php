@extends('layouts.app')
@section('title', $schedule->exists ? 'تعديل سعر الرسوم' : 'سعر رسوم جديد')
@section('content')
<div class="container">
    <div class="row justify-content-center"><div class="col-md-8">
        <div class="card">
            <div class="card-header"><h4 class="mb-0">{{ $schedule->exists ? 'تعديل سعر الرسوم' : 'سعر رسوم جديد' }}</h4></div>
            <div class="card-body">
                @include('layouts._errors')
                <form method="POST" action="{{ $schedule->exists ? route('fee-schedules.update', $schedule) : route('fee-schedules.store') }}">
                    @csrf @if ($schedule->exists) @method('PUT') @endif
                    <div class="mb-3">
                        <label class="form-label">القسم</label>
                        <select name="department_id" required class="form-select">
                            <option value="">— اختر —</option>
                            @foreach ($departments as $d)<option value="{{ $d->id }}" @selected((string) old('department_id', $schedule->department_id) === (string) $d->id)>{{ $d->name_ar }}</option>@endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">السنة الدراسية</label>
                        <select name="academic_year_id" required class="form-select">
                            <option value="">— اختر —</option>
                            @foreach ($years as $y)<option value="{{ $y->id }}" @selected((string) old('academic_year_id', $schedule->academic_year_id) === (string) $y->id)>{{ $y->name }}</option>@endforeach
                        </select>
                    </div>
                    <div class="row mb-3">
                        <div class="col-6"><label class="form-label">المبلغ</label>
                            <input type="number" step="0.01" min="0.01" name="amount" value="{{ old('amount', $schedule->amount) }}" required class="form-control"></div>
                        <div class="col-6"><label class="form-label">العملة</label>
                            <select name="currency" required class="form-select">
                                @foreach (\App\Models\FeeSchedule::CURRENCIES as $code => $label)<option value="{{ $code }}" @selected(old('currency', $schedule->currency) === $code)>{{ $label }}</option>@endforeach
                            </select></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ملاحظات</label>
                        <textarea name="notes" rows="2" class="form-control">{{ old('notes', $schedule->notes) }}</textarea>
                    </div>
                    <button class="btn btn-primary"><i class="fas fa-save me-1"></i> حفظ</button>
                    <a href="{{ route('fee-schedules.index') }}" class="btn btn-outline-secondary">إلغاء</a>
                </form>
            </div>
        </div>
    </div></div>
</div>
@endsection
