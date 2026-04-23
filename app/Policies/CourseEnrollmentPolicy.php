<?php

namespace App\Policies;

use App\Models\CourseEnrollment;
use App\Models\User;

class CourseEnrollmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isActive();
    }

    public function create(User $user): bool
    {
        return $user->isActive();
    }

    public function update(User $user, CourseEnrollment $courseEnrollment): bool
    {
        return $user->isActive()
            && (int) $user->id === (int) $courseEnrollment->course->user_id;
    }
}
