@extends('layouts.app')
@section('title', 'الإعدادات الأكاديمية')
@section('content')
<div class="container">
    <div class="row justify-content-center"><div class="col-lg-8">
        <div class="card">
            <div class="card-header"><h4 class="mb-0">الإعدادات الأكاديمية</h4></div>
            <div class="card-body">
                <p class="text-muted">لا توجد قيم افتراضية: ما لم يُضبط السلم أو نسبة النجاح لا تُحسب التقديرات أو المعدلات أو النجاح والرسوب.</p>
                @include('layouts._errors')
                <form method="POST" action="{{ route('academic-settings.update') }}">
                    @csrf @method('PUT')
                    <div class="mb-3">
                        <label class="form-label">نسبة النجاح في المقرر (%) — اتركها فارغة إن لم تُحدَّد</label>
                        <input type="number" step="0.01" min="0" max="100" name="pass_percentage" value="{{ old('pass_percentage', $passPercentage) }}" class="form-control w-auto">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">مهلة التظلم على الدرجة (بالأيام من نشر النتيجة) — فارغة = بلا مهلة</label>
                        <input type="number" min="1" max="365" name="appeal_window_days" value="{{ old('appeal_window_days', $appealWindowDays) }}" class="form-control w-auto">
                    </div>
                    <h5 class="mt-4">سلم التقديرات</h5>
                    <p class="small text-muted">الصفوف الفارغة تُتجاهل. يجب أن يتضمن السلم تقديرًا حده الأدنى 0.</p>
                    @php($rows = old('scale', $scale))
                    @for ($i = 0; $i < 10; $i++)
                        <div class="row g-2 mb-2">
                            <div class="col"><input name="scale[{{ $i }}][letter]" value="{{ $rows[$i]['letter'] ?? '' }}" placeholder="الرمز" class="form-control"></div>
                            <div class="col"><input type="number" step="0.01" name="scale[{{ $i }}][min]" value="{{ $rows[$i]['min'] ?? '' }}" placeholder="الحد الأدنى %" class="form-control"></div>
                            <div class="col"><input type="number" step="0.01" name="scale[{{ $i }}][points]" value="{{ $rows[$i]['points'] ?? '' }}" placeholder="النقاط (اختياري)" class="form-control"></div>
                        </div>
                    @endfor
                    <button class="btn btn-primary mt-3"><i class="fas fa-save me-1"></i> حفظ</button>
                </form>
            </div>
        </div>
    </div></div>
</div>
@endsection
