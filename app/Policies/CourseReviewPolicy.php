<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\Course;
use App\Models\CourseReview;
use App\Models\User;
use App\Policies\Concerns\HandlesAdminAccess;

class CourseReviewPolicy
{
    use HandlesAdminAccess;

    public function viewAny(User|Admin $user): bool
    {
        return $user->isActive();
    }

    public function view(User|Admin $user, CourseReview $courseReview): bool
    {
        return $user->isActive();
    }

    public function create(User|Admin $user, ?Course $course = null): bool
    {
        return $user->isActive();
    }

    public function update(User|Admin $user, CourseReview $courseReview): bool
    {
        return $user->isActive()
            && (int) $courseReview->user_id === (int) $user->id;
    }

    public function delete(User|Admin $user, CourseReview $courseReview): bool
    {
        return $this->update($user, $courseReview);
    }
}
