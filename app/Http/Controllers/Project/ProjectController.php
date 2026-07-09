<?php

namespace App\Http\Controllers\Project;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Specialization;
use App\Models\User;
use App\Models\ProjectMember;
use App\Models\Notification;
use App\Models\Idea;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ProjectController extends Controller
{
    // ===========================
    // عرض قائمة المشاريع
    // ===========================
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Project::with(['supervisor', 'specialization', 'students']);

        // فلترة حسب الدور
        if ($user->isStudent()) {
            $query->whereHas('students', function($q) use ($user) {
                $q->where('users.id', $user->id);
            });
        } elseif ($user->isSupervisor()) {
            $query->where('supervisor_id', $user->id);
        }

        // فلاتر إضافية
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }
        if ($request->has('specialization_id') && $request->specialization_id) {
            $query->where('specialization_id', $request->specialization_id);
        }
        if ($request->has('academic_year') && $request->academic_year) {
            $query->where('academic_year', $request->academic_year);
        }
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title_ar', 'like', "%{$search}%")
                    ->orWhere('title_en', 'like', "%{$search}%")
                    ->orWhere('abstract_ar', 'like', "%{$search}%");
            });
        }

        $projects = $query->latest()->paginate(15);
        $specializations = Specialization::active()->get();

        return view('projects.index', compact('projects', 'specializations'));
    }

    // ===========================
    // المشاريع قيد المراجعة (للمشرف)
    // ===========================
    public function pendingReview(Request $request)
    {
        if (!auth()->user()->isSupervisor() && !auth()->user()->isAdmin()) {
            abort(403);
        }

        $query = Project::with(['supervisor', 'specialization', 'students'])
            ->whereIn('status', [Project::STATUS_SUBMITTED, Project::STATUS_UNDER_REVIEW]);

        if (auth()->user()->isSupervisor() && !auth()->user()->isAdmin()) {
            $query->where('supervisor_id', auth()->id());
        }

        if ($request->has('specialization_id') && $request->specialization_id) {
            $query->where('specialization_id', $request->specialization_id);
        }

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title_ar', 'like', "%{$search}%")
                  ->orWhere('title_en', 'like', "%{$search}%");
            });
        }

        $projects = $query->latest('submission_date')->paginate(15);
        $specializations = Specialization::active()->get();

        return view('projects.pending_review', compact('projects', 'specializations'));
    }

    // ===========================
    // أرشيف المشاريع المكتملة
    // ===========================
    public function archive(Request $request)
    {
        $query = Project::with(['supervisor', 'specialization', 'students'])
            ->where('status', Project::STATUS_COMPLETED);

        if ($request->has('specialization_id') && $request->specialization_id) {
            $query->where('specialization_id', $request->specialization_id);
        }
        if ($request->has('academic_year') && $request->academic_year) {
            $query->where('academic_year', $request->academic_year);
        }
        if ($request->has('supervisor_id') && $request->supervisor_id) {
            $query->where('supervisor_id', $request->supervisor_id);
        }
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title_ar', 'like', "%{$search}%")
                  ->orWhere('title_en', 'like', "%{$search}%")
                  ->orWhere('abstract_ar', 'like', "%{$search}%");
            });
        }

        $projects = $query->latest('defense_date')->paginate(15);
        $specializations = Specialization::active()->get();
        $academicYears = Project::where('status', Project::STATUS_COMPLETED)
            ->select('academic_year')
            ->distinct()
            ->orderBy('academic_year', 'desc')
            ->pluck('academic_year');
        $supervisors = User::where('role', 'supervisor')
            ->whereHas('supervisedProjects', function($q) {
                $q->where('status', Project::STATUS_COMPLETED);
            })
            ->get();

        return view('projects.archive', compact('projects', 'specializations', 'academicYears', 'supervisors'));
    }

    // ===========================
    // إدارة المناقشات (Defense)
    // ===========================
    public function showDefenseForm(Project $project)
    {
        if (!auth()->user()->isAdmin() && !(auth()->user()->isSupervisor() && $project->supervisor_id === auth()->id())) {
            abort(403);
        }
        if ($project->status !== Project::STATUS_APPROVED) {
            return back()->with('error', 'لا يمكن تحديد موعد مناقشة إلا للمشاريع المقبولة.');
        }
        return view('projects.set_defense', compact('project'));
    }

    /**
     * تحديد موعد المناقشة مع تحقق after:now
     */
    public function setDefenseDate(Request $request, Project $project)
    {
        if (!auth()->user()->isAdmin() && !(auth()->user()->isSupervisor() && $project->supervisor_id === auth()->id())) {
            abort(403);
        }

        $validated = $request->validate([
            'defense_date'      => 'required|date|after:now',
            'defense_location'  => 'nullable|string|max:255',
            'defense_notes'     => 'nullable|string',
        ], [
            'defense_date.after'    => 'يجب أن يكون تاريخ ووقت المناقشة في المستقبل.',
            'defense_date.required' => 'يرجى تحديد تاريخ ووقت المناقشة.',
        ]);

        $project->update([
            'defense_date'     => $validated['defense_date'],
            'defense_location' => $validated['defense_location'],
            'defense_notes'    => $validated['defense_notes'],
        ]);

        // إشعار الطلاب
        foreach ($project->students as $student) {
            $this->sendNotification(
                $student->id,
                'تحديد موعد المناقشة',
                "تم تحديد موعد مناقشة مشروعك '{$project->title_ar}' بتاريخ " . date('Y-m-d H:i', strtotime($validated['defense_date'])),
                'info',
                route('projects.show', $project)
            );
        }

        // إشعار المشرف (إذا قام الأدمن بالتحديد)
        if (auth()->id() !== $project->supervisor_id) {
            $this->sendNotification(
                $project->supervisor_id,
                'تحديد موعد مناقشة',
                "تم تحديد موعد مناقشة المشروع '{$project->title_ar}' بتاريخ " . date('Y-m-d H:i', strtotime($validated['defense_date'])),
                'info',
                route('projects.show', $project)
            );
        }

        return redirect()->route('projects.show', $project)->with('success', 'تم تحديد موعد المناقشة بنجاح.');
    }

    public function defenseSchedule(Request $request)
    {
        if (!auth()->user()->isSupervisor() && !auth()->user()->isAdmin()) {
            abort(403);
        }

        $query = Project::with(['supervisor', 'students'])
            ->whereNotNull('defense_date')
            ->where('status', '!=', Project::STATUS_REJECTED);

        if (auth()->user()->isSupervisor() && !auth()->user()->isAdmin()) {
            $query->where('supervisor_id', auth()->id());
        }

        $projects = $query->orderBy('defense_date', 'asc')->paginate(15);
        return view('projects.defense_schedule', compact('projects'));
    }

    // ===========================
    // إنشاء مشروع جديد
    // ===========================
    public function create()
    {
        $specializations = Specialization::active()->get();
        $supervisors = User::where('role', 'supervisor')->where('is_active', true)->get();
        return view('projects.create', compact('specializations', 'supervisors'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title_ar'          => 'required|string|max:255',
            'title_en'          => 'nullable|string|max:255',
            'abstract_ar'       => 'required|string',
            'abstract_en'       => 'nullable|string',
            'keywords'          => 'nullable|string',
            'supervisor_id'     => 'required|exists:users,id',
            'specialization_id' => 'required|exists:specializations,id',
            'academic_year'     => 'required|integer|min:2020|max:2030',
            'semester'          => 'required|in:first,second,summer',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // جلب العام الدراسي النشط إذا لم يُرسل أو استخدام المُدخل
        $academic_year = $request->academic_year;
        if (class_exists('App\Models\AcademicYear')) {
            $activeYear = AcademicYear::getActive();
            $academic_year = $activeYear ? $activeYear->year : $academic_year;
        }

        $project = Project::create($request->only([
            'title_ar', 'title_en', 'abstract_ar', 'abstract_en', 'keywords',
            'supervisor_id', 'specialization_id', 'semester'
        ]) + [
            'academic_year' => $academic_year,
            'status'        => Project::STATUS_DRAFT,
        ]);

        $this->logActivity('create', 'Project', $project->id);
        return redirect()->route('projects.show', $project)->with('success', 'تم إنشاء المشروع بنجاح');
    }

    // ===========================
    // عرض المشروع - منع الطالب من رؤية زر التعديل إذا لم تكن الحالة draft
    // ===========================
    public function show($id)
    {
        $project = Project::with([
            'supervisor', 'specialization', 'specialization.department', 'specialization.department.college',
            'students', 'files', 'evaluations', 'comments.user'
        ])->findOrFail($id);

        $canEvaluate = auth()->user()->isSupervisor() && $project->supervisor_id === auth()->id()
            && $project->status === Project::STATUS_SUBMITTED;

        $canEdit = false;
        if (auth()->user()->isAdmin()) {
            $canEdit = true;
        } elseif (auth()->user()->isSupervisor() && $project->supervisor_id === auth()->id()) {
            $canEdit = $project->status !== Project::STATUS_COMPLETED;
        } elseif (auth()->user()->isStudent() && $project->students->contains(auth()->id())) {
            // الطالب يستطيع التعديل فقط إذا كان المشروع في حالة مسودة
            $canEdit = ($project->status === Project::STATUS_DRAFT);
        }

        return view('projects.show', compact('project', 'canEvaluate', 'canEdit'));
    }

    // ===========================
    // تعديل المشروع - منع الطالب من الدخول إلى صفحة التعديل إذا لم تكن الحالة draft
    // ===========================
    public function edit($id)
    {
        $project = Project::findOrFail($id);
        if (!auth()->user()->isAdmin() &&
            !(auth()->user()->isSupervisor() && $project->supervisor_id === auth()->id()) &&
            !(auth()->user()->isStudent() && $project->students->contains(auth()->id()))) {
            abort(403);
        }

        // منع الطالب من التعديل إذا لم تكن الحالة مسودة
        if (auth()->user()->isStudent() && $project->status !== Project::STATUS_DRAFT) {
            abort(403, 'لا يمكن تعديل المشروع بعد تقديمه أو قبوله.');
        }

        if (!auth()->user()->isAdmin() && in_array($project->status, [Project::STATUS_COMPLETED, Project::STATUS_REJECTED])) {
            abort(403, 'لا يمكن تعديل مشروع مكتمل أو مرفوض.');
        }

        $specializations = Specialization::active()->get();
        $supervisors = User::where('role', 'supervisor')->where('is_active', true)->get();
        return view('projects.edit', compact('project', 'specializations', 'supervisors'));
    }

    // ===========================
    // تحديث المشروع - منع الطالب من إرسال التعديلات إذا لم تكن الحالة draft
    // ===========================
    public function update(Request $request, $id)
    {
        $project = Project::findOrFail($id);
        if (!auth()->user()->isAdmin() &&
            !(auth()->user()->isSupervisor() && $project->supervisor_id === auth()->id()) &&
            !(auth()->user()->isStudent() && $project->students->contains(auth()->id()))) {
            abort(403);
        }

        // منع الطالب من التحديث إذا لم تكن الحالة مسودة
        if (auth()->user()->isStudent() && $project->status !== Project::STATUS_DRAFT) {
            abort(403, 'لا يمكن تعديل المشروع بعد تقديمه أو قبوله.');
        }

        if (!auth()->user()->isAdmin() && in_array($project->status, [Project::STATUS_COMPLETED, Project::STATUS_REJECTED])) {
            abort(403, 'لا يمكن تعديل مشروع مكتمل أو مرفوض.');
        }

        $validator = Validator::make($request->all(), [
            'title_ar'          => 'required|string|max:255',
            'title_en'          => 'nullable|string|max:255',
            'abstract_ar'       => 'required|string',
            'abstract_en'       => 'nullable|string',
            'keywords'          => 'nullable|string',
            'supervisor_id'     => 'required|exists:users,id',
            'specialization_id' => 'required|exists:specializations,id',
            'academic_year'     => 'required|integer|min:2020|max:2030',
            'semester'          => 'required|in:first,second,summer',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $oldValues = $project->toArray();
        $project->update($request->only([
            'title_ar', 'title_en', 'abstract_ar', 'abstract_en', 'keywords',
            'supervisor_id', 'specialization_id', 'academic_year', 'semester'
        ]));
        $this->logActivity('update', 'Project', $project->id, $oldValues, $project->toArray());

        return redirect()->route('projects.show', $project)->with('success', 'تم تحديث المشروع بنجاح');
    }

    // ===========================
    // تقديم المشروع للمراجعة
    // ===========================
    public function submit($id)
    {
        $project = Project::findOrFail($id);
        if (!auth()->user()->isStudent() || !$project->students->contains(auth()->id())) {
            abort(403);
        }
        if ($project->status !== Project::STATUS_DRAFT) {
            return back()->with('error', 'لا يمكن تقديم المشروع في حالته الحالية');
        }

        $project->update(['status' => Project::STATUS_SUBMITTED, 'submission_date' => now()]);
        $this->logActivity('submit', 'Project', $project->id);
        $this->sendNotification(
            $project->supervisor_id,
            'تقديم مشروع جديد',
            "تم تقديم المشروع '{$project->title_ar}' للمراجعة",
            'info',
            route('projects.show', $project)
        );

        return redirect()->route('projects.show', $project)->with('success', 'تم تقديم المشروع للمراجعة بنجاح');
    }

    // ===========================
    // مراجعة المشروع (للمشرف)
    // ===========================
    public function review(Request $request, $id)
    {
        $project = Project::findOrFail($id);

        // فقط المشرف على المشروع أو المدير
        if (!auth()->user()->isSupervisor() || $project->supervisor_id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'status'   => 'required|in:approved,rejected',
            'feedback' => 'nullable|string',
        ]);

        $project->update([
            'status'       => $request->status,
            'feedback'     => $request->feedback,
            'approval_date'=> $request->status === 'approved' ? now() : null,
        ]);

        // إشعار للطلاب
        $message = $request->status === 'approved'
            ? "تم قبول مشروعك '{$project->title_ar}' ويمكنك الآن رفع الملفات."
            : "تم رفض مشروعك '{$project->title_ar}' بسبب: " . ($request->feedback ?: 'لم يتم تقديم ملاحظات');

        foreach ($project->students as $student) {
            $this->sendNotification(
                $student->id,
                'نتيجة مراجعة المشروع',
                $message,
                $request->status === 'approved' ? 'success' : 'error',
                route('projects.show', $project)
            );
        }

        return redirect()->route('projects.show', $project)
            ->with('success', "تم {$project->status_name} المشروع بنجاح");
    }

    // ===========================
    // حذف المشروع
    // ===========================
    public function destroy($id)
    {
        $project = Project::findOrFail($id);
        if (!auth()->user()->isAdmin()) {
            abort(403);
        }

        $this->logActivity('delete', 'Project', $project->id);
        foreach ($project->files as $file) {
            $this->deleteFile($file->file_path);
        }
        $project->delete();

        return redirect()->route('projects.index')->with('success', 'تم حذف المشروع بنجاح');
    }

    // ===========================
    // دوال الأفكار
    // ===========================
    public function createIdea()
    {
        $specializations = Specialization::active()->get();
        return view('projects.create_idea', compact('specializations'));
    }

    public function submitIdea(Request $request)
    {
        $request->validate([
            'title_ar'          => 'required|string|max:255',
            'abstract_ar'       => 'nullable|string',
            'keywords'          => 'nullable|string',
            'specialization_id' => 'nullable|exists:specializations,id',
        ]);

        $duplicate = Idea::where('status', Idea::STATUS_APPROVED)
            ->where(function ($q) use ($request) {
                $q->where('title_ar', 'like', "%{$request->title_ar}%");
                if ($request->keywords) {
                    $q->orWhere('keywords', 'like', "%{$request->keywords}%");
                }
            })->exists();

        if ($duplicate) {
            return back()->with('error', 'يوجد فكرة معتمدة سابقة بنفس العنوان أو الكلمات المفتاحية.');
        }

        $idea = Idea::create([
            'title_ar'          => $request->title_ar,
            'abstract_ar'       => $request->abstract_ar,
            'keywords'          => $request->keywords,
            'specialization_id' => $request->specialization_id,
            'student_id'        => auth()->id(),
            'status'            => Idea::STATUS_PENDING,
            'submitted_at'      => now(),
        ]);

        $specialists = User::where('role', 'supervisor')->orWhere('role', 'admin')->get();
        foreach ($specialists as $specialist) {
            Notification::create([
                'user_id' => $specialist->id,
                'title'   => 'فكرة مشروع جديدة',
                'message' => "تم تقديم فكرة جديدة: {$idea->title_ar} من قبل الطالب " . auth()->user()->name,
                'type'    => 'info',
                'link'    => route('projects.idea.show', $idea),
            ]);
        }

        return redirect()->route('dashboard')->with('success', 'تم تقديم الفكرة بنجاح، سيتم مراجعتها من قبل المختصين.');
    }

    public function showIdea(Idea $idea)
    {
        if (auth()->id() !== $idea->student_id && !auth()->user()->isSupervisor() && !auth()->user()->isAdmin()) {
            abort(403);
        }
        return view('projects.idea_show', compact('idea'));
    }

    public function reviewIdea(Request $request, Idea $idea)
    {
        if (!auth()->user()->isSupervisor() && !auth()->user()->isAdmin()) {
            abort(403);
        }

        $action = $request->input('action');
        $notes = $request->input('review_notes');

        if ($action === 'approve') {
            $project = Project::create([
                'title_ar'          => $idea->title_ar,
                'title_en'          => $idea->title_en,
                'abstract_ar'       => $idea->abstract_ar,
                'abstract_en'       => $idea->abstract_en,
                'keywords'          => $idea->keywords,
                'specialization_id' => $idea->specialization_id,
                'status'            => Project::STATUS_APPROVED,
                'academic_year'     => null,
                'semester'          => null,
            ]);

            ProjectMember::create([
                'project_id' => $project->id,
                'student_id' => $idea->student_id,
                'role'       => 'leader',
                'joined_at'  => now(),
            ]);

            $idea->update([
                'status'       => Idea::STATUS_APPROVED,
                'review_notes' => $notes,
                'reviewed_by'  => auth()->id(),
                'reviewed_at'  => now(),
                'project_id'   => $project->id,
            ]);

            $message = 'تمت الموافقة على الفكرة. يمكنك الآن البدء في تطوير المشروع.';
            $link    = route('projects.show', $project);
        } else {
            $idea->update([
                'status'       => Idea::STATUS_REJECTED,
                'review_notes' => $notes,
                'reviewed_by'  => auth()->id(),
                'reviewed_at'  => now(),
            ]);
            $message = 'تم رفض الفكرة. يمكنك تعديلها وإعادة تقديمها.';
            $link    = route('projects.idea.show', $idea);
        }

        Notification::create([
            'user_id' => $idea->student_id,
            'title'   => 'نتيجة مراجعة الفكرة',
            'message' => $message,
            'type'    => $action === 'approve' ? 'success' : 'danger',
            'link'    => $link,
        ]);

        return redirect()->route('projects.idea.show', $idea)->with('success', 'تمت مراجعة الفكرة بنجاح');
    }

    // ===========================
    // دوال مساعدة
    // ===========================
    private function sendNotification($userId, $title, $message, $type, $link = null)
    {
        if (empty($userId)) {
            return;
        }
        Notification::create([
            'user_id' => $userId,
            'title'   => $title,
            'message' => $message,
            'type'    => $type,
            'link'    => $link,
        ]);
    }

    // ===========================
    // دالة ثابتة للحصول على اسم الحالة (للاستخدام في التقارير)
    // ===========================
    public static function getStatusName($status)
    {
        return match($status) {
            Project::STATUS_DRAFT         => 'مسودة',
            Project::STATUS_SUBMITTED     => 'تم التقديم',
            Project::STATUS_UNDER_REVIEW  => 'قيد المراجعة',
            Project::STATUS_APPROVED      => 'مقبول',
            Project::STATUS_REJECTED      => 'مرفوض',
            Project::STATUS_COMPLETED     => 'مكتمل',
            default                       => $status,
        };
    }
}