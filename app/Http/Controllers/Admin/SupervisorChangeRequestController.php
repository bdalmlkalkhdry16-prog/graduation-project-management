<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\User;
use App\Models\SupervisorChangeRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\SupervisorChangeRequestMail;

class SupervisorChangeRequestController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = SupervisorChangeRequest::with(['project', 'student', 'currentSupervisor', 'proposedSupervisor']);

        if ($user->isStudent()) {
            $query->where('student_id', $user->id);
        } elseif ($user->isSupervisor()) {
            $query->where(function($q) use ($user) {
                $q->where('current_supervisor_id', $user->id)
                  ->orWhere('proposed_supervisor_id', $user->id);
            });
        }
        // Admin sees all

        $requests = $query->latest()->paginate(15);
        return view('admin.supervisor-change-requests.index', compact('requests'));
    }

    public function create(Project $project)
    {
        $student = auth()->user();
        if (!$student->isStudent() || !$project->students->contains($student->id)) {
            abort(403);
        }
        if (in_array($project->status, [Project::STATUS_COMPLETED, Project::STATUS_REJECTED])) {
            return back()->with('error', 'لا يمكن طلب تغيير المشرف لمشروع مكتمل أو مرفوض.');
        }
        $exists = SupervisorChangeRequest::where('project_id', $project->id)
                ->where('student_id', $student->id)
                ->where('status', 'pending')
                ->exists();
        if ($exists) {
            return back()->with('error', 'لديك طلب معلق بالفعل لهذا المشروع.');
        }

        $supervisors = User::where('role', 'supervisor')->where('is_active', true)->get();
        return view('admin.supervisor-change-requests.create', compact('project', 'supervisors'));
    }

    public function store(Request $request, Project $project)
    {
        $student = auth()->user();
        if (!$student->isStudent() || !$project->students->contains($student->id)) {
            abort(403);
        }

        $request->validate([
            'proposed_supervisor_id' => 'required|exists:users,id|different:current_supervisor_id',
            'reason' => 'required|string|min:10|max:500',
        ]);

        $currentSupervisorId = $project->supervisor_id;

        $changeRequest = SupervisorChangeRequest::create([
            'project_id' => $project->id,
            'student_id' => $student->id,
            'current_supervisor_id' => $currentSupervisorId,
            'proposed_supervisor_id' => $request->proposed_supervisor_id,
            'reason' => $request->reason,
            'status' => 'pending',
        ]);

        // إشعار للمدراء
        $admins = User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            \App\Models\Notification::create([
                'user_id' => $admin->id,
                'title' => 'طلب تغيير مشرف جديد',
                'message' => "طلب الطالب {$student->name} تغيير مشرف مشروع '{$project->title_ar}' من {$project->supervisor->name} إلى " . User::find($request->proposed_supervisor_id)->name,
                'type' => 'info',
                'link' => route('supervisor-requests.show', $changeRequest),  // ✅ تم التعديل
            ]);
        }

        return redirect()->route('dashboard')->with('success', 'تم تقديم طلب تغيير المشرف بنجاح، سيتم مراجعته من قبل الإدارة.');
    }

    public function show(SupervisorChangeRequest $supervisorChangeRequest)
    {
        $user = auth()->user();
        if (!$user->isAdmin() && !$user->isSupervisor() && $supervisorChangeRequest->student_id != $user->id) {
            abort(403);
        }
        return view('admin.supervisor-change-requests.show', compact('supervisorChangeRequest'));
    }

    public function approve(SupervisorChangeRequest $supervisorChangeRequest)
    {
        if (!auth()->user()->isAdmin()) abort(403);
        if ($supervisorChangeRequest->status !== 'pending') {
            return back()->with('error', 'تمت مراجعة هذا الطلب بالفعل.');
        }

        $project = $supervisorChangeRequest->project;
        $oldSupervisor = $project->supervisor_id;
        $newSupervisor = $supervisorChangeRequest->proposed_supervisor_id;

        $project->update(['supervisor_id' => $newSupervisor]);
        $supervisorChangeRequest->update([
            'status' => 'approved',
            'reviewed_at' => now(),
            'reviewed_by' => auth()->id(),
        ]);

        // إشعار للطالب
        \App\Models\Notification::create([
            'user_id' => $supervisorChangeRequest->student_id,
            'title' => 'تم قبول طلب تغيير المشرف',
            'message' => "تم تغيير مشرف مشروعك '{$project->title_ar}' إلى " . User::find($newSupervisor)->name,
            'type' => 'success',
            'link' => route('projects.show', $project),
        ]);
        // إشعار للمشرف الجديد
        \App\Models\Notification::create([
            'user_id' => $newSupervisor,
            'title' => 'تم تعيينك مشرفاً على مشروع',
            'message' => "تم تعيينك مشرفاً على مشروع '{$project->title_ar}' بعد موافقة الإدارة.",
            'type' => 'info',
            'link' => route('projects.show', $project),
        ]);

        return redirect()->route('supervisor-requests.index')->with('success', 'تم قبول الطلب وتغيير المشرف بنجاح.');  // ✅ تم التعديل
    }

    public function reject(Request $request, SupervisorChangeRequest $supervisorChangeRequest)
    {
        if (!auth()->user()->isAdmin()) abort(403);
        $request->validate(['admin_feedback' => 'required|string|min:5|max:500']);
        if ($supervisorChangeRequest->status !== 'pending') {
            return back()->with('error', 'تمت مراجعة هذا الطلب بالفعل.');
        }
        $supervisorChangeRequest->update([
            'status' => 'rejected',
            'admin_feedback' => $request->admin_feedback,
            'reviewed_at' => now(),
            'reviewed_by' => auth()->id(),
        ]);

        \App\Models\Notification::create([
            'user_id' => $supervisorChangeRequest->student_id,
            'title' => 'تم رفض طلب تغيير المشرف',
            'message' => "تم رفض طلب تغيير مشرف مشروع '{$supervisorChangeRequest->project->title_ar}' بسبب: " . $request->admin_feedback,
            'type' => 'error',
            'link' => route('projects.show', $supervisorChangeRequest->project),
        ]);

        return redirect()->route('supervisor-requests.index')->with('success', 'تم رفض الطلب.');  // ✅ تم التعديل
    }
}