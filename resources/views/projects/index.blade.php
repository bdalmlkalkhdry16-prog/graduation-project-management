@extends('layouts.app')

@section('title', 'المشاريع')

@section('content')
    <div class="container-fluid">
        <!-- رأس الصفحة مع إحصائيات سريعة -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
            <div>
                <h2 class="fw-bold mb-1">
                    <i class="fas fa-project-diagram text-primary me-2"></i>المشاريع
                </h2>
                <p class="text-muted mb-0">إدارة ومتابعة المشاريع العلمية</p>
            </div>
            <div class="d-flex gap-2">
                @if(auth()->user()->isAdmin() || auth()->user()->isSupervisor())
                    <a href="{{ route('projects.create') }}" class="btn btn-primary shadow-sm">
                        <i class="fas fa-plus-circle me-2"></i>مشروع جديد
                    </a>
                @elseif(auth()->user()->isStudent() && auth()->user()->projects()->count() == 0)
                    <a href="{{ route('projects.create') }}" class="btn btn-primary shadow-sm">
                        <i class="fas fa-plus-circle me-2"></i>تقديم مشروع جديد
                    </a>
                @endif
            </div>
        </div>

        <!-- بطاقات الإحصائيات السريعة -->
        <div class="row g-3 mb-4">
            <div class="col-md-3 col-sm-6">
                <div class="card border-0 shadow-sm bg-primary bg-opacity-10 h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-1">إجمالي المشاريع</h6>
                                <h3 class="fw-bold mb-0">{{ $projects->total() }}</h3>
                            </div>
                            <i class="fas fa-chalkboard fa-2x text-primary opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="card border-0 shadow-sm bg-success bg-opacity-10 h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-1">المشاريع المنشورة</h6>
                                <h3 class="fw-bold mb-0">{{ $projects->where('status', '!=', 'draft')->count() }}</h3>
                            </div>
                            <i class="fas fa-check-circle fa-2x text-success opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="card border-0 shadow-sm bg-warning bg-opacity-10 h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-1">قيد المراجعة</h6>
                                <h3 class="fw-bold mb-0">{{ $projects->where('status', 'under_review')->count() }}</h3>
                            </div>
                            <i class="fas fa-clock fa-2x text-warning opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="card border-0 shadow-sm bg-info bg-opacity-10 h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-1">المقبولة والمكتملة</h6>
                                <h3 class="fw-bold mb-0">{{ $projects->whereIn('status', ['approved', 'completed'])->count() }}</h3>
                            </div>
                            <i class="fas fa-trophy fa-2x text-info opacity-50"></i>
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
                        <i class="fas fa-filter me-2 text-primary"></i>فلترة المشاريع
                    </h5>
                    <a href="{{ route('projects.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-redo-alt me-1"></i>إعادة تعيين
                    </a>
                </div>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('projects.index') }}" id="filterForm">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-tag me-1"></i>الحالة
                            </label>
                            <select name="status" class="form-select">
                                <option value="">جميع الحالات</option>
                                <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>مسودة</option>
                                <option value="submitted" {{ request('status') == 'submitted' ? 'selected' : '' }}>تم التقديم</option>
                                <option value="under_review" {{ request('status') == 'under_review' ? 'selected' : '' }}>قيد المراجعة</option>
                                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>مقبول</option>
                                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>مرفوض</option>
                                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>مكتمل</option>
                            </select>
                        </div>
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
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-search me-1"></i>بحث سريع
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="fas fa-search text-muted"></i>
                                </span>
                                <input type="text" name="search" class="form-control border-start-0" 
                                       placeholder="عنوان المشروع، اسم المشرف، أو رقم المشروع..." 
                                       value="{{ request('search') }}">
                            </div>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100 shadow-sm">
                                <i class="fas fa-search me-2"></i>بحث
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- جدول المشاريع المحسن مع دعم الشاشات الصغيرة -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped align-middle mb-0">
                        <thead class="table-light">
                            <tr class="text-nowrap">
                                <th width="5%">#</th>
                                <th width="25%">عنوان المشروع</th>
                                <th width="15%">المشرف</th>
                                <th width="15%">التخصص</th>
                                <th width="8%">السنة</th>
                                <th width="12%">الحالة</th>
                                <th width="10%">نسبة النجاح</th>
                                <th width="10%">الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($projects as $project)
                                <tr>
                                    <td class="fw-bold">{{ $loop->iteration }}</td>
                                    <td>
                                        <a href="{{ route('projects.show', $project) }}" class="text-decoration-none fw-semibold">
                                            {{ Str::limit($project->title_ar, 50) }}
                                            @if($project->students_count)
                                                <span class="badge bg-secondary ms-1" data-bs-toggle="tooltip" title="عدد الطلاب">
                                                    <i class="fas fa-users"></i> {{ $project->students_count }}
                                                </span>
                                            @endif
                                        </a>
                                    </td>
                                    <td>
                                        @if($project->supervisor)
                                            <div class="d-flex align-items-center gap-2">
                                                <i class="fas fa-chalkboard-user text-primary"></i>
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
                                        @php
                                            $statusConfig = [
                                                'draft' => ['class' => 'secondary', 'icon' => 'fa-pen', 'text' => 'مسودة'],
                                                'submitted' => ['class' => 'warning', 'icon' => 'fa-paper-plane', 'text' => 'تم التقديم'],
                                                'under_review' => ['class' => 'primary', 'icon' => 'fa-spinner fa-pulse', 'text' => 'قيد المراجعة'],
                                                'approved' => ['class' => 'info', 'icon' => 'fa-check-circle', 'text' => 'مقبول'],
                                                'rejected' => ['class' => 'danger', 'icon' => 'fa-times-circle', 'text' => 'مرفوض'],
                                                'completed' => ['class' => 'success', 'icon' => 'fa-star', 'text' => 'مكتمل']
                                            ];
                                            $status = $statusConfig[$project->status] ?? ['class' => 'secondary', 'icon' => 'fa-question', 'text' => $project->status_name];
                                        @endphp
                                        <span class="badge bg-{{ $status['class'] }} bg-opacity-10 text-{{ $status['class'] }} border border-{{ $status['class'] }} border-opacity-25 px-3 py-2">
                                            <i class="fas {{ $status['icon'] }} me-1"></i>
                                            {{ $status['text'] }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($project->success_percentage)
                                            <div class="d-flex flex-column gap-1" style="min-width: 100px;">
                                                <div class="d-flex justify-content-between small">
                                                    <span>الإنجاز</span>
                                                    <span class="fw-bold">{{ $project->success_percentage }}%</span>
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
                                            <span class="text-muted">-</span>
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
                                            @if(auth()->user()->isAdmin() || 
                                                (auth()->user()->isSupervisor() && $project->supervisor_id == auth()->id()) || 
                                                (auth()->user()->isStudent() && $project->students->contains(auth()->id())))
                                                <a href="{{ route('projects.edit', $project) }}" 
                                                   class="btn btn-sm btn-outline-secondary"
                                                   data-bs-toggle="tooltip" 
                                                   title="تعديل">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            @endif
                                            @if(auth()->user()->isAdmin())
                                                <button type="button" 
                                                        class="btn btn-sm btn-outline-danger" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#deleteModal{{ $project->id }}"
                                                        data-bs-toggle="tooltip" 
                                                        title="حذف">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            @endif
                                        </div>

                                        <!-- مودال تأكيد الحذف -->
                                        @if(auth()->user()->isAdmin())
                                            <div class="modal fade" id="deleteModal{{ $project->id }}" tabindex="-1">
                                                <div class="modal-dialog modal-dialog-centered">
                                                    <div class="modal-content">
                                                        <div class="modal-header bg-danger text-white">
                                                            <h5 class="modal-title">
                                                                <i class="fas fa-exclamation-triangle me-2"></i>تأكيد الحذف
                                                            </h5>
                                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <p>هل أنت متأكد من حذف المشروع <strong>{{ $project->title_ar }}</strong>؟</p>
                                                            <p class="text-danger mb-0"><small>هذا الإجراء لا يمكن التراجع عنه.</small></p>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                                                            <form action="{{ route('projects.destroy', $project) }}" method="POST" class="d-inline">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-danger">حذف</button>
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
                                    <td colspan="8" class="text-center py-5">
                                        <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                                        <h5 class="text-muted">لا توجد مشاريع</h5>
                                        <p class="text-muted small">لم يتم العثور على أي مشاريع تطابق معايير البحث</p>
                                        @if(auth()->user()->isStudent() && auth()->user()->projects()->count() == 0)
                                            <a href="{{ route('projects.create') }}" class="btn btn-primary mt-2">
                                                <i class="fas fa-plus me-2"></i>إنشاء أول مشروع
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- تذييل الجدول مع روابط الصفحات -->
                @if($projects->hasPages())
                    <div class="d-flex justify-content-between align-items-center p-3 bg-light border-top">
                        <div class="text-muted small">
                            عرض {{ $projects->firstItem() ?? 0 }} - {{ $projects->lastItem() ?? 0 }} من إجمالي {{ $projects->total() }} مشروع
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
    // تفعيل الـ Tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });

    // تحسين تجربة النموذج: إعادة تعيين الفلترة عند الضغط على زر المسح
    document.querySelectorAll('.reset-filter').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            window.location.href = '{{ route("projects.index") }}';
        });
    });
</script>
@endpush