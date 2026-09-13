<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreScheduleRequest;
use App\Http\Requests\UpdateScheduleRequest;
use App\Models\Room;
use App\Models\Schedule;
use App\Models\Section;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ScheduleController extends Controller
{
    public function index(): View
    {
        $schedules = Schedule::with(['section.course', 'section.faculty.user', 'room'])
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->paginate(20);

        return view('staff.schedules.index', compact('schedules'));
    }

    public function create(): View
    {
        $sections = Section::with(['course', 'faculty.user'])->get();
        $rooms = Room::where('status', Room::STATUS_AVAILABLE)->orderBy('name')->get();

        return view('staff.schedules.create', compact('sections', 'rooms'));
    }

    public function store(StoreScheduleRequest $request): RedirectResponse
    {
        Schedule::create($request->validated());

        return redirect()->route('staff.schedules.index')->with('success', 'تم إنشاء الجدول بنجاح.');
    }

    public function edit(Schedule $schedule): View
    {
        $sections = Section::with(['course', 'faculty.user'])->get();
        $rooms = Room::orderBy('name')->get();

        return view('staff.schedules.edit', compact('schedule', 'sections', 'rooms'));
    }

    public function update(UpdateScheduleRequest $request, Schedule $schedule): RedirectResponse
    {
        $schedule->update($request->validated());

        return redirect()->route('staff.schedules.index')->with('success', 'تم تحديث الجدول بنجاح.');
    }
}