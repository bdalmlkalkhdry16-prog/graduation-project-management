<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStudentProfileRequest;
use App\Http\Requests\UpdateStudentProfileRequest;
use App\Models\Level;
use App\Models\Program;
use App\Models\Specialization;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class StudentProfileController extends Controller
{
    public function index(): View
    {
        $profiles = StudentProfile::with(['user', 'specialization', 'program'])
            ->latest()
            ->paginate(20);

        return view('staff.student-profiles.index', compact('profiles'));
    }

    public function create(): View
    {
        $eligibleUsers = User::where('role', 'student')
            ->whereDoesntHave('studentProfile')
            ->get();

        $specializations = Specialization::active()->get();
        $programs = Program::all();
        $levels = Level::all();

        return view('staff.student-profiles.create', compact('eligibleUsers', 'specializations', 'programs', 'levels'));
    }

    public function store(StoreStudentProfileRequest $request): RedirectResponse
    {
        try {
            $profile = StudentProfile::create($request->validated());
        } catch (\InvalidArgumentException $e) {
            return back()->withInput()->withErrors(['program_id' => $e->getMessage()]);
        }

        return redirect()
            ->route('staff.student-profiles.show', $profile)
            ->with('success', 'تم إنشاء ملف الطالب بنجاح.');
    }

    public function show(StudentProfile $studentProfile): View
    {
        $studentProfile->load(['user', 'specialization', 'program.specialization', 'currentLevel', 'serviceRequests']);

        return view('staff.student-profiles.show', compact('studentProfile'));
    }

    public function edit(StudentProfile $studentProfile): View
    {
        $specializations = Specialization::active()->get();
        $programs = Program::all();
        $levels = Level::all();

        return view('staff.student-profiles.edit', compact('studentProfile', 'specializations', 'programs', 'levels'));
    }

    public function update(UpdateStudentProfileRequest $request, StudentProfile $studentProfile): RedirectResponse
    {
        try {
            $studentProfile->update($request->validated());
        } catch (\InvalidArgumentException $e) {
            return back()->withInput()->withErrors(['program_id' => $e->getMessage()]);
        }

        return redirect()
            ->route('staff.student-profiles.show', $studentProfile)
            ->with('success', 'تم تحديث ملف الطالب بنجاح.');
    }
}