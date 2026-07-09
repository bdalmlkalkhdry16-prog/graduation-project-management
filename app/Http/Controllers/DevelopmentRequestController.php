<?php

namespace App\Http\Controllers;

use App\Models\DevelopmentRequest;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DevelopmentRequestController extends Controller
{
    /**
     * عرض قائمة طلبات التطوير
     */
    public function index(Request $request)
    {
        $query = DevelopmentRequest::with(['project', 'student']);

        // فلترة حسب الحالة
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // فلترة حسب المشروع
        if ($request->has('project_id') && $request->project_id) {
            $query->where('project_id', $request->project_id);
        }

        // إذا كان المستخدم طالباً، عرض طلباته فقط
        if (auth()->user()->isStudent()) {
            $query->where('student_id', auth()->id());
        }

        $requests = $query->latest()->paginate(15);

        return view('development-requests.index', compact('requests'));
    }

    /**
     * عرض نموذج إنشاء طلب تطوير
     */
    public function create(Project $project)
    {
        // التحقق: يجب أن يكون الطالب مشاركاً في المشروع
        if (!auth()->user()->isStudent() || !$project->students->contains(auth()->id())) {
            abort(403);
        }

        // التحقق: يجب أن يكون المشروع مكتملاً
        if ($project->status !== Project::STATUS_COMPLETED) {
            return back()->with('error', 'لا يمكن طلب تطوير مشروع غير مكتمل');
        }

        // التحقق من وجود طلب سابق
        $existingRequest = $project->developmentRequests()
            ->where('student_id', auth()->id())
            ->where('status', 'pending')
            ->first();

        if ($existingRequest) {
            return back()->with('error', 'لديك طلب تطوير قيد الانتظار لهذا المشروع');
        }

        return view('development-requests.create', compact('project'));
    }

    /**
     * تخزين طلب تطوير جديد
     */
    public function store(Request $request, Project $project)
    {
        if (!auth()->user()->isStudent() || !$project->students->contains(auth()->id())) {
            abort(403);
        }

        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|min:10|max:1000',
            'proposed_improvements' => 'nullable|string|max:2000',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $developmentRequest = DevelopmentRequest::create([
            'project_id' => $project->id,
            'student_id' => auth()->id(),
            'reason' => $request->reason,
            'proposed_improvements' => $request->proposed_improvements,
            'status' => 'pending',
        ]);

        $this->logActivity('create', 'DevelopmentRequest', $developmentRequest->id);

        // إرسال إشعار للمدير
        \App\Models\Notification::create([
            'user_id' => 1, // أول مدير
            'title' => 'طلب تطوير مشروع جديد',
            'message' => "طلب تطوير مشروع '{$project->title_ar}' من الطالب " . auth()->user()->name,
            'type' => 'info',
            'link' => route('development-requests.show', $developmentRequest),
        ]);

        return redirect()->route('development-requests.index')
            ->with('success', 'تم إرسال طلب التطوير بنجاح');
    }

    /**
     * عرض تفاصيل طلب التطوير
     */
    public function show(DevelopmentRequest $developmentRequest)
    {
        // التحقق من الصلاحية
        if (!auth()->user()->isAdmin() &&
            $developmentRequest->student_id !== auth()->id()) {
            abort(403);
        }

        $developmentRequest->load(['project', 'student', 'reviewer']);

        return view('development-requests.show', compact('developmentRequest'));
    }

    /**
     * الموافقة على طلب التطوير (للمدير فقط)
     */
    public function approve(Request $request, DevelopmentRequest $developmentRequest)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403);
        }

        $validator = Validator::make($request->all(), [
            'admin_feedback' => 'nullable|string|max:500',
        ]);

        $developmentRequest->approve($request->admin_feedback);

        $this->logActivity('approve', 'DevelopmentRequest', $developmentRequest->id);

        // إرسال إشعار للطالب
        \App\Models\Notification::create([
            'user_id' => $developmentRequest->student_id,
            'title' => 'تمت الموافقة على طلب التطوير',
            'message' => "تمت الموافقة على طلب تطوير المشروع '{$developmentRequest->project->title_ar}'",
            'type' => 'success',
            'link' => route('development-requests.show', $developmentRequest),
        ]);

        return redirect()->route('development-requests.index')
            ->with('success', 'تمت الموافقة على الطلب بنجاح');
    }

    /**
     * رفض طلب التطوير (للمدير فقط)
     */
    public function reject(Request $request, DevelopmentRequest $developmentRequest)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403);
        }

        $validator = Validator::make($request->all(), [
            'admin_feedback' => 'required|string|min:10|max:500',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        $developmentRequest->reject($request->admin_feedback);

        $this->logActivity('reject', 'DevelopmentRequest', $developmentRequest->id);

        // إرسال إشعار للطالب
        \App\Models\Notification::create([
            'user_id' => $developmentRequest->student_id,
            'title' => 'تم رفض طلب التطوير',
            'message' => "تم رفض طلب تطوير المشروع '{$developmentRequest->project->title_ar}' بسبب: {$request->admin_feedback}",
            'type' => 'error',
            'link' => route('development-requests.show', $developmentRequest),
        ]);

        return redirect()->route('development-requests.index')
            ->with('success', 'تم رفض الطلب بنجاح');
    }
}
