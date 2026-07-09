@extends('layouts.app')

@section('title', $project->title_ar)

@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- العمود الرئيسي -->
        <div class="col-lg-8">
            <!-- بطاقة المشروع -->
            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h4 class="mb-0 fw-bold">{{ $project->title_ar }}</h4>
                        <small class="text-muted">آخر تحديث: {{ $project->updated_at->diffForHumans() }}</small>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        @if($canEdit)
                            <a href="{{ route('projects.edit', $project) }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-edit"></i> تعديل
                            </a>
                        @endif
                        @if(auth()->user()->isStudent() && $project->status == 'draft' && $project->students->contains(auth()->id()))
                            <form action="{{ route('projects.submit', $project) }}" method="POST" class="d-inline">
                                @csrf
                                <button class="btn btn-sm btn-success" onclick="return confirm('هل أنت متأكد من تقديم المشروع للمراجعة؟')">
                                    <i class="fas fa-paper-plane"></i> تقديم للمراجعة
                                </button>
                            </form>
                        @endif
                        @if(auth()->user()->isSupervisor() && $project->supervisor_id == auth()->id() && $project->status == 'submitted')
                            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#reviewModal">
                                <i class="fas fa-check-double"></i> اتخاذ قرار
                            </button>
                        @endif
                        @if(auth()->user()->isSupervisor() && $project->supervisor_id == auth()->id() && $project->status == 'approved')
                            <a href="{{ route('evaluations.create', $project) }}" class="btn btn-sm btn-success">
                                <i class="fas fa-star"></i> تقييم المشروع
                            </a>
                        @endif
                    </div>
                </div>

                <div class="card-body">
                    <!-- شريط تقدم الحالة -->
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="fw-semibold">حالة المشروع</span>
                            <span class="badge bg-{{ $project->status == 'completed' ? 'success' : ($project->status == 'approved' ? 'info' : ($project->status == 'rejected' ? 'danger' : 'warning')) }} px-3 py-2">
                                {{ $project->status_name }}
                            </span>
                        </div>
                        <div class="progress" style="height: 10px;">
                            @php
                                $progressMap = [
                                    'draft' => 10,
                                    'submitted' => 30,
                                    'under_review' => 50,
                                    'approved' => 75,
                                    'completed' => 100,
                                    'rejected' => 100
                                ];
                                $progress = $progressMap[$project->status] ?? 0;
                            @endphp
                            <div class="progress-bar bg-{{ $project->status == 'completed' ? 'success' : ($project->status == 'rejected' ? 'danger' : 'primary') }}"
                                 role="progressbar"
                                 style="width: {{ $progress }}%"
                                 aria-valuenow="{{ $progress }}"
                                 aria-valuemin="0"
                                 aria-valuemax="100">
                            </div>
                        </div>
                        <div class="d-flex justify-content-between mt-1 small text-muted">
                            <span>📝 مسودة</span>
                            <span>📬 تقديم</span>
                            <span>🔍 مراجعة</span>
                            <span>✅ قبول</span>
                            <span>🏁 اكتمال</span>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12 mb-3">
                            <strong><i class="fas fa-heading"></i> العنوان:</strong> {{ $project->title_ar }}
                        </div>
                        @if($project->title_en)
                            <div class="col-12 mb-3">
                                <strong><i class="fas fa-language"></i> Title:</strong> {{ $project->title_en }}
                            </div>
                        @endif
                        <div class="col-12 mb-3">
                            <strong><i class="fas fa-align-left"></i> الملخص:</strong>
                            <p class="mt-1">{{ $project->abstract_ar }}</p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <strong><i class="fas fa-chalkboard-user"></i> المشرف:</strong>
                            <p>{{ $project->supervisor->name ?? '-' }}</p>
                        </div>
                        <div class="col-md-4 mb-3">
                            <strong><i class="fas fa-graduation-cap"></i> التخصص:</strong>
                            <p>{{ $project->specialization->name_ar ?? '-' }}</p>
                        </div>
                        <div class="col-md-4 mb-3">
                            <strong><i class="fas fa-chart-line"></i> نسبة النجاح:</strong>
                            <p>
                                @if($project->success_percentage)
                                    <div class="d-flex align-items-center gap-2">
                                        <span>{{ $project->success_percentage }}%</span>
                                        <div class="progress flex-grow-1" style="height: 5px;">
                                            <div class="progress-bar bg-success" style="width: {{ $project->success_percentage }}%"></div>
                                        </div>
                                    </div>
                                @else
                                    -
                                @endif
                            </p>
                        </div>
                    </div>

                    @if($project->feedback)
                        <div class="alert alert-info">
                            <i class="fas fa-comment"></i> <strong>ملاحظات المشرف:</strong> {{ $project->feedback }}
                        </div>
                    @endif

                    @if($project->keywords)
                        <div class="mb-3">
                            <strong><i class="fas fa-tags"></i> الكلمات المفتاحية:</strong> 
                            @foreach(explode(',', $project->keywords) as $keyword)
                                <span class="badge bg-secondary me-1">{{ trim($keyword) }}</span>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <!-- أعضاء المشروع -->
            @include('projects.members.index', ['members' => $project->members, 'project' => $project])

            <!-- ملفات المشروع (محسّنة) -->
            @include('projects.files.index', ['files' => $project->files, 'project' => $project])

        </div>

        <!-- العمود الجانبي -->
        <div class="col-lg-4">
            <!-- معلومات إضافية -->
            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-header bg-white">
                    <i class="fas fa-info-circle text-primary"></i> معلومات إضافية
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li class="mb-2"><i class="fas fa-calendar-alt text-secondary me-2"></i> <strong>تاريخ التسليم:</strong> {{ $project->submission_date ? \Carbon\Carbon::parse($project->submission_date)->format('Y-m-d') : '—' }}</li>
                        <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> <strong>تاريخ القبول:</strong> {{ $project->approval_date ? \Carbon\Carbon::parse($project->approval_date)->format('Y-m-d') : '—' }}</li>
                        <li class="mb-2"><i class="fas fa-users text-info me-2"></i> <strong>عدد الأعضاء:</strong> {{ $project->students->count() }}</li>
                        <li class="mb-2"><i class="fas fa-file-alt text-warning me-2"></i> <strong>عدد الملفات:</strong> {{ $project->files->count() }}</li>
                        <li class="mb-2"><i class="fas fa-database text-dark me-2"></i> <strong>إجمالي حجم الملفات:</strong> 
                            @php
                                $totalSize = $project->files->sum('size');
                                echo $totalSize ? formatBytes($totalSize) : '0 KB';
                            @endphp
                        </li>
                    </ul>
                </div>
            </div>

            <!-- معلومات المناقشة - مع مودال بدلاً من الرابط المباشر -->
            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-header bg-white">
                    <i class="fas fa-gavel text-primary"></i> معلومات المناقشة
                </div>
                <div class="card-body">
                    @if($project->defense_date)
                        <p><i class="fas fa-calendar-check text-success me-2"></i> <strong>التاريخ:</strong> {{ \Carbon\Carbon::parse($project->defense_date)->format('Y-m-d H:i') }}</p>
                        @if($project->defense_location)
                            <p><i class="fas fa-location-dot text-danger me-2"></i> <strong>المكان:</strong> {{ $project->defense_location }}</p>
                        @endif
                        @if($project->defense_notes)
                            <p><i class="fas fa-pen me-2"></i> <strong>ملاحظات:</strong> {{ $project->defense_notes }}</p>
                        @endif
                    @else
                        <p class="text-muted"><i class="fas fa-clock"></i> لم يتم تحديد موعد المناقشة بعد.</p>
                    @endif

                    @if(auth()->user()->isSupervisor() && $project->supervisor_id == auth()->id() && $project->status == 'approved')
                        <button class="btn btn-sm btn-primary mt-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#setDefenseModal">
                            <i class="fas fa-calendar-plus"></i> {{ $project->defense_date ? 'تعديل الموعد' : 'تحديد موعد' }}
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- مودال مراجعة المشروع (للمشرف) -->
@if(auth()->user()->isSupervisor() && $project->supervisor_id == auth()->id() && $project->status == 'submitted')
<div class="modal fade" id="reviewModal" tabindex="-1" aria-labelledby="reviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('projects.review', $project) }}" method="POST">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="reviewModalLabel">
                        <i class="fas fa-clipboard-list"></i> مراجعة المشروع
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">القرار</label>
                        <select name="status" class="form-select" required>
                            <option value="approved">✔ قبول المشروع</option>
                            <option value="rejected">✖ رفض المشروع</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">ملاحظات (لن تظهر للطالب في حالة القبول، إلزامية للرفض)</label>
                        <textarea name="feedback" class="form-control" rows="4" placeholder="اكتب ملاحظاتك هنا..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary">تأكيد القرار</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<!-- ============================================= -->
