<?php

namespace App\Policies;

use App\Models\StudentProfile;
use App\Models\User;

class StudentProfilePolicy
{
    /** اطلاع الإدارة على السجل الأكاديمي لأي طالب (صلاحية صريحة). */
    public function viewAcademicRecord(User $user, StudentProfile $profile): bool
    {
        return $user->hasPermission('academic-records.viewAll');
    }
}
