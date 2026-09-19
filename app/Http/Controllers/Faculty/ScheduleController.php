<?php

namespace App\Http\Controllers\Faculty;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class ScheduleController extends Controller
{
    /**
     * عرض جدول عضو هيئة التدريس.
     */
    public function index(): View
    {
        $facultyProfile = auth()->user()->facultyProfile;

        $schedules = collect();

        if ($facultyProfile) {
            $schedules = $facultyProfile
                ->sections()
                ->with([
                    'course',
                    'academicTerm',
                    'schedules.room',
                ])
                ->get()
                ->flatMap(function ($section) {
                    return $section->schedules->map(function ($schedule) use ($section) {
                        $schedule->setRelation('section', $section);

                        return $schedule;
                    });
                })
                ->sortBy([
                    ['day_of_week', 'asc'],
                    ['start_time', 'asc'],
                ])
                ->values();
        }

        return view(
            'faculty.schedule.index',
            compact('schedules', 'facultyProfile')
        );
    }
}