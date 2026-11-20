<?php

namespace App\Policies;

use App\Models\User;

class FeeSchedulePolicy
{
    /** عرض جدول الرسوم. */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('fees.manage') || $user->hasPermission('fees.view');
    }

    /** تحديد أسعار الرسوم وتعديلها. */
    public function manage(User $user): bool
    {
        return $user->hasPermission('fees.manage');
    }
}
