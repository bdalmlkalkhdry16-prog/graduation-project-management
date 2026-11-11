<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use Illuminate\View\View;

class ScheduleController extends Controller
{
    public function index(): View
    {
        $profile = auth()->user()->studentProfile;

        $schedules = collect();

        if ($profile) {
            $sectionIds = $profile->enrollments()->active()->pluck('section_id');

            $schedules = Schedule::whereIn('section_id', $sectionIds)
                ->with(['section.course', 'section.faculty.user', 'room'])
                ->orderBy('day_of_week')
                ->orderBy('start_time')
                ->get();
        }

        return view('student.schedule.index', compact('schedules', 'profile'));
    }
}