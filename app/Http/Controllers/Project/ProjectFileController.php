<?php

namespace App\Http\Controllers\Project;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class ProjectFileController extends Controller
{
    /**
     * عرض قائمة ملفات المشروع
     */
    public function index(Project $project)
    {
        // التحقق: المدير، المشرف، أو عضو المشروع فقط
        if (!auth()->user()->isAdmin() &&
            !auth()->user()->isSupervisor() &&
            !$project->students->contains(auth()->id())) {
            abort(403, 'غير مصرح لك بعرض ملفات هذا المشروع.');
        }

        $files = $project->files()->with('uploader')->latest()->get();
        return view('projects.files.index', compact('project', 'files'));
    }

    /**
     * رفع ملف جديد للمشروع
     */
    public function store(Request $request, Project $project)
    {
        // التحقق: المدير، المشرف، أو عضو المشروع فقط
        if (!auth()->user()->isAdmin() &&
            !auth()->user()->isSupervisor() &&
            !$project->students->contains(auth()->id())) {
            abort(403, 'غير مصرح لك برفع ملفات لهذا المشروع.');
        }

        if (in_array($project->status, [Project::STATUS_COMPLETED, Project::STATUS_REJECTED])) {
            return back()->with('error', 'لا يمكن رفع ملفات لمشروع مكتمل أو مرفوض');
        }

        $validator = Validator::make($request->all(), [
            'file' => 'required|file|max:10240',
            'file_category' => 'required|in:proposal,report,presentation,source_code,poster,other',
            'description' => 'nullable|string|max:500',
            'version' => 'nullable|integer|min:1',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();
        $fileSize = $file->getSize();
        $fileType = $file->getClientMimeType();

        $path = $file->store("projects/{$project->id}/files", 'public');
        $version = $request->version ?? $this->getNextVersion($project, $request->file_category);

        $projectFile = ProjectFile::create([
            'project_id' => $project->id,
            'file_name' => $originalName,
            'file_path' => $path,
            'file_type' => $fileType,
            'file_size' => $fileSize,
            'file_category' => $request->file_category,
            'version' => $version,
            'uploaded_by' => auth()->id(),
            'description' => $request->description,
            'is_approved' => auth()->user()->isSupervisor() || auth()->user()->isAdmin(),
        ]);

        $this->logActivity('upload', 'ProjectFile', $projectFile->id);

        // إرسال إشعار للمشرف إذا كان موجوداً والطالب هو من رفع الملف
        if (auth()->user()->isStudent() && $project->supervisor_id) {
            $this->sendNotification(
                $project->supervisor_id,
                'ملف جديد مرفوع',
                "تم رفع ملف جديد '{$originalName}' في مشروع '{$project->title_ar}'",
                'info',
                route('projects.files.index', $project)
            );
        }

        return redirect()->route('projects.files.index', $project)
            ->with('success', 'تم رفع الملف بنجاح');
    }

    /**
     * تحميل ملف
     */
    public function download(ProjectFile $file)
    {
        $project = $file->project;

        // التحقق: المدير، المشرف، أو عضو المشروع فقط
        if (!auth()->user()->isAdmin() &&
            !auth()->user()->isSupervisor() &&
            !$project->students->contains(auth()->id())) {
            abort(403, 'غير مصرح لك بتحميل ملفات هذا المشروع.');
        }

        if (!Storage::disk('public')->exists($file->file_path)) {
            return back()->with('error', 'الملف غير موجود');
        }

        $this->logActivity('download', 'ProjectFile', $file->id);
        return Storage::disk('public')->download($file->file_path, $file->file_name);
    }

    /**
     * حذف ملف
     */
    public function destroy(ProjectFile $file)
    {
        $project = $file->project;

        // التحقق مما إذا كان المستخدم قائد الفريق
        $isLeader = $project->members()
            ->where('student_id', auth()->id())
            ->where('role', 'leader')
            ->exists();

        // صلاحية الحذف: مدير، أو مشرف المشروع، أو الطالب الذي رفع الملف، أو قائد الفريق
        if (!auth()->user()->isAdmin() &&
            !(auth()->user()->isSupervisor() && $project->supervisor_id === auth()->id()) &&
            !(auth()->user()->isStudent() && ($file->uploaded_by === auth()->id() || $isLeader))) {
            abort(403, 'غير مصرح لك بحذف هذا الملف.');
        }

        // منع حذف ملفات من مشروع مكتمل (لغير المدير)
        if ($project->status === Project::STATUS_COMPLETED && !auth()->user()->isAdmin()) {
            return back()->with('error', 'لا يمكن حذف ملفات من مشروع مكتمل');
        }

        if (Storage::disk('public')->exists($file->file_path)) {
            Storage::disk('public')->delete($file->file_path);
        }

        $this->logActivity('delete', 'ProjectFile', $file->id);
        $file->delete();

        return redirect()->route('projects.files.index', $project)
            ->with('success', 'تم حذف الملف بنجاح');
    }

    /**
     * الموافقة على ملف (للمشرف)
     */
    public function approve(ProjectFile $file)
    {
        $project = $file->project;

        // فقط المشرف على المشروع أو المدير
        if ($project->supervisor_id !== auth()->id() && !auth()->user()->isAdmin()) {
            abort(403, 'غير مصرح لك بالموافقة على هذا الملف.');
        }

        $file->update(['is_approved' => true]);

        $this->logActivity('approve', 'ProjectFile', $file->id);

        // إرسال إشعار للطالب الذي رفع الملف (إذا كان موجوداً)
        if ($file->uploaded_by) {
            $this->sendNotification(
                $file->uploaded_by,
                'تمت الموافقة على الملف',
                "تمت الموافقة على الملف '{$file->file_name}' في مشروع '{$project->title_ar}'",
                'success',
                route('projects.files.index', $project)
            );
        }

        return back()->with('success', 'تمت الموافقة على الملف بنجاح');
    }

    /**
     * الحصول على رقم الإصدار التالي
     */
    private function getNextVersion($project, $category)
    {
        $lastFile = $project->files()
            ->where('file_category', $category)
            ->orderBy('version', 'desc')
            ->first();
        return $lastFile ? $lastFile->version + 1 : 1;
    }

    /**
     * إرسال إشعار (مع التحقق من وجود المستلم)
     */
    private function sendNotification($userId, $title, $message, $type, $link = null)
    {
        if (empty($userId)) {
            return;
        }

        \App\Models\Notification::create([
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'link' => $link,
        ]);
    }
}