<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\User;
use App\Models\College;
use App\Models\Department;
use App\Models\Specialization;
use App\Exports\ProjectsExport;
use App\Exports\StudentsExport;
use App\Exports\SupervisorsExport;
use App\Exports\StatisticsExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index()
    {
        $years = Project::select('academic_year')
            ->distinct()
            ->orderBy('academic_year', 'desc')
            ->pluck('academic_year');

        $colleges = College::active()->get();
        $departments = Department::active()->get();
        $specializations = Specialization::active()->get();

        return view('admin.reports.index', compact('years', 'colleges', 'departments', 'specializations'));
    }

    public function generate(Request $request)
    {
        $request->validate([
            'report_type' => 'required|in:projects,students,supervisors,statistics',
            'format' => 'required|in:html,pdf,excel',
        ]);

        $data = [];
        $view = null;
        $export = null;

        switch ($request->report_type) {
            case 'projects':
                $projects = $this->getProjectsQuery($request)->get();
                $data = [
                    'title' => 'تقرير المشاريع',
                    'projects' => $projects,
                    'total' => $projects->count(),
                    'avg_success_rate' => $projects->avg('success_percentage'),
                    'by_status' => $projects->groupBy('status')->map->count(),
                ];
                $view = 'reports.projects_pdf';
                $export = new ProjectsExport($projects);
                break;

            case 'students':
                $students = $this->getStudentsQuery($request)->get();
                $data = [
                    'title' => 'تقرير الطلاب',
                    'students' => $students,
                    'total' => $students->count(),
                    'with_projects' => $students->filter(fn($s) => $s->projects()->count() > 0)->count(),
                    'without_projects' => $students->filter(fn($s) => $s->projects()->count() == 0)->count(),
                ];
                $view = 'reports.students_pdf';
                $export = new StudentsExport($students);
                break;

            case 'supervisors':
                $supervisors = $this->getSupervisorsQuery($request)->get();
                $data = [
                    'title' => 'تقرير المشرفين',
                    'supervisors' => $supervisors,
                    'total' => $supervisors->count(),
                    'total_projects' => $supervisors->sum(fn($s) => $s->supervisedProjects()->count()),
                ];
                $view = 'reports.supervisors_pdf';
                $export = new SupervisorsExport($supervisors);
                break;

            case 'statistics':
                $stats = $this->getStatistics();
                $data = [
                    'title' => 'تقرير الإحصائيات',
                    'stats' => $stats,
                ];
                $view = 'reports.statistics_pdf';
                $export = new StatisticsExport($stats);
                break;
        }

        // عرض HTML
        if ($request->format === 'html') {
            return view('admin.reports.result', compact('data'));
        }

        // PDF باستخدام خط Amiri
        if ($request->format === 'pdf') {
            $pdf = Pdf::loadView($view, compact('data'))
                ->setPaper('A4', 'portrait')
                ->setOptions([
                    'defaultFont' => 'Amiri',  // استخدام خط Amiri
                    'isHtml5ParserEnabled' => true,
                    'isRemoteEnabled' => true,
                ]);
            $fileName = 'تقرير_' . $request->report_type . '_' . date('Y-m-d') . '.pdf';
            return $pdf->download($fileName);
        }

        // Excel
        if ($request->format === 'excel') {
            $fileName = 'تقرير_' . $request->report_type . '_' . date('Y-m-d') . '.xlsx';
            return Excel::download($export, $fileName);
        }

        return back();
    }

    private function getProjectsQuery($request)
    {
        $query = Project::with(['supervisor', 'specialization', 'students']);

        if ($request->filled('year')) {
            $query->where('academic_year', $request->year);
        }
        if ($request->filled('specialization_id')) {
            $query->where('specialization_id', $request->specialization_id);
        }
        if ($request->filled('department_id')) {
            $specializationIds = Specialization::where('department_id', $request->department_id)->pluck('id');
            $query->whereIn('specialization_id', $specializationIds);
        }
        if ($request->filled('college_id')) {
            $departmentIds = Department::where('college_id', $request->college_id)->pluck('id');
            $specializationIds = Specialization::whereIn('department_id', $departmentIds)->pluck('id');
            $query->whereIn('specialization_id', $specializationIds);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return $query->latest();
    }

    private function getStudentsQuery($request)
    {
        $query = User::where('role', 'student')->with(['projects', 'specialization']);

        if ($request->filled('specialization_id')) {
            $query->where('specialization_id', $request->specialization_id);
        }
        if ($request->filled('department_id')) {
            $specializationIds = Specialization::where('department_id', $request->department_id)->pluck('id');
            $query->whereIn('specialization_id', $specializationIds);
        }
        if ($request->filled('college_id')) {
            $departmentIds = Department::where('college_id', $request->college_id)->pluck('id');
            $specializationIds = Specialization::whereIn('department_id', $departmentIds)->pluck('id');
            $query->whereIn('specialization_id', $specializationIds);
        }

        return $query;
    }

    private function getSupervisorsQuery($request)
    {
        $query = User::where('role', 'supervisor')->with(['specialization', 'supervisedProjects']);

        if ($request->filled('specialization_id')) {
            $query->where('specialization_id', $request->specialization_id);
        }
        if ($request->filled('department_id')) {
            $specializationIds = Specialization::where('department_id', $request->department_id)->pluck('id');
            $query->whereIn('specialization_id', $specializationIds);
        }
        if ($request->filled('college_id')) {
            $departmentIds = Department::where('college_id', $request->college_id)->pluck('id');
            $specializationIds = Specialization::whereIn('department_id', $departmentIds)->pluck('id');
            $query->whereIn('specialization_id', $specializationIds);
        }

        return $query;
    }

    private function getStatistics()
    {
        return [
            'total_students' => User::where('role', 'student')->count(),
            'total_supervisors' => User::where('role', 'supervisor')->count(),
            'total_projects' => Project::count(),
            'total_colleges' => College::count(),
            'total_departments' => Department::count(),
            'total_specializations' => Specialization::count(),
            'projects_by_status' => Project::select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray(),
            'projects_by_year' => Project::select('academic_year', DB::raw('count(*) as count'))
                ->groupBy('academic_year')
                ->orderBy('academic_year', 'desc')
                ->pluck('count', 'academic_year')
                ->toArray(),
        ];
    }
}