<!-- مودال تحديد موعد المناقشة (جديد) -->
<!-- ============================================= -->
@if(auth()->user()->isSupervisor() && $project->supervisor_id == auth()->id() && $project->status == 'approved')
<div class="modal fade" id="setDefenseModal" tabindex="-1" aria-labelledby="setDefenseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('projects.set_defense', $project) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="setDefenseModalLabel">
                        <i class="fas fa-calendar-plus me-2"></i> {{ $project->defense_date ? 'تعديل موعد المناقشة' : 'تحديد موعد المناقشة' }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">تاريخ ووقت المناقشة <span class="text-danger">*</span></label>
                        <input type="datetime-local" name="defense_date" class="form-control" 
                               value="{{ $project->defense_date ? \Carbon\Carbon::parse($project->defense_date)->format('Y-m-d\TH:i') : '' }}" 
                               required>
                        <small class="text-muted">اختر التاريخ والوقت المناسبين للمناقشة.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">المكان</label>
                        <input type="text" name="defense_location" class="form-control" 
                               placeholder="مثال: قاعة رقم 3، مبنى الكلية" 
                               value="{{ $project->defense_location }}">
                        <small class="text-muted">اذكر مكان إقامة المناقشة (اختياري).</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">ملاحظات إضافية</label>
                        <textarea name="defense_notes" class="form-control" rows="2" 
                                  placeholder="أي ملاحظات حول المناقشة مثل متطلبات خاصة...">{{ $project->defense_notes }}</textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> حفظ الموعد
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@endsection

@push('styles')
<style>
    /* تحسينات إضافية للجدول */
    .table-files th {
        background-color: #f8fafc;
        font-weight: 600;
    }
    .file-size {
        font-family: monospace;
        direction: ltr;
        display: inline-block;
    }
    .status-badge {
        font-size: 0.8rem;
        padding: 0.35rem 0.75rem;
    }
</style>
@endpush