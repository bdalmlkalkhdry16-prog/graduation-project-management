<?php

namespace App\Policies;

use App\Models\StudentServiceRequest;
use App\Models\User;

class StudentServiceRequestPolicy
{
    public function create(User $user): bool
    {
        return $user->hasPermission('service-requests.manageOwn') && $user->studentProfile !== null;
    }

    public function view(User $user, StudentServiceRequest $request): bool
    {
        if ($user->hasPermission('service-requests.manageAll')) {
            return true;
        }

        return $user->studentProfile?->id === $request->student_profile_id;
    }

    public function updateStatus(User $user, StudentServiceRequest $request): bool
    {
        return $user->hasPermission('service-requests.manageAll');
    }
}