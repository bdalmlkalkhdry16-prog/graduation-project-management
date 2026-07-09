@extends('layouts.app')

@section('title', 'التقارير')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header">
            <h4 class="mb-0">إنشاء تقرير</h4>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.reports.generate') }}" method="POST" target="_blank">
                @csrf
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">نوع التقرير</label>
                        <select name="report_type" class="form-select" required>
                            <option value="projects">المشاريع</option>
                            <option value="students">الطلاب</option>
                            <option value="supervisors">المشرفين</option>
                            <option value="statistics">إحصائيات عامة</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">صيغة التقرير</label>
                        <select name="format" class="form-select" required>
                            <option value="html">عرض في المتصفح</option>
                        
                            <option value="excel">Excel</option>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">السنة الأكاديمية</label>
                        <select name="year" class="form-select">
                            <option value="">الكل</option>
                            @foreach($years as $year)
                                <option value="{{ $year }}">{{ $year }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">الكلية</label>
                        <select name="college_id" class="form-select" id="college">
                            <option value="">الكل</option>
                            @foreach($colleges as $college)
                                <option value="{{ $college->id }}">{{ $college->name_ar }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">القسم</label>
                        <select name="department_id" class="form-select" id="department">
                            <option value="">الكل</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}" data-college="{{ $dept->college_id }}">{{ $dept->name_ar }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">التخصص</label>
                        <select name="specialization_id" class="form-select" id="specialization">
                            <option value="">الكل</option>
                            @foreach($specializations as $spec)
                                <option value="{{ $spec->id }}" data-department="{{ $spec->department_id }}">{{ $spec->name_ar }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">حالة المشروع</label>
                        <select name="status" class="form-select">
                            <option value="">الكل</option>
                            <option value="draft">مسودة</option>
                            <option value="submitted">تم التقديم</option>
                            <option value="under_review">قيد المراجعة</option>
                            <option value="approved">مقبول</option>
                            <option value="rejected">مرفوض</option>
                            <option value="completed">مكتمل</option>
                        </select>
                    </div>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">إنشاء التقرير</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Dynamic filtering of departments based on college
    const collegeSelect = document.getElementById('college');
    const departmentSelect = document.getElementById('department');
    const specializationSelect = document.getElementById('specialization');

    function filterDepartments() {
        const collegeId = collegeSelect.value;
        for (let i = 0; i < departmentSelect.options.length; i++) {
            const opt = departmentSelect.options[i];
            const deptCollege = opt.getAttribute('data-college');
            if (collegeId === '' || deptCollege === collegeId) {
                opt.style.display = '';
            } else {
                opt.style.display = 'none';
            }
        }
        departmentSelect.value = '';
        filterSpecializations();
    }

    function filterSpecializations() {
        const departmentId = departmentSelect.value;
        for (let i = 0; i < specializationSelect.options.length; i++) {
            const opt = specializationSelect.options[i];
            const specDept = opt.getAttribute('data-department');
            if (departmentId === '' || specDept === departmentId) {
                opt.style.display = '';
            } else {
                opt.style.display = 'none';
            }
        }
        specializationSelect.value = '';
    }

    collegeSelect.addEventListener('change', filterDepartments);
    departmentSelect.addEventListener('change', filterSpecializations);
    filterDepartments();
</script>
@endpush
@endsection