<?php

namespace App\Policies;

use App\Models\StudentFee;
use App\Models\User;

class StudentFeePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('payments.record') || $user->hasPermission('fees.generate');
    }

    /** المحاسب، أو الطالب صاحب الرسم. */
    public function view(User $user, StudentFee $fee): bool
    {
        return $this->viewAny($user)
            || ($user->hasPermission('fees.viewOwn') && $user->studentProfile?->id === $fee->student_profile_id);
    }

    public function generate(User $user): bool
    {
        return $user->hasPermission('fees.generate');
    }

    /** رفع حجب النتائج استثناءً: المدير فقط (يمر عبر Gate::before)، ولا يُمنح لأي دور. */
    public function releaseResults(User $user): bool
    {
        return false;
    }

    public function recordPayment(User $user): bool
    {
        return $user->hasPermission('payments.record');
    }
}
