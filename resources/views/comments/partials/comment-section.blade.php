<div class="card mt-4">
    <div class="card-header">
        <h5 class="mb-0">التعليقات</h5>
    </div>
    <div class="card-body">
        <!-- نموذج إضافة تعليق -->
        <form action="{{ route('comments.store', $project) }}" method="POST" class="mb-4">
            @csrf
            <div class="mb-3">
                <textarea name="content" class="form-control" rows="3" placeholder="اكتب تعليقك..."></textarea>
            </div>
            <button type="submit" class="btn btn-primary">إرسال تعليق</button>
        </form>

        <!-- قائمة التعليقات -->
        <div class="comments-list">
            @foreach($comments->where('parent_id', null) as $comment)
                <div class="comment mb-3 p-3 border rounded">
                    <div class="d-flex justify-content-between">
                        <strong>{{ $comment->user->name }}</strong>
                        <small class="text-muted">{{ $comment->created_at->diffForHumans() }}</small>
                    </div>
                    <p class="mt-2">{{ $comment->content }}</p>
                    @if($comment->user_id == auth()->id() || auth()->user()->isAdmin())
                        <div class="mt-2">
                            <button class="btn btn-sm btn-link text-primary reply-btn" data-comment-id="{{ $comment->id }}">رد</button>
                            <button class="btn btn-sm btn-link text-secondary edit-btn" data-comment-id="{{ $comment->id }}" data-content="{{ $comment->content }}">تعديل</button>
                            <form action="{{ route('comments.destroy', $comment) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-link text-danger" onclick="return confirm('هل أنت متأكد من حذف التعليق؟')">حذف</button>
                            </form>
                        </div>
                    @endif

                    <!-- نموذج الرد (مخفي) -->
                    <div class="reply-form mt-3" id="reply-form-{{ $comment->id }}" style="display:none;">
                        <form action="{{ route('comments.store', $project) }}" method="POST">
                            @csrf
                            <input type="hidden" name="parent_id" value="{{ $comment->id }}">
                            <textarea name="content" class="form-control mb-2" rows="2" placeholder="اكتب ردك..."></textarea>
                            <button type="submit" class="btn btn-sm btn-primary">إرسال الرد</button>
                            <button type="button" class="btn btn-sm btn-secondary cancel-reply" data-comment-id="{{ $comment->id }}">إلغاء</button>
                        </form>
                    </div>

                    <!-- الردود -->
                    @if($comment->replies->count())
                        <div class="replies ms-4 mt-3">
                            @foreach($comment->replies as $reply)
                                <div class="comment-reply mb-2 p-2 bg-light rounded">
                                    <div class="d-flex justify-content-between">
                                        <strong>{{ $reply->user->name }}</strong>
                                        <small class="text-muted">{{ $reply->created_at->diffForHumans() }}</small>
                                    </div>
                                    <p class="mt-1">{{ $reply->content }}</p>
                                    @if($reply->user_id == auth()->id() || auth()->user()->isAdmin())
                                        <div>
                                            <button class="btn btn-sm btn-link text-secondary edit-reply-btn" data-comment-id="{{ $reply->id }}" data-content="{{ $reply->content }}">تعديل</button>
                                            <form action="{{ route('comments.destroy', $reply) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-link text-danger" onclick="return confirm('حذف الرد؟')">حذف</button>
                                            </form>
                                        </div>
                                    @endif
                                    <!-- نموذج تعديل الرد -->
                                    <div class="edit-reply-form mt-2" id="edit-reply-form-{{ $reply->id }}" style="display:none;">
                                        <form action="{{ route('comments.update', $reply) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <textarea name="content" class="form-control mb-2" rows="2">{{ $reply->content }}</textarea>
                                            <button type="submit" class="btn btn-sm btn-primary">حفظ</button>
                                            <button type="button" class="btn btn-sm btn-secondary cancel-edit-reply" data-comment-id="{{ $reply->id }}">إلغاء</button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</div>

@push('scripts')
    <script>
        // Show reply form
        document.querySelectorAll('.reply-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const commentId = this.dataset.commentId;
                document.getElementById(`reply-form-${commentId}`).style.display = 'block';
            });
        });
        document.querySelectorAll('.cancel-reply').forEach(btn => {
            btn.addEventListener('click', function() {
                const commentId = this.dataset.commentId;
                document.getElementById(`reply-form-${commentId}`).style.display = 'none';
            });
        });
        // Edit comment (simple)
        document.querySelectorAll('.edit-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const commentId = this.dataset.commentId;
                const content = this.dataset.content;
                // يمكنك تنفيذ تعديل عبر مربع حوار
                const newContent = prompt('تعديل التعليق:', content);
                if (newContent && newContent !== content) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = `{{ url('comments') }}/${commentId}`;
                    form.innerHTML = `
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="content" value="${newContent}">
                `;
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        });
        // Edit reply
        document.querySelectorAll('.edit-reply-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const replyId = this.dataset.commentId;
                document.getElementById(`edit-reply-form-${replyId}`).style.display = 'block';
            });
        });
        document.querySelectorAll('.cancel-edit-reply').forEach(btn => {
            btn.addEventListener('click', function() {
                const replyId = this.dataset.commentId;
                document.getElementById(`edit-reply-form-${replyId}`).style.display = 'none';
            });
        });
    </script>
@endpush
