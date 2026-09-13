<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\AttendanceSession;
use Illuminate\View\View;

class AttendanceOversightController extends Controller
{
    public function index(): View
    {
        $sessions = AttendanceSession::with(['schedule.section.course', 'schedule.section.faculty.user', 'takenBy'])
            ->latest('session_date')
            ->paginate(20);

        return view('staff.attendance.index', compact('sessions'));
    }

    public function show(AttendanceSession $attendanceSession): View
    {
        $this->authorize('view', $attendanceSession);

        $attendanceSession->load(['records.studentProfile.user', 'schedule.section.course']);

        return view('staff.attendance.show', compact('attendanceSession'));
    }
}