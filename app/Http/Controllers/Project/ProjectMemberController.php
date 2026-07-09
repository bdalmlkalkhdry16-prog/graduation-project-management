<?php

namespace App\Http\Controllers\Project;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProjectMemberController extends Controller
{
    /**
     * عرض أعضاء المشروع
     */
    public function index(Project $project)
    {
        // التحقق من الصلاحية
        if (!auth()->user()->isAdmin() &&
            !auth()->user()->isSupervisor() &&
            !$project->students->contains(auth()->id())) {
            abort(403);
        }

        $members = $project->members()->with('student')->get();
        $leader = $project->members()->where('role', 'leader')->first();

        return view('projects.members.index', compact('project', 'members', 'leader'));
    }

    /**
     * عرض نموذج إضافة عضو جديد
     */
    public function create(Project $project)
    {
        // التحقق: يمكن للقائد أو المشرف إضافة أعضاء
        $isLeader = $project->members()
            ->where('student_id', auth()->id())
            ->where('role', 'leader')
            ->exists();

        if (!auth()->user()->isAdmin() &&
            !auth()->user()->isSupervisor() &&
            !$isLeader) {
            abort(403);
        }

        // التحقق: لا يمكن إضافة أعضاء للمشاريع المكتملة
        if (in_array($project->status, [Project::STATUS_COMPLETED, Project::STATUS_REJECTED])) {
            return back()->with('error', 'لا يمكن إضافة أعضاء لمشروع مكتمل أو مرفوض');
        }

        // الحصول على الطلاب المتاحين للإضافة
        $existingMemberIds = $project->members()->pluck('student_id')->toArray();
        $availableStudents = User::where('role', 'student')
            ->where('is_active', true)
            ->whereNotIn('id', $existingMemberIds)
            ->with('specialization')
            ->get();

        return view('projects.members.create', compact('project', 'availableStudents'));
    }

