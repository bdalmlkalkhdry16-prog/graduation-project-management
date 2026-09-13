<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function index(): View
    {
        $profile = auth()->user()->studentProfile;

        $records = $profile
            ? $profile->attendanceRecords()
                ->with(['session.schedule.section.course'])
                ->latest()
                ->paginate(20)
            : null;

        return view('student.attendance.index', compact('records'));
    }
}