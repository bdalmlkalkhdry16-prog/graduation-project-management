<?php

namespace App\Http\Controllers\Evaluation;

use App\Http\Controllers\Controller;
use App\Models\Evaluation;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EvaluationController extends Controller
{
    /**
     * عرض قائمة التقييمات حسب دور المستخدم
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        if ($user->isAdmin()) {
            $evaluations = Evaluation::with(['project', 'supervisor'])
                ->where('status', 'finalized')
                ->latest()
                ->paginate(15);
        } elseif ($user->isSupervisor()) {
            $evaluations = Evaluation::with(['project', 'supervisor'])
                ->whereHas('project', function($q) use ($user) {
                    $q->where('supervisor_id', $user->id);
                })
                ->latest()
                ->paginate(15);
        } else {
            $evaluations = Evaluation::with(['project', 'supervisor'])
                ->where('status', 'finalized')
                ->whereHas('project', function($q) use ($user) {
                    $q->whereHas('students', function($sq) use ($user) {
                        $sq->where('users.id', $user->id);
                    });
                })
                ->latest()
                ->paginate(15);
        }

        return view('evaluations.index', compact('evaluations'));
    }

    /**
     * عرض تفاصيل تقييم معين
     */
    public function show(Evaluation $evaluation)
    {
        $user = auth()->user();

        $canView = false;
        if ($user->isAdmin()) {
            $canView = true;
        } elseif ($user->isSupervisor() && $evaluation->project->supervisor_id === $user->id) {
            $canView = true;
        } elseif ($user->isStudent() && $evaluation->project->students->contains($user->id)) {
            $canView = true;
        }

        if (!$canView) {
            abort(403);
        }

        $evaluation->load(['project', 'project.supervisor', 'project.students', 'supervisor']);

        return view('evaluations.show', compact('evaluation'));
    }

    /**
     * عرض نموذج تقييم المشروع
     */
    public function create(Project $project)
    {
        if ($project->supervisor_id !== auth()->id()) {
            abort(403);
        }

        // السماح بالتقييم للمشاريع المقبولة أو المقدمة للمراجعة
        if (!in_array($project->status, [Project::STATUS_SUBMITTED, Project::STATUS_APPROVED])) {
            return back()->with('error', 'لا يمكن تقييم المشروع في حالته الحالية');
        }

        $existingEvaluation = $project->evaluations()
            ->where('supervisor_id', auth()->id())
            ->where('status', '!=', 'finalized')
            ->first();

        if ($existingEvaluation) {
            return redirect()->route('evaluations.edit', $existingEvaluation);
        }

        return view('evaluations.create', compact('project'));
    }

    /**
     * تخزين تقييم جديد
     */
    public function store(Request $request, Project $project)
    {
        if ($project->supervisor_id !== auth()->id()) {
            abort(403);
        }

        $validator = Validator::make($request->all(), [
            'creativity_score' => 'required|integer|min:0|max:100',
            'implementation_score' => 'required|integer|min:0|max:100',
            'documentation_score' => 'required|integer|min:0|max:100',
            'presentation_score' => 'required|integer|min:0|max:100',
            'strengths' => 'nullable|string',
            'weaknesses' => 'nullable|string',
            'recommendations' => 'nullable|string',
            'status' => 'in:draft,submitted',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $evaluation = Evaluation::create([
                'project_id' => $project->id,
                'supervisor_id' => auth()->id(),
                'creativity_score' => $request->creativity_score,
                'implementation_score' => $request->implementation_score,
                'documentation_score' => $request->documentation_score,
                'presentation_score' => $request->presentation_score,
                'strengths' => $request->strengths,
                'weaknesses' => $request->weaknesses,
                'recommendations' => $request->recommendations,
                'status' => 'finalized',
                'evaluated_at' => now(),
            ]);

           
             
            $evaluation->calculateTotalPercentage();

    // 👇 هذان السطران هما الحل
$project->refresh();  // تأكد من جلب أحدث بيانات المشروع
    $project->calculateSuccessPercentage();

    if ($evaluation->total_percentage >= 60) {
        $project->update(['status' => Project::STATUS_COMPLETED]);
    }

            $this->logActivity('create', 'Evaluation', $evaluation->id);

            return redirect()->route('evaluations.show', $evaluation)
                ->with('success', 'تم حفظ التقييم بنجاح');
        } catch (\Exception $e) {
            return back()->with('error', 'حدث خطأ أثناء حفظ التقييم: ' . $e->getMessage());
        }
    }

    /**
     * عرض نموذج تعديل التقييم
     */
    public function edit(Evaluation $evaluation)
    {
        if ($evaluation->supervisor_id !== auth()->id()) {
            abort(403);
        }

        if ($evaluation->status === 'finalized') {
            return back()->with('error', 'لا يمكن تعديل تقييم نهائي');
        }

        return view('evaluations.edit', compact('evaluation'));
    }

    /**
     * تحديث التقييم
     */
    public function update(Request $request, Evaluation $evaluation)
{
    if ($evaluation->supervisor_id !== auth()->id()) {
        abort(403);
    }

    if ($evaluation->status === 'finalized') {
        return back()->with('error', 'لا يمكن تعديل تقييم نهائي');
    }

    $validator = Validator::make($request->all(), [
        'creativity_score' => 'required|integer|min:0|max:100',
        'implementation_score' => 'required|integer|min:0|max:100',
        'documentation_score' => 'required|integer|min:0|max:100',
        'presentation_score' => 'required|integer|min:0|max:100',
        'strengths' => 'nullable|string',
        'weaknesses' => 'nullable|string',
        'recommendations' => 'nullable|string',
    ]);

    if ($validator->fails()) {
        return back()->withErrors($validator)->withInput();
    }

    $oldValues = $evaluation->toArray();

    $evaluation->update([
        'creativity_score' => $request->creativity_score,
        'implementation_score' => $request->implementation_score,
        'documentation_score' => $request->documentation_score,
        'presentation_score' => $request->presentation_score,
        'strengths' => $request->strengths,
        'weaknesses' => $request->weaknesses,
        'recommendations' => $request->recommendations,
    ]);

    $evaluation->calculateTotalPercentage();

    // تحديث نسبة نجاح المشروع
    $project = $evaluation->project;
    $project->calculateSuccessPercentage();  // 👈 هذا السطر هو الحل

    // تحديث حالة المشروع إذا كانت النسبة >= 60
    if ($evaluation->total_percentage >= 60 && $project->status !== Project::STATUS_COMPLETED) {
        $project->update(['status' => Project::STATUS_COMPLETED]);
    }

    $this->logActivity('update', 'Evaluation', $evaluation->id, $oldValues, $evaluation->toArray());

    return redirect()->route('evaluations.show', $evaluation)
        ->with('success', 'تم تحديث التقييم بنجاح');
}
    /**
     * تقديم التقييم (جعله نهائياً)
     */
    public function submit(Evaluation $evaluation)
    {
        if ($evaluation->supervisor_id !== auth()->id()) {
            abort(403);
        }

        if ($evaluation->status === 'finalized') {
            return back()->with('error', 'التقييم بالفعل نهائي');
        }

        $evaluation->update([
            'status' => 'finalized',
            'evaluated_at' => now(),
        ]);

        $project = $evaluation->project;
        $project->calculateSuccessPercentage();

        // تغيير حالة المشروع إلى مكتمل إذا كانت النسبة >= 60
        if ($project->success_percentage >= 60 && $project->status !== Project::STATUS_COMPLETED) {
            $project->update(['status' => Project::STATUS_COMPLETED]);
        }

        // إشعار للطلاب
        $this->sendNotification($project->students, $project);

        $this->logActivity('submit', 'Evaluation', $evaluation->id);

        return redirect()->route('projects.show', $project)
            ->with('success', 'تم تقديم التقييم بنجاح');
    }

    // ===========================
    // دوال مساعدة
    // ===========================

    private function sendNotification($students, $project)
    {
        foreach ($students as $student) {
            $this->sendNotificationToUser(
                $student->id,
                'تقييم المشروع',
                "تم تقييم مشروعك '{$project->title_ar}' بنسبة نجاح {$project->success_percentage}%",
                'info',
                route('projects.show', $project)
            );
        }
    }

    private function sendNotificationToUser($userId, $title, $message, $type, $link = null)
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