    /**
     * إضافة عضو جديد للمشروع
     */
    public function store(Request $request, Project $project)
    {
        // التحقق من الصلاحية
        $isLeader = $project->members()
            ->where('student_id', auth()->id())
            ->where('role', 'leader')
            ->exists();

        if (!auth()->user()->isAdmin() &&
            !auth()->user()->isSupervisor() &&
            !$isLeader) {
            abort(403);
        }

        $validator = Validator::make($request->all(), [
            'student_id' => 'required|exists:users,id',
            'role' => 'required|in:leader,member',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // التحقق: الطالب غير مسجل بالفعل
        $existing = $project->members()->where('student_id', $request->student_id)->exists();
        if ($existing) {
            return back()->with('error', 'الطالب مسجل بالفعل في هذا المشروع');
        }

        // التحقق: إذا كان الدور قائد، تأكد من عدم وجود قائد آخر
        if ($request->role === 'leader') {
            $existingLeader = $project->members()->where('role', 'leader')->exists();
            if ($existingLeader) {
                return back()->with('error', 'المشروع لديه قائد بالفعل');
            }
        }

        $member = ProjectMember::create([
            'project_id' => $project->id,
            'student_id' => $request->student_id,
            'role' => $request->role,
            'joined_at' => now(),
        ]);

        $this->logActivity('create', 'ProjectMember', $member->id);

        // إرسال إشعار للطالب المضاف
        $this->sendNotification(
            $request->student_id,
            'انضمام لمشروع',
            "تمت إضافتك كمشروع '{$project->title_ar}'",
            'success',
            route('projects.show', $project)
        );

        return redirect()->route('projects.members.index', $project)
            ->with('success', 'تم إضافة العضو بنجاح');
    }

    /**
     * تغيير دور العضو
     */
    public function updateRole(Request $request, Project $project, ProjectMember $member)
    {
        // التحقق: يمكن للمشرف فقط تغيير الأدوار
        if (!auth()->user()->isAdmin() && !auth()->user()->isSupervisor()) {
            abort(403);
        }

        $validator = Validator::make($request->all(), [
            'role' => 'required|in:leader,member',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        // التحقق: إذا كان الدور قائد، تأكد من عدم وجود قائد آخر
        if ($request->role === 'leader') {
            $existingLeader = $project->members()
                ->where('role', 'leader')
                ->where('id', '!=', $member->id)
                ->exists();
            if ($existingLeader) {
                return back()->with('error', 'المشروع لديه قائد بالفعل');
            }
        }

        $oldValues = $member->toArray();
        $member->update(['role' => $request->role]);

        $this->logActivity('update', 'ProjectMember', $member->id, $oldValues, $member->toArray());

        // إرسال إشعار للطالب
        $this->sendNotification(
            $member->student_id,
            'تغيير دور في المشروع',
            "تم تغيير دورك في مشروع '{$project->title_ar}' إلى " . ($request->role === 'leader' ? 'قائد الفريق' : 'عضو'),
            'info',
            route('projects.show', $project)
        );

        return back()->with('success', 'تم تغيير دور العضو بنجاح');
    }

    /**
     * إزالة عضو من المشروع
     */
    public function destroy(Project $project, ProjectMember $member)
    {
        // التحقق من الصلاحية
        $isLeader = $project->members()
            ->where('student_id', auth()->id())
            ->where('role', 'leader')
            ->exists();

        if (!auth()->user()->isAdmin() &&
            !auth()->user()->isSupervisor() &&
            !$isLeader) {
            abort(403);
        }

        // لا يمكن إزالة القائد إذا كان هناك أعضاء آخرون
        if ($member->isLeader() && $project->members()->count() > 1) {
            return back()->with('error', 'لا يمكن إزالة قائد المشروع قبل تعيين قائد آخر');
        }

        $studentName = $member->student->name;

        $this->logActivity('delete', 'ProjectMember', $member->id);

        $member->delete();

        // إرسال إشعار للطالب
        $this->sendNotification(
            $member->student_id,
            'إزالة من المشروع',
            "تمت إزالتك من مشروع '{$project->title_ar}'",
            'warning',
            null
        );

        return redirect()->route('projects.members.index', $project)
            ->with('success', "تم إزالة {$studentName} من المشروع بنجاح");
    }

    /**
     * عرض نموذج دعوة طالب للمشروع
     */
    public function showInviteForm(Project $project)
    {
        // التحقق: يمكن للقائد أو المشرف دعوة أعضاء جدد
        $isLeader = $project->members()
            ->where('student_id', auth()->id())
            ->where('role', 'leader')
            ->exists();

        if (!auth()->user()->isAdmin() &&
            !auth()->user()->isSupervisor() &&
            !$isLeader) {
            abort(403);
        }

        return view('projects.members.invite', compact('project'));
    }

    /**
     * إرسال دعوة لطالب
     */
    public function sendInvite(Request $request, Project $project)
    {
        // التحقق من الصلاحية
        $isLeader = $project->members()
            ->where('student_id', auth()->id())
            ->where('role', 'leader')
            ->exists();

        if (!auth()->user()->isAdmin() &&
            !auth()->user()->isSupervisor() &&
            !$isLeader) {
            abort(403);
        }

        $validator = Validator::make($request->all(), [
            'student_email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        $student = User::where('email', $request->student_email)
            ->where('role', 'student')
            ->first();

        if (!$student) {
            return back()->with('error', 'البريد الإلكتروني غير مسجل لطالب');
        }

        // التحقق: الطالب غير مسجل بالفعل
        $existing = $project->members()->where('student_id', $student->id)->exists();
        if ($existing) {
            return back()->with('error', 'الطالب مسجل بالفعل في هذا المشروع');
        }

        // إنشاء رمز دعوة
        $inviteCode = \Illuminate\Support\Str::random(32);

        // تخزين الدعوة (يمكن إنشاء جدول منفصل للدعوات)
        \Illuminate\Support\Facades\Cache::put(
            "invite_{$inviteCode}",
            [
                'project_id' => $project->id,
                'student_id' => $student->id,
                'invited_by' => auth()->id(),
                'expires_at' => now()->addDays(7),
            ],
            now()->addDays(7)
        );

        // إرسال إشعار للطالب
        $inviteLink = route('projects.invite.accept', $inviteCode);

        $this->sendNotification(
            $student->id,
            'دعوة للانضمام لمشروع',
            "تمت دعوتك للانضمام لمشروع '{$project->title_ar}'. اضغط للموافقة",
            'info',
            $inviteLink
        );

        return redirect()->route('projects.members.index', $project)
            ->with('success', "تم إرسال الدعوة إلى {$student->name} بنجاح");
    }

    /**
     * قبول دعوة المشروع
     */
    public function acceptInvite($code)
    {
        $invite = \Illuminate\Support\Facades\Cache::get("invite_{$code}");

        if (!$invite) {
            return redirect()->route('dashboard')
                ->with('error', 'الدعوة غير صالحة أو منتهية الصلاحية');
        }

        if ($invite['student_id'] !== auth()->id()) {
            abort(403);
        }

        $project = Project::findOrFail($invite['project_id']);

        // التحقق: الطالب غير مسجل بالفعل
        $existing = $project->members()->where('student_id', auth()->id())->exists();
        if ($existing) {
            \Illuminate\Support\Facades\Cache::forget("invite_{$code}");
            return redirect()->route('projects.show', $project)
                ->with('info', 'أنت بالفعل عضو في هذا المشروع');
        }

        // تحديد الدور (إذا كان أول عضو يصبح قائداً)
        $role = $project->members()->count() == 0 ? 'leader' : 'member';

        ProjectMember::create([
            'project_id' => $project->id,
            'student_id' => auth()->id(),
            'role' => $role,
            'joined_at' => now(),
        ]);

        \Illuminate\Support\Facades\Cache::forget("invite_{$code}");

        // إرسال إشعار للداعي
        $this->sendNotification(
            $invite['invited_by'],
            'تم قبول الدعوة',
            "قام " . auth()->user()->name . " بقبول الدعوة للانضمام لمشروع '{$project->title_ar}'",
            'success',
            route('projects.members.index', $project)
        );

        return redirect()->route('projects.show', $project)
            ->with('success', 'تم قبول الدعوة وانضمامك للمشروع بنجاح');
    }

    /**
     * إرسال إشعار
     */
    private function sendNotification($userId, $title, $message, $type, $link = null)
    {
        \App\Models\Notification::create([
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'link' => $link,
        ]);
    }
}
