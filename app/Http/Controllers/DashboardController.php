<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use App\Models\College;
use App\Models\Specialization;
use App\Models\Idea; // إضافة استيراد نموذج الأفكار
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * عرض لوحة التحكم حسب دور المستخدم
     */
    public function index()
    {
        $user = auth()->user();
        if ($user->isAdmin()) {
            return $this->adminDashboard();
        } elseif ($user->isSupervisor()) {
            return $this->supervisorDashboard();
        } else {
            return $this->studentDashboard();
        }
    }

    /**
     * لوحة تحكم المدير
     */
    private function adminDashboard()
    {
        $stats = [
            'total_students' => User::where('role', 'student')->count(),
            'total_supervisors' => User::where('role', 'supervisor')->count(),
            'total_projects' => Project::count(),
            'total_colleges' => College::count(),
            'total_specializations' => Specialization::count(),
            'projects_by_status' => Project::select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->pluck('count', 'status'),
            'projects_by_year' => Project::select('academic_year', DB::raw('count(*) as count'))
                ->groupBy('academic_year')
                ->orderBy('academic_year', 'desc')
                ->limit(5)
                ->pluck('count', 'academic_year'),
            'recent_projects' => Project::with(['supervisor', 'specialization'])
                ->latest()
                ->limit(5)
                ->get(),
            'recent_users' => User::latest()->limit(5)->get(),
            // إضافة الأفكار المعلقة للمدير
            'pending_ideas' => Idea::where('status', 'pending')
                ->with(['student', 'specialization'])
                ->latest('submitted_at')
                ->get(),
        ];

        return view('dashboard.admin', compact('stats'));
    }

    /**
     * لوحة تحكم المشرف
     */
    private function supervisorDashboard()
    {
        $supervisorId = auth()->id();

        $stats = [
            'total_supervised_projects' => Project::where('supervisor_id', $supervisorId)->count(),
            'pending_review' => Project::where('supervisor_id', $supervisorId)
                ->where('status', Project::STATUS_SUBMITTED)
                ->count(),
            'approved_projects' => Project::where('supervisor_id', $supervisorId)
                ->where('status', Project::STATUS_APPROVED)
                ->count(),
            'completed_projects' => Project::where('supervisor_id', $supervisorId)
                ->where('status', Project::STATUS_COMPLETED)
                ->count(),
            'recent_projects' => Project::where('supervisor_id', $supervisorId)
                ->with(['students', 'specialization'])
                ->latest()
                ->limit(5)
                ->get(),
            'pending_evaluations' => \App\Models\Evaluation::where('supervisor_id', $supervisorId)
                ->where('status', 'draft')
                ->count(),
            // إضافة الأفكار المعلقة للمشرف
            'pending_ideas' => Idea::where('status', 'pending')
                ->with(['student', 'specialization'])
                ->latest('submitted_at')
                ->get(),
        ];

        return view('dashboard.supervisor', compact('stats'));
    }

    /**
     * لوحة تحكم الطالب
     */
    private function studentDashboard()
    {
        $studentId = auth()->id();

        $stats = [
            'my_projects' => DB::table('project_members')
                ->where('student_id', $studentId)
                ->count(),
            'active_projects' => DB::table('project_members')
                ->join('projects', 'project_members.project_id', '=', 'projects.id')
                ->where('project_members.student_id', $studentId)
                ->whereNotIn('projects.status', [Project::STATUS_COMPLETED, Project::STATUS_REJECTED])
                ->count(),
            'completed_projects' => DB::table('project_members')
                ->join('projects', 'project_members.project_id', '=', 'projects.id')
                ->where('project_members.student_id', $studentId)
                ->where('projects.status', Project::STATUS_COMPLETED)
                ->count(),
            'my_projects_list' => auth()->user()->projects()
                ->with(['supervisor', 'specialization'])
                ->latest()
                ->limit(5)
                ->get(),
            'available_projects' => Project::where('status', Project::STATUS_APPROVED)
                ->whereDoesntHave('members')
                ->limit(5)
                ->get(),
        ];

        return view('dashboard.student', compact('stats'));
    }
}
