<?php

namespace App\Http\Controllers\Faculty;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAttendanceSessionRequest;
use App\Http\Requests\UpdateAttendanceRecordsRequest;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\Schedule;
use App\Models\StudentProfile;
use App\Models\StudyPlan;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function index(): View
    {
        $facultyProfile = auth()->user()->facultyProfile;

        $schedules = $facultyProfile
            ? Schedule::whereHas('section', fn ($q) => $q->where('faculty_profile_id', $facultyProfile->id))
                ->with(['section.course', 'room'])
                ->get()
            : collect();

        $sessions = $facultyProfile
            ? AttendanceSession::whereHas('schedule.section', fn ($q) => $q->where('faculty_profile_id', $facultyProfile->id))
                ->with(['schedule.section.course'])
                ->latest('session_date')
                ->paginate(20)
            : null;

        return view('faculty.attendance.index', compact('schedules', 'sessions'));
    }

    public function create(): View
    {
        $facultyProfile = auth()->user()->facultyProfile;

        $schedules = $facultyProfile
            ? Schedule::whereHas('section', fn ($q) => $q->where('faculty_profile_id', $facultyProfile->id))
                ->with(['section.course', 'room'])
                ->get()
            : collect();

        return view('faculty.attendance.create', compact('schedules'));
    }

    public function store(StoreAttendanceSessionRequest $request): RedirectResponse
    {
        $session = AttendanceSession::create([
            ...$request->validated(),
            'taken_by' => auth()->id(),
        ]);

        return redirect()->route('faculty.attendance.show', $session);
    }

    public function show(AttendanceSession $attendanceSession): View
    {
        $this->authorize('manageAttendance', $attendanceSession);

        $attendanceSession->load(['schedule.section.course', 'records']);

        $levelIds = StudyPlan::where('course_id', $attendanceSession->schedule->section->course_id)->pluck('level_id');
        $roster = StudentProfile::whereIn('current_level_id', $levelIds)->with('user')->get();

        $existingRecords = $attendanceSession->records->keyBy('student_profile_id');

        return view('faculty.attendance.show', compact('attendanceSession', 'roster', 'existingRecords'));
    }

    public function update(UpdateAttendanceRecordsRequest $request, AttendanceSession $attendanceSession): RedirectResponse
    {
        foreach ($request->validated()['records'] as $record) {
            AttendanceRecord::updateOrCreate(
                [
                    'attendance_session_id' => $attendanceSession->id,
                    'student_profile_id' => $record['student_profile_id'],
                ],
                [
                    'status' => $record['status'],
                    'recorded_by' => auth()->id(),
                ]
            );
        }

        return redirect()
            ->route('faculty.attendance.show', $attendanceSession)
            ->with('success', 'تم حفظ الحضور بنجاح.');
    }
}