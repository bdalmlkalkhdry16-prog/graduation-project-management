<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\StudyPlan;
use Illuminate\View\View;

class ScheduleController extends Controller
{
    public function index(): View
    {
        $profile = auth()->user()->studentProfile;

        $schedules = collect();

        if ($profile?->current_level_id) {
            $courseIds = StudyPlan::where('level_id', $profile->current_level_id)->pluck('course_id');

            $schedules = Schedule::whereHas('section', fn ($q) => $q->whereIn('course_id', $courseIds))
                ->with(['section.course', 'section.faculty.user', 'room'])
                ->orderBy('day_of_week')
                ->orderBy('start_time')
                ->get();
        }

        return view('student.schedule.index', compact('schedules', 'profile'));
    }
}