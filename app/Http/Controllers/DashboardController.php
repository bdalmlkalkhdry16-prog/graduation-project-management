<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use App\Models\College;
use App\Models\Specialization;
use App\Models\Idea;
use App\Models\AcademicYear;
use App\Models\Department;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * عرض لوحة التحكم حسب أدوار المستخدم وصلاحياته
     */
    public function index()
    {
        $user = auth()->user();

        // مدير النظام
        if ($user->isAdmin()) {
            return $this->adminDashboard();
        }

        // موظف شؤون الطلاب
        if ($user->hasRole('staff')) {
            return view('dashboard.staff');
        }

        // عضو هيئة التدريس / المشرف
        if ($user->hasRole('faculty')) {

            // إذا كان لديه دور مشرف مشاريع تخرج أيضاً
            if ($user->hasRole('supervisor')) {
                return $this->supervisorDashboard();
            }

            return view('dashboard.faculty');
        }

        // مشرف مشاريع التخرج
        if ($user->hasRole('supervisor')) {
            return $this->supervisorDashboard();
        }

        // الطالب
        return $this->studentDashboard();
    }

    /**
     * لوحة تحكم مدير النظام
     */
    private function adminDashboard()
    {
        $stats = [

            // =========================
            // المستخدمون
            // =========================

            'total_users' => User::count(),

            'total_students' => User::where('role', 'student')->count(),

            'total_faculty' => User::whereIn('role', [
                'faculty',
                'supervisor'
            ])->count(),

            'total_staff' => User::where('role', 'staff')->count(),

            // =========================
            // الهيكل الأكاديمي
            // =========================

            'total_colleges' => College::count(),

            'total_departments' => Department::count(),

            'total_specializations' => Specialization::count(),

            'total_academic_years' => AcademicYear::count(),

            // =========================
            // مشاريع التخرج
            // =========================

            'total_projects' => Project::count(),

            'projects_by_status' => Project::select(
                    'status',
                    DB::raw('count(*) as count')
                )
                ->groupBy('status')
                ->pluck('count', 'status'),

            'projects_by_year' => Project::select(
                    'academic_year',
                    DB::raw('count(*) as count')
                )
                ->groupBy('academic_year')
                ->orderByDesc('academic_year')
                ->limit(5)
                ->pluck('count', 'academic_year'),

            'recent_projects' => Project::with([
                    'supervisor',
                    'specialization'
                ])
                ->latest()
                ->limit(5)
                ->get(),

            // =========================
            // المستخدمون الجدد
            // =========================

            'recent_users' => User::latest()
                ->limit(5)
                ->get(),

            // =========================
            // أفكار مشاريع التخرج
            // =========================

            'pending_ideas' => Idea::where('status', 'pending')
                ->with([
                    'student',
                    'specialization'
                ])
                ->latest('submitted_at')
                ->limit(5)
                ->get(),

            // =========================
            // إحصائيات الحسابات
            // =========================

            'active_users' => User::where('is_active', true)->count(),

            'inactive_users' => User::where('is_active', false)->count(),
        ];

        return view('dashboard.admin', compact('stats'));
    }

    /**
     * لوحة تحكم مشرف مشاريع التخرج
     */
    private function supervisorDashboard()
    {
        $supervisorId = auth()->id();

        $stats = [
            'total_supervised_projects' => Project::where(
                'supervisor_id',
                $supervisorId
            )->count(),

            'pending_review' => Project::where(
                'supervisor_id',
                $supervisorId
            )
                ->where('status', Project::STATUS_SUBMITTED)
                ->count(),

            'approved_projects' => Project::where(
                'supervisor_id',
                $supervisorId
            )
                ->where('status', Project::STATUS_APPROVED)
                ->count(),

            'completed_projects' => Project::where(
                'supervisor_id',
                $supervisorId
            )
                ->where('status', Project::STATUS_COMPLETED)
                ->count(),

            'recent_projects' => Project::where(
                'supervisor_id',
                $supervisorId
            )
                ->with([
                    'students',
                    'specialization'
                ])
                ->latest()
                ->limit(5)
                ->get(),

            'pending_evaluations' => \App\Models\Evaluation::where(
                'supervisor_id',
                $supervisorId
            )
                ->where('status', 'draft')
                ->count(),

            'pending_ideas' => Idea::where('status', 'pending')
                ->with([
                    'student',
                    'specialization'
                ])
                ->latest('submitted_at')
                ->limit(5)
                ->get(),
        ];

        return view(
            'dashboard.supervisor',
            compact('stats')
        );
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
                ->join(
                    'projects',
                    'project_members.project_id',
                    '=',
                    'projects.id'
                )
                ->where(
                    'project_members.student_id',
                    $studentId
                )
                ->whereNotIn(
                    'projects.status',
                    [
                        Project::STATUS_COMPLETED,
                        Project::STATUS_REJECTED
                    ]
                )
                ->count(),

            'completed_projects' => DB::table('project_members')
                ->join(
                    'projects',
                    'project_members.project_id',
                    '=',
                    'projects.id'
                )
                ->where(
                    'project_members.student_id',
                    $studentId
                )
                ->where(
                    'projects.status',
                    Project::STATUS_COMPLETED
                )
                ->count(),

            'my_projects_list' => auth()->user()
                ->projects()
                ->with([
                    'supervisor',
                    'specialization'
                ])
                ->latest()
                ->limit(5)
                ->get(),

            'available_projects' => Project::where(
                    'status',
                    Project::STATUS_APPROVED
                )
                ->whereDoesntHave('members')
                ->limit(5)
                ->get(),
        ];

        return view(
            'dashboard.student',
            compact('stats')
        );
    }
}