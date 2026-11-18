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

    /** الكشف الرسمي: لطلب كشف درجات مقبول/مكتمل فقط، للطالب صاحبه أو لمعالج الطلبات. */
    public function downloadTranscript(User $user, StudentServiceRequest $request): bool
    {
        return $request->type === StudentServiceRequest::TYPE_TRANSCRIPT
            && in_array($request->status, [StudentServiceRequest::STATUS_APPROVED, StudentServiceRequest::STATUS_COMPLETED], true)
            && $this->view($user, $request);
    }

    public function updateStatus(User $user, StudentServiceRequest $request): bool
    {
        return $user->hasPermission('service-requests.manageAll');
    }
}