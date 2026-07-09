@extends('layouts.app')

@if(isset($project))
@php
    // دالة مساعدة لتحويل البايت إلى وحدة مقروءة
    function formatBytes($bytes, $precision = 2) {
        if ($bytes <= 0) return '0 B';
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $index = floor(log($bytes, 1024));
        $index = min($index, count($units) - 1);
        return round($bytes / pow(1024, $index), $precision) . ' ' . $units[$index];
    }

    $totalFiles = $files->count();
    $totalSize = $files->sum('size');
    $formattedTotalSize = formatBytes($totalSize);
    $approvedFiles = $files->where('is_approved', true)->count();
    $pendingFiles = $files->where('is_approved', false)->count();
    $fileCategories = $files->groupBy('file_category');
@endphp

<div class="card mt-4 border-0 shadow-sm">
    <div class="card-header bg-white py-3 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
        <div>
            <h5 class="mb-0 fw-bold">
                <i class="fas fa-paperclip text-primary me-2"></i> ملفات المشروع
                <span class="badge bg-secondary ms-2">{{ $totalFiles }}</span>
            </h5>
        </div>
        <div>
            @if(in_array($project->status, ['draft', 'submitted', 'under_review', 'approved']) &&
                ( (auth()->user()->isStudent() && $project->students->contains(auth()->id())) ||
                  (auth()->user()->isSupervisor() && $project->supervisor_id == auth()->id()) ||
                  auth()->user()->isAdmin() ))
                <button class="btn btn-sm btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#uploadFileModal">
                    <i class="fas fa-cloud-upload-alt me-1"></i> رفع ملف جديد
                </button>
            @endif
            <button class="btn btn-sm btn-outline-secondary shadow-sm" onclick="location.reload();" title="تحديث القائمة">
                <i class="fas fa-sync-alt"></i>
            </button>
        </div>
    </div>

    <!-- بطاقات إحصائيات الملفات (محسّنة) -->
    <div class="row g-3 p-3 pt-0">
        <div class="col-md-4 col-sm-6">
            <div class="card border-0 bg-light">
                <div class="card-body py-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted">إجمالي الملفات</small>
                            <h5 class="mb-0">{{ $totalFiles }}</h5>
                        </div>
                        <i class="fas fa-file-alt fa-2x text-primary opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-sm-6">
            <div class="card border-0 bg-light">
                <div class="card-body py-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted">الحجم الإجمالي</small>
                            <h5 class="mb-0">{{ $formattedTotalSize }}</h5>
                        </div>
                        <i class="fas fa-database fa-2x text-success opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-sm-6">
            <div class="card border-0 bg-light">
                <div class="card-body py-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted">قيد المراجعة</small>
                            <h5 class="mb-0">{{ $pendingFiles }}</h5>
                        </div>
                        <i class="fas fa-clock fa-2x text-warning opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- جدول الملفات المحسن -->
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0" id="filesTable">
                <thead class="table-light">
                    <tr class="text-nowrap">
                        <th style="width: 30%">اسم الملف</th>
                        <th style="width: 12%">النوع</th>
                        <th style="width: 8%">الحجم</th>
                        <th style="width: 8%">الإصدار</th>
                        <th style="width: 12%">تاريخ الرفع</th>
                        <th style="width: 12%">الحالة</th>
                        <th style="width: 18%">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($files as $file)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                @php
                                    $icon = 'fa-file-alt';
                                    if(str_contains($file->mime_type, 'pdf')) $icon = 'fa-file-pdf';
                                    elseif(str_contains($file->mime_type, 'image')) $icon = 'fa-file-image';
                                    elseif(str_contains($file->mime_type, 'word')) $icon = 'fa-file-word';
                                    elseif(str_contains($file->mime_type, 'excel')) $icon = 'fa-file-excel';
                                    elseif(str_contains($file->mime_type, 'zip')) $icon = 'fa-file-archive';
                                    elseif($file->file_category == 'source_code') $icon = 'fa-code';
                                @endphp
                                <i class="fas {{ $icon }} text-secondary fa-fw"></i>
                                <span class="fw-semibold">{{ $file->file_name }}</span>
                            </div>
                            @if($file->description)
                                <small class="text-muted d-block mt-1">{{ Str::limit($file->description, 40) }}</small>
                            @endif
                        </td>
                        <td>
                            @php
                                $categoryLabels = [
                                    'proposal' => 'خطة المشروع',
                                    'report' => 'تقرير نهائي',
                                    'presentation' => 'عرض تقديمي',
                                    'source_code' => 'كود مصدري',
                                    'poster' => 'ملصق',
                                    'other' => 'أخرى'
                                ];
                                $categoryClass = [
                                    'proposal' => 'primary',
                                    'report' => 'success',
                                    'presentation' => 'info',
                                    'source_code' => 'dark',
                                    'poster' => 'warning',
                                    'other' => 'secondary'
                                ];
                            @endphp
                            <span class="badge bg-{{ $categoryClass[$file->file_category] ?? 'secondary' }} bg-opacity-10 text-{{ $categoryClass[$file->file_category] ?? 'secondary' }} px-2 py-1">
                                {{ $categoryLabels[$file->file_category] ?? $file->category_name }}
                            </span>
                        </td>
                        <td>
                            {{ formatBytes($file->size) }}  <!-- تم التعديل هنا -->
                        </td>
                        <td><span class="badge bg-light text-dark border">v{{ $file->version }}</span></td>
                        <td class="text-nowrap">
                            <i class="far fa-calendar-alt text-muted me-1"></i>
                            {{ $file->created_at->format('Y-m-d') }}
                        </td>
                        <td>
                            @if($file->is_approved)
                                <span class="badge bg-success bg-opacity-15 text-success px-3 py-2 rounded-pill">
                                    <i class="fas fa-check-circle me-1"></i>موافق عليه
                                </span>
                            @else
                                <span class="badge bg-warning bg-opacity-15 text-warning px-3 py-2 rounded-pill">
                                    <i class="fas fa-hourglass-half me-1"></i>قيد المراجعة
                                </span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">
                                <a href="{{ route('projects.files.download', $file) }}" 
                                   class="btn btn-outline-primary" 
                                   data-bs-toggle="tooltip" 
                                   title="تحميل الملف">
                                    <i class="fas fa-download"></i>
                                </a>
                                @if((auth()->user()->isSupervisor() && $project->supervisor_id == auth()->id() && !$file->is_approved) || auth()->user()->isAdmin())
                                    <form action="{{ route('projects.files.approve', $file) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" 
                                                class="btn btn-outline-success" 
                                                data-bs-toggle="tooltip" 
                                                title="الموافقة على الملف"
                                                onclick="return confirm('هل تريد الموافقة على هذا الملف؟')">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </form>
                                @endif
                                @if((auth()->user()->isStudent() && $file->uploaded_by == auth()->id()) || auth()->user()->isSupervisor() || auth()->user()->isAdmin())
                                    <button type="button" 
                                            class="btn btn-outline-danger" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#deleteFileModal{{ $file->id }}"
                                            data-bs-toggle="tooltip" 
                                            title="حذف الملف">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                @endif
                            </div>

                            <!-- مودال تأكيد حذف الملف -->
                            @if((auth()->user()->isStudent() && $file->uploaded_by == auth()->id()) || auth()->user()->isSupervisor() || auth()->user()->isAdmin())
                            <div class="modal fade" id="deleteFileModal{{ $file->id }}" tabindex="-1">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header bg-danger text-white">
                                            <h5 class="modal-title">
                                                <i class="fas fa-trash-alt me-2"></i>حذف الملف
                                            </h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p>هل أنت متأكد من حذف الملف <strong>{{ $file->file_name }}</strong>؟</p>
                                            <p class="text-danger mb-0"><small>هذا الإجراء لا يمكن التراجع عنه.</small></p>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                                            <form action="{{ route('projects.files.destroy', $file) }}" method="POST" class="d-inline">
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
                            <td colspan="7" class="text-center py-5">
                                <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">لا توجد ملفات مرفوعة</h5>
                                <p class="text-muted small">قم برفع الملفات الخاصة بالمشروع مثل خطة المشروع، التقرير، العرض التقديمي</p>
                                @if(in_array($project->status, ['draft', 'submitted', 'under_review', 'approved']) &&
                                    ( (auth()->user()->isStudent() && $project->students->contains(auth()->id())) ||
                                      (auth()->user()->isSupervisor() && $project->supervisor_id == auth()->id()) ||
                                      auth()->user()->isAdmin() ))
                                    <button class="btn btn-primary btn-sm mt-2" data-bs-toggle="modal" data-bs-target="#uploadFileModal">
                                        <i class="fas fa-cloud-upload-alt me-1"></i> رفع أول ملف
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- مودال رفع ملف محسن -->
<div class="modal fade" id="uploadFileModal" tabindex="-1" aria-labelledby="uploadFileModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <form action="{{ route('projects.files.store', $project) }}" method="POST" enctype="multipart/form-data" id="uploadFileForm">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="uploadFileModalLabel">
                        <i class="fas fa-cloud-upload-alt me-2"></i> رفع ملف جديد
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">الملف <span class="text-danger">*</span></label>
                            <input type="file" name="file" id="uploadFileInput" class="form-control" required accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.zip,.rar,.jpg,.png,.txt,.py,.java,.c,.cpp,.js,.html,.css">
                            <small class="text-muted">الحد الأقصى 10MB. الأنواع المسموحة: PDF, Word, Excel, PowerPoint, صور, أرشيف، نصوص، كود.</small>
                            <div id="filePreview" class="mt-2 small text-muted"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">نوع الملف <span class="text-danger">*</span></label>
                            <select name="file_category" class="form-select" required>
                                <option value="proposal">📄 خطة المشروع</option>
                                <option value="report">📑 التقرير النهائي</option>
                                <option value="presentation">📊 عرض تقديمي</option>
                                <option value="source_code">💻 الكود المصدري</option>
                                <option value="poster">🖼️ ملصق</option>
                                <option value="other">📁 أخرى</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">رقم الإصدار</label>
                            <input type="number" name="version" class="form-control" value="1" min="1">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">الوصف (اختياري)</label>
                            <textarea name="description" class="form-control" rows="2" placeholder="وصف مختصر للملف ومحتواه..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary" id="submitUploadBtn">
                        <i class="fas fa-cloud-upload-alt me-1"></i> رفع
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // تفعيل التلميحات (Tooltips)
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    tooltipTriggerList.forEach(function (tooltipTriggerEl) {
        new bootstrap.Tooltip(tooltipTriggerEl)
    });

    // معاينة الملف قبل الرفع
    document.getElementById('uploadFileInput')?.addEventListener('change', function(e) {
        const file = e.target.files[0];
        const preview = document.getElementById('filePreview');
        if (file) {
            const sizeFormatted = formatBytesClient(file.size);
            preview.innerHTML = `<i class="fas fa-check-circle text-success"></i> الملف المحدد: ${file.name} (${sizeFormatted})`;
            if (file.size > 10 * 1024 * 1024) {
                preview.innerHTML += `<br><span class="text-danger">⚠️ الحجم يتجاوز الحد الأقصى 10MB</span>`;
                document.getElementById('submitUploadBtn').disabled = true;
            } else {
                document.getElementById('submitUploadBtn').disabled = false;
            }
        } else {
            preview.innerHTML = '';
        }
    });

    // دالة مساعدة للعميل لعرض الحجم
    function formatBytesClient(bytes, decimals = 2) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const dm = decimals < 0 ? 0 : decimals;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
    }

    // إظهار رسائل الجلسة
    @if(session('success'))
        toastr.success("{{ session('success') }}");
    @elseif(session('error'))
        toastr.error("{{ session('error') }}");
    @endif
</script>
@endpush

@endif