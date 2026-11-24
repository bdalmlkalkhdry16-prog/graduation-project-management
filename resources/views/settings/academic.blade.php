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
                    <h5 class="mt-4">ربط مشاريع التخرج بالنظام الأكاديمي</h5>
                    <p class="small text-muted">كلها اختيارية: ما تتركه فارغًا أو غير مفعَّل لا يُفرض. تُطبَّق عند إضافة طالب لمشروع أو قبول دعوة، ولا تمس المشاريع القائمة.</p>
                    <div class="row g-3 mb-3">
                        <div class="col-md-4"><label class="form-label">أدنى ساعات معتمدة مُنجَزة (نجاح)</label>
                            <input type="number" step="0.5" min="0.5" name="project_min_completed_hours" value="{{ old('project_min_completed_hours', $project['min_hours']) }}" class="form-control"></div>
                        <div class="col-md-4"><label class="form-label">أدنى رقم مستوى حالي</label>
                            <input type="number" min="1" name="project_min_level" value="{{ old('project_min_level', $project['min_level']) }}" class="form-control"></div>
                        <div class="col-md-4"><label class="form-label">أقصى عدد أعضاء للمشروع</label>
                            <input type="number" min="1" name="project_max_members" value="{{ old('project_max_members', $project['max_members']) }}" class="form-control"></div>
                    </div>
                    <div class="form-check mb-2"><input type="hidden" name="project_require_fees_settled" value="0"><input class="form-check-input" type="checkbox" name="project_require_fees_settled" value="1" id="pf" @checked(old('project_require_fees_settled', $project['fees']))><label class="form-check-label" for="pf">اشتراط سداد الرسوم المولَّدة للانضمام لمشروع</label></div>
                    <div class="form-check mb-2"><input type="hidden" name="project_single_active" value="0"><input class="form-check-input" type="checkbox" name="project_single_active" value="1" id="ps" @checked(old('project_single_active', $project['single']))><label class="form-check-label" for="ps">لا يشارك الطالب إلا في مشروع واحد غير مرفوض أو مكتمل</label></div>
                    <div class="form-check mb-3"><input type="hidden" name="graduation_requires_project" value="0"><input class="form-check-input" type="checkbox" name="graduation_requires_project" value="1" id="pg" @checked(old('graduation_requires_project', $project['graduation']))><label class="form-check-label" for="pg">اشتراط مشروع تخرج مكتمل لإفادة التخرج</label></div>

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
