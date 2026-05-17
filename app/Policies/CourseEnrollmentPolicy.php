<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\CourseEnrollment;
use App\Models\User;
use App\Policies\Concerns\HandlesAdminAccess;

class CourseEnrollmentPolicy
{
    use HandlesAdminAccess;

    public function viewAny(User|Admin $user): bool
    {
        return $user->isActive();
    }

    public function view(User|Admin $user, CourseEnrollment $courseEnrollment): bool
    {
        return $user->isActive();
    }

    public function create(User|Admin $user): bool
    {
        return $user->isActive();
    }

    public function update(User|Admin $user, CourseEnrollment $courseEnrollment): bool
    {
        return $user->isActive()
            && (int) $user->id === (int) $courseEnrollment->course->user_id;
    }
}
