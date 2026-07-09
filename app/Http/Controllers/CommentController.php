<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CommentController extends Controller
{
    /**
     * إضافة تعليق جديد
     */
    public function store(Request $request, Project $project)
    {
        $validator = Validator::make($request->all(), [
            'content' => 'required|string|min:2|max:1000',
            'parent_id' => 'nullable|exists:comments,id',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $comment = Comment::create([
            'project_id' => $project->id,
            'user_id' => auth()->id(),
            'content' => $request->content,
            'parent_id' => $request->parent_id,
        ]);

        $this->logActivity('create', 'Comment', $comment->id);

        // إرسال إشعار لصاحب المشروع والمشرف
        $this->sendCommentNotification($project, $comment);

        return back()->with('success', 'تم إضافة التعليق بنجاح');
    }

    /**
     * تحديث تعليق
     */
    public function update(Request $request, Comment $comment)
    {
        // التحقق من أن المستخدم هو صاحب التعليق
        if ($comment->user_id !== auth()->id() && !auth()->user()->isAdmin()) {
            abort(403);
        }

        $validator = Validator::make($request->all(), [
            'content' => 'required|string|min:2|max:1000',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        $oldValues = $comment->toArray();

        $comment->update([
            'content' => $request->content,
            'is_edited' => true,
            'edited_at' => now(),
        ]);

        $this->logActivity('update', 'Comment', $comment->id, $oldValues, $comment->toArray());

        return back()->with('success', 'تم تحديث التعليق بنجاح');
    }

    /**
     * حذف تعليق
     */
    public function destroy(Comment $comment)
    {
        // التحقق من أن المستخدم هو صاحب التعليق أو مدير
        if ($comment->user_id !== auth()->id() && !auth()->user()->isAdmin()) {
            abort(403);
        }

        $this->logActivity('delete', 'Comment', $comment->id);

        // حذف جميع الردود على هذا التعليق
        $comment->replies()->delete();
        $comment->delete();

        return back()->with('success', 'تم حذف التعليق بنجاح');
    }

    /**
     * إرسال إشعار عند إضافة تعليق
     */
    private function sendCommentNotification($project, $comment)
    {
        // إرسال إشعار للمشرف إذا كان موجوداً وليس هو صاحب التعليق
        if ($project->supervisor_id && $project->supervisor_id !== auth()->id()) {
            \App\Models\Notification::create([
                'user_id' => $project->supervisor_id,
                'title' => 'تعليق جديد على مشروع',
                'message' => "تم إضافة تعليق جديد على مشروع '{$project->title_ar}'",
                'type' => 'info',
                'link' => route('projects.show', $project),
            ]);
        }

        // إرسال إشعار للطلاب في المشروع (باستثناء صاحب التعليق)
        foreach ($project->students as $student) {
            if ($student->id !== auth()->id()) {
                \App\Models\Notification::create([
                    'user_id' => $student->id,
                    'title' => 'تعليق جديد على مشروع',
                    'message' => "تم إضافة تعليق جديد على مشروع '{$project->title_ar}'",
                    'type' => 'info',
                    'link' => route('projects.show', $project),
                ]);
            }
        }
    }
}