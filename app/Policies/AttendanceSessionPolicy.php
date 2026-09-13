<?php

namespace App\Policies;

use App\Models\AttendanceSession;
use App\Models\User;

class AttendanceSessionPolicy
{
    public function manageAttendance(User $user, AttendanceSession $session): bool
    {
        if (! $user->hasPermission('attendance.manageOwn')) {
            return false;
        }

        $facultyProfile = $user->facultyProfile;

        return $facultyProfile && $session->schedule->section->faculty_profile_id === $facultyProfile->id;
    }

    public function view(User $user, AttendanceSession $session): bool
    {
        if ($user->hasPermission('attendance.viewAll')) {
            return true;
        }

        return $this->manageAttendance($user, $session);
    }
}