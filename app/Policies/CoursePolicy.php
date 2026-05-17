<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\Course;
use App\Models\User;
use App\Policies\Concerns\HandlesAdminAccess;

class CoursePolicy
{
    use HandlesAdminAccess;

    public function viewAny(User|Admin $user): bool
    {
        return $user->isActive();
    }

    public function view(User|Admin $user, Course $course): bool
    {
        return $user->isActive();
    }

    public function create(User|Admin $user): bool
    {
        return $user->isActive();
    }

    public function update(User|Admin $user, Course $course): bool
    {
        return $user->isActive()
            && (int) $user->id === (int) $course->user_id;
    }

    public function delete(User|Admin $user, Course $course): bool
    {
        return $this->update($user, $course);
    }

    public function viewEnrollments(User|Admin $user, Course $course): bool
    {
        return $this->update($user, $course);
    }
}
