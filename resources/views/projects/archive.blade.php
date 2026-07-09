@extends('layouts.app')

@section('title', 'أرشيف المشاريع المكتملة')

@section('content')
<div class="container-fluid">
    <!-- رأس الصفحة مع معلومات سياقية -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h2 class="fw-bold mb-1">
                <i class="fas fa-archive text-success me-2"></i>أرشيف المشاريع المكتملة
            </h2>
            <p class="text-muted mb-0">استعراض المشاريع التي تم إنجازها ومناقشتها بنجاح</p>
        </div>
        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary shadow-sm">
            <i class="fas fa-arrow-right me-1"></i> العودة إلى لوحة التحكم
        </a>
    </div>

    <!-- بطاقات إحصائيات سريعة -->
    @php
        $totalCompleted = $projects->total();
        $avgSuccess = $projects->avg('success_percentage');
        $topProject = $projects->sortByDesc('success_percentage')->first();
        $uniqueSpecializations = $projects->pluck('specialization.name_ar')->unique()->filter()->count();
    @endphp
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm bg-success bg-opacity-10 h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">إجمالي المشاريع المكتملة</h6>
                            <h3 class="fw-bold mb-0">{{ $totalCompleted }}</h3>
                        </div>
                        <i class="fas fa-check-circle fa-2x text-success opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm bg-info bg-opacity-10 h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">متوسط نسبة النجاح</h6>
                            <h3 class="fw-bold mb-0">{{ number_format($avgSuccess ?? 0, 1) }}%</h3>
                        </div>
                        <i class="fas fa-chart-line fa-2x text-info opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm bg-primary bg-opacity-10 h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">التخصصات المشاركة</h6>
                            <h3 class="fw-bold mb-0">{{ $uniqueSpecializations }}</h3>
                        </div>
                        <i class="fas fa-graduation-cap fa-2x text-primary opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm bg-warning bg-opacity-10 h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">أعلى نسبة نجاح</h6>
                            <h3 class="fw-bold mb-0">{{ $topProject->success_percentage ?? '—' }}%</h3>
                            <small class="text-muted">{{ Str::limit($topProject->title_ar ?? '', 25) }}</small>
                        </div>
                        <i class="fas fa-trophy fa-2x text-warning opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- لوحة الفلترة والبحث المتقدمة -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold">
                    <i class="fas fa-filter me-2 text-success"></i>فلترة الأرشيف
                </h5>
                <a href="{{ route('projects.archive') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-redo-alt me-1"></i>إعادة تعيين
                </a>
            </div>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('projects.archive') }}" id="archiveFilterForm">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-graduation-cap me-1"></i>التخصص
                        </label>
                        <select name="specialization_id" class="form-select">
                            <option value="">جميع التخصصات</option>
                            @foreach($specializations as $spec)
                                <option value="{{ $spec->id }}" {{ request('specialization_id') == $spec->id ? 'selected' : '' }}>
                                    {{ $spec->name_ar }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-calendar-alt me-1"></i>السنة الأكاديمية
                        </label>
                        <select name="academic_year" class="form-select">
                            <option value="">الكل</option>
                            @foreach($academicYears as $year)
                                <option value="{{ $year }}" {{ request('academic_year') == $year ? 'selected' : '' }}>
                                    {{ $year }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-chalkboard-user me-1"></i>المشرف
                        </label>
                        <select name="supervisor_id" class="form-select">
                            <option value="">جميع المشرفين</option>
                            @foreach($supervisors as $supervisor)
                                <option value="{{ $supervisor->id }}" {{ request('supervisor_id') == $supervisor->id ? 'selected' : '' }}>
                                    {{ $supervisor->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-search me-1"></i>بحث
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0">
                                <i class="fas fa-search text-muted"></i>
                            </span>
                            <input type="text" name="search" class="form-control border-start-0" 
                                   placeholder="عنوان المشروع أو اسم الطالب..." 
                                   value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-md-1 d-flex align-items-end">
                        <button type="submit" class="btn btn-success w-100 shadow-sm">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- جدول المشاريع المكتملة مع تحسينات -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle mb-0">
                    <thead class="table-light">
                        <tr class="text-nowrap">
                            <th width="5%">#</th>
                            <th width="25%">عنوان المشروع</th>
                            <th width="15%">الطلاب</th>
                            <th width="12%">المشرف</th>
                            <th width="12%">التخصص</th>
                            <th width="8%">السنة</th>
                            <th width="10%">نسبة النجاح</th>
                            <th width="10%">تاريخ المناقشة</th>
                            <th width="8%">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($projects as $project)
                        <tr>
                            <td><span class="fw-bold">{{ $loop->iteration }}</span></td>
                            <td>
                                <a href="{{ route('projects.show', $project) }}" class="text-decoration-none fw-semibold">
                                    {{ Str::limit($project->title_ar, 60) }}
                                </a>
                                @if($project->files->count() > 0)
                                    <span class="badge bg-light text-dark ms-1" data-bs-toggle="tooltip" title="{{ $project->files->count() }} ملف(ات) مرفقة">
                                        <i class="fas fa-paperclip"></i> {{ $project->files->count() }}
                                    </span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $studentsList = $project->students->pluck('name')->take(2);
                                    $remaining = $project->students->count() - 2;
                                @endphp
                                @if($studentsList->isNotEmpty())
                                    <div class="d-flex flex-column gap-1">
                                        <div>{{ $studentsList->implode(', ') }}</div>
                                        @if($remaining > 0)
                                            <small class="text-muted">+{{ $remaining }} آخرين</small>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($project->supervisor)
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="fas fa-chalkboard-user text-success"></i>
                                        <span>{{ $project->supervisor->name }}</span>
                                    </div>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-light text-dark">
                                    {{ $project->specialization->name_ar ?? '-' }}
                                </span>
                            </td>
                            <td>{{ $project->academic_year }}</td>
                            <td>
                                @if($project->success_percentage)
                                    <div class="d-flex flex-column gap-1" style="min-width: 100px;">
                                        <div class="d-flex justify-content-between small">
                                            <span>الإنجاز</span>
                                            <span class="fw-bold text-success">{{ $project->success_percentage }}%</span>
                                        </div>
                                        <div class="progress" style="height: 6px;">
                                            <div class="progress-bar bg-success" 
                                                 role="progressbar" 
                                                 style="width: {{ $project->success_percentage }}%"
                                                 aria-valuenow="{{ $project->success_percentage }}" 
                                                 aria-valuemin="0" 
                                                 aria-valuemax="100">
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @if($project->defense_date)
                                    <div class="d-flex align-items-center gap-1 text-nowrap">
                                        <i class="fas fa-calendar-check text-muted small"></i>
                                        <span>{{ \Carbon\Carbon::parse($project->defense_date)->format('Y-m-d') }}</span>
                                    </div>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('projects.show', $project) }}" 
                                       class="btn btn-sm btn-outline-primary" 
                                       data-bs-toggle="tooltip" 
                                       title="عرض التفاصيل">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if($project->files->count() > 0)
                                        <a href="{{ route('projects.files.index', $project) }}" 
                                           class="btn btn-sm btn-outline-info" 
                                           data-bs-toggle="tooltip" 
                                           title="تحميل الملفات">
                                            <i class="fas fa-download"></i>
                                        </a>
                                    @endif
                                    @if(auth()->user()->isAdmin())
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-danger" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#deleteArchiveModal{{ $project->id }}"
                                                data-bs-toggle="tooltip" 
                                                title="حذف من الأرشيف">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    @endif
                                </div>

                                <!-- مودال تأكيد الحذف للمشاريع المؤرشفة -->
                                @if(auth()->user()->isAdmin())
                                    <div class="modal fade" id="deleteArchiveModal{{ $project->id }}" tabindex="-1">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                                <div class="modal-header bg-danger text-white">
                                                    <h5 class="modal-title">
                                                        <i class="fas fa-exclamation-triangle me-2"></i>حذف المشروع
                                                    </h5>
                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p>هل أنت متأكد من حذف المشروع <strong>{{ $project->title_ar }}</strong> من الأرشيف؟</p>
                                                    <p class="text-danger mb-0"><small>هذا الإجراء لا يمكن التراجع عنه، وسيتم حذف جميع بيانات المشروع نهائياً.</small></p>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                                                    <form action="{{ route('projects.destroy', $project) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger">حذف نهائي</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </td>
                        </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5">
                                    <i class="fas fa-archive fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">لا توجد مشاريع مكتملة بعد</h5>
                                    <p class="text-muted small">سيتم عرض المشاريع التي تمت مناقشتها واعتمادها هنا</p>
                                    <a href="{{ route('projects.index') }}" class="btn btn-outline-success mt-2">
                                        <i class="fas fa-list me-2"></i>استعراض المشاريع الحالية
                                    </a>
                                 </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- تذييل مع إجمالي النتائج وروابط الصفحات -->
            @if($projects->hasPages() || $projects->total() > 0)
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-center p-3 bg-light border-top gap-2">
                    <div class="text-muted small">
                        <i class="fas fa-chart-simple me-1"></i>
                        عرض {{ $projects->firstItem() ?? 0 }} - {{ $projects->lastItem() ?? 0 }} من إجمالي {{ $projects->total() }} مشروع مكتمل
                    </div>
                    <div>
                        {{ $projects->appends(request()->query())->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // تفعيل التلميحات (tooltips)
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });

    // إعادة تعيين الفلترة عند الضغط على زر "إعادة تعيين" (يتم التعامل معه عبر الرابط)
    // دعم إضافي: تفعيل خاصية enter لتقديم النموذج بشكل طبيعي
</script>
@endpush