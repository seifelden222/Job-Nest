<?php

namespace App\Policies;

use App\Models\Course;
use App\Models\User;

class CoursePolicy
{
    public function create(User $user): bool
    {
        return $user->isActive();
    }

    public function update(User $user, Course $course): bool
    {
        return $user->isActive()
            && (int) $user->id === (int) $course->user_id;
    }

    public function delete(User $user, Course $course): bool
    {
        return $this->update($user, $course);
    }

    public function viewEnrollments(User $user, Course $course): bool
    {
        return $this->update($user, $course);
    }
}
