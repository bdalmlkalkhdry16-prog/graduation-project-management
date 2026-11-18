<?php

namespace App\Policies;

use App\Models\User;

class AcademicSettingPolicy
{
    public function manage(User $user): bool
    {
        return $user->hasPermission('academic-settings.manage');
    }
}
