@extends('layouts.app')

@section('title', $idea->title_ar)

@section('content')
    <div class="container">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0">{{ $idea->title_ar }}</h4>
                @if(auth()->user()->isSupervisor() || auth()->user()->isAdmin())
                    @if($idea->status === 'pending')
                        <div>
                            <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#reviewModal" data-action="approve">موافقة</button>
                            <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#reviewModal" data-action="reject">رفض</button>
                        </div>
                    @endif
                @endif
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>العنوان:</strong> {{ $idea->title_ar }}
                    </div>
                    <div class="col-md-6">
                        <strong>التخصص:</strong> {{ $idea->specialization->name_ar ?? '-' }}
                    </div>
                </div>
                @if($idea->title_en)
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <strong>Title (English):</strong> {{ $idea->title_en }}
                        </div>
                    </div>
                @endif
                <div class="mb-3">
                    <strong>الملخص:</strong><br>
                    {{ $idea->abstract_ar ?? 'لا يوجد' }}
                </div>
                @if($idea->keywords)
                    <div class="mb-3">
                        <strong>الكلمات المفتاحية:</strong> {{ $idea->keywords }}
                    </div>
                @endif
                <div class="row">
                    <div class="col-md-6">
                        <strong>مقدم الفكرة:</strong> {{ $idea->student->name }}
                    </div>
                    <div class="col-md-6">
                        <strong>تاريخ التقديم:</strong> {{ $idea->submitted_at->format('Y-m-d H:i') }}
                    </div>
                </div>
                @if($idea->review_notes)
                    <div class="alert alert-info mt-3">
                        <strong>ملاحظات المختص:</strong><br>
                        {{ $idea->review_notes }}
                    </div>
                @endif
                @if($idea->project)
                    <div class="mt-3">
                        <a href="{{ route('projects.show', $idea->project) }}" class="btn btn-outline-primary">عرض المشروع المرتبط</a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Modal للمراجعة (يظهر فقط للمشرفين والمدير) -->
    @if(auth()->user()->isSupervisor() || auth()->user()->isAdmin())
        <div class="modal fade" id="reviewModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="{{ route('projects.idea.review', $idea) }}" method="POST">
                        @csrf
                        <input type="hidden" name="action" id="action">
                        <div class="modal-header">
                            <h5 class="modal-title">مراجعة الفكرة</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">ملاحظات (ستظهر للطالب)</label>
                                <textarea name="review_notes" class="form-control" rows="3"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                            <button type="submit" class="btn btn-primary">تأكيد</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <script>
            document.querySelectorAll('[data-bs-toggle="modal"]').forEach(btn => {
                btn.addEventListener('click', function() {
                    const action = this.getAttribute('data-action');
                    document.getElementById('action').value = action;
                });
            });
        </script>
    @endif
@endsection
